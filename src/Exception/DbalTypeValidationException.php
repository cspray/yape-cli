<?php declare(strict_types=1);

namespace Cspray\Yape\Cli\Exception;

use Cspray\Yape\Cli\Internal\DbalTypeDefinition;
use Cspray\Yape\Cli\Internal\ValidationResults;

/**
 *
 * @package Cspray\Yape\Exception
 * @license See LICENSE in source root
 */
class DbalTypeValidationException extends ValidationException {

    private $definition;

    public function __construct(DbalTypeDefinition $definition, ValidationResults $results) {
        parent::__construct('An error was encountered validating that a DbalTypeDefinition would result in valid PHP code.', 0, null);
        $this->definition = $definition;
        $this->setValidationResults($results);
    }

    public function getDbalTypeDefinition() : DbalTypeDefinition {
        return $this->definition;
    }

}