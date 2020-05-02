<?php declare(strict_types=1);

namespace Cspray\Yape\Cli\Internal;

/**
 * Defines what a generated Enum should look like.
 *
 * @package Cspray\Yape
 * @license See LICENSE in source root
 */
final class EnumDefinition implements CodeDefinition {

    private $classDefinition;
    private $enumValues;

    public function __construct(ClassSignatureDefinition $classDefinition, string ...$enumValues) {
        $this->classDefinition = $classDefinition;
        $this->enumValues = $enumValues;
    }

    public function getEnumClass() : ClassSignatureDefinition {
        return $this->classDefinition;
    }

    /**
     * A list of strings that correspond to the values, and static constructor methods, for the given enum.
     *
     * @return string[]
     */
    public function getEnumValues() : array {
        return $this->enumValues;
    }

    public function getPrimaryClass() : ClassSignatureDefinition {
        return $this->classDefinition;
    }
}