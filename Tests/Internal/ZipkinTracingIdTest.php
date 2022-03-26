<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\Internal;

use Auxmoney\OpentracingBundle\Internal\ZipkinTracingId;
use Auxmoney\OpentracingBundle\Service\Tracing;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ZipkinTracingIdTest extends TestCase
{
    use ProphecyTrait;

    /** @var Tracing|ObjectProphecy */
    private $tracing;
    private ZipkinTracingId $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->tracing = $this->prophesize(Tracing::class);

        $this->subject = new ZipkinTracingId($this->tracing->reveal());
    }

    public function testGetAsStringSuccess(): void
    {
        $this->tracing->injectTracingHeadersIntoCarrier([])->shouldBeCalled()->willReturn(['x-b3-traceid' => 'abc']);

        self::assertSame('abc', $this->subject->getAsString());
    }

    public function testGetAsStringNoHeader(): void
    {
        $this->tracing->injectTracingHeadersIntoCarrier([])->shouldBeCalled()->willReturn([]);

        self::assertSame('none', $this->subject->getAsString());
    }
}
