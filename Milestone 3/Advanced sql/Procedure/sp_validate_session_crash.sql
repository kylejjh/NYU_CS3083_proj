
-- USE dino_runner;
DROP PROCEDURE IF EXISTS sp_validate_session_crash;


DELIMITER //
CREATE PROCEDURE sp_validate_session_crash(IN p_session_id INT)
BEGIN
  DECLARE v_crash VARCHAR(10);
  DECLARE v_obst  INT;
  DECLARE v_uncleared INT;
  DECLARE v_match     INT;

  SELECT crash_type, obstacle_type_id
    INTO v_crash, v_obst
  FROM session
  WHERE session_id = p_session_id;

  IF v_crash IS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Session not found';
  END IF;

  IF v_crash = 'COLLIDE' THEN
    SELECT SUM(CASE WHEN cleared = 0 THEN 1 ELSE 0 END)
      INTO v_uncleared
    FROM obstacle_spawn
    WHERE session_id = p_session_id;

    IF v_uncleared <> 1 THEN
      SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'COLLIDE must have exactly one uncleared spawn';
    END IF;

    SELECT COUNT(*)
      INTO v_match
    FROM obstacle_spawn
    WHERE session_id = p_session_id
      AND cleared = 0
      AND obstacle_type_id = v_obst;

    IF v_match <> 1 THEN
      SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Crash obstacle_type_id mismatch';
    END IF;

  ELSE
    SELECT SUM(CASE WHEN cleared = 0 THEN 1 ELSE 0 END)
      INTO v_uncleared
    FROM obstacle_spawn
    WHERE session_id = p_session_id;

    IF v_uncleared <> 0 THEN
      SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Non-collide session cannot contain uncleared spawns';
    END IF;
  END IF;
END//
DELIMITER ;
-- CALL sp_validate_session_crash(10);
