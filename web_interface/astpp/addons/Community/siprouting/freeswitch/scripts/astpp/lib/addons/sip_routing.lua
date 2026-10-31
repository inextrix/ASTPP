function sip_routing_info(sip_id)  
	local rotuting_arr;
	local query = db_sql("SELECT * from sip_device_routing WHERE sip_device_id = ?i limit 1", sip_id);
	Logger.debug("[PBX_SIP_ROUTING] [GET_ROUTING_INFO] Query :" .. query)
	assert (dbh:query(query, function(u)
		rotuting_arr = u
	end))  
	return rotuting_arr
end

function get_record_info(customer_userinfo)
	local record = {}
    if(didinfo ~= nil)then
    local query  = db_sql("SELECT sip_device_routing.is_recording FROM sip_devices AS sip_devices, accounts AS accounts ,sip_device_routing AS sip_device_routing  WHERE sip_devices.accountid = accounts.id AND sip_devices.id = sip_device_routing.sip_device_id AND sip_devices.username = ?s AND accounts.status = 0 ", didinfo['extensions']);
		Logger.debug("[GET get_record_info] Query :" .. query)	
		assert (dbh:query(query, function(u)
			record = u
		end))
	end
    return record
end


function check_local_call(destination_number)
    -- local query = "SELECT sip_devices.id as sip_id,sip_devices.username as username,accounts.number as accountcode,sip_devices.accountid as accountid,accounts.did_cid_translation as did_cid_translation,sip_devices.codec as sip_codec FROM "..TBL_SIP_DEVICES.." as sip_devices,"..TBL_USERS.." as  accounts ,domains WHERE accounts.id=domains.accountid AND accounts.status=0 AND accounts.deleted=0 AND accounts.id=sip_devices.accountid AND sip_devices.username=\"" ..destination_number .."\"  AND domains.domain=\"" ..user_domain .."\" limit 1";
	local query = db_sql("SELECT sip_devices.id as sip_id,sip_devices.username as username,accounts.number as accountcode,sip_devices.accountid as accountid,accounts.did_cid_translation as did_cid_translation,sip_devices.codec as sip_codec FROM "..TBL_SIP_DEVICES.." as sip_devices,"..TBL_USERS.." as  accounts WHERE accounts.status=0 AND accounts.deleted=0 AND accounts.id=sip_devices.accountid AND sip_devices.username = ?s limit 1", destination_number);
   
    Logger.debug("[CHECK_LOCAL_CALL] Query :" .. query)
    assert (dbh:query(query, function(u)
        sip2sipinfo = u;
    end))
    return sip2sipinfo;
end


function sip_device_fail_over(didinfo,xml,destination_number)
	SipDestinationInfo = check_local_call(didinfo['extensions'],didinfo['accountid']) 
	if(SipDestinationInfo and SipDestinationInfo ~= '')then
		local sip_routing_arr = sip_routing_info(SipDestinationInfo['sip_id'])
		if(sip_routing_arr)then
			table.insert(xml, [[<action application="set" data="on_busy_flag=]]..sip_routing_arr['on_busy_flag']..[["/>]]);
			table.insert(xml, [[<action application="set" data="on_busy_destination=]]..sip_routing_arr['on_busy_destination']..[["/>]]);
			table.insert(xml, [[<action application="set" data="no_answer_flag=]]..sip_routing_arr['no_answer_flag']..[["/>]]);
			table.insert(xml, [[<action application="set" data="no_answer_destination=]]..sip_routing_arr['no_answer_destination']..[["/>]]);
			table.insert(xml, [[<action application="set" data="not_register_flag=]]..sip_routing_arr['not_register_flag']..[["/>]]);
			table.insert(xml, [[<action application="set" data="not_register_destination=]]..sip_routing_arr['not_register_destination']..[["/>]]);
			table.insert(xml, [[<action application="set" data="variable_sip_to_host=]]..params:getHeader("variable_sip_to_host")..[["/>]]);
			table.insert(xml, [[<action application="set" data="variable_sip_to_port=]]..params:getHeader("variable_sip_to_port")..[["/>]]);
			table.insert(xml, [[<action application="set" data="leg_timeout=]]..config['leg_timeout']..[["/>]]);
			table.insert(xml, [[<action application="set" data="userinfo_id=]]..SipDestinationInfo['accountid']..[["/>]]);
			table.insert(xml, [[<action application="set" data="sip_destination_number=]]..didinfo['extensions']..[["/>]]);
			table.insert(xml, [[<action application="set" data="destination_number=]]..destination_number..[["/>]]);
			table.insert(xml, [[<action application="set" data="did_number=]]..didinfo['did_number']..[["/>]]);
			table.insert(xml, [[<action application="lua" data="astpp/lib/sip_routing/astpp-sipdevice-routing.lua"/>]]);
		end 
	end
