#!/bin/bash
set -x

adduser $1
usermod -aG sudo $1
ufw allow OpenSSH
ufw enable
rsync --archive --chown=$1:$1 ~/.ssh /home/$1

mkdir /home/$1/survloop/
cp /tmp/survloop/maintenance-mode.sh /home/$1/survloop/maintenance-mode.sh
cp /tmp/survloop/deploy-update-from-staging.sh /home/$1/survloop/deploy-update-from-staging.sh
cp /tmp/survloop/index.php /home/$1/survloop/index.php

exit
