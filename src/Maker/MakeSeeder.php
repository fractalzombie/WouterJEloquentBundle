<?php

namespace Maker;

use Illuminate\Database\Seeder as IlluminateSeeder;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\FileManager;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use WouterJ\EloquentBundle\Seeder;

/**
 * @author Wouter de Jong <wouter@wouterj.nl>
 */
class MakeSeeder extends AbstractMaker
{
    private $fileManager;

    public function __construct(FileManager $fileManager)
    {
        $this->fileManager = $fileManager;
    }

    public static function getCommandName(): string
    {
        return 'make:seeder';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->setDescription('Create a new seeder class')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the seeder class')
        ;

        $inputConfig->setArgumentAsNonInteractive('name');
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $className = $input->getArgument('name');

        $stubPath = dirname((new \ReflectionClass(IlluminateSeeder::class))->getFileName()).'/Console/Seeds/stubs';
        $stub = file_get_contents($stubPath.'/seeder.stub');
        $stub = str_replace(['DummyClass', '{{ class }}', '{{class}}'], Str::getShortClassName($className), $stub);
        $stub = str_replace(IlluminateSeeder::class, Seeder::class, $stub);

        if ($namespace = Str::getNamespace($className)) {
            $stub = str_replace('<?php', "<?php\n\nnamespace ".$namespace.';', $stub);
        }

        $path = $this->fileManager->getRelativePathForFutureClass($className);
        if (null === $path) {
            throw new \LogicException(sprintf('Could not determine where to locate the new class "%s", maybe try with a full namespace like "\\My\\Full\\Namespace\\%s"', $className, Str::getShortClassName($className)));
        }

        $this->fileManager->dumpFile($path, $stub);
    }
}