end



function sip_device_routing(xml,destination_number,destinationinfo,callerid_array) 
	Logger.info("[PBX_SIP_ROUTING] SIP ID : "..destinationinfo['sip_id'])
	local sip_routing_arr = sip_routing_info(destinationinfo['sip_id'])
--HP 10-Jan-2019 PBX Feature Code Related changes.

	-- if(sip_routing_arr and tonumber(sip_routing_arr['do_not_disturb']) == 1)then
		Logger.info("[PBX_SIP_ROUTING] Call Forwarding Flag : "..sip_routing_arr['call_forwarding_flag'])
		local sip_destination_number = destination_number
		if(tonumber(sip_routing_arr['call_forwarding_flag']) == 0 and sip_routing_arr['call_forwarding_destination'] ~= nil and sip_routing_arr['call_forwarding_destination'] ~= '')then
			sip_destination_number = sip_routing_arr['call_forwarding_destination']
			routing_voicemail_number = sip_routing_arr['call_forwarding_destination']
			Logger.info("[PBX_SIP_ROUTING] SIP Call Forwarding Enable")
			table.insert(xml, [[<action application="set" data="sip_h_X-call-type=did"/>]]);
			table.insert(xml, [[<action application="set" data="sip_h_X-did-call-type=LOCAL"/>]]);
			bridge = "{sip_invite_params=user=LOCAL,sip_from_uri="..sip_routing_arr['call_forwarding_destination'].."@${domain_name}}[leg_timeout="..config['leg_timeout'].."]user/"..sip_routing_arr['call_forwarding_destination'].."@"..params:getHeader("variable_sip_to_host")..":"..params:getHeader("variable_sip_to_port")
			table.insert(xml, [[<action application="bridge" data="]]..bridge..[["/>]]);
		else
			sip_destination_number = destination_number
			routing_voicemail_number = destination_number
			Logger.info("[PBX_SIP_ROUTING] SIP Call Forwarding Disable")
			local sip_call_string = '';
			sip_call_string = "user/"..destination_number.."@"..params:getHeader("variable_sip_to_host")..(params:getHeader("variable_sip_to_port") and ":"..params:getHeader("variable_sip_to_port") or "")
			table.insert(xml, [[<action application="set" data="hangup_after_bridge=true"/>]]);
			table.insert(xml, [[<action application="bridge" data="{sip_invite_params=user=LOCAL,ignore_early_media=true,sip_h_P-call_type='custom_forward',sip_h_P-Accountcode=]]..userinfo['id']..[[}[leg_timeout=]]..config['leg_timeout']..[[ ] ]]..sip_call_string..[["/>]]);

		end
		if notify then notify(xml,destination_number) end
		table.insert(xml, [[<action application="set" data="on_busy_flag=]]..sip_routing_arr['on_busy_flag']..[["/>]]);
		table.insert(xml, [[<action application="set" data="on_busy_destination=]]..sip_routing_arr['on_busy_destination']..[["/>]]);
		table.insert(xml, [[<action application="set" data="no_answer_flag=]]..sip_routing_arr['no_answer_flag']..[["/>]]);
		table.insert(xml, [[<action application="set" data="no_answer_destination=]]..sip_routing_arr['no_answer_destination']..[["/>]]);
		table.insert(xml, [[<action application="set" data="not_register_flag=]]..sip_routing_arr['not_register_flag']..[["/>]]);
		table.insert(xml, [[<action application="set" data="not_register_destination=]]..sip_routing_arr['not_register_destination']..[["/>]]);
		table.insert(xml, [[<action application="set" data="variable_sip_to_host=]]..params:getHeader("variable_sip_to_host")..[["/>]]);
		table.insert(xml, [[<action application="set" data="variable_sip_to_port=]]..(params:getHeader("variable_sip_to_port") or "")..[["/>]]);
		table.insert(xml, [[<action application="set" data="leg_timeout=]]..config['leg_timeout']..[["/>]]);
		table.insert(xml, [[<action application="set" data="userinfo_id=]]..userinfo['id']..[["/>]]);
		table.insert(xml, [[<action application="set" data="sip_destination_number=]]..sip_destination_number..[["/>]]);
		table.insert(xml, [[<action application="set" data="did_number=]]..sip_destination_number..[["/>]]);
		table.insert(xml, [[<action application="lua" data="astpp/lib/sip_routing/astpp-sipdevice-routing.lua"/>]]);
