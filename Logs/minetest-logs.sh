#!/bin/bash

LOG_DIR="/var/log/minetest-logs"
mkdir -p "$LOG_DIR"

CONTAINERS=(
	"minetest-classique"
	"minetest-creatif"
	"minetest-exploration"
	"minetest-survie"
	"minetest-perso"
)

for container in "${CONTAINER]}"; do
	if lxc-info -n "$container" -s 2>/dev/null | grep -q "RUNNING"; then
		lxc-attach -n "$container" --  /bin/bash -c "cat /var/log/minetest/minetest.log 2>/dev/null" > "$LOG_DIR/${container}.log"
	fi
done

chmod 744 -R "$LOG_DIR"
