#!/bin/bash

function usage()
{
	echo ""
	echo "Usage: build-less.sh [options]"
	echo ""
	echo "  -h, --help                       Display usage"
	echo "  -p <path>, --path=<path>         Define the path to the less files, defaults to ./assets/css"
	echo "  -a <value>, --autoprefix=<value> Define autoprefix value"
	echo "  -i <value>, --ignore=<value>     Define ignore value"
	echo "  --no-map                         Disable source-map generation"
	echo "  --no-min                         Disable minification"
	echo ""
	echo ""
	echo "Required binary"
	echo ""
	echo "  lessc"
	echo ""
	echo "Less plugins (optional - requires NPM to install)"
	echo ""
	echo "  less-plugin-clean-css"
	echo "  less-plugin-autoprefix"
}

function checkRequirement()
{
	type lessc >/dev/null 2>&1 || { echo >&2 "Error: lessc is required"; exit 1; }
}

function containsElement()
{
	local e
	for e in "${@:2}"; do [[ "$e" == "$1" ]] && return 0; done
	return 1
}

checkRequirement

ROOTPATH=$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)

LESSPATH="assets/css"
ARG_AUTOPREFIX="last 10 versions"
ARG_SOURCEMAP=1
ARG_MINIFY=1
ARG_IGNORE=()

while (($#))
do
	case $1 in
		-p)
			if [[ $# -lt 2 || ${2:0:1} == "-" ]]
			then
				echo "Error: Expecting a path value"
				echo "See --help for more information"
				exit 1
			fi

			LESSPATH="$2"
			shift
		;;

		--path)
			echo "Error: Expecting a path value"
			echo "See --help for more information"
			exit 1
		;;

		--path=*)
			LESSPATH="${1#*=}"
		;;

		-a)
			if [[ $# -lt 2 || ${2:0:1} == "-" ]]
			then
				echo "Error: Expecting an autoprefix value"
				echo "See --help for more information"
				exit 1
			fi

			ARG_AUTOPREFIX="$2"
			shift
		;;

		--autoprefix)
			echo "Error: Expecting an autoprefix value"
			echo "See --help for more information"
			exit 1
		;;

		--autoprefix=*)
			ARG_AUTOPREFIX="${1#*=}"
		;;

		--no-map)
			ARG_SOURCEMAP=0
		;;

		--no-min)
			ARG_MINIFY=0
		;;

		-i)
			if [[ $# -lt 2 || ${2:0:1} == "-" ]]
			then
				echo "Error: Expecting an ignore value"
				echo "See --help for more information"
				exit 1
			fi

			ARG_IGNORE=("$2")
			shift
		;;

		--ignore)
			echo "Error: Expecting an ignore value"
			echo "See --help for more information"
			exit 1
		;;

		--ignore=*)
			ARG_IGNORE=("${1#*=}")
		;;

		-h|--help)
			usage
			exit 0
		;;
	esac

	shift
done

if [ ! -d "$ROOTPATH"/"$LESSPATH" ]
then
	echo "Error: Directory "$ROOTPATH"/"$LESSPATH" does not exist"
	echo "See --help for more information"
	exit 1
fi

FILES="$ROOTPATH"/"$LESSPATH"/*.less

for f in $FILES
do
	FILENAME=$(basename "$f" .less)

	if containsElement $FILENAME $ARG_IGNORE
	then
		continue
	fi

	echo "Compiling $f"

	AUTOPREFIX=--autoprefix="$ARG_AUTOPREFIX"
	SOURCEMAP=""
	MINIFY=""

	if [ $ARG_SOURCEMAP -eq 1 ]
	then
		SOURCEMAP=--source-map="$ROOTPATH"/"$LESSPATH"/"$FILENAME".css.map
	fi

	if [ $ARG_MINIFY -eq 1 ]
	then
		MINIFY=--clean-css
	fi

	lessc "$MINIFY" "$AUTOPREFIX" "$SOURCEMAP" "$f" "$ROOTPATH"/"$LESSPATH"/"$FILENAME".css
done

echo "Completed!"
