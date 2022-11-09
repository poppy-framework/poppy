<?php

namespace Poppy\SensitiveWord\Tests;

use Poppy\Core\Redis\RdsDb;
use Poppy\SensitiveWord\Action\Word;
use Poppy\SensitiveWord\Classes\Sensitive\Dict;
use Poppy\SensitiveWord\Classes\Sensitive\Words;
use Poppy\SensitiveWord\Models\SysSensitiveWord;
use Poppy\System\Tests\Base\SystemTestCase;

class WordsTest extends SystemTestCase
{
    protected string $banWord = '暴政';

    public function testClear()
    {
        RdsDb::instance()->del('tag:py-sensitive-word:*');
    }

    public function testFilter(): void
    {
        $Word = new Word();
        if (!SysSensitiveWord::where('word', $this->banWord)->exists()) {
            if (!$Word->establish([
                'word' => $this->banWord,
            ])) {
                $this->fail($Word->getError());
            }
        }
        $id = SysSensitiveWord::where('word', $this->banWord)->value('id');

        (new Dict())->build();

        $value = sensitive_words('暴政');
        $this->assertFalse($value);
        $value = sensitive_words('嬴政暴政', Words::TYPE_WORDS);
        $this->assertEquals('暴政', $value[0] ?? '');
        $value = sensitive_words('嬴政暴政', Words::TYPE_REPLACE);
        $this->assertEquals('嬴政**', $value);

        if (!$Word->delete($id)) {
            $this->fail($Word->getError());
        }

        if (!$Word->establish([
            'word' => $this->banWord,
        ])) {
            $this->fail($Word->getError());
        }

        if (!$Word->delete([$id])) {
            $this->fail($Word->getError());
        }
    }
}
