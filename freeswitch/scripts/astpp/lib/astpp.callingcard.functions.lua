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

-- Authenticate calling card 
function auth_callingcard()

    local ani_flag = 0
    local cardnum = -1
    local cardinfo
    local pin=""
    local carddata
    local aniinfo

	if(config['cc_ani_auth'] == "0")  then 
		local ani_number = session:getVariable("caller_id_number")

		aniinfo = get_ani(ani_number);

		if(aniinfo ~= nil) then

		  if(tonumber(aniinfo['status']) == 1) then
			session:streamFile("astpp-callerid-blocked.wav")      
			session:hangup();
		  end   
		  cardinfo = get_account_by_id(aniinfo['accountid'])   
		  cardnum = cardinfo['number'];		 

		  local card_flag = validate_card_usage(cardinfo);
          if (card_flag==1) then
              error_xml_without_cdr("","ACCOUNT_EXPIRE","ASTPP-CALLINGCARD",config['playback_audio_notification'])
		      session:hangup();
		  end
		  ani_flag = 1
		end 
	end
	
	if (ani_flag == 0) then

		if(cardnum == -1) then
			local retries = "0"
	        local authenticated = 0			
			if(tonumber(config['card_retries'])) == 0 then config['card_retries'] =1 end
			if(tonumber(config['pin_retries'])) == 0 then config['pin_retries'] =1 end
			while (tonumber(authenticated) ~= 1 and tonumber(retries) < tonumber(config['card_retries'])) do
				cardnum = session:playAndGetDigits(1, 15, 1, config['calling_cards_number_input_timeout'], "#", "astpp-accountnum.wav", "", "^[0-9]+$")
				Logger.debug("Got DTMF digits: ".. cardnum )
			     if (cardnum ~= "") then		    
		        		cardinfo = get_account(cardnum);       
		    	     end
			     if(cardinfo == nil) then
					session:streamFile("astpp-badaccount.wav")      
	    		     else 
					authenticated = 1
					pin = cardinfo['pin']
		    	     end
	    		     retries = retries + 1
			end
			 if ( tonumber(authenticated) ~= 1 and tonumber(retries) == tonumber(config['card_retries']) ) then
				session:streamFile("astpp-goodbye.wav")      
				session:hangup();
		        end
		end
		if ( pin ~= "" ) then
			local retries = 0;
			local authenticated = 0;
			while (tonumber(authenticated) ~= 1 and tonumber(retries) < tonumber(config['pin_retries'])) do

				local pin_number = session:playAndGetDigits(1, 15, 1, config['calling_cards_pin_input_timeout'], "#", "astpp-pleasepin.wav", "", "^[0-9]+$")
				Logger.debug("We recieved a pin : ".. pin_number )

				if ( pin_number ~= pin ) then	
						session:streamFile("astpp-invalidpin.wav")      
				else 
						authenticated = 1
				end
				retries = retries + 1
			end

			if ( tonumber(authenticated) ~= 1 and tonumber(retries) == tonumber(config['pin_retries']) ) then
				session:streamFile("astpp-invalidpin")      
				session:hangup();
			end

		end 

	      -- Validate customer
	      local card_flag = validate_card_usage(cardinfo);
	      if (card_flag==1) then
		    error_xml_without_cdr("","ACCOUNT_EXPIRE","ASTPP-CALLINGCARD",config['playback_audio_notification'])
		    session:hangup();
	      end

         if(config['cc_ani_auth'] == "0")  then    
    	      --Ask for save callerid for pinless authentication
	          save_ani(cardinfo)
         end
	end 

	return cardinfo;
end


-- To save caller id for pinless authentication
function save_ani(cardinfo)
	local result = session:playAndGetDigits(1, 1, 1, 5000, "#", "astpp-register.wav", "", "^[1-1]+$")

    local callstart = os.date("!%Y-%m-%d %H:%M:%S")    
	if(tonumber(result) == 1) then
		local query = db_sql("INSERT INTO ani_map (number,accountid,status,creation_date,last_modified_date) VALUES (?s,?i,0,?s,?s)", session:getVariable("caller_id_number"), cardinfo['id'], callstart, callstart);
		Logger.debug("[SAVE_ANI] Query :" .. query)
		dbh:query(query);
	end
end
-- To get account by id
function get_account_by_id(accountid)
	local query = db_sql("SELECT *,(select currencyrate from currency where id=currency_id) as currencyrate FROM "..TBL_USERS.." WHERE id = ?i AND status=0 AND deleted=0 limit 1", accountid);
	
			Logger.debug("[DOAUTHORIZATION] Query :" .. query)
	
			local userinfo;
			assert (dbh:query(query, function(u)
					userinfo = u;
			end))
			return userinfo;
	
	
