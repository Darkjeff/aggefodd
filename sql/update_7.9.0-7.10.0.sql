ALTER TABLE llx_agefodd_session ADD COLUMN send_survey_status integer NULL;
ALTER TABLE llx_agefodd_session ADD INDEX send_survey_status_session ( send_survey_status);
UPDATE llx_agefodd_session SET send_survey_status = '2' WHERE send_survey_status IS NULL;
-- Je ne met pas de valeur par défaut, qui normalement devrait être 1, mais lors d'une montée de version il peut y avoir beaucoup d'anciennes sessions
