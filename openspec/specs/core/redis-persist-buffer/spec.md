# core/redis-persist-buffer Specification

## Purpose

记录 `core` 模块是否提供"高频写入先缓冲到 Redis、再异步批量刷库"的持久化能力，并明确该能力已被下线，避免后续误用或重复实现。

## Requirements

### Requirement: 不提供 Redis 缓冲式持久化能力
`core` 模块 SHALL NOT 提供通用的、面向任意数据表的"Redis 缓冲写入 + 异步批量刷库"机制（即历史上的 `RdsPersist`）。业务模块的写入操作 MUST 直接落库或使用各自模块自行实现并维护的持久化策略，不得依赖本能力。

#### Scenario: 缓冲持久化类不存在
- **WHEN** 任意代码尝试引用 `Poppy\Core\Redis\RdsPersist` 类
- **THEN** 该类不存在，引用将在编译/自动加载阶段失败

#### Scenario: 刷库命令未注册
- **WHEN** 执行 `php artisan list` 或调用 `php artisan py-core:persist {table}`
- **THEN** `py-core:persist` 不在已注册命令列表中，命令不可执行

#### Scenario: 原子锁能力不受影响
- **WHEN** 业务代码调用 `RdsStore::inLock()` 获取分布式原子锁
- **THEN** 该能力正常工作，不因缓冲持久化能力下线而受影响（两者仅共用 Redis 连接标识，无功能依赖）

### Requirement: 历史遗留缓冲数据需人工确认
若生产环境 Redis 中在下线前仍存在通过历史 `RdsPersist::insert`/`update` 写入、但尚未通过 `py-core:persist` 刷库的缓冲数据（key 前缀 `persist:*`），运维 MUST 在部署本次变更前完成以下二选一处理：手动执行一次刷库，或确认这些数据可以丢弃。

#### Scenario: 部署前存在未刷库的缓冲数据
- **WHEN** 部署前发现 Redis 中存在 `persist:*` 前缀的 key
- **THEN** 运维需在移除命令前手动执行 `php artisan py-core:persist all` 完成最后一次刷库，或书面确认该数据可丢弃
