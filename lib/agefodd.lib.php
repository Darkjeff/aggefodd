<?php
/**
 * Copyright (C) 2012 Florian Henry <florian.henry@open-concept.pro>
 * Copyright (C) 2012		JF FERRY	<jfefe@aternatik.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file agefodd/lib/agefodd.lib.php
 * \ingroup agefodd
 * \brief Some display function
 */
$langs->load('agefodd@agefodd');

/**
 * Return head table for training tabs screen
 *
 * @param object $object training
 * @return array head table of tabs
 *        
 */
function training_prepare_head($object) {
	global $langs, $conf, $user;
	
	$h = 0;
	$head = array ();
	
	$head [$h] [0] = dol_buildpath('/agefodd/training/card.php', 1) . '?id=' . $object->id;
	$head [$h] [1] = $langs->trans("Card");
	$head [$h] [2] = 'card';
	$hselected = $h;
	$h ++;
	
	$head [$h] [0] = dol_buildpath('/agefodd/session/list.php', 1) . '?training_view=1&search_training_ref=' . $object->ref_obj;
	$head [$h] [1] = $langs->trans("AgfMenuSess");
	$head [$h] [2] = 'sessions';
	$hselected = $h;
	$h ++;
	
	$head [$h] [0] = dol_buildpath('/agefodd/training/training_adm.php', 1) . '?id=' . $object->id;
	$head [$h] [1] = $langs->trans("AgftrainingAdmTask");
	$head [$h] [2] = 'trainingadmtask';
	$hselected = $h;
	$h ++;
	
	$head [$h] [0] = dol_buildpath('/agefodd/training/note.php', 1) . '?id=' . $object->id;
	$head [$h] [1] = $langs->trans("AgfNote");
	$head [$h] [2] = 'notes';
	$hselected = $h;
	$h ++;
	
	$head [$h] [0] = dol_buildpath('/agefodd/training/info.php', 1) . '?id=' . $object->id;
	$head [$h] [1] = $langs->trans("Info");
	$head [$h] [2] = 'info';
	$hselected = $h;
	$h ++;
	
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'agefodd_training');
	
	return $head;
}

/**
 * Return head table for session tabs screen
 *
 * @param object $object session
 * @param int $showconv if convention tabs have to be shown
 * @return array head table of tabs
 */
