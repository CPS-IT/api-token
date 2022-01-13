<?php

namespace Fr\ApiToken\Configuration\Module;

use DWenzel\T3extensionTools\Configuration\ModuleRegistrationInterface;
use DWenzel\T3extensionTools\Configuration\ModuleRegistrationTrait;
use Fr\ApiToken\Configuration\Extension;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2020 Dirk Wenzel <wenzel@cps-it.de>
 *  All rights reserved
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 * A copy is found in the text file GPL.txt and important notices to the license
 * from the author is found in LICENSE.txt distributed with these scripts.
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
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
