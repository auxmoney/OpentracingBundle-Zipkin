<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Factory;

use Exception;
use OpenTracing\NoopTracer;
use OpenTracing\Tracer;
use Psr\Log\LoggerInterface;
use Zipkin\Endpoint;
use Zipkin\Reporters\Http;
use Zipkin\Samplers\BinarySampler;
use Zipkin\TracingBuilder;
use ZipkinOpenTracing\Tracer as ZipkinTracer;

class ZipkinTracerFactory implements TracerFactory
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function create(string $projectName, string $agentHost, string $agentPort): Tracer
    {
        $tracer = new NoopTracer();

        if (!dns_get_record($agentHost) && !filter_var($agentHost, FILTER_VALIDATE_IP)) {
            $this->logger->warning(self::class . ': could not resolve agent host "' . $agentHost . '"');
            return $tracer;
        }

        try {
            $endpoint = Endpoint::create($projectName, gethostbyname($agentHost), null, (int) $agentPort);
            $reporter = new Http();
            $sampler = BinarySampler::createAsAlwaysSample();
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
