#!/bin/bash
set -e

TARGETED_ENV="${1:-default}"
if [[ "${TARGETED_ENV}" == "master" ]]; then
    TARGETED_ENV="preprod"
fi

DOCKER_IMAGE="eu.gcr.io/$GCLOUD_PROJECT/app"

# Google Cloud authentication
echo $GCLOUD_SERVICE_KEY | base64 --decode > $HOME/gcloud-service-key.json && cp $HOME/gcloud-service-key.json gcloud-service-key.json
sudo /opt/google-cloud-sdk/bin/gcloud --quiet components update
sudo /opt/google-cloud-sdk/bin/gcloud auth activate-service-account --key-file $HOME/gcloud-service-key.json
sudo /opt/google-cloud-sdk/bin/gcloud config set project $GCLOUD_PROJECT

# Build the image
yarn run build-prod
yarn run build-amp
docker pull $DOCKER_IMAGE:$TARGETED_ENV
docker build --cache-from=$DOCKER_IMAGE:$TARGETED_ENV -t $DOCKER_IMAGE:$CIRCLE_SHA1 .

# Push the images to Google Cloud
sudo /opt/google-cloud-sdk/bin/gcloud docker -- push $DOCKER_IMAGE:$CIRCLE_SHA1

# Set image tag
sudo /opt/google-cloud-sdk/bin/gcloud container images add-tag $DOCKER_IMAGE:$CIRCLE_SHA1 $DOCKER_IMAGE:$TARGETED_ENV --quiet
