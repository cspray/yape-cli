<?php declare(strict_types=1);

namespace Cspray\Yape\Cli;

use Cspray\Yape\Enum;
use Cspray\Yape\Cli\Internal\TemplateEnumCodeGenerator;
use Cspray\Yape\Cli\Internal\EnumDefinition;
use Cspray\Yape\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 *
 * @package Cspray\Yape\Test
 * @license See LICENSE in source root
 */
abstract class EnumTest extends TestCase {

    static public function setUpBeforeClass() : void {
        parent::setUpBeforeClass();
        $enumDefinition = static::getEnumDefinition();
        $code = (new TemplateEnumCodeGenerator())->generate($enumDefinition);

        $code = preg_replace('/<\?php/', '', $code);

        eval($code);
    }

    static abstract protected function getEnumDefinition() : EnumDefinition;

    /**
     * @param string $name
     * @dataProvider enumNameProvider
     */
    public function testEnumValuesAreSameObject(string $name) {
        $one = $this->convertNameToCallable($this->getEnumDefinition(), $name)();
        $two = $this->convertNameToCallable($this->getEnumDefinition(), $name)();

        $this->assertSame($one, $two);
    }

    /**
     * @param string $name
     * @dataProvider enumNameProvider
     */
    public function testEnumValuesImplementEnumInterface(string $name) {
        $one = $this->convertNameToCallable($this->getEnumDefinition(), $name)();

        $this->assertInstanceOf(Enum::class, $one);
    }

    /**
     * @param string $enumName
     * @param string $expected
     * @dataProvider toStringProvider
     */
    public function testToString(string $enumName, string $expected) {
        $actual = $this->convertNameToCallable($this->getEnumDefinition(), $enumName)();

        $this->assertSame($expected, $actual->toString());
    }

    /**
     * @param callable $firstMethod
     * @param callable $secondMethod
     * @param bool $expected
     * @dataProvider equalsProvider
     */
    public function testEquals(callable $firstMethod, callable $secondMethod, bool $expected) {
        $one = $firstMethod();
        $this->assertSame($expected, $one->equals($secondMethod()));
    }

    public function testValues() {
        $enumDef = $this->getEnumDefinition();
        $actual = $this->convertNameToCallable($enumDef, 'values')();

        $expected = [];
        foreach ($enumDef->getEnumValues() as $enumValue) {
            $expected[] = $this->convertNameToCallable($enumDef, $enumValue)();
        }

        $this->assertSame($expected, $actual);
    }

    /**
     * @param string $enumName
     * @dataProvider enumNameProvider
     */
    public function testValueOf(string $enumName) {
        $enumDef = $this->getEnumDefinition();
        $actual = $this->convertNameToCallable($enumDef, 'valueOf')($enumName);
        $expected = $this->convertNameToCallable($enumDef, $enumName)();

        $this->assertSame($expected, $actual);
    }

    /**
     * @throws \Exception
     */
    public function testInvalidValueOf() {
        $enumDef = $this->getEnumDefinition();
        $enumClass = $enumDef->getEnumClass()->getFullyQualifiedClassName();
        $value = bin2hex(random_bytes(10));
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The value "' . $value . '" is not a valid ' . $enumClass . ' name');

        $this->convertNameToCallable($enumDef, 'valueOf')($value);
    }

    final public function enumNameProvider() : array {
        $data = [];
        foreach ($this->getEnumDefinition()->getEnumValues() as $enumValue) {
            $data[]= [$enumValue];
        }
        return $data;
    }

    final public function toStringProvider() : array {
        $data = [];
        $enumDefinition = $this->getEnumDefinition();
        foreach ($enumDefinition->getEnumValues() as $enumValue) {
            $data[] = [$enumValue, $enumValue];
        }

        return $data;
    }

    private function convertNameToCallable(EnumDefinition $enumDefinition, string $name) {
        $callback = $enumDefinition->getEnumClass()->getFullyQualifiedClassName() . '::' . $name;
        return $callback;
    }

    abstract protected function equalsProvider() : array;

}