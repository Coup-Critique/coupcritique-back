#!/bin/bash

composer clearcache && composer install
php8.2 bin/console cache:clear
php8.2 bin/console doctrine:schema:update --dump-sql --force
yarn install --frozen-lockfile && yarn build