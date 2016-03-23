#!/bin/bash
DIR=$(dirname $0)
cd "${DIR}/.."
composer run:site-validation-csv

echo "5秒後に自動終了します。"
sleep 5s
