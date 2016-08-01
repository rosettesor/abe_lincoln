var fs = require('fs');
var gulp = require('gulp');
var uglify = require('gulp-uglify');
var stylish = require('jshint-stylish');
var checkstyle = require('gulp-jshint-checkstyle-reporter');

var jshint = require('gulp-jshint');

gulp.task('lintXML', function () {
  gulp.src('public/js/*.js')
    .pipe(jshint())
    .pipe(checkstyle())
    .pipe(gulp.dest('checkstyle'))
});

gulp.task('lint', function () {
  gulp.src('public/js/*.js')
    .pipe(jshint())
    .pipe(jshint.reporter(stylish));
});

gulp.task('compress', function () {
  gulp.src('public/js/*.js')
    .pipe(uglify())
    .pipe(gulp.dest('public/dist/js'));
});
