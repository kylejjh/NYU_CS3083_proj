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
