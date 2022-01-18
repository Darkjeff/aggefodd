ALTER TABLE llx_agefodd_stagiaire_certif ADD COLUMN mark varchar(20) DEFAULT NULL;
ALTER TABLE llx_agefodd_opca ADD COLUMN fk_session_trainee integer DEFAULT NULL;
ALTER TABLE llx_agefodd_session_formateur ADD COLUMN fk_agefodd_formateur_type integer;
UPDATE llx_agefodd_formateur_type SET active=1;
UPDATE llx_agefodd_stagiaire_type SET active=1;

