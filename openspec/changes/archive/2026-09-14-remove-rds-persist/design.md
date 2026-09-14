## Context

`RdsPersist`、`PersistCommand`、`PyCoreDef::ckPersistPersist()` 三者是一条完整链路：命令行调用 `RdsPersist::exec()/execTable()`，内部用 `PyCoreDef::ckPersistPersist()` 拼 Redis key。`RdsStore::inLock()` 共用同一个 redis tag（`py-core-persist`，通过 `sys_tag()` 动态解析，非静态 config 注册项）但走独立的 key 命名方法 `ckPersistRdsLock()`，两者除了共用连接标识外没有代码耦合。详见 proposal.md - Why / Impact。

## Goals / Non-Goals

**Goals:**
- 完整删除 `RdsPersist` 缓冲刷库链路（类、命令、专属 key 方法、单测、文档）
- 保证 `RdsStore::inLock()` 原子锁能力零影响
- 给出生产环境遗留缓冲数据的处理步骤，避免静默丢数据

**Non-Goals:**
- 不改动 `py-core-persist` 这个 redis tag 本身的连接配置
- 不为业务模块引入替代的"写入缓冲"方案——本次是纯移除，若未来有模块需要类似能力，应按需另起设计
- 不涉及 `RdsFieldExpired` 的功能改动，只更正文档里对它的一句不准确描述

## Decisions

**1. 删除顺序：先文档核实，再删代码，最后删文档**
- 原因：`docs/workflow/core/business.md` / `contracts.md` 里两处标注"是否有定时调度"为"待确认"，本次已通过 grep `Kernel`/`ServiceProvider::registerSchedule()` 确认无调度。删代码前把这个结论记录进 tasks，避免后续review 时重复怀疑同一个问题。

**2. `PyCoreDef` 只删 `ckPersistPersist()`，不动 `ckPersistRdsLock()`**
- 替代方案考虑过"把两个方法都保留，只删 RdsPersist 类"——放弃，因为 `ckPersistPersist()` 是死代码，符合 coding.md「精准修改：清理自己造成的孤儿代码」原则；`ckPersistRdsLock()` 仍被 `RdsStore::inLock()` 使用，必须保留。

**3. 不做过渡期兼容（no deprecation shim）**
- 原因：已扫描确认 `system`/`content`/`ad` 等业务模块无引用，`py-core:persist` 也没有外部定时任务依赖。属于内部工具类，且项目内部约定（coding.md）不允许为不会发生的场景做兼容处理，直接删除即可，不引入 `@deprecated` 过渡版本。

## Risks / Trade-offs

- **[风险] 生产 Redis 中可能存在历史遗留的 `persist:*` 缓冲 key，命令删除后无法再刷库** → **缓解**：tasks 中加一步"部署前人工检查 `persist:*` key 是否存在"，已写入 specs 的对应 Requirement/Scenario，作为强制检查项，不是可选项。
- **[风险] 误删 `py-core-persist` tag 相关的锁定逻辑，导致 `RdsStore::inLock()` 失效** → **缓解**：design 中已明确边界（只删 `ckPersistPersist`，保留 `ckPersistRdsLock`），tasks 拆分为独立步骤并要求改动后跑 `RdsStore` 相关测试（如有）或至少手动验证 `inLock()`。
- **[权衡] 不保留任何过渡兼容层** → 换来的是改动更简单、无需后续二次清理；代价是如果扫描有遗漏（比如动态字符串拼接调用 `RdsPersist::class`），会直接报错而不是静默降级——可接受，因为直接报错比静默数据丢失更安全，且已做过全仓库 grep。

## Migration Plan

1. 人工确认生产 Redis 无遗留 `persist:*` key（或已手动刷库/书面确认可丢弃）——部署前置条件，不属于代码改动步骤
2. 删除 `poppy/core/tests/Redis/RdsPersistTest.php`
3. 删除 `poppy/core/src/Commands/PersistCommand.php`，同步移除 `poppy/core/src/ServiceProvider.php` 中的命令注册
4. 删除 `poppy/core/src/Redis/RdsPersist.php`
5. 从 `poppy/core/src/Classes/PyCoreDef.php` 删除 `ckPersistPersist()`，保留 `ckPersistRdsLock()`
6. 更新 `docs/workflow/core/flows.md`（删"流程四"章节及时序图）、`business.md`、`contracts.md`
7. 跑 `php -l` 语法检查 + `php artisan route:list`（确认命令服务提供者未报错）+ 现有 core 模块测试套件

**回滚策略**：本次改动是纯删除、无数据结构/schema 变更，回滚即 `git revert` 对应 commit；线上层面无需额外回滚动作（因为遗留数据已在部署前处理完毕）。

## Open Questions

（无——所有不确定项已在本次调研中核实清楚，不遗留到实现阶段。）
