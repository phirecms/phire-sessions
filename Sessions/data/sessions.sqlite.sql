--
-- Sessions Module SQLite Database for Phire CMS 2.0
--

--  --------------------------------------------------------

--
-- Set database encoding
--

PRAGMA encoding = "UTF-8";
PRAGMA foreign_keys = ON;

-- --------------------------------------------------------

--
-- Table structure for table "user_session_config"
--

DROP TABLE IF EXISTS "[{prefix}]user_session_config";
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
  CONSTRAINT "fk_user_session_role_id" FOREIGN KEY ("role_id") REFERENCES "[{prefix}]user_roles" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

CREATE INDEX "sess_role_id" ON "[{prefix}]user_session_config" ("role_id");

-- --------------------------------------------------------

--
-- Table structure for table "user_session_data"
--

DROP TABLE IF EXISTS "[{prefix}]user_session_data";
CREATE TABLE IF NOT EXISTS "[{prefix}]user_session_data" (
  "user_id" integer,
  "logins" text,
  "failed_attempts" integer,
  UNIQUE ("user_id"),
  CONSTRAINT "fk_sess_data_user_id" FOREIGN KEY ("user_id") REFERENCES "[{prefix}]users" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

CREATE INDEX "sess_data_user_id" ON "[{prefix}]user_session_data" ("user_id");

-- --------------------------------------------------------

--
-- Table structure for table "user_sessions"
--

DROP TABLE IF EXISTS "[{prefix}]user_sessions";
CREATE TABLE IF NOT EXISTS "[{prefix}]user_sessions" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "user_id" integer,
  "ip" varchar(255) NOT NULL,
  "ua" varchar(255) NOT NULL,
  "start" integer NOT NULL,
  UNIQUE ("id"),
  CONSTRAINT "fk_session_user" FOREIGN KEY ("user_id") REFERENCES "[{prefix}]users" ("id") ON DELETE CASCADE ON UPDATE CASCADE
) ;

INSERT INTO "sqlite_sequence" ("name", "seq") VALUES ('[{prefix}]user_sessions', 4000);
CREATE INDEX "sess_user_id" ON "[{prefix}]user_sessions" ("user_id");
