#!/bin/sh

### パラメータありの場合のテスト

cp -Rfpav src/data/SampleSite-testParam/* src/data/SampleSite/
composer run:site-scan
composer run:site-validation
composer run:cssworks
composer run:site-validation-csv
composer run:scraping
open -b com.google.Chrome http://127.0.0.1:8899/mst.php