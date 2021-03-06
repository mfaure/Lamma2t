// include gulp
var gulp = require('gulp');

// include plug-ins
var jshint = require('gulp-jshint');
var changed = require('gulp-changed');
var imagemin = require('gulp-imagemin');
var minifyHTML = require('gulp-minify-html');
var concat = require('gulp-concat');
var stripDebug = require('gulp-strip-debug');
var uglify = require('gulp-uglify');
var autoprefix = require('gulp-autoprefixer');
var minifyCSS = require('gulp-minify-css');

// JS hint task
gulp.task('jshint', function () {
    gulp.src('./src/JS/*.js')
        .pipe(jshint())
        .pipe(jshint.reporter('default'));
});

// minify new images
gulp.task('imagemin', function () {
    var imgSrc = './src/Images/**/*',
        imgDst = './build/Images';

    gulp.src(imgSrc)
        .pipe(changed(imgDst))
        .pipe(imagemin())
        .pipe(gulp.dest(imgDst));
});

// minify new or changed HTML pages
gulp.task('htmlpage', function () {
    var htmlSrc = './src/*.html',
        htmlDst = './build';

    gulp.src(htmlSrc)
        .pipe(changed(htmlDst))
        .pipe(minifyHTML())
        .pipe(gulp.dest(htmlDst));
});

// JS concat, strip debugging and minify
gulp.task('scripts', function () {
    gulp.src(['./src/JS/lib.js', './src/JS/*.js'])
        .pipe(concat('Script.js'))
        .pipe(stripDebug())
        .pipe(uglify())
        .pipe(gulp.dest('./build/JS/'));
});

// CSS concat, auto-prefix and minify
gulp.task('styles', function () {
    gulp.src(['./src/CSS/*.css'])
        .pipe(concat('Styles.css'))
        .pipe(autoprefix('last 2 versions'))
        .pipe(minifyCSS())
        .pipe(gulp.dest('./build/CSS/'));
});

// Copy PHP files
gulp.task('phpcopy', function () {
    gulp.src(['./src/index.php'])
        .pipe(gulp.dest('./build/'));
});

// default gulp task
//gulp.task('default', ['imagemin', 'htmlpage', 'scripts', 'styles'], function() {
gulp.task('default', ['htmlpage', 'scripts', 'styles', 'phpcopy'], function () {
    // watch for HTML changes
    gulp.watch('./src/*.html', function () {
        gulp.run('htmlpage');
    });

    // watch for JS changes
    gulp.watch('./src/JS/*.js', function () {
        gulp.run('jshint', 'scripts');
    });

    // watch for CSS changes
    gulp.watch('./src/CSS/*.css', function () {
        gulp.run('styles');
    });

    // watch for PHP changes
    gulp.watch('./src/index.php', function () {
        gulp.run('phpcopy');
    });
});
