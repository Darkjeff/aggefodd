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
-- Structure de la table llx_agefodd_session_admlevel
--
CREATE TABLE IF NOT EXISTS llx_agefodd_session_admlevel (
  rowid int(11) NOT NULL auto_increment,
  level_rank int(11) NOT NULL default 0,
  fk_parent_level int(11) default 0,
  indice int(11) NOT NULL,
  intitule varchar(150) NOT NULL,
  delais_alerte int(11) NOT NULL,
  fk_user_author int(11) NOT NULL,
  datec datetime NOT NULL,
  fk_user_mod int(11) NOT NULL,
  tms timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (rowid)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
