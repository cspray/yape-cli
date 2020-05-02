<?php declare(strict_types=1);

namespace Cspray\Yape\Cli\Internal;

/**
 *
 * @package Cspray\Yape
 * @license See LICENSE in source root
 */
abstract class AbstractTemplateGenerator {

    protected function render(string $template, $context) : string {
        // this is its own anonymous function instead of doing this inline so that the template only has the
        // variables as defined by the $context.
        $function = function() use($template) {
            ob_start();
            include $template;
            return ob_get_clean();
        };

        return '<?php declare(strict_types=1);' . PHP_EOL . PHP_EOL . $function->call($context);
    }

}