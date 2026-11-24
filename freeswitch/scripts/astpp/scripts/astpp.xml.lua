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


function xml_escape(value)
	local XML_ESCAPE_MAP = { ["&"] = "&amp;", ["<"] = "&lt;", [">"] = "&gt;", ['"'] = "&quot;", ["'"] = "&apos;" }
	if value == nil then return "" end
	return (string.gsub(tostring(value), "[&<>\"']", XML_ESCAPE_MAP))
end

function sip_clean(value)
	if value == nil then return "" end
	return (string.gsub(tostring(value), "[%$%{%}\\%c]", ""))
end

function xml_sip(value)
	return xml_escape(sip_clean(value))
end

-- Dialplan header part
function freeswitch_xml_header(xml,destination_number,accountcode,maxlength,call_direction,accountname,xml_user_rates,customer_userinfo,config,xml_did_rates,reseller_cc_limit,callerid_array,original_destination_number)
	local callstart = os.date("!%Y-%m-%d %H:%M:%S")

	table.insert(xml, [[<?xml version="1.0" encoding="UTF-8" standalone="no"?>]]);
	table.insert(xml, [[<document type="freeswitch/xml">]]);
	table.insert(xml, [[<section name="dialplan" description="ASTPP Dialplan">]]);
	table.insert(xml, [[<context name="]]..xml_escape(params:getHeader("Caller-Context"))..[[">]]);
	table.insert(xml, [[<extension name="]]..xml_sip(destination_number)..[[">]]);
	table.insert(xml, [[<condition field="destination_number" expression="]]..xml_escape(plus_destination_number(sip_clean(params:getHeader("Caller-Destination-Number"))))..[[">]]);
	table.insert(xml, [[<action application="set" data="effective_destination_number=]]..xml_escape(plus_destination_number(sip_clean(original_destination_number)))..[["/>]]);
	Logger.info("maxlength::::::::: "..maxlength);
	table.insert(xml, [[<action application="set" data="bridge_pre_execute_bleg_app=sched_hangup"/>]]);
	table.insert(xml, [[<action application="set" data="bridge_pre_execute_bleg_data=+]]..((maxlength) * 60)..[[ normal_clearing"/>]]);
     
	table.insert(xml, [[<action application="set" data="callstart=]]..callstart..[["/>]]);
	table.insert(xml, [[<action application="set" data="hangup_after_bridge=true"/>]]);

	-- Made it configurable if someone want to set continue_on_fail for specific disposition	
	local continue_on_fail = '!USER_BUSY'
	if (config['continue_on_fail'] ~= nil) then
		continue_on_fail = config['continue_on_fail']
	end

	table.insert(xml, [[<action application="set" data="continue_on_fail=TRUE"/>]]);  
	--table.insert(xml, [[<action application="set" data="ignore_early_media=true"/>]]);       

	table.insert(xml, [[<action application="set" data="account_id=]]..xml_escape(customer_userinfo['id'])..[["/>]]);
	table.insert(xml, [[<action application="set" data="parent_id=]]..xml_escape(customer_userinfo['reseller_id'])..[["/>]]);
	table.insert(xml, [[<action application="set" data="entity_id=]]..xml_escape(customer_userinfo['type'])..[["/>]]);
	table.insert(xml, [[<action application="set" data="call_processed=internal"/>]]);
	table.insert(xml, [[<action application="set" data="call_direction=]]..xml_escape(call_direction)..[["/>]]);
	table.insert(xml, [[<action application="set" data="accountname=]]..xml_sip(accountname)..[["/>]]);
	if (package_id and tonumber(package_id) > 0) then
		table.insert(xml, [[<action application="set" data="package_id=]]..xml_escape(package_id)..[["/>]]);
	end
	if (call_direction == "inbound" and tonumber(config['inbound_fax']) > 0) then
		table.insert(xml, [[<action application="export" data="t38_passthru=true"/>]]);    
		table.insert(xml, [[<action application="set" data="fax_enable_t38=true"/>]]);    
		table.insert(xml, [[<action application="set" data="fax_enable_t38_request=true"/>]]);    
	elseif (call_direction == "outbound" and tonumber(config['outbound_fax']) > 0) then
		table.insert(xml, [[<action application="export" data="t38_passthru=true"/>]]);    
		table.insert(xml, [[<action application="set" data="fax_enable_t38=true"/>]]);    
		table.insert(xml, [[<action application="set" data="fax_enable_t38_request=true"/>]]);    
	end
	--custom outbound        
	if custom_outbound then custom_outbound(xml) end 

	if(tonumber(config['balance_announce']) == 0) then
		table.insert(xml, [[<action application="sleep" data="1000"/>]]);
		table.insert(xml, [[<action application="playback" data="/usr/share/freeswitch/sounds/en/us/callie/astpp-this-card-has-a-balance-of.wav"/>]]);
		local tmp_prefix=''
		if get_international_balance_prefix then tmp_prefix = get_international_balance_prefix(customer_userinfo) end 	

		customer_balance = tonumber(customer_userinfo['posttoexternal']) == 1 and tonumber(customer_userinfo[tmp_prefix..'credit_limit'])+(tonumber(customer_userinfo[tmp_prefix..'balance'])*(-1)) or tonumber(customer_userinfo[tmp_prefix..'balance'])

		table.insert(xml, [[<action application="say" data="en CURRENCY PRONOUNCED ]].. customer_balance..[["/>]]);

	end
	if(tonumber(config['minutes_announce']) == 0) then
		table.insert(xml, [[<action application="sleep" data="500"/>]]);
		table.insert(xml, [[<action application="playback" data="/usr/share/freeswitch/sounds/en/us/callie/astpp-this-call-will-last.wav"/>]]);
		table.insert(xml, [[<action application="say" data="en NUMBER PRONOUNCED ]].. math.floor(maxlength)..[["/>]]);
		table.insert(xml, [[<action application="playback" data="/usr/share/freeswitch/sounds/en/us/callie/astpp-minute.wav"/>]]);       
	end
    
	if (call_direction == "inbound") then 
		table.insert(xml, [[<action application="set" data="origination_rates_did=]]..xml_escape(xml_user_rates)..[["/>]]);
	else
		table.insert(xml, [[<action application="set" data="origination_rates=]]..xml_escape(xml_user_rates)..[["/>]]);
	end

	if(xml_did_rates ~= nil and xml_did_rates ~= '') then
		table.insert(xml, [[<action application="set" data="origination_rates=]]..xml_escape(xml_did_rates)..[["/>]]);
	end

	-- Set original caller id for CDRS
    if (callerid_array['original_cid_name'] ~= '' and callerid_array['original_cid_name'] ~= '<null>')  then
            table.insert(xml, [[<action application="set" data="original_caller_id_name=]]..xml_sip(callerid_array['original_cid_name'])..[["/>]]);
    end
    if (callerid_array['cid_number'] ~= '' and callerid_array['cid_number'] ~= '<null>')  then
            table.insert(xml, [[<action application="set" data="original_caller_id_number=]]..xml_sip(callerid_array['original_cid_number'])..[["/>]]);
    end

	-- Set max channel limit for user if > 0
	if(tonumber(customer_userinfo['maxchannels']) > 0) then
	    	table.insert(xml, [[<action application="limit" data="db ]]..xml_sip(accountcode)..[[ user_]]..xml_sip(accountcode)..[[ ]]..xml_escape(customer_userinfo['maxchannels'])..[[ !SWITCH_CONGESTION"/>]]);
	end

	-- Set CPS limit for user if > 0
	if (tonumber(customer_userinfo['cps']) > 0) then
		table.insert(xml, [[<action application="limit" data="hash CPS_]]..xml_sip(accountcode)..[[ CPS_user_]]..xml_sip(accountcode)..[[ ]]..xml_escape(customer_userinfo['cps'])..[[/1 !SWITCH_CONGESTION"/>]]);
	end

    -- Set max channel limit for resellers
    if (reseller_cc_limit ~= nil) then
        table.insert(xml, reseller_cc_limit);
    end   

	if(tonumber(customer_userinfo['is_recording']) == 0) then 
		table.insert(xml, [[<action application="export" data="is_recording=1"/>]]);
		table.insert(xml, [[<action application="export" data="media_bug_answer_req=true"/>]]);
		table.insert(xml, [[<action application="export" data="RECORD_STEREO=true"/>]]);
		table.insert(xml, [[<action application="export" data="record_sample_rate=8000"/>]]);
		table.insert(xml, [[<action application="export" data="execute_on_answer=record_session $${recordings_dir}/${uuid}.wav"/>]]);
	end
	return xml
