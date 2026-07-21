# 对外契约

## 管理后台路由（`src/Http/Routes/backend.php`）

> 路由组前缀：`{mgr-page}/sensitive-word`，其中 `{mgr-page}` 取 `config('poppy.framework.prefix')`，未配置时默认为 `mgr-page`。  
> 中间件：`backend-auth`。  
> 命名空间：`Poppy\SensitiveWord\Http\Request\Backend`。  
> 三条路由均使用 `Router::any()`，因此路由层接受所有 HTTP 方法；表单组件只在 `POST` 时执行保存。

| HTTP 方法 | URI | 路由名 | 控制器方法 | 用途 |
|---|---|---|---|---|
| `*（any）` | `{mgr-page}/sensitive-word/` | `py-sensitive-word:backend.word.index` | `WordController@index` | MgrPage 敏感词列表、筛选及批量操作入口 |
| `*（any）` | `{mgr-page}/sensitive-word/establish/{id?}` | `py-sensitive-word:backend.word.establish` | `WordController@establish` | GET 渲染多行新增表单；POST 新增一个或多个词 |
| `*（any）` | `{mgr-page}/sensitive-word/delete/{id?}` | `py-sensitive-word:backend.word.delete` | `WordController@delete` | 删除路径 ID、请求参数 ID 或 ID 数组 |

默认配置下的 URI 分别为 `mgr-page/sensitive-word/`、`mgr-page/sensitive-word/establish/{id?}` 和 `mgr-page/sensitive-word/delete/{id?}`。

模块没有 API 路由、普通 Web 路由或 OpenAPI Schema。

## 后台请求契约

### 列表：`py-sensitive-word:backend.word.index`

| 参数 | 位置 | 类型 | 必填 | 规则/作用 |
|---|---|---|---|---|
| `word` | Query | string | 否 | `ListSysSensitiveWord::filter()` 使用 `like` 筛选敏感词 |
| MgrPage 分页/排序参数 | Query | 由 `Grid` 定义 | 否 | 列表分页和 `id` 排序；本模块未重新定义字段名 |

响应是 `Grid(new SysSensitiveWord())` 配合 `ListSysSensitiveWord` 生成的 MgrPage 页面/PJAX/表格响应；列固定为 `id`、`word` 和操作。它不是面向外部调用方的稳定 JSON 资源契约。

### 新增：`py-sensitive-word:backend.word.establish`

请求体（HTML Form / AJAX）：

```text
word=词条一\n词条二\n词条三
```

| 字段 | 类型 | 必填 | Action 校验 | 后续转换 |
|---|---|---|---|---|
| `word` | string | 是 | `Rule::required()`、`Rule::string()` | 整体 `trim` → 按 `PHP_EOL` 分行 → `array_filter` 去空行 → 排除数据库已有值 |

- GET：返回 `FormSensWordEstablish` 渲染的 MgrPage 表单，字段是 textarea `word`，帮助文案为“一行一个”。
- POST 成功（AJAX）：HTTP 200，响应由 `Resp::success('操作成功', '_top_reload|1')` 生成。

```json
{
  "status": 0,
  "message": "操作成功",
  "data": {
    "_top_reload": "1"
  }
}
```

- Action 验证失败时 `status=5`；没有可新增词或全部重复时 `status=1`。AJAX 错误同样以 HTTP 200 返回：

```json
{
  "status": 1,
  "message": "没有需要添加的敏感词, 输入内容存在重复数据"
}
```

非 AJAX 请求由 `Resp` 返回宿主项目的消息页面/重定向响应，不保证上述 JSON 形状。

### 删除：`py-sensitive-word:backend.word.delete`

ID 的解析优先级为 `input('id', (array) $id)`：请求参数 `id` 存在时使用请求值，否则将可选路径参数转成数组。

| 参数 | 位置 | 类型 | 必填 | 说明 |
|---|---|---|---|---|
| `id` | Path | int | 否 | 单条删除；省略时可从请求参数取得 |
| `id` | Query/Form | int 或 int[] | 否 | 单条或 MgrPage 批量删除 |

成功 AJAX 响应：

```json
{
  "status": 0,
  "message": "删除成功",
  "data": {
    "_reload": "1"
  }
}
```

