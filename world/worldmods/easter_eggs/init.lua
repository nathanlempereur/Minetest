
minetest.register_node("easter_eggs:gold_egg", {
    description = "Gold egg",
    drawtype = "mesh",
    mesh = "easter_eggs_gold_egg.obj",
    tiles = { "easter_eggs_gold.png" },
    groups = { oddly_breakable_by_hand = 3 },
    paramtype = "light",
    inventory_image = "easter_eggs_gold_egg.png",
    wield_image = "easter_eggs_gold_egg.png",
    drop = "easter_eggs:gold_egg"
})


minetest.register_craft({
    output = "default:goldblock",
    recipe = {
        { "easter_eggs:gold_egg", "easter_eggs:gold_egg", "easter_eggs:gold_egg" },
        { "easter_eggs:gold_egg", "easter_eggs:gold_egg", "easter_eggs:gold_egg" },
        { "easter_eggs:gold_egg", "easter_eggs:gold_egg", "easter_eggs:gold_egg" }
    }
})

