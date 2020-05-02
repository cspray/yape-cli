<?php declare(strict_types=1);

namespace Cspray\Yape\Cli;

use Cspray\Yape\Cli\Internal\ApplicationConfiguration;
use Cspray\Yape\Cli\CreateEnumCommand;
use Cspray\Yape\Cli\StatusCode;
use Cspray\Yape\Cli\Internal\EnumCodeGenerator;
use Cspray\Yape\Cli\Internal\EnumDefinition;
use Cspray\Yape\Cli\Internal\EnumDefinitionFactory;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamFile;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 *
 * @package Cspray\Yape\Test\Console
 * @license See LICENSE in source root
 */
class CreateEnumCommandTest extends TestCase {

    private $codeGenerator;
    private $vfs;

    private $rootDir;
    private $outputDir;

    public function setUp() : void {
        parent::setUp();

        $this->codeGenerator = $this->getMockBuilder(EnumCodeGenerator::class)->getMock();
        $this->vfs = vfsStream::setup('code-generator');
        $this->rootDir = vfsStream::url('code-generator/enums');
        $this->outputDir = 'src/' . bin2hex(random_bytes(4));

        vfsStream::newDirectory('enums/' . $this->outputDir)->at($this->vfs);
    }

    private function getCreateEnumCommand() : CreateEnumCommand {
        return new CreateEnumCommand($this->codeGenerator, new EnumDefinitionFactory(), new ApplicationConfiguration($this->rootDir, $this->outputDir, 'src/Doctrine'));
    }

    public function testDescription() {
        $enumCommand = $this->getCreateEnumCommand();

        $this->assertSame("A PHP7+ code generator for creating type-safe, object-backed enums.", $enumCommand->getDescription());
    }

    public function testEnumClassArgument() {
        $enumCommand = $this->getCreateEnumCommand();
        $this->assertTrue($enumCommand->getDefinition()->hasArgument('enumClass'));

        $argument = $enumCommand->getDefinition()->getArgument('enumClass');

        $this->assertTrue($argument->isRequired());
        $this->assertFalse($argument->isArray());
        $this->assertSame('The fully-qualified class name for your enum', $argument->getDescription());
        $this->assertNull($argument->getDefault());
    }

    public function testEnumValuesArgument() {
        $enumCommand = $this->getCreateEnumCommand();
        $this->assertTrue($enumCommand->getDefinition()->hasArgument('enumValues'));

        $argument = $enumCommand->getDefinition()->getArgument('enumValues');

        $this->assertTrue($argument->isArray());
        $this->assertTrue($argument->isRequired());
        $this->assertSame('A list of values you\'d like for your enum', $argument->getDescription());
        $this->assertSame([], $argument->getDefault());
    }

    public function testDryRunOption() {
        $enumCommand = $this->getCreateEnumCommand();
        $this->assertTrue($enumCommand->getDefinition()->hasOption('dry-run'));

        $option = $enumCommand->getDefinition()->getOption('dry-run');

        $this->assertFalse($option->acceptValue());
        $this->assertFalse($option->getDefault());
        $this->assertSame('Use this flag to output the generated enum to stdout instead of writing to disk', $option->getDescription());
        $this->assertSame('d', $option->getShortcut());
    }

    public function testOutputDirOption() {
        $enumCommand = $this->getCreateEnumCommand();
        $this->assertTrue($enumCommand->getDefinition()->hasOption('output-dir'));

        $option = $enumCommand->getDefinition()->getOption('output-dir');

        $this->assertTrue($option->isValueRequired());
        $this->assertSame('Specify a directory, under the current working directory, in which the enum will be stored. By default this is "src/Enums".', $option->getDescription());
        $this->assertSame('o', $option->getShortcut());
    }

    public function testInvalidInputShowsError() {
        $tester = new CommandTester($this->getCreateEnumCommand());
        $tester->execute([
            'enumClass' => 'Foo\\Bad Namespace\\Enum',
            'enumValues' => ['One', 'Two', 'Three']
        ]);

        $expected = <<<CONSOLE
There was an error validating the input provided. Please fix the following errors and try again:

 [ERROR] - The enum namespace must have only valid PHP namespace characters
CONSOLE;

        $this->assertSame(StatusCode::InputInvalid()->getStatusCode(), $tester->getStatusCode());
        $this->assertSame($expected, trim($tester->getDisplay()));
    }