end

function freeswitch_xml_local(xml,destination_number,destinationinfo,callerid_array)
    local sip_routing_info = sip_routing_info(destinationinfo['sip_id'])

    Logger.info("DESTINATION : "..destination_number)

    if(tonumber(sip_routing_info['call_waiting']) == 1) then 
        table.insert(xml, [[<action application="limit" data="hash inbound ]]..destination_number..[[ ]]..sip_routing_info['call_waiting']..[[ !USER_BUSY" />]]);
    end	
    table.insert(xml, [[<action application="export" data="presence_data=]]..livecall_data..[[||||||LOCAL|||]]..params:getHeader("variable_sip_contact_host")..[["/>]])
    Logger.warning("[LOCAL CALL] Recording Flag :"..sip_routing_info['is_recording'])
    if(tonumber(sip_routing_info['is_recording']) == 0) then 
        table.insert(xml, [[<action application="export" data="is_recording=1"/>]]);
        table.insert(xml, [[<action application="export" data="media_bug_answer_req=true"/>]]);
        table.insert(xml, [[<action application="export" data="RECORD_STEREO=true"/>]]);
        table.insert(xml, [[<action application="export" data="record_sample_rate=8000"/>]]);
        table.insert(xml, [[<action application="export" data="execute_on_answer=record_session $${recordings_dir}/${uuid}.wav"/>]]);
    end    -------------- Caller Id translation ---------
    Logger.warning("[FSXMLLOCAL] Caller ID Translation Starts")
    callerid_array['cid_name'] = do_number_translation(destinationinfo['did_cid_translation'],callerid_array['cid_name'])
    callerid_array['cid_number'] = do_number_translation(destinationinfo['did_cid_translation'],callerid_array['cid_number'])
    xml = freeswitch_xml_callerid(xml,callerid_array)	    	   
    Logger.warning("[FSXMLLOCAL] Caller ID Translation Ends")
    ----------------------------------------------------------------------

    table.insert(xml, [[<action application="set" data="calltype=LOCAL"/>]]);
    table.insert(xml, [[<action application="set" data="receiver_accid=]]..destinationinfo['accountid']..[["/>]]);

    if notify then notify(xml,destination_number) end
    --Check Fifo flag
    local sip_routing_array = {}
    routing_voicemail_number = destination_number
    if sip_device_routing then 
        sip_device_routing(xml,destination_number,destinationinfo,callerid_array)  
    end
    -- To leave voicemail 
    leave_voicemail(xml,routing_voicemail_number,routing_voicemail_number)
    return xml
end

