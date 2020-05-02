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
class CreateDbalTypeCommandDefinition extends InputDefinition {

    public function __construct() {
        parent::__construct();
        $this->addArguments([
            new InputArgument('dbalTypeClass', InputArgument::REQUIRED, 'The fully-qualified class name for your Doctrine DBAL Type'),
            new InputArgument('enumClass', InputArgument::REQUIRED, 'The fully-qualified class name for your enum'),
            new InputArgument('dbalType', InputArgument::REQUIRED, 'The name of the type to be registered with Doctrine'),
        ]);

        $this->addOptions([
            new InputOption('dry-run', 'd', null, 'Use this flag to output the generated Doctrine DBAL Type to stdout instead of writing to disk'),
            new InputOption('output-dir', 'o', InputOption::VALUE_REQUIRED, 'Specify a directory, under the current working directory, in which the Doctrine DBAL Type will be stored. By default this is "src/Doctrine".')
        ]);
    }

}