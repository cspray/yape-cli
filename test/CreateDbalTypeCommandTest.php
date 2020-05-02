<?php declare(strict_types=1);

namespace Cspray\Yape\Cli;

use Cspray\Yape\Cli\CreateDbalTypeCommand;
use Cspray\Yape\Cli\StatusCode;
use Cspray\Yape\Cli\Internal\ApplicationConfiguration;
use Cspray\Yape\Cli\Internal\DbalTypeCodeGenerator;
use Cspray\Yape\Cli\Internal\DbalTypeDefinition;
use Cspray\Yape\Cli\Internal\DbalTypeDefinitionFactory;
use Cspray\Yape\Cli\Support\EnumTypeStub;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamFile;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 *
 * @package Cspray\Yape\Test\Console
 * @license See LICENSE in source root
 */
class CreateDbalTypeCommandTest extends TestCase {

    private $codeGenerator;
    private $vfs;

    private $rootDir;
    private $outputDir;

    public function setUp() : void {
        parent::setUp();

        $this->codeGenerator = $this->getMockBuilder(DbalTypeCodeGenerator::class)->getMock();
        $this->vfs = vfsStream::setup('code-generator');
        $this->rootDir = vfsStream::url('code-generator/types');
        $this->outputDir = 'src/' . bin2hex(random_bytes(4));

        vfsStream::newDirectory('types/' . $this->outputDir)->at($this->vfs);
    }

    private function getCreateDbalTypeCommand() : CreateDbalTypeCommand {
        $factory = new DbalTypeDefinitionFactory();
        return new CreateDbalTypeCommand($this->codeGenerator, $factory, new ApplicationConfiguration($this->rootDir, 'src/Enums', $this->outputDir));
    }

    public function testDescription() {
        $command = $this->getCreateDbalTypeCommand();

        $this->assertSame('A PHP7+ code generator to create custom Doctrine DBAL Types from generated enums.', $command->getDescription());
    }

    public function testDbalTypeClassArgument() {
        $command = $this->getCreateDbalTypeCommand();
        $this->assertTrue($command->getDefinition()->hasArgument('dbalTypeClass'));

        $argument = $command->getDefinition()->getArgument('dbalTypeClass');

        $this->assertTrue($argument->isRequired());
        $this->assertFalse($argument->isArray());
        $this->assertSame('The fully-qualified class name for your Doctrine DBAL Type', $argument->getDescription());
        $this->assertNull($argument->getDefault());
    }

    public function testEnumClassArgument() {
        $command = $this->getCreateDbalTypeCommand();
        $this->assertTrue($command->getDefinition()->hasArgument('enumClass'));

        $argument = $command->getDefinition()->getArgument('enumClass');

        $this->assertTrue($argument->isRequired());
        $this->assertFalse($argument->isArray());
        $this->assertSame('The fully-qualified class name for your enum', $argument->getDescription());
        $this->assertNull($argument->getDefault());
    }

    public function testDbalTypeArgument() {
        $command = $this->getCreateDbalTypeCommand();
        $this->assertTrue($command->getDefinition()->hasArgument('dbalType'));

        $argument = $command->getDefinition()->getArgument('dbalType');

        $this->assertTrue($argument->isRequired());
        $this->assertFalse($argument->isArray());
        $this->assertSame('The name of the type to be registered with Doctrine', $argument->getDescription());
        $this->assertNull($argument->getDefault());
    }

    public function testDryRunOption() {
        $command = $this->getCreateDbalTypeCommand();
        $this->assertTrue($command->getDefinition()->hasOption('dry-run'));

        $option = $command->getDefinition()->getOption('dry-run');

        $this->assertFalse($option->acceptValue());
        $this->assertFalse($option->getDefault());
        $this->assertSame('Use this flag to output the generated Doctrine DBAL Type to stdout instead of writing to disk', $option->getDescription());
        $this->assertSame('d', $option->getShortcut());
    }

