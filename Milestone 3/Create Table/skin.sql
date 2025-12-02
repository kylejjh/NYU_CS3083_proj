CREATE TABLE skin (
  skin_id     INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(50) NOT NULL,
  rarity      ENUM('common','rare','epic','legendary') NOT NULL DEFAULT 'common',
  is_default  BOOLEAN NOT NULL DEFAULT 0
) ENGINE=InnoDB;
