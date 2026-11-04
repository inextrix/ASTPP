#!/usr/bin/lua
-- LOGGING
LOGLEVEL = "notice"
-- PROGNAME
PROGNAME = "Astpp_SIP-Devices"
-- Global functions
local sound_path = "/usr/share/freeswitch/sounds/en/us/callie/"
-- Load config file
dofile("/var/lib/astpp/astpp.lua");
local script_path="";
if(SCRIPT_PATH ~= nil and SCRIPT_PATH ~='')then
	script_path = SCRIPT_PATH;
else
	script_path = "/usr/share/freeswitch/scripts/astpp/";
end
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
dofile(script_path.."lib/astpp.callingcard.functions.lua")

addon_path = script_path.."../addons.lua"
dofile(addon_path);

config = load_conf()

function logger(message)
  freeswitch.console_log(LOGLEVEL,"["..PROGNAME.."] "..message.."\n")
end
if session:ready() then
	on_busy_flag = session:getVariable("on_busy_flag");
	on_busy_destination = session:getVariable("on_busy_destination");
	no_answer_flag = session:getVariable("no_answer_flag");
	no_answer_destination = session:getVariable("no_answer_destination");
	not_register_flag = session:getVariable("not_register_flag");
	not_register_destination = session:getVariable("not_register_destination");
	opensips_flag = session:getVariable("opensips_flag");
	opensips_domain = session:getVariable("opensips_domain");
	leg_timeout = session:getVariable("leg_timeout");
	sip_destination_number = session:getVariable("sip_destination_number");
	last_disposition = session:getVariable("originate_disposition");
	last_sip_rates = session:getVariable("origination_rates");
	variable_sip_to_host = session:getVariable("variable_sip_to_host");
	variable_sip_to_port = session:getVariable("variable_sip_to_port");
	userinfo_id = session:getVariable("userinfo_id");
	did_number = session:getVariable("did_number");

	user_domain = session:getVariable("user_domain");
	logger("This Is Last Dispositions "..last_disposition.." This Is SIP Number : "..sip_destination_number)	
	local routing_destination = ''
	local permission_flag = ''
	if last_disposition == "USER_NOT_REGISTERED" or last_disposition == "UNALLOCATED_NUMBER" then
		routing_destination = not_register_destination
		permission_flag = not_register_flag
	end
	if last_disposition == "USER_BUSY" then
		routing_destination = on_busy_destination
		permission_flag = on_busy_flag
	end
	if last_disposition == "NO_USER_RESPONSE" or last_disposition == "NO_ANSWER" or last_disposition == "SUBSCRIBER_ABSENT"  or last_disposition == "NORMAL_TEMPORARY_FAILURE"  or last_disposition == "ALLOTTED_TIMEOUT" then
		routing_destination = no_answer_destination
		permission_flag = no_answer_flag
	end

	if (permission_flag=='' and sip_destination_number ~= '') then
		routing_destination = sip_destination_number
		permission_flag = 1
	end

	if(routing_destination ~= '' and permission_flag ~= '' and tonumber(permission_flag) == 0) then
		bridge = "{ignore_early_media=true,sip_h_P-did_number="..did_number..",sip_h_P-call_type='custom_forward',sip_h_P-Accountcode="..userinfo_id.."}user/"..routing_destination.."@"..variable_sip_to_host..((variable_sip_to_port and variable_sip_to_port ~= "") and ":"..variable_sip_to_port or "")
		session:execute("bridge", bridge)
	end
	if last_disposition ~= "SUCCESS" then
	 	session:execute("answer");

	 logger("Dialed User For Voicemail :" .. sip_destination_number );
	 session:execute("voicemail", "default "..variable_sip_to_host.." "..sip_destination_number);

	end

end
