#!/bin/bash

set -e
set -x

DB=$1
TRAVIS_PHP_VERSION=$2

if [ "$TRAVIS_PHP_VERSION" == "5.5" -a "$DB" == "mysqli" ]
then
	phpBB/vendor/bin/phpunit --configuration phpBB/ext/$EXTNAME/travis/phpunit-$DB-travis.xml --bootstrap ./tests/bootstrap.php --coverage-clover build/logs/clover.xml
else
	phpBB/vendor/bin/phpunit --configuration phpBB/ext/$EXTNAME/travis/phpunit-$DB-travis.xml --bootstrap ./tests/bootstrap.php
fi
