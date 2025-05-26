<?php

declare(strict_types=1);

/*
 * This file is part of the api_token Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * README.md file that was distributed with this source code.
 */

use TYPO3\CodingStandards\CsFixerConfig;

$config = CsFixerConfig::create()
    ->setHeader(
        'This file is part of the api_token Extension for TYPO3 CMS.' . PHP_EOL .
        '' . PHP_EOL .
        'For the full copyright and license information, please read the' . PHP_EOL .
        'README.md file that was distributed with this source code.'
    );

$config->getFinder()
    ->in(__DIR__)
    ->exclude([
        '.Build',
        'config',
        '.ddev',
        'node_modules',
        'var',
    ]);

return $config;
