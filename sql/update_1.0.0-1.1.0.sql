ALTER TABLE llx_agefodd_contact ADD COLUMN archive tinyint NOT NULL DEFAULT 0 AFTER fk_socpeople;
ALTER TABLE llx_agefodd_contact MODIFY tms timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP;

ALTER TABLE llx_agefodd_convention MODIFY tms timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP;

ALTER TABLE llx_agefodd_facture MODIFY tms timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP;

ALTER TABLE llx_agefodd_formateur MODIFY tms timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP;
ALTER TABLE llx_agefodd_formateur MODIFY archive tinyint NOT NULL DEFAULT 0;

ALTER TABLE llx_agefodd_formation_catalogue CHANGE COLUMN ref_interne ref varchar(40) NOT NULL;
ALTER TABLE llx_agefodd_formation_catalogue MODIFY archive tinyint NOT NULL DEFAULT 0;
ALTER TABLE llx_agefodd_formation_catalogue ADD COLUMN fk_user_author int(11) NOT NULL AFTER tms;
ALTER TABLE llx_agefodd_formation_catalogue CHANGE COLUMN fk_user fk_user_mod int(11) NOT NULL;

ALTER TABLE llx_agefodd_formation_objectifs_peda ADD COLUMN fk_user_author int(11) NOT NULL AFTER tms;
ALTER TABLE llx_agefodd_formation_objectifs_peda ADD COLUMN datec datetime NOT NULL  AFTER tms;
ALTER TABLE llx_agefodd_formation_objectifs_peda CHANGE COLUMN fk_user fk_user_mod int(11) NOT NULL;

ALTER TABLE llx_agefodd_session_place RENAME TO llx_agefodd_place;
ALTER TABLE llx_agefodd_place CHANGE COLUMN code ref_interne varchar(80) NOT NULL;
ALTER TABLE llx_agefodd_place CHANGE COLUMN pays fk_pays varchar(30) NOT NULL;
UPDATE llx_agefodd_place SET fk_pays=0 WHERE pays NOT IN (SELECT libelle FROM llx_c_pays);
UPDATE llx_agefodd_place SET fk_pays=p.rowid FROM llx_c_pays as p WHERE pays=p.libelle;
ALTER TABLE llx_agefodd_place ADD CONSTRAINT llx_agefodd_session_ibfk_1 FOREIGN KEY (fk_pays) REFERENCES llx_c_pays (rowid);
ALTER TABLE llx_agefodd_place MODIFY archive tinyint NOT NULL DEFAULT 0;
ALTER TABLE llx_agefodd_place MODIFY tms timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP;

ALTER TABLE llx_agefodd_session DROP COLUMN fk_agefodd_formateur;
ALTER TABLE llx_agefodd_session MODIFY tms timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP;
ALTER TABLE llx_agefodd_session ADD COLUMN cost_trainer double(24,8) DEFAULT 0 AFTER notes;
ALTER TABLE llx_agefodd_session ADD COLUMN cost_site double(24,8) DEFAULT 0 AFTER cost_trainer;
ALTER TABLE llx_agefodd_session ADD COLUMN sell_price double(24,8) DEFAULT 0 AFTER cost_site;
ALTER TABLE llx_agefodd_session ADD COLUMN is_date_res_site tinyint NOT NULL DEFAULT 0 AFTER sell_price;
ALTER TABLE llx_agefodd_session ADD COLUMN date_res_site datetime DEFAULT NULL AFTER is_date_res_site;
ALTER TABLE llx_agefodd_session ADD COLUMN is_date_res_trainer tinyint NOT NULL DEFAULT 0 AFTER is_date_res_site;
ALTER TABLE llx_agefodd_session ADD COLUMN date_res_trainer datetime DEFAULT NULL AFTER is_date_res_trainer;
ALTER TABLE llx_agefodd_session ADD COLUMN date_ask_OPCA datetime DEFAULT NULL AFTER date_res_trainer;
ALTER TABLE llx_agefodd_session ADD COLUMN is_date_ask_OPCA tinyint NOT NULL DEFAULT 0 AFTER date_ask_OPCA;
ALTER TABLE llx_agefodd_session ADD COLUMN is_OPCA tinyint NOT NULL DEFAULT 0 AFTER is_date_ask_OPCA;
ALTER TABLE llx_agefodd_session ADD COLUMN fk_soc_OPCA int(11) DEFAULT NULL AFTER is_OPCA;
ALTER TABLE llx_agefodd_session ADD COLUMN fk_socpeople_OPCA int(11) DEFAULT NULL AFTER fk_soc_OPCA;
ALTER TABLE llx_agefodd_session ADD COLUMN num_OPCA_soc varchar(100) DEFAULT NULL AFTER fk_socpeople_OPCA;
ALTER TABLE llx_agefodd_session ADD COLUMN num_OPCA_file varchar(100) DEFAULT NULL AFTER num_OPCA_soc;

