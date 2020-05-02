<?php declare(strict_types=1);

namespace Cspray\Yape\Cli;

use Cspray\Yape\Cli\InputDefinition\CreateDbalTypeCommandDefinition;
use Cspray\Yape\Cli\Internal\ApplicationConfiguration;
use Cspray\Yape\Cli\Internal\DbalTypeCodeGenerator;
use Cspray\Yape\Cli\Internal\DbalTypeDefinition;
use Cspray\Yape\Cli\Internal\DbalTypeDefinitionFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class CreateDbalTypeCommand extends AbstractCodeGeneratorCommand {

    protected static $defaultName = 'create-enum-dbal-type';

    private $codeGenerator;
    private $factory;

    public function __construct(DbalTypeCodeGenerator $codeGenerator, DbalTypeDefinitionFactory $factory, ApplicationConfiguration $configuration) {
        parent::__construct(
            $configuration,
            function(InputInterface $input) {
                return $this->factory->fromConsole($input);
            },
            function(DbalTypeDefinition $definition) {
                return $this->codeGenerator->generate($definition);
            }
        );
        $this->codeGenerator = $codeGenerator;
        $this->factory = $factory;
    }

    protected function configure() {
        $this->setDescription('A PHP7+ code generator to create custom Doctrine DBAL Types from generated enums.')
            ->setHelp($this->getHelpText())
            ->setDefinition(new CreateDbalTypeCommandDefinition());
    }

    private function getHelpText() : string {
        return <<<HELP
The dbalTypeClass should be a fully qualified class name which MUST have at least 1 namespace part. Simply providing only a 
class name will result in an invalid DBAL Type definition and an error.

The enumClass should be a fully-qualified class name of an enum that MUST already exist. If the enum cannot be loaded will 
result in an invalid DBAL Type definition and an error.

The dbalType should be a string value that represents the name of your DBAL Type to Doctrine. For example, if you were going 
to refer to the type as 'compass' in your entity configurations then the value of this argument should be 'compass'.
HELP;
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        return $this->executeCodeGeneration($input, $output)->getStatusCode();
    }

    protected function getGeneratedCodeDescriptor() : string {
        return 'DBAL Type';
    }

    protected function getDefaultOutputDir() : string {
        return $this->getConfig()->getDefaultDbalTypeOutputDir();
    }
}