function session_prepare_head($object, $showconv = 0) {
	global $langs, $conf, $user;
	
	$h = 0;
	$head = array ();
	
	$head [$h] [0] = dol_buildpath('/agefodd/session/card.php', 1) . '?id=' . $object->id;
	$head [$h] [1] = $langs->trans("Card");
	$head [$h] [2] = 'card';
	$h ++;
	
	$head [$h] [0] = dol_buildpath('/agefodd/session/subscribers.php', 1) . '?id=' . $object->id;
	$head [$h] [1] = $langs->trans("AgfParticipant");
	$head [$h] [2] = 'subscribers';
	$h ++;
	
	if ($conf->global->AGF_MANAGE_CERTIF) {
		$head [$h] [0] = dol_buildpath('/agefodd/session/subscribers_certif.php', 1) . '?id=' . $object->id;
		$head [$h] [1] = $langs->trans("AgfCertificate");
		$head [$h] [2] = 'certificate';
		$h ++;
	}
	
	$head [$h] [0] = dol_buildpath('/agefodd/session/trainer.php', 1) . '?action=edit&id=' . $object->id;
	$head [$h] [1] = $langs->trans("AgfFormateur");
	$head [$h] [2] = 'trainers';
	$h ++;
	
	/*$head[$h][0] = DOL_URL_ROOT.'/agefodd/s_fpresence.php?id='.$object->id;
	 $head[$h][1] = $langs->trans("AgfFichePresence");
	$head[$h][2] = 'presence';
	$h++;*/
	// TODO fiche de presence
	
	$head [$h] [0] = dol_buildpath('/agefodd/session/administrative.php', 1) . '?id=' . $object->id;
	$head [$h] [1] = $langs->trans("AgfAdmSuivi");
	$head [$h] [2] = 'administrative';
	$h ++;
	
	$head [$h] [0] = dol_buildpath('/agefodd/session/document.php', 1) . '?id=' . $object->id;
	$head [$h] [1] = $langs->trans("AgfLinkedDocuments");
	$head [$h] [2] = 'document';
	$h ++;
	
	$head [$h] [0] = dol_buildpath('/agefodd/session/document_trainee.php', 1) . '?id=' . $object->id;
	$head [$h] [1] = $langs->trans("AgfLinkedDocumentsByTrainee");
	$head [$h] [2] = 'document_trainee';
	$h ++;
	
	$head [$h] [0] = dol_buildpath('/agefodd/session/send_docs.php', 1) . '?id=' . $object->id;
	$head [$h] [1] = $langs->trans("AgfSendDocuments");
	$head [$h] [2] = 'send_docs';
	$h ++;
	
	$head [$h] [0] = dol_buildpath('/agefodd/session/document_files.php', 1) . '?id=' . $object->id;
	$head [$h] [1] = $langs->trans("Documents");
	$head [$h] [2] = 'documentfiles';
	$h ++;
	
	if ($showconv) {
		$head [$h] [0] = dol_buildpath('/agefodd/session/convention.php', 1) . '?sessid=' . $object->id;
		$head [$h] [1] = $langs->trans("AgfConvention");
		$head [$h] [2] = 'convention';
		$h ++;
	}
	
	if (! empty($conf->global->AGF_ADVANCE_COST_MANAGEMENT)) {
		
		$head [$h] [0] = dol_buildpath('/agefodd/session/cost.php', 1) . '?id=' . $object->id;
		$head [$h] [1] = $langs->trans("AgfCostManagement");
		$head [$h] [2] = 'cost';
		$h ++;
	}
	
	$head [$h] [0] = dol_buildpath('/agefodd/session/info.php', 1) . '?id=' . $object->id;
	$head [$h] [1] = $langs->trans("Info");
	$head [$h] [2] = 'info';
	$h ++;
	
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'agefodd_session');
	
	return $head;
}

/**
 * Return head table for trainee tabs screen
 *
 * @param object $object trainee
 * @return array head table of tabs
 */
function trainee_prepare_head($object, $showcursus = 0) {
	global $langs, $conf, $user;
	
	$h = 0;
	$head = array ();
	
	$head [$h] [0] = dol_buildpath('/agefodd/trainee/card.php', 1) . '?id=' . $object->id;
	$head [$h] [1] = $langs->trans("Card");
	$head [$h] [2] = 'card';
	$h ++;
	
	if ($conf->global->AGF_MANAGE_CERTIF) {
		$head [$h] [0] = dol_buildpath('/agefodd/trainee/certificate.php', 1) . '?id=' . $object->id;
		$head [$h] [1] = $langs->trans("AgfCertificate");
		$head [$h] [2] = 'certificate';
		$h ++;
	}
	
	$head [$h] [0] = dol_buildpath('/agefodd/trainee/session.php', 1) . '?id=' . $object->id;
	$head [$h] [1] = $langs->trans("AgfSessionDetail");
	$head [$h] [2] = 'sessionlist';
	$h ++;
	
	if ($conf->global->AGF_MANAGE_CURSUS) {
		$head [$h] [0] = dol_buildpath('/agefodd/trainee/cursus.php', 1) . '?id=' . $object->id;
		$head [$h] [1] = $langs->trans("AgfMenuCursus");
		$head [$h] [2] = 'cursus';
		$h ++;
		
		if (! empty($showcursus)) {
			$head [$h] [0] = dol_buildpath('/agefodd/trainee/cursus_detail.php', 1) . '?id=' . $object->id . '&cursus_id=' . $object->cursus_id;
			$head [$h] [1] = $langs->trans("AgfCursusDetail");
			$head [$h] [2] = 'cursusdetail';
			$h ++;
		}
	}
	
	$head [$h] [0] = dol_buildpath('/agefodd/trainee/info.php', 1) . '?id=' . $object->id;
	$head [$h] [1] = $langs->trans("Info");
	$head [$h] [2] = 'info';
	$h ++;
	
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'agefodd_trainee');
	
	return $head;
}

