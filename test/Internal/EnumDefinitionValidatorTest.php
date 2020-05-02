<?php declare(strict_types=1);

namespace Cspray\Yape\Cli\Internal;

use PHPUnit\Framework\TestCase;

/**
 *
 * @package Cspray\Yape\Test
 * @license See LICENSE in source root
 */
class EnumDefinitionValidatorTest extends TestCase {

    /**
     * @var EnumDefinitionValidator
     */
    private $subject;

    public function setUp() : void {
        parent::setUp();
        $this->subject = new EnumDefinitionValidator();
    }

    public function testValidateWithBadNamespace() {
        $definition = new EnumDefinition(new ClassSignatureDefinition('This is a bad namespace\\EnumName'), 'One');
        $results = $this->subject->validate($definition);

        $this->assertFalse($results->isValid());

        $expected = ['The enum namespace must have only valid PHP namespace characters'];
        $actual = $results->getErrorMessages();

        $this->assertSame($expected, $actual);
    }

    public function testValidateWithBadClassName() {
        $definition = new EnumDefinition(new ClassSignatureDefinition('Vendor\\GoodApp\\Bad Class Name'), 'One');
        $results = $this->subject->validate($definition);

        $this->assertFalse($results->isValid());

        $expected = ['The enum class must have only valid PHP class characters'];
        $actual = $results->getErrorMessages();

        $this->assertSame($expected, $actual);
    }

    public function testValidatorWithEmptyEnumValue() {
        $definition = new EnumDefinition(new ClassSignatureDefinition('Vendor\\GoodApp\\GoodClassName'));
        $results = $this->subject->validate($definition);

        $this->assertFalse($results->isValid());

        $expected = ['There must be at least one enum value'];
        $actual = $results->getErrorMessages();

        $this->assertSame($expected, $actual);
    }

    public function testValidatorWithBadEnumValueName() {
        $definition = new EnumDefinition(new ClassSignatureDefinition('Vendorr\\GoodApp\\Some_Class_Name1'), 'Bad Method Name');
        $results = $this->subject->validate($definition);

        $this->assertFalse($results->isValid());

        $expected = ['All enum values must have only valid PHP class method characters'];
        $actual = $results->getErrorMessages();

        $this->assertSame($expected, $actual);
    }

    public function testValidatorWithReservedWordInNamespace() {
        $definition = new EnumDefinition(new ClassSignatureDefinition('Parent\\Class\\SomeClass'), 'OhNo');
        $results = $this->subject->validate($definition);

        $this->assertFalse($results->isValid());

        $expected = ['The enum namespace must not have any PHP reserved words'];
        $actual = $results->getErrorMessages();

        $this->assertSame($expected, $actual);
    }

    public function testValidatorWithReservedWordInClass() {
        $definition = new EnumDefinition(new ClassSignatureDefinition('ParentNamespace\\SubNamespace\\Class'), 'OhNo');
        $results = $this->subject->validate($definition);

        $this->assertFalse($results->isValid());

        $expected = ['The enum class must not be a PHP reserved word'];
        $actual = $results->getErrorMessages();

        $this->assertSame($expected, $actual);
    }

    public function testValidatorWithDuplicateMethodNames() {
        $definition = new EnumDefinition(new ClassSignatureDefinition('Foo\\Bar\\Baz\\Enum'), 'One', 'One', 'Two');
        $results = $this->subject->validate($definition);

        $this->assertFalse($results->isValid());

        $expected = ['The enum values may not contain duplicates'];
        $actual = $results->getErrorMessages();

        $this->assertSame($expected, $actual);
    }

}