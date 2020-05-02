<?php declare(strict_types=1);

namespace Cspray\Yape\Cli\Internal;

use Cspray\Yape\Enum;
use Cspray\Yape\Cli\Support\EnumTypeStub;
use PHPUnit\Framework\TestCase;

/**
 *
 * @package Cspray\Yape\Test
 * @license See LICENSE in source root
 */
class DbalTypeDefinitionValidatorTest extends TestCase {

    public function testDbalTypeClassNotValidClassCharactersIsInvalid() {
        $definition = new DbalTypeDefinition(
            new ClassSignatureDefinition('YourNamespace\\Bad Naespace\\bad ! class name'),
            new ClassSignatureDefinition(EnumTypeStub::class),
            'foo_bar'
        );
        $subject = new DbalTypeDefinitionValidator();
        $results = $subject->validate($definition);

        $expected = [
            'The DBAL Type namespace must have only valid PHP namespace characters',
            'The DBAL Type class must have only valid PHP class characters'
        ];

        $this->assertFalse($results->isValid());
        $this->assertSame($expected, $results->getErrorMessages());
    }

    public function testDbalTypeClassHasReservedWorsIsInvalid() {
        $definition = new DbalTypeDefinition(
            new ClassSignatureDefinition('YourNamespace\\Foo\\exit'),
            new ClassSignatureDefinition(EnumTypeStub::class),
            'foo_bar'
        );
        $subject = new DbalTypeDefinitionValidator();
        $results = $subject->validate($definition);

        $expected = [
            'The DBAL Type class must not be a PHP reserved word',
        ];

        $this->assertFalse($results->isValid());
        $this->assertSame($expected, $results->getErrorMessages());
    }

    public function testEnumClassDoesNotExistIsInvalid() {
        $definition = new DbalTypeDefinition(
            new ClassSignatureDefinition('YourNamespace\\Doctrine\\DbalType'),
            new ClassSignatureDefinition('YourNamespace\\ClassNotFound'),
            'foo_bar'
        );
        $subject = new DbalTypeDefinitionValidator();
        $results = $subject->validate($definition);

        $expected = ['The class "YourNamespace\\ClassNotFound" could not be loaded. Please ensure that it exists and is autoloadable.'];

        $this->assertFalse($results->isValid());
        $this->assertSame($expected, $results->getErrorMessages());
    }

    public function testEnumClassDoesNotExtendEnumIsInvalid() {
        $definition = new DbalTypeDefinition(
            new ClassSignatureDefinition('YourNamespace\\Doctrine\\DbalType'),
            new ClassSignatureDefinition(ValidationResults::class),
            'foo_bar'
        );
        $subject = new DbalTypeDefinitionValidator();
        $results = $subject->validate($definition);

        $expected = [
            sprintf(
                'The class "%s" does not implement the %s interface and could not be converted into an Enum DBAL Type.',
                ValidationResults::class,
                Enum::class
            )
        ];
        $this->assertFalse($results->isValid());
        $this->assertSame($expected, $results->getErrorMessages());
    }

    public function testDbalTypeNotEmpty() {
        $definition = new DbalTypeDefinition(
            new ClassSignatureDefinition('YourNamespace\\Doctrine\\DbalType'),
            new ClassSignatureDefinition(EnumTypeStub::class),
            ''
        );
        $subject = new DbalTypeDefinitionValidator();
        $results = $subject->validate($definition);

        $expected = [
            sprintf(
                'The DBAL type for "%s" must not be empty.',
                EnumTypeStub::class
            )
        ];
        $this->assertFalse($results->isValid());
        $this->assertSame($expected, $results->getErrorMessages());
    }

}