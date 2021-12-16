#!/bin/bash
shopt -s extglob

echo "DEBUG: GITHUB_HEAD_REF = $GITHUB_HEAD_REF"
echo "DEBUG: GITHUB_REF = $GITHUB_REF"

cd build/testproject/
composer remove auxmoney/opentracing-bundle-jaeger
VENDOR_VERSION=""
CURRENT_REF=${GITHUB_HEAD_REF:-GITHUB_REF}
CURRENT_BRANCH=${CURRENT_REF#refs/heads/}
echo "DEBUG: CURRENT_REF = $CURRENT_REF"
echo "DEBUG: CURRENT_BRANCH = $CURRENT_BRANCH"
if [[ $CURRENT_BRANCH -ne "master" ]]; then
    composer config minimum-stability dev
    VENDOR_VERSION=":dev-${CURRENT_BRANCH}"
fi
composer require auxmoney/opentracing-bundle-zipkin${VENDOR_VERSION}
composer dump-autoload
cd ../../

docker run -d -p 9411:9411 --name zipkin openzipkin/zipkin:2.19
sleep 5
docker stop zipkin
