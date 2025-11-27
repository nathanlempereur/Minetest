#!/bin/bash

for c in $(lxc-ls); do
	a=$(fail2ban-client status "$c" | grep "Banned IP list")
	for ip in $(echo "$a" | tr -d '`' | tr -d "-" | tr -d "Banned" | tr -d "IP" | tr -d "list:" );do
		for i in $(lxc-ls); do
			lxc-attach -n "$i" -- /bin/bash -c "ipset add Deny $ip" > /dev/null 2>&1
		done
		echo "$ip" >> /root/IPban.csv
		fail2ban-client set "$c" unbanip "$ip"

	done
done


