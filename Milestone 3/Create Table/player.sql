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
