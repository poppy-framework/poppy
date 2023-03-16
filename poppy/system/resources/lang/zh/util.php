<?php

return [
    'captcha' => [
        'send_success' => '发送验证码成功',
    ],
    'setting' => [
        'key_not_match'      => '给定的键 :key 格式不匹配',
        'value_out_of_range' => '所设定的内容超长',
    ],
    'classes' => [
        'models' => [
            'pam_account'         => '用户账户',
            'pam_role'            => '用户角色',
            'pam_ban'             => '用户封禁',
            'pam_log'             => '登录日志',
            'pam_permission'      => '用户权限',
            'pam_permission_role' => '权限角色',
            'pam_role_account'    => '账户角色',
            'pam_token'           => '登录凭证',
            'sys_config'          => '系统设置',
        ],
    ],
    'policy'  => [
        'pam_role' => [
            'create' => '用户角色创建',
            'edit'   => '用户角色编辑',
        ],
    ],
];