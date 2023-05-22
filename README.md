# Installation instructions

[See Wiki home](https://gitlab.com/sb-dev-team/white-label-drupal-9/-/wikis/Home)

# Staging site operations

### Connect to Staging environment

[See instructions to setup your 1password public key](https://docs.google.com/document/d/1-zus9COVvHZsmavxAgZdaA3fEfybOlqRGl1Y5ZkNlYs/edit#heading=h.xzgpjcaienuq)

1. Make sure your public key has been shared to the hosting provider (this should be already done for the SBX dev team)
2. Add the configuration to `.ssh/config`:

```apacheconf
#### NRGI STAGING ###
Host nrgi-staging-cloudelligent
   Hostname 34.196.233.10
   User nrgi_dev
   Port 22
   IdentityAgent ~/.1password/agent.sock
```

## Export database and import locally

on application server:

```bash
cd /var/www/html
./vendor/bin/drush cr && ./vendor/bin/drush sql-dump --result-file=~/nrgi_staging.sql --gzip --extra-dump="--set-gtid-purged=OFF"
```

On your local machine - download staging database:

```bash
rsync -avz --progress nrgi-staging-cloudelligent:~/nrgi_staging.sql.gz ~/temp/nrgi/
```

**Database incompatibility workaround when importing DB to local MariaDB**

Unzip the database archive:

```bash
gunzip ~/temp/nrgi/nrgi_staging.sql.gz
```

Replace all `utf8mb4_0900_ai_ci` with `utf8mb4_unicode_ci`:

```bash
sed -i '' 's/utf8mb4_0900_ai_ci/utf8mb4_unicode_ci/g' ~/temp/nrgi/nrgi_staging.sql
```

DDEV import as usual on your local container:

```bash
ddev import-db --src ~/temp/nrgi/nrgi_staging.sql
```

## Import database on staging environement

Un-archive database:

```bash
gunzip ~/nrgi_staging_archive.sql.gz

```

Import:

``` bash
mysql -h nrgi-website-dev.co1xvf1mjdfa.us-east-1.rds.amazonaws.com -u nrgi_dev -p nrgi_dev_db < ~/nrgi_staging_archive.sql
```

(DB password can be found on staging's `settings.local.php`).

## NRGI PDF Generation.

## Local setup

Local setup for PDF generation in DDEV.

Login to browserless.io and copy the API key from the dashboard.
Client's $settings['browserless_api_key'] is already added into settings.php
Start NGROK to provide a public URL for your local ddev share

Copy the URL to your settings.local.php, eg

```bash
$settings['ngrok_url'] = 'https://be3c-87-224-41-135.ngrok-free.app';
````

If your site has $settings['trusted_host_patterns'] (hosting dependant), add your ngrox URL via settings.local.php,
eg

```bash
$settings['trusted_host_patterns'][] = '^be3c\-87\-224\-41\-135\.ngrok\-free\.app$'
```

Run the PDF generation script.
