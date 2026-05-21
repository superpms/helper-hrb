# helper-hrb 开发文档

本目录是 `superpms/helper-hrb` 的包级开发文档入口。文档基于当前包源码和 server 端 `server/kits/*/hrb` 实际使用方式整理，只说明 composer 包面向开发者的结构、使用方式和扩展边界。

## 先读什么

1. 新接手这个包：读 [getting-started/01-package-contract.md](getting-started/01-package-contract.md)。
2. 要写新的请求客户端：读 [core/01-client.md](core/01-client.md) 和 [core/02-request.md](core/02-request.md)。
3. 要理解 server 里已有用法：读 [modules/01-server-kit-patterns.md](modules/01-server-kit-patterns.md)。
4. 要查方法签名：读 [reference/01-api-reference.md](reference/01-api-reference.md)。

## 按问题读

- 包的定位、安装和边界：[getting-started/01-package-contract.md](getting-started/01-package-contract.md)
- 客户端扩展点、`before()` / `after()`：[core/01-client.md](core/01-client.md)
- 请求对象字段、参数追加、`callback()`：[core/02-request.md](core/02-request.md)
- 动态 options 的读写和必填属性行为：[core/03-options.md](core/03-options.md)
- server 现有 kit 如何继承和覆盖：[modules/01-server-kit-patterns.md](modules/01-server-kit-patterns.md)
- `execute()` 内部顺序、query 构造、返回链路：[internals/01-execution-flow.md](internals/01-execution-flow.md)
- 类、方法、默认返回值速查：[reference/01-api-reference.md](reference/01-api-reference.md)

## 不在这里读

- 不记录业务端页面、运营动作或用户流程。
- 不展开每个第三方平台的完整接口字段；这些属于各自 server kit 的请求类。
- 不记录 `helper-fetch` 的底层 cURL 选项细节；这里只说明 helper-hrb 怎样调用它。
- 不把 `.memory` 参考资料复制进包文档；本目录以当前代码事实为准。
