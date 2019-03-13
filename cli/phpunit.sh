#!/usr/bin/env bash

# Script ENV ----------------------------------------------------------------------

SRC="${BASH_SOURCE[0]//\\//}"
[[ -z "$SRC" ]] && SRC="$(readlink -f $0)"
DIR="$(cd -P "${SRC%/*}" > /dev/null && pwd)"

CLI_DIR="$DIR"
ROOT_DIR="$(cd -P "${DIR}/../" > /dev/null && pwd)"

if which cygpath > /dev/null 2>&1; then
    DIR="$(cygpath -m $DIR)"
    ROOT_DIR="$(cygpath -m $ROOT_DIR)"
fi

# Config ----------------------------------------------------------------------------

declare fa_phpunit_cfg="$ROOT_DIR/phpunit.xml"
declare da_scripts="$ROOT_DIR/tests/"

# Functions -----------------------------------------------------------------------

_phpunit() {
    echo "php $ROOT_DIR/vendor/phpunit/phpunit/phpunit" "$@"
    php "$ROOT_DIR/vendor/phpunit/phpunit/phpunit" "$@"
}

_phpunit_with_cfg() {
    declare -a phpunit_opt

    phpunit_opt+=('--colors=auto')
    [[ -f ${fa_phpunit_cfg} ]] && phpunit_opt+=('--configuration' "$fa_phpunit_cfg")

    _phpunit "${phpunit_opt[@]}" "$@"
}

_composer() {
    "$CLI_DIR/composer.sh" "$@"
}

_list-funcs() {
    declare -F | sed 's/declare -f //' | grep -P '^do_' | sed 's/do_//' | grep -v 'list-funcs'
}

# Actions -------------------------------------------------------------

do_list-funcs() {
    declare action="$1"

    if [[ -z ${action} ]]; then
        _list-funcs
    else
        [[ ${action} == group ]] && do_group -l "${@:2}"
    fi
}

do_c() {
    _phpunit "$@"
}

do_full() {
    echo -e '\n== Updating libs ==================================\n'
    _composer install

    echo -e '\n== Running tests ==================================\n'

    _phpunit_with_cfg "${da_scripts}"

    echo -e '\n== Finished tests ==================================\n'
}

do_group() {
    # -- Arguments --
    declare list
    declare flags="lCc:" OPTIND=1
    declare -a params
    for (( ; OPTIND <= $#; )) do
        getopts "$flags" flag && { case $flag in
            l) list=1 ;;
            C) PHPUNIT_CFG='' ;;
            c) PHPUNIT_CFG="${OPTARG}" ;;
        esac; } || {
            params+=("${!OPTIND}"); ((OPTIND++))
        }
    done

    declare -a groups="${params[@]}"

    if [[ -n "$list" ]]; then
        echo default
        grep -P '@group\s+\S+' -o -R "$da_scripts" -h | awk '{print $2}' | sort -u
    else
        [[ -z "$groups" ]] && { echo "No groups is set!"; return 1; }

            echo "Groups: ${groups[@]}"

            _phpunit_with_cfg --group "${groups[@]}"
    fi
}

# Options -------------------------------------------------------------

export TEST_XDEBUG=0
export TEST_DEV=0
export TEST_OFFLINE=0
export TEST_ONLINE=0

declare -a params

_process_options() {
    # -- Arguments --
    declare flags=":dxXOou:" OPTIND=1
    for (( ; OPTIND <= $#; )) do
        getopts "$flags" flag && { case ${flag} in
            x) export TEST_XDEBUG=1 ;;
            X) export XDEBUG_CONFIG='' ;;
            d) export TEST_DEV=1 ;;
            u) export TEST_USER="${OPTARG}" ;;
            O) export TEST_OFFLINE=1 ;;
            o) export TEST_ONLINE=1 ;;
            :) [[ -n ${OPTARG} ]] && params+=("-${OPTARG}") ;;
            ?) [[ -n ${OPTARG} ]] && params+=("-${OPTARG}") ;;
            *) echo "Unexpected: ${flag} ${OPTARG} ${OPTIND}" ;;
        esac; } || {
            params+=("${!OPTIND}"); ((OPTIND++));
        }
    done
}

# Run -----------------------------------------------------------------

if [[ $1 == 'c' ]]; then
    do_c "${@:2}"
else
    _process_options "$@"

    action="${params[0]}"

    [[ -z ${action} ]] && action=list-funcs

    "do_$action"  "${params[@]:1}"
fi

