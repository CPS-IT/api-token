# CLI Commands

## `apitoken:generate`

Generates a new API token, stores it and prints identifier and secret.
The secret is shown only once; only its hash is stored.

```bash
./vendor/bin/typo3 apitoken:generate [options]
```

### Options

| Option | Short | Description | Default |
|--------|-------|-------------|---------|
| `--name` | | Token name. Asked interactively when omitted. | - |
| `--description` | `-d` | Token description. Asked interactively when omitted. | - |
| `--expires` | `-e` | Expiration as interval or date (see below) | `+1 year` |
| `--json` | `-j` | Print the result as JSON | off |
| `--no-interaction` | `-n` | Symfony's standard flag: never ask. Pass `--name` and `--description` with it. | off |

### Expiration formats

`--expires` accepts everything PHP's `DateTime` understands, evaluated in
the server's default time zone. The date must lie in the future.

```bash
# Relative
--expires="+1 year"
--expires="+6 months"
--expires="+2 weeks"
--expires="+1 hour"

# Absolute
--expires="2027-12-31"
--expires="2027-12-31 23:59:59"

# ISO 8601
--expires="2027-12-31T23:59:59+00:00"
```

### Examples

Interactive:

```bash
./vendor/bin/typo3 apitoken:generate
```

Non-interactive:

```bash
./vendor/bin/typo3 apitoken:generate \
    --name="CI/CD Token" \
    --description="Automated deployment authentication" \
    --expires="+3 months" \
    --no-interaction
```

Text output:

```
 [OK] Your token was successfully generated.
      Identifier: 4a6f8b2e3d0c1
      Secret: 7a5c9f2b-4d8e-4a3c-9e5f-2b4d8e1a3c82
      Valid until: 2027-02-11T14:30:00+01:00
      (Please keep information safely and secure. Token is shown only once.)
```

JSON output (`--json`):

```json
{
    "name": "CI/CD Token",
    "description": "Automated deployment authentication",
    "identifier": "4a6f8b2e3d0c1",
    "secret": "7a5c9f2b-4d8e-4a3c-9e5f-2b4d8e1a3c82",
    "valid_until": "2027-02-11T14:30:00+01:00"
}
```

Use in a script:

```bash
TOKEN_JSON=$(./vendor/bin/typo3 apitoken:generate \
    --name="Deploy $(date +%Y%m%d)" --description="" \
    --expires="+1 hour" --json --no-interaction)

IDENTIFIER=$(echo "$TOKEN_JSON" | jq -r '.identifier')
SECRET=$(echo "$TOKEN_JSON" | jq -r '.secret')

curl -X POST "https://your-site.com/api/endpoint" \
    -H "x-api-identifier: $IDENTIFIER" \
    -H "application-authorization: $SECRET"
```

### Errors and exit codes

| Exit code | Meaning |
|-----------|---------|
| `0` | Token created |
| `1` | Invalid input (empty name, unparsable or past `--expires`) |

```bash
$ ./vendor/bin/typo3 apitoken:generate --expires="invalid-date"

 [ERROR] Invalid expiration "invalid-date". Use formats like "+1 year", "2027-12-31" or ISO 8601.
```

With `--json`, errors are printed as `{"error": "..."}`.

## Revoking tokens

There is no CLI command for revocation. Disable or delete the token in the
[backend module](BackendModule.md).

## Best Practices

1. **Secure Storage**: Never store secrets in version control
2. **Short-Lived Tokens**: Use short `--expires` values for automated processes
3. **Descriptive Names**: Use clear names so tokens can be identified and revoked
4. **JSON Output**: Use `--json` for programmatic processing
5. **Error Handling**: Check the exit code in scripts

## Next Steps

- [Backend Module](BackendModule.md)
- [Usage Examples](Usage.md)
- [API Reference](ApiReference.md)
