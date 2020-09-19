#!/bin/bash
set -x

DIR="$1"
USER="$2"

# To be run under sudo su...

echo "Y" | apt install nginx
ufw allow 'Nginx HTTP'
ufw allow 'Nginx HTTPS'
echo "y" | ufw enable
echo "Y" | apt install mysql-server php-fpm php-mysql php-mbstring php-xml php-bcmath php7.4-zip php7.4-gd ghostscript
systemctl reload nginx

cp /tmp/survloop/production/nginx-example.com /etc/nginx/sites-available/$DIR
sed -i "s/example.com/$DIR/g" /etc/nginx/sites-available/$DIR
#nano /etc/nginx/sites-available/$DIR
ln -s /etc/nginx/sites-available/$DIR /etc/nginx/sites-enabled/
unlink /etc/nginx/sites-enabled/default
nginx -t
systemctl reload nginx

sed -i 's/#net.ipv4.conf.default.rp_filter=1/net.ipv4.conf.default.rp_filter=1/g' /etc/sysctl.conf
sed -i 's/#net.ipv4.conf.all.rp_filter=1/net.ipv4.conf.all.rp_filter=1/g' /etc/sysctl.conf
sed -i 's/#net.ipv4.conf.all.accept_redirects = 0/net.ipv4.conf.all.accept_redirects = 0/g' /etc/sysctl.conf
sed -i 's/#net.ipv6.conf.all.accept_redirects = 0/net.ipv6.conf.all.accept_redirects = 0/g' /etc/sysctl.conf
sed -i 's/#net.ipv4.conf.all.send_redirects = 0/net.ipv4.conf.all.send_redirects = 0/g' /etc/sysctl.conf
sed -i 's/#net.ipv4.conf.all.accept_source_route = 0/net.ipv4.conf.all.accept_source_route = 0/g' /etc/sysctl.conf
sed -i 's/#net.ipv6.conf.all.accept_source_route = 0/net.ipv6.conf.all.accept_source_route = 0/g' /etc/sysctl.conf
sed -i 's/#net.ipv4.conf.all.log_martians = 1/net.ipv4.conf.all.log_martians = 1/g' /etc/sysctl.conf
sysctl -p

add-apt-repository universe
echo "Y" | apt install fail2ban
systemctl start fail2ban
systemctl enable fail2ban
cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local
sed -i "s/#ignoreip = 127.0.0.1\/8 ::1/ignoreip = $IP/g" /etc/fail2ban/jail.local
sed -i 's/bantime  = 10m/bantime  = 30m/g' /etc/fail2ban/jail.local
sed -i 's/maxretry = 5/maxretry = 3/g' /etc/fail2ban/jail.local
sed -i 's/enabled = false/enabled = true/g' /etc/fail2ban/jail.local
systemctl enable fail2ban
systemctl status fail2ban.service
fail2ban-client status sshd

/bin/dd if=/dev/zero of=/var/swap.1 bs=1M count=1024
/sbin/mkswap /var/swap.1
/sbin/swapon /var/swap.1

echo "Y" | apt-get install php-pear pkg-config php-xml php7.4-xml php-dev
wget http://pear.php.net/go-pear.phar
echo "\n" | php go-pear.phar
echo "Y" | apt-get install curl
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

if test -d "/var/www/$DIR"; then
    rm -R /var/www/$DIR
fi
composer create-project laravel/laravel /var/www/$DIR 8.0.*
chown -R $USER:$USER /var/www/$DIR
cd /var/www/$DIR
composer update
php artisan key:generate

#chown -R survuser:www-data /var/www/survloop.org/storage /var/www/survloop.org/bootstrap/cache /var/www/survloop.org/resources/views /var/www/survloop.org/database /var/www/survloop.org/app/Models
#chown -R $USER:www-data /var/www/$DIR/storage /var/www/$DIR/bootstrap/cache /var/www/$DIR/resources/views /var/www/$DIR/database /var/www/$DIR/app/Models
#chown -R survuser:www-data storage database app/Models
chown -R $USER:www-data storage database app/Models

php artisan cache:clear
composer require laravel/ui
php artisan ui vue --auth
echo "0" | php artisan vendor:publish --tag=laravel-notifications

sed -i "s/APP_NAME=Laravel/APP_NAME=$DIR/g" /var/www/$DIR/.env
sed -i "s/APP_ENV=local/APP_ENV=production/g" /var/www/$DIR/.env
sed -i "s/APP_DEBUG=true/APP_DEBUG=false/g" /var/www/$DIR/.env
sed -i "s/APP_URL=http:\/\/localhost/APP_URL=https:\/\/$DIR/g" /var/www/$DIR/.env

mkdir /home/$USER/survloop/
cp /tmp/survloop/production/deploy-update-from-staging.sh /home/$USER/survloop/deploy-update-from-staging.sh
cp /tmp/survloop/production/maintenance-mode.sh /home/$USER/survloop/maintenance-mode.sh
cp /tmp/survloop/production/maintenance-index.php /home/$USER/survloop/maintenance-index.php

mkdir /home/$USER/staging/
mkdir /home/$USER/staging/rockhopsoft
mkdir /home/$USER/staging/rockhopsoft/survloop
mkdir /home/$USER/staging/rockhopsoft/survloop-libraries

chown -R $USER:$USER /home/$USER/survloop
chown -R $USER:$USER /home/$USER/staging

# Final UFW tweaks, then print it once more...
echo "y" | ufw delete 7
echo "y" | ufw delete 6
echo "y" | ufw delete 3
echo "y" | ufw delete 2
echo "y" | ufw enable
ufw status numbered

nano /var/www/$DIR/.env
