<?php declare(strict_types=1);

namespace Cspray\Yape\Cli;

use Cspray\Yape\Enum;
use Cspray\Yape\EnumTrait;

final class StatusCode implements Enum {

    use EnumTrait {
        EnumTrait::__construct as private setName;
    }

    private $statusCode;

    private function __construct(string $name, int $statusCode) {
        $this->setName($name);
        $this->statusCode = $statusCode;
    }

    public function getStatusCode() : int {
        return $this->statusCode;
    }

    public static function Ok() : self {
        return self::getSingleton('Ok', 0);
    }

    public static function InputInvalid() : self {
        return self::getSingleton('InputInvalid', 255);
    }

    public static function EnumExists() : self {
        return self::getSingleton('EnumExists', 254);
    }

    public static function InputOptionsConflict() : self {
        return self::getSingleton('InputOptionsConflict', 199);
    }

    public static function SystemOutputDirectoryInvalid() : self {
        return self::getSingleton('SystemOutputDirectoryInvalid', 198);
    }

    // It is imperative that if you add a new value post code generation you add the method name here!
    static protected function getAllowedValues() : array {
        return ['Ok', 'InputInvalid', 'EnumExists', 'InputOptionsConflict', 'SystemOutputDirectoryInvalid', 'SystemAutoloadInvalid', ];
    }

}
