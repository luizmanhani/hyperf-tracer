<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf + OpenCodeCo
 *
 * @link     https://opencodeco.dev
 * @document https://hyperf.wiki
 * @contact  leo@opencodeco.dev
 * @license  https://github.com/opencodeco/hyperf-metric/blob/main/LICENSE
 */

namespace Hyperf\Tracer\Adapter\Reporter;

use Hyperf\Contract\StdoutLoggerInterface;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Zipkin\Reporter;
use Zipkin\Reporters\Http;

use function Hyperf\Support\make;

class ReporterFactory
{
    public function __construct(
        private ContainerInterface $container
    ) {
    }

    public function make(array $option = []): Reporter
    {
        $class = $option['class'] ?? '';
        $constructor = $option['constructor'] ?? [];

        if (! class_exists($class)) {
            throw new RuntimeException(sprintf('Class %s is not exists.', $class));
        }

        if (! is_a($class, Reporter::class, true)) {
            throw new RuntimeException('Unsupported reporter.');
        }

        if ($class === Http::class) {
            $constructor['requesterFactory'] = $this->container->get(HttpClientFactory::class);
        }

        if ($this->container->has(StdoutLoggerInterface::class)) {
            $constructor['logger'] = $this->container->get(StdoutLoggerInterface::class);
        }

        return make($class, $constructor);
    }
}