/**
 * Return head table for trainer tabs screen
 *
 * @param object $object trainer
 * @return array head table of tabs
 */
function trainer_prepare_head($object) {
	global $langs, $conf, $user;
	
	$h = 0;
	$head = array ();
	
	$head [$h] [0] = dol_buildpath('/agefodd/trainer/card.php', 1) . '?id=' . $object->id;
	$head [$h] [1] = $langs->trans("Card");
	$head [$h] [2] = 'card';
	$h ++;
	
	$head [$h] [0] = dol_buildpath('/agefodd/trainer/session.php', 1) . '?id=' . $object->id;
	$head [$h] [1] = $langs->trans("AgfSessionDetail");
	$head [$h] [2] = 'sessionlist';
	$h ++;
	
	$head [$h] [0] = dol_buildpath('/agefodd/trainer/info.php', 1) . '?id=' . $object->id;
	$head [$h] [1] = $langs->trans("Info");
	$head [$h] [2] = 'info';
	$h ++;
	
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'agefodd_trainer');
	
	return $head;
}

/**
 * Return head table for contact tabs screen
 *
 * @param object $object contact
 * @return array head table of tabs
 */
function contact_prepare_head($object) {
	global $langs, $conf, $user;
	
	$h = 0;
	$head = array ();
	
	$head [$h] [0] = dol_buildpath('/agefodd/contact/card.php', 1) . '?id=' . $object->id;
	$head [$h] [1] = $langs->trans("Card");
	$head [$h] [2] = 'card';
	$h ++;
	
	$head [$h] [0] = dol_buildpath('/agefodd/contact/info.php', 1) . '?id=' . $object->id;
	$head [$h] [1] = $langs->trans("Info");
	$head [$h] [2] = 'info';
	$h ++;
	
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'agefodd_contact');
	
	return $head;
}

/**
 * Return head table for site tabs screen
 *
 * @param object $object site
 * @return array head table of tabs
 */
function site_prepare_head($object) {
	global $langs, $conf, $user;
	
	$h = 0;
	$head = array ();
	
	$head [$h] [0] = dol_buildpath('/agefodd/site/card.php', 1) . '?id=' . $object->id;
	$head [$h] [1] = $langs->trans("Card");
	$head [$h] [2] = 'card';
	$h ++;
	
	$head [$h] [0] = dol_buildpath('/agefodd/site/reg_int.php', 1) . '?id=' . $object->id;
	$head [$h] [1] = $langs->trans("AgfRegInt");
	$head [$h] [2] = 'reg_int';
	$h ++;
	
	$head [$h] [0] = dol_buildpath('/agefodd/session/list.php', 1) . '?site_view=1&search_site=' . $object->id;
	$head [$h] [1] = $langs->trans("AgfMenuSess");
	$head [$h] [2] = 'sessions';
	$h ++;
	
	$head [$h] [0] = dol_buildpath('/agefodd/site/info.php', 1) . '?id=' . $object->id;
	$head [$h] [1] = $langs->trans("Info");
	$head [$h] [2] = 'info';
	$h ++;
	
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'agefodd_site');
	
	return $head;
}

/**
 * Return head table for program tabs screen
 *
 * @param object $object program
 * @return array head table of tabs
 */
function cursus_prepare_head($object) {
	global $langs, $conf, $user;
	
	$h = 0;
	$head = array ();
	
	$head [$h] [0] = dol_buildpath('/agefodd/cursus/card.php', 1) . '?id=' . $object->id;
	$head [$h] [1] = $langs->trans("Card");
	$head [$h] [2] = 'card';
	$h ++;
	
	$head [$h] [0] = dol_buildpath('/agefodd/cursus/card_trainee.php', 1) . '?id=' . $object->id;
	$head [$h] [1] = $langs->trans("AgfMenuActStagiaire");
	$head [$h] [2] = 'trainee';
	$h ++;
	
	$head [$h] [0] = dol_buildpath('/agefodd/cursus/info.php', 1) . '?id=' . $object->id;
	$head [$h] [1] = $langs->trans("Info");
	$head [$h] [2] = 'info';
	$h ++;
	
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'agefodd_cursus');
	
	return $head;
}