end

function get_account(accountcode)
	local query = db_sql("SELECT *,(select currencyrate from currency where id=currency_id) as currencyrate FROM "..TBL_USERS.." WHERE number = ?s AND status=0 AND deleted=0 limit 1", accountcode);

	Logger.debug("[DOAUTHORIZATION] Query :" .. query)

	local userinfo;
	assert (dbh:query(query, function(u)
		userinfo = u;	
	end))
	return userinfo;
end



-- Check DID info 
function get_ani(ani_number)
    
    -- The caller id used to go in unquoted, so MariaDB compared it as a number. Kept for
    -- numeric caller ids; anything else is compared as escaped text (was an SQL error, or
    -- SQL injected through the caller id).
    local ani_type = string.match(ani_number or "", "^%s*[+-]?%d+%s*$") and "?i" or "?s"
    local query = db_sql("SELECT * FROM ani_map WHERE number = "..ani_type, ani_number)
    Logger.debug("[CHECK_DID] Query :" .. query)  
    assert (dbh:query(query, function(u)
	ani = u;
    end))
    return ani;
end

--check if account is active or expire. 
function validate_card_usage(carddata) 

    -- Check a few things before saying the card is ok.
    if ( session:ready() ) then
        
        local callstart = os.date("!%Y-%m-%d %H:%M:%S")
    
        -- Now the card is in use and nobody else can use it.
        if ( carddata['first_used'] == "0000-00-00 00:00:00") then

            -- If "firstused" has not been set, we will set it now.
               local query = db_sql("UPDATE accounts SET first_used = now() WHERE id = ?i", carddata['id']);
               Logger.debug("[validate_card_usage] Query :" .. query)  
               dbh:query(query);

            if ( tonumber(carddata['validfordays']) > 0 ) then
                -- Check if the card is set to expire and deal with that as appropriate.            
                local query = db_sql("UPDATE accounts SET expiry = DATE_ADD(?s, INTERVAL ?i day) WHERE id = ?i", callstart, carddata['validfordays'], carddata['id'])
                Logger.debug("[validate_card_usage] Query :" .. query)
	            dbh:query(query);
                return 0
            end
    
        elseif ( tonumber(carddata['validfordays']) >= 0 ) then
        
                if (carddata['expiry'] == '0000-00-00 00:00:00') then
                    local query = db_sql("UPDATE accounts SET expiry = DATE_ADD(?s, INTERVAL ?i day) WHERE id = ?i", callstart, carddata['validfordays'], carddata['id'])
                    Logger.debug("[validate_card_usage] Query :" .. query)
    	            dbh:query(query);

                    local query = db_sql("SELECT DATE_ADD(?s, INTERVAL ?i day) AS expiry", callstart, carddata['validfordays'])
                    Logger.debug("[validate_card_usage] Query :" .. query)
                    assert (dbh:query(query, function(u)
                    	new_expiry = u
                    end))
                    carddata['expiry'] = new_expiry['expiry']
                end

                local query = db_sql("SELECT DATE_FORMAT(?s , '%Y%m%d%H%i%s') AS expiry", carddata['expiry'])
                Logger.debug("[validate_card_usage] Query :" .. query)
                assert (dbh:query(query, function(u)
                	expiry = u
                end))

                local query = db_sql("SELECT DATE_FORMAT(?s , '%Y%m%d%H%i%s') AS expiry", callstart)
                Logger.debug("[validate_card_usage] Query :" .. query)
                assert (dbh:query(query, function(u)
                	now = u
                end))
                
                if(tonumber(expiry['expiry']) <= tonumber(now['expiry'])) then
                    local query = db_sql("DELETE FROM ani_map WHERE accountid = ?i", carddata['id']);
	                dbh:query(query);
                    return 1
                end    
            
        elseif ( tonumber(carddata['validfordays']) < 0) then
           		 return 1
   	    end
        return 0
    end
end	

--Calculate balance 
-- function get_balance1(cardinfo)
--     if ( session:ready() ) then
--     	local balance = ((cardinfo['credit_limit'] * cardinfo['posttoexternal']) + cardinfo['balance'])
--     	return balance
--     else
--         return '0';
--     end
-- end

