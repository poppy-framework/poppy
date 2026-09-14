# OpenSpec 工作流复刻计划(poppy 项目)

> 本文档只是**复刻流程**本身——按阶段拆解「从参考项目搬什么、怎么改、谁来验」，
> 不是复刻结果。每个阶段做完就停下来，等人工验证通过再进入下一阶段。
> 不在这里写具体的 guards 代码或最终的 config.yaml 内容——那些产出物落在
> 对应阶段实际创建的文件里，本文档只登记「做了什么决定、为什么」。

## 0. 参考来源与基线差异

- 参考项目：`/Users/duoli/Projects/hanrui-jinnuo/mono4ts`（TypeScript pnpm monorepo）
- 目标项目：本仓库（Laravel 6 + Poppy 伪多模块，PHP >= 7.4，composer）
- `openspec` CLI 已全局可用（`1.13.0`，`which openspec` → mise 安装的 node 全局包），
  两个项目共用同一个全局 CLI，不需要额外安装。
- 参考项目的体系分两层：
  1. **通用层**：`openspec` CLI 自带的标准 skills（`openspec-new-change` /
     `openspec-propose` / `openspec-apply-change` / `openspec-verify-change` /
     `openspec-archive-change` / `openspec-continue-change` / `openspec-update-change` /
     `openspec-ff-change` / `openspec-explore` / `openspec-onboard` /
     `openspec-sync-specs` / `openspec-bulk-archive-change`）——这些不绑定项目技术栈，
     是 CLI 生成的标准物料，理论上可以直接搬（用 CLI 重新生成比手工复制更可靠，见阶段 1）。
  2. **项目定制层**：`devops-workflow` schema + `project.json` + `config.yaml` 的 `rules` +
     9 个 `guards/*.mjs` + `rules/enforced/constitution.md` + `rules/advisory/*.md` +
     `devops-openspec-workflow` 编排 skill——这一层深度绑定 pnpm/vitest/TS monorepo 的
     具体命令、目录结构、组件注册表等概念，**不能整体照搬**，需要逐项判断「这个概念在
     PHP 伪多模块架构下的对应物是什么」，没有对应物的就不移植。

## 1. 阶段拆解

每个阶段结束时都在本文件「验证记录」表里补一行，注明验证人（多厘）、日期、结论。
未拿到「通过」之前不得开始下一阶段。

### 阶段 1 — 落地 openspec CLI 通用层

**做什么：**
- 在本仓库根目录跑 `openspec init` 生成标准骨架和 12 个标准
  `.claude/skills/openspec-*` skill 文件。
- 不手工复制参考项目的 skill 文件——用 CLI 现场生成，版本号自然对齐本机的
  `1.13.0`，避免拷贝旧版本导致的 drift。
- 生成后跑 `diff` 与参考项目的同名 skill 文件对比，确认只有版本号/项目名等占位符
  不同，逻辑内容一致。

**产出：** `openspec/config.yaml`（CLI 默认版，`schema: spec-driven`）、
`.claude/skills/openspec-*/SKILL.md` × 12（含 `devops-openspec-workflow`
编排 skill，由同一次 `init` 生成，内容与参考项目一致，见阶段 6 说明）。

**不做：** 不在这一步引入 `devops-workflow` schema，也不手工创建
`project.json`（该文件属于 `devops-workflow` 自定义 schema 的产物，归属阶段 3/5，
CLI 默认 schema 不生成它）；先用 CLI 默认的 `spec-driven` schema 跑通最小闭环
（new change → proposal → apply → archive）。

### 阶段 2 — 用最小闭环验证 CLI 基础可用性

**做什么：**
- 用一个无风险的小改动（例如给某个已存在的 spec 类文档打样）走一遍
  `openspec-new-change` → `openspec-propose` → `openspec-apply-change` →
  `openspec-archive-change` 的完整链路。
- 确认 `openspec validate` / `openspec archive` 等命令在 PHP 项目里不依赖任何
  Node/TS 专属假设（实测：CLI 本身是技术栈无关的，只在 schema 定制层才会绑定语言）。

**产出：** 一个已归档的示例 change，作为后续「这条流程在本项目里跑得通」的证据。

### 阶段 3 — 设计 PHP/Laravel 版的 `devops-workflow` schema（仅设计，不落地）

参考项目 `config.yaml` 的 `rules` 一节是全流程唯一的判定口径来源（连同 schema.yaml
的 instruction 和 templates/ 的模板），本阶段要做的是**逐条过一遍参考项目的 7 类
rules（interview / explore / proposal / specs / design / tasks / exec-plan / verify），
判断每条在 PHP 伪多模块架构下的等价约束是什么**，写成对照表（不是照抄），
再决定要不要简化。

