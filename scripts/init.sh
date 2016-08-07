#!/bin/bash

BLACK=$(tput setaf 0)
RED=$(tput setaf 1)
GREEN=$(tput setaf 2)
YELLOW=$(tput setaf 3)
LIME_YELLOW=$(tput setaf 190)
POWDER_BLUE=$(tput setaf 153)
BLUE=$(tput setaf 4)
MAGENTA=$(tput setaf 5)
CYAN=$(tput setaf 6)
WHITE=$(tput setaf 7)
RESET=$(tput sgr0)
BLINK=$(tput blink)
REVERSE=$(tput smso)
UNDERLINE=$(tput smul)
ENDUNDERLINE=$(tput rmul)
BOLD=$(tput bold)

ROOTPATH=$(cd "$(dirname $0)"/../../ && pwd)

cd $ROOTPATH

printf "This will generate a basic scaffold on the root folder. ${BOLD}[Y/n]${RESET} "

read PERMISSION

if [ "$PERMISSION" == "Y" ] || [ "$PERMISSION" == "y" ] || [ "$PERMISSION" == "" ]; then
	echo ""
	echo "${UNDERLINE}Create basic folders${ENDUNDERLINE}"
	echo "${RESET}"

	FOLDERS=(apis assets controllers routers schemas tables templates views)

	for F in ${FOLDERS[@]}; do
		printf "${BOLD}    "

		if [ -d $F ]; then
			printf "${RED}Exist       "
		else
			mkdir $F
			printf "${GREEN}Created     "
		fi

		printf "${RESET}$F\n"
	done

	echo "${RESET}"

	echo ""
	echo "${UNDERLINE}Copy sample scaffold files${ENDUNDERLINE}"
	echo "${RESET}"

	for F in $(find lib/scaffold -type f); do
		printf "${BOLD}    "

		RELATIVEF=${F:13}

		if [ -f "$RELATIVEF" ]; then
			printf "${RED}Exist       "
		else
			mkdir -p $(dirname $RELATIVEF)
			cp $F $RELATIVEF
			printf "${GREEN}Copy        "
		fi

		printf "${RESET}$RELATIVEF\n"
	done;

	echo "${RESET}"

	if [ ! -d node_modules ]; then
		echo "${BOLD}${RED}node_modules${RESET} folder not found."
		echo "    Run ${CYAN}${UNDERLINE}npm install${ENDUNDERLINE}."
	else
		echo "${BOLD}${GREEN}node_modules${RESET} folder found."
	fi

	echo "${RESET}"

	if [ ! -d vendor ]; then
		echo "${BOLD}${RED}vendor${RESET} folder not found."
		echo "    Run ${CYAN}${UNDERLINE}composer install${ENDUNDERLINE}."
	else
		echo "${BOLD}${GREEN}vendor${RESET} folder found."
	fi

	echo "${RESET}"
fi


