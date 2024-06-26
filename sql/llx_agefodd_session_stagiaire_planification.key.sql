-- ============================================================================
-- Copyright (C) 2009-2010	Erick Bullier	<eb.dev@ebiconsulting.fr>
-- Copyright (C) 2010-2011	Regis Houssin	<regis@dolibarr.fr>
-- Copyright (C) 2020		Florian Henry	<florian.henry@open-concept.pro>
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
-- Contraintes pour la table llx_agefodd_session_stagiaire_planification
--
ALTER TABLE llx_agefodd_session_stagiaire_planification ADD CONSTRAINT llx_agefodd_session_stagiaire_planification_ibfk_1 FOREIGN KEY (fk_agefodd_session) REFERENCES llx_agefodd_session (rowid) ON DELETE CASCADE;
ALTER TABLE llx_agefodd_session_stagiaire_planification ADD CONSTRAINT llx_agefodd_session_stagiaire_planification_ibfk_2 FOREIGN KEY (fk_agefodd_session_stagiaire) REFERENCES llx_agefodd_session_stagiaire (rowid) ON DELETE CASCADE;
ALTER TABLE llx_agefodd_session_stagiaire_planification ADD CONSTRAINT llx_agefodd_session_stagiaire_planification_ibfk_3 FOREIGN KEY (fk_calendrier_type) REFERENCES llx_c_agefodd_session_calendrier_type (rowid);

