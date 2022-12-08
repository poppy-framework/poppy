<?php

declare(strict_types = 1);

namespace Poppy\SensitiveWord\Action;

use Exception;
use Poppy\Framework\Classes\Traits\AppTrait;
use Poppy\Framework\Validation\Rule;
use Poppy\SensitiveWord\Classes\PySensitiveWordDef;
use Poppy\SensitiveWord\Models\SysSensitiveWord;
use Poppy\System\Classes\Traits\PamTrait;
use Validator;

/**
 * 敏感词表
 */
class Word
{
    use AppTrait, PamTrait;

    /**
     * @var SysSensitiveWord|null
     */
    protected ?SysSensitiveWord $item = null;

    /**
     * @var string
     */
    protected string $wordTable;

    public function __construct()
    {
        $this->wordTable = (new SysSensitiveWord())->getTable();
    }

    /**
     * 编辑/创建
     * @param array $data
     * @return bool
     */
    public function establish(array $data): bool
    {
        $initDb    = [
            'word' => trim((string) data_get($data, 'word', '')),
        ];
        $validator = Validator::make($initDb, [
            'word' => [
                Rule::required(),
                Rule::string(),
            ],
        ], [], [
            'word' => '敏感词',
        ]);
        if ($validator->fails()) {
            return $this->setError($validator->messages());
        }

        $words = array_filter(explode(PHP_EOL, $initDb['word']));
        if (!count($words)) {
            return $this->setError('没有需要添加的敏感词');
        }
        $existsWords = SysSensitiveWord::whereIn('word', $words)->pluck('word')->toArray();

        $diff = collect();
        collect($words)->diff($existsWords)->each(function ($item) use ($diff) {
            $diff->push([
                'word' => $item,
            ]);
        });

        if (!$diff->count()) {
            return $this->setError('没有需要添加的敏感词, 输入内容存在重复数据');
        }

        SysSensitiveWord::insert($diff->toArray());

        // 移除词典
        sys_cache('py-sensitive-word')->forget(PySensitiveWordDef::ckDict());

        return true;
    }

    /**
     * 删除数据
     * @param array|int $id 敏感词id
     * @return bool|null
     */
    public function delete($id): bool
    {
        $id = (array) $id;
        try {
            SysSensitiveWord::whereIn('id', $id)->delete();
            // 移除词典
            sys_cache('py-sensitive-word')->forget(PySensitiveWordDef::ckDict());
            return true;
        } catch (Exception $e) {
            return $this->setError($e->getMessage());
        }
    }
}