**对照表草案（人工确认后细化）：**

| 参考项目概念 | 本项目对应物 | 备注 |
|---|---|---|
| `packages/` monorepo 包边界 | `poppy/{module}/` 模块边界 | 模块清单见 `.claude/rules/module-map.md` |
| 共享层命中三张表（桶文件/注册表、跨包契约、序号型资源） | 跨模块引用矩阵（见 `.claude/rules/cross-module.md` §2.3）+ Event 契约（`event-conventions.md`） | 需要重新设计成「事件/Action/Model 契约」而非「TS 包导出」 |
| `pnpm build` / `pnpm test:changed` / `pnpm lint` | `php -l` 语法检查 / `phpunit --filter` / `php-cs-fixer fix --diff` / `phplint` | 见 `CLAUDE.md` 质量校验章节，命令已现成 |
| `guards/components-registry.mjs`（React 组件注册表一致性） | 无直接对应物 | Laravel 无组件注册表概念，此 guard 不移植 |
| `guards/dev-port-alignment.mjs` | 无直接对应物 | 单体 Laravel 应用无多端口概念，不移植 |
| `guards/drizzle-journal.mjs`（Drizzle migration 序号一致性） | Laravel migration 时间戳文件名一致性 | 需要新写，判据不同（Laravel 用时间戳非自增序号，冲突概率低，价值存疑，待评估） |
| `guards/file-length.mjs` | 可直接复用逻辑，改扩展名为 `.php` | 通用性强 |
| `guards/ratchet.mjs`（棘轮式质量收紧） | 可直接复用逻辑 | 通用性强，具体阈值需按 PHP 项目现状重新采样 |
| `guards/rules-index.mjs` | 可直接复用逻辑，改扫描 `.claude/rules/` | 通用性强 |
| `guards/spec-xref.mjs` | 可直接复用逻辑 | 通用性强 |
| `guards/state-waitlist.mjs` | 可直接复用逻辑 | 通用性强 |
| `guards/worktree-orphan.mjs` | 视是否采用 git worktree 并行执行策略而定 | 本项目目前未见 worktree 并行开发实践，需先确认是否需要 |
| `rules/enforced/constitution.md` | 对应 `.claude/rules/architecture.md` 的分层约束（CP-N 形式） | 需要把现有 MUST/严禁 条款重写成 constitution 的 CP-N 编号格式 |
| L7 硬闸门命令清单 | 见 `CLAUDE.md`：`php-cs-fixer` / `phplint` / `phpunit` | 命令已现成，只需绑定到 `project.json.commands` |

**本阶段产出：** 只更新上表 + 附一份文字说明，**不创建** `config.yaml` / `guards/*.mjs`
等实际文件。人工确认对照表方向后，才进入阶段 4。

### 阶段 4 — 逐个 guard 移植（每个 guard 单独验证）

- 每个 guard 作为独立的子任务：先写，跑 `node openspec/check.mjs`（或等价单元测试）
  验证其在本项目样本文件上的判定结果符合预期（有意制造一个违规样本 + 一个合规样本）。
  不批量移植 9 个 guard，一个一个来，人工逐个确认。
- 不移植的 guard（`components-registry` / `dev-port-alignment`，以及待定的
  `worktree-orphan`）在本文件「验证记录」里注明「不适用」及原因，不留空白。

### 阶段 5 — 落地 `devops-workflow` schema.yaml + config.yaml + rules（人工审阅每条 rule）

- 基于阶段 3 的对照表，正式写 `openspec/config.yaml` 的 `rules:` 节。
- 每类 artifact（interview/explore/proposal/specs/design/tasks/exec-plan/verify）的
  rules 逐条来源标注：「直接复用参考项目原文」还是「按 PHP 项目改写」，改写的要写明
  改写理由（不能默默改措辞）。
- `schema.yaml` 与 `templates/*.md` 同步：模板里的槽位说明要对齐本项目的分层
  （Http/Request → Action → Model/Dao → Event/Job，而非参考项目的前端组件层）。

### 阶段 6 — 落地 `devops-openspec-workflow` 编排 skill

- 参考项目的这个 skill 本身是**schema 无关的通用编排器**（按其 SKILL.md 声明：
  "本文不对所在仓库做任何假设"），只是把仓库特定事实指向
  `openspec/rules/enforced/project.md` 和 `openspec/rules/advisory/pitfalls.md`。
  因此这一步大概率可以直接复用文件本身，只需要确认阶段 5 产出的两份规则文件
  路径命名一致。
