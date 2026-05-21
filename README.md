# superpms/helper-hrb

`superpms/helper-hrb` 是 server 端 kits 用来封装第三方 HTTP 请求的开发者工具包。它不直接提供某个业务接口，而是在 `superpms/helper-fetch` 之上提供请求客户端、请求对象和选项对象三类基础结构，让具体 kit 通过继承完成鉴权、签名、参数序列化和结果回调。

## 安装与依赖

```bash
composer require superpms/helper-hrb
```

包内 `composer.json` 确认的运行要求：

- PHP `>=8.1`
- `superpms/helper-fetch` `^1.0.0`
- PSR-4 自动加载：`pms\\` => `src/pms/`
- 核心命名空间：`pms\helper\hrb`

server 端当前通过 `server/composer.json` 引入 `superpms/helper-hrb`，实际使用点集中在 `server/kits/*/hrb` 下。

## 快速开始

最小使用方式是定义一个请求类继承 `HRBRequest`，再定义一个客户端类继承 `HRBClient`：

```php
<?php

use pms\helper\fetch\Response;
use pms\helper\hrb\HRBClient;
use pms\helper\hrb\HRBRequest;
use pms\helper\hrb\HRBRequestInterface;

class ExampleRequest extends HRBRequest
{
    protected string $uri = 'https://example.com/api';
    protected string $method = 'GET';

    public function setKeyword(string $keyword): static
    {
        return $this->pushArgument('keyword', $keyword);
    }
}

class ExampleClient extends HRBClient
{
    protected array $headers = [
        'Accept: application/json',
    ];

    protected function before(HRBRequestInterface $request): HRBRequestInterface
    {
        return $request->pushQuery('token', 'example-token');
    }

    protected function after(Response $response): mixed
    {
        return $response->getJsonBody();
    }
}

$result = (new ExampleClient())->execute(
    (new ExampleRequest())->setKeyword('demo')
);
```

`HRBClient::execute()` 会合并客户端级和请求级的 `headers`、`arguments`、`query`，调用 `before()`，通过 `helper-fetch\Client` 发起请求，再调用 `after()` 和请求对象自己的 `callback()`。

## 核心模块

- `HRBClientInterface`：定义统一执行入口 `execute(HRBRequestInterface $request): mixed`。
- `HRBClient`：默认执行管线，负责合并参数、执行前后钩子、调用 `helper-fetch`。
- `HRBRequestInterface`：定义请求对象需要暴露的 method、uri、headers、arguments、query 和 callback。
- `HRBRequest`：请求对象基类，提供 setter、push 方法和默认透传 callback。
- `HRBOptions`：动态选项基类，用 `__get()` / `__set()` 维护 `$data`，读取缺失属性时抛出必填异常。

## 文档索引

- [docs/README.md](docs/README.md)：文档入口与阅读路径。
- [docs/getting-started/01-package-contract.md](docs/getting-started/01-package-contract.md)：包定位、安装、边界和 server 当前接入事实。
- [docs/core/01-client.md](docs/core/01-client.md)：`HRBClient` 与执行管线。
- [docs/core/02-request.md](docs/core/02-request.md)：`HRBRequest` 与请求对象写法。
- [docs/core/03-options.md](docs/core/03-options.md)：`HRBOptions` 与动态选项对象。
- [docs/modules/01-server-kit-patterns.md](docs/modules/01-server-kit-patterns.md)：server kits 中已确认的继承与扩展模式。
- [docs/internals/01-execution-flow.md](docs/internals/01-execution-flow.md)：内部执行顺序、参数合并和返回处理。
- [docs/reference/01-api-reference.md](docs/reference/01-api-reference.md)：公开类、方法和返回结构速查。

## 边界说明

这个包只描述 composer 包层面的开发契约，不承载业务端页面、运营流程或具体业务功能文档。具体第三方平台的接口字段、业务校验和场景编排应留在对应 server kit 中；如果要修改底层 cURL 行为，应先查看 `superpms/helper-fetch`。
