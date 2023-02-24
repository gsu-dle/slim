<?php

declare(strict_types=1);

namespace GAState\Web\Slim\Session;

use ArrayAccess       as ArrayAccess;
use Countable         as Countable;
use Iterator          as Iterator;
use IteratorAggregate as IteratorAggregate;

/**
 * @extends ArrayAccess<string,mixed>
 * @extends IteratorAggregate<string,mixed>
 */
interface AppSessionInterface extends ArrayAccess, Countable, IteratorAggregate
{
    /**
     * @param array<string, mixed> $options
     *
     * @return bool
     */
    public function startSession(array $options): bool;


    /**
     * @return bool
     */
    public function endSession(): bool;


    /**
     * @param string $key
     *
     * @return bool
     */
    public function offsetExists(mixed $key): bool;


    /**
     * @param string $key
     *
     * @return mixed
     */
    public function offsetGet(mixed $key): mixed;


    /**
     * @param ?string $key
     * @param mixed $value
     *
     * @return void
     */
    public function offsetSet(mixed $key, mixed $value): void;


    /**
     * @param string $key
     *
     * @return void
     */
    public function offsetUnset(mixed $key): void;


    /**
     * @return Iterator
     */
    public function getIterator(): Iterator;


    /**
     * @return array<string,mixed>
     */
    public function __serialize(): array;


    /**
     * @param array<string,mixed> $data
     *
     * @return void
     */
    public function __unserialize(array $data): void;


    /**
     * @return int
     */
    public function count(): int;
}
