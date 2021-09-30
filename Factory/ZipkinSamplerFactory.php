<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Factory;

use Exception;
use Zipkin\Sampler;
use Zipkin\Samplers\BinarySampler;
use Zipkin\Samplers\PercentageSampler;

final class ZipkinSamplerFactory implements SamplerFactory
{
    /**
     * {@inheritDoc}
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function createSampler(string $samplerClass, $samplerValue): Sampler
    {
        if ($samplerClass === BinarySampler::class) {
            return $this->createBinarySampler($samplerValue);
        }

        if ($samplerClass === PercentageSampler::class) {
            return PercentageSampler::create($samplerValue);
        }

        throw new Exception(sprintf('unknown sampler class %s given', $samplerClass));
    }

    /**
     * @param mixed $samplerValue
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @throws Exception
     */
    private function createBinarySampler($samplerValue): BinarySampler
    {
        if (!is_bool($samplerValue)) {
            throw new Exception(
                sprintf('sampler value for the binary sampler must be an boolean, %s given', gettype($samplerValue))
            );
        }

        if ($samplerValue) {
            return BinarySampler::createAsAlwaysSample();
        }

        return BinarySampler::createAsNeverSample();
    }
}