ALTER TABLE llx_agefodd_session_admlevel MODIFY tms timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP;
ALTER TABLE llx_agefodd_session_admlevel ADD COLUMN level_rank int(11) NOT NULL default '0' AFTER top_level;
ALTER TABLE llx_agefodd_session_admlevel ADD COLUMN fk_parent_level int(11) default '0' AFTER level_rank;
ALTER TABLE llx_agefodd_session_admlevel DROP COLUMN top_level;
TRUNCATE TABLE llx_agefodd_session_admlevel;
INSERT INTO llx_agefodd_session_admlevel(rowid, level_rank, fk_parent_level, indice, intitule, delais_alerte, fk_user_author, datec, fk_user_mod, tms) VALUES
(1, 0, 0, 100, 'Préparation de l''action', -40, 1, '2012-01-01 00:00:00', 0, '2012-01-01 00:00:00'),
(2, 1, 1, 101, 'Inscription des stagiaires', -31, 1, '2012-01-01 00:00:00', 0, '2012-01-01 00:00:00'),
(3, 0, 0, 200, 'Transmission de la convention de formation', -30, 1, '2012-01-01 00:00:00', 0, '2012-01-01 00:00:00'),
(4, 1, 3, 201, 'Impression convention et vérification', -31, 1, '2012-01-01 00:00:00', 0, '2012-01-01 00:00:00'),
(5, 1, 3, 202, 'Envoi convention (VP ou numérique avec AC)', -30, 1, '2012-01-01 00:00:00', 0, '2012-01-01 00:00:00'),
(6, 0, 0, 300, 'Envoi des convocations', -15, 1, '2012-01-01 00:00:00', 0, '2012-01-01 00:00:00'),
(7, 1, 6, 301, 'Preparation du dossier<br>(convoc., rég. intérieur, programme, fiche péda, conseils pratiques)', -15, 1, '2012-01-01 00:00:00', 0, '2012-01-01 00:00:00'),
(8, 1, 6, 302, 'Envoi du dossier à chaque stagiaire (inter) ou au respo. formation (intra)', -15, 1, '2012-01-01 00:00:00', 0, '2012-01-01 00:00:00'),
(9, 0, 0, 400, 'Vérifications et mise en place des moyens', -10, 1, '2012-01-01 00:00:00', 0, '2012-01-01 00:00:00'),
(10, 1, 9, 401, 'Verification du retour de la convention signée', -10, 1, '2012-01-01 00:00:00', 0, '2012-01-01 00:00:00'),
(11, 0, 0, 500, 'Execution de la prestation', 0, 1, '2012-01-01 00:00:00', 0, '2012-01-01 00:00:00'),
(12, 0, 0, 600, 'Cloture administrative', 8, 1, '2012-01-01 00:00:00', 0, '2012-01-01 00:00:00'),
(13, 1, 12, 601, 'Impression des attestations', 8, 1, '2012-01-01 00:00:00', 0, '2012-01-01 00:00:00'),
(14, 1, 12, 602, 'Creation de la facture et verification', 8, 1, '2012-01-01 00:00:00', 0, '2012-01-01 00:00:00'),
(15, 1, 12, 603, 'Création du courrier d''accompagnement', 8, 1, '2012-01-01 00:00:00', 0, '2012-01-01 00:00:00'),
(16, 1, 12, 604, 'Impression de la liasse administrative', 8, 1, '2012-01-01 00:00:00', 0, '2012-01-01 00:00:00'),
(17, 1, 12, 605, 'Envoi de la liasse administrative', 8, 1, '2012-01-01 00:00:00', 0, '2012-01-01 00:00:00');