删除异常由 `Action\Word::delete()` 转为 `Resp` 错误；AJAX 返回 `status=1` 及异常消息。删除不存在的 ID 不报错，Eloquent `delete()` 影响 0 行仍返回成功。

## PHP 运行时契约

### 全局函数 `sensitive_words()`

模块通过 Composer `autoload.files` 自动加载 `src/Support/functions.php`，因此调用方无需导入类即可调用：

```php
sensitive_words(string $words, string $action = Words::TYPE_CHECK)
```

| `$action` 常量 | 值 | 返回类型 | 返回语义 |
|---|---|---|---|
| `Poppy\SensitiveWord\Classes\Sensitive\Words::TYPE_CHECK` | `check` | bool | 未命中返回 `true`；命中返回 `false` |
| `Words::TYPE_WORDS` | `words` | array | 返回本次扫描记录的敏感词数组 |
| `Words::TYPE_REPLACE` | `replace` | string（无目录分支代码可返回 array） | 将命中词按字符长度替换为 `*` 后返回文本 |

异常契约：共享缓存没有字典时，`Dict::getDirectory()` 会从 `sys_sensitive_word` 全表构建；若表内无词，`Words::setTree()` 抛出 `Poppy\SensitiveWord\Exceptions\DirectoryNotFoundException('词库不存在')`。全局函数不捕获该异常。

未知 `$action` 最终落入 `TYPE_CHECK` 的返回分支；调用方仍应只传三个公开常量，避免依赖这一兜底行为。

### 核心公开类方法

| 类 | 方法 | 输入 | 输出/副作用 |
|---|---|---|---|
| `Poppy\SensitiveWord\Action\Word` | `establish(array $data): bool` | `['word' => string]` | 批量插入新词并删除 `dict` 缓存；失败信息从 `getError()` 读取 |
| `Poppy\SensitiveWord\Action\Word` | `delete(array|int $id): bool` | 单 ID 或 ID 数组 | 物理删除并删除 `dict` 缓存 |
| `Poppy\SensitiveWord\Classes\Sensitive\Dict` | `getDirectory(): ?Words` | 无 | 缓存命中返回 `Words`；未命中时构建 |
| `Poppy\SensitiveWord\Classes\Sensitive\Dict` | `build(): ?Words` | 无 | 全表加载词库、构建 Trie、写入缓存 |
| `Poppy\SensitiveWord\Classes\Sensitive\Words` | `setTree(array $data): self` | 词字符串数组 | 构建/扩充 Trie；空数组抛 `DirectoryNotFoundException` |
| `Words` | `illegal(string $content): bool` | UTF-8 文本 | 是否命中；结果同时影响对象内命中词状态 |
| `Words` | `setSearchAllIllegal(bool $searchAllIllegal): self` | 是否扫描全部 | 控制短路或收集模式 |
| `Words` | `getIllegalWords(): array` | 无 | 返回对象已记录的命中词 |
| `Words` | `replaceIllegalWords()` | 无 | 根据已记录命中词替换上一次 `illegal()` 的内容 |

## 数据库契约

迁移 `2021_04_25_233311_create_sys_sensitive_word_table.php` 创建：

| 表 | 字段 | 类型/约束 | 说明 |
|---|---|---|---|
| `sys_sensitive_word` | `id` | `bigIncrements` | 主键 |
| `sys_sensitive_word` | `word` | `varchar(50)`, default `''` | 敏感词文本；迁移未声明唯一索引 |

`SysSensitiveWord::$timestamps = false`，可批量赋值字段仅为 `word`，无 Eloquent 关联、Scope、Accessor 或 Mutator。

## 缓存契约

| Tag | Key | 值 | 读取/写入方 | 失效方 |
|---|---|---|---|---|
| `py-sensitive-word` | `dict`（`PySensitiveWordDef::ckDict()`） | 序列化的 `Words` Trie 对象 | `Dict::getDirectory()` / `Dict::build()` | `Action\Word::establish()`、`Action\Word::delete()` |

`InitCommand` 不删除该缓存；`sensitive_words()` 还在 PHP 进程内通过静态变量保存一次取得的 `Words` 实例。

