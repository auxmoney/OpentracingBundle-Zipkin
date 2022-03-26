<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\Factory;

use Auxmoney\OpentracingBundle\Factory\ZipkinSamplerFactory;
use Exception;
use PHPUnit\Framework\TestCase;
use Zipkin\Samplers\BinarySampler;
use Zipkin\Samplers\PercentageSampler;

class ZipkinSamplerFactoryTest  extends TestCase
{
    private ZipkinSamplerFactory $subject;

    public function setUp(): void
    {
        $this->subject = new ZipkinSamplerFactory();
    }

    /**
     * @throws Exception
     */
    public function testCreateActiveBinarySampler(): void
    {
        $binarySampler = $this->subject->createSampler(BinarySampler::class, true);
        self::assertTrue($binarySampler->isSampled('dummyTraceId'));
    }

    /**
     * @throws Exception
     */
    public function testCreateInactiveBinarySampler(): void
    {
        $binarySampler = $this->subject->createSampler(BinarySampler::class, false);
        self::assertFalse($binarySampler->isSampled('dummyTraceId'));
    }

    /**
     * @throws Exception
     */
    public function testCreateBinarySamplerWithoutStringValue(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('sampler value for the binary sampler must be an boolean, string given');
        $this->subject->createSampler(BinarySampler::class, 'true');
    }

    /**
     * @throws Exception
     */
    public function testCreate100PercentageSampler(): void
    {
        $percentageSampler = $this->subject->createSampler(PercentageSampler::class, 1.0);
        self::assertTrue($percentageSampler->isSampled('dummyTraceId'));
    }

    /**
     * @throws Exception
     */
    public function testCreate0PercentageSampler(): void
    {
        $percentageSampler = $this->subject->createSampler(PercentageSampler::class, 0.0);
        self::assertFalse($percentageSampler->isSampled('dummyTraceId'));
    }

    /**
     * @throws Exception
     */
    public function testCreateUnknownSampler(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('unknown sampler class unknown given');

        $this->subject->createSampler('unknown', 0.0);
    }
}
