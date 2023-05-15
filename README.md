# Installation instructions

[See Wiki home](https://gitlab.com/sb-dev-team/white-label-drupal-9/-/wikis/Home)

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
