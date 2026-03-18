-- Table structure for form_submissions
-- Run this in phpMyAdmin or MySQL CLI against the `new` database

CREATE TABLE IF NOT EXISTS `form_submissions` (
  `id`                   int(11)          NOT NULL AUTO_INCREMENT,
  `name`                 varchar(255)     NOT NULL,
  `contact`              varchar(20)      NOT NULL,
  `email`                varchar(255)     NOT NULL,
  `whatsapp`             varchar(20)      NOT NULL,
  `college_name`         varchar(255)     NOT NULL,
  `courses_selected`     text             NOT NULL,
  `batch`                varchar(50)      NOT NULL,
  `year`                 varchar(30)      DEFAULT NULL,
  `total_program`        enum('training','internship') NOT NULL,
  `internship_duration`  varchar(50)      DEFAULT NULL,
  `total_price`          decimal(10,2)    DEFAULT NULL,
  `executive_name`       varchar(255)     DEFAULT NULL,
  `remarks`              text             DEFAULT NULL,
  `certificate_status`   enum('pending','issued') NOT NULL DEFAULT 'pending',
  `start_date`           date             DEFAULT NULL,
  `end_date`             date             DEFAULT NULL,
  `days`                 int(11)          DEFAULT NULL,
  `certificate_date`     date             DEFAULT NULL,
  `submitted_at`         timestamp        NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
