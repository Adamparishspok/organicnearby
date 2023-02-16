// Load plugins
const { src, dest, watch, parallel, series } = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const gulpautoprefixer = require('gulp-autoprefixer');
const browsersync = require('browser-sync').create();
const fileinclude = require('gulp-file-include');
const useref = require('gulp-useref');
const cached = require('gulp-cached');
const gulpIf = require('gulp-if');
const del = require('del');
const npmDist = require('gulp-npm-dist');
const postcss = require('gulp-postcss');
const cssnano = require('cssnano');
const autoprefixer = require('autoprefixer');
const replace = require('gulp-replace');
const gulpTerser = require('gulp-terser');
const sourcemaps = require('gulp-sourcemaps');
const uglify = require('gulp-uglify');

// Paths to project folders

const paths = {
  base: {
    base: './',
    node: './node_modules',
  },
  src: {
    basesrc: './src',
    basesrcfiles: './src/**/*',
    scss: './src/scss/**/*.scss',
    css: './src/css',
    js: './srcs/**/*.js',
    vendorJs: './srcs/vendors/*.js',
    html: './src/**/*.html',
    images: './src/images/**/*',
    fonts: './src/fonts/**/*',
    assets: './src/**/*',
    partials: '.src/**/*',
  },
  temp: {
    basetemp: './.temp',
  },
  dist: {
    basedist: './dist',
    js: './dists',
    vendorJs: './dists/vendors',
    images: './dist/images',
    css: './dist/css',
    fonts: './dist/fonts',
    libs: './dist/libs',
  },
};

// SCSS to CSS
function scss(callback) {
  return src(paths.src.scss)
    .pipe(sourcemaps.init())
    .pipe(sass().on('error', sass.logError))
    .pipe(gulpautoprefixer())
    .pipe(sourcemaps.write('.'))
    .pipe(dest(paths.src.css))
    .pipe(browsersync.stream());
  callback();
}

// vendor js
function vendorJs(callback) {
  return src(paths.src.vendorJs).pipe(uglify()).pipe(dest(paths.dist.vendorJs));
  callback();
}

// Image
function images(callback) {
  return src(paths.src.images).pipe(dest(paths.dist.images));
  callback();
}

// Font task
function fonts(callback) {
  return src(paths.src.fonts).pipe(dest(paths.dist.fonts));
  callback();
}

// HTML
function html(callback) {
  return src([paths.src.html, '!./src/**/*'])
    .pipe(
      fileinclude({
        prefix: '@@',
        basepath: '@file',
      }),
    )
    .pipe(replace(/src="(.{0,10})node_modules/g, 'src="$1libs'))
    .pipe(replace(/href="(.{0,10})node_modules/g, 'href="$1libs'))
    .pipe(useref())
    .pipe(cached())

    .pipe(gulpIf('*.css', postcss([autoprefixer(), cssnano()]))) // PostCSS plugins with cssnano

    .pipe(gulpIf('*.js', gulpTerser()))
    .pipe(dest(paths.dist.basedist))
    .pipe(browsersync.stream());
  callback();
}

// File include task for temp
function fileincludeTask(callback) {
  return src([paths.src.html, '!./src/**/*'])
    .pipe(
      fileinclude({
        prefix: '@@',
        basepath: '@file',
      }),
    )
    .pipe(cached())
    .pipe(dest(paths.temp.basetemp));
  callback();
}

// Copy libs file from nodemodules to dist
function copyLibs(callback) {
  return src(npmDist(), { base: paths.base.node }).pipe(dest(paths.dist.libs));
  callback();
}

// Clean .temp folder
function cleanTemp(callback) {
  del.sync(paths.temp.basetemp);
  callback();
}

// Clean Dist folder
function cleanDist(callback) {
  del.sync(paths.dist.basedist);
  callback();
}

// Browser Sync Serve
function browsersyncServe(callback) {
  browsersync.init({
    server: {
      baseDir: [paths.temp.basetemp, paths.src.basesrc, paths.base.base],
    },
  });
  callback();
}

// SyncReload
function syncReload(callback) {
  browsersync.reload();
  callback();
}

// Watch Task
function watchTask() {
  watch(paths.src.html, series(fileincludeTask, syncReload));
  watch(
    [paths.src.images, paths.src.fonts, paths.src.vendorJs],
    series(images, fonts, vendorJs),
  );
  watch([paths.src.scss], series(scss, syncReload));
}

// Default Task Preview
exports.default = series(fileincludeTask, browsersyncServe, watchTask);

// Build Task for Dist
exports.build = series(
  parallel(cleanDist),
  html,
  images,
  fonts,
  vendorJs,
  copyLibs,
  cleanTemp,
);

// export tasks
exports.scss = scss;
exports.vendorJs = vendorJs;
exports.images = images;
exports.fonts = fonts;
exports.html = html;
exports.fileincludeTask = fileincludeTask;
exports.copyLibs = copyLibs;
exports.cleanTemp = cleanTemp;
exports.cleanDist = cleanDist;
