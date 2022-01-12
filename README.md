# Fr/ApiToken

## Generates API credentials
### Generate by CLI command

`./vendor/bin/typo3cms apitoken:generate`
* Asks for token name,  a descriptive help to identify record, no technical usage.
* Asks for token description, a descriptive help to identify record, no technical usage.

Puts out:
  ```
Your token was successfully generated.

Identifier: 4a6*******d

Secret: 7a5-*****c82

(Please keep information safely and secure. Token is shown only once.)
  ```

Persists name, description, identifier, date of expiration (1 year) and hash value to verify token by API call.
Please note secret (token) securely, it will not be shown a second time. 

### Generate in Typo3 backend 

tbd


## Use in your extension

1. Require extension: `composer req fr/api-token`
2. Implement Token check in your API Handler 

```
if(\Fr\ApiToken\Request\Validation\ApiTokenAuthenticator::isNotAuthenticated($request)){
  return \Fr\ApiToken\Request\Validation\ApiTokenAuthenticator::returnErrorResponse();
}
```
3. Put into API request header 
* `x-api-identifier = {your x-api-identifier generated above}`and 
* `application-authorization = {your secret token generated above}`


# License

Copyright notice

(c) 2021 Team Bravo <info@familie-redlich.de>
All rights reserved

The GNU General Public License can be found at
http://www.gnu.org/copyleft/gpl.html.
A copy is found in the text file GPL.txt and important notices to the license
from the author is found in LICENSE.txt distributed with these scripts.
This script is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
This copyright notice MUST APPEAR in all copies of the script!