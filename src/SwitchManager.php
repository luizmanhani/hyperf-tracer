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
namespace Hyperf\Tracer;

use Hyperf\Context\Context;
use OpenTracing\Span;

class SwitchManager
{
    private array $config
        = [
            'guzzle' => false,
            'redis' => false,
            'db' => false,
            // beta feature, please don't enable 'method' in production environment
            'method' => false,
            'error' => false,
        ];

    /**
     * Apply the configuration to SwitchManager.
     */
    public function apply(array $config): void
    {
        $this->config = array_replace($this->config, $config);
    }

    /**
     * Determine if the tracer is enabled ?
     */
    public function isEnabled(string $identifier): bool
    {
        if (! isset($this->config[$identifier])) {
            return false;
        }

        return $this->config[$identifier] && Context::get('tracer.root') instanceof Span;
    }
}
