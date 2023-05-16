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

## Export database

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
sed -i '' 's/utf8mb4_0900_ai_ci/utf8mb4_unicode_ci/g' nrgi_staging.sql
```

DDEV import as usual on your local container:

```bash
ddev import-db --src ~/temp/nrgi/nrgi_staging.sql
```

## Import database

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

The custom NRGI plugin makes the PDF generation ready to work on your local env without usiong ```ddev share```.
You just need to create additional service in your DDEV setup.

Please create file "{PROJECT_ROOT}/.ddev/docker-compose.chromedriver.yml" with contents:

```yaml
version: '3.6'
services:
  chromedriver:
    container_name: ddev-${DDEV_SITENAME}-chromedriver
    image: browserless/chrome:latest
    labels:
      # These labels ensure this service is discoverable by ddev
      com.ddev.site-name: ${DDEV_SITENAME}
      com.ddev.approot: $DDEV_APPROOT
    external_links:
      - ddev-router:${DDEV_SITENAME}.${DDEV_TLD}
  # This links the Chromedriver service to the web service defined
  # in the main docker-compose.yml, allowing applications running
  # in the web service to access the driver at `chromedriver`.
  web:
    links:
      - chromedriver:$DDEV_HOSTNAME
```

Then run ```ddev restart``` and new service with headless Chrome browser will be launched.
