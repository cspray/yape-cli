<?php declare(strict_types=1);

namespace Cspray\Yape\Cli\Internal;

/**
 *
 * @package Cspray\Yape\Console\Configuration
 * @license See LICENSE in source root
 */
final class ApplicationConfiguration {

    private $rootDir;
    private $enumOutputDir;
    private $dbalTypeDir;

    public function __construct(string $rootDir, string $enumOutputDir, string $dbalTypeDir) {
        $this->rootDir = $rootDir;
        $this->enumOutputDir = $enumOutputDir;
        $this->dbalTypeDir = $dbalTypeDir;
    }

    public function getRootDir() : string {
        return $this->rootDir;
    }

    public function getDefaultEnumOutputDir() : string {
        return $this->enumOutputDir;
    }

    public function getDefaultDbalTypeOutputDir() : string {
        return $this->dbalTypeDir;
    }

}