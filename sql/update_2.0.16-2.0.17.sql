ALTER TABLE llx_agefodd_formation_catalogue ADD COLUMN entity int(11) NOT NULL DEFAULT 1 AFTER ref;
ALTER TABLE llx_agefodd_session ADD COLUMN entity int(11) NOT NULL DEFAULT 1 AFTER rowid;
ALTER TABLE llx_agefodd_stagiaire ADD COLUMN entity int(11) NOT NULL DEFAULT 1 AFTER rowid;
ALTER TABLE llx_agefodd_place ADD COLUMN entity int(11) NOT NULL DEFAULT 1 AFTER rowid;
