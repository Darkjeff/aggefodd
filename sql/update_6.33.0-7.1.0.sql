CREATE TABLE IF NOT EXISTS llx_agefodd_session_trainee_path_img_signature_calendrier (
rowid integer NOT NULL auto_increment PRIMARY KEY,
entity integer NOT NULL DEFAULT 1,
fk_person integer NOT NULL,
person_type varchar(7) NOT NULL,
fk_session integer NOT NULL,
fk_calendrier integer NOT NULL,
ip varchar(250) NOT NULL,
navigateur varchar(250) NOT NULL,
datec DATETIME NOT NULL,
dates DATETIME NOT NULL,
tms timestamp NOT NULL
) ENGINE=InnoDB;

ALTER TABLE llx_agefodd_session_trainee_path_img_signature_calendrier ADD CONSTRAINT llx_agf_sess_trnee_path_img_sign_cal_ibfk_2 FOREIGN KEY (fk_session) REFERENCES llx_agefodd_session (rowid);
ALTER TABLE llx_agefodd_session_trainee_path_img_signature_calendrier ADD CONSTRAINT llx_agf_sess_trnee_path_img_sign_cal_ibfk_3 FOREIGN KEY (fk_calendrier) REFERENCES llx_agefodd_session_calendrier (rowid);
