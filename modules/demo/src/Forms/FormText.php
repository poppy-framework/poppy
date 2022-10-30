<?php

namespace Demo\Forms;

use Poppy\Framework\Validation\Rule;

class FormText extends FormBaseWidget
{

    /**
     * 表单标题
     * @var string
     */
    protected $title = 'Text(文本)';

    public $ajax = true;


    /**
     * Build a form here.
     */
    public function form()
    {
        $this->text('text', '文本')->help('文本输入框');
        // 添加 code 代码
        $code = <<<CODE
\$this->text('text', '文本')->help('文本输入框');
CODE;
        $this->code('text-code', 'Code@文本')->default($code);
        $this->divider();

        $this->text('text_icon', '图标')->icon('fa fa-gamepad')->help('设置前置展示图标');
        // 添加 code 代码
        $code = <<<CODE
\$this->text('text_icon', ''图标)->icon('fa fa-gamepad')->help('设置前置展示图标');
CODE;
        $this->code('text_icon-code', 'Code@图标')->default($code);
        $this->divider();

        $this->text('text_focus', '聚焦')->autofocus()->help('输入框焦点显示');
        // 添加 code 代码
        $code = <<<CODE
\$this->text('text_focus', '焦点')->autofocus()->help('输入框焦点显示');
CODE;
        $this->code('text_focus-code', 'Code@焦点')->default($code);
        $this->divider();

        $this->text('text_disable', '禁用')->disable()->default('禁用')->help('输入框禁用');
        // 添加 code 代码
        $code = <<<CODE
\$this->text('text_disable', '禁用')->disable()->default('禁用')->help('输入框禁用');
CODE;
        $this->code('text_disable-code', 'Code@禁用')->default($code);
        $this->divider();

        $this->text('text_read', '只读')->readonly()->placeholder('只读')->help('文本输入框为只读');
        // 添加 code 代码
        $code = <<<CODE
\$this->text('text_read', '只读')->readonly()->placeholder('只读')->help('文本输入框为只读');
CODE;
        $this->code('text_read-code', 'Code@只读')->default($code);
        $this->divider();

        $this->text('text_max_or_min', '长度')->rules([
            'min:2', 'max:4', 'nullable',
        ])->help('文本长度需符合最少2位且最多4位条件');
        // 添加 code 代码
        $code = <<<CODE
\$this->text('text_max_or_min', '长度')->rules([
    'min:2', 'max:4', 'nullable',
])->help('文本长度需符合最少2位且最多4位条件');
CODE;
        $this->code('text_max_or_min-code', 'Code@长度')->default($code);
        $this->divider();

        $this->text('text_preg', '正则')->rules([
            Rule::nullable(),
            'regex:/^[a-z]{2,}$/',
        ])->help('文本内容需符合 a-z 至少2位');
        // 添加 code 代码
        $code = <<<CODE
\$this->text('text_preg', '正则')->rules([
    Rule::nullable(),
    'regex:/^[a-z]{2,}$/',
])->help('文本内容需符合 a-z 至少2位');
CODE;
        $this->code('text_preg-code', 'Code@正则')->default($code);
        $this->divider();

        $this->text('text_no_icon', '文本(无Icon)')
            ->prepend('')
            ->append('xxx')->help('文本(无图标显示)');
        // 添加 code 代码
        $code = <<<CODE
\$this->text('text_no_icon', '文本(无Icon)')
    ->prepend('')
    ->append('xxx')->help('文本(无图标显示)');
CODE;
        $this->code('text_no_icon-code', 'Code@文本(无Icon)')->default($code);
        $this->divider();

        $this->text('text_required', '必填文本')->rules([
            Rule::required(),
        ])->help('文本内容必须填写');
        // 添加 code 代码
        $code = <<<CODE
\$this->text('text_required', '必填文本')->rules([
    Rule::required(),
])->help('文本内容必须填写');
CODE;
        $this->code('text_required-code', 'Code@必填文本')->default($code);
        $this->divider();

        $this->text('text_with_datalist', '有 Data List 的输入框')->rules([
            Rule::required(),
        ])->datalist([
            'Db1', 'Db2', 'Db3',
        ])->placeholder('有 Data List 的输入框')->help('有 Data List 的输入框');
        // 添加 code 代码
        $code = <<<CODE
\$this->text('text_with_datalist', '有 Data List 的输入框')->rules([
    Rule::required(),
])->datalist([
    'Db1', 'Db2', 'Db3',
])->placeholder('有 Data List 的输入框')->help('有 Data List 的输入框');
CODE;
        $this->code('text_with_datalist-code', 'Code@有 Data List 的输入框')->default($code);
        $this->divider();

        $this->text('alpha', '字母')->rules([
            Rule::alpha(),
            Rule::nullable(),
        ])->help('文本内容必须完全由字母组成');
        // 添加 code 代码
        $code = <<<CODE
\$this->text('alpha', '字母')->rules([
    Rule::alpha(),
    Rule::nullable(),
])->help('文本内容必须完全由字母组成');
CODE;
        $this->code('alpha-code', 'Code@字母')->default($code);
        $this->divider();

        $this->text('dash', 'Dash')->rules([
            Rule::alphaDash(),
            Rule::nullable(),
        ])->help('文本内容可以包含字母 数字以及破折号和下划线组成');
        // 添加 code 代码
        $code = <<<CODE
\$this->text('dash', 'Dash')->rules([
    Rule::alphaDash(),
    Rule::nullable(),
])->help('文本内容可以包含字母 数字以及破折号和下划线组成');
CODE;
        $this->code('dash-code', 'Code@Dash')->default($code);
        $this->divider();

        $this->text('between', 'Between')->rules([
            Rule::numeric(),
            Rule::between(1, 3),
            Rule::nullable(),
        ])->help('文本输入值必须在1-3之间');
        // 添加 code 代码
        $code = <<<CODE
\$this->text('between', 'Between')->rules([
    Rule::numeric(),
    Rule::between(1, 3),
    Rule::nullable(),
])->help('文本输入值必须在1-3之间');
CODE;
        $this->code('between-code', 'Code@Between')->default($code);
        $this->divider();

        $this->text('boolean', 'Bool类型')->rules([
            Rule::boolean(),
            Rule::nullable(),
        ])->help('需输入可以转换为bool类型值');
        // 添加 code 代码
        $code = <<<CODE
\$this->text('boolean', 'Bool类型')->rules([
    Rule::boolean(),
    Rule::nullable(),
])->help('需输入可以转换为bool类型值');
CODE;
        $this->code('boolean-code', 'Code@Bool类型')->default($code);
        $this->divider();

        $this->text('email', 'Email')->rules([
            Rule::email(),
            Rule::nullable(),
        ])->help('输入值需符合邮箱格式');
        // 添加 code 代码
        $code = <<<CODE
\$this->text('email', 'Email')->rules([
    Rule::email(),
    Rule::nullable(),
])->help('输入值需符合邮箱格式');
CODE;
        $this->code('email-code', 'Code@Email')->default($code);
        $this->divider();

        $this->text('int', 'Int')->rules([
            Rule::integer(),
            Rule::nullable(),
        ])->help('输入框必须是整数类型');
        // 添加 code 代码
        $code = <<<CODE
\$this->text('int', 'Int')->rules([
    Rule::integer(),
    Rule::nullable(),
])->help('输入框必须是整数类型');
CODE;
        $this->code('int-code', 'Code@Int')->default($code);
        $this->divider();

        $this->text('in', 'In')->rules([
            Rule::in(['a', 'b']),
            Rule::nullable(),
        ])->help('输入内容必须是 a 或 b');
        // 添加 code 代码
        $code = <<<CODE
\$this->text('in', 'In')->rules([
    Rule::in(['a', 'b']),
    Rule::nullable(),
])->help('输入内容必须是 a 或 b');
CODE;
        $this->code('in-code', 'Code@In')->default($code);
        $this->divider();

        $this->text('json', 'Json')->rules([
            Rule::json(),
            Rule::nullable(),
        ])->help('文本内容必须是json格式');
        // 添加 code 代码
        $code = <<<CODE
\$this->text('json', 'Json')->rules([
    Rule::json(),
    Rule::nullable(),
])->help('文本内容必须是json格式');
CODE;
        $this->code('kaka-code', 'Code@kaka')->default($code);
        $this->divider();

        $this->text('max', 'Max')->rules([
            Rule::numeric(),
            Rule::max(2),
            Rule::nullable(),
        ])->help('输入值需不大于2');
        // 添加 code 代码
        $code = <<<CODE
\$this->text('max', 'Max')->rules([
    Rule::numeric(),
    Rule::max(2),
    Rule::nullable(),
])->help('输入值需不大于2');
CODE;
        $this->code('max-code', 'Code@Max')->default($code);
        $this->divider();

        $this->text('min', 'Min')->rules([
            Rule::nullable(),
            Rule::numeric(),
            Rule::min(2),
        ])->help('输入值需不小于2');
        // 添加 code 代码
        $code = <<<CODE
\$this->text('min', 'Min')->rules([
    Rule::nullable(),
    Rule::numeric(),
    Rule::min(2),
])->help('输入值需不小于2');
CODE;
        $this->code('min-code', 'Code@Min')->default($code);
        $this->divider();

        $this->text('not_in', 'Not_in')->rules([
            Rule::notIn(['a', 'b']),
            Rule::nullable(),
        ])->help('输入值必须不为 a 或 b');
        // 添加 code 代码
        $code = <<<CODE
\$this->text('not_in', 'Not_in')->rules([
    Rule::notIn(['a', 'b']),
    Rule::nullable(),
])->help('输入值必须不为 a 或 b');
CODE;
        $this->code('not_in-code', 'Code@Not_in')->default($code);
        $this->divider();

        $this->text('numeric', 'Numeric')->rules([
            Rule::numeric(),
            Rule::nullable(),
        ])->help('数值输入框');
        // 添加 code 代码
        $code = <<<CODE
\$this->text('numeric', 'Numeric')->rules([
    Rule::numeric(),
    Rule::nullable(),
])->help('数值输入框');
CODE;
        $this->code('numeric-code', 'Code@Numeric')->default($code);
        $this->divider();

        $this->text('regex', 'Regex')->rules([
            Rule::regex('/[a-z]{1,}/'),
            Rule::nullable(),
        ])->help('输入内容需符合 a-z 至少1位组成');
        // 添加 code 代码
        $code = <<<CODE
\$this->text('regex', 'Regex')->rules([
    Rule::regex('/[a-z]{1,}/'),
    Rule::nullable(),
])->help('输入内容需符合 a-z 至少1位组成');
CODE;
        $this->code('regex-code', 'Code@Regex')->default($code);
        $this->divider();

        $this->text('url', 'URL')->rules([
            Rule::url(),
            Rule::nullable(),
        ])->help('输入内容需符合URL');
        // 添加 code 代码
        $code = <<<CODE
\$this->text('url', 'URL')->rules([
    Rule::url(),
    Rule::nullable(),
])->help('URL输入框');
CODE;
        $this->code('url-code', 'Code@URL')->default($code);
        $this->divider();

        $this->text('alpha')->rules([
            Rule::alpha(),
            Rule::nullable(),
        ])->help('Laravel Alpha 规则, 全部为字母');
        // 添加 code 代码
        $code = <<<CODE
\$this->text('alpha')->rules([
    Rule::alpha(),
    Rule::nullable(),
])->help('Laravel Alpha 规则, 全部为字母');
CODE;
        $this->code('url-code', 'Code@URL')->default($code);
        $this->divider();

        $this->text('alpha_dash')->rules([
            Rule::alphaDash(),
            Rule::nullable(),
        ])->help('Laravel Alpha 规则, 全部为字母或者下划线');
        // 添加 code 代码
        $code = <<<CODE
\$this->text('alpha_dash')->rules([
    Rule::alphaDash(),
    Rule::nullable(),
])->help('Laravel Alpha 规则, 全部为字母或者下划线');
CODE;
        $this->code('url-code', 'Code@URL')->default($code);
    }
}
