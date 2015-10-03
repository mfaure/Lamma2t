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

    echo "<ul>";
    for ($i = $firstForecastIndexforNow; $i <= MAX_FORECAST; $i++) {
        echo "<li class=\"slide\" id=\"forecast-" . $i . "\">";
        echo "  <h2>" . $myDateFormatter->format($forecasts[$i]->validDate);
        echo "     <span class=\"tzSmall\">heure locale</span>";
        echo "  </h2>\n";
        echo "  <p>";
        echo "      <img src=\"" . $forecasts[$i]->ImageUrlOptimised . "\" ";
        echo "      alt=\"Prévisions météo Bonifacio Archipel Maddalena " . $myDateFormatter->format($forecasts[$i]->validDate) . "\"/>";
        echo "  </p>\n \n";
        echo "</li>";
    }
    echo "</ul>";
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
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" media="all" href="CSS/Styles.css">
    </head>
    <body>

        <div id="c" class="carousel">
            <?php displayForecasts(); ?>
        </div>

        <div id="about">
            <a href="about.html">À propos de Lamma2T</a>
        </div>

        <footer>
            Matthieu 2T - matthieu CHEZ stramanari.eu
        </footer>

        <script src="JS/Script.js"></script>
        <script type="text/javascript">
            var _paq = _paq || [];
            _paq.push(['trackPageView']);
            _paq.push(['enableLinkTracking']);
            (function () {
                var u = "//MyPiwikServer";
                _paq.push(['setTrackerUrl', u + 'piwik.php']);
                _paq.push(['setSiteId', MyPiwikSiteId]);
                var d = document, g = d.createElement('script'), s = d.getElementsByTagName('script')[0];
                g.type = 'text/javascript';
                g.async = true;
                g.defer = true;
                g.src = u + 'piwik.js';
                s.parentNode.insertBefore(g, s);
            })();
        </script>
        <noscript><p><img src="//MyPiwikCompleteServerSiteId" style="border:0;" alt="" /></p></noscript>
    </body>
</html>
