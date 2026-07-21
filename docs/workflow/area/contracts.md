# 对外契约

## API 路由（api_v1.php）

**前缀**：`/api_v1/area`，**中间件**：`api-sign`
**命名空间**：`Poppy\Area\Http\Request\ApiV1\Web`

| HTTP方法          | URI                 | 请求类/控制器                                                | 中间件       | 说明                                                            |
|-----------------|---------------------|--------------------------------------------------------|-----------|---------------------------------------------------------------|
| `any`（GET/POST） | `/api_v1/area/area/code`    | `ApiV1\Web\AreaController::code()`                   | api-sign  | 获取地区代码树（`code` 字段为 `left(code, 6)`，不命中缓存，直接走表）               |
| `any`（GET/POST） | `/api_v1/area/area/country` | `ApiV1\Web\AreaController::country()`                | api-sign  | 获取国别数组（`[{en, iso, py, zh, cty}, …]`，命中 30 天缓存）              |

**OpenAPI Schema**（位于 `Http/Request/ApiV1/Web/Area/`）：

| Response Body 类                | Schema 名                          | 说明                |
|------------------------------|------------------------------------|-------------------|
| `AreaCodeResponseBody`       | `PoppyAreaAreaCodeResponseBody`    | 地区树响应              |
| `AreaCountryResponseBody`    | `PoppyAreaAreaCountryResponseBody` | 国别响应（键值对 `iso=>zh`） |

## 管理后台路由（backend.php）

**前缀**：`/backend/area`，**中间件**：`backend-auth`
**命名空间**：`Poppy\Area\Http\Request\Backend`

| HTTP方法          | URI                       | 控制器方法                                          | 路由名                                       | 说明                                    |
|-----------------|--------------------------|------------------------------------------------|-------------------------------------------|---------------------------------------|
| `any`           | `/backend/area/`                  | `Backend\ContentController::index()`           | `py-area:backend.content.index`           | 地区列表（MgrPage Grid + ListSysArea）     |
| `any`           | `/backend/area/establish/{id?}`   | `Backend\ContentController::establish()`       | `py-area:backend.content.establish`       | 新增/编辑地区（FormAreaEstablish 表单）       |
| `any`           | `/backend/area/delete/{id?}`      | `Backend\ContentController::delete()`          | `py-area:backend.content.delete`          | 删除地区（走 `Action\Area::delete()`）       |
| `any`           | `/backend/area/fix`               | `Backend\ContentController::fix()`             | `py-area:backend.content.fix`             | 重算 children / top_parent_id / level |

## 发布的事件（本模块对外发布）

无。本模块不主动 `event(new …)` 发布领域事件。

## 监听的事件（本模块消费）

| 监听器类                                                            | 监听的事件                       | 业务动作                            | 产生的事件/任务 |
|----------------------------------------------------------------|-----------------------------|---------------------------------|----------|
| `Listeners\PoppyOptimized\ClearCacheListener`                  | `Poppy\Framework\Events\PoppyOptimized` | `sys_tag('py-area')->clear()` 清空标签下所有缓存 | —        |

## 队列任务

无（`Jobs/` 目录不存在）。

## Artisan 命令

| 命令签名                | 说明                                                  | 调度方式  |
|---------------------|-----------------------------------------------------|-------|
| `py-area:init`      | 从 `resources/def/*.json` 初始化省/市/区/县到 `sys_area`，并清 Redis 临时 Hash | 手动触发  |

## 跨模块调用（本模块调用其他模块）

