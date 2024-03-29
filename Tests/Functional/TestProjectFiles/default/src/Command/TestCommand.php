<?php

declare(strict_types=1);

namespace App\Command;

use Auxmoney\OpentracingBundle\Internal\TracingId;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends Command
{
    private $tracingId;

    public function __construct(TracingId $tracingId)
    {
        parent::__construct('test:zipkin');
        $this->setDescription('some fancy command description');
        $this->tracingId = $tracingId;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln($this->tracingId->getAsString());
        return 0;
    }
}
