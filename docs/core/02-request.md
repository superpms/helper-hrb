# HRBRequest

`HRBRequest` 是单次请求对象基类，文件位于 `src/pms/helper/hrb/HRBRequest.php`。它实现 `HRBRequestInterface`，负责保存请求 URI、HTTP 方法、Header、Arguments、Query，并提供链式 setter。

## 默认字段

```php
protected string $uri;
protected string $method = 'GET';
protected array $headers = [];
protected mixed $arguments = [];
protected array $query = [];
```

请求类通常通过属性默认值和业务化 setter 组织参数：

```php
class GetAccessToken extends HRBRequest
{
    protected string $uri = 'https://api.weixin.qq.com/cgi-bin/token';
    protected string $method = 'GET';
    protected mixed $arguments = [
        'grant_type' => 'client_credential',
    ];

    public function setGrantType(string $grantType): self
    {
        $this->arguments['grant_type'] = $grantType;
        return $this;
    }
}
```

## Method

`getMethod()` 返回大写方法名：

```php
public function getMethod(): string
{
    return strtoupper($this->method);
}
```

如果请求类把 `$method` 写成 `post`，执行时仍会传给底层为 `POST`。

## URI

`getUri()` / `setUri()` 负责读写请求地址。URI 可以在请求类中直接声明，也可以由客户端在 `before()` 中补齐。

server 中两类方式都存在：

- 微信支付、腾讯地图、微信小程序请求类通常直接声明完整 URI。
- 阿里云 RPC 客户端在 `before()` 中把 endpoint 写入 URI。
- OSS 客户端把请求对象里的 object key 拼到 endpoint 后。

## Headers

Header 使用字符串数组，格式遵循 `helper-fetch\Client::addHeader()` 接收的形式：

```php
[
    'Content-Type: application/json',
    'Accept: application/json',
]
```

相关方法：

- `getHeaders(): array`
- `setHeaders(array $headers): static`
- `pushHeader(string $header): static`

`pushHeader()` 只追加完整 header 行，不做键值解析。

## Arguments

Arguments 表示请求主体或 GET 参数的一部分，类型为 `mixed`。默认是空数组。

相关方法：

- `getArguments(): mixed`
- `setArguments(mixed $arguments): static`
- `pushArgument(string $key, mixed $value): static`

默认 `HRBClient` 会把 arguments 交给 `helper-fetch\Client::setData()`：

- GET：数组或对象会被 `http_build_query()` 后追加到 URL。
- POST：原样作为 `CURLOPT_POSTFIELDS`。
- HEAD：由 `helper-fetch` 打开 header 返回。

如果第三方接口要求 JSON 或 form-urlencoded，应该在客户端 `before()` 中把 arguments 转成对应字符串。

## Query

Query 表示总是拼在 URI 查询串上的参数。相关方法：

- `getQuery(): array`
- `setQuery(array $query): static`
- `pushQuery(string $key, string $value): static`

默认客户端会先合并 query，再通过 `http_build_query()` 拼到 URI 上。对于 GET 请求，如果 arguments 也非空，底层 `helper-fetch` 还会继续把 arguments 追加到 URL。

开发新请求时应明确区分：

- 需要参与签名或固定放在 URL 上的参数，通常放 query。
- 业务请求参数，通常放 arguments。
- 如果某个平台签名要求把 URL 原有 query 和对象 query 合并，应在客户端 `before()` 中自行处理。

## callback()

```php
public function callback(mixed $result): mixed
{
    return $result;
}
```

`callback()` 是请求对象级的最终结果处理点。默认透传 `after()` 的结果。请求类可以覆盖它，把平台返回转换成调用方需要的结构。

server 中微信支付 JSAPI 请求会在 callback 中把 `prepay_id` 组装成前端支付参数，并使用客户端在 `before()` 中注入的 `apiclient_key` 签名。

## 写请求类的建议

- URI、method、headers 使用属性默认值表达。
- 业务参数用命名 setter 写入 `$arguments` 或 `$query`。
- 请求体编码不要放在请求类中，优先放到客户端 `before()`，这样同平台请求可以复用。
- 结果结构只属于某个请求时再覆盖 `callback()`。
