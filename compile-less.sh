#!/bin/bash

function usage()
{
	echo ""
	echo "Usage: build-less.sh [options]"
	echo ""
	echo "  -h, --help               Display usage"
	echo "  -p <path>, --path=<path> Define the path to the less files, defaults to ./css"
	echo ""
}

function checkRequirement()
{
	type npm >/dev/null 2>&1 || { echo >&2 "Error: npm is required"; exit 1; }
	type lessc >/dev/null 2>&1 || { echo >&2 "Error: lessc is required"; exit 1; }
}

checkRequirement

ROOTPATH=$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)
LESSPATH="css"

while (($#))
do
	case $1 in
		-p)
		LESSPATH="$2"
		shift
		;;
		--path=*)
		LESSPATH="${1#*=}"
		;;
		-h|--help)
		usage
		exit 0
		;;
	esac

	shift
done

if [ ! -d $ROOTPATH/$LESSPATH ]; then
	echo "Error: Directory $ROOTPATH/$LESSPATH does not exist"
	echo "See --help for more information"
	exit 1
fi

FILES=$ROOTPATH/$LESSPATH/*.less

for f in $FILES
do
	echo "Compiling $f"
	FILENAME=$(basename $f .less)
	lessc --clean-css -autoprefix="last 10 versions" --source-map="$ROOTPATH/$LESSPATH/$FILENAME.map" $f $ROOTPATH/$LESSPATH/$FILENAME.css
done

echo "Completed!"
