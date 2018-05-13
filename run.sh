#!/bin/bash

#docker build -t aweb-framework:1.0 .

docker rm -f aweb-framework-run-01

docker run -d \
--name aweb-framework-run-01 \
-e ERRORS=1 \
-e MYSQL_READ_DEFAULT_SERVER=10.12.100.53 \
-p 8103:80  \
aweb-framework:1.0