end

function urlencode(str)
	if (str) then
	str = string.gsub (str, "\n", "\r\n")
	str = string.gsub (str, "([^%w ])",
	function (c) return string.format ("%%%02X", string.byte(c)) end)
		str = string.gsub (str, " ", "+")
	end
	return str    
end

-- Dialplan footer part
function freeswitch_xml_footer(xml)
	table.insert(xml,[[</condition>]]);
	table.insert(xml,[[</extension>]]);
	table.insert(xml,[[</context>]]);
	table.insert(xml,[[</section>]]);
	table.insert(xml,[[</document>]]);
	return xml
end

-- Dialplan for outbound calls
function freeswitch_xml_outbound(xml,destination_number,outbound_info,callerid_array,rate_group_id,old_trunk_id,force_outbound_routes,rategroup_type,livecall_data)

	--Dhaval trunk localization ASTPPENT-675
	local tr_localization_tunk=nil
	tr_localization_tunk = get_localization(outbound_info['trunk_id'],'Trunk')
	if (tr_localization_tunk ~= nil) then
		tr_localization_tunk['out_caller_id_terminate'] = tr_localization_tunk['out_caller_id_terminate']:gsub(" ", "")
		callerid_array['cid_name'] = do_number_translation(tr_localization_tunk['out_caller_id_terminate'],callerid_array['original_cid_name'])
		callerid_array['cid_number'] = do_number_translation(tr_localization_tunk['out_caller_id_terminate'],callerid_array['original_cid_number'])
		tr_localization_tunk['number_terminate'] = tr_localization_tunk['number_terminate']:gsub(" ", "")
		destination_number = do_number_translation(tr_localization_tunk['number_terminate'],destination_number)
	end
	--Dhaval trunk localization end
	local temp_destination_number = destination_number
	local tr_localization=nil
	tr_localization = get_localization(outbound_info['provider_id'],'T')
	
	if (tr_localization ~= nil) then
		tr_localization['out_caller_id_terminate'] = tr_localization['out_caller_id_terminate']:gsub(" ", "")
		-------------- Caller Id translation ---------	 
		callerid_array['cid_name'] = do_number_translation(tr_localization['out_caller_id_terminate'],callerid_array['cid_name'])
		callerid_array['cid_number'] = do_number_translation(tr_localization['out_caller_id_terminate'],callerid_array['cid_number'])    
		xml = freeswitch_xml_callerid(xml,callerid_array)	    	   	    
    	----------------------------------------------------------------------

    	-------------- Destination number translation ---------
		tr_localization['number_terminate'] = tr_localization['number_terminate']:gsub(" ", "")
		temp_destination_number = do_number_translation(tr_localization['number_terminate'],destination_number)
		-----------------------------------
		
	end
	-- ASTPPCOM-1382 Ashish start
	xml = freeswitch_xml_callerid(xml,callerid_array)
	-- ASTPPCOM-1382 Ashish end
	if(outbound_info['prepend'] ~= '' or outbound_info['strip'] ~= '') then

        if (outbound_info['prepend'] == '') then 
            outbound_info['prepend'] = '*'                        
        end

        if (outbound_info['strip'] == '') then 
            outbound_info['strip'] = '*'
        end

		temp_destination_number = do_number_translation(outbound_info['strip'].."/"..outbound_info['prepend'],temp_destination_number)
	end
    
	xml_termiantion_rates= "ID:"..outbound_info['outbound_route_id'].."|CODE:"..outbound_info['pattern'].."|DESTINATION:"..outbound_info['comment'].."|CONNECTIONCOST:"..outbound_info['connectcost'].."|INCLUDEDSECONDS:"..outbound_info['includedseconds'].."|COST:"..outbound_info['cost'].."|INC:"..outbound_info['inc'].."|INITIALBLOCK:"..outbound_info['init_inc'].."|TRUNK:"..outbound_info['trunk_id'].."|PROVIDER:"..outbound_info['provider_id'];
	-- ASTPPCOM-982 Ashish start
	if(params:getHeader("variable_sip_h_P-Voice_broadcast") == 'true')then
		local sip_user = params:getHeader("variable_sip_h_P-Voice_broadcast_type")
		table.insert(xml, [[<action application="set" data="calltype=BROADCAST"/>]]);
		table.insert(xml, [[<action application="set" data="sip_user=]]..xml_sip(sip_user)..[["/>]]);
	else
		table.insert(xml, [[<action application="set" data="calltype=STANDARD"/>]]);
	end
	-- ASTPPCOM-982 Ashish End
	table.insert(xml, [[<action application="set" data="termination_rates=]]..xml_escape(xml_termiantion_rates)..[["/>]]);
	table.insert(xml, [[<action application="set" data="trunk_id=]]..xml_escape(outbound_info['trunk_id'])..[["/>]]);
	table.insert(xml, [[<action application="set" data="provider_id=]]..xml_escape(outbound_info['provider_id'])..[["/>]]);
	table.insert(xml, [[<action application="set" data="rate_flag=]]..xml_escape(rategroup_type)..[["/>]]);
	table.insert(xml, [[<action application="set" data="force_trunk_flag=]]..xml_escape(force_outbound_routes)..[["/>]]);
    table.insert(xml, [[<action application="export" data="presence_data=trunk_id=]]..xml_escape(outbound_info['trunk_id'])..[["/>]])
    table.insert(xml, [[<action application="set" data="intcall=]]..(outbound_info['intcall'] and 1 or 0)..[["/>]])

	-- Check if is there any gateway configuration params available for it.
	if (outbound_info['dialplan_variable'] ~= '') then 
		Logger.info(" ".. outbound_info['dialplan_variable']);
		local dialplan_variable = split(outbound_info['dialplan_variable'],",")      
		for dialplan_variable_key,dialplan_variable_value in pairs(dialplan_variable) do
			local dialplan_variable_data = split(dialplan_variable_value,"=")  
			Logger.debug("[GATEWAY VARIABLE ] : "..dialplan_variable_data[1] );
			if( dialplan_variable_data[1] ~= nil and dialplan_variable_data[2] ~= nil) then
				table.insert(xml, [[<action application="set" data="]]..xml_escape(dialplan_variable_data[1].."="..dialplan_variable_data[2])..[["/>]]);
			end
		end             
	end
	----------------------- END Gateway configuraiton -------------------------------
	-- Set force codec if configured
	
	--~ livecall_data = livecall_data.."|||"..outbound_info['trunk_name'].." // "..outbound_info['pattern'].." // "..outbound_info['comment'].." // "..outbound_info['cost']
	
	if(outbound_info['trunk_id']~=nil) then
		livecall_data = livecall_data.."|||"..outbound_info['trunk_name'].." // "..outbound_info['pattern'].." // "..outbound_info['comment'].." // "..outbound_info['cost'].." // trunk_id="..outbound_info['trunk_id']
	else
		livecall_data = livecall_data.."|||"..outbound_info['trunk_name'].." // "..outbound_info['pattern'].." // "..outbound_info['comment'].." // "..outbound_info['cost']
	end
	
    table.insert(xml, [[<action application="export" data="presence_data=]]..xml_sip(livecall_data)..[[|||STD"/>]])
	
    chan_var = "leg_timeout="..outbound_info['leg_timeout']
    --HP:ASTPPCOM-944
    p_id_var = "{sip_cid_type="..outbound_info['sip_cid_type'].."}"
    if (outbound_info['codec'] ~= '') then
            chan_var = chan_var..",absolute_codec_string=".."^^:"..outbound_info['codec']:gsub("%,", ":")
     --//HP: ASTPPCOM-945
     else
	if(params:getHeader('variable_sip_from_user') and params:getHeader('variable_sip_from_user') ~= "")then
		local sip_codec = get_sip_codec(params:getHeader('variable_sip_from_user'))
		if(sip_codec and sip_codec ~= "")then
			Logger.warning("[XML] sip_codec : "..sip_codec) --//HP: ASTPPENT-4002
			chan_var = chan_var..",absolute_codec_string=".."^^:"..sip_codec:gsub("%,", ":")
		end
	end
	--//HP: ASTPPCOM-945 END
            
    end            

	-- Set CPS limit for user if > 0
	if (tonumber(outbound_info['cps']) ~=nil and tonumber(outbound_info['cps']) > 0) then
		table.insert(xml, [[<action application="limit" data="hash CPS_]]..xml_escape(outbound_info['trunk_id'])..[[ CPS_trunk_]]..xml_escape(outbound_info['trunk_id'])..[[ ]]..xml_escape(outbound_info['cps'])..[[/1 !SWITCH_CONGESTION"/>]]);
	end

	local bridge_vars = xml_escape(p_id_var.."["..chan_var.."]")
	local bridge_number = xml_sip(temp_destination_number)
	if(tonumber(outbound_info['maxchannels']) > 0) then
    --HP:ASTPPCOM-944
		table.insert(xml, [[<action application="limit_execute" data="db ]]..xml_escape(outbound_info['path'])..[[ gw_]]..xml_escape(outbound_info['path'])..[[ ]]..xml_escape(outbound_info['maxchannels'])..[[ bridge ]]..bridge_vars..[[sofia/gateway/]]..xml_escape(outbound_info['path'])..[[/]]..bridge_number..[["/>]]);
	else
    --HP:ASTPPCOM-944
		table.insert(xml, [[<action application="bridge" data="]]..bridge_vars..[[sofia/gateway/]]..xml_escape(outbound_info['path'])..[[/]]..bridge_number..[["/>]]);
	end
    --HP:ASTPPCOM-944
	if(outbound_info['path1'] ~= '' and outbound_info['path1'] ~= outbound_info['path']) then
		table.insert(xml, [[<action application="bridge" data="]]..bridge_vars..[[sofia/gateway/]]..xml_escape(outbound_info['path1'])..[[/]]..bridge_number..[["/>]]);
	end

	if(outbound_info['path2'] ~= '' and outbound_info['path2'] ~= outbound_info['path'] and outbound_info['path2'] ~= outbound_info['path1']) then
    --HP:ASTPPCOM-944
		table.insert(xml, [[<action application="bridge" data="]]..bridge_vars..[[sofia/gateway/]]..xml_escape(outbound_info['path2'])..[[/]]..bridge_number..[["/>]]);
	end
    return xml
