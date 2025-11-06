<?php

namespace pms\helper\hrb;

abstract class HRBOptions
{
    protected array $data = [];
    /**
     * @throws \Exception
     */
    public function __get(string $name)
    {
        return $this->data[$name] ?? throw new \Exception(static::class.":$name 属性为必填项");
    }

    public function __set(string $name, $value)
    {
        $this->data[$name] = $value;
    }
}