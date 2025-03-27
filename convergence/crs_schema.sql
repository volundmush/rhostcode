CREATE TABLE IF NOT EXISTS meter (
    meter_id INT AUTO_INCREMENT PRIMARY KEY,
    scene_meter_id INT NOT NULL,
    scene_id INT NOT NULL,
    UNIQUE(scene_id,meter_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS meter_history (
    meter_history_id INT AUTO_INCREMENT PRIMARY KEY,
    meter_id INT NOT NULL,
    meter_name VARCHAR(255) NOT NULL,
    meter_value INT NOT NULL DEFAULT 0,
    meter_target_number INT NOT NULL DEFAULT 5,
    meter_size INT NOT NULL DEFAULT 10,
    meter_type INT NOT NULL DEFAULT 0,
    meter_valid_from DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    meter_valid_to DATETIME NULL DEFAULT NULL,
    meter_active TINYINT NOT NULL DEFAULT 1,
    INDEX(meter_id, meter_valid_from, meter_valid_to),
    FOREIGN KEY (meter_id) REFERENCES meter(meter_id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE OR REPLACE VIEW meter_view AS
SELECT
    m.meter_id,
    m.scene_id,
    m.scene_meter_id,
    mh.meter_history_id,
    mh.meter_name,
    mh.meter_value,
    mh.meter_target_number,
    mh.meter_size,
    mh.meter_type,
    mh.meter_valid_from,
    mh.meter_valid_to,
    mh.meter_active
FROM meter m
         LEFT JOIN meter_history mh
              ON mh.meter_id = m.meter_id;