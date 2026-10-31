function custom_inbound_7(xml,didinfo,userinfo,config,xml_did_rates,callerid_array,livecall_data)
	if did_pbx_info then did_pbx_info(xml,didinfo,userinfo,config,xml_did_rates,callerid_array) end
end

function check_local_call(destination_number)
	local query = db_sql("SELECT sip_devices.id as sip_id,sip_devices.username as username,accounts.number as accountcode,sip_devices.accountid as accountid,accounts.did_cid_translation as did_cid_translation,sip_devices.codec as sip_codec FROM "..TBL_SIP_DEVICES.." as sip_devices,"..TBL_USERS.." as  accounts WHERE accounts.status=0 AND accounts.deleted=0 AND accounts.id=sip_devices.accountid AND sip_devices.username = ?s limit 1", destination_number);
   
    Logger.debug("[CHECK_LOCAL_CALL] Query :" .. query)
    assert (dbh:query(query, function(u)
        sip2sipinfo = u;
    end))
    return sip2sipinfo;
end

function get_trim_value (s)
    return (string.gsub(s, "^%s*(.-)%s*$", "%1"))
end

function get_ringgroup(rgroupid)
	local ringgroup_arr;
	local query = db_sql("SELECT id as ringgroup_id,name as ringgroup_name,strategy as ringgroup_strategy,destinations as ringgroup_destinations,description as ringgroup_description from pbx_ringgroup WHERE id = ?i and status = 0 LIMIT 1", rgroupid);
	Logger.debug("[Functions] [GET RING GROUP INFO] Query :" .. query)
	assert (dbh:query(query, function(u)
		ringgroup_arr = u
	end))  
	return ringgroup_arr
end

function fail_cdrs_pbx(xml,callerid_array,userinfo,destination_number)
	hangup_cause = "NO_ROUTE_DESTINATION";
	local sound_path = "/usr/share/freeswitch/sounds/en/us/callie/"
	audio_file = sound_path ..  "astpp-badphone.wav";
	local callstart = os.date("!%Y-%m-%d %H:%M:%S")
	if (callerid_array['original_cid_name'] ~= '' and callerid_array['original_cid_name'] ~= '<null>')  then
		table.insert(xml, [[<action application="set" data="original_caller_id_name=]]..callerid_array['original_cid_name']..[["/>]]);
	end
	if (callerid_array['cid_number'] ~= '' and callerid_array['cid_number'] ~= '<null>')  then
		table.insert(xml, [[<action application="set" data="original_caller_id_number=]]..callerid_array['original_cid_name']..[["/>]]);
	end
	table.insert(xml, [[<action application="set" data="error_cdr=1"/>]]);
	table.insert(xml, [[<action application="set" data="callstart=]]..callstart..[["/>]]);
	table.insert(xml, [[<action application="set" data="account_id=]]..userinfo['id']..[["/>]]);
	local parent_id=get_parentid(userinfo['id']);
	if(parent_id~=nil and parent_id~="") then
		table.insert(xml, [[<action application="set" data="parent_id=]]..parent_id..[["/>]]);
	end
	table.insert(xml, [[<action application="set" data="call_direction=inbound"/>]]);
	table.insert(xml, [[<action application="playback" data="]]..audio_file..[["/>]]);
	table.insert(xml, [[<action application="set" data="sip_ignore_remote_cause=true"/>]]);        
	table.insert(xml, [[<action application="set" data="call_processed=internal"/>]]);
	table.insert(xml, [[<action application="set" data="effective_destination_number=]]..destination_number..[["/>]]); 
	table.insert(xml, [[<action application="set" data="last_bridge_hangup_cause=]]..hangup_cause..[["/>]]);        
	table.insert(xml, [[<action application="hangup" data="]]..hangup_cause..[["/>]]);   
	table.insert(xml, [[<action application="set" data="sip_ignore_remote_cause=true"/>]]);        
	table.insert(xml, [[<action application="playback" data="]]..audio_file..[["/>]]);
	table.insert(xml, [[<action application="hangup" data="]]..hangup_cause..[["/>]]); 
end

