# Lamma2T

 A performance optimised and user-experience enhanced view of Consorzio Lamma Rete weather forecast for Bonifacio (France) / Maddalena archipelago (Italia)

## Issues encountered

Using [Lamma Rete weather forecast](http://www.lamma.rete.toscana.it/meteo/modelli/ventomare) I encounter two main issues :

* Getting accessing to the Bonifacio forecasts is tedious.
* Each request is slow, and moreover you have to make a new request for each new hour of the forecast, which is really annoying.
* The page of the forecast contains lots of useless information, including heavy images, which slows down the loading of the page. Having to reload the page each time you want a new hour for the forecast, you have to be really zen before getting mad :)
* The page is not optimised for being viewed on mobile devices.
* Last, the mobile app of Lamma Rete doesn't solve any of these issues.

## Solutions brought by Lamma2T

Solve all the issues listed above by having a single page optimised, which means:

* All the hours of the forecast are loaded in **one time** (no more waiting between two hours of a forecast)
* The hours of a forecast are translated to local time of Bonifacio / Maddalena (no more headaches computing the local hour wondering whether to add or subtract one or two hours)
* Getting to the next or previous hour of forecast is now easy, even on a tiny screen and with your big hairy and imprecise finger :)
* The images are much more lighter
* Loading time: increased by 66% (!)

Noteworthy info: all meteorological data is **NOT** modified by Lamma2t, it is exactly the same as on the original LammaRete site. (I have no legitimacy for weather forecasts, but [I think I do for web sites](http://www.linkedin.com/in/matthieufaure) ;) )

## Usage

Simply go to http://mm3g.ovh/ and follow the **Lamma2T** link.

## Contact

matthieu2T AT stramanari.eu