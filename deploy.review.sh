#!/bin/bash

composer clearcache && composer install
php7.4 bin/console cache:clear
php7.4 bin/console doctrine:schema:update --dump-sql --force
yarn install --frozen-lockfile && yarn build
cachetool opcache:reset
