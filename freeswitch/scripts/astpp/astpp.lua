-------------------------------------------------------------------------------------
-- ASTPP - Open Source VoIP Billing Solution
--
-- Copyright (C) 2016 iNextrix Technologies Pvt. Ltd.
-- Samir Doshi <samir.doshi@inextrix.com>
-- ASTPP Version 3.0 and above
-- License https://www.gnu.org/licenses/agpl-3.0.html
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU Affero General Public License as
-- published by the Free Software Foundation, either version 3 of the
-- License, or (at your option) any later version.
-- 
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU Affero General Public License for more details.
-- 
-- You should have received a copy of the GNU Affero General Public License
-- along with this program.  If not, see <http://www.gnu.org/licenses/>.
--------------------------------------------------------------------------------------

-- Define script path 
local script_path = "/usr/share/freeswitch/scripts/astpp/";

-- Load config file
dofile("/var/lib/astpp/astpp.lua");

-- Load CONSTANT file
dofile("/usr/share/freeswitch/scripts/astpp/constant.lua");

-- Load json file to decode json string
JSON = (loadfile (script_path .."lib/JSON.lua"))();

-- Load utility file 
dofile(script_path.."lib/astpp.utility.lua");

-- Include Logger file to print messages 
dofile(script_path.."lib/astpp.logger.lua");

-- Include database connection file to connect database
dofile(script_path.."lib/astpp.db.lua");

-- Call database connection
db_connect()

-- Include common functions file 
dofile(script_path.."lib/astpp.functions.lua");
config = load_conf()
--load_addon_list array
addon_list = load_addon_list()

-- Include file to build xml for fs
dofile(script_path.."scripts/astpp.xml.lua");

-- Include custom file to load custom function
dofile(script_path.."lib/astpp.custom.lua");

-- Load addons files
dirname = script_path..'lib/addons/'
f = io.popen('ls ' .. dirname)
for name in f:lines() do dofile(dirname..name..'') end

if (not params) then
	params = {}
	function params:getHeader(name)
		self.name = name;
	end
	function params:serialize(name)
		self.name = name;
	end
end

-- Values taken from SIP signalling (dialed number, caller id, From user, custom P- headers)
-- are set by the caller and end up in the generated dialplan XML. SQL values are escaped by
-- db_sql() (lib/astpp.db.lua); this filter stays as a second layer.
-- Wrap params:getHeader() so every script only sees sanitized values, and ignore the
-- voice broadcast / accountcode override headers unless the call was originated by this
-- server itself (voice broadcast, callback).
do
	local real_params = params
	local number_headers = {
		["Caller-Destination-Number"] = true,
		["Caller-Caller-ID-Number"] = true,
		["variable_effective_caller_id_number"] = true,
		["variable_sip_from_user"] = true,
		["variable_sip_h_P-cb_destination"] = true,
		["variable_sip_h_P-Accountcode"] = true,
		["variable_sip_h_P-Voice_broadcast_type"] = true,
		["Hunt-Username"] = true,
	}
	local name_headers = {
		["Caller-Caller-ID-Name"] = true,
		["variable_effective_caller_id_name"] = true,
	}
	local local_only_headers = {
		["variable_sip_h_P-Voice_broadcast"] = true,
		["variable_sip_h_P-Voice_broadcast_type"] = true,
		["variable_sip_h_P-cb_destination"] = true,
		["variable_sip_h_P-Accountcode"] = true,
	}
	local function raw_header(name)
		return real_params:getHeader(name)
	end
	local net_addr = raw_header("Hunt-Network-Addr") or raw_header("variable_sip_network_ip") or ""
	local local_addr = raw_header("variable_sip_local_network_addr") or ""
	local is_local_origin = (net_addr == "127.0.0.1" or net_addr == "::1" or (net_addr ~= "" and net_addr == local_addr))

	params = {}
	function params:getHeader(name)
		local value = raw_header(name)
		if value == nil then return nil end
		if local_only_headers[name] and not is_local_origin then
			return nil
		end
		if number_headers[name] then
			return (string.gsub(value, "[^%w%+%*#%-%._@]", ""))
		end
		if name_headers[name] then
			return (string.gsub(value, "[%c'\"\\`;<>&]", ""))
		end
		return value
	end
	function params:serialize(...)
		return real_params:serialize(...)
	end
end

if (config['debug']==2) then
    -- print all params 
    if (params:serialize() ~= nil) then
    	Logger.notice ("[xml_handler] Params:\n" .. params:serialize())
    end	

    for param_key,param_value in pairs(XML_REQUEST) do --pseudocode
    	Logger.info ("[xml_REQUEST] "..param_key..": " .. param_value)
    end
end

dofile(script_path.."scripts/astpp."..XML_REQUEST["section"]..".lua")

