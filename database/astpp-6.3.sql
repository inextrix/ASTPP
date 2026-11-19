-- ASTPP 6.3 (PHP 8.5 support, ASTPPCOM-1422): database changes.
-- Import after astpp-6.2.sql, on new and on existing installations.
-- Every statement can be run more than once.

-- Set footer version to 6.3
UPDATE `system` SET `value` = '6.3' WHERE `name` = 'version';
