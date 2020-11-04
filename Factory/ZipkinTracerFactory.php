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
    private $logger;
    private $agentHostResolver;
    private $samplerFactory;
    private $zipkinUrl;
    private $zipkinConnectionConfig;

    public function __construct(
        AgentHostResolver $agentHostResolver,
        SamplerFactory $samplerFactory,
        LoggerInterface $logger,
        string $zipkinUrl = "http://localhost:9411",
        array $zipkinConnectionConfig = []
    ) {
        $this->agentHostResolver = $agentHostResolver;
        $this->logger = $logger;
        $this->samplerFactory = $samplerFactory;
        $this->zipkinUrl = $zipkinUrl;
        $this->zipkinConnectionConfig = $zipkinConnectionConfig;
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
            $reporter = new Http(null, array_merge($this->zipkinConnectionConfig, ['endpoint_url' => $this->zipkinUrl . '/api/v2/spans']));
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
