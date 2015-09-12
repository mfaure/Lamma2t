<?php

const MAX_FORECAST = "57"; //count begins from 1 (not 0) to MAX_FORECAST

$tzUTC = new DateTimeZone('UTC');
$tzParis = new DateTimeZone('Europe/Paris');

$myTimeStamp = new DateTime("now", $tzParis);
$myTimeStamp->setTimezone($tzUTC);

$modelInitDate = computeModelInitDate();
$forecasts = array();

class Forecast {

    // Images optimised, with relative (local) URL
    const WIND_URL_OPTIMISED_STUB = "images_2optimised/wind10m_N_web_";
    const WIND_URL_OPTIMISED_EXT = ".optimised.png";

    /*
     * Forecasts are given by LammaRete :
     * - from 1 to 37  : step between 2 forecasts = 1 hour
     * - from 38 to 49 : step between 2 forecasts = 3 hours
     * - from 50 to 57 : step between 2 forecasts = 6 hours
     */
    const STEPS_OF_1HOUR_FORECASTS = 37;
    const STEPS_OF_3HOURS_FORECASTS = 49;

    /*
     * Compute the timestamp of the current forecast ("validDate") based on :
     * - the timestamp of the run of the model ("initDate")
     * - the number of the forecast (describing the time shift from the init)
     */

    function computeModelValidDate($index) {
        global $modelInitDate;
        $timeShiftInHours = NULL;
        $modelValidDate = clone $modelInitDate;

        if ($index <= self::STEPS_OF_1HOUR_FORECASTS) {
            // index <= 37 => shift = index hours - 1
            $timeShiftInHours = $index - 1;
        } elseif ($index <= self::STEPS_OF_3HOURS_FORECASTS) {
            // 37 < index <= 49 => shift = 37 hours + (index-37) * 3 hours - 1
            $timeShiftInHours = self::STEPS_OF_1HOUR_FORECASTS +
                    ($index - self::STEPS_OF_1HOUR_FORECASTS) * 3 - 1;
        } else {
            //  49 < index => shift = 37 hours + 12 * 3 hours + (index-49)*6 hours - 1
            $timeShiftInHours = self::STEPS_OF_1HOUR_FORECASTS +
                    (self::STEPS_OF_3HOURS_FORECASTS - self::STEPS_OF_1HOUR_FORECASTS) * 3 +
                    ($index - self::STEPS_OF_3HOURS_FORECASTS) * 6 - 1;
        }

        $myTimeShift = DateInterval::createFromDateString($timeShiftInHours . ' hours');
        return $modelValidDate->add($myTimeShift);
    }

    function __construct($i) {
        // Construct the URL of the image of the forecast
        $this->ImageUrlOptimised = self::WIND_URL_OPTIMISED_STUB . $i . self::WIND_URL_OPTIMISED_EXT;

        // Construct the timestamp of the forecast ("validDate")
        $this->validDate = $this->computeModelValidDate($i);
    }
}

/*
 * Compute the timestamp of the run of LammaRete ("initDate") based on
 * the current hour
 */

function computeModelInitDate() {
    $myModelInitDate = "NULL";
    global $tzUTC;
    global $tzParis;
    global $myTimeStamp;

    // UTC hours of model updates:
    // * http://www.lamma.rete.toscana.it/meteo/modelli/wrf-info-sul-modello
    // * http://www.lamma.rete.toscana.it/mare/modelli/ww3-info-sul-modello

    $ModelRun01Morning = new DateTime("now", $tzParis);
    $ModelRun01Morning->setTimezone($tzUTC);
    $ModelRun01Morning = $ModelRun01Morning->setTime(7, 30);

    $ModelRun02Evening = new DateTime("now", $tzParis);
    $ModelRun02Evening->setTimezone($tzUTC);
    $ModelRun02Evening = $ModelRun02Evening->setTime(21, 30);

    if ($myTimeStamp <= $ModelRun01Morning) {
        // before 7h30 UTC: init = day - 1 at 12h UTC
        $myModelInitDate = new DateTime("now", $tzUTC);
        $myModelInitDate = $myModelInitDate->sub(new DateInterval('P1D')); // P1D = Period 1 day cf http://php.net/manual/fr/dateinterval.construct.php
        $myModelInitDate = $myModelInitDate->setTime(12, 00);
    } elseif ($myTimeStamp >= $ModelRun02Evening) {
        //  after 21h30 UTC: init = day at 12h UTC
        $myModelInitDate = new DateTime("now", $tzUTC);
        $myModelInitDate = $myModelInitDate->setTime(12, 00);
    } else {
        // between 7h30 and 21h30 UTC: init = day at 00h UTC
        $myModelInitDate = new DateTime("now", $tzUTC);
        $myModelInitDate = $myModelInitDate->setTime(00, 00);
    }
    return $myModelInitDate;
}

