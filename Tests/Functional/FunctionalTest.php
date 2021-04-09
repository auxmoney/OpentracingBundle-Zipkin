<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\Functional;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class FunctionalTest extends TestCase
{
    public function testSuccessfulTracing(): void
    {
        $this->setupTestProject();

        $p = new Process(['symfony', 'console', 'test:zipkin'], 'build/testproject');
        $p->mustRun();
        $traceId = trim($p->getOutput());
        self::assertNotEmpty($traceId);

        $spans = $this->getTraceFromZipkinAPI($traceId);
        self::assertCount(1, $spans);
        self::assertSame('test:zipkin', $spans[0]['name']);
    }

    public function setUp(): void
    {
        parent::setUp();

        $p = new Process(['docker', 'start', 'zipkin']);
        $p->mustRun();

        sleep(10);
    }

    protected function tearDown(): void
    {
        $p = new Process(['git', 'reset', '--hard', 'reset'], 'build/testproject');
        $p->mustRun();
        $p = new Process(['docker', 'stop', 'zipkin']);
        $p->mustRun();

        parent::tearDown();
    }

    private function getTraceFromZipkinAPI(string $traceId): array
    {
        $client = new Client();
        $response = $client->get(sprintf('http://localhost:9411/zipkin/api/v2/trace/%s', $traceId));
        return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }

    private function setupTestProject(): void
    {
        $filesystem = new Filesystem();
        $filesystem->mirror('Tests/Functional/TestProjectFiles/default/', 'build/testproject/');

        $p = new Process(['composer', 'dump-autoload'], 'build/testproject');
        $p->mustRun();
        $p = new Process(['symfony', 'console', 'cache:clear'], 'build/testproject');
        $p->mustRun();
    }
}
