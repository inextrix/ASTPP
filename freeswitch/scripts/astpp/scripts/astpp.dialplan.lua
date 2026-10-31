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

destination_number = params:getHeader("Caller-Destination-Number")

if (destination_number == nil) then
    return;
end

Logger.info("[Dialplan] Dialed number : "..destination_number)

-- ASTPPCOM-982 Ashish start
if (params:getHeader("variable_sip_h_P-Voice_broadcast") == 'true') then
	destination_number = params:getHeader("variable_sip_h_P-cb_destination")
	Logger.debug("Clicktocall Destination Number " ..destination_number)
end
-- ASTPPCOM-982 Ashish End
--Check if dialed number is calling card access number
local cc_access_number = get_cc_access_number(destination_number)
if (cc_access_number and cc_access_number['access_number'] ~= '' and cc_access_number['access_number'] == destination_number) then
        if (destination_number == cc_access_number['access_number']) then
            generate_cc_dialplan(destination_number);
            return;
        end
end
----------------------- END CALLING CARD SECTION -------------------------------

--@TODO: We will take it to Feature code module
------------------------- VOICEMAIL LISTEN START--------------------------------------
if(config['voicemail_number'] ~= nil and config['voicemail_number']:gsub("%s+", "") ~= '' and config['voicemail_number']:gsub("%s+", "") == destination_number) then
Logger.info("[Dialplan] VOICEMAIL : ")
	xml = xml_voicemail(xml,destination_number)
return;
end
------------------------- VOICEMAIL LISTEN END --------------------------------------

---- Getting callerid for localization feature ---- 
-- ASTPPCOM-1382 Ashish start
if (params:getHeader('variable_effective_caller_id_name') ~= nil and params:getHeader('variable_effective_caller_id_name') ~= '' and params:getHeader('variable_effective_caller_id_number') ~= nil and params:getHeader('variable_effective_caller_id_number') ~= '') then
    callerid_number = params:getHeader('variable_effective_caller_id_number') or ""
    callerid_name = params:getHeader('variable_effective_caller_id_name') or ""
elseif ((params:getHeader('variable_effective_caller_id_number') ~= nil and params:getHeader('variable_effective_caller_id_number') ~= '') or (params:getHeader('variable_effective_caller_id_name') == nil and params:getHeader('variable_effective_caller_id_name') == '')) then
    callerid_number = params:getHeader('variable_effective_caller_id_number') or ""
    callerid_name = params:getHeader('Caller-Caller-ID-Name') or ""

elseif ((params:getHeader('variable_effective_caller_id_name') ~= nil and params:getHeader('variable_effective_caller_id_name') ~= '') or (params:getHeader('variable_effective_caller_id_number') == nil and params:getHeader('variable_effective_caller_id_number') == '')) then
    callerid_number = params:getHeader('Caller-Caller-ID-Number') or ""
    callerid_name = params:getHeader('variable_effective_caller_id_name') or ""
else
    callerid_number = params:getHeader('Caller-Caller-ID-Number') or ""
    callerid_name = params:getHeader('Caller-Caller-ID-Name') or ""
end
-- ASTPPCOM-1382 Ashish end


--To override custom callerid from addon
if custom_callerid then custom_callerid() end     
Logger.info("[Dialplan] Caller Id name / number  : "..callerid_name.." / "..callerid_number)

--Saving caller id information in array
callerid_array = {}
callerid_array['cid_name'] = callerid_name
callerid_array['cid_number'] = callerid_number
callerid_array['original_cid_name'] = callerid_name
callerid_array['original_cid_number'] = callerid_number
--------------------------------------

-- Define default variables 
call_direction = 'outbound'
local calltype = 'ASTPP-STANDARD'
local accountcode = ''
local sipcall = ''
local auth_type = 'default'
local authinfo = {}
local accountname = 'default'
local original_destination_number=''
package_id = 0

accountcode = params:getHeader("variable_accountcode")

--To override custom calltype
if custom_calltype then
	calltype_custom = custom_calltype() 
	if(calltype_custom ~= '' and calltype_custom ~= nil) then
		calltype = calltype_custom 
	end