/**
 * Return head table for admin tabs screen
 *
 * @return array head table of tabs
 */
function agefodd_admin_prepare_head() {
	global $langs, $conf;
	
	$langs->load("agefodd@agefodd");
	
	$h = 0;
	$head = array ();
	
	$head [$h] [0] = dol_buildpath("/agefodd/admin/admin_agefodd.php", 1);
	$head [$h] [1] = $langs->trans("Settings");
	$head [$h] [2] = 'settings';
	$h ++;
	
	$head [$h] [0] = dol_buildpath("/agefodd/admin/formation_catalogue_extrafields.php", 1);
	$head [$h] [1] = $langs->trans("ExtraFieldsTraining");
	$head [$h] [2] = 'attributetraining';
	$h ++;
	
	$head [$h] [0] = dol_buildpath("/agefodd/admin/session_extrafields.php", 1);
	$head [$h] [1] = $langs->trans("ExtraFieldsSessions");
	$head [$h] [2] = 'attributesession';
	$h ++;
	
	$head [$h] [0] = dol_buildpath("/agefodd/admin/cursus_extrafields.php", 1);
	$head [$h] [1] = $langs->trans("ExtraFieldsCursus");
	$head [$h] [2] = 'attributecursus';
	$h ++;
	
	$head [$h] [0] = dol_buildpath("/agefodd/admin/about.php", 1);
	$head [$h] [1] = $langs->trans("About");
	$head [$h] [2] = 'about';
	$h ++;
	
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'agefodd');
	
	return $head;
}

/**
 * Define head array for tabs of agenda setup pages
 *
 * @param string $param add to url
 * @return array Array of head
 */
function calendars_prepare_head($param) {
	global $langs, $conf, $user;
	
	$h = 0;
	$head = array ();
	
	$head [$h] [0] = dol_buildpath("/agefodd/agenda/index.php", 1) . ($param ? '?' . $param : '');
	$head [$h] [1] = $langs->trans("AgfMenuAgenda");
	$head [$h] [2] = 'card';
	$h ++;
	
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'agefodd_agenda');
	
	return $head;
}

/**
 * Calcule le nombre de regroupement par premier niveau des tâches adminsitratives
 *
 * @return int nbre de niveaux
 */
function ebi_get_adm_level_number() {
	global $db;
	
	$sql = "SELECT l.rowid, l.level_rank";
	$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session_admlevel as l";
	$sql .= " WHERE l.level_rank = 0";
	
	$result = $db->query($sql);
	if ($result) {
		$num = $db->num_rows($result);
		$db->free($result);
		return $num;
	} else {
		$error = "Error " . $db->lasterror();
		return - 1;
	}
}

/**
 * Calcule le nombre de regroupement par premier niveau des tâches adminsitratives
 *
 * @return int nbre de niveaux
 */
function ebi_get_adm_training_level_number() {
	global $db;
	
	$sql = "SELECT l.rowid, l.level_rank";
	$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_training_admlevel as l";
	$sql .= " WHERE l.level_rank = 0";
	
	$result = $db->query($sql);
	if ($result) {
		$num = $db->num_rows($result);
		$db->free($result);
		return $num;
	} else {
		$error = "Error " . $db->lasterror();
		return - 1;
	}
}

/**
 * Calcule le nombre de regroupement par premier niveau des tâches par session
 *
 * @param int $session de la session
 * @return int nbre de niveaux
 */
function ebi_get_level_number($session) {
	global $db;
	
	$sql = "SELECT l.rowid, l.level_rank";
	$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session_adminsitu as l";
	$sql .= " WHERE l.level_rank = 0 AND l.fk_agefodd_session=" . $session;
	
	dol_syslog("ebi_get_level_number sql=" . $sql, LOG_DEBUG);
	$result = $db->query($sql);
	if ($result) {
		$num = $db->num_rows($result);
		$db->free($result);
		return $num;
	} else {
		$error = "Error " . $db->lasterror();
		return - 1;
	}
}

