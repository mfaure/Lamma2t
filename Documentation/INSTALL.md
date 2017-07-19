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

* create a user `mm3g`
* place `lamma2t-cron.sh` in `/home/mm3g/bin/`
* configure a cron job to run `lamma2t-cron.sh` this way:
  ```
  # Teach day at 8h30, 8h45, 9h30, 9h45, 22h30, 22h45, 23h30, 23h45
  30,45 8,9,22,23	* * *	/home/mm3g/bin/lamma2t-cron.sh
  ```

### Web application

Locally run `gulp default`. All the webapp is in the `build/` directory. Simply upload its content to the webserver.

### Configure

* Add piwik values to index.php and about.html (replace `MyPiwikServer`, `MyPiwikSiteId`, `MyPiwikCompleteServerSiteId`)