<?php

declare(strict_types=1);

namespace App\Console\Command;

use Doctrine\ORM\Tools\EntityGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager as DoctrineEntityManager;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Doctrine\ORM\Proxy\ProxyFactory;
use Oryx\ORM\Mapping\XmlDriver;
use Symfony\Component\Dotenv\Dotenv;
use ReflectionProperty;

/**
 * Generates entity classes and method stubs from your mapping information.
 */
class GenerateEntitiesCommand extends Command
{
    protected static $defaultName = 'orm:generate:entities';
    protected static $defaultDescription = 'Generates entity classes and method stubs from mapping information';

    private string $outputDir;

    public function __construct(string $outputDir = null)
    {
        $this->outputDir = $outputDir ?? (getcwd() . '/src/Entity');

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'output-dir',
                InputArgument::OPTIONAL,
                'The directory where to write the generated classes.',
                $this->outputDir
            )
            ->addOption(
                'filter',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Generate only the classes matching the filter. '
                . 'A filter could be a namespace (ending with \*) or a class name.'
            )
            ->addOption(
                'no-backup',
                null,
                InputOption::VALUE_NONE,
                'If set, no backup of the existing file will be created.'
            )
            ->addOption(
                'update-if-empty',
                null,
                InputOption::VALUE_NONE,
                'If set, EntityGenerator will not overwrite existing files.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Get the project root (current working directory when the command is run)
        $projectRoot = getcwd();

        // Load environment variables from the project root
        $dotenv = new Dotenv();
        $dotenv->bootEnv($projectRoot . '/.env');

        // Set up database connection (same as cli-config.php)
        $connectionParams = [
            'driver' => 'pdo_mysql',
            'host' => $_ENV['DB_HOST'],
            'port' => $_ENV['DB_PORT'],
            'dbname' => $_ENV['DB_NAME'],
            'user' => $_ENV['DB_USER'],
            'password' => $_ENV['DB_PASSWORD'],
            'charset' => 'utf8mb4',
        ];

        try {
            $connection = DriverManager::getConnection($connectionParams);
        } catch (\Throwable $e) {
            $io->warning('Could not connect to the database: ' . $e->getMessage());
            $io->warning('Using an in-memory SQLite database for metadata generation.');

            // Fallback to an in-memory SQLite database
            $connectionParams = [
                'driver' => 'pdo_sqlite',
                'memory' => true,
            ];
            $connection = DriverManager::getConnection($connectionParams);
        }

        // Create Doctrine ORM configuration
        $doctrineConfig = new Configuration();

        // Set up metadata driver using our XML driver
        $xmlDriver = new XmlDriver([
            $projectRoot . '/src/Schema/definitions',
        ]);
        $doctrineConfig->setMetadataDriverImpl($xmlDriver);

        // Proxy configuration - matching EntityManager.php settings
        $doctrineConfig->setAutoGenerateProxyClasses(
            ProxyFactory::AUTOGENERATE_NEVER
        );
        $doctrineConfig->setProxyDir(sys_get_temp_dir());
        $doctrineConfig->setProxyNamespace('Oryx\ORM\Proxy');

        // Create Doctrine EntityManager instance
        $entityManager = DoctrineEntityManager::create($connection, $doctrineConfig);

        // Create a metadata factory and set the driver by reflection
        $metadataFactory = new ClassMetadataFactory();
        // Set the driver property
        $driverReflection = new ReflectionProperty(ClassMetadataFactory::class, 'driver');
        $driverReflection->setAccessible(true);
        $driverReflection->setValue($metadataFactory, $xmlDriver);
        // Set the initialized flag to true to prevent initialize() from being called
        $initializedReflection = new ReflectionProperty(ClassMetadataFactory::class, 'initialized');
        $initializedReflection->setAccessible(true);
        $initializedReflection->setValue($metadataFactory, true);

        // Get all metadata
        $metadatas = $metadataFactory->getAllMetadata();

        if (empty($metadatas)) {
            $io->warning('No metadata classes to process.');

            return Command::SUCCESS;
        }

        // Apply filter if provided
        $filter = $input->getOption('filter');
        if ($filter) {
            $filteredMetadatas = [];
            foreach ($metadatas as $metadata) {
                foreach ($filter as $pattern) {
                    if (fnmatch($pattern, $metadata->getName())) {
                        $filteredMetadatas[] = $metadata;
                        continue 2;
                    }
                }
            }
            $metadatas = $filteredMetadatas;
        }

        if (empty($metadatas)) {
            $io->warning('No metadata classes match the filter.');

            return Command::SUCCESS;
        }

        $io->title('Generating entity classes');

        $generator = new EntityGenerator();
        $generator->setUpdateEntityIfExists($input->getOption('update-if-empty'));
        $generator->setGenerateStubMethods(true);
        $generator->setRegenerateEntityIfExists(false);
        $generator->setNumSpaces(4);

        $generatedCount = 0;
        foreach ($metadatas as $metadata) {
            $className = $metadata->getName();
            $io->text(sprintf('Processing entity "<info>%s</info>"', $className));

            try {
                $generator->generate([$metadata], $this->outputDir, !$input->getOption('no-backup'));
                $io->text(sprintf('  > Generated <info>%s</info>', $className));
                $generatedCount++;
            } catch (\Throwable $e) {
                $io->error(sprintf('  > Failed to generate entity "<info>%s</info>: %s', $className, $e->getMessage()));
            }
        }

        $io->success(sprintf('Generated %d entity classes', $generatedCount));

        return Command::SUCCESS;
    }
}
