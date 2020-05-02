<?php declare(strict_types=1);

namespace Cspray\Yape\Cli;

use Cspray\Yape\Cli\InputDefinition\CreateEnumCommandDefinition;
use Cspray\Yape\Cli\Internal\ApplicationConfiguration;
use Cspray\Yape\Cli\Internal\EnumCodeGenerator;
use Cspray\Yape\Cli\Internal\EnumDefinition;
use Cspray\Yape\Cli\Internal\EnumDefinitionFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class CreateEnumCommand extends AbstractCodeGeneratorCommand {

    protected static $defaultName = 'create-enum';

    private $codeGenerator;
    private $enumDefinitionFactory;

    public function __construct(
        EnumCodeGenerator $codeGenerator,
        EnumDefinitionFactory $enumDefinitionFactory,
        ApplicationConfiguration $config
    ) {
        parent::__construct(
            $config,
            function(InputInterface $input) {
                return $this->enumDefinitionFactory->fromConsole($input);
            },
            function(EnumDefinition $definition) {
                return $this->codeGenerator->generate($definition);
            }
        );
        $this->codeGenerator = $codeGenerator;
        $this->enumDefinitionFactory = $enumDefinitionFactory;
    }

    protected function configure() {
        $this->setDescription('A PHP7+ code generator for creating type-safe, object-backed enums.')
            ->setHelp($this->getHelpText())
            ->setDefinition(new CreateEnumCommandDefinition());
    }

    private function getHelpText() : string {
        return <<<CONSOLE
The enumClass should be a fully qualified class name which MUST have at least 1 namespace part. Simply providing only a 
class name will result in an invalid Enum definition and an error.

There MUST be at least 1 entry in enumValues that will correspond to a static method on the generated class to retrieve 
that enum value. 
CONSOLE;

    }

    protected function execute(InputInterface $input, OutputInterface $output) : int {
        return $this->executeCodeGeneration($input, $output)->getStatusCode();
    }

    protected function getGeneratedCodeDescriptor() : string {
        return 'enum';
    }

    protected function getDefaultOutputDir() : string {
        return $this->getConfig()->getDefaultEnumOutputDir();
    }
}