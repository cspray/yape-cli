<?php declare(strict_types=1);

namespace Cspray\Yape\Cli\Internal;

/**
 *
 * @package Cspray\Yape
 * @license See LICENSE in source root
 */
final class DbalTypeDefinition implements CodeDefinition {

    private $dbalTypeClass;
    private $enumClass;
    private $dbalTypeName;

    public function __construct(ClassSignatureDefinition $dbalTypeClass, ClassSignatureDefinition $enumClass, string $dbalTypeName) {
        $this->dbalTypeClass = $dbalTypeClass;
        $this->enumClass = $enumClass;
        $this->dbalTypeName = $dbalTypeName;
    }

    public function getDbalTypeClass() : ClassSignatureDefinition {
         return $this->dbalTypeClass;
    }

    public function getEnumClass() : ClassSignatureDefinition {
        return $this->enumClass;
    }

    public function getDbalType() : string {
        return $this->dbalTypeName;
    }

    public function getPrimaryClass() : ClassSignatureDefinition {
        return $this->dbalTypeClass;
    }
}