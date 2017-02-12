let gulp = require('gulp');
let sass = require('gulp-sass');
let autoprefixer = require('gulp-autoprefixer');
let uglify = require('gulp-uglify');
let concat = require('gulp-concat');

gulp.task('default', ['build', 'prepare']);

gulp.task('build', ['build:library', 'build:css']);

gulp.task('build:library', function() {
	return gulp.src([
		'bower_components/fetch/fetch.js',
		'bower_components/es6-promise/es6-promise.auto.min.js',
		'bower_components/html5-polyfills/dataset.js',
		'bower_components/html5-polyfills/classList.js',
		'bower_components/vue/dist/vue.min.js',
		'assets/library/**/*.js'
	])
		.pipe(concat('library.js'))
		.pipe(uglify())
		.pipe(gulp.dest('assets/static'));
});

gulp.task('build:css', function() {
	return gulp.src('assets/sass/**/*.sass')
		.pipe(sass({
			indentedSyntax: true,
			includePaths: ['assets/sass']
		}).on('error', sass.logError))
		.pipe(autoprefixer({
			browsers: ['last 10 versions']
		}))
		.pipe(gulp.dest('assets/css'));
});

gulp.task('prepare', []);

gulp.task('watch', ['build', 'watch:library', 'watch:css']);

gulp.task('watch:library', function() {
	return gulp.watch('assets/library/**/*.js', ['build:library']);
});

gulp.task('watch:css', function() {
	return gulp.watch('assets/sass/**/*.sass', ['build:css']);
});
