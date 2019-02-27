#!/bin/bash

MIGRATION_DIRECTORY='app/migrations'
LAST_MIGRATION_FILE_NAME=$(ls -t $MIGRATION_DIRECTORY | head -n1)
FILE_PATH=${MIGRATION_DIRECTORY}/${LAST_MIGRATION_FILE_NAME}

echo "Last file name is $LAST_MIGRATION_FILE_NAME"

echo "Replacing ' : void' to ': void' ..."
if grep -q ' : void' $FILE_PATH; then
    sed -i 's/ : void/: void/g' $FILE_PATH
    echo "Pattern found and replaced"
else
    echo "Pattern wasn't found"
fi

echo "Removing 'declare(strict_types=1);' ..."
if grep -q 'declare(strict_types=1);' $FILE_PATH;
then
    sed -i 's/ declare(strict_types=1);//g' $FILE_PATH
    echo "Pattern found and replaced"
else
    echo "Pattern wasn't found"
fi

echo "Removing function comments ..."
if grep -q 'Auto-generated Migration: Please modify to your needs!' $FILE_PATH;
then
    sed -i '8,10d' $FILE_PATH
    echo "Pattern found and replaced"
else
    echo "Pattern wasn't found"
fi

echo "Removing comments ..."
if grep -q 'migration is auto-generated, please modify it to your needs' $FILE_PATH;
then
    sed -i '/migration is auto-generated, please modify it to your needs/d' $FILE_PATH
    echo "Pattern found and replaced"
else
    echo "Pattern wasn't found"
fi

echo "Removing 'abortIf' ..."
if grep -q 'abortIf' $FILE_PATH;
then
    sed -i '/abortIf/ { N; d; }' $FILE_PATH
    echo "Pattern found and replaced"
else
    echo "Pattern wasn't found"
fi
