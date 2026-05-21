# server kit 使用模式

本页只记录 server 端当前已经确认的 helper-hrb 使用模式，帮助开发者理解这个 composer 包在真实代码里的扩展方式。不展开具体业务端功能。

## 当前使用范围

server 端当前 `pms\helper\hrb` 使用点集中在这些 kit：

- `kits/alibaba/aliyun/hrb`
- `kits/cloudflare/r2/hrb`
- `kits/payment/wechat/hrb`
- `kits/print/feieyun/hrb`
- `kits/tencent/map/hrb`
- `kits/tencent/weApp/hrb`

已确认的结构：

- 当前 server 端可见的 `HRBClient` 继承点包括：
  - `kits/alibaba/aliyun/hrb/RequestClient.php`
  - `kits/alibaba/aliyun/hrb/OssRequestClient.php`
  - `kits/cloudflare/r2/hrb/RequestClient.php`
  - `kits/payment/wechat/hrb/RequestClient.php`
  - `kits/print/feieyun/hrb/RequestClient.php`
  - `kits/tencent/map/hrb/RequestClient.php`
  - `kits/tencent/weApp/hrb/RequestClient.php`
- 31 个请求类继承 `HRBRequest`。
- 支付和打印相关 options 继承 `HRBOptions`。

## 客户端模式

### JSON API 客户端

腾讯地图、微信小程序、微信支付等客户端通常：

- 设置 `Content-Type` / `Accept` Header。
- 在构造函数中保存 token、key、商户号等公共参数。
- 在 `before()` 中签名或把 POST arguments 编码成 JSON。
- 在 `after()` 中返回 `Response::getJsonBody()`。

这种模式适合返回 JSON 的平台 API。

### form-urlencoded 客户端

飞鹅云打印客户端当前：

- 默认 Header 为 `Content-Type: application/x-www-form-urlencoded`。
- 构造函数写入 `user`、`stime`、`sig` 公共参数。
- `before()` 中把 POST arguments 转成 `http_build_query()` 字符串。

这种模式适合要求表单编码的第三方接口。

### 签名客户端

微信支付和阿里云客户端把签名逻辑放在客户端层，而不是请求类层：

- 微信支付根据 method、URI path、query、body、timestamp、nonce 生成 Authorization Header。
- 阿里云 RPC 根据 headers、query、payload hash 生成 ACS Authorization Header。

签名放在客户端层的好处是同一平台的请求类只关注接口参数，签名规则集中维护。

### 对象存储客户端

阿里云 OSS 和 Cloudflare R2 当前覆盖了 `execute()`，没有走默认 `helper-fetch` JSON 返回链路。原因是对象存储请求需要直接处理：

- HTTP 状态码。
- 原始响应头。
- 原始 body。
- HEAD 请求。
- 自定义响应对象。

这说明 `HRBClientInterface` 的关键价值是执行入口契约，默认 `HRBClient::execute()` 不是所有请求类型的强制路径。

## 请求类模式

请求类通常继承 `HRBRequest` 并负责：

- 声明 `$uri`。
- 声明 `$method`。
- 声明默认 `$headers` 或 `$arguments`。
- 提供语义化 setter，把入参写入 `$arguments` 或 `$query`。
- 必要时覆盖 `callback()`。

server 中常见写法：

```php
class RefundRequest extends HRBRequest
{
    protected string $method = 'POST';
    protected string $uri = 'https://api.mch.weixin.qq.com/v3/refund/domestic/refunds';

    public function setOutRefundNo(string $outRefundNo): self
    {
        $this->arguments['out_refund_no'] = $outRefundNo;
        return $this;
    }
}
```

## options 模式

`HRBOptions` 用于承载一组动态配置，当前主要被请求类或静态校验方法读取。典型模式：

- 继承类用 `@property` 声明字段。
- 通过动态属性写入值。
- 在读取缺失字段时由 `HRBOptions::__get()` 抛出必填异常。
- 复杂数组用命名方法构造。

## 新增 kit 的建议

新增使用 helper-hrb 的 server kit 时，建议按这个顺序写：

1. 先定义请求类，确认每个接口的 URI、method、headers、arguments、query。
2. 再定义客户端，集中处理公共参数、签名、请求体编码和响应解析。
3. 如果多个请求共享配置对象，再定义 options。
4. 如果默认 `execute()` 无法满足响应处理，再覆盖 `execute()`，但保持入参为 `HRBRequestInterface`。

不要把某个业务端的页面状态、运营动作或业务编排写进 helper-hrb 包文档或包源码。
