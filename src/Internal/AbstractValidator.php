<?php declare(strict_types=1);

namespace Cspray\Yape\Cli\Internal;

/**
 *
 * @package Cspray\Yape
 * @license See LICENSE in source root
 */
abstract class AbstractValidator {

    private const PHP_LABEL_REGEX = '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/';

    /**
     * All of the possible reserved words in PHP that cannot be used as either namespace or class names.
     */
    private const RESERVED_WORDS = [
        'self',
        'static',
        'parent',
        '__halt_compiler',
        'abstract',
        'and',
        'array',
        'as',
        'break',
        'callable',
        'case',
        'catch',
        'class',
        'clone',
        'const',
        'continue',
        'declare',
        'default',
        'die',
        'do',
        'echo',
        'else',
        'elseif',
        'empty',
        'enddeclare',
        'endfor',
        'endforeach',
        'endif',
        'endswitch',
        'endwhile',
        'eval',
        'exit',
        'extends',
        'final',
        'finally',
        'for',
        'foreach',
        'function',
        'global',
        'goto',
        'if',
        'implements',
        'include',
        'include_once',
        'instanceof',
        'insteadof',
        'interface',
        'isset',
        'list',
        'namespace',
        'new',
        'or',
        'print',
        'private',
        'protected',
        'public',
        'require',
        'require_once',
        'return',
        'switch',
        'throw',
        'trait',
        'try',
        'unset',
        'use',
        'var',
        'while',
        'xor',
        'yield',
        'yield from',
        '__CLASS__',
        '__DIR__',
        '__FILE__',
        '__FUNCTION__',
        '__LINE__',
        '__METHOD__',
        '__NAMESPACE__',
        '__TRAIT__'
    ];

    protected function validateClassSignatureDefinition(ClassSignatureDefinition $definition, array &$errors, string $classDescriptor) : void {
        $namespace = $definition->getNamespace();
        $class = $definition->getClassName();

        if ($this->doesNamespaceHaveReservedWord($namespace)) {
            $errors[] = sprintf('The %s namespace must not have any PHP reserved words', $classDescriptor);
        } else if (!$this->isValidNamespaceLabel($namespace)) {
            $errors[] = sprintf('The %s namespace must have only valid PHP namespace characters', $classDescriptor);
        }

        if ($this->isReservedWord($class)) {
            $errors[] = sprintf('The %s class must not be a PHP reserved word', $classDescriptor);
        } else if (!$this->isValidPhpLabel($class)) {
            $errors[] = sprintf('The %s class must have only valid PHP class characters', $classDescriptor);
        }
    }

    protected function isValidPhpLabel(string $s) : bool {
        return !!preg_match(self::PHP_LABEL_REGEX, $s);
    }

    protected function isReservedWord(string $s) : bool {
        return in_array($s, self::RESERVED_WORDS) || in_array(strtolower($s), self::RESERVED_WORDS);
    }

    private function isValidNamespaceLabel(string $namespaces) : bool {
        $namespaceParts = explode('\\', $namespaces);
        foreach ($namespaceParts as $namespacePart) {
            if (!$this->isValidPhpLabel($namespacePart)) {
                return false;
            }
        }
        return true;
    }

    private function doesNamespaceHaveReservedWord(string $namespaces) : bool {
        $namespaceParts = explode('\\', $namespaces);
        foreach ($namespaceParts as $namespacePart) {
            if ($this->isReservedWord($namespacePart)) {
                return true;
            }
        }
        return false;
    }

}