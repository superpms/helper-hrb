<?php

namespace pms\helper\hrb;

abstract class HRBRequest implements HRBRequestInterface
{

    protected string $uri;
    protected string $method = 'GET';
    protected array $headers = [];
    protected mixed $arguments = [];

    protected array $query = [];

    public function getMethod(): string{
        return strtoupper($this->method);
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function setUri(string $uri): static
    {
        $this->uri = $uri;
        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setHeaders(array $headers): static
    {
        $this->headers = $headers;
        return $this;
    }

    public function pushHeader(string $header): static
    {
        $this->headers [] = $header;
        return $this;
    }

    public function getArguments(): mixed
    {
        return $this->arguments;
    }

    public function setArguments(mixed $arguments): static
    {
        $this->arguments = $arguments;
        return $this;
    }

    public function pushArgument(string $key, mixed $value): static
    {
        $this->arguments[$key] = $value;
        return $this;
    }


    public function getQuery(): array{
        return $this->query;
    }

    public function setQuery(array $query): static{
        $this->query = $query;
        return $this;
    }

    public function pushQuery(string $key, string $value):static
    {
        $this->query[$key] = $value;
        return $this;
    }

    public function callback(mixed $result): mixed
    {
        return $result;
    }

}