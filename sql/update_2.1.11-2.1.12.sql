ALTER TABLE llx_agefodd_formation_catalogue ADD COLUMN pedago_usage text DEFAULT NULL;
ALTER TABLE llx_agefodd_formation_catalogue ADD COLUMN sanction text DEFAULT NULL;
ALTER TABLE llx_agefodd_session ADD COLUMN fk_socpeople_presta integer DEFAULT NULL;
