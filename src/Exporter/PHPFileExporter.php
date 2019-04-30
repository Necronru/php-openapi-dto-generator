<?php


namespace Necronru\DtoGenerator\Exporter;


use Necronru\DtoGenerator\ClassDefinition;
use Necronru\DtoGenerator\Provider\OpenApiV3;
use Psr\Log\LoggerInterface;

class PHPFileExporter
{
    /**
     * @var OpenApiV3
     */
    protected $provider;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(OpenApiV3 $provider, LoggerInterface $logger)
    {
        $this->provider = $provider;
        $this->logger = $logger;
    }

    public function export(string $outputDir, string $namespace = '', bool $isDryRun = false): void
    {
        if (!is_dir($outputDir)) {
            if (!mkdir($outputDir, 0777, true) && !is_dir($outputDir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $outputDir));
            }
        }

        $provider = $this->provider;
        $classes = $provider();

        /** @var ClassDefinition[] $classes */
        foreach ($classes as $className => $definition) {
            $fileName     = sprintf('%s/%s.php', $outputDir, $className);
            $phpNamespace = $namespace ? sprintf("\nnamespace %s;\n", $namespace) : '';
            $content      = sprintf("<?php\n%s\n%s\n", $phpNamespace, $definition->getClass());

            if (!$isDryRun) {
                file_put_contents($fileName, $content);
            }
        }
    }
}