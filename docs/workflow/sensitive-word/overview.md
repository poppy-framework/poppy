# 敏感词（`Poppy\SensitiveWord\`）

## 模块职责

敏感词模块负责维护 `sys_sensitive_word` 词库、将词库构造成 UTF-8 字符字典树并缓存，以及通过全局函数 `sensitive_words()` 提供命中判断、命中词提取和星号替换三种同步过滤能力；管理入口位于 Poppy MgrPage 后台。

算法实现是基于 `HashMap` 节点的 Trie（字典树）前缀扫描：`Words::buildTree()` 逐字符建树，`Words::illegal()` 从文本位置向下匹配。代码没有失败指针或回退表，因此不是 Aho-Corasick（AC）自动机。

## 目录结构

```text
poppy/sensitive-word/
├── configurations/
│   ├── menus.yaml                 # 后台菜单
│   └── permissions.yaml           # 后台权限声明
├── resources/
│   ├── def/words.txt              # 默认词库（5,705 行）
│   ├── lang/zh/                   # 后台 SEO、Action、模型文案
│   ├── migrations/                # sys_sensitive_word 建表迁移
│   └── views/backend/content/     # 历史 Blade 模板（当前 MgrPage 未直接引用）
├── src/
│   ├── Action/Word.php            # 批量新增、批量/单条删除与缓存失效
│   ├── Classes/
│   │   ├── Contracts/HashMapContract.php
│   │   ├── PySensitiveWordDef.php # 缓存键定义
│   │   └── Sensitive/
│   │       ├── Dict.php           # 数据库词库加载及缓存
│   │       ├── HashMap.php        # Trie 节点容器
│   │       └── Words.php          # 建树、扫描、命中词提取、替换
│   ├── Commands/InitCommand.php   # 默认词库导入命令
│   ├── Exceptions/DirectoryNotFoundException.php
│   ├── Http/
│   │   ├── MgrPage/               # 后台表单与列表定义
│   │   ├── Request/Backend/WordController.php
│   │   ├── Routes/backend.php
│   │   └── RouteServiceProvider.php
│   ├── Models/SysSensitiveWord.php
│   ├── ServiceProvider.php
│   └── Support/functions.php      # sensitive_words() 全局函数
└── tests/WordsTest.php
```

## 分层清单

| 目录/层级 | 职责 | PHP 文件数 |
|---|---|---:|
| `Action` | 词库写操作与缓存失效 | 1 |
| `Models` | Eloquent 敏感词模型 | 1 |
| `Classes` | Trie、字典加载、HashMap 契约及缓存键 | 5 |
| `Http/Request` | 后台控制器 | 1 |
| `Http/MgrPage` | 后台表单、列表、筛选和批量操作 | 2 |
| `Http/Routes` | 后台路由定义 | 1 |
| `Commands` | 默认词库导入命令 | 1 |
| `Exceptions` | 空词库异常 | 1 |
| `Support` | 全局过滤函数 | 1 |
| `Events` / `Listeners` / `Jobs` / `Hooks` | 未实现（`Listeners` 仅有 `.gitkeep`） | 0 |

## 技术栈

| 技术 | 版本/说明 |
|---|---|
| PHP | `>=7.4`；依赖 `ext-mbstring` 进行 UTF-8 字符扫描 |
| Laravel | 宿主项目 `laravel/framework: 6.*` |
| Poppy | 宿主项目 4.3；`PoppyServiceProvider` 注册模块、路由和命令 |
| ORM | Eloquent；模型 `Poppy\SensitiveWord\Models\SysSensitiveWord` |
| 管理后台 | `poppy/mgr-page` 的 `Grid`、`FormWidget`、`ListBase` |
| 缓存 | `sys_tag('py-sensitive-word')`（Poppy Core `RdsDb` 标签缓存），键为 `dict` |
| 匹配算法 | UTF-8 Trie 前缀扫描；不是 AC 自动机 |
| 队列/事件 | 无；检测、词库写入和导入均同步执行 |

> 模块自身 `composer.json` 只显式声明 PHP 与 `ext-mbstring`；Framework、Core、MgrPage、System 和 Illuminate 类由宿主项目提供。

## 路由概览

| 路由文件 | 类型 | 前缀 | 中间件 | 路由数 | 说明 |
|---|---|---|---|---:|---|
| `backend.php` | 管理后台 | `{mgr-page}/sensitive-word`；默认 `mgr-page/sensitive-word` | `backend-auth` | 3 | 列表、新增、删除，均由 `Router::any()` 注册 |

模块没有 API 或普通 Web 路由。详细路由与路由名见 [contracts.md](contracts.md)。

## 模型清单

| 模型 | 数据表 | 关键字段/关联 | 说明 |
|---|---|---|---|
| `Poppy\SensitiveWord\Models\SysSensitiveWord` | `sys_sensitive_word` | `id`, `word`; 无模型关联；无时间戳 | 一行一个敏感词，`word` 数据库长度为 50 |

## 依赖的其他模块

| 模块 | 引用方式 | 说明 |
|---|---|---|
| `poppy/framework` | `PoppyServiceProvider`、`AppTrait`、`Rule`、`Resp` 等 | 模块注册、校验、错误与响应封装 |
| `poppy/core` | 全局函数 `sys_tag()` | 读取、写入和失效 `py-sensitive-word:dict` 缓存 |
| `poppy/mgr-page` | `Grid`、`FormWidget`、`ListBase`、`Operations` 等 | 后台列表、筛选、表单和批量删除 |
| `poppy/system` | `PamTrait` | 将后台账号上下文注入 `Action\Word`；当前写操作未进一步调用权限方法 |

## 被其他模块依赖

扫描 `poppy/*/src/` 后，未发现其他 Poppy 模块通过 `use Poppy\SensitiveWord\...` 直接引用本模块，也未发现它们调用全局函数 `sensitive_words()`。

`modules/demo/src/Http/Lists/ListGridOperation.php` 通过路由名 `py-sensitive-word:backend.word.delete` 配置了示例表格的批量删除，形成路由级依赖；模块菜单 `configurations/menus.yaml` 则引用 `py-sensitive-word:backend.word.index`。

## 边界说明（不负责的事项）

- 不负责在内容、评论、消息等业务模块中自动接入过滤；调用方需主动调用 `sensitive_words()` 并决定后续动作。
- 不提供审核单、人工复核状态或审核工作流；`words` 模式只返回命中词数组。
- 不提供拼音、同音字、繁简转换、大小写归一、空白/符号跳过或正则模糊匹配。
- 不提供外部 HTTP API、事件、监听器、队列任务或定时任务。
- 不实现 AC 自动机的失败指针、多模式线性匹配或词库版本发布机制。

## 文档索引

- 业务逻辑 → [business.md](business.md)
- 对外契约 → [contracts.md](contracts.md)
- 执行流程 → [flows.md](flows.md)

## 待确认

- `resources/views/backend/content/` 两个 Blade 模板仍包含地区模块文案和 `py-area:*` 路由，但当前 `WordController` 使用 MgrPage `Grid`/`FormWidget`，未直接引用这些模板；需确认是否为可清理的历史文件。
- `manifest.json` 的名称为“地区”、描述为 area module，疑似复制遗留；需确认模块清单是否会展示该文件内容。
