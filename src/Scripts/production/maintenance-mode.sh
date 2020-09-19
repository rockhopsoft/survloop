#!/bin/bash
set -x

DIR=survloop.org

if [ $1 = 'on' ]
then
    if ! -d "/var/www/$DIR-in-progress"; then
        if ! -d "/var/www/$DIR-maintenance"; then
            mkdir /var/www/$DIR-maintenance
            mkdir /var/www/$DIR-maintenance/public
            cp /var/www/$DIR/vendor/rockhopsoft/survloop/src/Scripts/production/mainentance-index.php /var/www/$DIR-maintenance/public/index.php
            sudo nano /var/www/$DIR-maintenance/public/index.php
        fi
        mkdir /var/www/$DIR-in-progress
        mv /var/www/$DIR /var/www/$DIR-production
        mv /var/www/$DIR-maintenance /var/www/$DIR
    fi
elif [ $1 = 'off' ]
then
    if test -d "/var/www/$DIR-in-progress"; then
        mv /var/www/$DIR /var/www/$DIR-maintenance
        mv /var/www/$DIR-production /var/www/$DIR
        rm -R /var/www/$DIR-in-progress
    fi
fi
