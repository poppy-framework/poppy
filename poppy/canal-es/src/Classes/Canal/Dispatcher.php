<?php

declare(strict_types = 1);

namespace Poppy\CanalEs\Classes\Canal;


use Poppy\CanalEs\Classes\Canal\Message\Message;
use Poppy\CanalEs\Classes\Canal\Message\Prepare;
use Poppy\CanalEs\Classes\Es\Document;
use Poppy\Framework\Helper\ArrayHelper;

class Dispatcher
{
    /**
     * @var Prepare
     */
    private $prepare;

    /**
     * @var Document
     */
    private $document;


    /**
     * 回调输出
     * @var callable
     */
    private $output;

    /**
     * Dispatch constructor.
     * @param Message       $messages
     * @param callable|null $output 输出
     */
    public function __construct(Message $messages, callable $output = null)
    {
        $this->prepare  = new Prepare($messages);
        $this->document = new Document();
        $this->output   = $output;
    }


    public function dispatch()
    {
        $output = $this->output;
        if ($records = $this->prepare->records()) {
            // 统计数据
            $new  = array_reduce($records, function ($carry, $item) {
                return array_merge($carry, array_keys($item));
            }, []);
            $desc = ArrayHelper::genKey(array_count_values($new));
            $this->document->bulk($records);
            $output && $output(sys_gen_mk(self::class, 'Records `' . $desc . '` sync to Es'));
        }
        else {
            $output && $output(sys_gen_mk(self::class, 'No Records sync to Es'));
        }
    }
}