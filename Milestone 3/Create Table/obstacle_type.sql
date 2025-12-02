CREATE TABLE obstacle_type (
  obstacle_type_id  INT AUTO_INCREMENT PRIMARY KEY,
  name              VARCHAR(50) NOT NULL,
  altitude          ENUM('GROUND','AIR') NOT NULL,
  width_px          INT NOT NULL,
  height_px         INT NOT NULL,
  CONSTRAINT chk_obstacle_size CHECK (width_px > 0 AND height_px > 0)
) ENGINE=InnoDB;
