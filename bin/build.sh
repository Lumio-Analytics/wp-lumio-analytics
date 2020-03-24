#!/bin/bash
CURDIR=`pwd`
cd $TMPDIR
rm -rf wp-lumio-analytics
git clone git@github.com:Lumio-Analytics/wp-lumio-analytics.git
cd wp-lumio-analytics/
composer install --no-ansi --no-dev --no-interaction --no-progress --no-scripts --optimize-autoloader
cd ..
zip -r9 $CURDIR/dist/wp-lumio-analytics-full.zip wp-lumio-analytics -x@wp-lumio-analytics/exclude.lst
cd wp-lumio-analytics/
git checkout wordpress.org
cd ..
zip -r9 $CURDIR/dist/wp-lumio-analytics.zip wp-lumio-analytics -x@wp-lumio-analytics/exclude.lst
cd $CURDIR