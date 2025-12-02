CREATE TABLE input_event (
  input_event_id  INT AUTO_INCREMENT PRIMARY KEY,
  session_id      INT NOT NULL,
  t_offset_ms     INT NOT NULL,
  action          ENUM('JUMP','DUCK','PAUSE','RESUME') NOT NULL,
  source          ENUM('KEYBOARD','TOUCH') NOT NULL DEFAULT 'KEYBOARD',
  CONSTRAINT fk_ie_session FOREIGN KEY (session_id) REFERENCES session(session_id),
  CONSTRAINT chk_ie_t CHECK (t_offset_ms >= 0)
) ENGINE=InnoDB;