function custom_inbound_0(xml,didinfo,userinfo,config,xml_did_rates,callerid_array,livecall_data)
	config['leg_timeout'] = didinfo['leg_timeout'];
	is_local_extension = "1"
	local bridge_str = ""
	local destination_str = {}
	local deli_str = {}
	string.gsub(didinfo['extensions'], "([,|]+)", function(value) deli_str[#deli_str + 1] =     value;  end);
	string.gsub(didinfo['extensions'], "([^,|]+)", function(value) destination_str[#destination_str + 1] =     value;  end);
	table.insert(xml, [[<action application="set" data="calltype=DID-LOCAL"/>]]); 

	common_chan_var = "{sip_invite_params=user=LOCAL,sip_from_uri="..didinfo['extensions'].."@${domain_name}}"
		for i = 1, #destination_str do
			if notify then notify(xml,destination_str[i]) end
			bridge_str = bridge_str.."[leg_timeout="..didinfo['leg_timeout'].."]user/"..destination_str[i].."@"..params:getHeader("variable_sip_to_host")..":"..params:getHeader("variable_sip_to_port") --//HP: 
			if i <= #deli_str then
				bridge_str = bridge_str..deli_str[i]
			end
		end
		SipDestinationInfo = check_local_call(didinfo['extensions'],didinfo['accountid']) 
		if(SipDestinationInfo and SipDestinationInfo ~= '')then
			sip_device_routing(xml,didinfo['extensions'],SipDestinationInfo,callerid_array) 
		else
			table.insert(xml, [[<action application="bridge" data="]]..bridge_str..[["/>]]);
		end
	if sip_device_fail_over then sip_device_fail_over(didinfo,xml,destination_number) end

	leave_voicemail(xml,destination_number,destination_str[1])
end

function custom_inbound_5(xml,didinfo,userinfo,config,xml_did_rates,callerid_array,livecall_data)
	config['leg_timeout'] = didinfo['leg_timeout'];
	is_local_extension = "1"
    local bridge_str = ""
	local destination_str = {}
	local deli_str = {}
	string.gsub(didinfo['extensions'], "([,|]+)", function(value) deli_str[#deli_str + 1] =     value;  end);
	string.gsub(didinfo['extensions'], "([^,|]+)", function(value) destination_str[#destination_str + 1] =     value;  end);
	table.insert(xml, [[<action application="set" data="calltype=SIP-DID"/>]]); 
	local destination_number = didinfo['extensions'];
		common_chan_var = "{sip_invite_params=user=LOCAL,sip_from_uri="..didinfo['extensions'].."@${domain_name}}"
		for i = 1, #destination_str do
			if notify then notify(xml,destination_str[i]) end
			bridge_str = bridge_str.."[leg_timeout="..didinfo['leg_timeout'].."]sofia/${sofia_profile_name}/"..destination_number.."${regex(${sofia_contact("..destination_str[i].."@${domain_name})}|^[^@]+(.*)|%1)}"
			if i <= #deli_str then
				bridge_str = bridge_str..deli_str[i]
			end
		end
		SipDestinationInfo = check_local_call(didinfo['extensions'],didinfo['accountid']) 
		if(SipDestinationInfo and SipDestinationInfo ~= '')then
			local sip_routing_arr = sip_routing_info(SipDestinationInfo['sip_id']);
			if(tonumber(sip_routing_arr['call_forwarding_flag']) == 0)then
				sip_device_routing(xml,didinfo['extensions'],SipDestinationInfo,callerid_array)
			else
				table.insert(xml, [[<action application="bridge" data="]]..common_chan_var..bridge_str..[["/>]]);
			end
		end
	if sip_device_fail_over then sip_device_fail_over(didinfo,xml,destination_number) end
	-- To leave voicemail 
	leave_voicemail(xml,destination_number,destination_str[1])
end

function recording_dialplan(customer_userinfo,xml,didinfo)
	Logger.info("[PBX] Call Direction : "..call_direction)
	recording_info = get_record_info(customer_userinfo['is_recording'])
	if(recording_info ~= nil and recording_info['is_recording'] ~= nil and tonumber(recording_info['is_recording']) ~= 1)then
		customer_userinfo['is_recording'] = recording_info['is_recording'];
	end 
		if(tonumber(customer_userinfo['is_recording']) == 0 ) then 
			table.insert(xml, [[<action application="export" data="is_recording=1"/>]]);
			table.insert(xml, [[<action application="export" data="media_bug_answer_req=true"/>]]);
			table.insert(xml, [[<action application="export" data="RECORD_STEREO=true"/>]]);
			table.insert(xml, [[<action application="export" data="record_sample_rate=8000"/>]]);
			if(didinfo ~= nil and didinfo['call_type'] ~= nil and tonumber(didinfo['call_type']) == 0)then
				table.insert(xml, [[<action application="export" data="execute_on_answer=record_session $${recordings_dir}/${uuid}DID-LOCAL_]]..didinfo['accountid']..[[.wav"/>]]);
			elseif(didinfo ~= nil and didinfo['call_type'] ~= nil and tonumber(didinfo['call_type']) == 5)then
				table.insert(xml, [[<action application="export" data="execute_on_answer=record_session $${recordings_dir}/${uuid}SIP-DID_]]..didinfo['accountid']..[[.wav"/>]]);			
			else
				table.insert(xml, [[<action application="export" data="execute_on_answer=record_session $${recordings_dir}/${uuid}.wav"/>]]);
			end
		end
	return xml
end

