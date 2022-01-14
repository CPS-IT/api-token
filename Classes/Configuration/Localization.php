<?php
declare(strict_types=1);
/**
 * This file is part of the iki Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * README.md file that was distributed with this source code.
 */
namespace Fr\ApiToken\Configuration;

use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\Exception\MissingArrayPathException;

abstract class Localization
{
    public static $extensionKey = Extension::KEY;

    public const TYPE_DEFAULT = 'default';
    public const TYPE_DB = 'db';
    public const TYPE_BACKEND = 'be';
    public const TYPE_CORE_GENERAL = 'core_general';
    public const TYPE_CORE_TABS = 'core_tabs';
    public const TYPE_MODULE = 'module';

    protected const FILES = [
        self::TYPE_DEFAULT => 'locallang',
        self::TYPE_DB => 'locallang_db',
        self::TYPE_BACKEND => 'locallang_be',
        self::TYPE_CORE_GENERAL => 'locallang_general',
        self::TYPE_CORE_TABS => 'Form/locallang_tabs',
        self::TYPE_MODULE => 'locallang_mod_%s',
    ];

    /**
     * @param string $tableName
     * @param bool $fromTca
     * @param bool $translate
     * @return string
     */
    public static function forTable(string $tableName, bool $fromTca = false, bool $translate = false): string
    {
        if ($fromTca) {
            try {
                $localizationString = ArrayUtility::getValueByPath($GLOBALS['TCA'], $tableName . '/ctrl/title');
            } catch (MissingArrayPathException $e) {
            }
        }
        if (!isset($localizationString)) {
            $localizationString = self::buildLocalizationString(self::TYPE_DB, $tableName);
        }
        return $translate ? self::translate($localizationString) : $localizationString;
    }

    /**
     * @param string $fieldName
     * @param string $tableName
     * @param string|null $item
     * @param bool $translate
     * @return string
     */
    public static function forField(string $fieldName, string $tableName, string $item = null, bool $translate = false): string
    {
        $localizationKey = $tableName . '.' . $fieldName;
        if (is_string($item) && strlen($item) > 0) {
            $localizationKey .= '.' . $item;
        }
        $localizationString = self::buildLocalizationString(self::TYPE_DB, $localizationKey);
        return $translate ? self::translate($localizationString) : $localizationString;
    }

    /**
     * @param int $pageType
     * @param string|null $state
     * @param bool $translate
     * @return string
     */
    public static function forPageType(int $pageType, string $state = null, bool $translate = false): string
    {
        return static::forField('doktype', 'pages', $pageType . ($state !== null ? '.' . $state : ''), $translate);
    }

    /**
     * @param string $tabName
     * @param bool $fromCore
     * @param bool $translate
     * @return string
     */
    public static function forTab(string $tabName, bool $fromCore = false, bool $translate = false): string
    {
        if ($fromCore) {
            $type = self::TYPE_CORE_TABS;
            $localizationKey = $tabName;
        } else {
            $type = self::TYPE_DB;
            $localizationKey = 'tabs.' . $tabName;
        }
        $localizationString = self::buildLocalizationString($type, $localizationKey);
        return $translate ? self::translate($localizationString) : $localizationString;
    }

    /**
     * @param string $paletteName
     * @param bool $translate
     * @return string
     */
    public static function forPalette(string $paletteName, bool $translate = false): string
    {
        $localizationString = self::buildLocalizationString(self::TYPE_DB, 'palettes.' . $paletteName);
        return $translate ? self::translate($localizationString) : $localizationString;
    }

    /**
     * @param string $validationName
     * @param string $type
     * @param bool $translate
     * @return string
     */
    public static function forFormValidation(string $validationName, string $type, bool $translate = false): string
    {
        $localizationString = self::buildLocalizationString(self::TYPE_BACKEND, 'validation.' . $validationName . '.' . $type);
        return $translate ? self::translate($localizationString) : $localizationString;
    }

    /**
     * @param string $identifier
     * @param bool $translate
     * @return string
     */
    public static function forCoreTranslation(string $identifier, bool $translate = false): string
    {
        $localizationString = self::buildLocalizationString(self::TYPE_CORE_GENERAL, 'LGL.' . $identifier);
        return $translate ? self::translate($localizationString) : $localizationString;
    }

    /**
     * @param string $moduleName
     * @param bool $translate
     * @return string
     */
    public static function forModule(string $moduleName, bool $translate = false): string
    {
        $localizationString = sprintf(self::buildLocalizationString(self::TYPE_MODULE), $moduleName);
        return $translate ? self::translate($localizationString) : $localizationString;
    }

    /**
     * @param string $pluginName
     * @param bool $translate
     * @return string
     */
    public static function forPlugin(string $pluginName, bool $translate = false): string
    {
        $localizationString = self::buildLocalizationString(self::TYPE_DB, 'plugins.' . lcfirst($pluginName));
        return $translate ? self::translate($localizationString) : $localizationString;
    }

    /**
     * @param string $pluginName
     * @param bool $translate
     * @return string
     */
    public static function forPluginDescription(string $pluginName, bool $translate = false): string
    {
        $localizationString = self::buildLocalizationString(self::TYPE_DB, 'plugins.' . lcfirst($pluginName) . '.description');
        return $translate ? self::translate($localizationString) : $localizationString;
    }

    /**
     * @param string $localizationKey
     * @return string
     */
    public static function translate(string $localizationKey): string
    {
        return self::getLanguageService()->sL($localizationKey);
    }

    /**
     * @param string $type
     * @param string|null $localizationKey
     * @param string|null $language
     * @return string
     */
    protected static function buildLocalizationString(string $type = self::TYPE_DEFAULT, string $localizationKey = null, string $language = null): string
    {
        $fileName = self::FILES[$type] ?: self::FILES[self::TYPE_DEFAULT];
        $language = $language ? ($language . '.') : '';
        $localizationKey = $localizationKey ? (':' . $localizationKey) : '';
        $extensionKey = (self::isCoreType($type) ? 'core' : static::$extensionKey) ?? Extension::KEY;

        /** @noinspection TranslationMissingInspection */
        return sprintf(
            'LLL:EXT:%s/Resources/Private/Language/%s%s.xlf%s',
            $extensionKey,
            $language,
            $fileName,
            $localizationKey
        );
    }

    /**
     * @param string $type
     * @return bool
     */
    protected static function isCoreType(string $type): bool
    {
        return in_array($type, [
            self::TYPE_CORE_GENERAL,
            self::TYPE_CORE_TABS,
        ]);
    }

    /**
     * @return LanguageService
     */
    protected static function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
