#!/bin/bash
set -x

DIR="$1"
USER="$2"
IP="$3"
PORT="$4"

# To be run under sudo su...

sed -i "s/#Port 22/Port $PORT/g" /etc/ssh/sshd_config
sed -i 's/PermitRootLogin yes/PermitRootLogin no/g' /etc/ssh/sshd_config
sed -i 's/UsePAM yes/UsePAM no/g' /etc/ssh/sshd_config
systemctl restart sshd

ufw default deny incoming
ufw default allow outgoing
ufw limit from $IP to any port $PORT
ufw allow http
ufw allow https
echo "y" | ufw enable
ufw status numbered

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

echo "Y" | apt install nginx
#ufw allow 'Nginx HTTP'
echo "Y" | apt install mysql-server php-fpm php-mysql php-mbstring php-xml php-bcmath php7.4-zip php7.4-gd ghostscript
systemctl reload nginx


cp /tmp/survloop/example.com /etc/nginx/sites-available/$1
sed -i "s/example.com/$DIR/g" /etc/nginx/sites-available/$1
#nano /etc/nginx/sites-available/$1
ln -s /etc/nginx/sites-available/$1 /etc/nginx/sites-enabled/
unlink /etc/nginx/sites-enabled/default
nginx -t
systemctl reload nginx

/bin/dd if=/dev/zero of=/var/swap.1 bs=1M count=1024
/sbin/mkswap /var/swap.1
/sbin/swapon /var/swap.1

echo "Y" | apt-get install php-pear pkg-config php-xml php7.4-xml php-dev
wget http://pear.php.net/go-pear.phar
echo '\n' | php go-pear.phar
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

nano /var/www/$DIR/.env

