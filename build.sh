#!/bin/bash

ROOTPATH=$(cd "$(dirname $0)" && pwd)

LESS=$ROOTPATH/node_modules/.bin/lessc
SASS=$ROOTPATH/node_modules/.bin/node-sass
POSTCSS=$ROOTPATH/node_modules/.bin/postcss

TYPE=$1
SUBTYPE=$2

function compileCSS()
{
	mkdir -p assets/css

	CSSRENDERER=$(grep -o "cssRenderer = '.*';" config.php | sed -n "s/cssRenderer = \'\(.*\)\';/\1/p")

	if [ "$CSSRENDERER" != "" ]; then
		case $CSSRENDERER in
			less)
				checkLess

				if [ "$TYPE" == "css" ] && [ "$SUBTYPE" != "" ]; then
					compileLess $SUBTYPE
				else
					for F in $ROOTPATH/assets/less/*.less; do
						compileLess $(basename $F .less)
					done
				fi
			;;
			sass)
				checkSass

				if [ "$TYPE" == "css" ] && [ "$SUBTYPE" != "" ]; then
					compileSass $SUBTYPE
				else
					for F in $ROOTPATH/assets/sass/*.sass; do
						compileSass $(basename $F .sass)
					done
				fi
			;;

			scss)
				checkSass

				if [ "$TYPE" == "css" ] && [ "$SUBTYPE" != "" ]; then
					compileScss $SUBTYPE
				else
					for F in $ROOTPATH/assets/scss/*.scss; do
						compileScss $(basename $F .scss)
					done
				fi
		esac
	fi
}

function checkLess()
{
	type $ROOTPATH/node_modules/.bin/lessc >/dev/null 2>&1 || {
		echo >&2 "Error: lessc is not found. Run npm install first."
		exit 1
	}
}

function checkSass()
{
	type $ROOTPATH/node_modules/.bin/node-sass >/dev/null 2>&1 || {
		echo >&2 "Error: node-sass is not found. Run npm install first."
		exit 1
	}
	type $ROOTPATH/node_modules/.bin/postcss >/dev/null 2>&1 || {
		echo >&2 "Error: postcss is not found. Run npm install first."
		exit 1
	}
}

function compileLess()
{
	$LESS --autoprefix='last 4 versions, ios 7, android 4.4' $ROOTPATH/assets/less/$1.less $ROOTPATH/assets/css/$1.css
}

function compileSass()
{
	$SASS -i $ROOTPATH/assets/sass/$1.sass > $ROOTPATH/assets/css/$1.css

	$POSTCSS --use autoprefixer --autoprefixer.browsers \"last 4 versions, ios 7, android 4.4\" -o $ROOTPATH/assets/css/$1.css $ROOTPATH/assets/css/$1.css
}

function compileScss()
{
	$SASS $ROOTPATH/assets/sass/$1.scss > $ROOTPATH/assets/css/$1.css

	$POSTCSS --use autoprefixer --autoprefixer.browsers \"last 4 versions, ios 7, android 4.4\" -o $ROOTPATH/assets/css/$1.css $ROOTPATH/assets/css/$1.css
}

if [ "$1" == "" ]; then
	compileCSS
else
	if [ "$1" == "css" ]; then
		compileCSS
	fi
fi

exit 0
