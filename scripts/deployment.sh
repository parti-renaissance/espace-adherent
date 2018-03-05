#!/bin/bash
set -xe

BRANCH="$1"
DOCKER_IMAGE_FULLNAME="eu.gcr.io/$GCLOUD_PROJECT/app:$BRANCH-$CIRCLE_SHA1"

TARGETED_ENV="$1"
if [[ "${TARGETED_ENV}" == "master" ]]; then
    TARGETED_ENV="preprod"
fi

# Get credentials
gcloud container clusters get-credentials $GCLOUD_CLUSTER --project $GCLOUD_PROJECT --zone europe-west1-d

# Migrates database
export GOOGLE_APPLICATION_CREDENTIALS=$HOME/gcloud-service-key.json

kubectl set image deploy/${TARGETED_ENV}-migrate enmarche=$DOCKER_IMAGE_FULLNAME

# Deploys
declare -a images=("${TARGETED_ENV}-app" "${TARGETED_ENV}-worker-mailer-campaign" "${TARGETED_ENV}-worker-mailer-transactional" "${TARGETED_ENV}-worker-referent")

for image in "${images[@]}"
do
   kubectl set image deployment/$image enmarche=$DOCKER_IMAGE_FULLNAME
done

# Send result to slack
migration_log=$(kubectl logs deploy/${TARGETED_ENV}-migrate --container=enmarche || true)
json="{\"text\": \"\`\`\`$(echo ${TARGETED_ENV} $migration_log | sed 's/"//g' | sed "s/'//g" | sed 's/\\/\//g' )\`\`\`\"}"
curl -s "Content-Type: application/json" -d "payload=$json" https://hooks.slack.com/services/$SLACK_TOKEN