function displayForecasts() {
    global $myTimeStamp;
    global $forecasts;
    global $modelInitDate;

    $myDateFormatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::LONG, IntlDateFormatter::NONE, 'Europe/Paris', IntlDateFormatter::GREGORIAN, "EEEE dd/MM/yy HH'h'");

    // We don't want to display forecasts that are in the past, so determine by
    // which one to begin
    $myInterval = $myTimeStamp->diff($modelInitDate);
    $firstForecastIndexforNow = 1 + $myInterval->format('%h');

    for ($i = $firstForecastIndexforNow; $i <= MAX_FORECAST; $i++) {
        echo "<div class=\"forecast-unit\" id=\"forecast-" . $i . "\">";
        echo "  <h2>" . $myDateFormatter->format($forecasts[$i]->validDate);
        echo " <span class=\"tzSmall\">heure locale</span>";
        echo "</h2>\n";
        echo "  <p>";
        echo "      <img src=\"" . $forecasts[$i]->ImageUrlOptimised . "\" ";
        echo "      alt=\"Prévisions météo Bonifacio Archipel Maddalena " . $myDateFormatter->format($forecasts[$i]->validDate) . "\"/>";
        echo "  </p>\n \n";
        echo "</div>";
    }
}

/*
 * Construct the array of all 57 forecasts
 */

function initLamma2T() {
    global $forecasts;
    
    for ($i = 1; $i <= MAX_FORECAST; $i++) {
        $forecasts[$i] = new Forecast($i);
    }
}

# initialisation
initLamma2T();
?>

<!DOCTYPE html> 
<html lang="fr">
    <head>
        <title>Lamma2T : Prévisions Météo Bouches de Bonifacio / Maddalena de LammaRete adapté par Matthieu 2T</title>
        <meta charset="UTF-8" >
        <style type="text/css">
            .tzSmall {font-size: small; font-weight:normal;}

            .slideshow {
                position: relative;
                /* necessary to absolutely position the images inside */
                width: 760px;
                /* same as the images inside */
                height: 670px;
            }
            .slideshow .forecast-unit {
                position: absolute;
                display: none;
            }
            .slideshow .forecast-unit:first-child {
                display: block;
                /* overrides the previous style */
            }
        </style>
    </head>
    <body>

        <script src="jquery-1.11.3.min.js"></script>
        <script>
            $(document).ready(function () {
                $('#next').on('click', getNext);
                $('#prev').on('click', getPrev);
            });

            function getNext() {
                var $curr = $('.slideshow .forecast-unit:visible'),
                        $next = ($curr.next().length) ? $curr.next() : $('.slideshow .forecast-unit').first();

                transition($curr, $next);
            }

            function getPrev() {
                var $curr = $('.slideshow .forecast-unit:visible'),
                        $next = ($curr.prev().length) ? $curr.prev() : $('.slideshow .forecast-unit').last();
                transition($curr, $next);
            }

            function transition($curr, $next) {
                $next.css('z-index', 2).fadeIn('fast', function () {
                    $curr.hide().css('z-index', 0);
                    $next.css('z-index', 1);
                });
            }
        </script>

        <button id="prev">&lt; Précédent</button>
        <button id="next">Suivant &gt;</button>

        <div class="slideshow">
            <?php displayForecasts(); ?>
        </div>

        <div id="about">
            <a href="about.html">À propos de Lamma2T</a>
        </div>
        
        <footer>
            Matthieu 2T - matthieu CHEZ stramanari.eu
        </footer>
    </body>
</html>
