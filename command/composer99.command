#!/bin/bash
DIR=$(dirname $0)
cd "${DIR}/.."
composer test

echo "10秒後に自動終了します。"
sleep 10s