end

--To override custom accountcode
if custom_accountcode then
	accountcode_custom = custom_accountcode()
	if(accountcode_custom ~= '' and accountcode_custom ~= nil) then
		accountcode = accountcode_custom 
	end
end
sipcall = params:getHeader("variable_sipcall")

call_direction = define_call_direction(destination_number,accountcode,config)

Logger.info("[Dialplan] Call direction : ".. call_direction)


-- If no account code found then do further authentication of call
if (accountcode == nil or accountcode == '') then

    from_ip = ""	
    	from_ip = params:getHeader('Hunt-Network-Addr')

    authinfo = doauthentication(destination_number,from_ip)

    if (authinfo ~= nil and authinfo['type'] == 'acl') then      
    	accountcode = authinfo['account_code']
        if (authinfo['prefix'] ~= '') then
            destination_number = do_number_translation(authinfo['prefix'].."/*",destination_number)
        end
    	auth_type = 'acl';
    	accountname = authinfo['name'] or ""
    end
end
-- ASTPPCOM-982 Ashish start
if (accountcode == nil or accountcode == "") then
	accountcode = params:getHeader("variable_sip_h_P-Accountcode")
end
-- ASTPPCOM-982 Ashish End
-- Still no account code that means call is not authenticated.
if (accountcode == nil or accountcode == "") then
  Logger.notice("[Dialplan] Call authentication fail..!!"..config['playback_audio_notification'])
  error_xml_without_cdr(destination_number,"AUTHENTICATION_FAIL",calltype,config['playback_audio_notification'],'0') 
  return
end

Logger.info("[Accountcode : ".. accountcode .."]" );


--Destination number string 
number_loop_str = number_loop(destination_number,'blocked_patterns') 

-- Do authorization
userinfo = doauthorization("number",accountcode,call_direction,destination_number,number_loop_str,config)

-- Unknown or deleted account (no row, doauthorization() logged it): reject it here.
-- The script went on and failed with a Lua error in the speed dial lookup (no account id), so the
-- call was not routed at all, without announcement.
if (userinfo['id'] == nil) then
	error_xml_without_cdr(destination_number,"ACCOUNT_INACTIVE_DELETED",calltype,config['playback_audio_notification'],0)
	return 0
end

--------------------------------------- SPEED DIAL --------------------------------------
--if(string.len(destination_number) == 1 ) then
	destination_number = get_speeddial_number(destination_number,userinfo['id'])
	Logger.info("[Dialplan] SPEED DIAL NUMBER : "..destination_number)
    
    -- Overriding call direction if speed dial destination is for DID or local extension 
    call_direction = define_call_direction(destination_number,accountcode,config)
    Logger.info("[Dialplan] New Call direction : ".. call_direction)
--end
-----------------------------------------------------------------------------------------

-- Apply the customer's origination localization (number_originate) here, before the DID
-- check, the blocked-prefix check and the package lookup. The number used to be translated only
-- further down, so a number dialled in national, 00 or + format never matched the package patterns
-- (call refused for lack of funds) or the blocked prefixes, and the CDR counted no package minutes.
local number_originate_applied = false
if (userinfo ~= nil and call_direction == 'outbound' and (tonumber(userinfo['localization_id']) or 0) > 0) then
	local early_localization = get_localization(userinfo['localization_id'],'O')
	if (early_localization and early_localization['number_originate'] ~= nil and early_localization['number_originate'] ~= '') then
		number_originate_applied = true
		local translated_number = do_number_translation((early_localization['number_originate']:gsub(" ", "")),destination_number)
		if (translated_number ~= destination_number) then
			Logger.info("[Dialplan] Localization : "..destination_number.." -> "..translated_number)
			destination_number = translated_number
			call_direction = define_call_direction(destination_number,accountcode,config)
			if (call_direction == 'outbound' and (userinfo['ACCOUNT_ERROR'] == '' or userinfo['ACCOUNT_ERROR'] == 'NO_SUFFICIENT_FUND')
				and check_blocked_prefix(userinfo,destination_number,number_loop(destination_number,'blocked_patterns')) == "false") then
				Logger.warning("[Dialplan] ["..accountcode.."] Destination blocked after localization : "..destination_number)
				userinfo['ACCOUNT_ERROR'] = 'DESTINATION_BLOCKED'
			end
		end
	end
