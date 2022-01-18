ALTER TABLE llx_agefodd_reg_interieur ADD COLUMN reg_int text NULL;
ALTER TABLE llx_agefodd_reg_interieur ADD COLUMN fk_user_author integer NOT NULL DEFAULT '0';
ALTER TABLE llx_agefodd_reg_interieur ADD COLUMN datec datetime NOT NULL DEFAULT NOW();
ALTER TABLE llx_agefodd_reg_interieur ADD COLUMN fk_user_mod integer NOT NULL DEFAULT '0';
ALTER TABLE llx_agefodd_reg_interieur ADD COLUMN tms timestamp NOT NULL default CURRENT_TIMESTAMP;
ALTER TABLE llx_agefodd_reg_interieur DROP COLUMN file;

ALTER TABLE llx_agefodd_place ADD COLUMN fk_reg_interieur integer NULL;
ALTER TABLE llx_agefodd_reg_interieur ADD CONSTRAINT llx_agefodd_reg_interieur_ibfk_1 FOREIGN KEY (rowid) REFERENCES llx_agefodd_place ( fk_reg_interieur)  ON DELETE CASCADE;

ALTER TABLE llx_agefodd_session ADD COLUMN type_session integer NULL;

ALTER TABLE llx_agefodd_session_calendrier ADD COLUMN fk_actioncomm integer NOT NULL DEFAULT '0';
ALTER TABLE llx_agefodd_session_calendrier ADD CONSTRAINT llx_agefodd_session_calendrier_ibfk_2 FOREIGN KEY (fk_actioncomm) REFERENCES llx_actioncomm (id)  ON DELETE CASCADE;

ALTER TABLE llx_agefodd_session ADD COLUMN fk_soc integer NULL;
ALTER TABLE llx_agefodd_session ADD COLUMN color varchar(32) NULL;
ALTER TABLE llx_agefodd_session ADD COLUMN nb_place integer NULL;
ALTER TABLE llx_agefodd_session ADD COLUMN nb_stagiaire integer NULL;
ALTER TABLE llx_agefodd_session ADD COLUMN force_nb_stagiaire integer NULL;
ALTER TABLE llx_agefodd_session ADD COLUMN cost_trip double(24,8) NULL;

ALTER TABLE llx_agefodd_session ADD INDEX fk_soc (fk_soc);
