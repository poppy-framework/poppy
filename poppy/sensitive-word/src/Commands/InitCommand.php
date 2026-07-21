<?php

declare(strict_types = 1);

namespace Poppy\SensitiveWord\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Poppy\SensitiveWord\Models\SysSensitiveWord;
use Throwable;

class InitCommand extends Command
{
    protected $name = 'py-sensitive-word:init';

    public function handle()
    {
        // init to db
        $path = poppy_path('poppy.sensitive-word', 'resources/def/words.txt');
        try {
            $words = file($path);

            $first = Arr::first($words);
            if (SysSensitiveWord::where('word', $first)->exists()) {
                $this->error('你已经导入了默认数据, 无需重新导入');

                return;
            }
            $this->info('Init Sensitive Word Data ....');
            $words  = array_unique($words);
            $import = [];
            foreach ($words as $word) {
                $import[] = [
                    'word' => trim($word),
                ];
            }
            SysSensitiveWord::insert($import);
            $this->info('Init Sensitive Word Data Success');
        }
        catch (Throwable $e) {
            $this->error($e->getMessage());
        }
    }
}
