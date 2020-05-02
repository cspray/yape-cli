<?php declare(strict_types=1);

namespace Cspray\Yape\Cli\Internal;

/**
 *
 * @package Cspray\Yape
 * @license See LICENSE in source root
 */
final class TemplateDbalTypeCodeGenerator extends AbstractTemplateGenerator implements DbalTypeCodeGenerator {

    public function generate(DbalTypeDefinition $dbalTypeDefinition) : string {
        return $this->render(dirname(__DIR__, 2) . '/resources/templates/dbal_type.php', $dbalTypeDefinition);
    }
}