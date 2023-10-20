CREATE TABLE IF NOT EXISTS llx_agefodd_stagiaire_soc_history (
     rowid integer NOT NULL auto_increment PRIMARY KEY,
     fk_user_creat integer default NULL,
    datec datetime NOT NULL,
    tms TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    fk_stagiaire integer NOT NULL,
    fk_soc integer NOT NULL,
    date_start datetime,
    date_end datetime
    ) ENGINE=InnoDB;
