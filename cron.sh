#!/bin/bash

php /var/www/html/coupcritique.fr/bin/console gesdinet:jwt:clear
#php /var/www/html/coupcritique.fr/bin/console app:clear-notification
php /var/www/html/coupcritique.fr/bin/console ban:teams