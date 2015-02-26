#!/bin/bash

set -e
set -x

DB=$1
TRAVIS_PHP_VERSION=$2

if [ '$TRAVIS_PHP_VERSION' = '5.3.3' ] && [ '$DB' = 'mysqli' ]; then
  wget https://scrutinizer-ci.com/ocular.phar
  php ocular.phar code-coverage:upload --format=php-clover coverage.clover
fi
