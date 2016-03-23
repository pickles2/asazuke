#!/bin/bash
DIR=$(dirname $0)
cd "${DIR}/.."
composer run:cssworks

echo "5秒後に自動終了します。"
sleep 5s
