<?php
declare(strict_types=1);
namespace Fr\ApiToken\Controller\Backend;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2020 Dirk Wenzel
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

use Fr\JohLib\Controller\Backend\AbstractBackendModuleController;
use Fr\ApiToken\Configuration\Extension;
use Fr\ApiToken\Configuration\Module\TokenModuleRegistration;
use Fr\ApiToken\Domain\Model\Token;
use Fr\ApiToken\Traits\TokenServiceTrait;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class TokenController
 */
class TokenController extends AbstractBackendModuleController
{
    use TokenServiceTrait;

    public const ROUTE = TokenModuleRegistration::ROUTE;
    public const TABLE_NAME = Token::TABLE_NAME;

    /**
     * @var SiteFinder
     */
    protected $siteFinder;

    /**
     * TokenController constructor.
     *
     * @param PageRenderer|null $pageRenderer
     * @param SiteFinder|null $siteFinder
     */
    public function __construct(PageRenderer $pageRenderer = null, SiteFinder $siteFinder = null)
    {
        parent::__construct($pageRenderer);
        $this->siteFinder = $siteFinder ?? GeneralUtility::makeInstance(SiteFinder::class);
    }

    /**
     * Displays s form for a new token
     * @param Token|null $newToken
     * @throws \Exception
     */
    public function newAction(Token $newToken = null)
    {
        $newToken = $newToken ?? GeneralUtility::makeInstance(Token::class);
        $secret = $this->tokenService->generateSecret();
        $hash = $this->tokenService->hash($secret);
        $identifier = $this->tokenService->generateIdentifier();

        $this->addFlashMessage(
            LocalizationUtility::translate('message.saveIdentifierAndSecretNow', Extension::KEY),
            LocalizationUtility::translate('header.saveIdentifierAndSecret', Extension::KEY),
            FlashMessage::INFO
        );

        $this->view->assignMultiple(
            [
                'identifier' => $identifier,
                'secret' => $secret,
                'hash' => $hash,
                'route' => static::ROUTE,
                'newToken' => $newToken,
                'sites' => $this->siteFinder->getAllSites()
            ]
        );
    }

    /**
     * Creates and persists a new
     * token from form values
     *
     * @param array $newToken
     * @throws StopActionException
     * @throws UnsupportedRequestTypeException
     * @throws \Exception
     */
    public function createAction(array $newToken) {
        $dateTimeZone = new \DateTimeZone(date_default_timezone_get());
        $validUntil = new \DateTime('+1 year', $dateTimeZone);

        $newToken['valid_until'] = $validUntil->format('U');
        $this->repository->add($newToken);
        $this->redirect('list');
    }

    /**
     * Delete a token
     *
     * @param int $token
     * @throws StopActionException
     * @throws UnsupportedRequestTypeException
     */
    public function deleteAction(int $token) {
        $this->repository->remove($token);
        $this->redirect('list');
    }
}
