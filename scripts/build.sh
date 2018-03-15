#!/bin/bash
set -xe

BRANCH="$1"
DOCKER_IMAGE="eu.gcr.io/$GCLOUD_PROJECT/app"
DOCKER_IMAGE_TAG="$BRANCH-$CIRCLE_SHA1"

# Build the image
gcloud docker -- pull $DOCKER_IMAGE:$BRANCH
docker build --cache-from=$DOCKER_IMAGE:$BRANCH -t $DOCKER_IMAGE:$DOCKER_IMAGE_TAG .

# Push the images to Google Cloud
gcloud docker -- push $DOCKER_IMAGE:$DOCKER_IMAGE_TAG

# Set image tag
gcloud container images add-tag $DOCKER_IMAGE:$DOCKER_IMAGE_TAG $DOCKER_IMAGE:$BRANCH --quiet
