#!/bin/bash
DIR=$(dirname $0)
cd "${DIR}/.."
composer
argv=("$@")
for i in `seq 1 $#`
do
 echo "argv[`expr $i - 1`]=${argv[$i-1]}"
done

echo "5秒後に自動終了します。"
sleep 5s
