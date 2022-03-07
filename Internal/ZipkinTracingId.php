<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Internal;

use Auxmoney\OpentracingBundle\Service\Tracing;

class ZipkinTracingId implements TracingId
{
    private Tracing $tracing;

    public function __construct(Tracing $tracing)
    {
        $this->tracing = $tracing;
    }

    public function getAsString(): string
    {
        $context = $this->tracing->injectTracingHeadersIntoCarrier([]);
        $traceHeaderName = 'x-b3-traceid';
        return $context[$traceHeaderName] ?? 'none';
    }
}
