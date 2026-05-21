# 包契约与使用边界

## 包定位

`superpms/helper-hrb` 是一个请求封装基础包。它把“客户端级公共配置”和“单次请求对象”拆开，让具体 server kit 在客户端里处理签名、公共参数、请求体编码和响应解析，在请求类里描述 URI、HTTP 方法和接口参数。

当前包本身只包含 5 个源码文件：

- `src/pms/helper/hrb/HRBClientInterface.php`
- `src/pms/helper/hrb/HRBClient.php`
- `src/pms/helper/hrb/HRBRequestInterface.php`
- `src/pms/helper/hrb/HRBRequest.php`
- `src/pms/helper/hrb/HRBOptions.php`

## Composer 信息

包内 `composer.json` 的事实：

- 包名：`superpms/helper-hrb`
- license：`Apache-2.0`
- PHP 版本：`>=8.1`
- 依赖：`superpms/helper-fetch` `^1.0.0`
- autoload：`pms\\` 映射到 `src/pms/`
- 命名空间：`pms\helper\hrb`

server 端当前在 `server/composer.json` 中依赖 `superpms/helper-hrb`，版本约束为 `^1.0.0`。

## 与 helper-fetch 的关系

`HRBClient` 默认不会自己操作 cURL，而是在 `execute()` 内创建 `pms\helper\fetch\Client`：

```php
$httpClient = new Client();

$res = $httpClient->setUrl($uri)
    ->addHeader($request->getHeaders())
    ->setMethod($method)
    ->setData($request->getArguments())
    ->execute();
```

因此：

- HTTP 连接、超时、Header 传入、GET/POST/HEAD 数据发送由 `helper-fetch` 负责。
- `helper-hrb` 负责请求对象抽象、公共参数合并、前后处理钩子和请求级回调。
- 如果要改 cURL 默认行为，应回到 `helper-fetch`；如果要改某个 kit 的签名或参数组织，应改该 kit 的 `RequestClient` 或请求类。

## 使用边界

这个包适合沉淀这些通用契约：

- 请求客户端统一入口。
- 请求对象的 URI、Method、Header、Arguments、Query 访问协议。
- 客户端级默认 headers、arguments、query 与请求级字段的合并规则。
- `before()`、`after()`、`callback()` 三段式扩展点。
- 简单动态 options 对象。

这个包不适合沉淀这些内容：

- 业务端页面、按钮、运营流程。
- 某个第三方平台的完整业务接口说明。
- 框架启动生命周期、服务注册或路由挂载逻辑。
- cURL 底层选项的全局策略。

## server 当前接入事实

当前 server 端实际使用 `pms\helper\hrb` 的位置集中在 `server/kits/*/hrb`：

- `kits/alibaba/aliyun/hrb`
- `kits/cloudflare/r2/hrb`
- `kits/payment/wechat/hrb`
- `kits/print/feieyun/hrb`
- `kits/tencent/map/hrb`
- `kits/tencent/weApp/hrb`

这些 kit 主要通过继承 `HRBClient`、`HRBRequest`、`HRBOptions` 来实现各自平台的请求适配。包文档只描述这些继承模式，不展开平台业务流程。
