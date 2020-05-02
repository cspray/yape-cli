<?php
/**
 * @var $this \Cspray\Yape\Cli\Internal\EnumDefinition
 */

$enumValues = '';
$twelveSpaces = str_repeat(' ', 12);
foreach ($this->getEnumValues() as $enumValue) {
  $enumValues .= $twelveSpaces . var_export($enumValue, true) . ",\n";
}
?>
namespace <?= $this->getEnumClass()->getNamespace() ?>;

use Cspray\Yape\Enum;
use Cspray\Yape\EnumTrait;

final class <?= $this->getEnumClass()->getClassName() ?> implements Enum {

    use EnumTrait;

<?php foreach($this->getEnumValues() as $enumValue): ?>
    public static function <?= $enumValue ?>() : self {
        return self::getSingleton(__FUNCTION__);
    }

<?php endforeach; ?>
    // It is imperative that if you add a new value post code generation you add the method name here!
    static protected function getAllowedValues() : array {
        return [
<?= $enumValues ?>
        ];
    }

}
