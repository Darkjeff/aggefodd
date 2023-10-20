-- ============================================================================
-- Copyright (C) 2009-2010	Erick Bullier	<eb.dev@ebiconsulting.fr>
-- Copyright (C) 2010-2011	Regis Houssin	<regis@dolibarr.fr>
-- Copyright (C) 2012		Florian Henry	<florian.henry@open-concept.pro>
--
-- This program is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 2 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program; if not, write to the Free Software
-- Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
--
-- ============================================================================
--
-- Structure de la table llx_agefodd_session_trainee_path_img_signature_calendrier
--

CREATE TABLE IF NOT EXISTS llx_agefodd_session_trainee_path_img_signature_calendrier (
rowid integer NOT NULL auto_increment PRIMARY KEY,
entity integer NOT NULL DEFAULT 1,
fk_person integer NOT NULL,
person_type varchar(7) NOT NULL,
fk_session integer NOT NULL,
fk_calendrier integer NOT NULL,
ip varchar(255) NOT NULL,
navigateur varchar(255) NOT NULL,
datec DATETIME NOT NULL,
dates DATETIME NOT NULL,
tms TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
path varchar(255) NOT NULL
) ENGINE=InnoDB;
