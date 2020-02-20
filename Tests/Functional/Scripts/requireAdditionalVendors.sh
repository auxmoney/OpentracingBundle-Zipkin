#!/bin/bash

cd build/testproject/
composer remove auxmoney/opentracing-bundle-jaeger
composer require auxmoney/opentracing-bundle-zipkin:dev-${BRANCH}
cd ../../

docker run -d -p 9411:9411 --name zipkin openzipkin/zipkin:2.19
sleep 5
docker stop zipkin
