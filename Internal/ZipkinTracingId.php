<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Internal;

use Auxmoney\OpentracingBundle\Service\Tracing;
use Zipkin\Propagation\B3;

class ZipkinTracingId implements TracingId
{
    private $tracing;

    public function __construct(Tracing $tracing)
    {
        $this->tracing = $tracing;
    }

    public function getAsString(): string
    {
        $context = $this->tracing->injectTracingHeadersIntoCarrier([]);
        $traceHeaderName = strtolower(B3::TRACE_ID_NAME);
        return $context[$traceHeaderName] ?? 'none';
    }
}
