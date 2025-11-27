#!/bin/bash

for i in $(lxc-ls); do 
	lxc-start "$i"
	lxc-attach "$i" -- /bin/bash -c "ipset create Deny hash:ip"
	lxc-attach "$i" -- /bin/bash -c "iptables -A INPUT -p udp --dport 30000 -m set --match-set Deny src -j DROP"
	for el in $(cat /root/IPban.csv); do
		lxc-attach "$i" -- /bin/bash -c "ipset add Deny $el"
	done
done
