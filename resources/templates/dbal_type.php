<?php
/**
 * @var $this \Cspray\Yape\DbalTypeDefinition
 */
?>
namespace <?= $this->getDbalTypeClass()->getNamespace() ?>;

use Cspray\Yape\Dbal\AbstractEnumType;

final class <?= $this->getDbalTypeClass()->getClassName() ?> extends AbstractEnumType {

    public function getName() : string {
        return <?= var_export($this->getDbalType(), true) ?>;
    }

    protected function getSupportedEnumType() : string {
        return <?= var_export($this->getEnumClass()->getFullyQualifiedClassName(), true) ?>;
    }

}

