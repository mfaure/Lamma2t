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
        <style type="text/css">
            .tzSmall {font-size: small; font-weight:normal;}
            
            .visuallyhidden {
                border: 0px none;
                clip: rect(0px, 0px, 0px, 0px);
                height: 1px;
                margin: -1px;
                overflow: hidden;
                padding: 0px;
                position: absolute;
                width: 1px;
            }

            .carousel, .slide {
                width: 751px;
                padding:0;
                margin: 0;
                overflow: hidden;
            }
            .carousel {
                position: relative;
            }
            .carousel ul {
                margin:0;
                padding: 0;
            }
            .slide {
                /*position: absolute;*/
                height: 650px; /* = 580px (img height) + 70 ()height of the h2, manually measured) */
                background-size: cover;
                position: relative;
                margin-bottom:1em;
                border:1px solid #333;
            }
            .carousel.active {
                height: 650px; /* = 580px (img height) + 70 ()height of the h2, manually measured) */
                /* overflow:hidden; */
                position:relative;
            }

            .active .slide {
                border: none;
                display: none;
                position:absolute;
                top:0;
                left:0;
                z-index:200;
            }

            .slide.current {
                display:block;
                z-index: 500;
            }
            .btn-prev,
            .btn-next {
                position:absolute;
                z-index: 700;
                top: 50%;
                margin-top: -2.5em;
                border:0;
                background: rgba(255,255,255,.6);
                line-height: 1;
                padding:2em .5em;
                transition: padding .4s ease-out;
            }

            .btn-next:hover,
            .btn-next:focus,
            .btn-prev:hover,
            .btn-prev:focus {
                padding-left: 2em;
                padding-right:2em;
            }

            .btn-prev {
                left:0;
                border-radius: 0 .25em .25em 0;
            }

            .btn-next {
                right:0;
                border-radius: .25em 0 0 .25em;
            }

            .carousel.with-slidenav {
                padding-bottom: 7em;
                background-color: #fff;
            }
            .slidenav {
                position: absolute;
                bottom:1em;
                left: 0;
                right: 0;
                text-align: center;
            }

            .slidenav li {
                display:inline-block;
                margin: 0 .5em;
            }

            .slidenav button {
                border: 2px solid #036;
                background-color: #036;
                line-height: 1em;
                height: 2em;
                width:2em;
                font-weight: bold;
                color: #fff;
            }

            .slidenav button.current {
                border-radius: .5em;
                background-color: #fff;
                color: #333;
            }

            .slidenav button:hover,
            .slidenav button:focus {
                border: 2px dashed #fff;
            }

            .slidenav button.current:hover,
            .slidenav button.current:focus {
                border: 2px dashed #036;
            }
        </style>
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
        
        <script>
            /* focusin/out event polyfill (firefox) */
            !function () {
                var w = window,
                        d = w.document;

                if (w.onfocusin === undefined) {
                    d.addEventListener('focus', addPolyfill, true);
                    d.addEventListener('blur', addPolyfill, true);
                    d.addEventListener('focusin', removePolyfill, true);
                    d.addEventListener('focusout', removePolyfill, true);
                }
                function addPolyfill(e) {
                    var type = e.type === 'focus' ? 'focusin' : 'focusout';
                    var event = new CustomEvent(type, {bubbles: true, cancelable: false});
                    event.c1Generated = true;
                    e.target.dispatchEvent(event);
                }
                function removePolyfill(e) {
                    if (!e.c1Generated) { // focus after focusin, so chrome will the first time trigger tow times focusin
                        d.removeEventListener('focus', addPolyfill, true);
                        d.removeEventListener('blur', addPolyfill, true);
                        d.removeEventListener('focusin', removePolyfill, true);
                        d.removeEventListener('focusout', removePolyfill, true);
                    }
                    setTimeout(function () {
                        d.removeEventListener('focusin', removePolyfill, true);
                        d.removeEventListener('focusout', removePolyfill, true);
                    });
                }
            }();

            var myCarousel = (function () {

                var carousel, slides, index, slidenav, settings, timer, setFocus, animationSuspended, announceSlide = false;

                function forEachElement(elements, fn) {
                    for (var i = 0; i < elements.length; i++)
                        fn(elements[i], i);
                }

                function removeClass(el, className) {
                    if (el.classList) {
                        el.classList.remove(className);
                    } else {
                        el.className = el.className.replace(new RegExp('(^|\\b)' + className.split(' ').join('|') + '(\\b|$)', 'gi'), ' ');
                    }
                }

                function hasClass(el, className) {
                    if (el.classList) {
                        return el.classList.contains(className);
                    } else {
                        return new RegExp('(^| )' + className + '( |$)', 'gi').test(el.className);
                    }
                }

                function init(set) {
                    settings = set;
                    carousel = document.getElementById(settings.id);
                    slides = carousel.querySelectorAll('.slide');

                    carousel.className = 'active carousel';

                    var ctrls = document.createElement('ul');

                    ctrls.className = 'controls';
                    ctrls.innerHTML = '<li>' +
                            '<button type="button" class="btn-prev"><img alt="Previous Slide" src="chevron-left-75c7dd0b.png" /></button>' +
                            '</li>' +
                            '<li>' +
                            '<button type="button" class="btn-next"><img alt="Next Slide" src="chevron-right-2f19bc8b.png" />' +
                            '</li>';

                    ctrls.querySelector('.btn-prev')
                            .addEventListener('click', function () {
                                announceSlide = true;
                                prevSlide();
                            });
                    ctrls.querySelector('.btn-next')
                            .addEventListener('click', function () {
                                announceSlide = true;
                                nextSlide();
                            });

                    carousel.appendChild(ctrls);

                    if (settings.slidenav || settings.animate) {
                        slidenav = document.createElement('ul');

                        slidenav.className = 'slidenav';

                        if (settings.animate) {
                            var li = document.createElement('li');

                            if (settings.startAnimated) {
                                li.innerHTML = '<button data-action="stop"><span class="visuallyhidden">Stop Animation </span>￭</button>';
                            } else {
                                li.innerHTML = '<button data-action="start"><span class="visuallyhidden">Start Animation </span>▶</button>';
                            }

                            slidenav.appendChild(li);
                        }

                        if (settings.slidenav) {
                            forEachElement(slides, function (el, i) {
                                var li = document.createElement('li');
                                var klass = (i === 0) ? 'class="current" ' : '';
                                var kurrent = (i === 0) ? ' <span class="visuallyhidden">(Current Slide)</span>' : '';

                                li.innerHTML = '<button ' + klass + 'data-slide="' + i + '"><span class="visuallyhidden">News</span> ' + (i + 1) + kurrent + '</button>';
                                slidenav.appendChild(li);
                            });
                        }

                        slidenav.addEventListener('click', function (event) {
                            var button = event.target;
                            if (button.localName == 'button') {
                                if (button.getAttribute('data-slide')) {
                                    stopAnimation();
                                    setSlides(button.getAttribute('data-slide'), true);
                                } else if (button.getAttribute('data-action') == "stop") {
                                    stopAnimation();
                                } else if (button.getAttribute('data-action') == "start") {
                                    startAnimation();
                                }
                            }
                        }, true);

                        carousel.className = 'active carousel with-slidenav';
                        carousel.appendChild(slidenav);
                    }

                    slides[0].parentNode.addEventListener('transitionend', function (event) {
                        var slide = event.target;
                        removeClass(slide, 'in-transition');
                        if (hasClass(slide, 'current')) {
                            slide.removeAttribute('aria-live');
                            announceSlide = false;
                            if (setFocus) {
                                slide.setAttribute('tabindex', '-1');
                                slide.focus();
                                setFocus = false;
                            }
                        }
                    });

                    carousel.addEventListener('mouseenter', suspendAnimation);
                    carousel.addEventListener('mouseleave', function (event) {
                        if (animationSuspended) {
                            startAnimation();
                        }
                    });

                    carousel.addEventListener('focusin', function (event) {
                        if (!hasClass(event.target, 'slide')) {
                            suspendAnimation();
                        }
                    });
                    carousel.addEventListener('focusout', function (event) {
                        if (!hasClass(event.target, 'slide') && animationSuspended) {
                            startAnimation();
                        }
                    });

                    index = 0;
                    setSlides(index);

                    if (settings.startAnimated) {
                        timer = setTimeout(nextSlide, 5000);
                    }
                }

                function setSlides(new_current, setFocusHere, transition) {
                    setFocus = typeof setFocusHere !== 'undefined' ? setFocusHere : false;
                    transition = typeof transition !== 'undefined' ? transition : 'none';

                    new_current = parseFloat(new_current);

                    var length = slides.length;
                    var new_next = new_current + 1;
                    var new_prev = new_current - 1;

                    if (new_next === length) {
                        new_next = 0;
                    } else if (new_prev < 0) {
                        new_prev = length - 1;
                    }

                    for (var i = slides.length - 1; i >= 0; i--) {
                        slides[i].className = "slide";
                    }

                    slides[new_next].className = 'next slide' + ((transition == 'next') ? ' in-transition' : '');
                    slides[new_next].setAttribute('aria-hidden', 'true');
                    slides[new_prev].className = 'prev slide' + ((transition == 'prev') ? ' in-transition' : '');
                    slides[new_prev].setAttribute('aria-hidden', 'true');

                    slides[new_current].className = 'current slide';
                    slides[new_current].removeAttribute('aria-hidden');
                    if (announceSlide) {
                        slides[new_current].setAttribute('aria-live', 'polite');
                    }

                    if (settings.slidenav) {
                        var buttons = carousel.querySelectorAll('.slidenav button[data-slide]');
                        for (var j = buttons.length - 1; j >= 0; j--) {
                            buttons[j].className = '';
                            buttons[j].innerHTML = '<span class="visuallyhidden">News</span> ' + (j + 1);
                        }
                        buttons[new_current].className = "current";
                        buttons[new_current].innerHTML = '<span class="visuallyhidden">News</span> ' + (new_current + 1) + ' <span class="visuallyhidden">(Current Slide)</span>';
                    }

                    index = new_current;

                }

                function nextSlide() {

                    var length = slides.length,
                            new_current = index + 1;

                    if (new_current === length) {
                        new_current = 0;
                    }

                    setSlides(new_current, false, 'prev');

                    if (settings.animate) {
                        timer = setTimeout(nextSlide, 5000);
                    }

                }

                function prevSlide() {
                    var length = slides.length,
                            new_current = index - 1;

                    if (new_current < 0) {
                        new_current = length - 1;
                    }

                    setSlides(new_current, false, 'next');

                }

                function stopAnimation() {
                    clearTimeout(timer);
                    settings.animate = false;
                    animationSuspended = false;
                    _this = carousel.querySelector('[data-action]');
                    _this.innerHTML = '<span class="visuallyhidden">Start Animation </span>▶';
                    _this.setAttribute('data-action', 'start');
                }

                function startAnimation() {
                    settings.animate = true;
                    animationSuspended = false;
                    timer = setTimeout(nextSlide, 5000);
                    _this = carousel.querySelector('[data-action]');
                    _this.innerHTML = '<span class="visuallyhidden">Stop Animation </span>￭';
                    _this.setAttribute('data-action', 'stop');
                }

                function suspendAnimation() {
                    if (settings.animate) {
                        clearTimeout(timer);
                        settings.animate = false;
                        animationSuspended = true;
                    }
                }

                return {
                    init: init,
                    next: nextSlide,
                    prev: prevSlide,
                    goto: setSlides,
                    stop: stopAnimation,
                    start: startAnimation
                };
            });

            var c = new myCarousel();
            c.init({
                id: 'c',
                slidenav: true,
                animate: false,
                startAnimated: false
            });
        </script>
    </body>
</html>
