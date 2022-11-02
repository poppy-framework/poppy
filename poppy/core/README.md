# Poppy Core 模块

> Poppy 核心模块, system 基于本模块

## 命令行

### 权限操作

```
php artisan py-core:permission {slug}
{slug}:
    - list   : 获取权限列表
    - init   : 权限初始化
    - menus  : 检查菜单[todo Undefined index: children]
    - assign : 将权限赋值给指定的用户组
    - check  : 权限检测
```

### 文档以及检查工具

```
php artisan py-core:doc {slug}
{slug}:
    - api   : 生成api文档[apidoc 生成目录]
    - cs    : code style - fix , 代码格式修复(todo 以后IDE 来做)
    - cs-pf : 
    - lint  : 安装检测PHP语法错误的工具
    - php   : 生成 php api 文档
    - log   : 查看当天的 storage 日志
```

### 检查代码

```
php artisan py-core:inspect {slug}
{slug} :
    - apidoc     : 检查api文档(需要指定目录)
    - class      : 方法检测
    - pages      : 检测页面Key[todo 以后会删掉]
    - file       : 检测文件命名[文件类和文件位置不匹配]
    - database   : 检测数据库配置
    - controller : 列出所有功能点
    - action     : 列出所有业务逻辑
    - seo        : 生成 seo 项目
    - db_seo     : 生成数据库SEO 数据
```

### 运维工具

```
php artisan py-core:op {slug}
{slug} : 
    - mail   : 发送运维邮件
```

## 配置说明

文件位置 : `config/poppy.php`

```
return [
    ...
    'core' => [

        // 设置维护的邮箱
        'op_mail'    => env('CORE_OP_MAIL', ''),

        // 设置 RBAC 模型以及外键 KEY
        'rbac' => [
            // 角色模型
            'role'            => \Poppy\System\Models\PamRole::class,
            // 账号模型
            'account'         => \Poppy\System\Models\PamAccount::class,
            // 角色账号模型
            'role_account'    => \Poppy\System\Models\PamRoleAccount::class,
            // 权限模型
            'permission'      => \Poppy\System\Models\PamPermission::class,
            // 角色权限模型
            'role_permission' => \Poppy\System\Models\PamPermissionRole::class,
            // 角色外键
            'role_fk'         => 'role_id',
            // 账号外键
            'account_fk'      => 'account_id',
            // 权限外键
            'permission_fk'   => 'permission_id',
        ],
    ],
    ...
]
```

## 持久化

持久化的流程是将数据放入到缓存, 然后所有的操作都会缓存起来, 然后通过计划任务将数据同步到数据库

### 缓存

持久化使用的缓存是 `tag:py-core:persist` KEY

```
tag:py-core:persist:
    {table}_insert : redis 列表
        [{
            key,    # id 
            insert  # 插入的条件语句
        }]
    {table}_update : redis hash
        [{
            where,  # 查询条件
            update  # 更新内容
        }]
```

### insert 持久化

这里适用的场景是单条插入可以延迟的情况采用统一插入

```
RdsPersist::insert('pam_log', $items);
```

### update 持久化

这里支持从数据库初始化数据, 如果数据不存在, 则创建一条数据并初始化到缓存中

| 2.24.1 版本之后持久化数据可以不进行初始化, 默认必须初始化

```
RdsPersist::update('gift_collection', $where, [
    'gift_num[+]' => 8,
]);
```

持久化使用的基本用法, 因为 persist 加入 facade, 所以可以使用 `Persist` 全局 Facade 来进行使用

```
$init = [
    'add' => 0,
];

$update = [
    'append' => 5,
];
$result = RdsPersist::calcUpdate($init, $update);
```

另外这里 update 支持额外的语法

```
<?php
$init = [
    'add'      => 0,
    'subtract' => 0,
    'preserve' => 0,
    'force'    => 0,
];¶

$update = [
    'add[+]'      => 5,   # 加语法, 保留两位小数, 使用 Number 来计算
    'subtract[-]' => 5,   # 减语法, 保留两位小数, 使用 Number 来计算
    'force'       => 8,   # 覆盖语法, 覆盖之前数据
                            # 不传值代表保留
];

$result = RdsPersist::calcUpdate($init, $update);
$result = [
    "add" => "5.00"
    "subtract" => "-5.00"
    "preserve" => 0
    "force" => 8
]

```

### 持久化到数据库

如果需要持久化到数据库则需要执行相关命令

Usage:
py-core:persist `<table>`

Arguments:
table Table to exec. [pam_log...|all]

```
$this->app['events']->listen('console.schedule', function (Schedule $schedule) {
    ...
    $schedule->command('py-core:persist', ['chat_room'])
        ->daily()->appendOutputTo($this->consoleLog());
    ...
})
```