| 本模块调用方                          | 目标模块     | 目标类                                                                  | 调用方法                                                                                       | 场景                                              |
|----------------------------------|----------|----------------------------------------------------------------------|--------------------------------------------------------------------------------------------|-------------------------------------------------|
| `Action\Area`                    | system   | `Poppy\System\Classes\Traits\PamTrait` / `FixTrait`                  | trait 注入 → `checkPam()`、`batchFix()`、`fixView()`                                          | 后台写入校验 PAM 权限；fix 流程提供分页/进度 UI                |
| `InitCommand`                    | core     | `Poppy\Core\Redis\RdsDb`                                             | `RdsDb::instance()`、`hMSet()`、`hGet()`、`del()`                                             | 灌数据时 Redis Hash 中间映射                            |
| `SysArea` 模型静态方法                  | core     | `Poppy\Core\Classes\PyCoreDef`                                       | `PyCoreDef::MIN_ONE_MONTH * 60`（作为缓存 TTL 秒）                                                  | 统一缓存时长常量                                        |
| `Action\Area` / `SysArea` 模型 / Listener | framework | `Poppy\Framework\Helper\TreeHelper` / `UtilHelper` / `sys_tag()` / `Events\PoppyOptimized` | `TreeHelper->init()/getTreeArray()`；`UtilHelper::genTree()`；`sys_tag('py-area')->remember/del/clear`；`PoppyOptimized` 事件 | 树形数据组装、缓存读写、缓存清理触发                          |
| `ServiceProvider`                 | mgr-page | `Poppy\MgrPage\Classes\Form` / `Grid\Filter`                         | `Form::extend('area', \Poppy\Area\Classes\Form\Field\Area::class)` 等                          | 注册 Form/Grid 字段扩展类型                            |

## 被其他模块调用（本模块被引用）

| 调用方模块       | 调用方类/位置                                | 本模块目标类                    | 调用方法                                                                                                                                  | 场景                              |
|-------------|----------------------------------------|---------------------------|-------------------------------------------------------------------------------------------------------------------------------------|---------------------------------|
| 待确认：需扫其他模块 | MgrPage Grid / Form 任意业务后台                          | `SysArea::cityTree()` / `cascader()` / `kvProvince()` / `kvCity()` / `kvArea()` / `kvCountry()` | 静态方法                                                                  | 后台做地址/地区选择、KV 展示              |
| 待确认：需扫其他模块 | 任意前台业务 Controller                              | 同上                                          | 同上                                                                                                                                  | 前端地区下拉、级联选择                  |

## MgrPage 扩展注册

在 `ServiceProvider::register()` 中注册：

```php
Form::extend('area', Classes\Form\Field\Area::class);
Filter::extend('area', Classes\Grid\Filter\Area::class);
```

字段渲染器：`Poppy\Area\Classes\Form\Field\Area`（继承 `Poppy\MgrPage\Classes\Form\Field`，视图 `py-area::tpl.form.area`）
过滤器渲染器：`Poppy\Area\Classes\Grid\Filter\Area` + Presenter `Classes\Grid\Filter\Presenter\Area`（视图 `py-area::tpl.filter.area`）

## 权限 / 菜单注册

- **权限点**：`backend:py-area.main.manage`（`configurations/permissions.yaml` → `slug: manage`），挂在组 `backend:py-area.main`。
- **菜单入口**：`configurations/menus.yaml` 在 `poppy.mgr-page/backend||setting` 注入点下挂"地区管理"菜单项，`route: py-area:backend.content.index`、`permission: backend:py-area.main.manage`。

## 待确认

- "被其他模块调用（本模块被引用）" 表中所有调用方模块均标注为"待确认：需扫其他模块"。当前模块分析阶段无法确认实际被引用范围，需要遍历 `poppy/*/src/` 下的所有 `Http/Request/` 与 `Http/MgrPage/` 来补充（发现位置：contracts.md → "被其他模块调用" 段落）
- `py-area:init` 是否在部署脚本 / 安装流程中被自动调度，或仅依赖人工首次执行（发现位置：contracts.md → "Artisan 命令" 段落）
- `ApiV1\Web\AreaController::code()` 接口直接全表查询不走缓存，未来若地区表行数膨胀（>10w）是否需要切换到 `SysArea::cityTree()` 缓存版本（发现位置：contracts.md → "API 路由" 段落）