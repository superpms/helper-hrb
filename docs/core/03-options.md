# HRBOptions

`HRBOptions` 是动态选项对象基类，文件位于 `src/pms/helper/hrb/HRBOptions.php`。它只维护一个受保护的 `$data` 数组，并通过魔术方法读写属性。

## 数据结构

```php
abstract class HRBOptions
{
    protected array $data = [];
}
```

继承类可以直接声明默认值：

```php
class DeviceAddOptions extends HRBOptions
{
    protected array $data = [
        'phone_4g' => '',
        'type' => 1,
    ];
}
```

## 写入属性

```php
public function __set(string $name, $value)
{
    $this->data[$name] = $value;
}
```

调用方可以像普通对象属性一样写入：

```php
$options = new DeviceAddOptions();
$options->sn = 'printer-sn';
$options->name = 'front-desk';
```

## 读取属性

```php
public function __get(string $name)
{
    return $this->data[$name] ?? throw new \Exception(static::class . ":$name 属性为必填项");
}
```

读取缺失属性会抛出异常，异常信息包含当前 options 类名和缺失属性名。这是当前包内唯一显式的必填属性保护。

## server 当前使用方式

server 中继承 `HRBOptions` 的 options 主要集中在：

- 微信支付 options：`BaseOptions`、`NotifyOptions`、`PaymentOptions`、`PaymentCombineOptions`、`RefundOptions`。
- 飞鹅云 options：`BaseOptions`、`DeviceAddOptions`、`DeviceUpdateOptions`。

这些类通常用 `@property` 标注可用字段，把实际字段值保存在 `$data` 中。部分 options 类还提供业务化方法，例如 `PaymentCombineOptions::pushSubOrders()` 会向 `$data['sub_orders']` 追加子单结构。

## 注意事项

- `HRBOptions` 不提供构造函数、不做类型校验、不做字段白名单限制。
- 必填判断发生在读取时，不发生在写入时。
- 如果要让 IDE 知道动态属性，应在继承类上维护准确的 `@property` 注释。
- 如果某个 options 需要复杂结构，优先在继承类中提供命名方法写入 `$data`。
