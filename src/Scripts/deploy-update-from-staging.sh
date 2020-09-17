#!/bin/bash
set -x

DIR="survloop.org"
USER="survuser"
PCKGA="rockhopsoft"
PCKGB="survlooporg"

if [ $1 = 'maintenance' ]
then
    . /home/$USER/survloop/mainentance-mode.sh on
    DIR="$DIR-production"
fi

rm -R /var/www/$DIR/vendor/rockhopsoft/survloop/src
mv /home/$USER/staging/rockhopsoft/survloop/src /var/www/$DIR/vendor/rockhopsoft/survloop/src

rm -R /var/www/$DIR/vendor/rockhopsoft/survloop-libraries/src
mv /home/$USER/staging/rockhopsoft/survloop-libraries/src /var/www/$DIR/vendor/rockhopsoft/survloop-libraries/src

rm -R /var/www/$DIR/vendor/$PCKGA/$PCKGB/src
mv /home/$USER/staging/$PCKGA/$PCKGB/src /var/www/$DIR/vendor/$PCKGA/$PCKGB/src

rm -R /var/www/$DIR/app/Models

cd /var/www/$DIR
echo "0" | php artisan vendor:publish --force
php artisan optimize:clear
composer dump-autoload

if [ $1 = 'maintenance' ]
then
    . /home/$USER/survloop/mainentance-mode.sh off
fi