end

-- Dialplan for inbound calls
function freeswitch_xml_inbound(xml,didinfo,userinfo,config,xml_did_rates,callerid_array,livecall_data)
	local is_local_extension = "0"
	callerid_array['cid_name'] = do_number_translation(didinfo['did_cid_translation'],callerid_array['cid_name'])
	callerid_array['cid_number'] = do_number_translation(didinfo['did_cid_translation'],callerid_array['cid_number'])
	--if(tonumber(didinfo['maxchannels']) > 0) then    		
	--    	table.insert(xml, [[<action application="limit" data="db ]]..didinfo['accountid']..[[ user_]]..didinfo['accountid']..[[ ]]..didinfo['maxchannels']..[[ !SWITCH_CONGESTION"/>]]);
	--end
	if (tonumber(userinfo['localization_id']) > 0 and or_localization and or_localization['in_caller_id_originate'] ~= nil) then     
		callerid_array['cid_name'] = do_number_translation(or_localization['in_caller_id_originate'],callerid_array['cid_name'])
		callerid_array['cid_number'] = do_number_translation(or_localization['in_caller_id_originate'],callerid_array['cid_number'])
	end   
	xml = freeswitch_xml_callerid(xml,callerid_array)	    	       
	table.insert(xml, [[<action application="set" data="receiver_accid=]]..xml_escape(didinfo['accountid'])..[["/>]]);
	if(tonumber(didinfo['maxchannels']) > 0) then
	    table.insert(xml, [[<action application="limit" data="db ]]..xml_sip(destination_number)..[[ did_]]..xml_sip(destination_number)..[[ ]]..xml_escape(didinfo['maxchannels'])..[[ !SWITCH_CONGESTION"/>]]);
	end
	if callerid_lookup_dialplan then callerid_lookup_dialplan(xml,didinfo) end
	table.insert(xml, [[<action application="export" data="presence_data=]]..xml_sip(livecall_data)..[[||||||DID"/>]])
	table.insert(xml, [[<action application="export" data="call_type=]]..xml_escape(didinfo['call_type'])..[["/>]])
	local custom_function_name = "custom_inbound_"..didinfo['call_type']
	Logger.debug("custom_function_name:::::::::::::::::::::::::" .. custom_function_name)
	_G[custom_function_name](xml,didinfo,userinfo,config,xml_did_rates,callerid_array,livecall_data) -- calls function from the global namespace
	return xml