## 发布/监听的事件

| 类型 | 类 | 说明 |
|---|---|---|
| 发布事件 | — | 模块没有 `Events/`，Action/Command 未调用 `event()` |
| 监听器 | — | `Listeners/` 仅有 `.gitkeep`，`ServiceProvider` 未注册监听器 |
| 队列任务 | — | 模块没有 `Jobs/`，未调用 `dispatch()` |

## Artisan 命令

| 命令签名 | 类 | 输入源 | 输出/副作用 | 调度方式 |
|---|---|---|---|---|
| `py-sensitive-word:init` | `Poppy\SensitiveWord\Commands\InitCommand` | `resources/def/words.txt` | 读取、原始行去重、逐行 trim、批量插入 `sys_sensitive_word` | `ServiceProvider` 注册，手动执行；未发现 schedule 注册 |

命令以文件第一行是否已存在作为“已导入”判定；成功输出 `Init Sensitive Word Data Success`，已导入时输出 `你已经导入了默认数据, 无需重新导入`，异常被捕获并通过 Console `error()` 输出。

## 跨模块调用（本模块调用其他模块）

| 本模块调用方 | 目标模块 | 目标类/能力 | 调用方法 | 场景 |
|---|---|---|---|---|
| `ServiceProvider`、`Action\Word`、`WordController` | `poppy/framework` | `PoppyServiceProvider`、`AppTrait`、`Rule`、`Resp` | 模块启动、参数校验、错误/响应封装 | 基础运行契约 |
| `Dict`、`Action\Word`、测试 | `poppy/core` | 全局 `sys_tag()` / `RdsDb` | `get/set/del/clear` | Trie 共享缓存 |
| `WordController`、`FormSensWordEstablish`、`ListSysSensitiveWord` | `poppy/mgr-page` | `Grid`、`FormWidget`、`ListBase`、`Operations` 等 | `render()`、表单/列表/批量操作 | 管理后台 UI |
| `Action\Word` | `poppy/system` | `Poppy\System\Classes\Traits\PamTrait` | `setPam()` | 接收后台账号上下文 |

## 被其他模块调用（本模块被引用）

对 `poppy/*/src/` 扫描 `Poppy\SensitiveWord\` 和 `sensitive_words(` 后，未发现其他 Poppy 模块直接消费本模块类或全局函数。

| 调用方 | 本模块目标 | 调用方式 | 场景 |
|---|---|---|---|
| `modules/demo` 的 `Demo\Http\Lists\ListGridOperation` | 后台删除路由 | `route_url('py-sensitive-word:backend.word.delete')` | Demo Grid 的 `batchDelete()` 示例 |
| 本模块 `configurations/menus.yaml` | 后台列表路由 | `py-sensitive-word:backend.word.index` | MgrPage 菜单导航 |

## 配置与资源契约

- `configurations/menus.yaml`：注入“敏感词管理”菜单，路由名保持 `py-sensitive-word:backend.word.index`，声明权限 `backend:py-sensitive-word.main.manage`。
- `configurations/permissions.yaml`：声明 `backend:py-sensitive-word` → `main.manage` 权限。
- `resources/def/words.txt`：`py-sensitive-word:init` 的默认导入源，当前 5,705 行。
- `resources/lang/zh/seo.php`：定义 `backend_word_index`、`backend_word_establish`、`backend_word_delete` 后台标题。

## 待确认

- `establish/{id?}` 的可选 ID 未被控制器或 Action 使用，是否应作为正式编辑契约的一部分？
- 三条后台路由均使用 `any()`；除表单 POST 外是否需要收紧 HTTP 方法并补充 CSRF/权限层面的明确契约？
- `backend:py-sensitive-word.main.manage` 出现在菜单/权限配置中，但路由组只显式声明 `backend-auth`；该权限是否由 MgrPage 导航层统一执行？
- `modules/demo` 的批量删除直接指向本模块生产路由，可能删除真实敏感词；需确认这是有意的集成示例还是复制遗留。
- 当前没有其他 Poppy 模块调用 `sensitive_words()`；需确认过滤能力是否尚未接入业务，或调用发生在扫描范围外的应用代码/动态 Hook 中。
