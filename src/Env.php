<?php

declare(strict_types=1);

namespace GAState\Web\Slim;

use Dotenv\Dotenv    as Dotenv;
use RuntimeException as RuntimeException;

class Env
{
    public const APP_CACHE_DIR         = 'APP_CACHE_DIR';
    public const APP_DIR               = 'APP_DIR';
    public const BASE_DIR              = 'BASE_DIR';
    public const BASE_URI              = 'BASE_URI';
    public const DI_ENABLE_CMPL        = 'DI_ENABLE_CMPL';
    public const DI_DEF_FILE           = 'DI_DEF_FILE';
    public const DI_CMPL_DIR           = 'DI_CMPL_DIR';
    public const DI_PRXY_DIR           = 'DI_PRXY_DIR';
    public const LOG_DIR               = 'LOG_DIR';
    public const LOG_NAME              = 'LOG_NAME';
    public const LOG_FILE              = 'LOG_FILE';
    public const LOG_LEVEL             = 'LOG_LEVEL';
    public const PDO_DSN               = 'PDO_DSN';
    public const PDO_USERNAME          = 'PDO_USERNAME';
    public const PDO_PASSWORD          = 'PDO_PASSWORD';
    public const SERVER_PREFIX         = 'SERVER_OPT';
    public const SESSION_PREFIX        = 'SESSION_OPT';
    public const SLIM_DIR              = 'SLIM_DIR';
    public const SLIM_DISP_ERR_DETAILS = 'SLIM_DISP_ERR_DETAILS';
    public const SLIM_LOG_ERR          = 'SLIM_LOG_ERR';
    public const SLIM_LOG_ERR_DETAILS  = 'SLIM_LOG_ERR_DETAILS';
    public const SLIM_TMPL_DIR         = 'SLIM_TMPL_DIR';
    public const TMPL_CACHE_DIR        = 'TMPL_CACHE_DIR';
    public const TMPL_DIR              = 'TMPL_DIR';
    public const TWIG_PREFIX           = 'TWIG_OPT';
    public const WORK_DIR              = 'WORK_DIR';


    protected static bool $envLoaded = false;


    /**
     * @param string $baseDir
     * @param string $baseURI
     *
     * @return void
     */
    final public static function load(
        string $baseDir,
        string $baseURI
    ): void {
        if (static::$envLoaded) {
            return;
        }

        $_baseDir = realpath($baseDir);
        if ($_baseDir === false) {
            throw new RuntimeException("Invalid base directory: '{$baseDir}'");
        }

        if (substr($baseURI, -1, 1) === '/') {
            $baseURI = substr($baseURI, 0, -1);
        }

        static::setString(static::BASE_DIR, $baseDir);
        static::setString(static::BASE_URI, $baseURI);

        (Dotenv::createImmutable($baseDir))->load();

        $server = static::getValues(static::SERVER_PREFIX);
        foreach ($server as $name => $value) {
            $_SERVER[$name] = $value;
        }

        static::setDefaults();

        static::$envLoaded = true;
    }


    protected static function setDefaults(): void
    {
        $baseDir = static::getString(static::BASE_DIR);
        $appDir  = static::setString(static::APP_DIR, "{$baseDir}/src");
        $logDir  = static::setString(static::LOG_DIR, "{$baseDir}/logs");
        $logName = static::setString(static::LOG_NAME, "app");
        $slimDir = static::setString(static::SLIM_DIR, __DIR__);
        $workDir = static::setString(static::WORK_DIR, "{$baseDir}/work");

        static::setString(static::APP_CACHE_DIR, "{$workDir}/app-cache");
        static::setBool(static::DI_ENABLE_CMPL, false);
        static::setString(static::DI_DEF_FILE, "{$appDir}/Dependencies.php");
        static::setString(static::DI_CMPL_DIR, "{$workDir}/php-di");
        static::setString(static::DI_PRXY_DIR, "{$workDir}/php-di/proxies");
        static::setString(static::LOG_FILE, "{$logDir}/{$logName}.log");
        static::setString(static::LOG_LEVEL, "ERROR");
        static::setBool(static::SLIM_DISP_ERR_DETAILS, false);
        static::setBool(static::SLIM_LOG_ERR, true);
        static::setBool(static::SLIM_LOG_ERR_DETAILS, false);
        static::setString(static::SLIM_TMPL_DIR, "{$slimDir}/../templates");
        static::setString(static::TMPL_CACHE_DIR, "{$workDir}/templates");
        static::setString(static::TMPL_DIR, "{$baseDir}/templates");
    }


    protected static function set(string $name, mixed $value): mixed
    {
        $_ENV[$name] = $value;
        return $value;
    }


    protected static function setString(string $name, string $default = ""): string
    {
        /** @var string $value */
        $value = static::set($name, static::getString($name, $default));
        return $value;
    }


    protected static function setInt(string $name, int $default = 0): int
    {
        /** @var int $value */
        $value = static::set($name, static::getInt($name, $default));
        return $value;
    }


    protected static function setBool(string $name, bool $default = false): bool
    {
        /** @var bool $value */
        $value = static::set($name, static::getBool($name, $default));
        return $value;
    }


    /**
     * @param string $name
     * @param mixed $default
     *
     * @return mixed
     */
    public static function get(
        string $name,
        mixed $default = null
    ): mixed {
        return $_ENV[$name] ?? $default;
    }


    /**
     * @param string $name
     * @param string $default
     *
     * @return string
     */
    public static function getString(string $name, string $default = ''): string
    {
        $value = static::get($name, $default);
        return strval(is_scalar($value) ? $value : '');
    }


    /**
     * @param string $name
     * @param int $default
     *
     * @return int
     */
    public static function getInt(string $name, int $default = 0): int
    {
        $value = static::get($name, $default);
        return intval(is_scalar($value) ? $value : 0);
    }


    /**
     * @param string $name
     * @param bool $default
     *
     * @return bool
     */
    public static function getBool(string $name, bool $default = false): bool
    {
        $strVal = strtolower(static::getString($name, $default ? '1' : '0'));
        return in_array($strVal, ['1', 'y', 'yes', 't', 'true', 'on'], true);
    }


    /**
     * @param string $prefix
     * @param array<string,int|float|string|bool> $values
     *
     * @return array<string,int|float|string|bool>
     */
    public static function getValues(string $prefix, array $values = []): array
    {
        if (substr($prefix, -1, 1) !== '_') {
            $prefix = "{$prefix}_";
        }

        foreach ($_ENV as $name => $value) {
            $name = strtoupper($name);
            if (str_starts_with($name, $prefix) && is_scalar($value)) {
                $values[strtolower(substr($name, strlen($prefix)))] = $value;
            }
        }

        return $values;
    }
}
