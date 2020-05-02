<?php declare(strict_types=1);

namespace Cspray\Yape\Cli;

use Cspray\Yape\Cli\Internal\ClassSignatureDefinition;
use Cspray\Yape\Cli\Internal\DbalTypeDefinition;
use Cspray\Yape\Cli\Internal\TemplateDbalTypeCodeGenerator;
use Cspray\Yape\Cli\Support\EnumTypeStub;
use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\TestCase;

/**
 *
 * @package Cspray\Yape\Test
 * @license See LICENSE in source root
 */
class TemplateDbalTypeCodeGeneratorTest extends TestCase {

    private static $expectedType = 'YourNamespace\\Bar\\EnumTypeStubType';

    private $subject;

    static public function setUpBeforeClass() : void {
        parent::setUpBeforeClass();
        $definition = new DbalTypeDefinition(
            new ClassSignatureDefinition(self::$expectedType),
            new ClassSignatureDefinition(EnumTypeStub::class),
            'enum_type_stub_test'
        );
        $code = (new TemplateDbalTypeCodeGenerator())->generate($definition);

        $code = preg_replace('/<\?php/', '', $code);

        eval($code);
    }

    public function setUp() : void {
        if (!Type::hasType('enum_type_stub_test')) {
            Type::addType('enum_type_stub_test', self::$expectedType);
        }

        $this->subject = Type::getType('enum_type_stub_test');
    }

    public function testClassHasCorrectTypeName() {
        $this->assertSame('enum_type_stub_test', $this->subject->getName());
    }

    public function testClassHasCorrectSupportedEnumType() {
        $reflection = new \ReflectionObject($this->subject);
        $method = $reflection->getMethod('getSupportedEnumType');
        $method->setAccessible(true);
        $actual = $method->invoke($this->subject);

        $this->assertSame(EnumTypeStub::class, $actual);
    }


}