# wulicode/poppy

Laravel 6 + Poppy 框架伪多模块业务系统，覆盖账号/认证、内容、广告、推送、对象存储、敏感词等能力。

## 文档阅读顺序

| 顺序 | 文档                                 | 什么时候看                                   |
|------|--------------------------------------|----------------------------------------------|
| 1    | `.claude/rules/module-map.md`        | 初次进入某个模块：模块职责、目录结构、依赖   |
| 2    | `.claude/rules/architecture.md`      | 实现业务逻辑前：分层约束、设计原则、编码标准 |
| 3    | `.claude/rules/coding.md`            | 编码过程中：编码原则（简洁/精准/目标驱动）   |
| 4    | `.claude/rules/event-conventions.md` | 改 Event/Listener/Job 前：事件契约、幂等约定 |
| 5    | `.claude/rules/http-conventions.md`  | 改路由/Request/Controller 前：HTTP 接口规范  |
| 6    | `.claude/rules/cross-module.md`      | 跨模块改动时：引用约束、改动汇报格式         |

> 项目当前 **无** `docs/workflow/` 工作流文档。建议运行 `/php-analyzer` 生成各模块 overview/business/contracts/flows。

## 模块清单

| 模块           | 命名空间             | 职责                                               |
|----------------|----------------------|----------------------------------------------------|
| framework      | Poppy\Framework\     | 框架核心（基类、ServiceProvider、Helper、Console） |
| core           | Poppy\Core\          | 核心服务（权限/RBAC/Redis/模块加载）               |
| system         | Poppy\System\        | 系统能力（账号/PAM/认证/SSO/上传/通知/设置）       |
| mgr-page       | Poppy\MgrPage\       | 后台框架（FormBuilder、模板、菜单）                |
| ad             | Poppy\Ad\            | 广告位与广告内容                                   |
| aliyun-oss     | Poppy\AliyunOss\     | 阿里云 OSS 存储集成                                |
| aliyun-push    | Poppy\AliyunPush\    | 阿里云移动推送                                     |
| app            | Poppy\App\           | 应用接入（App 签名/Middleware）                    |
| area           | Poppy\Area\          | 行政区划                                           |
| category       | Poppy\Category\      | 系统分类                                           |
| content        | Poppy\Content\       | 内容管理                                           |
| sensitive-word | Poppy\SensitiveWord\ | 敏感词检测                                         |
| sms            | Poppy\Sms\           | 短信发送                                           |
| version        | Poppy\Version\       | 版本管理                                           |
| demo           | Demo\                | 演示模块                                           |

详细模块信息见 `.claude/rules/module-map.md`

## 规则文件（.claude/rules/）

### 始终加载

| 文件              | 内容                                             |
|-------------------|--------------------------------------------------|
| `architecture.md` | 分层约束、编码标准、目录约定                     |
| `module-map.md`   | 模块速查（命名空间、目录、职责、跨模块引用方向） |
| `coding.md`       | 编码原则（简洁优先、精准修改、目标驱动）         |

### 按需加载（globs 匹配时自动加载）

| 文件                   | 内容                                          |
|------------------------|-----------------------------------------------|
| `event-conventions.md` | Event/Listener/Job 开发模板、幂等约定         |
| `http-conventions.md`  | HTTP 接口、Request/Controller、模板与分页规范 |
| `cross-module.md`      | 跨模块改动清单、引用约束、完成汇报格式        |


### 质量校验「新增」

- 代码风格 : 使用 PHP CS Fixer 校式化代码保障风格一致性

```
# 格式化所有文件
/opt/homebrew/bin/php -d memory_limit=-1 $(which php-cs-fixer) fix --config=./vendor/poppy/framework/.php-cs-fixer.php --diff --verbose
# 格式化指定文件
/opt/homebrew/bin/php -d memory_limit=-1 $(which php-cs-fixer) fix --config=./vendor/poppy/framework/.php-cs-fixer.php {file or directory to format}
`````

- 语法 Lint

```
# 格式化指定文件或者目录
./vendor/bin/phplint --configuration=./vendor/poppy/framework/.phplint.yaml {file or directory to check}
```