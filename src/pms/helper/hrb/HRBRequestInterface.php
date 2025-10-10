<?php

namespace pms\helper\hrb;

interface HRBRequestInterface
{
    public function getMethod(): string;

    public function getUri(): string;
    public function setUri(string $uri): static;

    public function getHeaders(): array;
    public function setHeaders(array $headers): static;
    public function pushHeader(string $header): static;

    public function getArguments(): mixed;
    public function setArguments(mixed $arguments): static;
    public function pushArgument(string $key, mixed $value): static;

    public function getQuery(): mixed;
    public function setQuery(array $query):static;
    public function pushQuery(string $key, string $value):static;

    public function callback(mixed $result):mixed;
}