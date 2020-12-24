#!/bin/bash
shopt -s extglob

cd build/testproject/
composer remove auxmoney/opentracing-bundle-jaeger
composer require auxmoney/opentracing-bundle-zipkin
rm -fr vendor/auxmoney/opentracing-bundle-zipkin/*
cp -r ../../!(build|vendor) vendor/auxmoney/opentracing-bundle-zipkin
composer dump-autoload
cd ../../

docker run -d -p 9411:9411 --name zipkin openzipkin/zipkin:2.19
sleep 5
docker stop zipkin
