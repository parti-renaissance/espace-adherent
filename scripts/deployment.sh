#!/bin/bash
set -e

# Get credentials
sudo /opt/google-cloud-sdk/bin/gcloud --quiet components update kubectl
sudo /opt/google-cloud-sdk/bin/gcloud container clusters get-credentials $GCLOUD_CLUSTER --project $GCLOUD_PROJECT --zone europe-west1-d

# Migrates database
export GOOGLE_APPLICATION_CREDENTIALS=$HOME/gcloud-service-key.json

sudo /opt/google-cloud-sdk/bin/kubectl set image deployment/staging-migrate enmarche=eu.gcr.io/$GCLOUD_PROJECT/app:$CIRCLE_SHA1

# Deploy to staging
declare -a images=("staging-app" "staging-worker-mailer-campaign" "staging-worker-mailer-transactional" "staging-worker-referent")

for image in "${images[@]}"
do
   sudo /opt/google-cloud-sdk/bin/kubectl set image deployment/$image enmarche=eu.gcr.io/$GCLOUD_PROJECT/app:$CIRCLE_SHA1
done

# Send result to slack
migration_log=$(sudo /opt/google-cloud-sdk/bin/kubectl logs staging-migrate --container=enmarche || true)
json="{\"text\": \"\`\`\`$(echo $migration_log | sed 's/"//g' | sed "s/'//g" | sed 's/\\/\//g' )\`\`\`\"}"
curl -s "Content-Type: application/json" -d "payload=$json" https://hooks.slack.com/services/$SLACK_TOKEN
