<?php declare(strict_types=1);

namespace Cspray\Yape\Cli;

use Cspray\Yape\Cli\Internal\ClassSignatureDefinition;
use Cspray\Yape\Cli\Internal\EnumDefinition;
use stdClass;

/**
 *
 * @package Cspray\Yape\Test
 * @license See LICENSE in source root
 */
class NoDependenciesEnumTest extends EnumTest {

    public function equalsProvider() : array {
        return [
            ['YourVendor\\YourApp\\YourEnum\\Compass::North', 'YourVendor\\YourApp\\YourEnum\\Compass::North', true],
            ['YourVendor\\YourApp\\YourEnum\\Compass::North', 'YourVendor\\YourApp\\YourEnum\\Compass::South', false],
            ['YourVendor\\YourApp\\YourEnum\\Compass::North', 'YourVendor\\YourApp\\YourEnum\\Compass::East', false],
            ['YourVendor\\YourApp\\YourEnum\\Compass::North', 'YourVendor\\YourApp\\YourEnum\\Compass::West', false],
            ['YourVendor\\YourApp\\YourEnum\\Compass::North', function() { return new stdClass(); }, false],
            ['YourVendor\\YourApp\\YourEnum\\Compass::South', function() { return new stdClass(); }, false],
            ['YourVendor\\YourApp\\YourEnum\\Compass::East', function() { return new stdClass(); }, false],
            ['YourVendor\\YourApp\\YourEnum\\Compass::West', function() { return new stdClass(); }, false],
        ];
    }

    static protected function getEnumDefinition() : EnumDefinition {
        return new EnumDefinition(
            new ClassSignatureDefinition('YourVendor\\YourApp\\YourEnum\\Compass'),
            'North',
            'South',
            'East',
            'West'
        );
    }
}