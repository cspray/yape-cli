<?php declare(strict_types=1);

namespace Cspray\Yape\Cli\Support;

use Cspray\Yape\Enum;
use Cspray\Yape\EnumTrait;

class EnumTypeStub implements Enum {

    use EnumTrait;

    static public function Foo() : self {
        return self::getSingleton('Foo');
    }

    static public function Bar() : self {
        return self::getSingleton('Bar');
    }

    static public function Baz() : self {
        return self::getSingleton('Baz');
    }

    static protected function getAllowedValues() : array {
        return ['Foo', 'Bar', 'Baz'];
    }

}

