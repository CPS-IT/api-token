#
# Table definition for "tx_apitoken_domain_model_token"
#
CREATE TABLE tx_apitoken_domain_model_token(
    name VARCHAR(255) NOT NULL DEFAULT '',
    identifier TINYTEXT NOT NULL DEFAULT '',
    description TEXT NOT NULL DEFAULT '',
    valid_until INT(11) NOT NULL DEFAULT '0',
    hash VARCHAR(255) NOT NULL DEFAULT '',
    site VARCHAR(255) NOT NULL DEFAULT ''
);

