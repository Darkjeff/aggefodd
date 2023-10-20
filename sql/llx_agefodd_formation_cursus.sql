-- ============================================================================
-- Copyright (C) 2013		Florian Henry	<florian.henry@open-concept.pro>
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
-- Structure de la table llx_agefodd_formation_cursus
--
CREATE TABLE IF NOT EXISTS llx_agefodd_formation_cursus (
  rowid integer NOT NULL auto_increment PRIMARY KEY,
  entity integer NOT NULL DEFAULT 1,
  fk_formation_catalogue integer NOT NULL,
  fk_cursus integer NOT NULL,
  fk_user_author integer NOT NULL,
  datec datetime NOT NULL,
  fk_user_mod integer NOT NULL,
  tms TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;
