<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Factory;

use Exception;
use OpenTracing\NoopTracer;
use OpenTracing\Tracer;
use Psr\Log\LoggerInterface;
use Zipkin\Endpoint;
use Zipkin\Reporters\Http;
use Zipkin\TracingBuilder;
use ZipkinOpenTracing\Tracer as ZipkinTracer;

final class ZipkinTracerFactory implements TracerFactory
{
    private LoggerInterface $logger;
    private AgentHostResolver $agentHostResolver;
    private SamplerFactory $samplerFactory;

    public function __construct(
        AgentHostResolver $agentHostResolver,
        SamplerFactory $samplerFactory,
        LoggerInterface $logger
    ) {
        $this->agentHostResolver = $agentHostResolver;
        $this->logger = $logger;
        $this->samplerFactory = $samplerFactory;
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @param mixed $samplerValue
     */
    public function create(
        string $projectName,
        string $agentHost,
        string $agentPort,
        string $samplerClass,
        $samplerValue
    ): Tracer {
        $tracer = new NoopTracer();

        try {
            $this->agentHostResolver->ensureAgentHostIsResolvable($agentHost);
            $endpoint = Endpoint::create($projectName, gethostbyname($agentHost), null, (int) $agentPort);
            $reporter = new Http();
            $samplerValue = json_decode($samplerValue);
            $sampler = $this->samplerFactory->createSampler($samplerClass, $samplerValue);
            $tracing = TracingBuilder::create()
                ->havingLocalEndpoint($endpoint)
                ->havingSampler($sampler)
                ->havingReporter($reporter)
                ->build();

            $tracer = new ZipkinTracer($tracing);
        } catch (Exception $exception) {
            $this->logger->warning(self::class . ': ' . $exception->getMessage());
        }

        return $tracer;
    }
}
