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

/* The carousel */
var myCarousel = (function () {

    var carousel, slides, index, slidenav, settings, timer, setFocus, animationSuspended, announceSlide = false;
    var timeBetweenSlides = 1000;
    //var pauseChar="&#9612;&#9612;";
    var pauseChar = "&#9614;&#9614;";
    var playChar = "&#9654;";

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
                //'<button type="button" class="btn-prev"><img alt="Previous Slide" src="chevron-left-75c7dd0b.png" /></button>' +
                '<button type="button" class="btn-prev"><img alt="Previous Slide" src="data:image/png;base64,' +
                'iVBORw0KGgoAAAANSUhEUgAAABUAAAAgCAQAAABXqfsDAAAAn0lEQVR4Ac3P' +
                'Ow4BURyF8S9uR2sBWpUF6OxBMvEolCo7oFJKbEDjUdqCx2YEGxjFcIWKG/+Z' +
                'U8jwnfbXHFJzSBWYEHMmyoZz/HM3ehp8LKFmwwX+bQMVeuoqHKpwxIccy9/B' +
                '1ZchTFXouARwjNkpoBuKGDW5Bnhr426OeEcJo06OeG/jNkl4EKfjCFTcJ6XW' +
                'Cz5QBgUfqZJZgzUzKvxLdx083wfNOldhAAAAAElFTkSuQmCC"' +
                ' /></button>' +
                '</li>' +
                '<li>' +
                //'<button type="button" class="btn-next"><img alt="Next Slide" src="chevron-right-2f19bc8b.png" /></button>' +
                '<button type="button" class="btn-next"><img alt="Next Slide" src="data:image/png;base64,' +
                'iVBORw0KGgoAAAANSUhEUgAAABUAAAAgCAQAAABXqfsDAAAAm0lEQVR4Ac3K' +
                'MQ4BURRG4ZOoaBSKN7MFKxAFZiE2YA0qvU5iAxob0Clt4c0kZhmmUjyhkhuX' +
                'P5EY57QfT3WQ6rLjSs2YD/U4kh5fmPC27Z1puCapeE8yeIpToNJxjo7JKL/B' +
                'DbM2cMTiAqfwAg99fDZ4jVNBY+hShRV9FeZtwZLsN3CkQjgZGD0I0cCA20KF' +
                'ACsVAsw5sGHA/3YDkdDfDhXJuSIAAAAASUVORK5CYII=' +
                '" /></button>' +
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
                    li.innerHTML = '<button data-action="stop">' +
                            '<span class="visuallyhidden">Stop Animation </span>' +
                            pauseChar + '</button>';
                } else {
                    li.innerHTML = '<button data-action="start">' +
                            '<span class="visuallyhidden">Start Animation </span>' +
                            playChar + '</button>';
                }

                slidenav.appendChild(li);
            }

            if (settings.slidenav) {
                forEachElement(slides, function (el, i) {
                    var li = document.createElement('li');
                    var klass = (i === 0) ? 'class="current" ' : '';
                    var kurrent = (i === 0) ? ' <span class="visuallyhidden">(Current Slide)</span>' : '';

                    li.innerHTML = '<button ' + klass + 'data-slide="' + i + '"><span class="visuallyhidden">Forecast</span> ' + (i + 1) + kurrent + '</button>';
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
            timer = setTimeout(nextSlide, timeBetweenSlides);
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
                buttons[j].innerHTML = '<span class="visuallyhidden">Forecast</span> ' + (j + 1);
            }
            buttons[new_current].className = "current";
            buttons[new_current].innerHTML = '<span class="visuallyhidden">Forecast</span> ' + (new_current + 1) + ' <span class="visuallyhidden">(Current Slide)</span>';
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
            timer = setTimeout(nextSlide, timeBetweenSlides);
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
        _this.innerHTML = '<span class="visuallyhidden">Start Animation </span>' + playChar;
        _this.setAttribute('data-action', 'start');
    }

    function startAnimation() {
        settings.animate = true;
        animationSuspended = false;
        timer = setTimeout(nextSlide, timeBetweenSlides);
        _this = carousel.querySelector('[data-action]');
        _this.innerHTML = '<span class="visuallyhidden">Stop Animation </span>' + pauseChar;
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

var c1 = new myCarousel();
var c2 = new myCarousel();

c1.init({
    id: 'carousel-wind',
    slidenav: true,
    animate: false,
    startAnimated: false
});

c2.init({
    id: 'carousel-swell',
    slidenav: true,
    animate: false,
    startAnimated: false
});