    public function testOutputDirOption() {
        $command = $this->getCreateDbalTypeCommand();
        $this->assertTrue($command->getDefinition()->hasOption('output-dir'));

        $option = $command->getDefinition()->getOption('output-dir');

        $this->assertTrue($option->isValueRequired());
        $this->assertSame('Specify a directory, under the current working directory, in which the Doctrine DBAL Type will be stored. By default this is "src/Doctrine".', $option->getDescription());
        $this->assertSame('o', $option->getShortcut());
    }

    public function testInvalidInputShowsError() {
        $tester = new CommandTester($this->getCreateDbalTypeCommand());
        $tester->execute([
            'dbalTypeClass' => 'Foo\\Bad Namespace\\Baz',
            'enumClass' => EnumTypeStub::class,
            'dbalType' => 'something'
        ]);

        $expected = <<<CONSOLE
There was an error validating the input provided. Please fix the following errors and try again:

 [ERROR] - The DBAL Type namespace must have only valid PHP namespace characters
CONSOLE;

        $this->assertSame(StatusCode::InputInvalid()->getStatusCode(), $tester->getStatusCode());
        $this->assertSame($expected, trim($tester->getDisplay()));
    }

    public function testOutputDirAndDryRunOptionTogetherShowsError() {
        $this->codeGenerator->expects($this->never())->method('generate');

        $tester = new CommandTester($this->getCreateDbalTypeCommand());
        $tester->execute([
            'dbalTypeClass' => 'Foo\\Bar\\Baz',
            'enumClass' => EnumTypeStub::class,
            'dbalType' => 'something',
            '--output-dir' => 'does not matter',
            '--dry-run' => true
        ]);

        $expected = <<<CONSOLE
[ERROR] You must not use the output-dir and dry-run options together.
CONSOLE;
        $this->assertSame(StatusCode::InputOptionsConflict()->getStatusCode(), $tester->getStatusCode());
        $this->assertSame($expected, trim($tester->getDisplay()));
    }

    public function testNoDryRunFileExistsShowError() {
        $this->codeGenerator->expects($this->never())->method('generate');

        vfsStream::newFile('types/' . $this->outputDir . '/EnumTypeStubType.php')->at($this->vfs)->setContent('existed');
        $path = vfsStream::url('code-generator/types/' . $this->outputDir . '/EnumTypeStubType.php');

        $tester = new CommandTester($this->getCreateDbalTypeCommand());
        $tester->execute([
            'dbalTypeClass' => 'Foo\\Bar\\EnumTypeStubType',
            'enumClass' => EnumTypeStub::class,
            'dbalType' => 'foo_bar'
        ]);


        $expected = <<<CONSOLE
There was an error creating your DBAL Type:

 [ERROR] - The DBAL Type specified, "Foo\Bar\EnumTypeStubType", already exists at                                       
         {$path}
CONSOLE;

        $this->assertSame(StatusCode::EnumExists()->getStatusCode(), $tester->getStatusCode());
        $this->assertSame($expected, trim($tester->getDisplay()));
        $this->assertSame('existed', file_get_contents($path));
    }

    public function testNoDryRunOutputDirDoesNotExistShowsError() {
        $this->codeGenerator->expects($this->never())->method('generate');

        rmdir(vfsStream::url('code-generator/types/' . $this->outputDir));

        $outputDir = vfsStream::url('code-generator/types/' . $this->outputDir);
        $tester = new CommandTester($this->getCreateDbalTypeCommand());
        $tester->execute([
            'dbalTypeClass' => 'Foo\\Bar\\DbalType',
            'enumClass' => EnumTypeStub::class,
            'dbalType' => 'foo_bar'
        ]);

        $expected = <<<CONSOLE
There was an error creating your DBAL Type:

 [ERROR] The output directory specified, "$outputDir", does not exist.
CONSOLE;

        $this->assertSame(StatusCode::SystemOutputDirectoryInvalid()->getStatusCode(), $tester->getStatusCode());
        $this->assertSame($expected, trim($tester->getDisplay()));
    }

