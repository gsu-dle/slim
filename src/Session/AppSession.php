<?php

declare(strict_types=1);

namespace GAState\Web\Slim\Session;

use ArrayIterator as ArrayIterator;
use Iterator      as Iterator;

class AppSession implements AppSessionInterface
{
    protected bool $deferStart = false;
    protected bool $deferEnd = false;
    protected bool $started = false;


    /**
     * @var array<string, mixed> $defaultOptions
     */
    protected array $defaultOptions = [
        'use_strict_mode'        => 1,
        'cookie_httponly'        => 1,
        'cookie_samesite'        => 'Lax',
        'cookie_lifetime'        => 7200,
        'gc_maxlifetime'         => 7200,
        'sid_length'             => 48,
        'sid_bits_per_character' => 5
    ];


    /**
     * @var array<string, mixed> $options
     */
    protected array $options = [];


    /**
     * @param array<string, mixed> $options
     * @param bool $deferStart
     * @param bool $deferEnd
     */
    public function __construct(
        array $options = [],
        bool $deferStart = false,
        bool $deferEnd = false
    ) {
        $this->deferStart = $deferStart;
        $this->deferEnd = $deferEnd;

        if ($this->deferStart) {
            $this->defaultOptions = array_merge($this->defaultOptions, $options);
        } else {
            $this->startSession($options);
        }
    }


    public function __destruct()
    {
        if (!$this->deferEnd) {
            $this->endSession();
        }
    }


    /**
     * @param array<string, mixed> $options
     *
     * @return bool
     */
    public function startSession(array $options = []): bool
    {
        if (!$this->started) {
            $this->options = array_merge($this->defaultOptions, $options);
            $this->started = session_start($this->options);
        }
        return $this->started;
    }


    /**
     * @return bool
     */
    public function endSession(): bool
    {
        if ($this->started) {
            $this->options = [];
            $this->started = !session_write_close();
        }
        return !$this->started;
    }


    /**
     * @param string $key
     *
     * @return bool
     */
    public function offsetExists(mixed $key): bool
    {
        return isset($_SESSION[$key]);
    }


    /**
     * @param string $key
     *
     * @return mixed
     */
    public function offsetGet(mixed $key): mixed
    {
        return $_SESSION[$key] ?? null;
    }


    /**
     * @param ?string $key
     * @param mixed $value
     *
     * @return void
     */
    public function offsetSet(mixed $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }


    /**
     * @param string $key
     *
     * @return void
     */
    public function offsetUnset(mixed $key): void
    {
        unset($_SESSION[$key]);
    }


    /**
     * @return Iterator
     */
    public function getIterator(): Iterator
    {
        return new ArrayIterator($_SESSION);
    }


    /**
     * @return array<string,mixed>
     */
    public function __serialize(): array
    {
        return [
            'started'        => $this->started,
            'defaultOptions' => $this->defaultOptions,
            'options'        => $this->options,
            'values'         => $_SESSION
        ];
    }


    /**
     * @param array<string,mixed> $data
     *
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $started = ($data['started'] ?? false) === true;

        $defaultOptions = $data['defaultOptions'] ?? null;
        if (is_array($defaultOptions)) {
            $this->defaultOptions = $defaultOptions;
        }

        $options = $data['options'] ?? null;
        if ($started) {
            $started = $this->startSession(is_array($options) ? $options : []);
        }

        $values = $data['values'] ?? null;
        if ($started && is_array($values)) {
            $_SESSION = $values;
        }
    }


    /**
     * @return int
     */
    public function count(): int
    {
        return count($_SESSION);
    }
}
