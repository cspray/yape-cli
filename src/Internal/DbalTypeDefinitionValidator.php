<?php declare(strict_types=1);

namespace Cspray\Yape\Cli\Internal;

use Cspray\Yape\Enum;

/**
 *
 * @package Cspray\Yape
 * @license See LICENSE in source root
 */
final class DbalTypeDefinitionValidator extends AbstractValidator {


    public function validate(DbalTypeDefinition $dbalTypeDefinition) : ValidationResults {
        $errors = [];

        $this->validateClassSignatureDefinition($dbalTypeDefinition->getDbalTypeClass(), $errors, 'DBAL Type');
        $this->validateClassSignatureDefinition($dbalTypeDefinition->getEnumClass(), $errors, 'enum');

        $enumFqcn = $dbalTypeDefinition->getEnumClass()->getFullyQualifiedClassName();
        if (!class_exists($enumFqcn)) {
            $errors[] = sprintf('The class "%s" could not be loaded. Please ensure that it exists and is autoloadable.', $enumFqcn);
        } else if (!in_array(Enum::class, class_implements($enumFqcn))) {
            $errors[] = sprintf('The class "%s" does not implement the %s interface and could not be converted into an Enum DBAL Type.', $enumFqcn, Enum::class);
        }

        $dbalType = $dbalTypeDefinition->getDbalType();
        if (empty($dbalType)) {
            $errors[] = sprintf('The DBAL type for "%s" must not be empty.', $enumFqcn);
        }

        return new ValidationResults(...$errors);
    }
}