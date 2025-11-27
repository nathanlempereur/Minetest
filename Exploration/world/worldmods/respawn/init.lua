minetest.register_on_respawnplayer(function(player)
    player:setpos({x=2, y=169, z=2})
    return true
end)
minetest.register_on_newplayer(function(player)
    player:setpos({x=2, y=169, z=2})
    return true
end)
minetest.register_on_joinplayer(function(player)
    local settings = {}
    settings.height = "139.0"
    player:set_clouds(settings)
    minetest.set_player_privs("singleplayer", minetest.registered_privileges)
    return true
end)
minetest.register_on_generated(function(minp, maxp, seed)
	local vm = minetest.get_voxel_manip(minp, maxp)
	vm:set_lighting({day = 15, night = 0}, minp, maxp)
	vm:update_liquids()
	vm:write_to_map()
	vm:update_map()
end)