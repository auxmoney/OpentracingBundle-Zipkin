<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Factory;

use Exception;
use Zipkin\Sampler;

interface SamplerFactory
{
    /**
     * @throws Exception
     * @param mixed $samplerValue
     */
    public function createSampler(string $samplerClass, $samplerValue): Sampler;
}
