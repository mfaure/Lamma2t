<?php

use DateTime;
use DateTimeZone;

const MAX_FORECAST = "57"; //count begins from 1 (not 0) to MAX_FORECAST

$tzUTC = new DateTimeZone('UTC');
$tzParis = new DateTimeZone('Europe/Paris');

$myTimeStamp = new DateTime("now", $tzParis);
$myTimeStamp->setTimezone($tzUTC);

class Forecast {

    // Images directly from LammaRete (without crop nor optimisation)
    const WIND_URL_ORIG_STUB = "http://www.lamma.rete.toscana.it/models/ventoemare/wind10m_N_web_";
    const WIND_URL_ORIG_EXT = ".png";
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

    public $modelInitDate = "NULL";

    function __construct(int $i) {
        // Construct the URL of the image of the forecast
        $this->ImageUrlOrig = self::WIND_URL_ORIG_STUB . $i . self::WIND_URL_ORIG_EXT;
        $this->ImageUrlOptimised = self::WIND_URL_OPTIMISED_STUB . $i . self::WIND_URL_OPTIMISED_EXT;

        /* @var $modelInitDate DateTime */
        $modelInitDate = computeModelInitDate();

        // Construct the timestamp of the forecast ("validDate")
        $this->validDate = computeModelValidDate($i, $modelInitDate);
    }

    /*
     * Compute the timestamp of the run of LammaRete ("initDate") based on
     * the current hour
     */

    function computeModelInitDate() {
        global $tzUTC;
        global $tzParis;
        global $myTimeStamp;
        $myModelInitDate = "NULL";

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

    /*
     * Compute the timestamp of the current forecast ("validDate") based on :
     * - the timestamp of the run of the model ("initDate")
     * - the number of the forecast (describing the time shift from the init)
     */

    function computeModelValidDate(int $i, DateTime $modelInitDate) {
        $timeShiftInHours = NULL;

        if ($i <= self::STEPS_OF_1HOUR_FORECASTS) {
            // i <= 37 => shift = i hours
            $timeShiftInHours = $i;
        } elseif ($i <= self::STEPS_OF_3HOURS_FORECASTS) {
            // 37 < i <= 49 => shift = 37 hours + (i-37) * 3 hours
            $timeShiftInHours = self::STEPS_OF_1HOUR_FORECASTS +
                    ($i - self::STEPS_OF_1HOUR_FORECASTS) * 3;
        } else {
            //  49 < i => shift = 37 hours + 12 * 3 hours + (i-49)*6 hours
            $timeShiftInHours = self::STEPS_OF_1HOUR_FORECASTS +
                    (self::STEPS_OF_3HOURS_FORECASTS - self::STEPS_OF_1HOUR_FORECASTS) * 3 +
                    ($i - self::STEPS_OF_3HOURS_FORECASTS) * 6;
        }

        $myTimeShift = DateInterval::createFromDateString($timeShiftInHours . ' hours');
        return $modelInitDate->add($myTimeShift);
    }

}

/*
 * Construct the array of all 57 forecasts
 */

function initLamma2T() {
    $forecasts = array();
    for ($i = 1; $i++; $i <= MAX_FORECAST) {
        $forecasts[$i] = new Forecast($i);
    }
}

function displayForecasts() {
    global $myTimeStamp;
    global $forecasts;

    $myDateFormatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::LONG, IntlDateFormatter::NONE, 'Europe/Paris', IntlDateFormatter::GREGORIAN, "EEEE dd/MM/yy HH'h'");
    $myDateFormatterTz = new IntlDateFormatter('fr_FR', IntlDateFormatter::LONG, IntlDateFormatter::NONE, 'Europe/Paris', IntlDateFormatter::GREGORIAN, "VVVV");

    // We don't want to display forecasts that are in the past, so determine by
    // which one to begin
    /* @var $myInterval DateTime */
    $myInterval = $myTimeStamp->diff($forecasts->modelInitDate);
    $firstForecastIndexforNow = $myInterval->format('%h');

    for ($i = $firstForecastIndexforNow; $i++; $i <= MAX_FORECAST) {
        echo "<div class=\"forecast-unit\" id=\"forecast-" . $i . "\">";
        echo "  <h2>" . $myDateFormatter->format($forecasts[$i]->validDate);
        echo " <span class=\"tzSmall\">" . $myDateFormatterTz->format($forecasts[$i]->validDate) . "</span>";
        echo "</h2>\n";
        echo "  <p>";
        echo "      <img src=\"" . $forecasts[$i]->ImageUrlOptimised. "\" ";
        echo "      alt=\"Prévisions météo Bonifacio Archipel Maddalena " . $myDateFormatter->format($forecasts[$i]->validDate) . "\"/>";
        echo "  </p>\n \n";
        echo "</div>";
    }
}
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
            var interval = undefined;
            $(document).ready(function () {
                //interval = setInterval(getNext, 2000); // milliseconds
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
                //clearInterval(interval);

                $next.css('z-index', 2).fadeIn('fast', function () {
                    $curr.hide().css('z-index', 0);
                    $next.css('z-index', 1);
                });
            }
        </script>

        <h1>Lamma2T : Prévisions météo Bouches de Bonif / Maddalena</h1>

        <button id="prev">&lt; Précédent</button>
        <button id="next">Suivant &gt;</button>

        <div class="slideshow">
            <?php displayForecasts(); ?>
        </div>

        <h2 id="metaInfo">Informations</h2>
        <h3>Pourquoi cette page ?</h3>
        <p>J'ai réalisé cette page pour mes besoins propres de navigation dans les bouches de Bonficio et l'Archipel de la Maddalena, typiquement lors d'encadrement de stages à l'école de voile Les Glénans.</p>
        <p>Le site <a href="http://www.lamma.rete.toscana.it/meteo/modelli/ventomare" lang="it">Consorzio LaMMA Rete</a> est particulièrement utile car il offre une représentation visuelle du champs de vent et ceci heure par heure. Par contre l'interface utilisateur du site est assez peu pratique, surtout sur téléphone mobile et en réseau 3G.</p>
        <p>C'est pour répondre à ce besoin que j'ai créé cette page. Si je résume mon cahier des charges, ça donne ceci : </p>
        <ul>
            <li>Avoir en une seule page, les prévisions de vent toutes les 2 heures pour les 36 heures à venir.</li>
            <li>Avoir l'heure légale de chaque prévision (sans devoir faire de calcul de UTC/heure légale, et ce été comme hiver).</li>
            <li>Optimiser la page pour la consultation en 3G (application des techniques "webperf", i.e. minification du code, optimisation des images, réduction du nombre de requêtes DNS)</li>
        </ul>
        <h3>Mises à jour</h3>
        <ul>
            <li>7h30 <abbr title="Temps Universel Coordonné">UTC</abbr> (9h30 en été, 8h30 en hiver) s'appuyant sur les données de 0h UTC</li>
            <li>21h30 UTC (23h30 en été, 22h30 en hiver) s'appuyant sur les données de 12h UTC</li>
        </ul>
        <p>Détail de <a href="http://www.lamma.rete.toscana.it/mare/modelli/ww3-info-sul-modello">mises à jour du modèle WW3 LaMMA RETE (en italien)</a></p>
        <h3>Contact</h3>
        <p>matthieu CHEZ stramanari.eu</p>
        <footer>
            Matthieu 2T - matthieu CHEZ stramanari.eu - août 2014
        </footer>
    </body>
</html>
