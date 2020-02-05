<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\Factory;

use Auxmoney\OpentracingBundle\Factory\AgentHostResolver;
use Auxmoney\OpentracingBundle\Factory\ZipkinTracerFactory;
use OpenTracing\NoopTracer;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use RuntimeException;
use ZipkinOpenTracing\Tracer;

class ZipkinTracerFactoryTest extends TestCase
{
    private $agentHostResolver;
    private $logger;
    private $projectName;
    private $agentHost;
    private $agentPort;
    private $subject;

    public function setUp()
    {
        parent::setUp();
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->agentHostResolver = $this->prophesize(AgentHostResolver::class);
        $this->projectName = 'project name';
        $this->agentHost = 'localhost';
        $this->agentPort = '9411';

        $this->subject = new ZipkinTracerFactory($this->agentHostResolver->reveal(), $this->logger->reveal());
    }

    public function testCreateSuccess(): void
    {
        $this->agentHostResolver->resolveAgentHost('localhost')->shouldBeCalled();
        $this->logger->warning(Argument::type('string'))->shouldNotBeCalled();

        self::assertInstanceOf(Tracer::class, $this->subject->create($this->projectName, $this->agentHost, $this->agentPort));
    }

    public function testCreateResolvingFailed(): void
    {
        $this->agentHostResolver->resolveAgentHost('localhost')->shouldBeCalled()->willThrow(new RuntimeException('resolving failer'));
        $this->logger->warning(Argument::containingString('resolving failer'))->shouldBeCalledOnce();

        self::assertInstanceOf(
            NoopTracer::class,
            $this->subject->create($this->projectName, $this->agentHost, $this->agentPort)
        );
    }

    public function testCreateException(): void
    {
        $this->logger->warning(Argument::containingString('Invalid port.'))->shouldBeCalledOnce();

        self::assertInstanceOf(
            NoopTracer::class,
            $this->subject->create($this->projectName, 'localhost', '70000')
        );
    }
}
