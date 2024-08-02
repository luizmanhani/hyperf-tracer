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

use GuzzleHttp\Client;
use Hyperf\Tracer\Aspect\CoroutineAspect;
use Hyperf\Tracer\Aspect\CreateTraceContextAspect;
use Hyperf\Tracer\Aspect\ElasticserachAspect;
use Hyperf\Tracer\Aspect\GrpcAspect;
use Hyperf\Tracer\Aspect\HttpClientAspect;
use Hyperf\Tracer\Aspect\MongoCollectionAspect;
use Hyperf\Tracer\Aspect\RedisAspect;
use Hyperf\Tracer\Aspect\RpcAspect;
use Hyperf\Tracer\Aspect\TraceAnnotationAspect;
use Hyperf\Tracer\Listener\DbQueryExecutedListener;
use Hyperf\Tracer\Middleware\TraceMiddleware;
use Jaeger\SpanContext;
use Jaeger\ThriftUdpTransport;
use OpenTracing\GlobalTracer;
use OpenTracing\Tracer;
use Zipkin\Propagation\Map;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                Tracer::class => TracerFactory::class,
                SwitchManager::class => SwitchManagerFactory::class,
                SpanTagManager::class => SpanTagManagerFactory::class,
                Client::class => Client::class,
            ],
            'listeners' => [
                DbQueryExecutedListener::class,
            ],
            'middlewares' => [
                TraceMiddleware::class,
            ],
            'annotations' => [
                'scan' => [
                    'class_map' => [
                        GlobalTracer::class => __DIR__ . '/../class_map/GlobalTracer.php',
                        Map::class => __DIR__ . '/../class_map/Map.php',
                        ThriftUdpTransport::class => __DIR__ . '/../class_map/ThriftUdpTransport.php',
                        SpanContext::class => __DIR__ . '/../class_map/SpanContext.php',
                    ],
                ],
            ],
            'aspects' => [
                CoroutineAspect::class,
                CreateTraceContextAspect::class,
                ElasticserachAspect::class,
                GrpcAspect::class,
                HttpClientAspect::class,
                RedisAspect::class,
                RpcAspect::class,
                TraceAnnotationAspect::class,
                MongoCollectionAspect::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for tracer.',
                    'source' => __DIR__ . '/../publish/opentracing.php',
                    'destination' => BASE_PATH . '/config/autoload/opentracing.php',
                ],
            ],
        ];
    }
}
