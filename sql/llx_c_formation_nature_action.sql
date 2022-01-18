CREATE TABLE llx_c_formation_nature_action (
                                     rowid int(11) AUTO_INCREMENT PRIMARY KEY,
                                     code varchar(20) UNIQUE,
                                     rank int default 0,
                                     label varchar(255),
                                     entity int default 0,
                                     active tinyint(4)
);
