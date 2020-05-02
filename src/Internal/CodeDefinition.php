<?php declare(strict_types=1);

namespace Cspray\Yape\Cli\Internal;

/**
 *
 * @package Cspray\Yape\Internal
 * @license See LICENSE in source root
 */
interface CodeDefinition {

    public function getPrimaryClass() : ClassSignatureDefinition;

}