/**
 * Calcule le nombre de regroupement par premier niveau terminés pour une session donnée
 *
 * @param int $sessid de la session
 * @return int nbre de niveaux
 */
function ebi_get_adm_lastFinishLevel($sessid) {
	global $db;
	
	$totaldone = 0;
	
	$sql = "SELECT rowid";
	$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session_adminsitu as s";
	$sql .= ' WHERE s.level_rank =0 ';
	$sql .= " AND fk_agefodd_session = " . $sessid;
	
	dol_syslog("ebi_get_adm_lastFinishLevel sql=" . $sql, LOG_DEBUG);
	$result = $db->query($sql);
	if ($result) {
		$num = $db->num_rows($result);
		if (! empty($num)) {
			while ( $obj = $db->fetch_object($result) ) {
				
				$sqlinner = "SELECT count(*) as cnt";
				$sqlinner .= " FROM " . MAIN_DB_PREFIX . "agefodd_session_adminsitu as s";
				$sqlinner .= ' WHERE s.level_rank <>0 ';
				$sqlinner .= " AND fk_parent_level = " . $obj->rowid . " AND fk_agefodd_session = " . $sessid;
				$sqlinner .= " AND archive = 1";
				
				dol_syslog("ebi_get_adm_lastFinishLevel sqlinner=" . $sqlinner, LOG_DEBUG);
				$resultinner = $db->query($sqlinner);
				if ($resultinner) {
					$objinner = $db->fetch_object($resultinner);
					
					$nbtaskdone = $objinner->cnt;
					
					$db->free($resultinner);
				} else {
					$error = "Error " . $db->lasterror();
					// print $error;
					return - 1;
				}
				
				$sqlinner = "SELECT count(*) as cnt";
				$sqlinner .= " FROM " . MAIN_DB_PREFIX . "agefodd_session_adminsitu as s";
				$sqlinner .= ' WHERE s.level_rank <>0 ';
				$sqlinner .= " AND fk_parent_level = " . $obj->rowid . " AND fk_agefodd_session = " . $sessid;
				
				dol_syslog("ebi_get_adm_lastFinishLevel sqlinner=" . $sqlinner, LOG_DEBUG);
				$resultinner = $db->query($sqlinner);
				if ($resultinner) {
					$objinner = $db->fetch_object($resultinner);
					
					$nbtotaltask = $objinner->cnt;
					
					$db->free($resultinner);
				} else {
					$error = "Error " . $db->lasterror();
					// print $error;
					return - 1;
				}
				
				dol_syslog("ebi_get_adm_lastFinishLevel nbtotaltask=" . $nbtotaltask, LOG_DEBUG);
				// No child check status
				if ($nbtotaltask == 0) {
					$sqlinner = "SELECT count(*) as cnt";
					$sqlinner .= " FROM " . MAIN_DB_PREFIX . "agefodd_session_adminsitu as s";
					$sqlinner .= " WHERE rowid=" . $obj->rowid;
					$sqlinner .= " AND archive = 1";
					
					dol_syslog("ebi_get_adm_lastFinishLevel sqlinner=" . $sqlinner, LOG_DEBUG);
					$resultinner = $db->query($sqlinner);
					if ($resultinner) {
						$objinner = $db->fetch_object($resultinner);
						
						$nbtaskdone = $objinner->cnt;
						
						$db->free($resultinner);
					} else {
						$error = "Error " . $db->lasterror();
						// print $error;
						return - 1;
					}
					
					$sqlinner = "SELECT count(*) as cnt";
					$sqlinner .= " FROM " . MAIN_DB_PREFIX . "agefodd_session_adminsitu as s";
					$sqlinner .= " WHERE rowid=" . $obj->rowid;
					
					dol_syslog("ebi_get_adm_lastFinishLevel sqlinner=" . $sqlinner, LOG_DEBUG);
					$resultinner = $db->query($sqlinner);
					if ($resultinner) {
						$objinner = $db->fetch_object($resultinner);
						
						$nbtotaltask = $objinner->cnt;
						
						$db->free($resultinner);
					} else {
						$error = "Error " . $db->lasterror();
						// print $error;
						return - 1;
					}
				}
				
				dol_syslog("ebi_get_adm_lastFinishLevel nbtaskdone=" . $nbtaskdone . " nbtotaltask=" . $nbtotaltask, LOG_DEBUG);
				// If number task done = nb task to do or no child level
				if (($nbtaskdone == $nbtotaltask))
					$totaldone ++;
			}
		}
		$db->free($result);
		dol_syslog("ebi_get_adm_lastFinishLevel totaldone=" . $totaldone, LOG_DEBUG);
		return $totaldone;
	} else {
		$error = "Error " . $db->lasterror();
		// print $error;
		return - 1;
	}
}

