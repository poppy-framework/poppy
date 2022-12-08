<?php

declare(strict_types = 1);

namespace Poppy\SensitiveWord\Tests;

use Poppy\Framework\Application\TestCase;
use Poppy\SensitiveWord\Action\Word;
use Poppy\SensitiveWord\Classes\Sensitive\Dict;
use Poppy\SensitiveWord\Classes\Sensitive\Words;
use Poppy\SensitiveWord\Exceptions\DirectoryNotFoundException;
use Poppy\SensitiveWord\Models\SysSensitiveWord;

class WordsTest extends TestCase
{
    protected string $banWord = '暴政';

    protected function setUp(): void
    {
        parent::setUp();
        sys_tag('py-sensitive-word')->clear();
    }

    /**
     * @throws DirectoryNotFoundException
     */
    public function testFilter(): void
    {
        $Word = new Word();
        if (!SysSensitiveWord::where('word', $this->banWord)->exists()) {
            if (!$Word->establish([
                'word' => $this->banWord,
            ])) {
                $this->fail($Word->getError()->getMessage());
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
            $this->fail($Word->getError()->getMessage());
        }

        if (!$Word->establish([
            'word' => $this->banWord,
        ])) {
            $this->fail($Word->getError()->getMessage());
        }

        if (!$Word->delete([$id])) {
            $this->fail($Word->getError()->getMessage());
        }
    }
}
