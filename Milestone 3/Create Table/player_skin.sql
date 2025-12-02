CREATE TABLE player_skin (
  player_id    INT NOT NULL,
  skin_id      INT NOT NULL,
  acquired_at  DATETIME NOT NULL,
  source       ENUM('DEFAULT','ACHIEVEMENT','PURCHASE') NOT NULL,
  PRIMARY KEY (player_id, skin_id),
  CONSTRAINT fk_ps_player FOREIGN KEY (player_id) REFERENCES player(player_id),
  CONSTRAINT fk_ps_skin   FOREIGN KEY (skin_id)   REFERENCES skin(skin_id)
) ENGINE=InnoDB;
