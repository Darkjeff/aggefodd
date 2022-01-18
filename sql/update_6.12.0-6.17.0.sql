CREATE TABLE IF NOT EXISTS llx_agefodd_session_catalogue (
    rowid integer NOT NULL auto_increment PRIMARY KEY,
    ref varchar(40) NOT NULL,
    ref_interne varchar(100) NULL,
    entity integer NOT NULL DEFAULT 1,
    intitule varchar(100) NOT NULL,
    duree real NOT NULL DEFAULT 0,
    nb_place integer NULL,
    public text NULL,
    methode text NULL,
    but text NULL,
    programme text NULL,
    pedago_usage text NULL,
    sanction text NULL,
    prerequis text NULL,
    note1 text NULL,
    note2 text NULL,
    archive smallint NOT NULL DEFAULT 0,
    fk_user_author integer NOT NULL,
    datec datetime NOT NULL,
    fk_user_mod integer NOT NULL,
    note_private	text,
    note_public	text,
    fk_product integer,
    nb_subscribe_min integer NULL,
    fk_c_category integer NULL,
    fk_c_category_bpf integer NULL,
    fk_nature_action_code varchar(20) NULL,
    certif_duration varchar(30) NULL,
    qr_code_info varchar(500) NULL,
    color varchar(32) NULL,
    tms timestamp NOT NULL,
    import_key varchar(36) DEFAULT NULL,
    accessibility_handicap integer NOT NULL DEFAULT 0,
    fk_session integer NULL
    ) ENGINE=InnoDB;
-- clonage de la table d'extrafield des forma pour les sessionCatalogue
CREATE TABLE llx_agefodd_session_catalogue_extrafields LIKE llx_agefodd_formation_catalogue_extrafields;

CREATE TABLE IF NOT EXISTS llx_agefodd_session_catalogue_objectifs_peda (
    rowid integer NOT NULL auto_increment PRIMARY KEY,
    fk_session_catalogue integer NOT NULL,
    intitule varchar(500) NOT NULL,
    priorite smallint default NULL,
    fk_user_author integer NOT NULL,
    datec datetime NOT NULL,
    fk_user_mod integer NOT NULL,
    tms timestamp NOT NULL
    ) ENGINE=InnoDB;
