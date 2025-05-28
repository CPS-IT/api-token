<?php

declare(strict_types=1);

/*
 * This file is part of the api_token Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * README.md file that was distributed with this source code.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Rector\Config\RectorConfig;
use Rector\Php82\Rector\Class_\ReadOnlyClassRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\ValueObject\PhpVersion;
use Ssch\TYPO3Rector\Set\Typo3LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/Classes',
        __DIR__ . '/Configuration',
        __DIR__ . '/Tests',
        __DIR__ . '/ext_*.php',
    ]);

    $rectorConfig->skip([
        __DIR__ . '/.Build',
        __DIR__ . '/config',
        __DIR__ . '/.ddev',
        __DIR__ . '/var',
        // Skip readonly class rector for Random class due to TYPO3 compatibility issues
        ReadOnlyClassRector::class => [
            __DIR__ . '/Classes/Crypto/Random.php',
        ],
    ]);

    $rectorConfig->phpVersion(PhpVersion::PHP_83);

    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_83,
        Typo3LevelSetList::UP_TO_TYPO3_12,
    ]);
};
