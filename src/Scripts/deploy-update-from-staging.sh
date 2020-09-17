#!/bin/bash
set -x

DIR="survloop.org"
USER="survuser"

if [ $1 = 'maintenance' ]
then
    . /home/$USER/survloop/mainentance-mode.sh on
    DIR="$DIR-production"
fi

cp -r /home/$USER/staging/survloop/src /var/www/$DIR/vendor/rockhopsoft/survloop/src-staged
cp -r /home/$USER/staging/survloop-libraries/src /var/www/$DIR/vendor/rockhopsoft/survloop-libraries/src-staged
cp -r /home/$USER/staging/survlooporg/src /var/www/$DIR/vendor/rockhopsoft/survlooporg/src-staged

rm -R /var/www/$DIR/vendor/rockhopsoft/survloop/src
mv /var/www/$DIR/vendor/rockhopsoft/survloop/src-staged /var/www/$DIR/vendor/rockhopsoft/survloop/src

rm -R /var/www/$DIR/vendor/rockhopsoft/survloop-libraries/src
mv /var/www/$DIR/vendor/rockhopsoft/survloop-libraries/src-staged /var/www/$DIR/vendor/rockhopsoft/survloop-libraries/src

rm -R /var/www/$DIR/vendor/rockhopsoft/survlooporg/src
mv /var/www/$DIR/vendor/rockhopsoft/survlooporg/src-staged /var/www/$DIR/vendor/rockhopsoft/survlooporg/src

rm -R /var/www/$DIR/app/Models

cd /var/www/$DIR
echo "0" | php artisan vendor:publish --force
php artisan optimize:clear
composer dump-autoload

if [ $1 = 'maintenance' ]
then
    . /home/$USER/survloop/mainentance-mode.sh off
fi
