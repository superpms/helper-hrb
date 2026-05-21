# API 参考

## HRBClientInterface

文件：`src/pms/helper/hrb/HRBClientInterface.php`

```php
public function execute(HRBRequestInterface $request): mixed;
```

定义请求客户端的执行入口。

## HRBClient

文件：`src/pms/helper/hrb/HRBClient.php`

### 属性

```php
protected array $arguments = [];
protected array $headers = [];
protected array $query = [];
```

客户端级默认参数、Header 和 Query。

### before()

```php
protected function before(HRBRequestInterface $request): HRBRequestInterface
```

默认返回原请求。用于继承类在请求发出前修改请求对象。

### after()

```php
protected function after(Response $response): mixed
```

默认返回 `$response->getJsonBody()`。

### execute()

```php
public function execute(HRBRequestInterface $request): mixed
```

执行请求并返回 `$request->callback($result)` 的结果。

### buildUrlWithQuery()

```php
private function buildUrlWithQuery(string $url, array $query = []): string
```

私有方法。query 为空时返回原 URL；否则根据 URL 中是否已有 `?` 选择追加 `?` 或 `&`，再拼接 `http_build_query($query)`。

## HRBRequestInterface

文件：`src/pms/helper/hrb/HRBRequestInterface.php`

```php
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
public function setQuery(array $query): static;
public function pushQuery(string $key, string $value): static;

public function callback(mixed $result): mixed;
```

注意：接口中 `getQuery()` 标注返回 `mixed`，当前 `HRBRequest` 实现返回 `array`。

## HRBRequest

文件：`src/pms/helper/hrb/HRBRequest.php`

### 属性

```php
protected string $uri;
protected string $method = 'GET';
protected array $headers = [];
protected mixed $arguments = [];
protected array $query = [];
```

### getMethod()

```php
public function getMethod(): string
```

返回大写 HTTP method。

### URI 方法

```php
public function getUri(): string
public function setUri(string $uri): static
```

读写请求 URI。

### Header 方法

```php
public function getHeaders(): array
public function setHeaders(array $headers): static
public function pushHeader(string $header): static
```

读写和追加 Header 行。

### Arguments 方法

```php
public function getArguments(): mixed
public function setArguments(mixed $arguments): static
public function pushArgument(string $key, mixed $value): static
```

读写和追加请求参数。

### Query 方法

```php
public function getQuery(): array
public function setQuery(array $query): static
public function pushQuery(string $key, string $value): static
```

读写和追加 URI query 参数。

### callback()

```php
public function callback(mixed $result): mixed
```

默认透传 `$result`。请求类可覆盖。

## HRBOptions

文件：`src/pms/helper/hrb/HRBOptions.php`

### 属性

```php
protected array $data = [];
```

### __get()

```php
public function __get(string $name)
```

返回 `$data[$name]`；不存在时抛出异常：

```text
<当前类名>:<属性名> 属性为必填项
```

### __set()

```php
public function __set(string $name, $value)
```

把值写入 `$data[$name]`。
