#!/usr/bin/env bash

# Init ----------------------------------------------------------------------

SRC="${BASH_SOURCE[0]//\\//}"
[[ -z "$SRC" ]] && SRC="$(readlink -f $0)"
DIR="$(cd -P "${SRC%/*}" > /dev/null && pwd)"

CLI_DIR="$DIR"
ROOT_DIR="$(cd -P "${DIR}/../" > /dev/null && pwd)"

if which cygpath > /dev/null 2>&1; then
    DIR="$(cygpath -m $DIR)"
    ROOT_DIR="$(cygpath -m $ROOT_DIR)"
fi

# Tools ---------------------------------------------------------------------

fa_composer="$DIR/composer.phar"

# Functions -----------------------------------------------------------------

php-no-xdebug() { XDEBUG_CONFIG= php "$@"; }

_composer() {
    [[ -f ${fa_composer} ]] || do_install-composer

    php-no-xdebug "$fa_composer" "$@"
}

# Actions --------------------------------------------------------

do_install-composer()
{
    cd ${DIR}

    echo 'Getting composer.phar ...'
    php-no-xdebug -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"

    declare actual_signature="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"

    echo 'Getting composer signature ...'
    declare expected_signature="$(wget -O - https://composer.github.io/installer.sig)"

    echo 'Checking composer signature ...'

    if [[ "$expected_signature" != "$actual_signature" ]]; then
        {
            echo 'ERROR: Invalid installer signature'
            echo "[$expected_signature] != [$actual_signature]"
        } >&2

        rm -f composer-setup.php

        return 1
    else
        echo 'Signature OK'
    fi

    echo 'Running composer setup ...'

    php-no-xdebug composer-setup.php
    declare res=$?

    echo 'Remove composer setup file'
    rm -f composer-setup.php

    if [[ ${res} -eq 0 ]]; then
        php-no-xdebug "$fa_composer" --version
    fi

    return ${res}
}

do_help() {
    echo '-- Работа с composer --'
    echo 'help                - Эта справка'
    echo ''
    echo 'c                   - Выполнить любую команду composer'
    echo 'install             - Поставить все нужные пакеты'
    echo 'require             - Выполнить require'
    echo 'status              - Выполнить install'
    echo ''
    echo 'Пакеты:'
    echo 'update <package>    - Обновить пакет'
    echo 'reinstall <package> - Переустановить пакет'
    echo 'show <package>      - Показать инфу по пакету'
    echo 'path <package>      - Получить путь установки пакета'
}

do_path() {
    declare package="$1"

    if [[ -z ${package} ]]; then
        _composer show -N
    else
        _composer show --path | grep "$package" | sort | head -1 | awk '{print $2}'
    fi
}

do_install() {
    echo '-- Composer install --'
    _composer install --ignore-platform-reqs "$@"
}

do_status() {
    echo '-- Composer status --'
    _composer status "$@"
}

do_reinstall() {
    declare package="$1"

    if [[ -z "${package}" ]]; then
        _composer show -N
    else
        declare path=$(do_path "$package")

          [[ -z "$path" ]]                          && { echo "No path for package [$package] !"; exit 1; }
        ! [[ -d "$path" ]]                          && { echo "Path [$path] is not dir !"; exit 1; }
        ! [[ "${path##${ROOT_DIR}}" != "${path}" ]] && { echo "Path [$path] is not subdir of [$ROOT_DIR] !"; exit 1; }

        echo "-- Reinstall $package --"
        echo "Deleting path [$path]"

        rm -rf "$path"

        do_install
    fi
}

do_version() {
    _composer --version
}

do_show() {
    declare package="$1"

    if [[ -z ${package} ]]; then
        _composer show -N
    else
        _composer show "$package" "${@:2}"
    fi
}

do_update() {
    declare package="$1"

    if [[ -z ${package} ]]; then
        _composer show -N
    else
        echo "-- Composer update $package --"
        _composer update "$package" "${@:2}"
    fi
}

do_require() {
    _composer require "$@"
}

do_c() {
    if [[ -z $1 ]]; then
        _composer list --raw | awk '{print $1}'
    else
        _composer "$@"
    fi
}

do_run() {
    _composer "$@"
}

# System ---------------------------------------------------------

do_list-funcs() {
    declare action="$1"

    if [[ -z ${action} ]]; then
        declare -F | sed 's/declare -f //' | grep -P '^do_' | sed 's/do_//'
    else
        [[ ${action} == update ]] && do_update
        [[ ${action} == path ]] && do_path
        [[ ${action} == show ]] && do_show
        [[ ${action} == reinstall ]] && do_reinstall
        [[ ${action} == c ]]      && do_c
    fi
}

# Run -----------------------------------------------------------------

cd "$ROOT_DIR"
action="$1"

[[ -z ${action} ]] && action=list-funcs

shift 1

# [[ ${action} != list-funcs ]] && pwd

"do_$action" "$@"
