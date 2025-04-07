<?php

declare(strict_types=1);

namespace GAState\Web\Slim\Log;

use Monolog\Handler\AbstractProcessingHandler as AbstractProcessingHandler;
use Monolog\Level                             as Level;
use Monolog\Logger                            as Logger;
use Monolog\LogRecord                         as LogRecord;
use PDO                                       as PDO;
use PDOStatement                              as PDOStatement;
use Psr\Log\LogLevel                          as LogLevel;

class DBLogHandler extends AbstractProcessingHandler
{
    /**
     * @var bool $initialized defines whether the MySQL connection is been initialized
     */
    private bool $initialized = false;


    /**
     * @var PDO $pdo object of database connection
     */
    protected PDO $pdo;


    /**
     * @var PDOStatement $statement statement to insert a new record
     */
    private PDOStatement $statement;


    /**
     * @var string $table the table to store the logs in
     */
    private string $table = 'logs';


    /**
     * @var string[] default fields that are stored in db
     */
    private array $defaultfields = ['id', 'datetime', 'channel', 'level', 'message'];


    /**
     * @var string[] additional fields to be stored in the database
     *
     * For each field `$field`, an additional context field with the name `$field`
     * is expected along the message, and further the database needs to have these fields
     * as the values are stored in the column name `$field`.
     */
    private array $additionalFields = [];


    /**
     * @var string[] $fields
     */
    private array $fields = [];


    /**
     * Constructor of this class, sets the PDO and calls parent constructor
     *
     * @param PDO $pdo PDO Connector for the database
     * @param string $table Table in the database to store the logs in
     * @param string[] $additionalFields Additional Context Parameters to store in database
     * @param bool $skipDatabaseModifications Defines whether attempts to alter database should be skipped
     * @param int|string|Level|LogLevel::* $level  The minimum logging level at which this handler will be triggered
     * @param bool $bubble
     *
     * @phpstan-param value-of<Level::VALUES>|value-of<Level::NAMES>|Level|LogLevel::* $level
     */
    public function __construct(
        ?PDO $pdo = null,
        string $table = '',
        array $additionalFields = [],
        bool $skipDatabaseModifications = false,
        int|string|Level $level = Logger::DEBUG,
        bool $bubble = true,
    ) {
        if (!is_null($pdo)) {
            $this->pdo = $pdo;
        }
        $this->table = $table;
        $this->additionalFields = $additionalFields;
        parent::__construct($level, $bubble);

        if ($skipDatabaseModifications) {
            $this->mergeDefaultAndAdditionalFields();
            $this->initialized = true;
        }
    }


    /**
     * Initializes this handler by creating the table if it not exists
     *
     * @return void
     */
    private function initialize(): void
    {
        $this->pdo->exec(
            "CREATE TABLE IF NOT EXISTS `{$this->table}`"
                . " ("
                . "   `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,"
                . "   `datetime` DATETIME NOT NULL,"
                . "   `channel` VARCHAR(255),"
                . "   `level` VARCHAR(16),"
                . "   `message` LONGTEXT,"
                . "   INDEX(datetime),"
                . "   INDEX(channel),"
                . "   INDEX(level)"
                . " )"
        );

        // Read out actual columns
        $actualFields = [];
        $rs = $this->pdo->query("SELECT * FROM `{$this->table}` LIMIT 0");
        if ($rs !== false) {
            for ($i = 0; $i < $rs->columnCount(); $i++) {
                $col = $rs->getColumnMeta($i);
                if ($col !== false) {
                    $actualFields[] = $col['name'];
                }
            }
        }
        /** @var string[] $actualFields */

        // Calculate changed entries
        $removedColumns = array_diff(
            $actualFields,
            $this->additionalFields,
            $this->defaultfields
        );
        $addedColumns = array_diff($this->additionalFields, $actualFields);

        // Remove columns
        if (count($removedColumns) > 0) {
            foreach ($removedColumns as $c) {
                $this->pdo->exec("ALTER TABLE `{$this->table}` DROP `{$c}`;");
            }
        }

        // Add columns
        if (count($addedColumns) > 0) {
            foreach ($addedColumns as $c) {
                $this->pdo->exec("ALTER TABLE `{$this->table}` add `{$c}` TEXT NULL DEFAULT NULL;");
            }
        }

        $this->mergeDefaultAndAdditionalFields();

        $this->initialized = true;
    }


    /**
     * Prepare the sql statment depending on the fields that should be written to the database
     *
     * @return void
     */
    private function prepareStatement(): void
    {
        // Prepare statement
        $columns = "";
        $fields = "";
        foreach ($this->fields as $key => $f) {
            if ($f === 'id') {
                continue;
            }
            if ($key === 1) {
                $columns .= "$f";
                $fields .= ":$f";
                continue;
            }

            $columns .= ", $f";
            $fields .= ", :$f";
        }

        $this->statement = $this->pdo->prepare("INSERT INTO `{$this->table}` ({$columns}) VALUES ({$fields})");
    }


    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param  LogRecord $record
     * @return void
     */
    protected function write(LogRecord $record): void
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        // reset $fields with default values
        $this->fields = $this->defaultfields;

        $recordContext = array_merge($record->context, $record->extra ?? []);
        $contentArray = array_merge([
            'datetime' => $record->datetime->format('c'),
            'channel' => $record->channel,
            'level' => $record->level->toPsrLogLevel(),
            'message' => $record->formatted,
            'context' => $recordContext
        ], $recordContext);

        // unset array keys that are passed put not defined to be stored, to prevent sql errors
        foreach ($contentArray as $key => $context) {
            if (!in_array($key, $this->fields, true)) {
                unset($contentArray[$key]);
                unset($this->fields[array_search($key, $this->fields, true)]);
            } elseif ($context === null) {
                unset($contentArray[$key]);
                unset($this->fields[array_search($key, $this->fields, true)]);
            } elseif (!(is_string($context) || is_numeric($context) || is_bool($context))) {
                $contentArray[$key] = json_encode($context);
            }
        }

        $this->prepareStatement();

        // Remove unused keys
        foreach ($this->additionalFields as $key => $context) {
            if (!isset($contentArray[$key])) {
                unset($this->additionalFields[$key]);
            }
        }

        // Fill content array with "null" values if not provided
        $contentArray = $contentArray + array_combine(
            $this->additionalFields,
            array_fill(0, count($this->additionalFields), null)
        );

        $this->statement->execute($contentArray);
    }


    /**
     * Merges default and additional fields into one array
     *
     * @return void
     */
    private function mergeDefaultAndAdditionalFields(): void
    {
        $this->defaultfields = array_merge($this->defaultfields, $this->additionalFields);
    }
}
