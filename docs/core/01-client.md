# HRBClient

`HRBClient` 是请求执行管线的默认实现，文件位于 `src/pms/helper/hrb/HRBClient.php`。它实现 `HRBClientInterface`，对外公开的核心方法是 `execute(HRBRequestInterface $request): mixed`。

## 属性

`HRBClient` 提供三个客户端级默认字段：

```php
protected array $arguments = [];
protected array $headers = [];
protected array $query = [];
```

继承类可以用它们保存平台级公共参数。例如 server 中已存在这些模式：

- 腾讯地图客户端在构造函数里把 `key` 写入 `$arguments`。
- 微信小程序客户端把 `access_token` 写入 `$query`。
- 飞鹅云客户端把 `user`、`stime`、`sig` 写入 `$arguments`。
- 阿里云客户端把安全 token 写入 `$headers`。

## execute()

默认执行顺序：

1. 从请求对象读取 HTTP method，并转为大写。
2. 合并客户端级和请求级 `headers`。
3. 合并客户端级和请求级 `arguments`。
4. 合并客户端级和请求级 `query`。
5. 调用 `before($request)`。
6. 把 query 拼到 URI 上。
7. 用 `pms\helper\fetch\Client` 发送请求。
8. 调用 `after($response)`。
9. 调用请求对象的 `callback($result)`。
10. 返回 callback 的结果。

合并使用 PHP 数组展开：

```php
[...$this->headers, ...$request->getHeaders()]
[...$this->arguments, ...$request->getArguments()]
[...$this->query, ...$request->getQuery()]
```

后展开的请求级字段会覆盖相同字符串键；数字键数组会按 PHP 数组展开规则重新追加。

## before()

```php
protected function before(HRBRequestInterface $request): HRBRequestInterface
```

默认直接返回原请求。继承类通常在这里做：

- 签名。
- 追加公共 query。
- 把相对 URI 改成完整 URI。
- 把数组参数转成 JSON 字符串。
- 把数组参数转成 `application/x-www-form-urlencoded` 字符串。
- 向请求对象注入 callback 需要的临时属性。

server 现有例子：

- `kits/payment/wechat/hrb/RequestClient.php` 在 `before()` 中签名，并在 POST 时 JSON 编码请求体。
- `kits/alibaba/aliyun/hrb/RequestClient.php` 在 `before()` 中设置 endpoint 并执行 ACS 签名。
- `kits/print/feieyun/hrb/RequestClient.php` 在 `before()` 中把 POST 参数转成 query string。

## after()

```php
protected function after(Response $response): mixed
```

默认返回：

```php
$response->getJsonBody();
```

这意味着默认管线假设响应体是 JSON。继承类可以覆盖 `after()` 处理非 JSON 返回、读取原始响应、读取响应头或返回自定义对象。

server 中 OSS 和 R2 客户端为了拿到状态码、响应头和原始 body，直接覆盖了 `execute()`，没有使用默认 `after()` 返回 JSON 的路径。

## 什么时候覆盖 execute()

优先覆盖 `before()` 和 `after()`。只有默认 `helper-fetch` 管线无法表达时，再覆盖 `execute()`。

当前 server 中覆盖 `execute()` 的场景是对象存储类请求，需要：

- 保留响应头。
- 支持 HEAD 请求的无 body 语义。
- 返回自定义响应对象。
- 直接读取 HTTP 状态码。

覆盖 `execute()` 时仍建议保留 `HRBRequestInterface` 作为入参，以维持请求对象契约一致。
