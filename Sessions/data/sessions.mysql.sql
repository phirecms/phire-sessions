--
-- Sessions Module MySQL Database for Phire CMS 2.0
--

-- --------------------------------------------------------
SET FOREIGN_KEY_CHECKS = 0;
--
-- Table structure for table `user_session_config`
--

DROP TABLE IF EXISTS `[{prefix}]user_session_config`;
CREATE TABLE IF NOT EXISTS `[{prefix}]user_session_config` (
  `role_id` int(16),
  `multiple_sessions` int(1),
  `allowed_attempts` int(16),
  `session_expiration` int(16),
  `timeout_warning` int(1),
  `ip_allowed` text,
  `ip_blocked` text,
  `log_emails` text,
  `log_type` int(1),
  UNIQUE (`role_id`),
  CONSTRAINT `fk_user_session_role_id` FOREIGN KEY (`role_id`) REFERENCES `[{prefix}]user_roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Table structure for table `user_session_data`
--

DROP TABLE IF EXISTS `[{prefix}]user_session_data`;
CREATE TABLE IF NOT EXISTS `[{prefix}]user_session_data` (
  `user_id` int(16),
  `logins` longtext,
  `failed_attempts` int(16),
  UNIQUE (`user_id`),
  CONSTRAINT `fk_sess_data_user_id` FOREIGN KEY (`user_id`) REFERENCES `[{prefix}]users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

DROP TABLE IF EXISTS `[{prefix}]user_sessions`;
CREATE TABLE IF NOT EXISTS `[{prefix}]user_sessions` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `user_id` int(16),
  `ip` varchar(255) NOT NULL,
  `ua` varchar(255) NOT NULL,
  `start` int(16) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `sess_user_id` (`user_id`),
  CONSTRAINT `fk_session_user` FOREIGN KEY (`user_id`) REFERENCES `[{prefix}]users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4001 ;

SET FOREIGN_KEY_CHECKS = 1;