    public function testDryRunOptionSendsGeneratedEnumToStdout() {
        $this->codeGenerator->expects($this->once())
            ->method('generate')
            ->with($this->callback(function(DbalTypeDefinition $definition) {
                $this->assertSame('Foo\\Bar\\DbalType', $definition->getDbalTypeClass()->getFullyQualifiedClassName());
                $this->assertSame(EnumTypeStub::class, $definition->getEnumClass()->getFullyQualifiedClassName());
                $this->assertSame('foo_bar', $definition->getDbalType());
                return true;
            }))
            ->willReturn('GENERATED CODE');

        $tester = new CommandTester($this->getCreateDbalTypeCommand());
        $tester->execute([
            'dbalTypeClass' => 'Foo\\Bar\\DbalType',
            'enumClass' => EnumTypeStub::class,
            'dbalType' => 'foo_bar',
            '--dry-run' => true
        ]);

        $expected = <<<CONSOLE
Your generated DBAL Type code:

GENERATED CODE
CONSOLE;

        $this->assertSame(StatusCode::Ok()->getStatusCode(), $tester->getStatusCode());
        $this->assertSame($expected, trim($tester->getDisplay()));
    }

    public function testNoDryRunOptionSendsGeneratedEnumToFile() {
        $this->codeGenerator->expects($this->once())
            ->method('generate')
            ->with($this->callback(function(DbalTypeDefinition $definition) {
                $this->assertSame('Foo\\Bar\\DbalType', $definition->getDbalTypeClass()->getFullyQualifiedClassName());
                $this->assertSame(EnumTypeStub::class, $definition->getEnumClass()->getFullyQualifiedClassName());
                $this->assertSame('foo_bar', $definition->getDbalType());
                return true;
            }))
            ->willReturn('GENERATED CODE');

        $tester = new CommandTester($this->getCreateDbalTypeCommand());
        $tester->execute([
            'dbalTypeClass' => 'Foo\\Bar\\DbalType',
            'enumClass' => EnumTypeStub::class,
            'dbalType' => 'foo_bar'
        ]);

        $expected = 'GENERATED CODE';

        /** @var vfsStreamFile $enumFile */
        $enumFile = $this->vfs->getChild('code-generator/types/' . $this->outputDir . '/DbalType.php');
        $this->assertSame(StatusCode::Ok()->getStatusCode(), $tester->getStatusCode());
        $this->assertNotNull($enumFile);
        $this->assertSame($expected, $enumFile->getContent());
        $this->assertSame('Your DBAL Type was stored at ' . vfsStream::url('code-generator/types/' . $this->outputDir . '/DbalType.php'), trim($tester->getDisplay()));
    }

    public function testOutputDirOptionRespected() {
        $this->codeGenerator->expects($this->once())
            ->method('generate')
            ->with($this->callback(function(DbalTypeDefinition $definition) {
                $this->assertSame('Foo\\Bar\\DbalType', $definition->getDbalTypeClass()->getFullyQualifiedClassName());
                $this->assertSame(EnumTypeStub::class, $definition->getEnumClass()->getFullyQualifiedClassName());
                $this->assertSame('foo_bar', $definition->getDbalType());
                return true;
            }))
            ->willReturn('GENERATED CODE');

        vfsStream::newDirectory('types/lib/known-dir')->at($this->vfs);

        $tester = new CommandTester($this->getCreateDbalTypeCommand());
        $tester->execute([
            'dbalTypeClass' => 'Foo\\Bar\\DbalType',
            'enumClass' => EnumTypeStub::class,
            'dbalType' => 'foo_bar',
            '--output-dir' => 'lib/known-dir'
        ]);

        $expected = 'GENERATED CODE';

        $noFile = $this->vfs->getChild('code-generator/types/' . $this->outputDir . '/DbalType.php');
        $this->assertNull($noFile);

        /** @var vfsStreamFile $enumFile */
        $file = $this->vfs->getChild('code-generator/types/lib/known-dir/DbalType.php');
        $this->assertNotNull($file);
        $this->assertSame($expected, $file->getContent());
        $this->assertSame(StatusCode::Ok()->getStatusCode(), $tester->getStatusCode());
        $this->assertSame('Your DBAL Type was stored at ' . vfsStream::url('code-generator/types/lib/known-dir/DbalType.php'), trim($tester->getDisplay()));
    }
}