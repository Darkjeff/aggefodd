ALTER TABLE llx_agefodd_session_formateur ADD COLUMN trainer_status integer DEFAULT NULL;
ALTER TABLE llx_agefodd_session_formateur_calendrier ADD COLUMN trainer_cost  real DEFAULT NULL;
ALTER TABLE llx_agefodd_session_formateur_calendrier ADD COLUMN trainer_status integer DEFAULT NULL;

