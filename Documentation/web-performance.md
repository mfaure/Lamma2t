# Web performance

Gains (lower is better):

* Number of requests: -30% (from 68 to 47)
* Loading time: -66% (from 3.5s to 1.2s)

## Methodology

Use Firefox, Press CTRL-SHIT-I, use Network panel and hard-reload page (CTRL-SHIT-R).

Make 10 measures, remove best and worst values, and compute average of the 8 remaining values.

## Values on 2015-10-03

Page [Lamma Rete (alternate version)](http://www.lamma.rete.toscana.it/meteo/modelli/ventomare) with only first forecast

* Requests : 68
* Total weight (KB) : 843
* Load time (s) : 3.51

Page [Lamma2T](http://mm3g.ovh/lamma2t/) (containing now 44 forecasts)

* Requests : 47
* Total weight (KB) : 1157
* Load time (s) :  1.2

## Values on 2015-08-05

Page [Lamma Rete (alternate version)](http://www.lamma.rete.toscana.it/meteo/modelli/ventomare) with only first forecast

* Requests : 68
* Total weight (KB) : 813.73
* Load time (s) : 4.79

Page [Lamma2T](http://mm3g.ovh/lamma2t/) (containing 37 forecasts)

* Requests : 32
* Total weight (KB) : 817,43
* Load time (s) : 1.03