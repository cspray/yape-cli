<?php declare(strict_types=1);

namespace Cspray\Yape\Cli\Internal;

use PHPUnit\Framework\TestCase;

/**
 *
 * @package Cspray\Yape\Test
 * @license See LICENSE in source root
 */
class ClassDefinitionTest extends TestCase {

    public function testClassDefinitionSplitsUpFqcnCorrectly() {
        $classDefinition = new ClassSignatureDefinition('Foo\\Bar\\Baz\\ClassName');

        $this->assertSame('Foo\\Bar\\Baz', $classDefinition->getNamespace());
        $this->assertSame('ClassName', $classDefinition->getClassName());
        $this->assertSame('Foo\\Bar\\Baz\\ClassName', $classDefinition->getFullyQualifiedClassName());
    }

}