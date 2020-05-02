<?php declare(strict_types=1);

namespace Cspray\Yape\Cli;

use Cspray\Yape\Cli\Exception\ValidationException;
use Cspray\Yape\Cli\Internal\ApplicationConfiguration;
use Cspray\Yape\Cli\Internal\ClassSignatureDefinition;
use Cspray\Yape\Cli\Internal\CodeDefinition;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 *
 * @package Cspray\Yape\Console
 * @license See LICENSE in source root
 */
abstract class AbstractCodeGeneratorCommand extends Command {

    private $config;
    private $definitionFactory;
    private $codeGenerator;

    protected function __construct(ApplicationConfiguration $config, callable $definitionFactory, callable $codeGenerator) {
        parent::__construct();
        $this->config = $config;
        $this->definitionFactory = $definitionFactory;
        $this->codeGenerator = $codeGenerator;
    }

    protected function getConfig() : ApplicationConfiguration {
        return $this->config;
    }

    protected function executeCodeGeneration(InputInterface $input, OutputInterface $output) : StatusCode {
        $cli = new SymfonyStyle($input, $output);

        try {
            $optionsConflict = $this->ensureInputOptionsDoNotConflict($cli, $input);
            if (!$optionsConflict->equals(StatusCode::Ok())) {
                return $optionsConflict;
            }

            $def = ($this->definitionFactory)($input);
            if ($input->getOption('dry-run')) {
                return $this->handleDryRun($cli, $def);
            } else {
                return $this->handleRealRun($cli, $def, $input);
            }
        } catch (ValidationException $enumValidationException) {
            return $this->handleValidationException($cli, $enumValidationException);
        }
    }

    private function handleDryRun(SymfonyStyle $cli, $definition) : StatusCode {
        $cli->writeln(sprintf('Your generated %s code:', $this->getGeneratedCodeDescriptor()));
        $cli->newLine();
        $cli->writeln(($this->codeGenerator)($definition));
        return StatusCode::Ok();
    }

    private function handleRealRun(SymfonyStyle $cli, CodeDefinition $definition, InputInterface $input) : StatusCode {
        $outputDirExists = $this->ensureOutputDirectoryExists($cli, $input);
        if (!$this->statusCodeOk($outputDirExists)) {
            return $outputDirExists;
        }

        $fileExists = $this->ensureFileDoesNotExist($cli, $input, $definition->getPrimaryClass());
        if (!$this->statusCodeOk($fileExists)) {
            return $fileExists;
        }
        $filePath = $this->getOutputPath($definition->getPrimaryClass(), $input);
        file_put_contents($filePath, ($this->codeGenerator)($definition));
        $cli->writeln(sprintf('Your %s was stored at %s', $this->getGeneratedCodeDescriptor(), $filePath));
        return StatusCode::Ok();
    }

    private function getOutputDir(InputInterface $input) : string {
        $outputDir = $input->getOption('output-dir') ?? $this->getDefaultOutputDir();
        return sprintf(
            '%s/%s',
            $this->config->getRootDir(),
            $outputDir
        );
    }

    private function getOutputPath(ClassSignatureDefinition $definition, InputInterface $input) : string {
        return sprintf(
            '%s/%s.php',
            $this->getOutputDir($input),
            $definition->getClassName()
        );
    }

    private function ensureInputOptionsDoNotConflict(SymfonyStyle $cli, InputInterface $input) : StatusCode {
        if ($input->getOption('dry-run') && !is_null($input->getOption('output-dir'))) {
            $cli->error('You must not use the output-dir and dry-run options together.');
            return StatusCode::InputOptionsConflict();
        } else {
            return StatusCode::Ok();
        }
    }

    private function ensureOutputDirectoryExists(SymfonyStyle $cli, InputInterface $input) : StatusCode {
        $outputDir = $this->getOutputDir($input);
        if (!is_dir($outputDir)) {
            $cli->writeln('There was an error creating your ' . $this->getGeneratedCodeDescriptor() . ':');
            $cli->newLine();
            $cli->error(sprintf('The output directory specified, "%s", does not exist.', $outputDir));
            return StatusCode::SystemOutputDirectoryInvalid();
        } else {
            return StatusCode::Ok();
        }
    }

    private function ensureFileDoesNotExist(SymfonyStyle $cli, InputInterface $input, ClassSignatureDefinition $classSignatureDefinition) : StatusCode {
        $filePath = $this->getOutputPath($classSignatureDefinition, $input);
        if (file_exists($filePath)) {
            $cli->writeln(sprintf('There was an error creating your %s:', $this->getGeneratedCodeDescriptor()));
            $cli->newLine();
            $cli->error(sprintf('- The %s specified, "%s", already exists at %s', $this->getGeneratedCodeDescriptor(), $classSignatureDefinition->getFullyQualifiedClassName(), $filePath));
            return StatusCode::EnumExists();
        }

        return StatusCode::Ok();
    }

    private function handleValidationException(SymfonyStyle $cli, ValidationException $validationException) : StatusCode {
        $cli->writeln('There was an error validating the input provided. Please fix the following errors and try again:');
        $cli->newLine();

        $errorMessages = $validationException->getValidationResults()->getErrorMessages();
        $cli->error(array_map(function($errMsg) { return "- {$errMsg}"; }, $errorMessages));
        return StatusCode::InputInvalid();
    }

    private function statusCodeOk(StatusCode $statusCodes) {
        return $statusCodes->equals(StatusCode::Ok());
    }

    abstract protected function getDefaultOutputDir(): string;

    abstract protected function getGeneratedCodeDescriptor() : string;



}