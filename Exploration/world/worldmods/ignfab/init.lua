minetest.register_node("ignfab:ignlogo", {
	description = "IGN logo",
	drawtype = "signlike",
	walkable = false,
	tiles = {"logo-ign.png"},
	wield_image =  "logo-ign.png",
	inventory_image =  "logo-ign.png",
	paramtype = "light",
	paramtype2 = "wallmounted",
	selection_box = {
		type = "wallmounted",
	},
	groups = {oddly_breakable_by_hand = 3, not_in_creative_inventory = 1}, 
});

minetest.register_node("ignfab:ignfablogo", {
	description = "IGNfab logo",
	drawtype = "signlike",
	walkable = false,
	tiles = {"logo-ignfab.png"},
	wield_image =  "logo-ignfab.png",
	inventory_image =  "logo-ignfab.png",
	paramtype = "light",
	paramtype2 = "wallmounted",
	selection_box = {
		type = "wallmounted",
	},
	groups = {oddly_breakable_by_hand = 3, not_in_creative_inventory = 1}, 
});



minetest.register_node("ignfab:overview", {
	description = "IGN Map Overview",
	visual_scale = 3.0,
	drawtype = "signlike",
	walkable = false,
	tiles = {"overview.png"},
	wield_image =  "overview.png",
	inventory_image =  "overview.png",
	paramtype = "light",
	paramtype2 = "wallmounted",
	selection_box = {
		type = "wallmounted",
	},
	groups = {oddly_breakable_by_hand = 3}, 
});

minetest.register_lbm({
	name = "ignfab:overview_rotation",
	nodenames = {"ignfab:overview","ignfab:ignlogo","ignfab:ignfablogo","ignfab:message"},
	run_at_every_load = true,
	action = function (pos,node)
		minetest.swap_node({x=2, y=171, z=5}, {name="ignfab:overview",param2=4})
		minetest.swap_node({x=4, y=172, z=5}, {name="ignfab:ignlogo",param2=4})
		--minetest.swap_node({x=4, y=170, z=5}, {name="ignfab:ignfablogo",param2=4})
		--minetest.set_node(pos, {name=node.name,param1=0,param2=4})
		--minetest.swap_node({x=4, y=171, z=5}, {name="ignfab:message",param2=4})
	end,
})

local data_buffer = {}