/**
 * Calcule le nombre de d'action filles
 *
 * @param int $id du niveaux
 * @return int nbre d'action filles
 */
function ebi_get_adm_indice_action_child($id) {
	global $db;
	
	$sql = "SELECT MAX(s.indice) as nb_action";
	$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session_admlevel as s";
	$sql .= " WHERE fk_parent_level=" . $id;
	
	dol_syslog("agefodd:lib:ebi_get_adm_indice_action_child sql=" . $sql, LOG_DEBUG);
	$result = $db->query($sql);
	if ($result) {
		$num = $db->num_rows($result);
		$obj = $db->fetch_object($result);
		
		$db->free($result);
		return $obj->nb_action;
	} else {
		$error = "Error " . $db->lasterror();
		return - 1;
	}
}

/**
 * Calcule l'indice min ou max d'un niveau
 *
 * @param int $lvl_rank des actions a tester
 * @param int $parent_level parent
 * @param int $type MIN ou MAX
 * @return int indice
 */
function ebi_get_adm_indice_per_rank($lvl_rank, $parent_level = '', $type = 'MIN') {
	global $db;
	
	$sql = "SELECT ";
	if ($type == 'MIN') {
		$sql .= ' MIN(s.indice) ';
	} else {
		$sql .= ' MAX(s.indice) ';
	}
	$sql .= " as indice";
	$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session_admlevel as s";
	$sql .= " WHERE s.level_rank=" . $lvl_rank;
	if ($parent_level != '') {
		$sql .= " AND s.fk_parent_level=" . $parent_level;
	}
	
	$result = $db->query($sql);
	if ($result) {
		$num = $db->num_rows($result);
		$obj = $db->fetch_object($result);
		
		$db->free($result);
		return $obj->indice;
	} else {
		$error = "Error " . $db->lasterror();
		return - 1;
	}
}

/**
 * Calcule l'indice min ou max d'un niveau
 *
 * @param int $lvl_rank des actions a tester
 * @param int $parent_level parent
 * @param int $type MIN ou MAX
 * @return int indice
 */
function ebi_get_adm_training_indice_per_rank($lvl_rank, $parent_level = '', $type = 'MIN') {
	global $db;
	
	$sql = "SELECT ";
	if ($type == 'MIN') {
		$sql .= ' MIN(s.indice) ';
	} else {
		$sql .= ' MAX(s.indice) ';
	}
	$sql .= " as indice";
	$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_training_admlevel as s";
	$sql .= " WHERE s.level_rank=" . $lvl_rank;
	if ($parent_level != '') {
		$sql .= " AND s.fk_parent_level=" . $parent_level;
	}
	
	$result = $db->query($sql);
	if ($result) {
		$num = $db->num_rows($result);
		$obj = $db->fetch_object($result);
		
		$db->free($result);
		return $obj->indice;
	} else {
		$error = "Error " . $db->lasterror();
		return - 1;
	}
}

/**
 * Formatage d'une liste à puce
 *
 * @param string $text chaine
 * @param boolean $form sortie au format html (true) ou texte (false)
 * @return string la chaine formater
 */
