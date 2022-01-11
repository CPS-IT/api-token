<?php
declare(strict_types=1);
namespace Fr\ApiToken\Configuration;

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



interface RestApiInterface
{

    /**
     * HTTP methods
     */
    public const METHOD_DELETE = 'DELETE';
    public const METHOD_GET = 'GET';
    public const METHOD_PATCH = 'PATCH';
    public const METHOD_POST = 'POST';
    public const METHOD_PUT = 'PUT';
    public const METHOD_UPDATE = 'UPDATE';

    /**
     * Valid methods
     */
    public const VALID_METHODS = [
        RestApiInterface::METHOD_DELETE,
        RestApiInterface::METHOD_GET,
        RestApiInterface::METHOD_PATCH,
        RestApiInterface::METHOD_POST,
        RestApiInterface::METHOD_PUT,
        RestApiInterface::METHOD_UPDATE,
    ];

    public const HEADER_NAME_IDENTIFIER = 'X-API-IDENTIFIER';
}
