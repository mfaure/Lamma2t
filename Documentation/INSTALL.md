# How to install Lamma2T

## Prerequesites

* Ubuntu 14.04
* wget
* pngcrush
* PHP 5.3
* php5-intl (IntlDateFormatter)
* Gulp

One click dependency install : `sudo apt-get install wget pngcrush php5-intl`

## Install

### Script to grab the forecasts

Configure a cron job to run `lamma2t-cron.sh`

### Web application

Locally run `gulp default`. All the webapp is in the `build/` directory. Simply upload its content to the webserver.

### Configure

* Add piwik values to index.php and about.html