- 复用后跑一次真实的端到端 change（interview → archive）验证三个人类决策点
  （L0 歧义澄清 / L3 design 审批 / L9 最终 diff 签字）触发正确。

### 阶段 7 — 用一个真实小需求跑通完整定制流水线

- 挑一个本项目里中等大小、跨 1-2 个模块的真实需求，完整走一遍定制后的
  L0-L10 流水线，验证 guards、rules、schema 三者协同工作，没有生搬 TS 项目
  概念导致的误判。
- 这一步的结论直接决定阶段 3-6 是否需要返工。

## 2. 验证记录

| 阶段 | 验证人 | 日期 | 结论 | 备注 |
|---|---|---|---|---|
| 1 | 多厘 | 2026-09-14 | 打回 → 已更正 | `project.json` 误归入阶段 1 产出，已改归阶段 3/5。骨架与 12 个 skill 已生成，待再次确认。 |
| 2 | | | 待验证 | |
| 3 | | | 待验证 | |
| 4 | | | 待验证 | |
| 5 | | | 待验证 | |
| 6 | | | 待验证 | |
| 7 | | | 待验证 | |

## 3. 不做代码提交说明

本次任务范围仅限于产出本流程文档（`openspec/design/readme.md`），不创建
`openspec/project.json`、`.claude/skills/openspec-*`、`guards/*.mjs` 等实际文件，
也不执行任何 `git add` / `git commit`。后续阶段的实际文件改动，均需在对应阶段
人工验证通过后，由用户明确要求再进行。

## 4. 阶段外例外记录

本节记录不属于上述 1-7 阶段编号、但因用户明确要求而提前落地的实际文件改动。
与阶段 1-7 不同，这类改动**不受「上一阶段验证通过才能进行下一阶段」的门禁约束**，
仅要求在此如实登记「改了什么、为什么、谁要求的」，避免脱离本文档的记录之外。

### 2026-09-14 — 本地 PHP 运行时环境 enforced 检测

**背景：** 项目要求 PHP 7.4（`composer.json` `require.php`），但本机系统默认 `php`
命令是 brew 安装的更高版本（8.5.x），已实际导致过一次因误用错误版本 PHP 排查问题的
情况。用户要求：不能只靠 `CLAUDE.md` 里的文档约定（对人类协作者和其他 AI 会话不生效），
要把「用哪个 PHP」这件事变成机械可校验的 enforced 条件。

**产出文件：**

| 文件 | 是否提交进版本库 | 作用 |
|---|---|---|
| `bin/php.example` | 是 | 团队共享的 wrapper 脚本模板，逐机器修改后本地生效 |
| `bin/php` | 否（已加入 `.gitignore`） | 各机器本地初始化产物，路径因系统而异，不适合共享 |
| `bin/check-env` | 是 | 校验 `bin/php` 是否存在、版本是否满足 `composer.json` 声明的自检脚本；**不侵入** `artisan` 或 `public/index.php` 等框架启动文件，避免检测逻辑出错阻断所有本地命令或生产请求 |
| `CLAUDE.md`（PHP 运行时版本约定一节）| 是 | 记录「为什么」和初始化步骤，作为 advisory 层补充说明 |

**验证：** `./bin/check-env` 在 `bin/php` 存在/缺失两种场景下分别返回 exit 0 / exit 1，
符合预期。

### 2026-09-14（同日修正）— 移除 git hook 强制拦截，明确定位为 AI 协作约定

最初实现里曾加入 `resources/.husky/pre-commit` + `git config core.hooksPath`，让提交时
自动拦截 PHP 版本不符的情况。用户复核后明确指出：**`bin/php` 这套东西是给 AI 协作会话用的
约定，不应该混进 CI/Git 层面的强制门禁**——两者定位不同，前者是「帮 AI 不用每次重新判断该
用哪个路径」，后者是「团队级别的强制卡点」，混在一起会让「这条约定到底对谁生效、绕不绕得开」
变得含糊。

**已撤销：**
- `resources/.husky/pre-commit` 文件已删除
- 本机 `git config core.hooksPath` 已 `--unset`
- `resources/.husky/` 目录（含 husky 引导用的 `_/husky.sh`）整体移除

**保留：**
- `bin/php`、`bin/php.example`、`bin/check-env` 不变，仍是 AI 协作时的调用约定和手动自检
  手段，只是不再有任何自动化机制强制触发它。

**遗留问题（留给未来判断，非本次范围）：** 如果之后确实需要团队级别的 CI/Git 强制门禁
（校验对象可以更广，不止 PHP 版本），应该作为阶段 3-7 `devops-workflow` schema 的
`guards/*` 或 `project.json.commands` 的一部分重新设计，而不是复用 `bin/check-env`
这套 AI 专用脚本硬套上去。

