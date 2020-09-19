#!/bin/bash
set -x

USER="$1"
IP="$2"
PORT="$3"
YUBI="$4"

apt-get update
apt-get upgrade
adduser $USER
usermod -aG sudo $USER
rsync --archive --chown=$USER:$USER ~/.ssh /home/$USER

sed -i "s/#Port 22/Port $PORT/g" /etc/ssh/sshd_config
sed -i 's/PermitRootLogin yes/PermitRootLogin no/g' /etc/ssh/sshd_config
sed -i 's/#LogLevel INFO/LogLevel VERBOSE/g' /etc/ssh/sshd_config

if [ -n "$4" ]
then
    apt install libpam-yubico -y
    echo "$USER:$YUBI" >> /etc/yubico
    sed -i 's/@include common-auth/auth required pam_yubico.so id=16 debug authfile=\/etc\/yubico/g' /etc/pam.d/sshd
    sed -i 's/ChallengeResponseAuthentication no/ChallengeResponseAuthentication yes/g' /etc/ssh/sshd_config
    sed -i 's/# Authentication:/AuthenticationMethods publickey,keyboard-interactive/g' /etc/ssh/sshd_config
    sed -i 's/UsePAM no/UsePAM yes/g' /etc/ssh/sshd_config
else
    sed -i 's/UsePAM yes/UsePAM no/g' /etc/ssh/sshd_config
fi
systemctl restart sshd

ufw default deny incoming
ufw default allow outgoing
ufw limit from $IP to any port $PORT
echo "y" | ufw enable
ufw status verbose
