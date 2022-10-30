<?php

namespace Demo\Forms;

use Poppy\Framework\Validation\Rule;

class FormRadio extends FormBaseWidget
{

    /**
     * 表单标题
     * @var string
     */
    protected $title = '单选框(Radio)';

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->radio('radio_ball', '球类')->options([
            'football'     => '足球',
            'basketball'   => '篮球',
            'table_tennis' => '乒乓球',
        ])
            ->help('
            因为是必选框, 在Layui 下无法进行渲染 required, 所以这里无法提交, 
            报 An invalid form control with name=\'radio_ball\' is not 
            focusable.所以 Ajax 在这里使用服务器验证'
            )
            ->rules([
                Rule::required(),
            ], [
                'required' => '必须的',
            ]);
        // 添加 code 代码
        $code = <<<CODE
\$this->radio('radio_ball', '球类')->options([
    'football'     => '足球',
    'basketball'   => '篮球',
    'table_tennis' => '乒乓球',
])
    ->help('
    因为是必选框, 在Layui 下无法进行渲染 required, 所以这里无法提交, 
    报 An invalid form control with name=\'radio_ball\' is not 
    focusable.所以 Ajax 在这里使用服务器验证'
    )
    ->rules([
        Rule::required(),
    ], [
        'required' => '必须的',
    ]);
CODE;
        $this->code('radio_ball-code', 'Code@球类')->default($code);
        $this->divider();

        $this->radio('sex', '性别')->options([
            'boy'  => '男',
            'girl' => '女',
        ])->default('girl')->help('设置默认选项');
        // 添加 code 代码
        $code = <<<CODE
\$this->radio('sex', '性别')->options([
    'boy'  => '男',
    'girl' => '女',
])->default('girl')->help('设置默认选项');
CODE;
        $this->code('sex-code', 'Code@性别')->default($code);
        $this->divider();

        $this->radio('stacked', 'Radio 竖向')->options([
            1 => 'Test01',
            2 => 'Test02',
        ])->stacked()->checked('Test01')->help('竖排显示单选且附加默认值');
        // 添加 code 代码
        $code = <<<CODE
\$this->radio('stacked', 'Radio 竖向')->options([
    1 => 'Test01',
    2 => 'Test02',
])->stacked()->checked('Test01')->help('竖排显示单选且附加默认值');
CODE;
        $this->code('stacked-code', 'Code@Radio 竖向')->default($code);
        $this->divider();

        $this->radio('values', 'Radio Values')->stacked()->values([
            'Test2', 'Test3',
        ])->checked('Test3')->help('这里Values 以及 checked 不能为键, 只能为值');
        // 添加 code 代码
        $code = <<<CODE
\$this->radio('values', 'Radio Values')->stacked()->values([
    'Test2', 'Test3',
])->checked('Test3')->help('这里Values 以及 checked 不能为键, 只能为值');
CODE;
        $this->code('values-code', 'Code@Radio Values')->default($code);

    }
}