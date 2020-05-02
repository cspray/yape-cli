<?php declare(strict_types=1);

namespace Cspray\Yape\Cli\Internal;

/**
 * An EnumDefinition validator that ensures an EnumDefinition will result in valid PHP code as well as semantically
 * correct enum.
 *
 * @package Cspray\Yape
 * @license See LICENSE in source root
 */
final class EnumDefinitionValidator extends AbstractValidator {


    /**
     * Runs a specific set of validations on an EnumDefinition and will return whether or not it is valid as well as
     * specific error messages for why the EnumDefinition is invalid.
     *
     * Validations that are ran:
     *
     * 1. There MUST be a non-empty namespace value present that adheres to a regex for valid PHP namespaces.
     * 2. Each namespace section MUST NOT be a reserved word.
     * 3. There MUST be a non-empty class value present that adheres to a regex for valid PHP classes.
     * 4. The class MUST NOT be a reserved word
     * 5. There MUST be at least one EnumValue in the EnumValueSet
     *
     * We do not anticipate an EnumValueSet being constructed properly if members of that set are invalid with regards
     * to the data structure; i.e. if you construct an EnumValueSet and add duplicate members or a member with the
     * incorrect data type for its value that will result in an exception and the EnumDefinition for that EnumValueSet
     * will never be passed to this method.
     *
     * @param EnumDefinition $enumDefinition
     * @return ValidationResults
     */
    public function validate(EnumDefinition $enumDefinition) : ValidationResults {
        $errorMessages = [];

        $this->validateClassSignatureDefinition($enumDefinition->getEnumClass(), $errorMessages, 'enum');

        if (count($enumDefinition->getEnumValues()) === 0) {
            $errorMessages[] = 'There must be at least one enum value';
        }

        // array_flip will put each enumValue as the key; if the same key exists in an array it is overwritten and thus
        // if our flipped array does not equal the same amount of values in our non-flipped array it is implied that
        // there is a duplicate value.
        if (count($enumDefinition->getEnumValues()) !== count(array_flip($enumDefinition->getEnumValues()))) {
            $errorMessages[] = 'The enum values may not contain duplicates';
        }

        foreach ($enumDefinition->getEnumValues() as $enumValue) {
            if (!$this->isValidPhpLabel($enumValue)) {
                $errorMessages[] = 'All enum values must have only valid PHP class method characters';
            }
        }

        return new ValidationResults(...$errorMessages);
    }

}