const $ = require('gulp-load-plugins')();
const babelify = require('babelify');
const browserify = require('browserify');
const buffer = require('vinyl-buffer');
const gulp = require('gulp');
const path = require('path');
const source = require('vinyl-source-stream');
const sass = require('gulp-sass')(require('sass'));
// const argv       = require('yargs').argv;
// const fs         = require('fs');

// Different Env settings from IDE
// - Check for --production flag
// const isProduction = !!(argv.production);

// Browsers to target when prefixing CSS.
const autoprefixerCompatibility = [
  'last 2 versions',
  'ie >= 9',
  'safari >= 8'
];

// Livereload over SSL
const livereloadSettings = {};


function css(file) {
  const dir = path.resolve(`${__dirname}/../css`);

  return gulp.src(file)
    // Enable sourcemaps
    .pipe($.sourcemaps.init({loadMaps: true}))

    // Handle errors
    .pipe(sass().on('error', sass.logError))

    // Run autoprefixer
    .pipe($.autoprefixer({
      Browserslist: autoprefixerCompatibility,
    }))

    // Output CSS file
    .pipe(gulp.dest('../css'))

    // Minify with maps and output
    .pipe($.sourcemaps.init())
    .pipe($.cleanCss())
    .pipe($.rename({suffix: '.min'}))
    .pipe($.sourcemaps.write('.'))
    .pipe(gulp.dest('../css'))

    // Run livereload
    .pipe($.filter([
      path.join(dir, '/**/*.css'),
      '../**/*.css',
    ]))
    .pipe($.livereload());
}

function js(filename, inputfile, outputLocation) {
  const dir = path.resolve(`${__dirname}/../js`);
  const bundler = browserify({
    entries: inputfile,
    debug: true
  });

  bundler.transform(babelify, {
    presets: [
      '@babel/preset-env'
    ],
    sourceMaps: true
  });

  return bundler.bundle()
    .pipe($.plumber({
      errorHandler: (error) => {
        console.log(error.message);
        this.emit('end');
      }
    }))
    .pipe(source(`${filename}.es6`))
    .pipe(buffer())
    .pipe($.sourcemaps.init({loadMaps: true}))
    .pipe($.concat(`${filename}.js`))
    .pipe(gulp.dest(outputLocation))

    // Minify & create sourcemap
    // .pipe($.rename({suffix: '.min'}))
    // .pipe($.uglify())
    // .pipe($.sourcemaps.write('.'))
    // .pipe(gulp.dest('../js'))

    .pipe($.filter([
      path.join(dir, '/**/*.js'),
      '../**/*.js'
    ]))
    // Run livereload
    .pipe($.livereload());
}

function themejs(filename) {
  return js(filename, `es6/${filename}.es6`, '../js');
}

// Build the theme JS file
gulp.task('build-js', gulp.series((done) => {
  // Add your js files here (we're doing them separately so they can be loaded
  // via libraries.yml)
  themejs('test');

  done();
}));

// Build the theme CSS file
gulp.task('build-css', gulp.series(() => css('scss/style.scss')));

// Build the TinyMCE CSS file
gulp.task('build-tinymce-css', gulp.series(() => css('scss/tinymce-style.scss')));

// Build SVG sprite
gulp.task('build-svg-sprite', gulp.series(() => {
  const config = {
    svg: {
      xmlDeclaration: false,
      namespaceClassnames: false,
    },
    mode: {
      // Activate the «symbol» mode
      symbol: {
        bust: false,
      }
    }
  };

  return gulp.src('sprite-images/**/*.svg')
    .pipe($.plumber({
      errorHandler(error) {
        console.log(error.message);
        this.emit('end');
      }
    }))
    .pipe($.svgSprite(config))
    .pipe(gulp.dest('../img'))
    .pipe($.livereload());
}));

gulp.task('build-all', gulp.series([
  'build-css',
  'build-tinymce-css',
]));

gulp.task('build', gulp.series([
  'build-css',
]));

// Reload the browser!
gulp.task('reload', gulp.series(() => {
  $.livereload.reload();
}));

// Watcher
gulp.task('watch', gulp.series([
  'build-css',
], () => {
  // Start livereload
  $.livereload.listen(livereloadSettings);

  // Watch .scss files
  gulp.watch('scss/**/*.scss')
    .on('change', gulp.series('build-css'));

  // Watch .js files
  gulp.watch(['es6/*.es6', 'es6/**/*.js'])
    .on('change', gulp.series('build-js'));

  // Watch .twig files
  gulp.watch('../../**/*.twig')
    .on('change', gulp.series('reload'));

  // Watch .php files
  gulp.watch([
    '../../../**/*.php',
    '!../node_modules/',
    '!../node_modules/**/*',
    '!node_modules/',
    '!node_modules/**/*',
  ])
    .on('change', gulp.series('reload'));
}));
