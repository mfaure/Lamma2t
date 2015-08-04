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
