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
