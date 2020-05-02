<?php declare(strict_types=1);

namespace Cspray\Yape\Cli\Exception;

use Cspray\Yape\Cli\Internal\ValidationResults;
use Cspray\Yape\Exception\Exception;

/**
 *
 * @package Cspray\Yape\Exception
 * @license See LICENSE in source root
 */
abstract class ValidationException extends Exception {

    private $validationResults;

    protected function setValidationResults(ValidationResults $validationResults) {
        $this->validationResults = $validationResults;
    }

    final public function getValidationResults() : ValidationResults {
        return $this->validationResults;
    }

}