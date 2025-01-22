#!/bin/bash

php /var/www/html/coupcritique.fr/bin/console gesdinet:jwt:clear
php /var/www/html/coupcritique.fr/bin/console notifications:remove
php /var/www/html/coupcritique.fr/bin/console ban:teams