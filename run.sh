#!/bin/bash

#docker build -t aweb-framework:1.0 .

docker rm -f aweb-framework-run-01

currentDir=`pwd`

docker run -d \
--name aweb-framework-run-01 \
-v $currentDir:/var/www/html \
-e ERRORS=1 \
-p 8103:80  \
aweb-framework:1.0
