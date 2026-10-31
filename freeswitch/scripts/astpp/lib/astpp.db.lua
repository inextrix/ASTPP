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

function db_connect()
	-- DB connection 
	dbh = freeswitch.Dbh("odbc://"..ODBC_DSN..":"..DB_USERNAME..":"..DB_PASSWD.."");
	--Logger.error ("odbc://"..ODBC_DSN..":"..DB_USERNAME..":"..DB_PASSWD.."")
	-- Check if DB connected or not
	if dbh:connected() == false then
	  Logger.error ("Database connection fail...!!!")
	  return
	end
end

-- Values never go into SQL text directly. The FreeSWITCH Lua Dbh has
-- no bind parameters, so statements are built by db_sql() from a template with typed placeholders:
--   ?s  text          -> MariaDB string literal in single quotes, escaped
--   ?i  integer       -> integer literal (optional sign, digits); anything else is refused
--   ?l  integer list  -> comma separated integers, for IN (...)
-- The template is code: never concatenate values, or SQL built from values, into it. Fragments
-- built from values (number_loop()) are appended outside db_sql(), so they are not scanned for
-- placeholders. A missing value or a non-integer for ?i/?l raises a Lua error and the request
-- fails instead of running a changed query.
-- Escaping as mysql_real_escape_string, except that a quote becomes '' : with NO_BACKSLASH_ESCAPES
-- on the server a value still cannot leave the literal (backslashes would only be doubled).
-- Needs an ASCII compatible connection charset (utf8 in /etc/odbc.ini; not GBK, Big5 or SJIS).
local db_escape_map = { ["\\"] = "\\\\", ["'"] = "''", ["\0"] = "\\0", ["\n"] = "\\n", ["\r"] = "\\r", ["\26"] = "\\Z" }

local function db_literal(kind, value)
	if value == nil then return nil, "missing" end
	if kind == "s" then
		if type(value) ~= "string" and type(value) ~= "number" then return nil, "not a text" end
		return "'" .. string.gsub(tostring(value), "[%c\\']", db_escape_map) .. "'"
	elseif kind == "i" then
		if type(value) == "number" then
			if value ~= value or value == math.huge or value == -math.huge or value ~= math.floor(value) then
				return nil, "not an integer"
			end
			return string.format("%d", value)
		end
		local digits = type(value) == "string" and string.match(value, "^%s*([+-]?%d+)%s*$")
		if not digits then return nil, "not an integer" end
		return digits
	end
	-- "l": comma separated integers
	if type(value) == "number" then return db_literal("i", value) end
	if type(value) ~= "string" then return nil, "not an integer list" end
	local items = {}
	for item in string.gmatch(value .. ",", "([^,]*),") do
		local literal = db_literal("i", item)
		if not literal then return nil, "not an integer list" end
		items[#items + 1] = literal
	end
	return table.concat(items, ",")
end

function db_sql(template, ...)
	local values, count, used = {...}, select("#", ...), 0
	local sql = string.gsub(template, "%?([sil])", function(kind)
		used = used + 1
		local literal, problem = db_literal(kind, values[used])
		if not literal then
			error("db_sql: value " .. used .. " (?" .. kind .. ") " .. problem .. " in: " .. string.sub(template, 1, 80), 0)
		end
		return literal
	end)
	if used ~= count then
		error("db_sql: " .. count .. " values for " .. used .. " placeholders in: " .. string.sub(template, 1, 80), 0)
	end
	return sql
end

-- Text value as escaped string literal, for SQL fragments built in loops (number_loop()).
function db_quote(value)
	local literal, problem = db_literal("s", value)
	if not literal then error("db_quote: value " .. problem, 2) end
	return literal
end
