#!/bin/bash
DIR=$(dirname $0)
cd "${DIR}/.."
open .

echo "10秒後に自動終了します。"
sleep 10s
