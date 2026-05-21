# 执行链路

本页描述 `HRBClient::execute()` 的内部顺序。理解这条链路可以判断参数最终在哪里合并、哪里编码、哪里返回。

## 主流程

```text
调用 execute($request)
  -> 读取并大写 method
  -> 合并 headers / arguments / query
  -> before($request)
  -> buildUrlWithQuery($request->getUri(), $request->getQuery())
  -> helper-fetch Client
       -> setUrl($uri)
       -> addHeader($headers)
       -> setMethod($method)
       -> setData($arguments)
       -> execute()
  -> after($response)
  -> $request->callback($result)
  -> 返回 callback 结果
```

## 参数合并

`HRBClient` 先把客户端级默认值合并到请求对象里：

```php
$request->setHeaders([...$this->headers, ...$request->getHeaders()])
    ->setArguments([...$this->arguments, ...$request->getArguments()])
    ->setQuery([...$this->query, ...$request->getQuery()]);
```

开发时要注意：

- 请求级字段在后面展开，同名字符串键会覆盖客户端默认值。
- 数字键 header 会追加并重排数字索引。
- 如果 `$arguments` 被请求类设置成字符串，客户端默认 `$arguments` 又是数组，默认合并会不适用；这类请求应在 `before()` 中完成字符串化，而不是在请求类初始状态就把 arguments 设成字符串。

## before 的位置

`before()` 在合并之后、拼接 query 之前执行。因此它可以读取最终合并后的 headers、arguments、query，也可以继续修改 URI、headers、arguments、query。

常见用途：

- 签名时读取最终 query 和 arguments。
- POST JSON 编码。
- form-urlencoded 编码。
- 补齐 endpoint。
- 写入 callback 需要的临时属性。

## Query 与 Arguments 的 URL 行为

默认执行管线里有两次可能追加 URL 参数：

1. `HRBClient::buildUrlWithQuery()` 会把 `$request->getQuery()` 拼到 URI。
2. `helper-fetch\Client::setData()` 在 GET 方法下会把 `$request->getArguments()` 再拼到 URL。

因此 GET 请求中 query 和 arguments 都可能进入最终 URL。区别在于：

- query 在进入 `helper-fetch` 前已经拼到 URI。
- arguments 由 `helper-fetch` 根据 GET 方法追加。

如果第三方平台签名需要完整 URL 参数，客户端应在签名前明确合并 URI 原有 query、请求 query 和 arguments。

## Response 与 callback

默认 `after()` 返回 `Response::getJsonBody()`：

- JSON body 有效时返回 `json_decode()` 的结果。
- 默认 `$toArray=false`，所以返回对象。
- body 为空或不是有效 JSON 时返回 `null`。

然后 `callback()` 接收这个结果。默认 callback 透传；请求类覆盖 callback 后，可以把平台返回转换成更适合调用方使用的结构。

## 非 JSON 响应

如果第三方接口返回二进制、XML、纯文本、响应头或状态码，默认 `after()` 不够用。可选路径：

- 覆盖 `after(Response $response)`，读取 `getRaw()`、`getBody()`、`getXmlBody()` 等。
- 覆盖 `execute()`，直接操作 cURL 或返回自定义响应对象。

当前对象存储类客户端采用覆盖 `execute()` 的方式，因为它们需要状态码和响应头。