--IVR playback and option selection
function playback_ivr(userinfo)   
    local retries = 0;
    
    config['ivr_count'] = 3;

	while (tonumber(retries) < tonumber(config['ivr_count'])) do
		result = session:playAndGetDigits(1, 1, 2, config['calling_cards_number_input_timeout'], "#", "astpp-callingcard-menu.wav", "", "^[1-3]+$")
	    Logger.debug("Got DTMF digits: "..result .."retries:".. retries )
	     retries = retries + 1
	     if (tonumber(result) == 1) then
    		userinfo = get_account(userinfo['id']);     
			process_destination(userinfo);  
	     elseif (tonumber(result) == 2) then
            config['calling_cards_balance_announce'] = '0';
			local play_balance = get_balance(userinfo,'',config);
			userinfo['balance']=play_balance
			say_balance(userinfo); 
			retries = retries - 1   
	     elseif (tonumber(result) == 3) then
			session:streamFile( "astpp-goodbye.wav" );
        	session:hangup();
	     end	    		     
	end
end

-- Play balance of account   --Calculate and say the card balance.
function say_balance(cardinfo)
	local balance = get_balance(cardinfo,'',config)  
	if ( balance > 0 ) then
		if (tonumber(config['calling_cards_balance_announce']) == tonumber('0')) then
			session:streamFile( "astpp-this-card-has-a-balance-of.wav" )
			-- Doing currency conversion to play audio file in customer currency
			customer_balance = (balance * cardinfo['currencyrate'])
			play_amount(customer_balance)
		end	
	end
	return balance
end

-- Play amount audio file 
function play_amount(amount)
    amount_array = explode(".",amount)
    amount_first_part = amount_array[1]
    if (amount_array[2] == '0' or amount_array[2] == nil) then
        amount_array[2] = '00'
    end
    amount_second_part = string.sub(amount_array[2],0,2)
    Logger.debug("[Play Amount First Part] Query :" .. amount_first_part)
    Logger.debug("[Play Amount Second Part] Query :" .. amount_second_part)
    session:execute("say", "en number pronounced " ..  amount_first_part);
    session:streamFile( "astpp-point.wav" )
    if(tonumber(amount_second_part) > 0 and string.sub(amount_second_part,0,1) == '0') then
        session:execute("say", "en number pronounced " ..  string.sub(amount_second_part,0,1));
        session:execute("say", "en number pronounced " ..  string.sub(amount_second_part,1,2));
    else
        session:execute("say", "en number pronounced " ..  amount_second_part);
    end
end

--Say how much the call will cost.
function say_cost(numberinfo,customer_carddata)        
    if ( tonumber(config['calling_cards_rate_announce']) == tonumber('0') ) then
        
        if ( tonumber(numberinfo['cost']) > 0 ) then    
            session:streamFile( "astpp-willcost.wav" );
            -- Doing currency conversion to play audio file in customer currency
            or_cost = (numberinfo['cost'] * customer_carddata['currencyrate'])
		    play_amount(or_cost) 
            --------------------------------------------------------------------
            --session:execute("say", "en currency pronounced " ..  or_cost);
            session:streamFile( "astpp-per.wav" );
            session:streamFile( "astpp-minutes.wav" );
        end
        
        if ( tonumber(numberinfo['connectcost']) > 0 ) then
            session:streamFile( "astpp-connectcharge.wav" );
            -- Doing currency conversion to play audio file in customer currency
            or_connectcost = (numberinfo['connectcost'] * customer_carddata['currencyrate'])
		    play_amount(or_connectcost) 
            --------------------------------------------------------------------
            --session:execute("say", "en currency pronounced " ..  or_connectcost);
            session:streamFile( "astpp-willapply.wav" );
        end
    end
end

--Playback time limit for call
function say_timelimit(minutes) 
	if ( session:ready() ) then
	
	   Logger.debug("[SAY_TIMELIMIT] MINUTES "..tonumber(minutes));    
	    
	    if ( tonumber(minutes) > 0 and tonumber(config['calling_cards_timelimit_announce']) == tonumber('0') ) then
		session:streamFile( "astpp-this-call-will-last.wav" );
		if ( minutes == 1 ) then
		    session:execute(  "say", "en number pronounced " .. minutes );
		    session:streamFile( "astpp-minute.wav" );
		
		elseif ( minutes > 1 ) then
		    session:execute(  "say", "en number pronounced " .. minutes );
		    session:streamFile( "astpp-minutes.wav" );
		end
	    
	    elseif ( minutes < 1 ) then
		session:streamFile( "astpp-not-enough-credit.wav" );
		session:streamFile( "astpp-goodbye.wav" );
		session:hangup();
	    end
	end 
end


