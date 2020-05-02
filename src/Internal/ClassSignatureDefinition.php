<?php declare(strict_types=1);

namespace Cspray\Yape\Cli\Internal;

/**
 *
 * @package Cspray\Yape
 * @license See LICENSE in source root
 */
final class ClassSignatureDefinition {

    private $namespace;
    private $class;

    public function __construct(string $fqcn) {
        $parts = explode('\\', $fqcn);
        $this->class = array_pop($parts);
        $this->namespace = join('\\', $parts);
    }

    public function getNamespace() : string {
        return $this->namespace;
    }

    public function getClassName() : string {
        return $this->class;
    }

    public function getFullyQualifiedClassName() : string {
        return $this->namespace . '\\' . $this->class;
    }

}