## 5. 如何构建 AI 环境（新成员 / 新机器搭建手册）

> 本节写给「拿到一台新机器或第一次 clone 本仓库、需要让 AI 协作环境跑起来」的人（人类或
> AI 会话）。只讲**现在已经落地、能直接照做的步骤**；设计决策的论证过程见上面第 0/1/3/4
> 节，本节不重复，只引用。尚未落地的部分（阶段 3-7 的 `devops-workflow` schema、
> `project.json`、`guards/*.mjs`、`.claude/settings.json` hook）会在完成后回来补充对应步骤，
> 现状是**没有**这些文件，不要假设它们存在。

### 5.1 环境分两层，搭建顺序也分两层

| 层 | 内容 | 是否已提交进版本库 | 本节涵盖 |
|---|---|---|---|
| 项目规则层 | `CLAUDE.md` + `.claude/rules/*.md`（6 个文件：architecture / coding / cross-module / event-conventions / http-conventions / module-map） | 是，clone 即得 | §5.2（确认即可，无需操作） |
| OpenSpec 流程层 | `.claude/skills/openspec-*` × 13（含 `devops-openspec-workflow`）+ `openspec/config.yaml`（CLI 默认 `spec-driven` schema）+ `openspec/specs/` | 是，阶段 1 产出，clone 即得 | §5.3（确认 CLI 可用即可） |
| AI 协作运行时层 | `bin/php`（AI 会话调用 PHP 命令时用，非 CI/Git 强制） | **否**，逐机器本地初始化 | §5.4（按需手动执行） |

### 5.2 项目规则层：确认即可

`CLAUDE.md` 和 `.claude/rules/*.md` 已经在版本库里，clone 仓库后 Claude Code 会自动读取，
**不需要任何初始化操作**。可以用下面命令确认文件齐全：

```bash
ls .claude/rules/
# 期望看到 6 个文件：architecture.md coding.md cross-module.md
# event-conventions.md http-conventions.md module-map.md
```

### 5.3 OpenSpec 流程层：确认 CLI 可用

前提：全局 `openspec` CLI 已安装（本项目用 mise 装的 node 全局包，`which openspec` 应能找到）。
`.claude/skills/openspec-*` 是阶段 1 用 `openspec init` 现场生成后提交进版本库的，clone 仓库后
即可直接使用，不需要重新跑 `init`：

```bash
which openspec          # 确认 CLI 全局可用
ls .claude/skills/       # 应看到 13 个 openspec-*/devops-openspec-workflow 目录
```

若某台新机器上 `openspec` CLI 未安装，参考阶段 1 的安装方式（node 全局包）单独装 CLI 本身，
**不要**重新对本仓库跑 `openspec init`——那会覆盖已经过阶段 1 验证的骨架。

### 5.4 AI 协作运行时层：按需手动初始化

`bin/php` 是 **AI 协作会话调用 PHP 命令时的约定**，不是 CI 或 Git 层面的强制门禁——
不会有任何自动化机制拦截提交或阻断命令，是否初始化、何时自检，按使用者判断即可。
原因见 §4「阶段外例外记录」：PHP 实际安装路径因系统（Apple Silicon / Intel / Linux）
而异，不适合把路径写死提交进版本库，所以每台机器各自初始化一次。

**步骤：**

```bash
# 1. 从模板初始化本机的 PHP wrapper
cp bin/php.example bin/php
chmod +x bin/php

# 2. 编辑 bin/php，把 PHP74 变量改成本机 PHP 7.4 的实际路径
#    参考路径见 bin/php.example 注释：
#      macOS Apple Silicon (brew): /opt/homebrew/opt/php@7.4/bin/php
#      macOS Intel (brew):         /usr/local/opt/php@7.4/bin/php
#      Linux:                      按发行版实际路径

# 3. 跑检测脚本，确认环境满足要求（手动执行，非自动触发）
./bin/check-env
# 期望输出: ✓ bin/php 版本 7.4.x 满足要求 (>=7.4)
```

完成后，AI 协作时日常开发命令走 `./bin/php`（如 `./bin/php artisan route:list`），
不要直接敲裸 `php`——原因见 `CLAUDE.md`「PHP 运行时版本约定」一节。

### 5.5 自检清单

需要确认环境状态时，用这份清单手动核对（不是提交前自动触发的门禁）：

```bash
ls .claude/rules/ | wc -l   # 期望 6
which openspec               # 期望有输出
./bin/check-env              # 期望 exit 0（若已初始化 bin/php）
```

三项满足，即可正常进行 AI 协作开发。
