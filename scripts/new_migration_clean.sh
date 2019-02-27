#!/bin/bash

MIGRATION_DIRECTORY='app/migrations'
LAST_MIGRATION_FILE_NAME=$(ls -t $MIGRATION_DIRECTORY | head -n1)
FILE_PATH=${MIGRATION_DIRECTORY}/${LAST_MIGRATION_FILE_NAME}

if grep -q 'declare(strict_types=1);' $FILE_PATH;
then
    sed -i '/declare(strict_types=1);/ { N; d; }' $FILE_PATH
fi

if grep -q 'Auto-generated Migration: Please modify to your needs!' $FILE_PATH;
then
    sed -i '8,10d' $FILE_PATH
fi

if grep -q 'migration is auto-generated, please modify it to your needs' $FILE_PATH;
then
    sed -i '/migration is auto-generated, please modify it to your needs/d' $FILE_PATH
fi

if grep -q 'abortIf' $FILE_PATH;
then
    sed -i '/abortIf/ { N; d; }' $FILE_PATH
fi
