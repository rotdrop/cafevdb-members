DELIMITER $$
CREATE OR REPLACE FUNCTION MUSICIAN_USER_ID() RETURNS VARCHAR(256) CHARSET ascii
    NO SQL
    DETERMINISTIC
    SQL SECURITY INVOKER
BEGIN
  RETURN COALESCE(@CLOUD_USER_ID, SUBSTRING_INDEX(USER(), '@', 1));
END$$
DELIMITER ;

CREATE OR REPLACE
SQL SECURITY INVOKER
VIEW PersonalizedMusiciansView
AS
SELECT * FROM Musicians m
WHERE m.user_id_slug = MUSICIAN_USER_ID()
