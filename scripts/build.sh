#!/bin/bash
set -xe

# Inject commit hash as app version
perl -pi -e "s/default/$CIRCLE_SHA1/g" ./app/config/app_version.yml

VERSION=${CIRCLE_TAG:-$CIRCLE_BRANCH}
DOCKER_IMAGE_TAG="eu.gcr.io/$GCLOUD_PROJECT/app:$VERSION-$CIRCLE_SHA1"
DOCKER_IMAGE_CACHE_TAG="eu.gcr.io/$GCLOUD_PROJECT/app:master"
DOCKER_IMAGE_LATEST_TAG="eu.gcr.io/$GCLOUD_PROJECT/app:latest"

# Build the image
gcloud docker -- pull $DOCKER_IMAGE_CACHE_TAG
docker build --cache-from=$DOCKER_IMAGE_CACHE_TAG -t $DOCKER_IMAGE_TAG .

# Push the images to Google Cloud
gcloud docker -- push $DOCKER_IMAGE_TAG

# Set image tag only for master
if [ "$VERSION" = "master" ]; then
    gcloud container images add-tag $DOCKER_IMAGE_TAG $DOCKER_IMAGE_CACHE_TAG $DOCKER_IMAGE_LATEST_TAG --quiet
fi
