## 1. 部署前置检查

- [ ] 1.1 人工检查生产 Redis 中是否存在 `persist:*` 前缀的遗留缓冲 key；若存在，执行一次 `php artisan py-core:persist all` 完成最后刷库，或书面确认可丢弃；验证方式：检查结论（"无遗留 key" 或 "已刷库/已确认可丢弃"）记录在本次变更的 PR 描述或部署记录中（本地开发环境 Redis 已确认无 `persist:*` key，但这不能替代生产环境检查，需部署前由运维在生产环境执行）

## 2. 删除测试与命令层

- [x] 2.1 删除 `poppy/core/tests/Redis/RdsPersistTest.php`；验证：`php artisan test`（或对应测试命令）不再收集到该测试文件 —— 已删除，`phpunit poppy/core/tests/Redis` 从 81 个测试中不再包含该类
- [x] 2.2 删除 `poppy/core/src/Commands/PersistCommand.php`，并移除 `poppy/core/src/ServiceProvider.php` 中 `Commands\PersistCommand::class` 的注册；验证：`php artisan list` 输出中不再包含 `py-core:persist` —— 已核实，命令列表中仅剩 `py-core:doc`/`py-core:inspect`/`py-core:permission`

## 3. 删除核心类与专属常量

- [x] 3.1 删除 `poppy/core/src/Redis/RdsPersist.php`；验证：`grep -rn "RdsPersist" --include="*.php" . | grep -v vendor` 只剩文档引用（本组任务完成后应为 0 处代码引用）—— 已核实为 0 处
- [x] 3.2 从 `poppy/core/src/Classes/PyCoreDef.php` 删除 `ckPersistPersist()` 方法，保留 `ckPersistRdsLock()`；验证：`php -l poppy/core/src/Classes/PyCoreDef.php` 通过语法检查，且 `grep -n "ckPersistRdsLock" poppy/core/src/Redis/RdsStore.php` 仍能找到调用（确认未被误删）—— 均已通过

## 4. 文档同步

- [x] 4.1 更新 `docs/workflow/core/flows.md`：删除"流程四（辅助）：持久化缓冲刷库（py-core:persist）"整节及其 mermaid 时序图；验证：`grep -n "RdsPersist\|py-core:persist" docs/workflow/core/flows.md` 无匹配 —— 已通过
- [x] 4.2 更新 `docs/workflow/core/business.md`：删除 `py-core-persist` 缓存说明中对 `RdsPersist` 的引用、"关键算法"里的 `RdsPersist` 段落、命令清单里的 `py-core:persist` 行，并移除"待确认"小节里关于 `py-core:persist` 调度的条目（已在本次变更中核实为无调度）；验证：`grep -n "RdsPersist\|py-core:persist" docs/workflow/core/business.md` 无匹配 —— 已通过；`py-core-persist` tag 说明已改为指向 `RdsStore::inLock()`
- [x] 4.3 更新 `docs/workflow/core/contracts.md`：删除 `RdsPersist` 方法签名列表、命令清单里的 `py-core:persist` 行；核实并更正 `RdsFieldExpired::clearExpiredField` 处"由 py-core:persist 周期性触发"的描述（若确认不准确则改为准确的触发方式或标注为待确认原因）；验证：`grep -n "RdsPersist\|py-core:persist" docs/workflow/core/contracts.md` 无匹配 —— 已通过；核实 `clearExpiredField` 除单测外无任何调用方，已更正描述

## 5. 验证与收尾

- [x] 5.1 全仓库确认无残留引用：`grep -rn "RdsPersist\|PersistCommand\|py-core:persist" --include="*.php" . | grep -v vendor`（应为空）及同一命令加 `--include="*.md"`（应只在本 openspec change 目录内出现）—— 均已通过
- [x] 5.2 语法检查改动文件：`./bin/php -l poppy/core/src/Classes/PyCoreDef.php poppy/core/src/ServiceProvider.php`；确认全部 "No syntax errors detected" —— 已通过，另跑了 php-cs-fixer --dry-run 风格检查，0 处需要修改
- [x] 5.3 跑 `core` 模块测试套件（如 `php artisan test --filter=Core` 或项目约定的 core 测试命令），确认无因删除而产生的失败 —— `./bin/php vendor/bin/phpunit poppy/core/tests/Redis`：81 tests, 231 assertions 全部通过（1 个与本次改动无关的既有 warning：`RdsBaseTest` 空测试类）
- [x] 5.4 手动或通过现有测试验证 `RdsStore::inLock()` 仍正常工作（原子锁未受影响）—— 已通过上述测试套件覆盖（`RdsStore` 相关测试位于同一测试目录且全部通过）
