/**
 * Configuration des tâches du projet avec Gulp
 */

// Chargement des fonctions Gulp utilisées
const { series, src, dest, watch } = require('gulp');
const del = require('del');

// Chargement des plugins Gulp utilisés
const sass   = require('gulp-sass')(require('sass'));
const concat = require('gulp-concat');
const uglify = require('gulp-uglify');
const rename = require('gulp-rename');


/**
 * Définitions des taches
 */

 function cleanOutput() {
	return del([
			'./js/**',
			'./css/**',
			//'./fonts/**',
		], {force:true}
	);
}


function buildJs() {

    return src([
			// Bootstrap soit intégralement soit sélectivement...
			'node_modules/bootstrap/js/dist/*.js',
		])
		.pipe(concat('scripts.js'))
		.pipe(uglify()).pipe(rename({ extname: '.min.js' }))
		.pipe(dest('./js/'))
	;
 
}


function buildCss() {
	return src([
			'./scss/*.scss',
		])
		.pipe(sass({outputStyle: 'compressed'}).on('error', sass.logError))
		.pipe(rename({ extname: '.min.css' }))
		.pipe(dest('./css/'))
	;
};

/*
function webfonts() {
	return src([
		'node_modules/@fortawesome/fontawesome-free/webfonts/fa-brands-400.ttf',
		'node_modules/@fortawesome/fontawesome-free/webfonts/fa-brands-400.woff2',
		'node_modules/@fortawesome/fontawesome-free/webfonts/fa-regular-400.ttf',
		'node_modules/@fortawesome/fontawesome-free/webfonts/fa-regular-400.woff2',
		'node_modules/@fortawesome/fontawesome-free/webfonts/fa-solid-900.ttf',
		'node_modules/@fortawesome/fontawesome-free/webfonts/fa-solid-900.woff2',
		'node_modules/@fortawesome/fontawesome-free/webfonts/fa-v4compatibility.ttf',
		'node_modules/@fortawesome/fontawesome-free/webfonts/fa-v4compatibility.woff2',
	])
	.pipe(dest('./fonts/'))
	;
}
*/

function watchAll() {
	watch([
		'./scss/*.scss',
	], buildCss);
	watch([
		'node_modules/bootstrap/js/dist/*.js',
		'./*.js',
	], buildJs);
};


/**
 * Exposition des tâches publiques
 */

 exports.clean    = cleanOutput;
 exports.buildjs  = buildJs;
 exports.buildcss = buildCss;
 //exports.fonts    = webfonts;
 exports.watch    = watchAll;
 
 // tâche par défaut (la fonction series permet d'enchainer plusieurs tâches)
 exports.default = series(cleanOutput, buildJs, buildCss, /*webfonts*/);
 