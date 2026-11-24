-- ASTPP 6.3 (PHP 8.5 support, ASTPPCOM-1422): database changes.
-- Import after astpp-6.2.sql, on new and on existing installations.
-- Every statement can be run more than once.

UPDATE `sip_devices` SET `status` = 1 WHERE `username` = '4727985745' AND `dir_params` LIKE '%"password":"+AZ9X9cwwX@w-hyX"%';
UPDATE `accounts` SET `status` = 1 WHERE `number` = '4727985745' AND `password` = 'dOxAtqeO7E6A-wGlILTl-RLbYiySAwss';

-- Set footer version to 6.3
UPDATE `system` SET `value` = '6.3' WHERE `name` = 'version';
