ALTER TABLE llx_agefodd_session_stagiaire ADD COLUMN fk_soc integer NOT NULL DEFAULT 0;

ALTER TABLE llx_agefodd_session_stagiaire ADD INDEX fk_session_soc_sta (fk_soc);

UPDATE llx_agefodd_session_stagiaire SET fk_soc = (SELECT sta.fk_soc FROM llx_agefodd_stagiaire as sta WHERE sta.rowid = fk_stagiaire) WHERE fk_soc = 0;
INSERT INTO llx_agefodd_stagiaire_soc_history (fk_user_creat, datec, fk_stagiaire, fk_soc, date_start) SELECT fk_user_author, NOW(), rowid, fk_soc, NOW() FROM llx_agefodd_stagiaire;
