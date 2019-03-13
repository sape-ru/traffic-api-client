#!/usr/bin/env bash

# Script ENV ----------------------------------------------------------------------

SRC="${BASH_SOURCE[0]//\\//}"
[[ -z "$SRC" ]] && SRC="$(readlink -f $0)"
DIR="$(cd -P "${SRC%/*}" > /dev/null && pwd)"

# Action --------------------------------------------------------------------------

cd ${DIR}

bash cli/phpunit.sh full
