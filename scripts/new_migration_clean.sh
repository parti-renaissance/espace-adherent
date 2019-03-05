#!/bin/bash

MIGRATION_DIRECTORY='src/Migrations'

for MIGRATION_FILE_PATH in `git ls-files --others --exclude-standard $MIGRATION_DIRECTORY`; do
    if grep -q 'declare(strict_types=1);' $MIGRATION_FILE_PATH;
    then
        sed -i '/declare(strict_types=1);/ { N; d; }' $MIGRATION_FILE_PATH
    fi

    if grep -q 'Auto-generated Migration: Please modify to your needs!' $MIGRATION_FILE_PATH;
    then
        sed -i '/^\/\*/d' $MIGRATION_FILE_PATH
        sed -i '/^ \* Auto-generated Migration: Please modify to your needs!/d' $MIGRATION_FILE_PATH
        sed -i '/^ \*\//d' $MIGRATION_FILE_PATH
    fi

    if grep -q 'migration is auto-generated, please modify it to your needs' $MIGRATION_FILE_PATH;
    then
        sed -i '/migration is auto-generated, please modify it to your needs/d' $MIGRATION_FILE_PATH
    fi

    if grep -q 'abortIf' $MIGRATION_FILE_PATH;
    then
        sed -i '/abortIf/ { N; d; }' $MIGRATION_FILE_PATH
    fi

    echo "File $MIGRATION_FILE_PATH has been cleaned"
done
