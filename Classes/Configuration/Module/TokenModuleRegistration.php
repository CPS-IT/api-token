<?php
/**
 * This file is part of the api_token extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * README.md file that was distributed with this source code.
 */
namespace Fr\ApiToken\Configuration\Module;

use DWenzel\T3extensionTools\Configuration\ModuleRegistrationInterface;
use DWenzel\T3extensionTools\Configuration\ModuleRegistrationTrait;
use Fr\ApiToken\Configuration\Extension;

class TokenModuleRegistration implements ModuleRegistrationInterface
{
    use ModuleRegistrationTrait;

    public const ROUTE = 'site_ApiToken';

    static protected string $subModuleName = 'generatesecret';
    static protected string $mainModuleName = 'system';
    static protected string $vendorExtensionName = Extension::VENDOR_NAME . '.' . Extension::NAME;
    static protected array $controllerActions = [
        \Fr\ApiToken\Controller\Backend\TokenController::class => 'list,new,create,delete'
    ];

    static protected string $position = 'bottom';
    static protected array $moduleConfiguration = [
        'access' => 'admin',
        'icon' => 'EXT:api_token/Resources/Public/Icons/Extension.svg',
        'labels' => 'LLL:EXT:api_token/Resources/Private/Language/locallang_mod_token.xlf',
    ];
}
