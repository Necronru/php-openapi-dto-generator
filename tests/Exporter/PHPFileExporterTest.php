<?php

namespace Necronru\DtoGenerator\Tests\Exporter;

use Necronru\DtoGenerator\Exporter\PHPFileExporter;
use Necronru\DtoGenerator\Provider\OpenApiV3;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class PHPFileExporterTest extends TestCase
{
    public function testExport(): void
    {
        $spec = [
            'components' => [
                'schemas' => [
                    'Test' => [
                        'required' => [
                            'id',
                            'type',
                        ],
                        'properties' => [
                            'id' => [
                                'type' => 'integer',
                                'nullable' => true,
                            ],

                            'type' => [
                                'type' => 'string'
                            ]
                        ],
                    ],
                ],
            ],
        ];

        $logger = $this->createMock(LoggerInterface::class);

        $provider = new OpenApiV3($spec);

        $outputDir = __DIR__ . '/../../cache';

        $exporter = new PHPFileExporter($provider, $logger);
        $exporter->export($outputDir);

        $this->assertFileExists($outputDir . '/Test.php');
    }

}