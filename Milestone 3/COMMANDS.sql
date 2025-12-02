CREATE DATABASE IF NOT EXISTS slime_runner_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE slime_runner_db;



CREATE TABLE player (
  player_id      INT AUTO_INCREMENT PRIMARY KEY,
  username       VARCHAR(50) NOT NULL,
  email          VARCHAR(100) UNIQUE,
  password       VARCHAR(200),
  account_type   ENUM('REGISTERED','GUEST') NOT NULL,
  created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_login_at  DATETIME NULL,
  CONSTRAINT uq_player_username UNIQUE (username),
  CONSTRAINT chk_player_account
    CHECK (
      (account_type='REGISTERED' AND email IS NOT NULL AND password IS NOT NULL)
      OR
      (account_type='GUEST' AND email IS NULL AND password IS NULL)
    )
) ENGINE=InnoDB;


INSERT INTO player (username, email, password, account_type, created_at)
VALUES
 ('slimeMaster','sm@demo.com','hashA','REGISTERED','2025-01-05 10:00:00'),
 ('dinoKing','dk@demo.com','hashB','REGISTERED','2025-01-06 10:00:00'),
 ('runner01','r1@demo.com','hashC','REGISTERED','2025-01-07 10:00:00'),
 ('runner02','r2@demo.com','hashD','REGISTERED','2025-01-08 10:00:00'),
 ('runner03','r3@demo.com','hashE','REGISTERED','2025-01-09 10:00:00'),
 ('guest101',NULL,NULL,'GUEST','2025-01-10 10:00:00'),
 ('guest102',NULL,NULL,'GUEST','2025-01-11 10:00:00'),
 ('guest103',NULL,NULL,'GUEST','2025-01-12 10:00:00'),
 ('guest104',NULL,NULL,'GUEST','2025-01-13 10:00:00'),
 ('guest105',NULL,NULL,'GUEST','2025-01-14 10:00:00');




CREATE TABLE season (
  season_id   INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(50) NOT NULL,
  start_date  DATE NOT NULL,
  end_date    DATE NOT NULL,
  is_active   BOOLEAN NOT NULL DEFAULT 0,
  CONSTRAINT chk_season_dates CHECK (start_date < end_date)
) ENGINE=InnoDB;


INSERT INTO season (name, start_date, end_date, is_active) VALUES
 ('Season 1','2025-01-01','2025-01-31',0),
 ('Season 2','2025-02-01','2025-02-28',0),
 ('Season 3','2025-03-01','2025-03-31',0),
 ('Season 4','2025-04-01','2025-04-30',0),
 ('Season 5','2025-05-01','2025-05-31',0),
 ('Season 6','2025-06-01','2025-06-30',0),
 ('Season 7','2025-07-01','2025-07-31',0),
 ('Season 8','2025-08-01','2025-08-31',0),
 ('Season 9','2025-09-01','2025-09-30',0),
 ('Season 10','2025-10-01','2025-10-31',1);




CREATE TABLE skin (
  skin_id     INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(50) NOT NULL,
  rarity      ENUM('common','rare','epic','legendary') NOT NULL DEFAULT 'common',
  is_default  BOOLEAN NOT NULL DEFAULT 0
) ENGINE=InnoDB;


INSERT INTO skin (name, rarity, is_default) VALUES
 ('Classic', 'common', 1),
 ('Desert Runner', 'rare', 0),
 ('Night Stalker', 'rare', 0),
 ('Cyber Slime', 'epic', 0),
 ('Forest Guard', 'common', 0),
 ('Lava Beast', 'epic', 0),
 ('Snow Scout', 'common', 0),
 ('Golden King', 'legendary', 0),
 ('Aero Swift', 'rare', 0),
 ('Shadow Ninja', 'epic', 0);
 



CREATE TABLE player_skin (
  player_id    INT NOT NULL,
  skin_id      INT NOT NULL,
  acquired_at  DATETIME NOT NULL,
  source       ENUM('DEFAULT','ACHIEVEMENT','PURCHASE') NOT NULL,
  PRIMARY KEY (player_id, skin_id),
  CONSTRAINT fk_ps_player FOREIGN KEY (player_id) REFERENCES player(player_id),
  CONSTRAINT fk_ps_skin   FOREIGN KEY (skin_id)   REFERENCES skin(skin_id)
) ENGINE=InnoDB;


