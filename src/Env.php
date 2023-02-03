<?php

declare(strict_types=1);

namespace GAState\Web\Slim;

use Dotenv\Dotenv;
use RuntimeException;

class Env
{
    public const BASE_URI = 'BASE_URI';
    public const BASE_DIR = 'BASE_DIR';
    public const WORK_DIR = 'WORK_DIR';
    public const APP_DIR  = 'APP_DIR';
    public const SLIM_DIR = 'SLIM_DIR';

    public const DI_DEF_FILE = 'DI_DEF_FILE';
    public const DI_CMPL_DIR = 'DI_CMPL_DIR';
    public const DI_PRXY_DIR = 'DI_PRXY_DIR';

    public const LOG_DIR   = 'LOG_DIR';
    public const LOG_NAME  = 'LOG_NAME';
    public const LOG_FILE  = 'LOG_FILE';
    public const LOG_LEVEL = 'LOG_LEVEL';

    public const SLIM_DISP_ERR_DETAILS = 'SLIM_DISP_ERR_DETAILS';
    public const SLIM_LOG_ERR          = 'SLIM_LOG_ERR';
    public const SLIM_LOG_ERR_DETAILS  = 'SLIM_LOG_ERR_DETAILS';

    public const SESSION_CACHE_NAME = 'SESSION_CACHE_NAME';
    public const APP_CACHE_NAME     = 'APP_CACHE_NAME';
    public const APP_CACHE_DIR      = 'APP_CACHE_DIR';

    public const SLIM_TMPL_DIR  = 'SLIM_TMPL_DIR';
    public const TMPL_DIR       = 'TMPL_DIR';
    public const TMPL_CACHE_DIR = 'TMPL_CACHE_DIR';

    public const SERVER_PREFIX  = 'SERVER_OPT';
    public const SESSION_PREFIX = 'SESSION_OPT';
    public const TWIG_PREFIX = 'TWIG_OPT';


    private static bool $envLoaded = false;


    /**
     * @param string $baseDir
     * @param string $baseURI
     *
     * @return void
     */
    public static function load(
        string $baseDir,
        string $baseURI
    ): void {
        if (self::$envLoaded) {
            return;
        }

        $_baseDir = realpath($baseDir);
        if ($_baseDir === false) {
            throw new RuntimeException("Invalid base directory: '{$baseDir}'");
        }

        (Dotenv::createImmutable($baseDir))->load();

        $server = self::getValues(self::SERVER_PREFIX);
        foreach ($server as $name => $value) {
            $_SERVER[$name] = $value;
        }

        if (substr($baseURI, -1, 1) === '/') {
            $baseURI = substr($baseURI, 0, -1);
        }

        $_ENV[self::BASE_DIR] = $baseDir;
        $_ENV[self::BASE_URI] = $baseURI;
        $_ENV[self::WORK_DIR] = $workDir = self::getString(self::WORK_DIR, "{$baseDir}/work");
        $_ENV[self::APP_DIR]  = $appDir = self::getString(self::APP_DIR, "{$baseDir}/src");
        $_ENV[self::SLIM_DIR] = $slimDir = self::getString(self::SLIM_DIR, __DIR__);

        $_ENV[self::DI_DEF_FILE] = self::getString(self::DI_DEF_FILE, "{$appDir}/Dependencies.php");
        $_ENV[self::DI_CMPL_DIR] = self::getString(self::DI_CMPL_DIR, "{$workDir}/php-di");
        $_ENV[self::DI_PRXY_DIR] = self::getString(self::DI_PRXY_DIR, "{$workDir}/php-di/proxies");

        $_ENV[self::LOG_DIR]   = $logDir = self::getString(self::LOG_DIR, "{$baseDir}/logs");
        $_ENV[self::LOG_NAME]  = $logName = self::getString(self::LOG_NAME, "Slim");
        $_ENV[self::LOG_FILE]  = self::getString(self::LOG_FILE, "{$logDir}/{$logName}.log");
        $_ENV[self::LOG_LEVEL] = self::getString(self::LOG_LEVEL, "ERROR");

        $_ENV[self::SLIM_DISP_ERR_DETAILS] = self::getBool(self::SLIM_DISP_ERR_DETAILS, false);
        $_ENV[self::SLIM_LOG_ERR]          = self::getBool(self::SLIM_LOG_ERR, true);
        $_ENV[self::SLIM_LOG_ERR_DETAILS]  = self::getBool(self::SLIM_LOG_ERR_DETAILS, false);

        $_ENV[self::SESSION_CACHE_NAME] = self::getString(self::SESSION_CACHE_NAME, 'SESSION_CACHE');
        $_ENV[self::APP_CACHE_NAME]     = self::getString(self::APP_CACHE_NAME, 'APP_CACHE');
        $_ENV[self::APP_CACHE_DIR]      = self::getString(self::APP_CACHE_DIR, "{$workDir}/app-cache");

        $_ENV[self::TMPL_DIR]       = self::getString(self::TMPL_DIR, "{$baseDir}/templates");
        $_ENV[self::SLIM_TMPL_DIR]  = self::getString(self::SLIM_TMPL_DIR, $slimDir . '/../templates');
        $_ENV[self::TMPL_CACHE_DIR] = self::getString(self::TMPL_DIR, "{$workDir}/templates");

        self::$envLoaded = true;
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
        return strval(self::get($name, $default));
    }


    /**
     * @param string $name
     * @param int $default
     *
     * @return int
     */
    public static function getInt(string $name, int $default = 0): int
    {
        return intval(self::get($name, $default));
    }


    /**
     * @param string $name
     * @param bool $default
     *
     * @return bool
     */
    public static function getBool(string $name, bool $default = false): bool
    {
        $strVal = strtolower(self::getString($name, $default ? '1' : '0'));
        return in_array($strVal, ['1', 'y', 'yes', 't', 'true', 'on'], true);
    }


    /**
     * @param string $prefix
     * @param array<string,string|int|bool> $values
     *
     * @return array<string,string|int|bool>
     */
    public static function getValues(string $prefix, array $values = []): array
    {
        if (substr($prefix, -1, 1) !== '_') {
            $prefix = "{$prefix}_";
        }

        foreach ($_ENV as $name => $value) {
            $name = strtoupper($name);
            if (str_starts_with($name, $prefix)) {
                $values[strtolower(substr($name, strlen($prefix)))] = $value;
            }
        }

        return $values;
    }
}
