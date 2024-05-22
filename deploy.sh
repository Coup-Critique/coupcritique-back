#!/bin/bash

composer clearcache && composer install
php bin/console cache:clear
php bin/console doctrine:schema:update --dump-sql --force --complete