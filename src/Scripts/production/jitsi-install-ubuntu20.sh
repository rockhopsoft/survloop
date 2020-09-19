#!/bin/bash
set -x

DOMAIN="$1"
IP="$2"
EMAIL="$3"

# To be run under sudo su...

apt-add-repository universe 
apt-get update
apt-get upgrade

sed -i 's/#net.ipv4.conf.default.rp_filter=1/net.ipv4.conf.default.rp_filter=1/g' /etc/sysctl.conf
sed -i 's/#net.ipv4.conf.all.rp_filter=1/net.ipv4.conf.all.rp_filter=1/g' /etc/sysctl.conf
sed -i 's/#net.ipv4.conf.all.accept_redirects = 0/net.ipv4.conf.all.accept_redirects = 0/g' /etc/sysctl.conf
sed -i 's/#net.ipv6.conf.all.accept_redirects = 0/net.ipv6.conf.all.accept_redirects = 0/g' /etc/sysctl.conf
sed -i 's/#net.ipv4.conf.all.send_redirects = 0/net.ipv4.conf.all.send_redirects = 0/g' /etc/sysctl.conf
sed -i 's/#net.ipv4.conf.all.accept_source_route = 0/net.ipv4.conf.all.accept_source_route = 0/g' /etc/sysctl.conf
sed -i 's/#net.ipv6.conf.all.accept_source_route = 0/net.ipv6.conf.all.accept_source_route = 0/g' /etc/sysctl.conf
sed -i 's/#net.ipv4.conf.all.log_martians = 1/net.ipv4.conf.all.log_martians = 1/g' /etc/sysctl.conf
sysctl -p

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



# https://www.vultr.com/docs/install-jitsi-meet-on-ubuntu-20-04-lts

apt-get install gnupg2 apt-transport-https
apt-get update
apt-get upgrade

ufw allow OpenSSH
ufw allow http
ufw allow https
ufw allow in 10000:20000/udp
echo "y" | ufw enable

apt install -y openjdk-8-jre-headless
java -version
echo "JAVA_HOME=$(readlink -f /usr/bin/java | sed "s:bin/java::")" | sudo tee -a /etc/profile
source /etc/profile

apt install -y nginx
systemctl start nginx.service
systemctl enable nginx.service

cd /tmp
wget -qO - https://download.jitsi.org/jitsi-key.gpg.key | sudo apt-key add -
echo "deb https://download.jitsi.org stable/"  | sudo tee -a /etc/apt/sources.list.d/jitsi-stable.list
apt update
apt install -y jitsi-meet

/usr/share/jitsi-meet/scripts/install-letsencrypt-cert.sh













apt install certbot
certbot certonly --standalone --preferred-challenges http-01 --agree-tos --no-eff-email -m $EMAIL -d $DOMAIN



hostnamectl set-hostname $DOMAIN

sed -i "s/127.0.1.1/$IP/g" /etc/hosts

curl https://download.jitsi.org/jitsi-key.gpg.key | sh -c 'gpg --dearmor > /usr/share/keyrings/jitsi-keyring.gpg'
echo 'deb [signed-by=/usr/share/keyrings/jitsi-keyring.gpg] https://download.jitsi.org stable/' | tee /etc/apt/sources.list.d/jitsi-stable.list > /dev/null
apt update

#ufw allow 80/tcp
#ufw allow 443/tcp
ufw allow 4443/tcp
ufw allow 10000/udp
echo "y" | ufw enable
ufw status verbose

echo "Y" | apt install jitsi-meet
