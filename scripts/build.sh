#!/bin/bash
set -xe

TARGETED_ENV="${1:-default}"
if [[ "${TARGETED_ENV}" == "master" ]]; then
    TARGETED_ENV="preprod"
fi

DOCKER_IMAGE="eu.gcr.io/$GCLOUD_PROJECT/app"
DOCKER_IMAGE_TAG="$TARGETED_ENV-$CIRCLE_SHA1"

# Build the image
gcloud docker -- pull $DOCKER_IMAGE:$TARGETED_ENV
docker build --cache-from=$DOCKER_IMAGE:$TARGETED_ENV -t $DOCKER_IMAGE:$DOCKER_IMAGE_TAG .

# Push the images to Google Cloud
gcloud docker -- push $DOCKER_IMAGE:$DOCKER_IMAGE_TAG

# Set image tag
gcloud container images add-tag $DOCKER_IMAGE:$DOCKER_IMAGE_TAG $DOCKER_IMAGE:$TARGETED_ENV --quiet