function ebi_liste_a_puce($text, $form = false) {
	// 1er niveau: remplacement de '# ' en debut de ligne par une puce de niv 1 (petit rond noir)
	// 2éme niveau: remplacement de '## ' en début de ligne par une puce de niv 2 (tiret)
	// 3éme niveau: remplacement de '### ' en début de ligne par une puce de niv 3 (>)
	// Pour annuler le formatage (début de ligne sur la mage gauche : '!#'
	$str = "";
	$line = explode("\n", $text);
	$level = 0;
	foreach ( $line as $row ) {
		if ($form) {
			if (preg_match('/^\!# /', $row)) {
				if ($level == 1)
					$str .= '</ul>' . "\n";
				if ($level == 2)
					$str .= '<ul>' . "\n" . '</ul>' . "\n";
				if ($level == 3)
					$str .= '</ul>' . "\n" . '</ul>' . "\n" . '</ul>' . "\n";
				$str .= preg_replace('/^\!# /', '', $row . '<br />') . "\n";
			} elseif (preg_match('/^# /', $row)) {
				if ($level == 0)
					$str .= '<ul>';
				if ($level == 2)
					$str .= '</ul>' . "\n";
				if ($level == 3)
					$str .= '</ul>' . "\n" . '</ul>' . "\n";
				$str .= '<li>' . preg_replace('/^# /', '', $row) . '</li>' . "\n";
				$level = 1;
			} elseif (preg_match('/^## /', $row)) {
				if ($level == 1)
					$str .= '<ul>';
				if ($level == 3)
					$str .= '</ul>' . "\n";
				$str .= '<li>' . preg_replace('/^## /', '', $row) . '</li>' . "\n";
				$level = 2;
			} elseif (preg_match('/^### /', $row)) {
				if ($level == 2)
					$str .= '<ul>';
				$str .= '<li>' . preg_replace('/^### /', '', $row) . '</li>' . "\n";
				$level = 3;
			} else
				$str .= '   ' . $row . '<br />' . "\n";
		} else {
			if (preg_match('/^\!# /', $row))
				$str .= preg_replace('/^\!# /', '', $row) . "\n";
			elseif (preg_match('/^# /', $row))
				$str .= chr(149) . ' ' . preg_replace('/^#/', '', $row) . "\n";
			elseif (preg_match('/^## /', $row))
				$str .= '   ' . '-' . preg_replace('/^##/', '', $row) . "\n";
			elseif (preg_match('/^### /', $row))
				$str .= '   ' . '  ' . chr(155) . ' ' . preg_replace('/^###/', '', $row) . "\n";
			else
				$str .= '   ' . $row . "\n";
		}
	}
	return $str;
}

/**
 * Calcule le next number d'indice pour une action (ecran conf module)
 *
 * @param int $id du niveaux
 * @return int action next number
 */
function ebi_get_adm_get_next_indice_action($id) {
	global $db;
	
	$sql = "SELECT MAX(s.indice) as nb_action";
	$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session_admlevel as s";
	$sql .= " WHERE fk_parent_level=" . $id;
	
	dol_syslog("agefodd:lib:ebi_get_adm_get_next_indice_action sql=" . $sql, LOG_DEBUG);
	$result = $db->query($sql);
	if ($result) {
		$num = $db->num_rows($result);
		$obj = $db->fetch_object($result);
		$db->free($result);
		if (! empty($obj->nb_action)) {
			return intval(intval($obj->nb_action) + 1);
		} else {
			$sql = "SELECT MAX(s.indice) as nb_action";
			$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session_admlevel as s";
			$sql .= " WHERE fk_parent_level=(SELECT fk_parent_level FROM " . MAIN_DB_PREFIX . "agefodd_session_admlevel WHERE rowid=" . $id . ")";
			
			dol_syslog("agefodd:lib:ebi_get_adm_get_next_indice_action sql=" . $sql, LOG_DEBUG);
			$result = $db->query($sql);
			if ($result) {
				$num = $db->num_rows($result);
				$obj = $db->fetch_object($result);
				
				$db->free($result);
				return intval(intval($obj->nb_action) + 1);
			} else {
				
				$error = "Error " . $db->lasterror();
				return - 1;
			}
		}
	} else {
		
		$error = "Error " . $db->lasterror();
		return - 1;
	}
}

