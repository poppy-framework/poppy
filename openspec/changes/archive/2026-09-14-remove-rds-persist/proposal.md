## Why

`RdsPersist`（`poppy/core/src/Redis/RdsPersist.php`）是一个把高频写入先缓冲到 Redis、再靠手动执行 `php artisan py-core:persist {table|all}` 异步刷库的机制。当前扫描确认：`system`/`content`/`ad` 等业务模块均未引用 `RdsPersist`，`Kernel` 中也没有为 `py-core:persist` 注册定时调度（`docs/workflow/core/flows.md`/`business.md` 中此前长期标注为"待确认"，现已核实确无调度）。这套机制目前处于"无人写入、无人调度"的闲置状态，继续保留会增加 `core` 模块的维护面（额外的 Redis key 约定、单测、命令），故予以移除。

## What Changes

- **BREAKING**：删除 `poppy/core/src/Redis/RdsPersist.php` 类及其对外方法（`update`/`insert`/`where`/`calcUpdate`/`exec`/`execTable`）
- **BREAKING**：删除 `php artisan py-core:persist {table}` 命令（`poppy/core/src/Commands/PersistCommand.php`），并移除 `poppy/core/src/ServiceProvider.php` 中对该命令的注册
- 删除 `poppy/core/tests/Redis/RdsPersistTest.php` 单测
- 删除 `PyCoreDef::ckPersistPersist()`（`poppy/core/src/Classes/PyCoreDef.php`），该方法仅服务于 `RdsPersist` 的 `persist:*` key 命名
- **保留** `PyCoreDef::ckPersistRdsLock()` 与 redis tag `py-core-persist`：`RdsStore::inLock()`（原子锁）复用同一个 tag/连接，与 `RdsPersist` 的缓冲逻辑相互独立，不能一并删除
- 同步更新 `docs/workflow/core/` 下三份文档（`flows.md` 删除"流程四"章节及时序图；`business.md` 删除 `RdsPersist` 相关缓存说明/算法段落/命令行；`contracts.md` 删除 `RdsPersist` 方法签名列表、`py-core:persist` 命令行，并核实/更正 `RdsFieldExpired::clearExpiredField` 处"由 py-core:persist 周期性触发"这句关联描述）

## Capabilities

### New Capabilities

- `core/redis-persist-buffer`：为 `RdsPersist` 缓冲刷库机制正式建立 spec baseline，用于在同一个 change 内以 `REMOVED Requirement` 的形式记录其被移除前的行为与移除决策，便于后续审计追溯

### Modified Capabilities

（无）

## Impact

- **代码**：`poppy/core/src/Redis/RdsPersist.php`（删除）、`poppy/core/src/Commands/PersistCommand.php`（删除）、`poppy/core/src/ServiceProvider.php`（移除命令注册）、`poppy/core/src/Classes/PyCoreDef.php`（移除 `ckPersistPersist`，保留 `ckPersistRdsLock`）、`poppy/core/tests/Redis/RdsPersistTest.php`（删除）
- **文档**：`docs/workflow/core/flows.md`、`docs/workflow/core/business.md`、`docs/workflow/core/contracts.md`
- **依赖模块**：无（已确认 system/content/ad 等模块无引用）
- **数据/运维**：如果生产 Redis 中仍存在历史遗留的 `persist:*` 缓冲 key（曾经写入但从未 `py-core:persist` 刷库的数据），移除命令后将永久无法通过该命令刷库，需要在部署前人工确认并清理或手动刷库一次
