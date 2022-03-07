<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\Factory;

use Auxmoney\OpentracingBundle\Factory\AgentHostResolver;
use Auxmoney\OpentracingBundle\Factory\SamplerFactory;
use Auxmoney\OpentracingBundle\Factory\ZipkinTracerFactory;
use OpenTracing\NoopTracer;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Zipkin\Samplers\BinarySampler;
use ZipkinOpenTracing\Tracer;

class ZipkinTracerFactoryTest extends TestCase
{
    use ProphecyTrait;

    private $samplerFactory;
    private $agentHostResolver;
    private $logger;
    private string $projectName;
    private string $agentHost;
    private string $agentPort;
    private ZipkinTracerFactory $subject;
    private string $samplerClass;
    private string $samplerValue;

    public function setUp(): void
    {
        parent::setUp();
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->agentHostResolver = $this->prophesize(AgentHostResolver::class);
        $this->projectName = 'project name';
        $this->agentHost = 'localhost';
        $this->agentPort = '9411';
        $this->samplerClass = BinarySampler::class;
        $this->samplerValue = 'true';
        $this->samplerFactory = $this->prophesize(SamplerFactory::class);

        $this->subject = new ZipkinTracerFactory(
            $this->agentHostResolver->reveal(),
            $this->samplerFactory->reveal(),
            $this->logger->reveal()
        );
    }

    public function testCreateSuccess(): void
    {
        $this->agentHostResolver->ensureAgentHostIsResolvable('localhost')->shouldBeCalled();
        $this->logger->warning(Argument::type('string'))->shouldNotBeCalled();
        $this->samplerFactory->createSampler($this->samplerClass, true)->shouldBeCalled()
            ->willReturn(BinarySampler::createAsAlwaysSample());

        self::assertInstanceOf(
            Tracer::class,
            $this->subject->create(
                $this->projectName,
                $this->agentHost,
                $this->agentPort,
                $this->samplerClass,
                $this->samplerValue
            )
        );
    }

    public function testCreateResolvingFailed(): void
    {
        $this->agentHostResolver->ensureAgentHostIsResolvable('localhost')->shouldBeCalled()->willThrow(
            new RuntimeException('resolving failer')
        );
        $this->logger->warning(Argument::containingString('resolving failer'))->shouldBeCalledOnce();
        $this->samplerFactory->createSampler($this->samplerClass, true)->shouldNotBeCalled();

        self::assertInstanceOf(
            NoopTracer::class,
            $this->subject->create(
                $this->projectName,
                $this->agentHost,
                $this->agentPort,
                $this->samplerClass,
                $this->samplerValue
            )
        );
    }

    public function testCreateException(): void
    {
        $this->samplerFactory->createSampler($this->samplerClass, true)->shouldNotBeCalled()
            ->willReturn(BinarySampler::createAsAlwaysSample());
        $this->logger->warning(Argument::containingString('Invalid port.'))->shouldBeCalledOnce();

        self::assertInstanceOf(
            NoopTracer::class,
            $this->subject->create(
                $this->projectName,
                'localhost',
                '70000',
                $this->samplerClass,
                $this->samplerValue
            )
        );
    }
}
