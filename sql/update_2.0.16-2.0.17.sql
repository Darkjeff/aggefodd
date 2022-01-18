ALTER TABLE llx_agefodd_formation_catalogue ADD COLUMN entity integer NOT NULL DEFAULT 1;
ALTER TABLE llx_agefodd_session ADD COLUMN entity integer NOT NULL DEFAULT 1;
ALTER TABLE llx_agefodd_stagiaire ADD COLUMN entity integer NOT NULL DEFAULT 1;
ALTER TABLE llx_agefodd_place ADD COLUMN entity integer NOT NULL DEFAULT 1;