end

-- @TODO : Need to confirm with Rushika for fraud feature
--Added for fraud detection checking
--if fraud_check then fraud_check(accountcode,destination_number) end

is_did_check = is_did(destination_number,config);
if (is_did_check ~= nil and is_did_check['id']) then
    Logger.info("[Dialplan] New Call direction HEREE : ".. call_direction)
	error_xml_without_cdr(destination_number,"NO_ROUTE_DESTINATION",calltype,config['playback_audio_notification'],userinfo['id'])
	return 0
end	

if(userinfo ~= nil) then
	if (didinfo ~= nil and didinfo['status']=='1') then 
		error_xml_without_cdr(destination_number,"UNALLOCATED_NUMBER",calltype,config['playback_audio_notification'],userinfo['id'])
		return 0
	end

	if(userinfo['ACCOUNT_ERROR'] == 'DESTINATION_BLOCKED') then
		error_xml_without_cdr(destination_number,"DESTINATION_BLOCKED",calltype,config['playback_audio_notification'],userinfo['id'])
		return 0
	end
	-- Expired and inactive accounts are rejected before the package lookup (they were
	-- checked after it: package_calculation() then failed with a Lua error, because balance is
	-- never set for such an account, and package minutes would even have cleared the account error)
    if(userinfo['ACCOUNT_ERROR'] == 'ACCOUNT_EXPIRE') then
		error_xml_without_cdr(destination_number,"ACCOUNT_EXPIRE",calltype,config['playback_audio_notification'],userinfo['id'])
		return 0
    end    
    if(userinfo['ACCOUNT_ERROR'] == 'ACCOUNT_INACTIVE_DELETED') then
		local accountid = 0
		if(userinfo['id'] and tonumber(userinfo['id']) > 0)then accountid = userinfo['id'] end
	Logger.debug("accountid : ".. accountid );
		error_xml_without_cdr(destination_number,"ACCOUNT_INACTIVE_DELETED",calltype,config['playback_audio_notification'],accountid)
		return 0
    end
    -- Get package information of customer	
	package_array = package_calculation (destination_number,userinfo,call_direction)

	if(userinfo['ACCOUNT_ERROR'] == 'NO_SUFFICIENT_FUND') then
		error_xml_without_cdr(destination_number,"NO_SUFFICIENT_FUND",calltype,config['playback_audio_notification'],userinfo['id'])
		return 0
	end

	-- Code for Prefix based routing to select rate group
	original_destination_number = destination_number	


    -- Get package information of customer	
-- Due to balance issue set this line before check balance	package_array = package_calculation (destination_number,userinfo,call_direction)
		
	userinfo = package_array[1]
	package_maxlength = package_array[2] or ""	
    -------------------------------------------------

	if(userinfo['ACCOUNT_ERROR'] == 'NO_SUFFICIENT_FUND') then
		error_xml_without_cdr(destination_number,"NO_SUFFICIENT_FUND",calltype,config['playback_audio_notification'],userinfo['id'])
		return 0
	end

	if(userinfo['local_call'] == '1' and call_direction == "local") then
        Logger.warning("[Functions] [DOAUTHORIZATION] ["..accountcode.."] LOCAL CALL IS DISABLE....!!");
		call_direction = 'outbound'
	end

end
--Check Ported number

if(call_direction == 'outbound')then
	if(addon_list  and addon_list['portednumber'] ~= '')then
		if get_ported_number then destination_number = get_ported_number(destination_number); end
	end
end