function did_pbx_info(xml,didinfo,userinfo,config,xml_did_rates,callerid_array)
    table.insert(xml, [[<action application="set" data="did_calltype=]]..didinfo['call_type']..[["/>]]);
	table.insert(xml, [[<action application="set" data="did_extensions=]]..didinfo['extensions']..[["/>]]);
    Logger.info("[RINGGROUP] Call Type : "..didinfo['call_type'])
    if(tonumber(userinfo['is_recording']) == 0) then 
		table.insert(xml, [[<action application="export" data="is_recording=1"/>]]);
		table.insert(xml, [[<action application="export" data="media_bug_answer_req=true"/>]]);
		table.insert(xml, [[<action application="export" data="RECORD_STEREO=true"/>]]);
		table.insert(xml, [[<action application="export" data="record_sample_rate=8000"/>]]);
		table.insert(xml, [[<action application="export" data="execute_on_answer=record_session $${recordings_dir}/${uuid}.wav"/>]]);
	end

    if(tonumber(didinfo['call_type']) == 7)then
        Logger.info("[RINGGROUP] Call Type : Ring Group")
        ringgroup_arr = get_ringgroup(didinfo['extensions'])
        if(ringgroup_arr and ringgroup_arr ~= nil)then
            strategy =  ringgroup_arr['ringgroup_strategy']
            ringgroup_dlr_str =''
            if(strategy == 'simultaneous')then
                separated=","
            else
                separated="|"
            end
            ringgroup_dlr_str=''
            params_ring = JSON:decode(ringgroup_arr['ringgroup_destinations'])
            Count = 0
            for Index, Value in pairs( params_ring ) do
                Count = Count + 1
            end
            if(tonumber(Count) == 0)then
                table.insert(xml, [[<action application="set" data="call_direction=inbound"/>]]);
                if(call_direction == 'local')then
                    table.insert(xml, [[<action application="set" data="calltype=LOCAL"/>]]);
                end
                local sound_path = "/usr/share/freeswitch/sounds/en/us/callie/"
                audio_file = sound_path ..  "astpp-badphone.wav";
                if (config['playback_audio_notification'] == "0") then
                    table.insert(xml, [[<action application="playback" data="]]..audio_file..[["/>]]);
                end
                table.insert(xml, [[<action application="set" data="sip_ignore_remote_cause=true"/>]]);        
                table.insert(xml, [[<action application="set" data="call_processed=internal"/>]]);
                table.insert(xml, [[<action application="set" data="effective_destination_number=]]..destination_number..[["/>]]); 
                table.insert(xml, [[<action application="set" data="last_bridge_hangup_cause=UNALLOCATED_NUMBER"/>]]);        
                table.insert(xml, [[<action application="hangup" data="UNALLOCATED_NUMBER"/>]]);      
                return
            end
            ringgroup_count= Count/4
            
            for h=1,ringgroup_count do
                destination="destination_"..h;
                delay="delay_"..h;
                timeout="time_out_"..h;
                Promptdropdown="Promptdropdown_"..h;
                local final_count = 0
                propmt_value = params_ring[Promptdropdown]
                if(tonumber(propmt_value) == 0)then
                    propmt_flag = 'true'
                else
                    propmt_flag = 'false'
                end
                if(strategy == 'simultaneous')then
                    propmt_flag = 'false'
                end
                api = freeswitch.API();
                local domain_name = "eval ${domain_name}";
                domain_name = api:executeString(domain_name);
                extension_status = "show channels like "..params_ring[destination].."@"..domain_name;
                extension_reply = api:executeString(extension_status);
                extension_reply = extension_reply:gsub("total.", "")
                extension_reply = extension_reply:gsub(" ", "")
                final_count = extension_reply
                final_count = get_trim_value(final_count);
                Logger.info("Final Count : "..final_count.."===")
                if (propmt_flag == 'false') then
                    group_confirm = "confirm=false,dialed_user="..params_ring[destination]..",";
                else
                    group_confirm = "confirm=false,dialed_user="..params_ring[destination]..",";
                end
                if(params_ring[destination] ~= nil)then
                    if(params_ring[timeout] == nil or params_ring[timeout] == 'null')then
                        params_ring[timeout] =0
                    end
                    if(params_ring[delay] == nil or params_ring[delay] == 'null')then
                        params_ring[delay] =0
                    end
                    if(tonumber(final_count) == 0)then
                        ringgroup_dlr_str = ringgroup_dlr_str .."[sip_invite_params=user=local,sip_h_p-call_type='custom_forward',call_timeout="..params_ring[timeout]..","..group_confirm.."leg_timeout="..params_ring[timeout]..",leg_delay_start="..params_ring[delay].."]".."user/"..params_ring[destination].."@"..params:getHeader("variable_sip_to_host")..":"..params:getHeader("variable_sip_to_port")..separated
                    end
                end
            end
            Logger.info("RingGroup Dlr Str : "..ringgroup_dlr_str)
            table.insert(xml, [[<action application="set" data="effective_caller_id_name=]]..callerid_array['cid_name']..[["/>]]);
            table.insert(xml, [[<action application="set" data="effective_caller_id_number=]]..callerid_array['cid_number']..[["/>]]);
            table.insert(xml, [[<action application="set" data="module_name=ringgroup"/>]]);
                table.insert(xml, [[<action application="bridge" data="{sip_h_P-call_type='custom_forward'}]]..ringgroup_dlr_str..[["/>]]);
        end
    else
        fail_cdrs_pbx(xml,callerid_array,userinfo,destination_number)
    end
end