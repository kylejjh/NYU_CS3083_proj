CREATE TABLE season (
  season_id   INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(50) NOT NULL,
  start_date  DATE NOT NULL,
  end_date    DATE NOT NULL,
  is_active   BOOLEAN NOT NULL DEFAULT 0,
  CONSTRAINT chk_season_dates CHECK (start_date < end_date)
) ENGINE=InnoDB;
