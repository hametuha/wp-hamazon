const gulp = require('gulp');
const $ = require('gulp-load-plugins')();
const mozjpeg = require('imagemin-mozjpeg');
const pngquant = require('imagemin-pngquant');
const webpack = require('webpack-stream');
const webpackBundle = require('webpack');
const named = require('vinyl-named');
const {dumpSetting} = require('@kunoichi/grab-deps');
const sass = require('gulp-sass')(require('sass'));

// Sass
gulp.task('scss', function () {
    return gulp.src('./src/scss/**/*.scss')
        .pipe($.plumber({
            errorHandler: $.notify.onError('<%= error.message %>')
        }))
        // .pipe( $.sassGlob() )
        .pipe($.sourcemaps.init())
        .pipe(sass({
            errLogToConsole: true,
            outputStyle: 'compressed',
            sourceComments: false,
            sourcemap: true,
            includePaths: ['./src/scss']
        }))
        // .pipe( $.autoprefixer() )
        .pipe($.sourcemaps.write('./map'))
        .pipe(gulp.dest('assets/css'));
});

// eslint
gulp.task('eslint', function () {
    return gulp.src([
        './src/js/**/*.js',
        './src/js/**/*.jsx'
    ])
        .pipe($.eslint({useEslintrc: true}))
        .pipe($.eslint.format());
});

// Bundle JavaScripts.
gulp.task('js:bundle', function () {
    const tmp = {};
    return gulp.src(['./src/js/**/*.jsx', './src/js/**/*.js'])
        .pipe($.plumber({
            errorHandler: $.notify.onError('<%= error.message %>')
        }))
        .pipe(named((file) => file.relative.replace(/\.[^\.]+$/, '')))
        .pipe(webpack(require('./webpack.config'), webpackBundle))
        .pipe(gulp.dest('./assets/js'));
});

// Image min
async function images_to_dist() {
    const imagemin = await import('gulp-imagemin');
    const imagePlugins = [
        pngquant({
            quality: [.65, .80],
            speed: 1,
            floyd: 0
        }),
        mozjpeg({
            quality: 85,
            progressive: true
        }),
        imagemin.svgo(),
        imagemin.optipng(),
        imagemin.gifsicle(),
    ];
    return gulp.src('./src/img/**/*')
        .pipe(imagemin.default(imagePlugins))
        .pipe(gulp.dest('./assets/img'));
}

gulp.task('imagemin', function (done) {
    return images_to_dist().then(() => done());
});

gulp.task('dump', function (done) {
    dumpSetting('assets');
    done();
});

// watch
gulp.task('watch', function () {
    // Make SASS
    gulp.watch('src/scss/**/*.scss', gulp.parallel('scss', 'stylelint'));
    // JS bundle
    gulp.watch(['src/js/**/*.jsx', 'src/js/**/*.js'], gulp.parallel('js:bundle', 'eslint'));
    // Minify Image
    gulp.watch('src/img/**/*', gulp.task('imagemin'));
    // Dump setting.
    gulp.watch(['assets/css/**/*.css', 'assets/js/**/*.js'], gulp.task('dump'));
});

// Default Tasks
gulp.task('default', gulp.task('watch'));

// Build
gulp.task('build', gulp.series(gulp.parallel('js:bundle', 'scss', 'imagemin'), 'dump'));

// Lint tasks.
gulp.task('lint', gulp.task('eslint'));
