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
