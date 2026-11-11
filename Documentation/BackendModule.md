# Backend Module

**System > API Token**, available to administrators only.

## Token list

Shows name, identifier, creation date, state and expiry of every token
that is not deleted. Disabled and expired tokens are struck through.

| State | Meaning |
|-------|---------|
| valid in time | Authenticates until "Valid Until" |
| invalid | Expired - does not authenticate |
| disabled (revoked) | Disabled via the "Visible" toggle - does not authenticate |

Clicking a row opens the record. There you can

- disable or re-enable the token ("Visible" toggle on the *Access* tab),
- change "Valid Until",
- edit name and description.

Identifier and hash are read-only.

## Creating a token

1. Click **Generate new token**.
2. Enter a name (required) and a description.
3. Click **Submit**.

Identifier and secret are generated on the server when you submit, and
shown **once** on the next page. Copy both before leaving the page. The
secret cannot be recovered; only its hash is stored. Tokens created here
are valid for one year.

## Revoking a token

Either action takes effect with the next request:

- **Disable** it in the record (toggle "Visible" off). Reversible.
- **Delete** it with the trash icon in the list (asks for confirmation).
  The deletion goes through TYPO3's DataHandler, so it appears in the
  backend log and the record can be restored from the recycler.
