<?php declare(strict_types=1);

namespace Cspray\Yape\Cli\InputDefinition;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

/**
 *
 * @package Cspray\Yape\Console\InputDefinition
 * @license See LICENSE in source root
 */
final class CreateEnumCommandDefinition extends InputDefinition {

    public function __construct() {
        parent::__construct([]);
        $this->addArguments([
            new InputArgument('enumClass', InputArgument::REQUIRED, 'The fully-qualified class name for your enum'),
            new InputArgument('enumValues', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'A list of values you\'d like for your enum')
        ]);

        $this->addOptions([
            new InputOption('dry-run', 'd', null, 'Use this flag to output the generated enum to stdout instead of writing to disk'),
            new InputOption('output-dir', 'o', InputOption::VALUE_REQUIRED, 'Specify a directory, under the current working directory, in which the enum will be stored. By default this is "src/Enums".')
        ]);
    }

}