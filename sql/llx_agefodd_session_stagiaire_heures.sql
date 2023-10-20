-- Copyright (C) ---Put here your own copyright and developer email---
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see <http://www.gnu.org/licenses/>.


CREATE TABLE llx_agefodd_session_stagiaire_heures(
	rowid INTEGER AUTO_INCREMENT PRIMARY KEY,
	entity INTEGER DEFAULT 1 NOT NULL,
	fk_stagiaire integer NOT NULL,
	fk_session integer NOT NULL,
	fk_calendrier integer NOT NULL,
	mail_sended integer DEFAULT 0,
	planned_absence integer DEFAULT 0,
	heures float NOT NULL,
    fk_user_author integer NOT NULL,
	datec DATETIME NOT NULL,
	tms TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	import_key VARCHAR(14)
) ENGINE=innodb;