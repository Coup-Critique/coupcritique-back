#!/bin/bash

composer clearcache && composer install
php bin/console cache:clear
php bin/console doctrine:schema:update --dump-sql --force

yarn install --frozen-lockfile && yarn build

git config --local user.email "gfrydz2@gmail.com"
git config --local user.name "GeoDaz"
git add -A
git commit --no-verify -m "[skip ci] update package version"
