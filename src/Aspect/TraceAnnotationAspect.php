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
namespace Hyperf\Tracer\Aspect;

use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Tracer\Annotation\Trace;
use Hyperf\Tracer\SpanStarter;
use OpenTracing\Tracer;
use Throwable;

class TraceAnnotationAspect extends AbstractAspect
{
    use SpanStarter;

    public array $annotations = [
        Trace::class,
    ];

    public function __construct(private Tracer $tracer)
    {
    }

    /**
     * @return mixed return the value from process method of ProceedingJoinPoint, or the value that you handled
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $source = $proceedingJoinPoint->className . '::' . $proceedingJoinPoint->methodName;
        $metadata = $proceedingJoinPoint->getAnnotationMetadata();
        /** @var Trace $annotation */
        if ($annotation = $metadata->method[Trace::class] ?? null) {
            $name = $annotation->name;
            $tag = $annotation->tag;
        } else {
            $name = $source;
            $tag = 'source';
        }
        $span = $this->startSpan($name);
        $span->setTag($tag, $source);
        try {
            $result = $proceedingJoinPoint->process();
        } catch (Throwable $e) {
            $span->setTag('error', true);
            $span->log(['message', $e->getMessage(), 'code' => $e->getCode(), 'stacktrace' => $e->getTraceAsString()]);
            throw $e;
        } finally {
            $span->finish();
        }
        return $result;
    }
}