-- To termination call to destination. Have all termination calculation inside this function.
function dialout( original_destination_number, destination_number, maxlength, userinfo, user_rates, origination_dp_string ,number_loop_str,parentinfo,livecall_reseller)
	if ( session:ready() ) then

		termination_rates = get_carrier_rates (destination_number,number_loop_str,parentinfo['pricelist_id'],user_rates['trunk_id'],user_rates['routing_type'])
		if (termination_rates ~= nil) then
		    local i = 1
		    local termination_rates_array = {}
		    local xml_termination_rates
		    
		    local j = 1
            local loss_less_carrier_array = {}

		    
		    for termination_rate_key,termination_rate_value in pairs(termination_rates) do
			if ( tonumber(termination_rate_value['cost']) > tonumber(user_rates['cost']) ) then		    
			    Logger.notice(termination_rate_value['path']..": "..termination_rate_value['cost'] .." > "..user_rates['cost']..", skipping")  
			    loss_less_carrier_array[tonumber(termination_rate_value['cost'])] = termination_rate_value
                j = j+1
			else
			    Logger.info("=============== Termination Rates Information ===================")
			    Logger.info("ID : "..termination_rate_value['outbound_route_id'])  
			    Logger.info("Code : "..termination_rate_value['pattern'])  
			    Logger.info("Destination : "..termination_rate_value['comment'])  
			    Logger.info("Connectcost : "..termination_rate_value['connectcost'])  
			    Logger.info("Free Seconds : "..termination_rate_value['includedseconds'])  
			    Logger.info("Prefix : "..termination_rate_value['pattern'])      		    
			    Logger.info("Strip : "..termination_rate_value['strip'])      		  
			    Logger.info("Termination rate id : "..termination_rate_value['trunk_id'])  		      		    
			    Logger.info("Gateway name : "..termination_rate_value['path'])      
			    Logger.info("Failover gateway : "..termination_rate_value['path1'])      		    
			    Logger.info("Vendor id : "..termination_rate_value['provider_id'])      		    		    			    
			    Logger.info("Max channels : "..termination_rate_value['maxchannels'])
			    termination_rate_value['trunk_name'] = termination_rate_value['path'];
				Logger.info("trunk_name : "..termination_rate_value['trunk_name'])			
				termination_rate_value['intcall']=userinfo['international_call']
			    Logger.info("=================================================================")
			    termination_rates_array[i] = termination_rate_value
			    i = i+1
			end
	   	    end
	   	    
			if (tonumber(userinfo['loss_less_routing']) == 0 and j > 1) then  
				loss_less_table = {}
				for loss_less_carrier_arr_key in pairs(loss_less_carrier_array) do 
					table.insert(loss_less_table, loss_less_carrier_arr_key) 
				end

				table.sort(loss_less_table)

				for loss_less_table_key,loss_less_table_value in ipairs(loss_less_table) do 
					termination_rates_array[i]=loss_less_carrier_array[loss_less_table_value]
					i = i+1
				end
			end

		    -- If we get any valid termination rates then build dialplan for outbound call
		    if (i > 1) then
		        local callstart = os.date("!%Y-%m-%d %H:%M:%S")    
				session:execute("export","call_processed=internal");
				session:execute("export","callstart="..callstart);
				session:execute("export","originated_destination_number="..destination_number);
				session:execute("export","effective_destination_number="..original_destination_number);
				session:execute("set","continue_on_fail=true");
				session:execute("set","hangup_after_bridge=true");
				session:execute("export","account_id="..userinfo['id']);
				session:execute("export","account_type="..userinfo['type']);
				session:execute("export","resellerid="..userinfo['reseller_id']);
				session:execute("export","parent_id="..userinfo['reseller_id']);
				session:execute("export","accountcode="..userinfo['number']);
				session:execute("export","call_direction=outbound");    
				session:execute("export","calltype=CALLINGCARD");    
				session:execute("export","origination_rates="..origination_dp_string);
				session:execute("set", "execute_on_answer=sched_hangup +" .. (maxlength * 60 ) );
                		session:execute("set", "process_cdr=true" );
				session:execute("sched_hangup","+"..(maxlength * 60 ).. "" );
	    		   		    		    		
		    	calleridinfo = get_override_callerid(userinfo)
                callerid_array = {}			   
		    	if (calleridinfo['cid_number'] ~= nil) then
				    if (calleridinfo['cid_number'] ~= nil)  then	                        
					    session:execute("export", "original_caller_id_name="..calleridinfo['cid_name']);
                        callerid_array['cid_name'] = calleridinfo['cid_name']
				    else
                        session:execute("export", "original_caller_id_name="..calleridinfo['cid_number']);
                        callerid_array['cid_name'] = calleridinfo['cid_number']
                    end

				    if (calleridinfo['cid_number'] ~= '')  then				    	
                        session:execute("export", "original_caller_id_number="..calleridinfo['cid_number']);
                        callerid_array['cid_number'] = calleridinfo['cid_number']
				    end
                else
					-- ASTPPCOM-592 Ashish start
                    if (session:getVariable('effective_caller_id_name') ~= nil and session:getVariable('effective_caller_id_name') ~= '' and session:getVariable('effective_caller_id_number') ~= nil and session:getVariable('effective_caller_id_number') ~= '') then
						callerid_array['cid_name'] = session:getVariable("effective_caller_id_name")
	                    callerid_array['cid_number'] = session:getVariable("effective_caller_id_number")
					elseif((session:getVariable('effective_caller_id_number') ~= nil and session:getVariable('effective_caller_id_number') ~= '') or (session:getVariable('effective_caller_id_name') == nil and session:getVariable('effective_caller_id_name') == ''))then
						callerid_array['cid_name'] = session:getVariable("caller_id_name")
	                    callerid_array['cid_number'] = session:getVariable("effective_caller_id_number")
					elseif ((session:getVariable('effective_caller_id_name') ~= nil and session:getVariable('effective_caller_id_name') ~= '') or (session:getVariable('effective_caller_id_number') == nil and session:getVariable('effective_caller_id_number') == '')) then
						callerid_array['cid_name'] = session:getVariable("effective_caller_id_name")
	                    callerid_array['cid_number'] = session:getVariable("caller_id_number")
					else
						session:execute("export", "original_caller_id_name="..session:getVariable("caller_id_name"));        
						session:execute("export", "original_caller_id_number="..session:getVariable("caller_id_number"));
						callerid_array['cid_name'] = session:getVariable("caller_id_name")
						callerid_array['cid_number'] = session:getVariable("caller_id_number")
					end
					-- ASTPPCOM-592 Ashish end
		    	end
		        
                -- If call is pstn and caller id translation defined then do caller id translation 
	            -- if (userinfo['std_cid_translation'] ~= '') then
             --        Logger.info("[DIALPLAN] Caller ID Translation Starts")
		           --  callerid_array['cid_name'] = do_number_translation(userinfo['std_cid_translation'],callerid_array['cid_name'])
		           --  callerid_array['cid_number'] = do_number_translation(userinfo['std_cid_translation'],callerid_array['cid_number'])
             --        Logger.info("[DIALPLAN] Caller ID Translation Ends")
	            -- end

	             if (tonumber(userinfo['localization_id']) > 0) then
			    	or_localization = get_localization(userinfo['localization_id'],'O')
			    end

		       -- If call is pstn and caller id translation defined then do caller id translation 
				if (or_localization and tonumber(userinfo['localization_id']) > 0 and or_localization['out_caller_id_originate'] ~= nil) then			       
					callerid_array['cid_name'] = do_number_translation(or_localization['out_caller_id_originate'],callerid_array['cid_name'])
					callerid_array['cid_number'] = do_number_translation(or_localization['out_caller_id_originate'],callerid_array['cid_number'])			        
				end
		          
				for termination_rate_arr_key,termination_rate_arr_value in pairs(termination_rates_array) do
			
					livecall_data = livecall_reseller.."|||"..userinfo['first_name'].."("..userinfo['number']..")|||"..user_rates['pattern'].." // "..user_rates['comment'].." // "..user_rates['cost'].."|||"..termination_rate_arr_value['trunk_name'].." // "..termination_rate_arr_value['pattern'].." // "..termination_rate_arr_value['comment'].." // "..termination_rate_arr_value['cost']
					session:execute("export","presence_data="..livecall_data.."|||CC");

					local tr_localization=nil
					tr_localization = get_localization(termination_rate_arr_value['provider_id'],'T')

					
					if (tr_localization ~= nil) then
						-------------- Caller Id translation ---------					    
					    callerid_array['cid_name'] = do_number_translation(tr_localization['out_caller_id_terminate'],callerid_array['cid_name'])
						callerid_array['cid_number'] = do_number_translation(tr_localization['out_caller_id_terminate'],callerid_array['cid_number'])
				    	----------------------------------------------------------------------

				    	-------------- Destination number translation ---------
						destination_number = do_number_translation(tr_localization['number_terminate'],destination_number)
						-----------------------------------
						
					end
					-- ASTPPCOM-592 Ashish start
					session:execute("set", "effective_caller_id_name="..callerid_array['cid_name']);
					session:execute("set", "effective_caller_id_number="..callerid_array['cid_number']);
					session:execute("export", "effective_caller_id_name="..callerid_array['cid_name']);
					session:execute("export", "effective_caller_id_number="..callerid_array['cid_number']);
					-- ASTPPCOM-592 Ashish end
				
     --                -------------- CID translation for OUTBOUND calls ---------
     --                Logger.warning("[FSCC] Caller ID Translation Starts")
     --                callerid_array['cid_name'] = do_number_translation(termination_rate_arr_value['cid_translation'],callerid_array['cid_name'])
	    --             callerid_array['cid_number'] = do_number_translation(termination_rate_arr_value['cid_translation'],callerid_array['cid_number'])    
	    --             session:execute("export", "origination_caller_id_name="..callerid_array['cid_name']);
     --                session:execute("export", "origination_caller_id_number="..callerid_array['cid_number']);

     --                Logger.warning("[FSCC] Caller ID Translation Ends")
     --                ---------------------------------------------------------------------- 

					-- if (termination_rate_arr_value['dialed_modify'] ~= '') then 
					-- 	destination_number = do_number_translation(termination_rate_arr_value['dialed_modify'],destination_number)
					-- end
				    --Dhaval ASTPPCOM-651(Record Calling card calls)
					if(tonumber(userinfo['is_recording']) == 0)then
						session:execute("export", "is_recording=1");
						session:execute("export", "media_bug_answer_req=true");
						session:execute("export", "record_sample_rate=8000");
						session:execute("export", "execute_on_answer=record_session $${recordings_dir}/${uuid}.wav");
					end
					--END
					if(termination_rate_arr_value['prepend'] ~= '' or termination_rate_arr_value['strip'] ~= '') then
						destination_number = do_number_translation(termination_rate_arr_value['strip'].."/"..termination_rate_arr_value['prepend'],destination_number)
					end
	
                    if ( session:ready() ) then
					xml_termination_rates= "ID:"..termination_rate_arr_value['outbound_route_id'].."|CODE:"..termination_rate_arr_value['pattern'].."|DESTINATION:"..termination_rate_arr_value['comment'].."|CONNECTIONCOST:"..termination_rate_arr_value['connectcost'].."|INCLUDEDSECONDS:"..termination_rate_arr_value['includedseconds'].."|COST:"..termination_rate_arr_value['cost'].."|INC:"..termination_rate_arr_value['inc'].."|TRUNK:"..termination_rate_arr_value['trunk_id'].."|PROVIDER:"..termination_rate_arr_value['provider_id'];
					session:execute("export","termination_rates="..xml_termination_rates);
        				--//Pass package id in dialplan
					if (package_id and tonumber(package_id) > 0) then
					    	Logger.notice("::::::::::: package_id!!!"..package_id);
						session:execute("export","package_id="..package_id.."");
					end
					session:execute("export","trunk_id="..termination_rate_arr_value['trunk_id']);        
					session:execute("export","provider_id="..termination_rate_arr_value['provider_id']);
					session:execute("set","hangup_after_bridge=false");
					if (termination_rate_arr_value['codec'] ~= '') then 
							session:execute("export","absolute_codec_string="..termination_rate_arr_value['codec']);
					end
    
					if(tonumber(termination_rate_arr_value['maxchannels']) > 0) then    
							session:execute("limit_execute","db "..termination_rate_arr_value['path'].." gw_"..termination_rate_arr_value['path'].." "..termination_rate_arr_value['maxchannels'].." bridge [leg_timeout="..termination_rate_arr_value['leg_timeout'].."]sofia/gateway/"..termination_rate_arr_value['path'].."/"..destination_number);
					else
							session:execute("bridge","[leg_timeout="..termination_rate_arr_value['leg_timeout'].."]sofia/gateway/"..termination_rate_arr_value['path'].."/"..destination_number);   
					end

					if(termination_rate_arr_value['path1'] ~= '' and termination_rate_arr_value['path1'] ~= termination_rate_arr_value['gateway']) then
						session:execute("bridge","[leg_timeout="..termination_rate_arr_value['leg_timeout'].."]sofia/gateway/"..termination_rate_arr_value['path1'].."/"..destination_number);   
					end

					if(termination_rate_arr_value['path2'] ~= '' and termination_rate_arr_value['path2'] ~= termination_rate_arr_value['gateway']) then
						session:execute("bridge","[leg_timeout="..termination_rate_arr_value['leg_timeout'].."]sofia/gateway/"..termination_rate_arr_value['path2'].."/"..destination_number);   
					end

                    end
				end

			else
		    	-- If no route found for outbound call then send no result dialplan for further process in fs
		    	Logger.notice("No termination rates found!!!");
		        error_xml_without_cdr(destination_number,"TERMINATION_RATE_NOT_FOUND","ASTPP-CALLINGCARD",config['playback_audio_notification']) 
		    	return
		    end
	       end
        end
