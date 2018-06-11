#!/bin/bash
set -xe

VERSION=${CIRCLE_TAG:-$CIRCLE_BRANCH}
DOCKER_IMAGE_TAG="eu.gcr.io/$GCLOUD_PROJECT/app-front:$VERSION-$CIRCLE_SHA1"

# Build the image
docker build -t $DOCKER_IMAGE_TAG react/

# Push the images to Google Cloud
gcloud docker -- push $DOCKER_IMAGE_TAG

# Set image tag only for master
if [ "$VERSION" = "master" ]; then
    gcloud container images add-tag $DOCKER_IMAGE_TAG --quiet
fi
