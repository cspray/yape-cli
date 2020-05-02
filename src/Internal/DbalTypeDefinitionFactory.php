<?php declare(strict_types=1);

namespace Cspray\Yape\Cli\Internal;

use Cspray\Yape\Cli\Exception\DbalTypeValidationException;
use Symfony\Component\Console\Input\InputInterface;

/**
 *
 * @package Cspray\Yape
 * @license See LICENSE in source root
 */
final class DbalTypeDefinitionFactory {

    private $validator;

    public function __construct() {
        $this->validator = new DbalTypeDefinitionValidator();
    }

    public function fromConsole(InputInterface $input) : DbalTypeDefinition {
        $dbalTypeClass = $input->getArgument('dbalTypeClass');
        $enumType = $input->getArgument('enumClass');
        $dbalType = $input->getArgument('dbalType');
        $definition = new DbalTypeDefinition(
            new ClassSignatureDefinition($dbalTypeClass),
            new ClassSignatureDefinition($enumType),
            $dbalType
        );

        $results = $this->validator->validate($definition);
        if (!$results->isValid()) {
            throw new DbalTypeValidationException($definition, $results);
        }

        return $definition;
    }

}