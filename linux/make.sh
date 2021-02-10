#!/bin/sh

# micro docker maker

IMAGE_TAG=${IMAGE_TAG-dixyes/microbuilder:latest}
CONTAINER_NAME=${CONTAINER_NAME-microbuilder}

# generate Dockerfile
php linux/dockerfile.php $@ > Dockerfile || {
    echo "Failed generating dockerfile" >&2
    exit 1
}

# make image
docker build . -t ${IMAGE_TAG} || {
    echo "Failed build using this dockerfile" >&2
    exit 1
}

# create intermediate container with Docerfile
id=`docker create --name ${CONTAINER_NAME} ${IMAGE_TAG}` || {
    echo "Failed create intermediate container" >&2
    exit 1
}

# copy it out
exec docker cp $id:/work/php/sapi/micro/micro.sfx .