ALTER TABLE llx_agefodd_session_adminsitu MODIFY tms timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP;
ALTER TABLE llx_agefodd_session_adminsitu ADD COLUMN fk_user_author int(11) NOT NULL AFTER tms;
ALTER TABLE llx_agefodd_session_adminsitu ADD COLUMN datec datetime NOT NULL AFTER fk_user_author;
ALTER TABLE llx_agefodd_session_adminsitu ADD COLUMN level_rank int(11) NOT NULL default '0' AFTER top_level;
ALTER TABLE llx_agefodd_session_adminsitu ADD COLUMN fk_parent_level int(11) default '0' AFTER level_rank;
ALTER TABLE llx_agefodd_session_adminsitu DROP COLUMN top_level;
UPDATE llx_agefodd_session_adminsitu SET level_rank=adm.level_rank FROM llx_agefodd_session_admlevel as adm WHERE adm.rowid=fk_agefodd_session_admlevel;
UPDATE llx_agefodd_session_adminsitu as ori,llx_agefodd_session_adminsitu as upd SET upd.fk_parent_level=ori.rowid WHERE upd.fk_parent_level=ori.fk_agefodd_session_admlevel AND upd.level_rank<>0 AND upd.fk_agefodd_session=ori.fk_agefodd_session;

ALTER TABLE llx_agefodd_session_calendrier MODIFY tms timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP;
ALTER TABLE llx_agefodd_session_calendrier ADD COLUMN heured_dt datetime NOT NULL AFTER heured;
ALTER TABLE llx_agefodd_session_calendrier ADD COLUMN heuref_dt datetime NOT NULL AFTER heuref;
UPDATE llx_agefodd_session_calendrier SET heured_dt=date + ' ' + heured;
UPDATE llx_agefodd_session_calendrier SET heuref_dt=date + ' ' + heuref;
ALTER TABLE llx_agefodd_session_calendrier DROP COLUMN heured;
ALTER TABLE llx_agefodd_session_calendrier DROP COLUMN heuref;
ALTER TABLE llx_agefodd_session_calendrier CHANGE COLUMN heured_dt heured datetime NOT NULL;
ALTER TABLE llx_agefodd_session_calendrier CHANGE COLUMN heuref_dt heuref datetime NOT NULL;

ALTER TABLE llx_agefodd_session_formateur MODIFY tms timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP;

ALTER TABLE llx_agefodd_session_stagiaire MODIFY tms timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP;
ALTER TABLE llx_agefodd_session_stagiaire CHANGE COLUMN fk_session heured fk_session_agefodd int(11) NOT NULL;

ALTER TABLE llx_agefodd_stagiaire ADD COLUMN civilite varchar(6) NOT NULL AFTER prenom;
UPDATE llx_agefodd_stagiaire SET civilite=civ.code FROM llx_c_civilite as civ WHERE civ.rowid=fk_c_civilite;
ALTER TABLE llx_agefodd_stagiaire DROP COLUMN fk_c_civilite;
ALTER TABLE llx_agefodd_stagiaire ADD CONSTRAINT llx_agefodd_stagiaire_ibfk_1 FOREIGN KEY (civilite) REFERENCES llx_c_civilite (code);

ALTER TABLE llx_agefodd_stagiaire_type CHANGE COLUMN ordere sort tinyint(4) NOT NULL;
INSERT INTO llx_agefodd_stagiaire_type (intitule, sort, datec, tms, fk_user_author, fk_user_mod) VALUES
('DIF', 1, '0000-00-00 00:00:00', '2010-06-30 18:48:05', 0, 0),
('Période PRO', 2, '0000-00-00 00:00:00', '2010-06-30 18:48:05', 0, 0),

