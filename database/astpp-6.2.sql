-- ASTPP 6.0 security fixes: database changes.
-- Import after astpp-6.0.sql and astpp-6.0.1.sql, on new and on existing installations.
-- Every statement can be run more than once.

-- K7: the demo customer 4727985745 and its SIP device ship with passwords that are public
-- (repository). Disable them as long as they still use these passwords.
UPDATE `sip_devices` SET `status` = 1 WHERE `username` = '4727985745' AND `dir_params` LIKE '%"password":"+AZ9X9cwwX@w-hyX"%';
UPDATE `accounts` SET `status` = 1 WHERE `number` = '4727985745' AND `password` = 'dOxAtqeO7E6A-wGlILTl-RLbYiySAwss';

-- H18: PayPal payments. Every PayPal form is created on the server with the amount, the currency
-- and the receiver it expects; the IPN (pages/paypal_response) is verified with PayPal
-- (cmd=_notify-validate), compared with this row and booked once (txn_id unique, status 0 -> 1).
-- kind: order (ref_id = orders.id), topup, invoice (ref_id = invoices.id); credit = amount to book
-- in base currency.
CREATE TABLE IF NOT EXISTS `paypal_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` char(32) NOT NULL,
  `accountid` int(11) NOT NULL,
  `kind` varchar(10) NOT NULL,
  `ref_id` int(11) NOT NULL DEFAULT 0,
  `amount` decimal(20,5) NOT NULL,
  `currency` char(3) NOT NULL,
  `credit` decimal(20,5) NOT NULL,
  `receiver` varchar(127) NOT NULL,
  `receiver_reseller_id` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0-Pending,1-Booked',
  `txn_id` varchar(32) DEFAULT NULL,
  `created_date` datetime NOT NULL,
  `booked_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  UNIQUE KEY `txn_id` (`txn_id`),
  KEY `accountid` (`accountid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- M8: password reset codes (signup/confirmpassword and signup/reset_password), one row per account.
-- code_hash = HMAC-SHA256 of "accountid|code" with the ENCRYPTION_KEY ('' = used up), attempts
-- per code, issued = codes sent since window_start (limit per hour). The code used to be kept in
-- account_unverified, together with a copy of the account password.
CREATE TABLE IF NOT EXISTS `password_resets` (
  `accountid` int(11) NOT NULL,
  `code_hash` char(64) NOT NULL DEFAULT '',
  `created_date` datetime NOT NULL,
  `attempts` tinyint(4) NOT NULL DEFAULT 0,
  `issued` tinyint(4) NOT NULL DEFAULT 0,
  `window_start` datetime NOT NULL,
  `client_ip` varchar(45) NOT NULL DEFAULT '',
  PRIMARY KEY (`accountid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- M6: portal passwords as password_hash() (common::password_store()). An Argon2id hash has about
-- 100 characters (bcrypt 60), the columns held only the Blowfish value. Widened once (the check
-- keeps a later import from rebuilding the table). Old Blowfish values stay valid and are replaced
-- by a hash at the next successful login of the account.
SET @astpp_sql = IF((SELECT CHARACTER_MAXIMUM_LENGTH FROM information_schema.COLUMNS
                      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'accounts' AND COLUMN_NAME = 'password') < 255,
                    'ALTER TABLE accounts MODIFY `password` varchar(255) NOT NULL DEFAULT \'\'', 'DO 0');
PREPARE astpp_stmt FROM @astpp_sql;
EXECUTE astpp_stmt;
DEALLOCATE PREPARE astpp_stmt;
SET @astpp_sql = IF((SELECT CHARACTER_MAXIMUM_LENGTH FROM information_schema.COLUMNS
                      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'account_unverified' AND COLUMN_NAME = 'password') < 255,
                    'ALTER TABLE account_unverified MODIFY `password` varchar(255) NOT NULL', 'DO 0');
PREPARE astpp_stmt FROM @astpp_sql;
EXECUTE astpp_stmt;
DEALLOCATE PREPARE astpp_stmt;
-- The old "forgot password" flow copied the Blowfish password of the account into
-- account_unverified, signups stored their generated password there (both readable with the
-- PRIVATE_KEY). Neither is read any more: a signup account gets its password through the link in
-- the welcome mail. "!" matches no password (common::password_locked()).
UPDATE account_unverified SET password = '!' WHERE password <> '!' AND password NOT LIKE '$%';

-- F5 (functional): the country row for +372 (ISO EE, capital Tallinn) was labelled ERITREA, so
-- Estonia was missing from every country drop-down and Eritrea appeared twice.
UPDATE countrycode SET country = 'ESTONIA' WHERE id = 61 AND iso = 'EE' AND countrycode = 372 AND country = 'ERITREA';
-- Set footer version to 6.2
UPDATE `system` SET `value` = '6.2' WHERE `name` = 'version';
