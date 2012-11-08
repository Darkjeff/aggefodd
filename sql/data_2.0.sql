INSERT INTO llx_agefodd_stagiaire_type (rowid,intitule, sort, datec, tms, fk_user_author, fk_user_mod) VALUES
(1,'financement par l''employeur (contrat pro.)', 3, now(), now(), 0, 0),
(2,'financement par l''employeur (autre)', 4, now(), now(), 0, 0),
(3,'demandeur d''emploi avec financement public', 5, now(), now(), 0, 0),
(4,'autre', 6, now(), now(), 0, 0),
(5,'DIF', 1, now(), now(), 0, 0),
(6,'Période PRO', 2, now(), now(), 0, 0);

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

DELETE FROM llx_c_actioncomm WHERE code LIKE 'AC_AGF%';

INSERT INTO llx_c_actioncomm (`id`, `code`, `type`, `libelle`, `module`, `active`, `todo`, `position`) VALUES (1030001, 'AC_AGF_SESS', 'agefodd', 'Link to Training', 'agefodd', 1, NULL, 10);
INSERT INTO llx_c_actioncomm (`id`, `code`, `type`, `libelle`, `module`, `active`, `todo`, `position`) VALUES (1030002, 'AC_AGF_CONVEN', 'agefodd', 'Send Convention by mail', 'agefodd', 1, NULL, 20);
INSERT INTO llx_c_actioncomm (`id`, `code`, `type`, `libelle`, `module`, `active`, `todo`, `position`) VALUES (1030003, 'AC_AGF_CONVOC', 'agefodd', 'Send Convocation by mail', 'agefodd', 1, NULL, 30);
INSERT INTO llx_c_actioncomm (`id`, `code`, `type`, `libelle`, `module`, `active`, `todo`, `position`) VALUES (1030004, 'AC_AGF_PEDAGO', 'agefodd', 'Send Fiche pédagogique by mail', 'agefodd', 1, NULL, 40);
INSERT INTO llx_c_actioncomm (`id`, `code`, `type`, `libelle`, `module`, `active`, `todo`, `position`) VALUES (1030005, 'AC_AGF_PRES', 'agefodd', 'Send Fiche présence by mail', 'agefodd', 1, NULL, 50);
INSERT INTO llx_c_actioncomm (`id`, `code`, `type`, `libelle`, `module`, `active`, `todo`, `position`) VALUES (1030006, 'AC_AGF_ATTES', 'agefodd', 'Send attestation by mail', 'agefodd', 1, NULL, 60);
INSERT INTO llx_c_actioncomm (`id`, `code`, `type`, `libelle`, `module`, `active`, `todo`, `position`) VALUES (1030006, 'AC_AGF_CLOT', 'agefodd', 'Send dossier cloture by mail', 'agefodd', 1, NULL, 70);
