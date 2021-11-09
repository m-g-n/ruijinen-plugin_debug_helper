var gulp = require('gulp');
var sass = require('gulp-sass');
var rename = require("gulp-rename");
var uglify = require("gulp-uglify");
var autoprefixer = require('gulp-autoprefixer');
var merge = require('merge-stream');

gulp.task('sass', function(done) {
	var common_style = gulp.src('./src/css/**/*.scss')
        .pipe(sass({outputStyle: 'compressed'}))
        .pipe(autoprefixer())
        .pipe(gulp.dest('dist/css/'));
	return merge(common_style);
});

// gulp.task('js', function(done) {
//     gulp.src(['./patterns/**/*.js', '!patterns/__patterns_examples/*.js'])
//         .pipe(uglify())
//         .pipe(rename({
//             extname: '.min.js'
//         }))
//         .pipe(gulp.dest('dist/js/patterns'));
// 	done();
// });

gulp.task('watch', () => {
	gulp.watch('./src/css/**/*.scss', gulp.task('sass'));
});