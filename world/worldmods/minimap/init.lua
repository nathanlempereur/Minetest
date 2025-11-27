if rawget(_G, "ign") and ign.mesure and ign.mesure.add_map then

	local filename = minetest.get_modpath(minetest.get_current_modname()) ..
		"/overlays.json"

	local file = io.open(filename, "rb")

	if not file then
		return
	end

	local content = file:read("*all")
	file:close()
	local overlays = minetest.parse_json(content)

	if overlays == nil or type(overlays) ~= "table" then
		minetest.log("error",
			string.format("Unable to parse content of file %s.", filename))
		return
	end


	for _, overlay in ipairs(overlays) do
		ign.mesure.add_map(overlay.label, overlay.texture);
	end
end
