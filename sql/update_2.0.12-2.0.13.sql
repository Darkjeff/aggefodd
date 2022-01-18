ALTER TABLE llx_agefodd_stagiaire_type ADD COLUMN active integer;

ALTER TABLE llx_agefodd_formation_catalogue ADD COLUMN but text;

ALTER TABLE llx_actioncomm MODIFY elementtype VARCHAR(32);