end

function custom_inbound_0(xml,didinfo,userinfo,config,xml_did_rates,callerid_array,livecall_data)
	is_local_extension = "1"
        local bridge_str = ""
        local common_chan_var = ""
	local destination_str = {}
	string.gsub(didinfo['extensions'], "([^,|]+)", function(value) destination_str[#destination_str + 1] =     value;  end);
	table.insert(xml, [[<action application="set" data="calltype=DID-LOCAL"/>]]);  
	local deli_str = {}
	string.gsub(didinfo['extensions'], "([,|]+)", function(value) deli_str[#deli_str + 1] =     value;  end);           
		for i = 1, #destination_str do
			if notify then notify(xml,destination_str[i]) end
			--//HP: ASTPPCOM-945
			local sip_codec = get_sip_codec(destination_str[i])
			local did_local_chan = ""
			if(sip_codec and sip_codec ~= "")then
				Logger.warning("[XML]  did_local_sip_codec : "..sip_codec) --//HP: ASTPPENT-4002
				did_local_chan = ",absolute_codec_string=".."^^:"..sip_codec:gsub("%,", ":")
			end

			bridge_str = bridge_str..xml_escape("[leg_timeout="..didinfo['leg_timeout']..did_local_chan.."]user/")..xml_sip(destination_str[i]).."@${domain_name}"
			if i <= #deli_str then
				bridge_str = bridge_str..xml_escape(deli_str[i])
			end
		end
		table.insert(xml, [[<action application="bridge" data="]]..bridge_str..[["/>]]);

	-- To leave voicemail
	leave_voicemail(xml,destination_number,destination_str[1])
	return xml;
end
function custom_inbound_1(xml,didinfo,userinfo,config,xml_did_rates,callerid_array,livecall_data)
	table.insert(xml, [[<action application="set" data="calltype=DID@IP"/>]]);
	--//HP: ASTPPCOM-945
	local did_local_chan = ""
	if(params:getHeader('variable_sip_from_user') and params:getHeader('variable_sip_from_user') ~= "")then
		local sip_codec = get_sip_codec(params:getHeader('variable_sip_from_user'))
		if(sip_codec and sip_codec ~= "")then
			Logger.warning("[XML] sip_codec : "..sip_codec) --//HP: ASTPPENT-4002
			did_local_chan = ",absolute_codec_string=".."^^:"..sip_codec:gsub("%,", ":")
		end
	end
	table.insert(xml, [[<action application="bridge" data="[leg_timeout=]]..xml_escape(didinfo['leg_timeout']..did_local_chan)..[[]sofia/${sofia_profile_name}/]]..xml_escape(didinfo['extensions'])..[["/>]]);
	return xml;
end
function custom_inbound_2(xml,didinfo,userinfo,config,xml_did_rates,callerid_array,livecall_data)
	table.insert(xml, [[<action application="set" data="calltype=DIRECT-IP"/>]]);
	--//HP: ASTPPCOM-945
	local did_local_chan = ""
	if(params:getHeader('variable_sip_from_user') and params:getHeader('variable_sip_from_user') ~= "")then
		local sip_codec = get_sip_codec(params:getHeader('variable_sip_from_user'))
		if(sip_codec and sip_codec ~= "")then
			Logger.warning("[XML] sip_codec : "..sip_codec) --//HP: ASTPPENT-4002
			did_local_chan = ",absolute_codec_string=".."^^:"..sip_codec:gsub("%,", ":")
		end
	end
	table.insert(xml, [[<action application="bridge" data="[leg_timeout=]]..xml_escape(didinfo['leg_timeout']..did_local_chan)..[[]sofia/${sofia_profile_name}/]]..xml_sip(destination_number)..[[@]]..xml_escape(didinfo['extensions'])..[["/>]]);
	return xml;
end
function custom_inbound_3(xml,didinfo,userinfo,config,xml_did_rates,callerid_array,livecall_data)
	table.insert(xml, [[<action application="set" data="calltype=OTHER"/>]]);
	table.insert(xml, [[<action application="bridge" data="]]..xml_escape(didinfo['extensions'])..[["/>]]);
	return xml;
end
function custom_inbound_4(xml,didinfo,userinfo,config,xml_did_rates,callerid_array,livecall_data)
	table.insert(xml, [[<action application="set" data="calltype=STANDARD"/>]]);
	table.insert(xml, [[<action application="set" data="accountcode=]]..xml_escape(didinfo['account_code'])..[["/>]]);
	table.insert(xml, [[<action application="set" data="caller_did_account_id=]]..xml_escape(userinfo['id'])..[["/>]]);
	if(xml_did_rates ~= nil and xml_did_rates ~= '')then
	        table.insert(xml, [[<action application="set" data="origination_rates_did=]]..xml_escape(xml_did_rates)..[["/>]]);
	end
	table.insert(xml, [[<action application="transfer" data="]]..xml_escape(didinfo['extensions'])..[[ XML default"/>]]);
	return xml;
end
function custom_inbound_5(xml,didinfo,userinfo,config,xml_did_rates,callerid_array,livecall_data)
	is_local_extension = "1"
        local bridge_str = ""
	local destination_str = {}
	string.gsub(didinfo['extensions'], "([^,|]+)", function(value) destination_str[#destination_str + 1] =     value;  end);

        local common_chan_var = ""
	table.insert(xml, [[<action application="set" data="calltype=SIP-DID"/>]]); 
	local deli_str = {}
	string.gsub(didinfo['extensions'], "([,|]+)", function(value) deli_str[#deli_str + 1] =     value;  end);           
		common_chan_var = "{sip_contact_user="..xml_sip(destination_number).."}"
		for i = 1, #destination_str do
			if notify then notify(xml,destination_str[i]) end
			--//HP: ASTPPCOM-945
			local sip_codec = get_sip_codec(destination_str[i])
			local did_local_chan = ""
			if(sip_codec and sip_codec ~= "")then
				Logger.warning("[XML]  did_local_sip_codec : "..sip_codec) --//HP: ASTPPENT-4002
				did_local_chan = ",absolute_codec_string=".."^^:"..sip_codec:gsub("%,", ":")
			end		
			bridge_str = bridge_str..xml_escape("[leg_timeout="..didinfo['leg_timeout']..did_local_chan.."]").."sofia/${sofia_profile_name}/"..xml_sip(destination_number).."${regex(${sofia_contact("..xml_sip(destination_str[i]).."@${domain_name})}|^[^@]+(.*)|%1)}"
			if i <= #deli_str then
				bridge_str = bridge_str..xml_escape(deli_str[i])
			end
		end
		table.insert(xml, [[<action application="bridge" data="]]..common_chan_var..bridge_str..[["/>]]);
        
-- To leave voicemail 
        leave_voicemail(xml,destination_number,destination_str[1])
	return xml;
end

-- Dialplan for sip2sip calls
function freeswitch_xml_local(xml,destination_number,destinationinfo,callerid_array,livecall_data)

    -------------- Caller Id translation ---------    
    callerid_array['cid_name'] = do_number_translation(destinationinfo['did_cid_translation'],callerid_array['cid_name'])
	callerid_array['cid_number'] = do_number_translation(destinationinfo['did_cid_translation'],callerid_array['cid_number'])
	xml = freeswitch_xml_callerid(xml,callerid_array)	    	       
    ----------------------------------------------------------------------

    table.insert(xml, [[<action application="set" data="calltype=LOCAL"/>]]);
    table.insert(xml, [[<action application="set" data="receiver_accid=]]..xml_escape(destinationinfo['accountid'])..[["/>]]);
	table.insert(xml, [[<action application="export" data="presence_data=]]..xml_sip(livecall_data)..[[||||||Local"/>]]);
    if notify then notify(xml,destination_number) end


      table.insert(xml, [[<action application="bridge" data="[leg_timeout=]]..xml_escape(config['leg_timeout'])..[[]user/]]..xml_sip(destination_number)..[[@${domain_name}"/>]]);
    

    -- To leave voicemail 
    leave_voicemail(xml,destination_number,destination_number)

    return xml
end

-- Set callerid to override in calls
function freeswitch_xml_callerid(xml,calleridinfo)
        if (calleridinfo['cid_name'] ~= '' and calleridinfo['cid_name'] ~= '<null>')  then
                table.insert(xml, [[<action application="set" data="effective_caller_id_name=]]..xml_sip(calleridinfo['cid_name'])..[["/>]]);
        end
        if (calleridinfo['cid_number'] ~= '' and calleridinfo['cid_number'] ~= '<null>')  then
                table.insert(xml, [[<action application="set" data="effective_caller_id_number=]]..xml_sip(calleridinfo['cid_number'])..[["/>]]);
        end
        return xml
end


-- not found dialplan
function not_found(xml)

	table.insert(xml, [[<?xml version="1.0" encoding="UTF-8" standalone="no"?>]]);
	table.insert(xml, [[<document type="freeswitch/xml">]]);
	table.insert(xml, [[<section name="result">]]);
	table.insert(xml, [[<result status="not found"/>]]);	    
	table.insert(xml, [[</section>]]);
	table.insert(xml, [[</document>]]);
	return xml
end

-- Generate header 
function xml_header(xml,destination_number)
    table.insert(xml, [[<?xml version="1.0" encoding="UTF-8" standalone="no"?>]]);
	table.insert(xml, [[<document type="freeswitch/xml">]]);
	table.insert(xml, [[<section name="dialplan" description="ASTPP Dialplan">]]);
	table.insert(xml, [[<context name="]]..xml_escape(params:getHeader("Caller-Context"))..[[">]]);
	table.insert(xml, [[<extension name="]]..xml_escape(plus_destination_number(sip_clean(params:getHeader("Caller-Destination-Number"))))..[[">]]);
	table.insert(xml, [[<condition field="destination_number" expression="]]..xml_escape(plus_destination_number(sip_clean(params:getHeader("Caller-Destination-Number"))))..[[">]]);
	return xml
end

-- To Leave voicemail dialplan
function leave_voicemail(xml,vm_alternate_greet_id,vm_destination)
	table.insert(xml, [[<condition field="${cond(${user_data ]]..xml_sip(vm_destination)..[[@${domain_name} param vm-enabled} == true ? YES : NO)}" expression="^YES$">]])
    table.insert(xml, [[<action application="answer"/>]]);
	table.insert(xml, [[<action application="export" data="voicemail_alternate_greet_id=]]..xml_sip(vm_alternate_greet_id)..[["/>]]);
    table.insert(xml, [[<action application="voicemail" data="default $${domain_name} ]]..xml_sip(vm_destination)..[["/>]]);
    table.insert(xml, [[<anti-action application="hangup" data="${originate_disposition}"/>]])
    table.insert(xml, [[</condition>]])
end

-- Generate voicemail dialplan
function xml_voicemail(xml,destination_number)
	local xml = {}
	xml = xml_header(xml,destination_number)
	    table.insert(xml, [[<action application="answer"/>]]);      
	    table.insert(xml, [[<action application="voicemail" data="check default ${domain_name} ]]..xml_sip(params:getHeader("Hunt-Username"))..[["/>]]);
	xml = xml_footer(xml)	   	    
	XML_STRING = table.concat(xml, "\n");
	Logger.debug("Generated XML:\n" .. XML_STRING)
	return xml
end

-- XML footer
function xml_footer(xml)
	table.insert(xml,[[</condition>]]);
	table.insert(xml,[[</extension>]]);
	table.insert(xml,[[</context>]]);
	table.insert(xml,[[</section>]]);
	table.insert(xml,[[</document>]]);
	return xml
end

-- Handle calls errors 
function error_xml_without_cdr(destination_number,error_code,calltype,playback_audio_notification,account_id)

     local xml = {};

	--Logger.warning("[ERROR]  call_direction:" .. call_direction)
	local log_type
	local log_message
	local hangup_cause 
	local audio_file
	local audio_file = ""
	local sound_path = "/usr/share/freeswitch/sounds/en/us/callie/"
	local accountcode = ""
	local post_cdrs = 0
	if(params:getHeader("variable_accountcode") == nil) then
		if(accountnumber ~= nil) then
			accountcode = accountnumber
		end
	else
		accountcode = params:getHeader("variable_accountcode");
	end
	if(error_code == "AUTHENTICATION_FAIL") then 
		log_type = "WARNING";
		log_message = "Accountcode ".. accountcode.." is not authenticated!!";
		hangup_cause = "AUTHENTICATION_FAIL";
		audio_file = sound_path ..  "astpp_expired.wav";
	elseif(error_code == "ACCOUNT_INACTIVE_DELETED") then
		log_type = "WARNING";
		log_message = "Accountcode ".. accountcode.." is either inactive or deleted!!";
		hangup_cause = "ACCOUNT_INACTIVE_DELETED";
		audio_file = sound_path ..  "astpp_expired.wav";
		if(account_id ~= '' and tonumber(account_id) >= 0)then post_cdrs = 1 end
	--~ Fraud Detection
	elseif(error_code == "FRAUD_CALL_PER_ACCOUNT") then
		log_type = "WARNING";
		log_message = "FRAUD_CALL_PER_ACCOUNT ".. account_id;
		hangup_cause = "FRAUD_CALL_PER_ACCOUNT";
	elseif(error_code == "FRAUD_COST_PER_ACCOUNT") then
		log_type = "WARNING";
		log_message = "FRAUD_COST_PER_ACCOUNT ".. account_id;
		hangup_cause = "FRAUD_COST_PER_ACCOUNT";
	elseif(error_code == "FRAUD_CALL_PER_DESTINATION") then
		log_type = "WARNING";
		log_message = "FRAUD_CALL_PER_DESTINATION ".. account_id;
		hangup_cause = "FRAUD_CALL_PER_DESTINATION";
	elseif(error_code == "FRAUD_COST_PER_DESTINATION") then
		log_type = "WARNING";
		log_message = "FRAUD_COST_PER_DESTINATION ".. account_id;
		hangup_cause = "FRAUD_COST_PER_DESTINATION";
	--~ Fraud Detection End
	--~ CLI - NONCLI Rategroup
	elseif(error_code == "CALLERID_NOT_FOUND") then
		log_type = "WARNING";
		log_message = "CALLERID_NOT_FOUND ".. account_id;
		hangup_cause = "CALLERID_NOT_FOUND";
	--~ CLI - NONCLI Rategroup End
	elseif(error_code == "ACCOUNT_EXPIRE") then
		log_type = "WARNING";
		log_message = "Accountcode ".. accountcode.." Account has been expired!!";
		hangup_cause = "ACCOUNT_EXPIRE";
		audio_file = sound_path ..  "astpp_expired.wav";
	elseif(error_code == "NO_SUFFICIENT_FUND") then
		log_type = "WARNING";
		log_message = "Accountcode ".. accountcode.." doesn't have sufficiant fund!!";
		hangup_cause = "NO_SUFFICIENT_FUND";
		audio_file = sound_path ..  "astpp-not-enough-credit.wav";
	elseif(error_code == "DESTINATION_BLOCKED") then
		log_type = "WARNING";
		log_message = "Accountcode ".. accountcode..". Dialed number ("..destination_number..") is blocked for account!!";
		hangup_cause = "DESTINATION_BLOCKED";
		audio_file = sound_path ..  "astpp-badnumber.wav";
	elseif(error_code == "ORIGNATION_RATE_NOT_FOUND") then
		log_type = "WARNING";

		log_message = "Accountcode ".. accountcode ..". Dialed number ("..destination_number..")  origination rates not found!!";
		hangup_cause = "ORIGNATION_RATE_NOT_FOUND";
		audio_file = sound_path ..  "astpp-badphone.wav";
	elseif(error_code == "RESELLER_COST_CHEAP") then
		log_type = "WARNING";
		log_message = "Accountcode ".. accountcode..". Dialed number ("..destination_number..") , Reseller call is priced too cheap! Call being barred!!";
		hangup_cause = "RESELLER_COST_CHEAP";
		audio_file = sound_path ..  "astpp-badphone.wav";

	elseif(error_code == "TERMINATION_RATE_NOT_FOUND") then
		log_type = "WARNING";
		log_message = "Accountcode ".. accountcode..". Dialed number ("..destination_number..") termination rates not found!!";
		hangup_cause = "TERMINATION_RATE_NOT_FOUND";
		audio_file = sound_path ..  "astpp-badphone.wav";
	elseif(error_code == "DID_DESTINATION_NOT_FOUND") then
		log_type = "WARNING";
		log_message = "Accountcode ".. accountcode..". Dialed number ("..destination_number..") destination not found!!";
		hangup_cause = "DID_DESTINATION_NOT_FOUND";
		audio_file = sound_path ..  "astpp-badphone.wav";
	elseif(error_code == "UNALLOCATED_NUMBER") then
		log_type = "WARNING";
		log_message = "Accountcode ".. accountcode..". Dialed DID number ("..destination_number..") is inactive!!";
		hangup_cause = "UNALLOCATED_NUMBER";
		audio_file = sound_path ..  "astpp-badphone.wav";
	elseif(error_code == "NO_ROUTE_DESTINATION") then
		log_type = "WARNING";
		log_message = "Accountcode ".. accountcode..". Dialed DID number ("..destination_number..") routes not set!!";
		hangup_cause = "NO_ROUTE_DESTINATION";
		audio_file = sound_path ..  "astpp-badphone.wav";
	end
	    Logger.debug("post_cdrs:::" .. post_cdrs)
	if(calltype ~= "ASTPP-CALLINGCARD" and tonumber(post_cdrs) ==0) then
	    xml = xml_header(xml,destination_number)
    	table.insert(xml, [[<action application="log" data="]]..xml_sip(log_type.." "..log_message)..[["/>]]);

	    if (playback_audio_notification == "0") then
	    	table.insert(xml, [[<action application="playback" data="]]..xml_escape(audio_file)..[["/>]]);
	    end


	    local callstart = os.date("!%Y-%m-%d %H:%M:%S")

	    if (callerid_array['original_cid_name'] ~= '' and callerid_array['original_cid_name'] ~= '<null>')  then
               table.insert(xml, [[<action application="set" data="original_caller_id_name=]]..xml_sip(callerid_array['original_cid_name'])..[["/>]]);
            end
            if (callerid_array['cid_number'] ~= '' and callerid_array['cid_number'] ~= '<null>')  then
               table.insert(xml, [[<action application="set" data="original_caller_id_number=]]..xml_sip(callerid_array['original_cid_number'])..[["/>]]);
            end

            table.insert(xml, [[<action application="set" data="error_cdr=1"/>]]);
	    table.insert(xml, [[<action application="set" data="callstart=]]..callstart..[["/>]]);
	    table.insert(xml, [[<action application="set" data="account_id=]]..xml_escape(account_id)..[["/>]]);
	    local parent_id= ""
	    if(account_id ~= '' and tonumber(account_id) >= 0)then
	        parent_id=get_parentid(account_id);
	    end
	    if(parent_id~=nil and parent_id~="") then
			table.insert(xml, [[<action application="set" data="parent_id=]]..xml_escape(parent_id)..[["/>]]);
	    end
	    if(call_direction == 'local')then
		    table.insert(xml, [[<action application="set" data="call_direction=inbound"/>]]);
	    else
		    table.insert(xml, [[<action application="set" data="call_direction=]]..xml_escape(call_direction)..[["/>]]);
	    end	
	    if(call_direction == 'inbound')then
		    table.insert(xml, [[<action application="set" data="calltype=DID"/>]]);
	    end
	    if(call_direction == 'local')then
		    table.insert(xml, [[<action application="set" data="calltype=LOCAL"/>]]);
	    end
		-- ASTPPCOM-982 Ashish start
		if (params:getHeader("variable_sip_h_P-Voice_broadcast") == 'true') then
			table.insert(xml, [[<action application="set" data="calltype=BROADCAST"/>]]);
		end
		-- ASTPPCOM-982 Ashish End
	    table.insert(xml, [[<action application="set" data="sip_ignore_remote_cause=true"/>]]);        
	    table.insert(xml, [[<action application="set" data="call_processed=internal"/>]]);
	    table.insert(xml, [[<action application="set" data="effective_destination_number=]]..xml_sip(destination_number)..[["/>]]);
	    --table.insert(xml, [[<action application="set" data="hangup_cause=${last_bridge_hangup_cause}"/>]]);  
	    --table.insert(xml, [[<action application="set" data="process_cdr=false"/>]]);      
	    table.insert(xml, [[<action application="set" data="last_bridge_hangup_cause=]]..hangup_cause..[["/>]]);        
	    table.insert(xml, [[<action application="hangup" data="]]..hangup_cause..[["/>]]);      

	    xml = xml_footer(xml);
	    XML_STRING = table.concat(xml, "\n");
	    Logger.debug("Generated XML:\n" .. XML_STRING)
	    return
	elseif(tonumber(post_cdrs) ==1)then
	    Logger.debug("post_cdrs IN:::" .. post_cdrs)
		xml = xml_header(xml,destination_number)
	  	table.insert(xml, [[<action application="set" data="sip_ignore_remote_cause=true"/>]]);        
	    	table.insert(xml, [[<action application="playback" data="]]..xml_escape(audio_file)..[["/>]]);
		table.insert(xml, [[<action application="hangup" data="]]..xml_escape(hangup_cause)..[["/>]]);
		xml = xml_footer(xml);
		XML_STRING = table.concat(xml, "\n");
		Logger.debug("Generated XML:\n" .. XML_STRING)
		return
    
	else
		session:execute("set", "process_cdr=false" );
		session:streamFile( audio_file );
	end
end

-- Generate calling card dialplan
function generate_cc_dialplan(destination_number)
	local xml = {};
	table.insert(xml, [[<?xml version="1.0" encoding="UTF-8" standalone="no"?>]]);
	table.insert(xml, [[<document type="freeswitch/xml">]]);
		table.insert(xml, [[<section name="dialplan" description="ASTPP Dialplan">]]);
			table.insert(xml, [[<context name="]]..xml_escape(params:getHeader("Caller-Context"))..[[">]]);
				table.insert(xml, [[<extension name="]]..xml_sip(destination_number)..[[">]]);
				table.insert(xml, [[<condition field="destination_number" expression="]]..xml_escape(plus_destination_number(sip_clean(destination_number)))..[[">]]);
					table.insert(xml, [[<action application="log" data="INFO ASTPP - Calling Card Call"/>]]);        
					table.insert(xml, [[<action application="answer"/>]]);
					table.insert(xml, [[<action application="sleep" data="2000"/>]]);                    
					table.insert(xml, [[<action application="lua" data="astpp-callingcards.lua"/>]]);    
				table.insert(xml,[[</condition>]]);
				table.insert(xml,[[</extension>]]);
			table.insert(xml,[[</context>]]);
		table.insert(xml,[[</section>]]);
	table.insert(xml,[[</document>]]);
	XML_STRING = table.concat(xml, "\n");
	Logger.debug("Generated XML:\n" .. XML_STRING)
end

-- Set reseller concurrent call limits
function set_cc_limit_resellers(reseller_userinfo)

	local xml_temp = ""
	-- Set CPS limit for reseller if > 0
	if (tonumber(reseller_userinfo['maxchannels']) > 0) then
		xml_temp = "<action application=\"limit\" data=\"db "..xml_sip(reseller_userinfo['number']).. " user_"..xml_sip(reseller_userinfo['number']).." "..xml_escape(reseller_userinfo['maxchannels']).." !SWITCH_CONGESTION\"/>"
	end

	if (tonumber(reseller_userinfo['cps']) > 0) then
		xml_temp = xml_temp.."<action application=\"limit\" data=\"hash CPS_"..xml_sip(reseller_userinfo['number']).. " CPS_user_"..xml_sip(reseller_userinfo['number']).." "..xml_escape(reseller_userinfo['cps']).."/1 !SWITCH_CONGESTION\"/>"
	end

	return xml_temp
end
