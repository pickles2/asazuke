#!/bin/bash
DIR=$(dirname $0)
cd "${DIR}/.."
composer run:scraping

echo "5秒後に自動終了します。"
sleep 5s