INSERT INTO player_skin (player_id, skin_id, acquired_at, source) VALUES
 (1,1,'2025-01-05 10:00:00','DEFAULT'), (2,1,'2025-01-06 10:00:00','DEFAULT'),
 (3,1,'2025-01-07 10:00:00','DEFAULT'), (4,1,'2025-01-08 10:00:00','DEFAULT'),
 (5,1,'2025-01-09 10:00:00','DEFAULT'), (6,1,'2025-01-10 10:00:00','DEFAULT'),
 (7,1,'2025-01-11 10:00:00','DEFAULT'), (8,1,'2025-01-12 10:00:00','DEFAULT'),
 (9,1,'2025-01-13 10:00:00','DEFAULT'), (10,1,'2025-01-14 10:00:00','DEFAULT'),
 (1,2,'2025-02-01 09:00:00','ACHIEVEMENT'),
 (2,3,'2025-02-01 09:05:00','PURCHASE'),
 (3,4,'2025-02-02 12:00:00','ACHIEVEMENT'),
 (4,5,'2025-02-03 12:00:00','PURCHASE'),
 (5,6,'2025-02-04 12:00:00','PURCHASE');
 
 
 CREATE TABLE obstacle_type (
  obstacle_type_id  INT AUTO_INCREMENT PRIMARY KEY,
  name              VARCHAR(50) NOT NULL,
  altitude          ENUM('GROUND','AIR') NOT NULL,
  width_px          INT NOT NULL,
  height_px         INT NOT NULL,
  CONSTRAINT chk_obstacle_size CHECK (width_px > 0 AND height_px > 0)
) ENGINE=InnoDB;


INSERT INTO obstacle_type (name, altitude, width_px, height_px) VALUES
 ('Cactus Small','GROUND',20,30),
 ('Cactus Tall','GROUND',25,50),
 ('Bird Low','AIR',35,25),
 ('Bird High','AIR',35,25),
 ('Rock','GROUND',30,20),
 ('Pit','GROUND',50,1),
 ('Boulder','GROUND',40,40),
 ('UFO','AIR',45,20),
 ('Fence','GROUND',30,25),
 ('Drone','AIR',30,15);
 
 
 

CREATE TABLE session (
  session_id        INT AUTO_INCREMENT PRIMARY KEY,
  player_id         INT NOT NULL,
  season_id         INT NOT NULL,
  skin_id           INT NULL,
  obstacle_type_id  INT NULL,
  started_at        DATETIME NOT NULL,
  ended_at          DATETIME NULL,
  
  -- duration in ms: computed when ended_at is set
  duration_ms       BIGINT AS (
                       CASE
                         WHEN ended_at IS NULL THEN NULL
                         ELSE TIMESTAMPDIFF(MICROSECOND, started_at, ended_at) DIV 1000
                       END
                     ) STORED,
  
  score             INT NOT NULL DEFAULT 0,
  distance_m        INT NOT NULL DEFAULT 0,
  top_speed         DECIMAL(6,2) NOT NULL DEFAULT 0.00,
  crash_type        ENUM('COLLIDE','QUIT','TIMEOUT') NULL,
  is_offline        BOOLEAN NOT NULL DEFAULT 0,
  device_type       VARCHAR(30) NOT NULL DEFAULT 'browser',
  seed              BIGINT NOT NULL,
  CONSTRAINT fk_sess_player  FOREIGN KEY (player_id)         REFERENCES player(player_id),
  CONSTRAINT fk_sess_season  FOREIGN KEY (season_id)         REFERENCES season(season_id),
  CONSTRAINT fk_sess_skin    FOREIGN KEY (skin_id)           REFERENCES skin(skin_id),
  CONSTRAINT fk_sess_crash   FOREIGN KEY (obstacle_type_id)  REFERENCES obstacle_type(obstacle_type_id),
  CONSTRAINT chk_nonnegatives CHECK (score >= 0 AND distance_m >= 0 AND top_speed >= 0),
  CONSTRAINT chk_ended_after_start CHECK (ended_at IS NULL OR ended_at >= started_at)
) ENGINE=InnoDB;


INSERT INTO session
(player_id, season_id, skin_id, obstacle_type_id, started_at, ended_at, score, distance_m, top_speed, crash_type, is_offline, device_type, seed)
VALUES
 (1,3,2,NULL,'2025-03-01 10:00:00','2025-03-01 10:05:00',4850,980,23.5,'QUIT',0,'browser',1001),
 (2,3,3,NULL,'2025-03-02 10:00:00','2025-03-02 10:04:00',4600,900,22.8,'QUIT',0,'browser',1002),
 (3,3,4,NULL,'2025-03-03 11:00:00','2025-03-03 11:06:00',4300,870,22.1,'TIMEOUT',0,'browser',1003),
 (4,3,5,NULL,'2025-03-04 12:00:00','2025-03-04 12:03:00',2500,500,20.0,'QUIT',0,'browser',1004),
 (5,3,6,NULL,'2025-03-05 12:00:00','2025-03-05 12:02:30',2350,480,19.0,'QUIT',0,'browser',1005),
 (6,3,1,NULL,'2025-03-06 13:00:00','2025-03-06 13:05:30',2100,700,21.2,'TIMEOUT',1,'browser',1006),
 (7,3,1,NULL,'2025-03-07 14:00:00','2025-03-07 14:03:30',1900,450,18.0,'QUIT',0,'browser',1007),
 (8,3,1,NULL,'2025-03-08 15:20:00','2025-03-08 15:24:00',1700,420,17.5,'QUIT',0,'browser',1008),
 (9,3,1,NULL,'2025-03-09 16:00:00','2025-03-09 16:05:00',2500,600,20.5,'TIMEOUT',0,'browser',1009),
 -- one COLLIDE case: obstacle_type_id must be set, and exactly one uncleared spawn will be added below
 (10,3,1,1,'2025-03-10 17:00:00','2025-03-10 17:02:00',1600,300,16.0,'COLLIDE',0,'browser',1010);

 



