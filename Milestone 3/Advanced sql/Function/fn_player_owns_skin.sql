DELIMITER //
CREATE FUNCTION fn_player_owns_skin(p_player_id INT, p_skin_id INT, p_at DATETIME)
RETURNS BOOLEAN
DETERMINISTIC
BEGIN
  DECLARE has_default BOOLEAN;
  DECLARE owned_count INT;

  -- default skin is globally owned
  SELECT is_default INTO has_default FROM skin WHERE skin_id = p_skin_id;
  IF has_default = 1 THEN
    RETURN TRUE;
  END IF;

  SELECT COUNT(*) INTO owned_count
    FROM player_skin
   WHERE player_id = p_player_id
     AND skin_id = p_skin_id
     AND acquired_at <= p_at;

  RETURN owned_count > 0;
END//
DELIMITER ;

