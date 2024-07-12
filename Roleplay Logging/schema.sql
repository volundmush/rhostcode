CREATE TABLE IF NOT EXISTS entity (
    entity_id INT AUTO_INCREMENT PRIMARY KEY,
    entity_name VARCHAR(60) NOT NULL,
    entity_objid VARCHAR(40) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS plot (
    plot_id INT AUTO_INCREMENT PRIMARY KEY,
    plot_title VARCHAR(255) UNIQUE,
    plot_pitch TEXT NULL,
    plot_pitch_color TEXT NULL,
    plot_outcome TEXT NULL,
    plot_outcome_color TEXT NULL,
    plot_date_start DATETIME NULL DEFAULT NULL,
    plot_date_end DATETIME NULL DEFAULT NULL
) ENGINE=InnoDB;

CREATE OR REPLACE VIEW plot_view AS
         SELECT plot_id,plot_title,plot_pitch,plot_pitch_color,plot_outcome,plot_outcome_color,
                plot_date_start,
                UNIX_TIMESTAMP(plot_date_start) AS plot_date_start_secs,
                plot_date_end,
                UNIX_TIMESTAMP(plot_date_end) AS plot_date_end_secs
         FROM plot;

CREATE TABLE IF NOT EXISTS plot_runner (
    plot_id INT NOT NULL,
    entity_id INT NOT NULL,
    runner_type INT NOT NULL DEFAULT 0,
    PRIMARY KEY(plot_id,entity_id),
    FOREIGN KEY (plot_id) REFERENCES plot(plot_id) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (entity_id) REFERENCES entity(entity_id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE OR REPLACE VIEW plot_runner_view AS
       SELECT p.*,pr.runner_type,e.*
       FROM plot_runner AS pr
           LEFT JOIN plot_view AS p ON p.plot_id = pr.plot_id
           LEFT JOIN entity AS e ON pr.entity_id = e.entity_id;

CREATE OR REPLACE VIEW plot_runner_view_runners AS
       SELECT plot_id,GROUP_CONCAT(entity_objid ORDER BY entity_name) AS runner_objids, GROUP_CONCAT(entity_name ORDER BY entity_name SEPARATOR '|') AS runner_names
       FROM plot_runner_view
           WHERE runner_type = 2
       GROUP BY plot_id;

CREATE OR REPLACE VIEW plot_runner_view_helpers AS
       SELECT plot_id,GROUP_CONCAT(entity_objid ORDER BY entity_name) AS helper_objids, GROUP_CONCAT(entity_name ORDER BY entity_name SEPARATOR '|') AS helper_names
       FROM plot_runner_view
           WHERE runner_type = 1
       GROUP BY plot_id;

CREATE OR REPLACE VIEW plot_runner_view_agg AS
       SELECT p.*,r.runner_objids,r.runner_names,h.helper_objids,h.helper_names
       FROM plot_view as p
           LEFT JOIN plot_runner_view_runners AS r ON p.plot_id = r.plot_id
           LEFT JOIN plot_runner_view_helpers AS h ON p.plot_id = h.plot_id;

CREATE TABLE IF NOT EXISTS scene (
    scene_id INT AUTO_INCREMENT PRIMARY KEY,
    scene_title VARCHAR(255) NOT NULL UNIQUE,
    scene_title_color VARCHAR(255) NOT NULL,
    scene_pitch TEXT NULL,
    scene_pitch_color TEXT NULL,
    scene_outcome TEXT NULL,
    scene_outcome_color TEXT NULL,
    scene_date_created DATETIME,
    scene_date_scheduled DATETIME NULL,
    scene_date_started DATETIME NULL,
    scene_date_finished DATETIME NULL,
    scene_status TINYINT DEFAULT 0,
    INDEX(scene_date_created),
    INDEX(scene_date_scheduled,scene_status)
) ENGINE=InnoDB;

CREATE OR REPLACE VIEW scene_view AS
    SELECT scene_id,scene_title,scene_title_color,scene_pitch,scene_pitch_color,scene_outcome,scene_outcome_color,
           scene_date_created,
           UNIX_TIMESTAMP(scene_date_created) AS scene_date_created_secs,
           scene_date_scheduled,
           UNIX_TIMESTAMP(scene_date_scheduled) AS scene_date_scheduled_secs,
           scene_date_started,
           UNIX_TIMESTAMP(scene_date_started) AS scene_date_started_secs,
           scene_date_finished,
           UNIX_TIMESTAMP(scene_date_finished) AS scene_date_finished_secs,
           scene_status
    FROM scene;

CREATE TABLE IF NOT EXISTS scene_plot (
    PRIMARY KEY(plot_id,scene_id),
    plot_id INT NOT NULL,
    scene_id INT NOT NULL,
    FOREIGN KEY (scene_id) REFERENCES scene(scene_id) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (plot_id) REFERENCES plot(plot_id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS actor (
    actor_id INT AUTO_INCREMENT PRIMARY KEY,
    scene_id INT NOT NULL,
    entity_id INT NOT NULL,
    actor_type TINYINT UNSIGNED NOT NULL DEFAULT 0,
    action_count INT UNSIGNED NOT NULL DEFAULT 0,
    actor_date_created DATETIME NOT NULL,
    UNIQUE(scene_id,entity_id),
    INDEX(scene_id,entity_id,actor_type),
    FOREIGN KEY (scene_id) REFERENCES scene(scene_id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE OR REPLACE VIEW actor_view AS
    SELECT a.actor_id,a.scene_id,a.entity_id,a.actor_type,a.action_count,
           e.entity_name,e.entity_objid
    FROM actor AS a
    LEFT JOIN entity AS e ON a.entity_id = e.entity_id;

CREATE TABLE IF NOT EXISTS actrole (
    actrole_id INT AUTO_INCREMENT PRIMARY KEY,
    actor_id INT NOT NULL,
    actrole_name VARCHAR(255) NOT NULL,
    FOREIGN KEY (actor_id) REFERENCES actor(actor_id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE OR REPLACE VIEW actrole_view AS
    SELECT ar.actrole_id,ar.actrole_name,IF(ar.actrole_name=a.entity_name,ar.actrole_name,CONCAT(a.entity_name, ' (as ', ar.actrole_name, ')')) as display_name,
           a.*
    FROM actrole AS ar
    LEFT JOIN actor_view AS a ON ar.actor_id = a.actor_id;

CREATE TABLE IF NOT EXISTS channel (
    channel_id INT AUTO_INCREMENT PRIMARY KEY,
    scene_id INT NOT NULL,
    channel_name VARCHAR(255) NOT NULL,
    channel_category TINYINT UNSIGNED NOT NULL DEFAULT 0,
    channel_type TINYINT UNSIGNED NOT NULL DEFAULT 0,
    UNIQUE(scene_id,channel_category,channel_type),
    FOREIGN KEY (scene_id) REFERENCES scene(scene_id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pose (
    pose_id INT AUTO_INCREMENT PRIMARY KEY,
    actrole_id INT NULL,
    channel_id INT NOT NULL,
    pose_is_deleted TINYINT UNSIGNED NOT NULL DEFAULT 0,
    pose_date_created DATETIME NOT NULL,
    pose_text TEXT NOT NULL,
    pose_text_color TEXT NULL,
    post_text_internal NULL,
    FOREIGN KEY (actrole_id) REFERENCES actrole(actrole_id) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (channel_id) REFERENCES channel(channel_id) ON UPDATE CASCADE ON DELETE CASCADE,
    INDEX(pose_date_created)
) ENGINE=InnoDB;

CREATE OR REPLACE VIEW pose_view AS
       SELECT p.pose_id,p.pose_is_deleted,p.pose_date_created,UNIX_TIMESTAMP(p.pose_date_created) AS pose_date_created_secs,
              p.pose_text,p.pose_text_color,p.pose_text_internal,
              c.channel_id,c.channel_category,c.channel_type,c.channel_name,
              a.*
           FROM pose AS p
       LEFT JOIN channel AS c ON p.channel_id = c.channel_id
       LEFT JOIN actrole_view as a ON p.actrole_id = a.actrole_id;