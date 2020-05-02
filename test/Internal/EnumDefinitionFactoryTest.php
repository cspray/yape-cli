<?php declare(strict_types=1);

namespace Cspray\Yape\Cli\Internal;

use Cspray\Yape\Cli\InputDefinition\CreateEnumCommandDefinition;
use Cspray\Yape\Cli\Exception\EnumValidationException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;

/**
 *
 * @package Cspray\Yape\Test
 * @license See LICENSE in source root
 */
class EnumDefinitionFactoryTest extends TestCase {

    public function testInvalidEnumDefinition() {
        $input = new ArrayInput(
            ['enumClass' => 'Foo\\Bar\\Bad Namesapace\\bad Class Name', 'enumValues' => ['Bad Method Name']],
            new CreateEnumCommandDefinition()
        );

        $exception = null;
        try {
            $factory = new EnumDefinitionFactory();
            $factory->fromConsole($input);
        } catch (EnumValidationException $enumValidationException) {
            $exception = $enumValidationException;
        } finally {
            $this->assertNotNull($exception);

            $enumDef = $exception->getEnumDefinition();
            $this->assertSame('Foo\\Bar\\Bad Namesapace', $enumDef->getEnumClass()->getNamespace());
            $this->assertSame('bad Class Name', $enumDef->getEnumClass()->getClassName());
            $this->assertSame(['Bad Method Name'], $enumDef->getEnumValues());

            $results = $exception->getValidationResults();
            $this->assertFalse($results->isValid());

            $expected = [
                'The enum namespace must have only valid PHP namespace characters',
                'The enum class must have only valid PHP class characters',
                'All enum values must have only valid PHP class method characters'
            ];
            $this->assertSame($expected, $results->getErrorMessages());
        }
    }

    public function testValidEnumDefinition() {
        $input = new ArrayInput(
            ['enumClass' => 'Foo\\Bar\\Baz\\EnumClass', 'enumValues' => ['One', 'Two', 'Three']],
            new CreateEnumCommandDefinition()
        );

        $factory = new EnumDefinitionFactory();
        $enumDefinition = $factory->fromConsole($input);

        $this->assertSame('Foo\\Bar\\Baz', $enumDefinition->getEnumClass()->getNamespace());
        $this->assertSame('EnumClass', $enumDefinition->getEnumClass()->getClassName());
        $this->assertSame(['One', 'Two', 'Three'], $enumDefinition->getEnumValues());
    }

}