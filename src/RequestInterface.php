<?php

namespace VS\Request;

/**
 * Interface RequestInterface
 * @package VS\Request
 * @author Varazdat Stepanyan
 */
interface RequestInterface
{
    /**
     * @return string
     */
    public function method(): string;

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * @param array $data
     */
    public function bind(array $data): void;

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key);

    /**
     * @return array
     */
    public function all(): array;
}