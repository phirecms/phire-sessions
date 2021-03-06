--
-- Sessions Module PostgreSQL Database for Phire CMS 2.0
--

-- --------------------------------------------------------

--
-- Table structure for table "user_session_config"
--

CREATE TABLE IF NOT EXISTS "[{prefix}]user_session_config" (
  "role_id" integer,
  "multiple_sessions" integer,
  "allowed_attempts" integer,
  "session_expiration" integer,
  "timeout_warning" integer,
  "ip_allowed" text,
  "ip_blocked" text,
  "log_emails" text,
  "log_type" integer,
  UNIQUE ("role_id"),
  CONSTRAINT "fk_user_session_role_id" FOREIGN KEY ("role_id") REFERENCES "[{prefix}]roles" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

-- --------------------------------------------------------

--
-- Table structure for table "user_session_data"
--

CREATE TABLE IF NOT EXISTS "[{prefix}]user_session_data" (
  "user_id" integer,
  "logins" text,
  "total_logins" integer,
  "failed_attempts" integer,
  UNIQUE ("user_id"),
  CONSTRAINT "fk_sess_data_user_id" FOREIGN KEY ("user_id") REFERENCES "[{prefix}]users" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

-- --------------------------------------------------------

--
-- Table structure for table "user_sessions"
--

CREATE SEQUENCE user_session_id_seq START 4001;

CREATE TABLE IF NOT EXISTS "[{prefix}]user_sessions" (
  "id" integer NOT NULL DEFAULT nextval('user_session_id_seq'),
  "user_id" integer,
  "ip" varchar(255) NOT NULL,
  "ua" varchar(255) NOT NULL,
  "start" integer NOT NULL,
  PRIMARY KEY ("id"),
  CONSTRAINT "fk_session_user" FOREIGN KEY ("user_id") REFERENCES "[{prefix}]users" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

ALTER SEQUENCE user_session_id_seq OWNED BY "[{prefix}]user_sessions"."id";
