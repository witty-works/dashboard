# Stripe CLI Integration

This document describes how to use Stripe CLI for development and testing.

## Installation

-   Download and install Stripe CLI: https://stripe.com/docs/stripe-cli

## Usage

-   Start Stripe listener:
    ```bash
    ./stripe_listen.sh
    ```

## Notes

-   For more details, see the official Stripe CLI documentation.

# Subscription & Team Management

## Create subscriptions

If there are any changes to the subscription, please add a comment to the entry describing the changes and tagging whoever needs to take note.

Please add the URL to the Bexio invoice to the notes.

```sql
# Open SQL database connection
$ platform sql -e main --app dashboard -p [project ID]

# Find team ID of user
SET @EMAIL := '[OWNER EMAIL]';
SELECT @TEAM_ID := current_team_id FROM users WHERE email = @EMAIL;

# Create Subscription (either by exact date or now)
SET @STARTS_AT = "YYYY-MM-DD 00:00:00"
SET @STARTS_AT = NOW();
# Adjust interval as needed
SET @RENEWS_AT = DATE_ADD(@STARTS_AT, INTERVAL 12 MONTH);
# If the subscription is supposed to end, set a date here .. potentially using @RENEWS_AT
SET @ENDS_AT = NULL;
# Adjust quantity of the licenses as needed
SET @QUANTITY = 5;
# Adjust plan type to 'teams' or 'enterprise'
SET @PLAN = 'enterprise';
# Set "witty_contract_id" and "company_name" (or fetch the name of the team as the company name)
SET @CONTRACT_ID = '';
SET @COMPANY_NAME = '';
SET @COMPANY_NAME = SELECT name FROM teams WHERE id = @TEAM_ID;

INSERT INTO subscriptions (team_id, type, stripe_id, stripe_status, stripe_price, quantity, starts_at, renews_at, ends_at, created_at, updated_at, witty_contract_id, company_name) VALUES
(@TEAM_ID, 'default', CONCAT('invoice_', @STARTS_AT), 'active', @PLAN, @QUANTITY, @STARTS_AT, @RENEWS_AT, @ENDS_AT, @STARTS_AT, @STARTS_AT, @CONTRACT_ID, @COMPANY_NAME);

INSERT INTO subscription_items (subscription_id, stripe_id, stripe_product, stripe_price, quantity, created_at, updated_at) VALUES
(LAST_INSERT_ID(), CONCAT('invoice_', @STARTS_AT), 'prod_LS1IgnPg9J0Qeh', @PLAN, @QUANTITY, @STARTS_AT, @STARTS_AT);

# Set "witty_contract_id" and "company_name"
UPDATE subscriptions SET witty_contract_id = @CONTRACT_ID, company_name = @COMPANY_NAME WHERE team_id = @TEAM_ID;

# Set subscription end YYYY-MM-DD
UPDATE subscriptions SET ends_at = '[FILL IN THE END DATE]' WHERE team_id = @TEAM_ID;
```

## Change owner

**Note if this is a subscription Stripe you also need to update the email on Stripe.**

```sql
# Open SQL database connection
$ platform sql -e main --app dashboard -p 56xlfiudba6c2

# Find IDs of users
SELECT @CURRENT_OWNER_ID := id FROM users WHERE email = '[CURRENT OWNER EMAIL]';
SELECT @NEW_OWNER_ID := id FROM users WHERE email = '[NEW OWNER EMAIL]';

# Find ID of relevant team
SELECT @TEAM_ID := current_team_id FROM users WHERE id = @CURRENT_OWNER_ID;

# Remove old owner and set new owner from personal team
UPDATE teams SET user_id = @NEW_OWNER_ID where id = @TEAM_ID and personal_team = 1;

# Make the old owner admin in the team
INSERT INTO team_user (user_id, team_id, role, created_at, updated_at) VALUES
(@CURRENT_OWNER_ID, @TEAM_ID, 'admin', NOW(), NOW());

# Make sure the new admin is no longer also a user on the team
DELETE FROM team_user WHERE user_id = @NEW_OWNER_ID and team_id = @TEAM_ID;

# Assign the personal team from the new owner to the old owner
SELECT @NEW_TEAM_ID := current_team_id FROM users WHERE id = @NEW_OWNER_ID;
UPDATE teams SET user_id = @CURRENT_OWNER_ID where id = @NEW_TEAM_ID and personal_team = 1;
```

## Add user to team

**Note this should only be done for users that do not actively use Witty**

```sql
# Open SQL database connection
$ platform sql -e main --app dashboard -p 56xlfiudba6c2

# Find IDs of user/team
SELECT @USER_ID := id FROM users WHERE email = '[USER EMAIL]';
SELECT @TEAM_ID := [TEAM ID];
# Role of the user within the team: 'admin' or 'user'
SELECT @ROLE := 'admin';

# Add user to team
INSERT INTO team_user (team_id, user_id, role, created_at, updated_at) values (@TEAM_ID,@USER_ID,@ROLE,NOW(),NOW());
```

## Copy dictionary items from one team to another

```sql
# Open SQL database connection
$ platform sql -e main --app dashboard -p 56xlfiudba6c2

# Set ID of the team FROM which to copy
SELECT @SOURCE_TEAM_ID := [SOURCE TEAM ID];
# Set ID if the team TO which to copy
SELECT @TARGRT_TEAM_ID := [TARGET TEAM ID];

# Copy all term replacements
INSERT IGNORE INTO term_replacements (team_id, term, replacement, language_code, explanation, url, emoji, word_type, created_at, updated_at)
  SELECT @TARGRT_TEAM_ID, term, replacement, language_code, explanation, url, emoji, word_type, NOW(), NOW() FROM term_replacements WHERE team_id = @SOURCE_TEAM_ID;

# Copy a specific language term replacements
SELECT @LANGUAGE := ['en' or 'de'];

INSERT IGNORE INTO term_replacements (team_id, term, replacement, language_code, explanation, url, emoji, word_type, created_at, updated_at)
  SELECT @TARGRT_TEAM_ID, term, replacement, language_code, explanation, url, emoji, word_type, NOW(), NOW() FROM term_replacements WHERE team_id = @SOURCE_TEAM_ID AND language_code = @LANGUAGE;

# Copy only the term replacements for any language
INSERT IGNORE INTO term_replacements (team_id, term, replacement, language_code, explanation, url, emoji, word_type, created_at, updated_at)
  SELECT @TARGRT_TEAM_ID, term, replacement, language_code, explanation, url, emoji, word_type, NOW(), NOW() FROM term_replacements WHERE team_id = @SOURCE_TEAM_ID AND language_code IS NULL;
```

## Change end date of free trial

Impersonate the relevant team owner and go to

[https://dashboard.witty.works/en/team/subscription?trial_ends_at=](https://dashboard.witty.works/en/team/subscription?trial_ends_at=2024-12-04)[yyyy-mm-dd]

### Update feature defaults

To update the default `user_licenses`, `term_replacements` and `false_positives`
counts please run the following query:

```
UPDATE teams SET user_licenses = [number of licenses], term_replacements = [number of term replacements], false_positives = [number of false positives] WHERE user_id = [some id];
```