/**
 * Calcule le next number d'indice pour une action (ecran conf module)
 *
 * @param int $id du niveaux
 * @return int action next number
 */
function ebi_get_adm_training_get_next_indice_action($id) {
	global $db;
	
	$sql = "SELECT MAX(s.indice) as nb_action";
	$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_training_admlevel as s";
	$sql .= " WHERE fk_parent_level=" . $id;
	
	dol_syslog("ebi_get_adm_training_get_next_indice_action sql=" . $sql, LOG_DEBUG);
	$result = $db->query($sql);
	if ($result) {
		$num = $db->num_rows($result);
		$obj = $db->fetch_object($result);
		$db->free($result);
		if (! empty($obj->nb_action)) {
			return intval(intval($obj->nb_action) + 1);
		} else {
			$sql = "SELECT MAX(s.indice) as nb_action";
			$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_training_admlevel as s";
			$sql .= " WHERE fk_parent_level=(SELECT fk_parent_level FROM " . MAIN_DB_PREFIX . "agefodd_training_admlevel WHERE rowid=" . $id . ")";
			
			dol_syslog("ebi_get_adm_training_get_next_indice_action sql=" . $sql, LOG_DEBUG);
			$result = $db->query($sql);
			if ($result) {
				$num = $db->num_rows($result);
				$obj = $db->fetch_object($result);
				
				$db->free($result);
				return intval(intval($obj->nb_action) + 1);
			} else {
				
				$error = "Error " . $db->lasterror();
				return - 1;
			}
		}
	} else {
		
		$error = "Error " . $db->lasterror();
		return - 1;
	}
}

/**
 * Calcule le next number d'indice pour une action (pour une session)
 *
 * @param int $id du niveaux
 * @param int $sessionid de la session
 * @return int action next number
 */
function ebi_get_next_indice_action($id, $sessionid) {
	global $db;
	
	$sql = "SELECT MAX(s.indice) as nb_action";
	$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session_adminsitu as s";
	$sql .= " WHERE fk_parent_level=" . $id;
	$sql .= " AND fk_agefodd_session=" . $sessionid;
	
	dol_syslog("ebi_get_get_next_indice_action sql=" . $sql, LOG_DEBUG);
	$result = $db->query($sql);
	if ($result) {
		$num = $db->num_rows($result);
		$obj = $db->fetch_object($result);
		$db->free($result);
		if (! empty($obj->nb_action)) {
			return intval(intval($obj->nb_action) + 1);
		} else {
			$sql = "SELECT MAX(s.indice) as nb_action";
			$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session_adminsitu as s";
			$sql .= " WHERE fk_parent_level=(SELECT fk_parent_level FROM " . MAIN_DB_PREFIX . "agefodd_session_adminsitu WHERE rowid=" . $id . " AND fk_agefodd_session=" . $sessionid . ")";
			$sql .= " AND fk_agefodd_session=" . $sessionid;
			
			dol_syslog("ebi_get_get_next_indice_action sql=" . $sql, LOG_DEBUG);
			$result = $db->query($sql);
			if ($result) {
				$num = $db->num_rows($result);
				$obj = $db->fetch_object($result);
				
				$db->free($result);
				return intval(intval($obj->nb_action) + 1);
			} else {
				
				$error = "Error " . $db->lasterror();
				return - 1;
			}
		}
	} else {
		
		$error = "Error " . $db->lasterror();
		return - 1;
	}
}

/**
 * Converti un code couleur hexa en tableau des couleurs RGB
 *
 * @param string $hex hexadecimale
 * @return array définition RGB
 */
function agf_hex2rgb($hex) {
	$hex = str_replace("#", "", $hex);
	
	if (strlen($hex) == 3) {
		$r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
		$g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
		$b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
	} else {
		$r = hexdec(substr($hex, 0, 2));
		$g = hexdec(substr($hex, 2, 2));
		$b = hexdec(substr($hex, 4, 2));
	}
	$rgb = array (
			$r,
			$g,
			$b 
	);
	// return implode(",", $rgb); // returns the rgb values separated by commas
	return $rgb; // returns an array with the rgb values
}
?>