if (userinfo ~= nil) then  
    
	-- print customer information 
	Logger.info("=============== Account Information ===================")
	Logger.info("User id : "..userinfo['id'])  
	Logger.info("Account code : "..userinfo['number'])
	Logger.info("Balance : "..get_balance(userinfo,'',config))  
	Logger.info("Type : "..userinfo['posttoexternal'].." [0:prepaid,1:postpaid]")  
	Logger.info("Ratecard id : "..userinfo['pricelist_id'])  
	Logger.info("========================================================")    
    
    if (tonumber(userinfo['localization_id']) > 0) then
    	or_localization = get_localization(userinfo['localization_id'],'O')
    end

	-- If call is pstn and dialed modify defined then do number translation
	if (not number_originate_applied and call_direction == 'outbound' and tonumber(userinfo['localization_id']) > 0 and or_localization and or_localization['number_originate'] ~= nil) then
		or_localization['number_originate'] = or_localization['number_originate']:gsub(" ", "")			
		destination_number = do_number_translation(or_localization['number_originate'],destination_number)
	end
	
    -- If call is pstn and caller id translation defined then do caller id translation
	--Dhaval ASTPPENT-675(Localization - Outbound caller not apply from customer localization)
	if (or_localization and tonumber(userinfo['localization_id']) > 0 and or_localization['out_caller_id_originate'] ~= nil and call_direction ~= 'inbound') then        
		or_localization['out_caller_id_originate'] = or_localization['out_caller_id_originate']:gsub(" ", "")	
		callerid_array['original_cid_name'] = do_number_translation(or_localization['out_caller_id_originate'],callerid_array['cid_name'])
		callerid_array['original_cid_number'] = do_number_translation(or_localization['out_caller_id_originate'],callerid_array['cid_number'])  		 
		callerid_array['cid_name'] = do_number_translation(or_localization['out_caller_id_originate'],callerid_array['cid_name'])
		callerid_array['cid_number'] = do_number_translation(or_localization['out_caller_id_originate'],callerid_array['cid_number'])        
	end
    --END

	if(call_direction == 'inbound' and config['did_global_translation'] ~= nil and config['did_global_translation'] ~= '' and tonumber(config['did_global_translation']) > 0) then
		-- @TODO: Implement localization for DID global translation
		--destination_number = do_number_translation(config['did_global_translation'],destination_number)
		destination_number = didinfo['did_number']
	end     

  	number_loop_str = number_loop(destination_number)

	-- Fine max length of call based on origination rates.
	origination_array = get_call_maxlength(userinfo,destination_number,call_direction,number_loop_str,config,didinfo)
	    
	if( origination_array == 'NO_SUFFICIENT_FUND' or origination_array == 'ORIGNATION_RATE_NOT_FOUND') then
	    error_xml_without_cdr(destination_number,origination_array,calltype,config['playback_audio_notification'],userinfo['id']) 
	    return
	end
	
	maxlength = origination_array[1]
	user_rates = origination_array[2]
	xml_user_rates = origination_array[3] or ""

	-- If customer has free seconds then override max length variable with it. 
	if(package_maxlength ~= "") then	
		maxlength=package_maxlength
	end   
    
    -- Reseller validation starts
	local reseller_ids = {}
	local i = 1
    local reseller_cc_limit = ""
    
	--For live call report display
	-- livecall_data = userinfo['first_name'].."("..userinfo['number']..")|||"..user_rates['pattern'].." // "..user_rates['comment'].." // "..user_rates['cost']   
	 
	if(user_rates['trunk_id'] ~= nil) then
		livecall_data = userinfo['first_name'].."("..userinfo['number']..")|||"..user_rates['pattern'].." // "..user_rates['comment'].." // "..user_rates['cost'].." // trunk_id="..user_rates['trunk_id']   
	else
	 	livecall_data = userinfo['first_name'].."("..userinfo['number']..")|||"..user_rates['pattern'].." // "..user_rates['comment'].." // "..user_rates['cost']    
	end  
	
	------------

	-- Set customer information in new variable
	customer_userinfo = userinfo
	rate_carrier_id = user_rates['trunk_id']
	--~ Logger.notice("Rate carrier ID 555: "..rate_carrier_id)
	--For live call report display
	livecall_reseller = "x"
	------------
    
	while (tonumber(userinfo['reseller_id']) > 0 and tonumber(maxlength) > 0 ) do
		number_loop_str = number_loop(destination_number,'blocked_patterns') 
		Logger.notice("FINDING LIMIT FOR RESELLER: "..userinfo['reseller_id'])

		reseller_userinfo = doauthorization("id",userinfo['reseller_id'],call_direction,destination_number,number_loop_str,config)


        if(customer_userinfo['pricelist_id_admin'] ~="" and tonumber(customer_userinfo['pricelist_id_admin']) ~=0  and customer_userinfo['pricelist_id_admin'] ~= nil)then
				reseller_userinfo['pricelist_id']=customer_userinfo['pricelist_id_admin'];
			        if(tonumber(customer_userinfo['pricelist_id_admin']) ~= 0)then
					Logger.notice("[Prefix Base Routing] Replace customer_userinfo pricelist id: "..reseller_userinfo['pricelist_id'])
				end
		end		
        if(reseller_userinfo['ACCOUNT_ERROR'] == 'ACCOUNT_INACTIVE_DELETED') then
		    -- an account id, not the account table (was a Lua error in tonumber())
		    error_xml_without_cdr(destination_number,"ACCOUNT_INACTIVE_DELETED",calltype,config['playback_audio_notification'],customer_userinfo['id'])
		    return 0
	    end

	    -- @TODO: Need to remove package selection for reseller as we have changed concept of package usage
        -- Get package information of reseller
	    -- package_array = package_calculation (destination_number,reseller_userinfo,call_direction)
		

	    if(reseller_userinfo['ACCOUNT_ERROR'] == 'NO_SUFFICIENT_FUND') then
		    error_xml_without_cdr(destination_number,"NO_SUFFICIENT_FUND",calltype,config['playback_audio_notification'],reseller_userinfo['id'])
		    return 0
	    end

	    -- doauthorization() returns early for an expired reseller, before the blocked prefixes
	    -- are checked; the blocklist of the reseller applies anyway
	    if(reseller_userinfo['ACCOUNT_ERROR'] == 'ACCOUNT_EXPIRE' and call_direction == 'outbound'
	    		and check_blocked_prefix(reseller_userinfo,destination_number,number_loop_str) == "false") then
		    Logger.warning("[Dialplan] [Reseller "..reseller_userinfo['id'].."] Destination blocked (reseller expired) : "..destination_number)
		    reseller_userinfo['ACCOUNT_ERROR'] = 'DESTINATION_BLOCKED'
	    end

	    -- blocked prefixes of a reseller were looked up but never enforced
	    if(reseller_userinfo['ACCOUNT_ERROR'] == 'DESTINATION_BLOCKED') then
		    error_xml_without_cdr(destination_number,"DESTINATION_BLOCKED",calltype,config['playback_audio_notification'],reseller_userinfo['id'])
		    return 0
	    end

	    -- customers of an expired reseller are rejected like an expired customer (their calls
	    -- went through)
	    if(reseller_userinfo['ACCOUNT_ERROR'] == 'ACCOUNT_EXPIRE') then
		    error_xml_without_cdr(destination_number,"ACCOUNT_EXPIRE",calltype,config['playback_audio_notification'],reseller_userinfo['id'])
		    return 0
	    end
    
    	-- @TODO: Remove number translation / localization for reseller as we will apply localization to customer directly
		number_loop_str = number_loop(destination_number)
		reseller_ids[i] = reseller_userinfo
	    
	    --For live call report display
    	livecall_reseller = livecall_reseller.."//"..reseller_userinfo['id']
		--------------
	    
		-- print reseller information 
		Logger.info("=============== Reseller Information ===================")
		Logger.info("User id : "..reseller_userinfo['id'])  
		Logger.info("Account code : "..reseller_userinfo['number'])
		Logger.info("Balance : "..get_balance(reseller_userinfo))  
		Logger.info("Type : "..reseller_userinfo['posttoexternal'].." [0:prepaid,1:postpaid]")  
		Logger.info("Ratecard id : "..reseller_userinfo['pricelist_id'])  
		
		origination_array_reseller=get_call_maxlength(reseller_userinfo,destination_number,call_direction,number_loop_str,config,didinfo)

        if( origination_array_reseller == 'NO_SUFFICIENT_FUND' or origination_array_reseller == 'ORIGNATION_RATE_NOT_FOUND') then
            error_xml_without_cdr(destination_number,origination_array_reseller,calltype,1,customer_userinfo['id']) 
    	    return
    	end 

		reseller_maxlength = origination_array_reseller[1];
		reseller_rates = origination_array_reseller[2];
		xml_reseller_rates = origination_array_reseller[3];

		xml_user_rates = xml_user_rates.."||"..xml_reseller_rates
		Logger.info("Reseller xml_user_rates : "..xml_user_rates)  
		Logger.info("========================================================")  
        -- If reseller has free seconds then override max length variable with it. 
        if(package_maxlength ~= "") then	
	        xml_reseller_rates=package_maxlength
        end  

		if (tonumber(reseller_maxlength) < tonumber(maxlength)) then 
			maxlength = reseller_maxlength
		end

        -- ITPL : Added checkout for reseller concurrent calls.    
        if (tonumber(reseller_userinfo['maxchannels']) > 0 or tonumber(reseller_userinfo['cps']) > 0) then
            reseller_cc_limit = set_cc_limit_resellers(reseller_userinfo)
        end
        	--~ if (tonumber(reseller_maxlength) < 1 or tonumber(reseller_rates['cost']) > tonumber(user_rates['cost'])) then
		rate_carrier_id = reseller_rates['trunk_id']
		userinfo = reseller_userinfo
	end -- End while 
	
	--- Reseller validation ends
	if ( tonumber(maxlength) <= 0 ) then
	    error_xml_without_cdr(destination_number,"NO_SUFFICIENT_FUND",calltype,config['playback_audio_notification'],customer_userinfo['id']);
	end


	Logger.info("Call Max length duration : "..maxlength.." minutes")
	--say_timelimit(minutes) 
	livecall_data = livecall_reseller.."|||"..livecall_data
	local xml = {}
    
	
	-- Generate dialplan for call
	if (call_direction == 'inbound') then
		-- ********* Check RECEIVER Balance and status of the Account *************
		local dialuserinfo
		Logger.info("[userinfo] INB_FREE:" .. INB_FREE)
		Logger.info("[userinfo] free_inbound:" .. config['free_inbound'])
		--TODO INB FREE
		callerid = get_override_callerid(customer_userinfo,callerid_name,callerid_number)
		if (callerid['cid_name'] ~= nil) then
			callerid_array['cid_name'] = callerid['cid_name']
			callerid_array['cid_number'] = callerid['cid_number']
			callerid_array['original_cid_name'] = callerid['cid_name']
			callerid_array['original_cid_number'] = callerid['cid_number']
		end 

		dialuserinfo = doauthorization('id',didinfo['accountid'],call_direction,destination_number,number_loop,config)	
		-- ********* Check & get Dialer Rate card information *********
		origination_array_DID = ''
		if(tonumber(config['free_inbound']) == 0)then
			origination_array_DID = get_call_maxlength(customer_userinfo,destination_number,"outbound",number_loop_str,config)
		end
		local actual_userinfo = customer_userinfo
		Logger.info("[userinfo] Actual CustomerInfo XML:" .. actual_userinfo['id'])
		--customer_userinfo['id'] = didinfo['accountid'];
		if((origination_array_DID ~= 'ORIGNATION_RATE_NOT_FOUND' and origination_array_DID ~= 'NO_SUFFICIENT_FUND' and origination_array_DID[3] ~= nil) or tonumber(config['free_inbound']) == 1) then 
			Logger.info("[userinfo] Userinfo XML:" .. customer_userinfo['id']) 
			xml_did_rates = origination_array_DID[3]
			if(xml_did_rates == '' or xml_did_rates == nil)then xml_did_rates = 0 end
		else
			error_xml_without_cdr(destination_number,"ORIGNATION_RATE_NOT_FOUND",calltype,config['playback_audio_notification'],customer_userinfo['id'])
			return
		end
		-- ********* END *********
		-- ASTPPCOM-914 Ashish start
		if(tonumber(config['free_inbound']) == 0)then
		-- ASTPPCOM-914 Ashish end
			while (tonumber(customer_userinfo['reseller_id']) > 0  ) do 
				Logger.info("[WHILE DID CONDITION] FOR CHECKING RESELLER :" .. customer_userinfo['reseller_id']) 
				customer_userinfo = doauthorization('id',customer_userinfo['reseller_id'],call_direction,destination_number,number_loop,config)	
				origination_array_DID = get_call_maxlength(customer_userinfo,destination_number,"outbound",number_loop_str,config)

				if(origination_array_DID ~= 'ORIGNATION_RATE_NOT_FOUND' and origination_array_DID ~= 'NO_SUFFICIENT_FUND' and origination_array_DID[3] ~= nil) then 
					Logger.info("[userinfo] Userinfo XML:" .. customer_userinfo['id']) 
					xml_did_rates = xml_did_rates .."||"..origination_array_DID[3]
				else
					error_xml_without_cdr(destination_number,"ORIGNATION_RATE_NOT_FOUND",calltype,config['playback_audio_notification'],customer_userinfo['id'])
					return
				end
			end
		-- ASTPPCOM-914 Ashish start
		end
		-- ASTPPCOM-914 Ashish end
		-- ********* END *********
		Logger.info("[userinfo] Actual CustomerInfo XML : " .. actual_userinfo['id'])
		xml = freeswitch_xml_header(xml,destination_number,accountcode,maxlength,call_direction,accountname,xml_user_rates,actual_userinfo,config,xml_did_rates,nil,callerid_array,original_destination_number)
		if callerid_lookup then didinfo['callerid_number'] = callerid_lookup(params) end
		if(didinfo['extensions'] == '')then
			error_xml_without_cdr(destination_number,"NO_ROUTE_DESTINATION",calltype,config['playback_audio_notification'],customer_userinfo['id'])
			return
		end
		xml = freeswitch_xml_inbound(xml,didinfo,actual_userinfo,config,xml_did_rates,callerid_array,livecall_data)
		xml = freeswitch_xml_footer(xml)	   	    
		XML_STRING = table.concat(xml, "\n");
		Logger.debug("[Dialplan] Generated XML:" .. XML_STRING)  
	elseif (call_direction == 'local') then
		local SipDestinationInfo;
		SipDestinationInfo = check_local_call(destination_number)
		
		xml = freeswitch_xml_header(xml,destination_number,accountcode,maxlength,call_direction,accountname,xml_user_rates,customer_userinfo,config,nil,nil,callerid_array,original_destination_number)

		xml = freeswitch_xml_local(xml,destination_number,SipDestinationInfo,callerid_array,livecall_data)
		xml = freeswitch_xml_footer(xml)	   	    
		XML_STRING = table.concat(xml, "\n");
		Logger.debug("[Dialplan] Generated XML:\n" .. XML_STRING)  

	else		
		 force_outbound_routes =0;
   		 if(rate_carrier_id ~= nil and string.len(rate_carrier_id) >= 1) then
			Logger.info("[DIALPLAN] User Rate ID : ".. user_rates['id'])
			force_outbound_routes = user_rates['id']
		 end
		-- Get termination rates 
		termination_rates = get_carrier_rates (destination_number,number_loop_str,userinfo['pricelist_id'],rate_carrier_id,user_rates['routing_type'])
	
	if (termination_rates ~= nil) then
	    local i = 1
	    local carrier_array = {}
	    
