# Lamma2T

 An enhanced view of Consorzio Lamma Rete weather forecast in Bonifacio (France) / Maddalena (Italia)

## Problem

Using [Lamma Rete weather forecast](http://www.lamma.rete.toscana.it/meteo/modelli/ventomare) I encounter two main issues :

* The geographical zone (e.g. Bonifacio) is not part of the URL: user must do an action to get to it.
* Webpage is heavy and not optimised for 3G (data on cell phone), and this may be costy if you are abroad

## Solution

* Have a single page optimised for web performance
* Enhance the User eXperience

## Prerequesites

* Ubuntu 14.04
* wget
* pngcrush
* PHP 5.3
* php5-intl (IntlDateFormatter)

One click dependency install : `sudo apt-get install wget pngcrush php5-intl`

## Install

@@@

## Web performance

### Methodology

Use Firefox, Press CTRL-SHIT-I, use Network panel and reload page. Make 3 measures, and compute average value for each metric.

### Values

Page [Lamma Rete (alternate version)](http://www.lamma.rete.toscana.it/meteo/modelli/ventomare) with only first forecast (2015-08-05)

* Requests : 68
* Total weight (KB) : 813.73
* Load time (s) : 4.79

Page [Lamma2T](http://mm3g.ovh/lamma2t/) (containing 37 forecasts) (2015-08-05)

* Requests : 32
* Total weight (KB) : 817,43
* Load time (s) : 1.03 