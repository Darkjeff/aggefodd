-- VMYSQL4.1 CREATE TABLE llx_c_formation_nature_action ( rowid int(11) AUTO_INCREMENT PRIMARY KEY, `code` varchar(20) UNIQUE, `rank` int default 0, label varchar(255), entity int default 0, active tinyint(4) );
-- VPGSQL8.2 CREATE TABLE llx_c_formation_nature_action ( rowid int(11) AUTO_INCREMENT PRIMARY KEY, "code" varchar(20) UNIQUE, "rank" int default 0, label varchar(255), entity int default 0, active tinyint(4) );

INSERT INTO llx_c_formation_nature_action (rowid, code, label, entity, active) VALUES (1, 'AGF_NAT_ACT_AF', 'action de formation', 0, 1);
INSERT INTO llx_c_formation_nature_action (rowid, code, label, entity, active) VALUES (2, 'AGF_NAT_ACT_BC', 'bilan de compétences', 0, 1);
INSERT INTO llx_c_formation_nature_action (rowid, code, label, entity, active) VALUES (3, 'AGF_NAT_ACT_VAE', 'action de VAE', 0, 1);
INSERT INTO llx_c_formation_nature_action (rowid, code, label, entity, active) VALUES (4, 'AGF_NAT_ACT_AFA', 'action de formation par apprentissage', 0, 1);