--	    local k = 1

	    
	    for termination_key,termination_value in pairs(termination_rates) do
		--~ if ( tonumber(termination_value['cost']) > tonumber(user_rates['cost']) ) then		    
		    	--~ Logger.notice(termination_value['path']..": "..termination_value['cost'] .." > "..user_rates['cost']..", skipping")  
			    	
		if (tonumber(termination_value['cost']) > tonumber(user_rates['cost']) ) then	
			Logger.notice(termination_value['path']..": "..termination_value['cost'] .." > "..user_rates['cost']..", skipping for loss less routing")  
		--	k = k+1    	
		else
			Logger.info("=============== Termination Rates Information ===================")
			Logger.info("ID : "..termination_value['outbound_route_id'])  
			Logger.info("Code : "..termination_value['pattern'])  
			Logger.info("Destination : "..termination_value['comment'])  
			Logger.info("Connectcost : "..termination_value['connectcost'])  
			Logger.info("Free Seconds : "..termination_value['includedseconds'])  
			Logger.info("Prefix : "..termination_value['pattern'])      		    
			Logger.info("Strip : "..termination_value['strip'])      		  
			Logger.info("Prepend : "..termination_value['prepend'])      		  
			Logger.info("Carrier id : "..termination_value['trunk_id'])  		      		    
			Logger.info("carrier_name : "..termination_value['path'])
			Logger.info("dialplan_variable : "..termination_value['dialplan_variable']) 
			Logger.info("Failover gateway : "..termination_value['path1'])      		    
			Logger.info("Vendor id : "..termination_value['provider_id'])      		    		    			
			Logger.info("Max channels : "..termination_value['maxchannels'])	    		
			termination_value['trunk_name'] = termination_value['path'];
			Logger.info("Trunk Name : "..termination_value['trunk_name'])			
			termination_value['intcall']=customer_userinfo['international_call']
			Logger.info("========================END OF TERMINATION RATES=======================")
			carrier_array[i] = termination_value
			i = i+1
		end
	    end -- For EACH END HERE
	    
		-- If we get any valid carrier rates then build dialplan for outbound call
		 if (i > 1) then
		--if (i > 1 or k > 1) then
            callerid = get_override_callerid(customer_userinfo,callerid_name,callerid_number)
            if (callerid['cid_name'] ~= nil) then
                callerid_array['cid_name'] = callerid['cid_name']
                callerid_array['cid_number'] = callerid['cid_number']
                callerid_array['original_cid_name'] = callerid['cid_name']
                callerid_array['original_cid_number'] = callerid['cid_number']
            end 
			
			xml = freeswitch_xml_header(xml,destination_number,accountcode,maxlength,call_direction,accountname,xml_user_rates,customer_userinfo,config,nil,reseller_cc_limit,callerid_array,original_destination_number)

			-- Added code to override callerid
            --xml = override_callerid_management(xml,customer_userinfo)

			--~ for carrier_arr_key,carrier_arr_array in pairs(carrier_array) do
			    --~ xml = freeswitch_xml_outbound(xml,destination_number,carrier_arr_array,callerid_array,livecall_data)
			--~ end
			local j =1;
			rate_group_id = userinfo['pricelist_id']
			for carrier_arr_key,carrier_arr_array in pairs(carrier_array) do
				old_trunk_id =0
				if(j > 1) then
					old_trunk_id =carrier_array[tonumber(j)-1]['trunk_id']
				end
				rate_group_details = get_pricelists(userinfo)				
				xml = freeswitch_xml_outbound(xml,destination_number,carrier_arr_array,callerid_array,rate_group_id,old_trunk_id,force_outbound_routes,rate_group_details['routing_type'],livecall_data)
				j=j+1;
			end			

		    xml = freeswitch_xml_footer(xml)
		else
			-- If no route found for outbound call then send no result dialplan for further process in fs
			Logger.notice("[Dialplan] No termination rates found...!!!");
			error_xml_without_cdr(destination_number,"TERMINATION_RATE_NOT_FOUND",calltype,config['playback_audio_notification'],customer_userinfo['id']) 
			return
		end  --- IF ELSE END HERE
		XML_STRING = table.concat(xml, "\n");
		Logger.debug("[Dialplan] Generated XML:\n" .. XML_STRING)  
	else
		Logger.notice("[Dialplan] No termination rates found...!!!");
		error_xml_without_cdr(destination_number,"TERMINATION_RATE_NOT_FOUND",calltype,config['playback_audio_notification'],customer_userinfo['id']);
		return
	end
    end
else
	error_xml_without_cdr(destination_number,"ACCOUNT_INACTIVE_DELETED",calltype,config['playback_audio_notification'],customer_userinfo['id']);
	return
end
