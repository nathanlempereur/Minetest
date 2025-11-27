#!/bin/bash 

read -p "Saisir l'ip à débannir : " IP
for i in $(lxc-ls);do
	lxc-attach "$i" -- /bin/bash -c "ipset del Deny $IP"
	fail2ban-client set $i unbanip $IP
done
systemctl restart fail2ban