end

-- Get destination number and findout origination rates related logic inside this function
function process_destination(userinfo)
	call_direction = 'outbound' 	  
	local origination_dp_string

	local destination = session:playAndGetDigits(1, 35, 3, config['calling_cards_dial_input_timeout'], "#", "astpp-phonenum.wav", "astpp-badphone.wav", '^[0-9]+$')

	--------------------------------------- SPEED DIAL --------------------------------------
	if(string.len(destination) == 1 ) then
		Logger.info("SPEED DIAL NUMBER SECTION ")
		destination_number = get_speeddial_number(destination,userinfo['id'])
		Logger.info("SPEED DIAL NUMBER : "..destination)
	end

	-----------------------------------------------------------------------------------------

	Logger.info("[CHECK_destination] Dialed destination number :" .. destination)
	local callerid_array = {}
	callerid_array['original_cid_number'] = session:getVariable("caller_id_number")
	callerid_array['cid_number'] = session:getVariable("caller_id_number")

    -- @TODO : Need to confirm with Rushika for fraud feature
	--Added for fraud detection checking
	-- if fraud_check then fraud=fraud_check(accountcode,destination_number,calltype,config,userinfo)
	-- 	if fraud==0 then
	-- 		return 0	
	-- 	end
	-- end    

	-- print customer information 
	Logger.info("=============== Account Information ===================")
	Logger.info("User id : "..userinfo['id'])  
	Logger.info("Account code : "..userinfo['number'])
	Logger.info("Balance : "..get_balance(userinfo,'',config))  
	Logger.info("Type : "..userinfo['posttoexternal'].." [0:prepaid,1:postpaid]")  
	Logger.info("Ratecard id : "..userinfo['pricelist_id'])  
	Logger.info("========================================================")    

	local original_destination_number = destination	
    
    if (tonumber(userinfo['localization_id']) > 0) then
    	or_localization = get_localization(userinfo['localization_id'],'O')
    end

	-- If call is pstn and dialed modify defined then do number translation
	if (or_localization and tonumber(userinfo['localization_id']) > 0 and or_localization['number_originate'] ~= nil) then				
		destination = do_number_translation(or_localization['number_originate'],destination)
	end
	
    -- If call is pstn and caller id translation defined then do caller id translation 
	-- if (tonumber(userinfo['localization_id']) > 0 and or_localization['out_caller_id_originate'] ~= nil) then
 --       Logger.info("[DIALPLAN] Caller ID Translation Starts")		
	-- 	callerid_array['cid_number'] = do_number_translation(or_localization['out_caller_id_originate'],callerid_array['cid_number'])
 --        Logger.info("[DIALPLAN] Caller ID Translation Ends")
	-- end	

 	--Check if destination blocked
    local number_loop_string = number_loop(destination,'blocked_patterns')
    local block_prefix = check_blocked_prefix(userinfo, destination,number_loop_string);
    if(block_prefix == "false") then
	   		error_xml_without_cdr(destination,"DESTINATION_BLOCKED","ASTPP-CALLINGCARD",config['playback_audio_notification'],userinfo['id']) ;
	        return 
    end	

	local  number_loop_str = number_loop(destination)

	if (routing_prefix_rategroup ~= nil) then
        userinfo['pricelist_id']=routing_prefix_rategroup['id']
    end

    -- Get package information of customer	
	package_id = 0
	package_array = package_calculation (destination,userinfo,'outbound')
		
	userinfo = package_array[1]
	package_maxlength = package_array[2] or ""	
    -------------------------------------------------
    -- Check if account have balance
	balance = get_balance(userinfo,'',config);
	if (tonumber(balance) <= 0 and tonumber(package_id) == 0) then
		error_xml_without_cdr('CALLINGCARD',"NO_SUFFICIENT_FUND","ASTPP-CALLINGCARD",config['playback_audio_notification']) 
		session:streamFile( "astpp-goodbye.wav" );
		session:hangup();
	end
	-- Fine max length of call based on origination rates.
  	local tmp_array = get_call_maxlength(userinfo,destination,"",number_loop_str,config)
	    
    if( tmp_array == 'NO_SUFFICIENT_FUND' or tmp_array == 'ORIGNATION_RATE_NOT_FOUND') then
	    error_xml_without_cdr(destination,tmp_array,"ASTPP-CALLINGCARD",config['playback_audio_notification'],userinfo['id']) 
	    return
	end
	
	local  maxlength = tmp_array[1]
	local  user_rates = tmp_array[2]
	local  xml_user_rates = tmp_array[3] or ""
	
	if (user_rates['id'] == "" or  user_rates['id'] == nil) then
		return
	end

	-- If customer has free seconds then override max length variable with it. 
	if(package_maxlength ~= "") then	
		maxlength=package_maxlength
	end   

	local customer_origination_rates_info = user_rates; 
	local customer_carddata = userinfo	
	local origination_dp_string = xml_user_rates

	if(tonumber(userinfo['maxchannels']) > 0) then 	
		session:execute("limit","db "..userinfo['number'].." db_"..userinfo['number'].." "..userinfo['maxchannels']) 
	end

	local minimumcharge = user_rates['cost'];    
	local reseller_list;    
	local inc_reseller_list;

	local cust_pricelist_id = userinfo['pricelist_id'];
	local cust_accountid = userinfo['id'];
	local cust_resellerid = userinfo['reseller_id'];
	local cust_type = userinfo['type'];

	local reseller_ids = {}
	local i = 1
	livecall_reseller = "x"

	while (tonumber(userinfo['reseller_id']) > 0 and tonumber(maxlength) > 0 ) do 

		Logger.notice("FINDING LIMIT FOR RESELLER: "..userinfo['reseller_id'])
		reseller_userinfo = get_account(userinfo['reseller_id'])

        if (reseller_userinfo == nil) then
	    	    error_xml_without_cdr(destination,"ACCOUNT_INACTIVE_DELETED","ASTPP-CALLINGCARD",config['playback_audio_notification']); 
		        return
		else

		    local number_loop_string = number_loop(destination,'blocked_patterns') 
	        local block_prefix = check_blocked_prefix(reseller_userinfo, destination,number_loop_string);
		    if( block_prefix == false) then
			    Logger.notice("Reseller call should be blocked here")
			    error_xml_without_cdr(destination,"DESTINATION_BLOCKED","ASTPP-CALLINGCARD",config['playback_audio_notification']) ;
	            return
		    end
			    
	        -- If call is pstn and dialed modify defined then do number translation
		    if (reseller_userinfo['dialed_modify'] ~= '') then
		        destination = do_number_translation(reseller_userinfo['dialed_modify'],destination)
		    end    

	        number_loop_str = number_loop(destination)
	        reseller_ids[i] = reseller_userinfo
			
			livecall_reseller = livecall_reseller.."//"..reseller_userinfo['id'] 
			
	        -- print reseller information 
		    Logger.info("=============== Reseller Information ===================")
		    Logger.info("User id : "..reseller_userinfo['id'])  
		    Logger.info("Account code : "..reseller_userinfo['number'])
		    Logger.info("Balance : "..get_balance(reseller_userinfo,'',config))  
		    Logger.info("Type : "..reseller_userinfo['posttoexternal'].." [0:prepaid,1:postpaid]")  
		    Logger.info("Ratecard id : "..reseller_userinfo['pricelist_id'])  
		    Logger.info("========================================================")  
		        
		    tmp_array_reseller = get_call_maxlength(reseller_userinfo,destination,call_direction,number_loop_str,config)	    
		
            if( tmp_array_reseller == 'NO_SUFFICIENT_FUND' or tmp_array_reseller == 'ORIGNATION_RATE_NOT_FOUND') then
        	    error_xml_without_cdr(destination,tmp_array_reseller,"ASTPP-CALLINGCARD",1,reseller_userinfo['id']) 
        	    return
        	end
    
		    reseller_maxlength = tmp_array_reseller[1];
		    reseller_rates = tmp_array_reseller[2];
		    xml_reseller_rates = tmp_array_reseller[3];
	
		    origination_dp_string = origination_dp_string.."||"..xml_reseller_rates	

		    if (tonumber(reseller_maxlength) < tonumber(maxlength)) then 
	    		maxlength = reseller_maxlength
		    end
		    
		    if (userinfo['loss_less_routing'] == 1 and (tonumber(reseller_maxlength) < 1 or tonumber(reseller_rates['cost']) > tonumber(user_rates['cost']))) then
			    error_xml_without_cdr(destination,"RESELLER_COST_CHEAP","ASTPP-CALLINGCARD",config['playback_audio_notification']); 
		        Logger.info("Reseller cost : "..reseller_rates['cost'].." User cost : "..user_rates['cost'])
		    	Logger.notice("Reseller call price is cheaper, so we cannot allow call to process!!")
	    		return
		    end
		    rate_carrier_id = reseller_rates['trunk_id']
		    userinfo = reseller_userinfo
            if(tonumber(userinfo['maxchannels']) > 0) then
    			session:execute("limit","db "..userinfo['number'].." db_"..userinfo['number'].." "..userinfo['maxchannels']) 
    		end
		end		
    end -- End while 
	
	maxlength = math.floor(maxlength);
	Logger.notice(""..maxlength .." Minutes")

    --&error_xml_without_cdr($destination,"NO_SUFFICIENT_FUND") if($maxlength<=0);

    --  Congratulations, we now have a working card,pin, and phone number.
    say_cost( customer_origination_rates_info,customer_carddata )
    say_timelimit(maxlength)

    dialout( original_destination_number, destination, maxlength, customer_carddata, customer_origination_rates_info , origination_dp_string ,number_loop_str,userinfo,livecall_reseller);

end