CREATE TABLE obstacle_spawn (
  spawn_id          INT AUTO_INCREMENT PRIMARY KEY,
  session_id        INT NOT NULL,
  obstacle_type_id  INT NOT NULL,
  t_offset_ms       INT NOT NULL,
  speed_at_spawn    DECIMAL(6,2) NOT NULL,
  cleared           BOOLEAN NOT NULL,
  CONSTRAINT fk_os_session  FOREIGN KEY (session_id)        REFERENCES session(session_id),
  CONSTRAINT fk_os_type     FOREIGN KEY (obstacle_type_id)  REFERENCES obstacle_type(obstacle_type_id),
  CONSTRAINT chk_spawn_speed CHECK (speed_at_spawn >= 0),
  CONSTRAINT chk_spawn_t    CHECK (t_offset_ms >= 0)
) ENGINE=InnoDB;


INSERT INTO obstacle_spawn (session_id, obstacle_type_id, t_offset_ms, speed_at_spawn, cleared) VALUES
 (1,1, 350, 20.0, 1),
 (2,2, 420, 21.0, 1),
 (3,3, 680, 22.0, 1),
 (4,4, 900, 23.0, 1),
 (5,5, 300, 18.0, 1),
 (6,6, 450, 19.0, 1),
 (7,7, 700, 20.0, 1),
 (8,8, 820, 21.0, 1),
 (9,9, 950, 22.0, 1),
 (10,1, 1020, 22.0, 0);
 
 


CREATE TABLE input_event (
  input_event_id  INT AUTO_INCREMENT PRIMARY KEY,
  session_id      INT NOT NULL,
  t_offset_ms     INT NOT NULL,
  action          ENUM('JUMP','DUCK','PAUSE','RESUME') NOT NULL,
  source          ENUM('KEYBOARD','TOUCH') NOT NULL DEFAULT 'KEYBOARD',
  CONSTRAINT fk_ie_session FOREIGN KEY (session_id) REFERENCES session(session_id),
  CONSTRAINT chk_ie_t CHECK (t_offset_ms >= 0)
) ENGINE=InnoDB;


INSERT INTO input_event (session_id, t_offset_ms, action, source) VALUES
 (1,670,'JUMP','KEYBOARD'),
 (2,500,'DUCK','KEYBOARD'),
 (3,1000,'JUMP','KEYBOARD'),
 (4,250,'JUMP','KEYBOARD'),
 (5,600,'PAUSE','KEYBOARD'),
 (6,650,'RESUME','KEYBOARD'),
 (7,300,'JUMP','KEYBOARD'),
 (8,700,'DUCK','KEYBOARD'),
 (9,1200,'JUMP','KEYBOARD'),
 (10,1010,'JUMP','KEYBOARD');
 
 


CREATE TABLE achievement (
  achievement_id  INT AUTO_INCREMENT PRIMARY KEY,
  name            VARCHAR(80) NOT NULL,
  description     VARCHAR(255) NOT NULL
) ENGINE=InnoDB;


INSERT INTO achievement (name, description) VALUES
 ('Marathon Runner','Run 1000m without collision'),
 ('Speedster','Reach top speed >= 23.0'),
 ('Survivor','Play 5 minutes without crashing'),
 ('Perfect Jump','Jump over 5 consecutive obstacles'),
 ('Night Owl','Play a run after 11pm'),
 ('Collector','Own 5 different skins'),
 ('Legendary Look','Use a legendary skin in a run'),
 ('No Pause','Finish a run with no pauses'),
 ('Air Master','Clear 5 AIR obstacles'),
 ('Ground Crusher','Clear 5 GROUND obstacles');
 



CREATE TABLE player_achievement (
  player_id       INT NOT NULL,
  achievement_id  INT NOT NULL,
  session_id      INT NOT NULL,
  unlocked_at     DATETIME NOT NULL,
  PRIMARY KEY (player_id, achievement_id),
  CONSTRAINT fk_pa_player   FOREIGN KEY (player_id)       REFERENCES player(player_id),
  CONSTRAINT fk_pa_ach      FOREIGN KEY (achievement_id)  REFERENCES achievement(achievement_id),
  CONSTRAINT fk_pa_session  FOREIGN KEY (session_id)      REFERENCES session(session_id)
) ENGINE=InnoDB;


INSERT INTO player_achievement (player_id, achievement_id, session_id, unlocked_at) VALUES
 (1,1,1,'2025-03-01 10:05:00'),
 (1,2,1,'2025-03-01 10:05:00'),
 (2,1,2,'2025-03-02 10:04:00'),
 (3,3,3,'2025-03-03 11:06:00'),
 (4,8,4,'2025-03-04 12:03:00'),
 (5,5,5,'2025-03-05 12:02:30'),
 (6,3,6,'2025-03-06 13:05:30'),
 (7,10,7,'2025-03-07 14:03:30'),
 (8,9,8,'2025-03-08 15:24:00'),
 (9,4,9,'2025-03-09 16:05:00');
 
 
 

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
