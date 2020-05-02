<?php declare(strict_types=1);

namespace Cspray\Yape\Cli\Internal;

/**
 *
 * @package Cspray\Yape
 * @license See LICENSE in source root
 */
final class ValidationResults {

    private $isValid;
    private $errorMessages;

    public function __construct(string ...$errorMessages) {
        $this->isValid = empty($errorMessages);
        $this->errorMessages = $errorMessages;
    }

    public function isValid() : bool {
        return $this->isValid;
    }

    public function getErrorMessages() : array {
        return $this->errorMessages;
    }

}