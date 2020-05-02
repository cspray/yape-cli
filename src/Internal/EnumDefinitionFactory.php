<?php declare(strict_types=1);

namespace Cspray\Yape\Cli\Internal;

use Cspray\Yape\Cli\Exception\EnumValidationException;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Creates an EnumDefinition from provided user input.
 *
 * @package Cspray\Yape
 * @license See LICENSE in source root
 */
final class EnumDefinitionFactory {

    private $enumDefinitionValidator;

    public function __construct() {
        $this->enumDefinitionValidator = new EnumDefinitionValidator();
    }

    /**
     * Create an EnumDefinition based off of arguments passed to the bin/yape console app.
     *
     * @param InputInterface $input
     * @return EnumDefinition
     */
    public function fromConsole(InputInterface $input) : EnumDefinition {
        $enumDefinition = $this->createEnumDefinition($input);
        $results = $this->enumDefinitionValidator->validate($enumDefinition);
        if (!$results->isValid()) {
            throw new EnumValidationException($enumDefinition, $results);
        }
        return $enumDefinition;
    }

    private function createEnumDefinition(InputInterface $input) : EnumDefinition {
        $fqcn = $input->getArgument('enumClass');
        $enumValues = $input->getArgument('enumValues');

        return new EnumDefinition(new ClassSignatureDefinition($fqcn), ...$enumValues);
    }

}