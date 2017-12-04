<?php

namespace vakata\validation;

/**
 * A validation class, supporting arrays and nested arrays of data.
 */
class Rule
{
    protected $key;
    protected $handler;
    protected $message;
    protected $rule;
    protected $data;
    protected $optional;
    protected $enabled;

    public function __construct(
        string $key,
        callable $handler,
        string $message = '',
        string $rule = 'callback',
        array $data = [],
        bool $optional = true
    ) {
        $this->key = $key;
        $this->handler = $handler;
        $this->message = $message;
        $this->rule = $rule;
        $this->data = $data;
        $this->optional = $optional;
        $this->enabled = true;
    }
    public function getKey(): string
    {
        return $this->key;
    }
    public function getMessage(): string
    {
        return $this->message;
    }
    public function getRule(): string
    {
        return $this->rule;
    }
    public function getData(): array
    {
        return $this->data;
    }
    public function isRequired(): bool
    {
        return !$this->optional;
    }
    public function isOptional(): bool
    {
        return $this->optional;
    }
    public function isEnabled(): bool
    {
        return $this->enabled;
    }
    public function isDisabled(): bool
    {
        return !$this->enabled;
    }

    public function setMessage(string $message): Rule
    {
        $this->message = $message;
        return $this;
    }
    public function setData(array $data): Rule
    {
        $this->data = $data;
        return $this;
    }
    public function required(): Rule
    {
        $this->optional = false;
        return $this;
    }
    public function optional(): Rule
    {
        $this->optional = true;
        return $this;
    }
    public function disable(): Rule
    {
        $this->enabled = false;
        return $this;
    }
    public function enable(): Rule
    {
        $this->enabled = true;
        return $this;
    }

    public function execute($value, $data)
    {
        return call_user_func($this->handler, $value, $data);
    }
}