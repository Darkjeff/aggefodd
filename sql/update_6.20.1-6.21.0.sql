ALTER TABLE llx_agefodd_session_admlevel ADD mandatory_file integer NOT NULL DEFAULT 0;
ALTER TABLE llx_agefodd_training_admlevel ADD mandatory_file integer NOT NULL DEFAULT 0;
ALTER TABLE llx_agefodd_session_adminsitu ADD mandatory_file integer NOT NULL DEFAULT 0;
ALTER TABLE llx_agefodd_session_adminsitu ADD file_name text DEFAULT NULL;

