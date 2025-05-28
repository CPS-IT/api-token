# CLI Commands

The API Token extension provides command-line tools for token management and administration.

## Available Commands

### `apitoken:generate`

Generate new API tokens from the command line.

#### Basic Usage

```bash
./vendor/bin/typo3 apitoken:generate
```

#### Interactive Mode

When run without parameters, the command enters interactive mode:

```bash
$ ./vendor/bin/typo3 apitoken:generate

API Token Generator
===================

Please provide a name for the token: Mobile App Authentication
Please provide a description: Token for authenticating mobile app requests
Expiration date [+1 year]: +6 months

Generating token...

✓ Token generated successfully!

Token Details:
--------------
Name:        Mobile App Authentication
Description: Token for authenticating mobile app requests
Identifier:  4a6f8b2e3d
Secret:      7a5c9f2b-4d8e-1a3c-9e5f-2b4d8e1a3c82
Valid Until: 2024-12-15 14:30:00

⚠️  SECURITY WARNING: This secret will not be displayed again!
   Please store it securely and never commit it to version control.
```

#### Non-Interactive Mode

For automation and CI/CD pipelines:

```bash
./vendor/bin/typo3 apitoken:generate \
    --name="CI/CD Token" \
    --description="Automated deployment authentication" \
    --expires="+3 months" \
    --no-interaction
```

#### Parameters

| Parameter | Short | Description | Default | Required |
|-----------|-------|-------------|---------|----------|
| `--name` | `-n` | Token name for identification | - | Yes |
| `--description` | `-d` | Detailed description | - | No |
| `--expires` | `-e` | Expiration date/interval | `+1 year` | No |
| `--no-interaction` | `-N` | Run without prompts | `false` | No |
| `--output-format` | `-f` | Output format (text, json, yaml) | `text` | No |

#### Expiration Formats

The `--expires` parameter accepts various formats:

```bash
# Relative formats
--expires="+1 year"
--expires="+6 months"
--expires="+30 days"
--expires="+2 weeks"

# Absolute dates
--expires="2024-12-31"
--expires="2024-12-31 23:59:59"

# ISO 8601 format
--expires="2024-12-31T23:59:59+00:00"
```

#### Output Formats

##### Text Format (Default)
```bash
./vendor/bin/typo3 apitoken:generate --name="Test" --no-interaction
```

```
✓ Token generated successfully!

Token Details:
--------------
Name:        Test
Identifier:  4a6f8b2e3d
Secret:      7a5c9f2b-4d8e-1a3c-9e5f-2b4d8e1a3c82
Valid Until: 2025-06-15 14:30:00
```

##### JSON Format
```bash
./vendor/bin/typo3 apitoken:generate \
    --name="API Token" \
    --output-format=json \
    --no-interaction
```

```json
{
    "status": "success",
    "token": {
        "name": "API Token",
        "description": null,
        "identifier": "4a6f8b2e3d",
        "secret": "7a5c9f2b-4d8e-1a3c-9e5f-2b4d8e1a3c82",
        "valid_until": "2025-06-15T14:30:00+00:00",
        "created_at": "2024-06-15T14:30:00+00:00"
    }
}
```

##### YAML Format
```bash
./vendor/bin/typo3 apitoken:generate \
    --name="API Token" \
    --output-format=yaml \
    --no-interaction
```

```yaml
status: success
token:
  name: API Token
  description: null
  identifier: 4a6f8b2e3d
  secret: 7a5c9f2b-4d8e-1a3c-9e5f-2b4d8e1a3c82
  valid_until: '2025-06-15T14:30:00+00:00'
  created_at: '2024-06-15T14:30:00+00:00'
```

## Advanced Usage Examples

### CI/CD Integration

Create a token for automated deployments:

```bash
#!/bin/bash
# deployment-script.sh

# Generate deployment token
TOKEN_OUTPUT=$(./vendor/bin/typo3 apitoken:generate \
    --name="Deployment $(date +%Y%m%d)" \
    --description="Automated deployment token" \
    --expires="+1 hour" \
    --output-format=json \
    --no-interaction)

# Extract credentials
IDENTIFIER=$(echo "$TOKEN_OUTPUT" | jq -r '.token.identifier')
SECRET=$(echo "$TOKEN_OUTPUT" | jq -r '.token.secret')

# Use in deployment
curl -X POST "https://api.example.com/deploy" \
    -H "x-api-identifier: $IDENTIFIER" \
    -H "application-authorization: $SECRET" \
    -d '{"action": "deploy", "environment": "production"}'
```

### Environment-Specific Tokens

Generate different tokens for different environments:

```bash
# Development environment
./vendor/bin/typo3 apitoken:generate \
    --name="Development API" \
    --description="Development environment access" \
    --expires="+1 month" \
    --no-interaction

# Staging environment
./vendor/bin/typo3 apitoken:generate \
    --name="Staging API" \
    --description="Staging environment access" \
    --expires="+2 weeks" \
    --no-interaction

# Production environment
./vendor/bin/typo3 apitoken:generate \
    --name="Production API" \
    --description="Production environment access" \
    --expires="+6 months" \
    --no-interaction
```

### Batch Token Generation

Create multiple tokens at once:

```bash
#!/bin/bash
# generate-multiple-tokens.sh

declare -a SERVICES=("mobile-app" "web-frontend" "analytics-service" "backup-service")

for service in "${SERVICES[@]}"; do
    echo "Generating token for $service..."

    ./vendor/bin/typo3 apitoken:generate \
        --name="$service Token" \
        --description="Authentication token for $service" \
        --expires="+1 year" \
        --output-format=json \
        --no-interaction > "${service}-token.json"

    echo "✓ Token saved to ${service}-token.json"
done
```

### Token Validation Script

Validate generated tokens:

```bash
#!/bin/bash
# validate-token.sh

if [ $# -ne 2 ]; then
    echo "Usage: $0 <identifier> <secret>"
    exit 1
fi

IDENTIFIER=$1
SECRET=$2

# Test the token
RESPONSE=$(curl -s -w "%{http_code}" \
    -H "x-api-identifier: $IDENTIFIER" \
    -H "application-authorization: $SECRET" \
    "https://your-api.com/validate")

HTTP_CODE="${RESPONSE: -3}"

if [ "$HTTP_CODE" -eq 200 ]; then
    echo "✓ Token is valid"
    exit 0
else
    echo "✗ Token validation failed (HTTP $HTTP_CODE)"
    exit 1
fi
```

## Error Handling

### Common Errors

#### Database Connection Error
```bash
$ ./vendor/bin/typo3 apitoken:generate

[ERROR] Database connection failed. Please check your database configuration.
```

**Solution**: Verify your database connection in `config/system/settings.php`.

#### Permission Error
```bash
$ ./vendor/bin/typo3 apitoken:generate

[ERROR] Insufficient permissions to create tokens.
```

**Solution**: Ensure you're running the command as a user with appropriate database permissions.

#### Invalid Expiration Date
```bash
$ ./vendor/bin/typo3 apitoken:generate --expires="invalid-date"

[ERROR] Invalid expiration date format. Use formats like '+1 year', '2024-12-31', or ISO 8601.
```

**Solution**: Use valid date formats as shown in the examples above.

### Exit Codes

The command returns different exit codes for automation:

- `0`: Success
- `1`: General error
- `2`: Invalid arguments
- `3`: Database error
- `4`: Permission error

## Integration with Build Tools

### GitHub Actions

```yaml
# .github/workflows/deploy.yml
name: Deploy with API Token

on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4

      - name: Generate API Token
        run: |
          ddev composer install
          TOKEN_JSON=$(ddev exec ./vendor/bin/typo3 apitoken:generate \
            --name="GitHub Action Deploy" \
            --expires="+1 hour" \
            --output-format=json \
            --no-interaction)

          echo "IDENTIFIER=$(echo $TOKEN_JSON | jq -r '.token.identifier')" >> $GITHUB_ENV
          echo "SECRET=$(echo $TOKEN_JSON | jq -r '.token.secret')" >> $GITHUB_ENV

      - name: Deploy
        run: |
          curl -X POST "${{ secrets.DEPLOY_URL }}" \
            -H "x-api-identifier: $IDENTIFIER" \
            -H "application-authorization: $SECRET"
```

### Makefile Integration

```makefile
# Makefile
.PHONY: generate-token deploy

generate-token:
    @echo "Generating API token..."
    @./vendor/bin/typo3 apitoken:generate \
        --name="Deployment Token" \
        --expires="+1 hour" \
        --output-format=json \
        --no-interaction > .deployment-token.json
    @echo "Token saved to .deployment-token.json"

deploy: generate-token
    @IDENTIFIER=$$(jq -r '.token.identifier' .deployment-token.json); \
    SECRET=$$(jq -r '.token.secret' .deployment-token.json); \
    curl -X POST "$(DEPLOY_URL)" \
        -H "x-api-identifier: $$IDENTIFIER" \
        -H "application-authorization: $$SECRET"
    @rm .deployment-token.json
```

## Best Practices

1. **Secure Storage**: Never store secrets in version control
2. **Short-Lived Tokens**: Use short expiration times for automated processes
3. **Descriptive Names**: Use clear, descriptive names for easy identification
4. **JSON Output**: Use JSON format for programmatic processing
5. **Error Handling**: Always check exit codes in scripts
6. **Token Rotation**: Regularly rotate long-lived tokens

## Next Steps

- [Backend Module Guide](BackendModule.md)
- [Usage Examples](Usage.md)
- [API Reference](ApiReference.md)
