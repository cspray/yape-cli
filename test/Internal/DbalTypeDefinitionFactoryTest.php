<?php declare(strict_types=1);

namespace Cspray\Yape\Cli\Internal;

use Cspray\Yape\Cli\InputDefinition\CreateDbalTypeCommandDefinition;
use Cspray\Yape\Enum;
use Cspray\Yape\Cli\Support\EnumTypeStub;
use Cspray\Yape\Cli\Exception\DbalTypeValidationException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;

/**
 *
 * @package Cspray\Yape\Test
 * @license See LICENSE in source root
 */
class DbalTypeDefinitionFactoryTest extends TestCase {

    public function testInvalidEnumDefinition() {
        $input = new ArrayInput(
            ['dbalTypeClass' => 'Foo\\Bar\\Baz\\Type', 'enumClass' => self::class, 'dbalType' => ''],
            new CreateDbalTypeCommandDefinition()
        );

        $exception = null;
        try {
            $factory = new DbalTypeDefinitionFactory();
            $factory->fromConsole($input);
        } catch (DbalTypeValidationException $enumValidationException) {
            $exception = $enumValidationException;
        } finally {
            $this->assertNotNull($exception);

            $definition = $exception->getDbalTypeDefinition();
            $this->assertSame(self::class, $definition->getEnumClass()->getFullyQualifiedClassName());
            $this->assertSame('', $definition->getDbalType());

            $results = $exception->getValidationResults();
            $this->assertFalse($results->isValid());

            $expected = [
                'The class "' . self::class . '" does not implement the ' . Enum::class . ' interface and could not be converted into an Enum DBAL Type.',
                'The DBAL type for "' . self::class . '" must not be empty.'

            ];
            $this->assertSame($expected, $results->getErrorMessages());
        }
    }

    public function testValidDefinition() {
        $input = new ArrayInput(
            ['dbalTypeClass' => 'YourNamespace\\Foo\\Bar\\EnumTypeStubType', 'enumClass' => EnumTypeStub::class, 'dbalType' => 'enum_class'],
            new CreateDbalTypeCommandDefinition()
        );

        $factory = new DbalTypeDefinitionFactory();
        $definition = $factory->fromConsole($input);

        $this->assertSame('YourNamespace\\Foo\\Bar\\EnumTypeStubType', $definition->getDbalTypeClass()->getFullyQualifiedClassName());
        $this->assertSame(EnumTypeStub::class, $definition->getEnumClass()->getFullyQualifiedClassName());
        $this->assertSame('enum_class', $definition->getDbalType());
    }
}