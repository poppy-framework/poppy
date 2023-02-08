<?php
declare(strict_types = 1);

namespace Poppy\Im\Rpc;

use Hyperf\Contract\NormalizerInterface;
use Hyperf\Utils\Serializer\Serializer;
use Hyperf\Utils\Serializer\SerializerFactory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                NormalizerInterface::class => new SerializerFactory(Serializer::class),
            ],
        ];
    }
}