    public function testOutputDirAndDryRunOptionTogetherShowsError() {
        $this->codeGenerator->expects($this->never())->method('generate');

        $tester = new CommandTester($this->getCreateEnumCommand());
        $tester->execute([
            'enumClass' => 'Foo\\Bar\\Baz\\Compass',
            'enumValues' => ['North', 'South', 'East', 'West'],
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

        vfsStream::newFile('enums/' . $this->outputDir . '/Compass.php')->at($this->vfs)->setContent('existed');
        $path = vfsStream::url('code-generator/enums/' . $this->outputDir . '/Compass.php');

        $tester = new CommandTester($this->getCreateEnumCommand());
        $tester->execute([
            'enumClass' => 'Foo\\Bar\\Baz\\Compass',
            'enumValues' => ['North', 'South', 'East', 'West']
        ]);


        $expected = <<<CONSOLE
There was an error creating your enum:

 [ERROR] - The enum specified, "Foo\Bar\Baz\Compass", already exists at                                                 
         {$path}
CONSOLE;

        $this->assertSame(StatusCode::EnumExists()->getStatusCode(), $tester->getStatusCode());
        $this->assertSame($expected, trim($tester->getDisplay()));
        $this->assertSame('existed', file_get_contents($path));
    }

    public function testNoDryRunOutputDirDoesNotExistShowsError() {
        $this->codeGenerator->expects($this->never())->method('generate');

        rmdir(vfsStream::url('code-generator/enums/' . $this->outputDir));

        $outputDir = vfsStream::url('code-generator/enums/' . $this->outputDir);
        $tester = new CommandTester($this->getCreateEnumCommand());
        $tester->execute([
            'enumClass' => 'Foo\\Bar\\Compass',
            'enumValues' => ['North', 'South', 'East', 'West']
        ]);

        $expected = <<<CONSOLE
There was an error creating your enum:

 [ERROR] The output directory specified, "$outputDir", does not exist.
CONSOLE;

        $this->assertSame(StatusCode::SystemOutputDirectoryInvalid()->getStatusCode(), $tester->getStatusCode());
        $this->assertSame($expected, trim($tester->getDisplay()));
    }

    public function testDryRunOptionSendsGeneratedEnumToStdout() {
        $this->codeGenerator->expects($this->once())
            ->method('generate')
            ->with($this->callback(function(EnumDefinition $enumDefinition) {
                $this->assertSame('Foo\\Bar\\Baz', $enumDefinition->getEnumClass()->getNamespace());
                $this->assertSame('Compass', $enumDefinition->getEnumClass()->getClassName());
                $this->assertSame(['North', 'South', 'East', 'West'], $enumDefinition->getEnumValues());
                return true;
            }))
            ->willReturn('GENERATED CODE');

        $tester = new CommandTester($this->getCreateEnumCommand());
        $tester->execute([
            'enumClass' => 'Foo\\Bar\\Baz\\Compass',
            'enumValues' => ['North', 'South', 'East', 'West'],
            '--dry-run' => true
        ]);

        $expected = <<<CONSOLE
Your generated enum code:

GENERATED CODE
CONSOLE;

        $this->assertSame(StatusCode::Ok()->getStatusCode(), $tester->getStatusCode());
        $this->assertSame($expected, trim($tester->getDisplay()));
    }

    public function testNoDryRunOptionSendsGeneratedEnumToFile() {
        $this->codeGenerator->expects($this->once())
            ->method('generate')
            ->with($this->callback(function(EnumDefinition $enumDefinition) {
                $this->assertSame('Foo\\Bar\\Baz', $enumDefinition->getEnumClass()->getNamespace());
                $this->assertSame('Compass', $enumDefinition->getEnumClass()->getClassName());
                $this->assertSame(['North', 'South', 'East', 'West'], $enumDefinition->getEnumValues());
                return true;
            }))
            ->willReturn('GENERATED CODE');

        $tester = new CommandTester($this->getCreateEnumCommand());
        $tester->execute([
            'enumClass' => 'Foo\\Bar\\Baz\\Compass',
            'enumValues' => ['North', 'South', 'East', 'West']
        ]);

        $expected = 'GENERATED CODE';

        /** @var vfsStreamFile $enumFile */
        $enumFile = $this->vfs->getChild('code-generator/enums/' . $this->outputDir . '/Compass.php');
        $this->assertNotNull($enumFile);
        $this->assertSame($expected, $enumFile->getContent());
        $this->assertSame(StatusCode::Ok()->getStatusCode(), $tester->getStatusCode());
        $this->assertSame('Your enum was stored at ' . vfsStream::url('code-generator/enums/' . $this->outputDir . '/Compass.php'), trim($tester->getDisplay()));
    }

    public function testOutputDirOptionRespected() {
        $this->codeGenerator->expects($this->once())
            ->method('generate')
            ->with($this->callback(function(EnumDefinition $enumDefinition) {
                $this->assertSame('Foo\\Bar\\Baz', $enumDefinition->getEnumClass()->getNamespace());
                $this->assertSame('Compass', $enumDefinition->getEnumClass()->getClassName());
                $this->assertSame(['North', 'South', 'East', 'West'], $enumDefinition->getEnumValues());
                return true;
            }))
            ->willReturn('GENERATED CODE');

        vfsStream::newDirectory('enums/lib/known-dir')->at($this->vfs);

        $tester = new CommandTester($this->getCreateEnumCommand());
        $tester->execute([
            'enumClass' => 'Foo\\Bar\\Baz\\Compass',
            'enumValues' => ['North', 'South', 'East', 'West'],
            '--output-dir' => 'lib/known-dir'
        ]);

        $expected = 'GENERATED CODE';

        $noFile = $this->vfs->getChild('code-generator/enums/' . $this->outputDir . '/Compass.php');
        $this->assertNull($noFile);

        /** @var vfsStreamFile $enumFile */
        $enumFile = $this->vfs->getChild('code-generator/enums/lib/known-dir/Compass.php');
        $this->assertNotNull($enumFile);
        $this->assertSame($expected, $enumFile->getContent());
        $this->assertSame(StatusCode::Ok()->getStatusCode(), $tester->getStatusCode());
        $this->assertSame('Your enum was stored at ' . vfsStream::url('code-generator/enums/lib/known-dir/Compass.php'), trim($tester->getDisplay()));
    }

}
