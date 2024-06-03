#!/usr/bin/env sh
# shellcheck disable=SC2039 # disable 'echo flags unsupported'
# shellcheck disable=SC2034 # disable 'appears unused'

# Define colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
WHITE='\033[0;37m'
RESET='\033[0m' # No Color

p() {
    text=$1
    color_input=$(echo "$2" | awk '{print toupper($0)}')
    eval "color=\$$color_input"

    # shellcheck disable=SC2154
    echo -e "${color}${text}${RESET}"
}
