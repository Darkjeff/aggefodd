<?php
/* Copyright (C) 2004      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2012 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (C) 2015      Raphaël Doursenaud   <rdoursenaud@gpcsolutions.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file /agefodd/dev/check_data_integrity.php
 * \brief dev part
 */
$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");

llxHeader('', $langs->trans('AgefoddShort'));


//agefodd_session_formateur
$sql = 'SELECT fk_session as fk_session FROM '.MAIN_DB_PREFIX.'agefodd_session_formateur WHERE fk_session NOT IN (SELECT rowid FROM '.MAIN_DB_PREFIX.'agefodd_session)';

$resql = $db->query($sql);
if ($resql) {
	if ($db->num_rows($resql)) {
		print '<BR><BR>';
		while ( $obj = $db->fetch_object($resql) ) {
			print 'Session '.$obj->fk_session.' dans '.MAIN_DB_PREFIX.'agefodd_session_formateur et non dans '.MAIN_DB_PREFIX.'agefodd_session<BR>';
		}

		$sql = 'SET FOREIGN_KEY_CHECKS=0; DELETE FROM '.MAIN_DB_PREFIX.'agefodd_session_formateur WHERE fk_session NOT IN (SELECT rowid FROM '.MAIN_DB_PREFIX.'agefodd_session)';
		if(GETPOST('do-it-for-me')=='yesiwantit') {
			$res = $db->query($sql);
			if($res===false) {
				var_dump($sql);exit;
			}
		}

		print '<BR><BR><BR>Suggestion de correction : '.$sql.' <BR><BR><BR>';
	}
}else {
	dol_print_error($db);
}

//agefodd_session_stagiaire
$sql = 'SELECT fk_session_agefodd as fk_session FROM '.MAIN_DB_PREFIX.'agefodd_session_stagiaire WHERE fk_session_agefodd NOT IN (SELECT rowid FROM '.MAIN_DB_PREFIX.'agefodd_session)';

$resql = $db->query($sql);
if ($resql) {
	if ($db->num_rows($resql)) {
		print '<BR><BR>';
		while ( $obj = $db->fetch_object($resql) ) {
			print 'Session '.$obj->fk_session.' dans '.MAIN_DB_PREFIX.'agefodd_session_stagiaire et non dans '.MAIN_DB_PREFIX.'agefodd_session<BR>';
		}

		$sql ='DELETE FROM '.MAIN_DB_PREFIX.'agefodd_session_stagiaire WHERE fk_session_agefodd NOT IN (SELECT rowid FROM llx_agefodd_session)';
                if(GETPOST('do-it-for-me')=='yesiwantit') {
                        $res = $db->query($sql);
                        if($res===false) {
                                var_dump($sql);exit;
                        }
                }

		print '<BR><BR><BR>Suggestion de correction : '.$sql.'<BR><BR><BR>';
	}
}else {
	dol_print_error($db);
}

//agefodd_session_formateur
$sql = 'SELECT  fk_agefodd_formateur as  fk_agefodd_formateur FROM '.MAIN_DB_PREFIX.'agefodd_session_formateur WHERE  fk_agefodd_formateur NOT IN (SELECT rowid FROM '.MAIN_DB_PREFIX.'agefodd_formateur)';

$resql = $db->query($sql);
if ($resql) {
	if ($db->num_rows($resql)) {
		print '<BR><BR>';
		while ( $obj = $db->fetch_object($resql) ) {
			print 'Foramteur '.$obj-> 	fk_agefodd_formateur.' dans '.MAIN_DB_PREFIX.'agefodd_session_formateur et non dans '.MAIN_DB_PREFIX.'agefodd_session<BR>';
		}

$sql ='DELETE FROM '.MAIN_DB_PREFIX.'agefodd_session_formateur WHERE fk_agefodd_formateur NOT IN (SELECT rowid FROM llx_agefodd_formateur)';
                if(GETPOST('do-it-for-me')=='yesiwantit') {
                        $res = $db->query($sql);
                        if($res===false) {
                                var_dump($sql);exit;
                        }
                }

print '<BR><BR><BR>Suggestion de correction : '.$sql.'<BR><BR><BR>';
	}
}else {
	dol_print_error($db);
}



//agefodd_session_adminsitu
$sql = 'SELECT fk_agefodd_session as fk_session FROM '.MAIN_DB_PREFIX.'agefodd_session_adminsitu WHERE fk_agefodd_session NOT IN (SELECT rowid FROM '.MAIN_DB_PREFIX.'agefodd_session)';

$resql = $db->query($sql);
if ($resql) {
	if ($db->num_rows($resql)) {
		print '<BR><BR>';
		while ( $obj = $db->fetch_object($resql) ) {
			print 'Session '.$obj->fk_session.' dans '.MAIN_DB_PREFIX.'agefodd_session_adminsitu et non dans '.MAIN_DB_PREFIX.'agefodd_session<BR>';
		}


$sql ='DELETE FROM '.MAIN_DB_PREFIX.'agefodd_session_adminsitu WHERE fk_agefodd_session NOT IN (SELECT rowid FROM llx_agefodd_session)';
                if(GETPOST('do-it-for-me')=='yesiwantit') {
                        $res = $db->query($sql);
                        if($res===false) {
                                var_dump($sql);exit;
                        }
                }

print '<BR><BR><BR>Suggestion de correction : '.$sql.'<BR><BR><BR>';

	}
}else {
	dol_print_error($db);
}


//agefodd_session_commercial
$sql = 'SELECT fk_session_agefodd as fk_session FROM '.MAIN_DB_PREFIX.'agefodd_session_commercial WHERE fk_session_agefodd NOT IN (SELECT rowid FROM '.MAIN_DB_PREFIX.'agefodd_session)';

$resql = $db->query($sql);
if ($resql) {
	if ($db->num_rows($resql)) {
		print '<BR><BR>';
		while ( $obj = $db->fetch_object($resql) ) {
			print 'Session '.$obj->fk_session.' dans '.MAIN_DB_PREFIX.'agefodd_session_commercial et non dans '.MAIN_DB_PREFIX.'agefodd_session<BR>';
		}

$sql ='DELETE FROM '.MAIN_DB_PREFIX.'agefodd_session_commercial WHERE fk_session_agefodd NOT IN (SELECT rowid FROM llx_agefodd_session)';
                if(GETPOST('do-it-for-me')=='yesiwantit') {
                        $res = $db->query($sql);
                        if($res===false) {
                                var_dump($sql);exit;
                        }
                }

print '<BR><BR><BR>Suggestion de correction : '.$sql.'<BR><BR><BR>';

	}
}else {
	dol_print_error($db);
}

//agefodd_session_calendrier
$sql = 'SELECT fk_agefodd_session as fk_session FROM '.MAIN_DB_PREFIX.'agefodd_session_calendrier WHERE fk_agefodd_session NOT IN (SELECT rowid FROM '.MAIN_DB_PREFIX.'agefodd_session)';

$resql = $db->query($sql);
if ($resql) {
	if ($db->num_rows($resql)) {
		print '<BR><BR>';
		while ( $obj = $db->fetch_object($resql) ) {
			print 'Session '.$obj->fk_session.' dans '.MAIN_DB_PREFIX.'agefodd_session_calendrier et non dans '.MAIN_DB_PREFIX.'agefodd_session<BR>';
		}

$sql ='DELETE FROM '.MAIN_DB_PREFIX.'agefodd_session_calendrier WHERE fk_agefodd_session NOT IN (SELECT rowid FROM llx_agefodd_session)';
                if(GETPOST('do-it-for-me')=='yesiwantit') {
                        $res = $db->query($sql);
                        if($res===false) {
                                var_dump($sql);exit;
                        }
                }

print '<BR><BR><BR>Suggestion de correction : '.$sql.'<BR><BR><BR>';
	}
}else {
	dol_print_error($db);
}



//agefodd_session_calendrier
$sql = 'SELECT fk_agefodd_session as fk_session FROM '.MAIN_DB_PREFIX.'agefodd_session_calendrier WHERE fk_actioncomm NOT IN (SELECT id FROM '.MAIN_DB_PREFIX.'actioncomm)';

$resql = $db->query($sql);
if ($resql) {
	if ($db->num_rows($resql)) {
		print '<BR><BR>';
		while ( $obj = $db->fetch_object($resql) ) {
			print 'Session '.$obj->fk_session.' dans '.MAIN_DB_PREFIX.'agefodd_session_calendrier et non dans '.MAIN_DB_PREFIX.'actioncomm<BR>';
		}

$sql ='DELETE FROM '.MAIN_DB_PREFIX.'agefodd_session_calendrier WHERE fk_actioncomm NOT IN (SELECT id FROM llx_actioncomm)';
                if(GETPOST('do-it-for-me')=='yesiwantit') {
                        $res = $db->query($sql);
                        if($res===false) {
                                var_dump($sql);exit;
                        }
                }

print '<BR><BR><BR>Suggestion de correction : '.$sql.'<BR><BR><BR>';

	}
}else {
	dol_print_error($db);
}

//agefodd_session_calendrier
$sql = 'SELECT fk_category as fk_category FROM '.MAIN_DB_PREFIX.'agefodd_formateur_category WHERE fk_category NOT IN (SELECT rowid FROM '.MAIN_DB_PREFIX.'agefodd_formateur_category_dict)';

$resql = $db->query($sql);
if ($resql) {
	if ($db->num_rows($resql)) {
		print '<BR><BR>';
		while ( $obj = $db->fetch_object($resql) ) {
			print 'Session '.$obj->fk_category.' dans '.MAIN_DB_PREFIX.'agefodd_formateur_category et non dans '.MAIN_DB_PREFIX.'agefodd_formateur_category_dict<BR>';
		}
		print '<BR><BR><BR>Suggestion de correction : DELETE FROM '.MAIN_DB_PREFIX.'agefodd_formateur_category WHERE fk_category NOT IN (SELECT rowid FROM '.MAIN_DB_PREFIX.'agefodd_formateur_category_dict)<BR><BR><BR>';
	}
}else {
	dol_print_error($db);
}

$sql = 'SELECT fk_cursus as fk_cursus FROM '.MAIN_DB_PREFIX.'agefodd_stagiaire_cursus WHERE fk_cursus NOT IN (SELECT rowid FROM '.MAIN_DB_PREFIX.'agefodd_cursus)';

$resql = $db->query($sql);
if ($resql) {
	if ($db->num_rows($resql)) {
		print '<BR><BR>';
		while ( $obj = $db->fetch_object($resql) ) {
			print 'Cursus '.$obj->fk_cursus.' dans '.MAIN_DB_PREFIX.'agefodd_formateur_category et non dans '.MAIN_DB_PREFIX.'agefodd_formateur_category_dict<BR>';
		}
		print '<BR><BR><BR>Suggestion de correction : DELETE FROM '.MAIN_DB_PREFIX.'agefodd_stagiaire_cursus WHERE fk_cursus NOT IN (SELECT rowid FROM '.MAIN_DB_PREFIX.'agefodd_cursus)<BR><BR><BR>';
	}
}else {
	dol_print_error($db);
}


$sql = 'SELECT rowid,nom,prenom,civilite FROM '.MAIN_DB_PREFIX.'agefodd_stagiaire WHERE civilite NOT IN (SELECT code FROM '.MAIN_DB_PREFIX.'c_civility)';

$resql = $db->query($sql);
if ($resql) {
	if ($db->num_rows($resql)) {
		print '<BR><BR>';
		while ( $obj = $db->fetch_object($resql) ) {
			print 'Civité '.$obj->civilite.' du participant id:'.$obj->rowid.'- nom:'.$obj->nom.' prenom:'.$obj->prenom.' n existe pas dans le dictionnaire des civilité<BR>';
		}
		print '<BR><BR><BR>Suggestion de correction : UPDATE '.MAIN_DB_PREFIX.'agefodd_stagiaire SET civilite=(SELECT code FROM '.MAIN_DB_PREFIX.'c_civility LIMIT 1) WHERE civilite NOT IN (SELECT code FROM '.MAIN_DB_PREFIX.'c_civility)<BR><BR><BR>';
	}
}else {
	dol_print_error($db);
}


$sql = 'SELECT fk_formation_catalogue FROM '.MAIN_DB_PREFIX.'agefodd_formation_objectifs_peda WHERE fk_formation_catalogue NOT IN (SELECT rowid FROM '.MAIN_DB_PREFIX.'agefodd_formation_catalogue)';

$resql = $db->query($sql);
if ($resql) {
	if ($db->num_rows($resql)) {
		print '<BR><BR>';
		while ( $obj = $db->fetch_object($resql) ) {
			print 'Formation id:'.$obj->fk_formation_catalogue.' n existe pas dans la table  agefodd_formation_catalogue<BR>';
		}
$sql ='DELETE FROM '.MAIN_DB_PREFIX.'agefodd_formation_objectifs_peda WHERE fk_formation_catalogue NOT IN (SELECT rowid FROM llx_agefodd_formation_catalogue)';
                if(GETPOST('do-it-for-me')=='yesiwantit') {
                        $res = $db->query($sql);
                        if($res===false) {
                                var_dump($sql);exit;
                        }
                }

print '<BR><BR><BR>Suggestion de correction : '.$sql.'<BR><BR><BR>';

	}
}else {
	dol_print_error($db);
}


$sql = 'SELECT fk_session_agefodd FROM '.MAIN_DB_PREFIX.'agefodd_session_contact WHERE fk_session_agefodd NOT IN (SELECT rowid FROM '.MAIN_DB_PREFIX.'agefodd_session)';

$resql = $db->query($sql);
if ($resql) {
	if ($db->num_rows($resql)) {
		print '<BR><BR>';
		while ( $obj = $db->fetch_object($resql) ) {
			print 'Session id:'.$obj->fk_formation_catalogue.' n existe pas dans la table  agefodd_Session<BR>';
		}
$sql ='DELETE FROM '.MAIN_DB_PREFIX.'agefodd_session_contact WHERE fk_session_agefodd NOT IN (SELECT rowid FROM llx_agefodd_session)';
                if(GETPOST('do-it-for-me')=='yesiwantit') {
                        $res = $db->query($sql);
                        if($res===false) {
                                var_dump($sql);exit;
                        }
                }

print '<BR><BR><BR>Suggestion de correction : '.$sql.'<BR><BR><BR>';

	}
}else {
	dol_print_error($db);
}


$sql = 'SELECT fk_agefodd_session FROM '.MAIN_DB_PREFIX.'agefodd_convention WHERE fk_agefodd_session NOT IN (SELECT rowid FROM '.MAIN_DB_PREFIX.'agefodd_session)';

$resql = $db->query($sql);
if ($resql) {
	if ($db->num_rows($resql)) {
		print '<BR><BR>';
		while ( $obj = $db->fetch_object($resql) ) {
			print 'Session id:'.$obj->fk_formation_catalogue.' n existe pas dans la table  agefodd_Session<BR>';
		}

$sql ='DELETE FROM '.MAIN_DB_PREFIX.'agefodd_convention WHERE fk_agefodd_session NOT IN (SELECT rowid FROM llx_agefodd_session)';
                if(GETPOST('do-it-for-me')=='yesiwantit') {
                        $res = $db->query($sql);
                        if($res===false) {
                                var_dump($sql);exit;
                        }
                }

print '<BR><BR><BR>Suggestion de correction : '.$sql.'<BR><BR><BR>';

	}
}else {
	dol_print_error($db);
}


$sql = 'SELECT fk_user_com FROM '.MAIN_DB_PREFIX.'agefodd_session_commercial WHERE fk_user_com NOT IN (SELECT rowid FROM '.MAIN_DB_PREFIX.'user)';

$resql = $db->query($sql);
if ($resql) {
	if ($db->num_rows($resql)) {
		print '<BR><BR>';
		while ( $obj = $db->fetch_object($resql) ) {
			print 'USer id:'.$obj->fk_user_com.' n existe pas dans la table  user<BR>';
		}
		print '<BR><BR><BR>Suggestion de correction : DELETE FROM '.MAIN_DB_PREFIX.'agefodd_session_commercial WHERE fk_user_com NOT IN (SELECT rowid FROM '.MAIN_DB_PREFIX.'user)<BR><BR><BR>';
	}
}else {
	dol_print_error($db);
}


$sql = 'SELECT fk_stagiaire FROM '.MAIN_DB_PREFIX.'agefodd_session_stagiaire WHERE fk_stagiaire NOT IN (SELECT rowid FROM '.MAIN_DB_PREFIX.'agefodd_stagiaire)';

$resql = $db->query($sql);
if ($resql) {
	if ($db->num_rows($resql)) {
		print '<BR><BR>';
		while ( $obj = $db->fetch_object($resql) ) {
			print 'Stagiaire id:'.$obj->fk_stagiaire.' n existe pas dans la table  agefodd_Session<BR>';
		}
		print '<BR><BR><BR>Suggestion de correction : DELETE FROM '.MAIN_DB_PREFIX.'agefodd_session_stagiaire WHERE fk_stagiaire NOT IN (SELECT rowid FROM '.MAIN_DB_PREFIX.'agefodd_stagiaire)<BR><BR><BR>';
	}
}else {
	dol_print_error($db);
}

$sql = 'SELECT fk_session_agefodd FROM '.MAIN_DB_PREFIX.'agefodd_session_element WHERE fk_session_agefodd NOT IN (SELECT rowid FROM '.MAIN_DB_PREFIX.'agefodd_session)';

$resql = $db->query($sql);
if ($resql) {
	if ($db->num_rows($resql)) {
		print '<BR><BR>';
		while ( $obj = $db->fetch_object($resql) ) {
			print 'session id:'.$obj->fk_session_agefodd.' n existe pas dans la table  agefodd_Session<BR>';
		}

$sql ='DELETE FROM '.MAIN_DB_PREFIX.'agefodd_session_element WHERE fk_session_agefodd NOT IN (SELECT rowid FROM llx_agefodd_session)';
                if(GETPOST('do-it-for-me')=='yesiwantit') {
                        $res = $db->query($sql);
                        if($res===false) {
                                var_dump($sql);exit;
                        }
                }

print '<BR><BR><BR>Suggestion de correction : '.$sql.'<BR><BR><BR>';

	}
}else {
	dol_print_error($db);
}


$sql = 'SELECT fk_training FROM '.MAIN_DB_PREFIX.'agefodd_training_admlevel WHERE fk_training NOT IN (SELECT rowid FROM '.MAIN_DB_PREFIX.'agefodd_formation_catalogue)';

$resql = $db->query($sql);
if ($resql) {
	if ($db->num_rows($resql)) {
		print '<BR><BR>';
		while ( $obj = $db->fetch_object($resql) ) {
			print 'training id:'.$obj->fk_training.' n existe pas dans la table agefodd_Session<BR>';
		}
$sql ='DELETE FROM '.MAIN_DB_PREFIX.'agefodd_training_admlevel WHERE fk_training NOT IN (SELECT rowid FROM llx_agefodd_formation_catalogue)';
                if(GETPOST('do-it-for-me')=='yesiwantit') {
                        $res = $db->query($sql);
                        if($res===false) {
                                var_dump($sql);exit;
                        }
                }

print '<BR><BR><BR>Suggestion de correction : '.$sql.'<BR><BR><BR>';

	}
}else {
	dol_print_error($db);
}

$sql = 'SELECT fk_cursus FROM '.MAIN_DB_PREFIX.'agefodd_formation_cursus WHERE fk_cursus NOT IN (SELECT rowid FROM '.MAIN_DB_PREFIX.'agefodd_cursus)';

$resql = $db->query($sql);
if ($resql) {
	if ($db->num_rows($resql)) {
		print '<BR><BR>';
		while ( $obj = $db->fetch_object($resql) ) {
			print 'cursus id:'.$obj->fk_cursus.' n existe pas dans la table agefodd_cursus<BR>';
		}
		print '<BR><BR><BR>Suggestion de correction : DELETE FROM '.MAIN_DB_PREFIX.'agefodd_formation_cursus WHERE fk_cursus NOT IN (SELECT rowid FROM '.MAIN_DB_PREFIX.'agefodd_cursus)<BR><BR><BR>';
	}
}else {
	dol_print_error($db);
}

$sql = 'SELECT fk_agefodd_session_formateur FROM '.MAIN_DB_PREFIX.'agefodd_session_formateur_calendrier WHERE fk_agefodd_session_formateur NOT IN (SELECT rowid FROM '.MAIN_DB_PREFIX.'agefodd_session_formateur)';

$resql = $db->query($sql);
if ($resql) {
	if ($db->num_rows($resql)) {
		print '<BR><BR>';
		while ( $obj = $db->fetch_object($resql) ) {
			print 'fk_agefodd_session_formateur id:'.$obj->fk_agefodd_session_formateur.' n existe pas dans la table agefodd_session_formateur<BR>';
		}
		print '<BR><BR><BR>Suggestion de correction : DELETE FROM '.MAIN_DB_PREFIX.'agefodd_session_formateur_calendrier WHERE fk_agefodd_session_formateur NOT IN (SELECT rowid FROM '.MAIN_DB_PREFIX.'agefodd_session_formateur)<BR><BR><BR>';
	}
}else {
	dol_print_error($db);
}

$sql = 'SELECT fk_session_place FROM '.MAIN_DB_PREFIX.'agefodd_session WHERE fk_session_place NOT IN (SELECT rowid FROM '.MAIN_DB_PREFIX.'agefodd_place)';

$resql = $db->query($sql);
if ($resql) {
	if ($db->num_rows($resql)) {
		print '<BR><BR>';
		while ( $obj = $db->fetch_object($resql) ) {
			print 'fk_session_place id:'.$obj->fk_session_place.' n existe pas dans la table agefodd_place<BR>';
		}
		print '<BR><BR><BR>Suggestion de correction : DELETE FROM '.MAIN_DB_PREFIX.'agefodd_session WHERE fk_session_place NOT IN (SELECT rowid FROM '.MAIN_DB_PREFIX.'agefodd_place)<BR><BR><BR>';
	}
}else {
	dol_print_error($db);
}

$sql = 'SELECT fk_socpeople FROM '.MAIN_DB_PREFIX.'agefodd_contact WHERE fk_socpeople NOT IN (SELECT rowid FROM '.MAIN_DB_PREFIX.'socpeople)';

$resql = $db->query($sql);
if ($resql) {
	if ($db->num_rows($resql)) {
		print '<BR><BR>';
		while ( $obj = $db->fetch_object($resql) ) {
			print 'fk_socpeople id:'.$obj->fk_socpeople.' n existe pas dans la table socpeople<BR>';
		}

$sql ='DELETE FROM '.MAIN_DB_PREFIX.'agefodd_contact WHERE fk_socpeople NOT IN (SELECT rowid FROM llx_socpeople)';
                if(GETPOST('do-it-for-me')=='yesiwantit') {
                        $res = $db->query($sql);
                        if($res===false) {
                                var_dump($sql);exit;
                        }
                }

print '<BR><BR><BR>Suggestion de correction : '.$sql.'<BR><BR><BR>';

	}
}else {
	dol_print_error($db);
}


$sql = 'SELECT fk_agefodd_convention FROM '.MAIN_DB_PREFIX.'agefodd_convention_stagiaire WHERE fk_agefodd_convention NOT IN (SELECT rowid FROM '.MAIN_DB_PREFIX.'agefodd_convention)';

$resql = $db->query($sql);
if ($resql) {
	if ($db->num_rows($resql)) {
		print '<BR><BR>';
		while ( $obj = $db->fetch_object($resql) ) {
			print 'fk_agefodd_convention id:'.$obj->fk_agefodd_convention.' n existe pas dans la table agefodd_convention<BR>';
		}
		print '<BR><BR><BR>Suggestion de correction : DELETE FROM '.MAIN_DB_PREFIX.'agefodd_convention_stagiaire WHERE fk_agefodd_convention NOT IN (SELECT rowid FROM '.MAIN_DB_PREFIX.'agefodd_convention)<BR><BR><BR>';
	}
}else {
	dol_print_error($db);
}

$sql = 'SELECT fk_agefodd_session_stagiaire FROM '.MAIN_DB_PREFIX.'agefodd_convention_stagiaire WHERE fk_agefodd_session_stagiaire NOT IN (SELECT rowid FROM '.MAIN_DB_PREFIX.'agefodd_session_stagiaire)';

$resql = $db->query($sql);
if ($resql) {
	if ($db->num_rows($resql)) {
		print '<BR><BR>';
		while ( $obj = $db->fetch_object($resql) ) {
			print 'fk_agefodd_session_stagiaire id:'.$obj->fk_agefodd_session_stagiaire.' n existe pas dans la table agefodd_session_stagiaire<BR>';
		}
		print '<BR><BR><BR>Suggestion de correction : DELETE FROM '.MAIN_DB_PREFIX.'agefodd_convention_stagiaire WHERE fk_agefodd_session_stagiaire NOT IN (SELECT rowid FROM '.MAIN_DB_PREFIX.'agefodd_session_stagiaire)<BR><BR><BR>';
	}
}else {
	dol_print_error($db);
}

$sql = 'SELECT rowid,civilite FROM '.MAIN_DB_PREFIX.'agefodd_stagiaire WHERE (civilite NOT IN (SELECT code FROM '.MAIN_DB_PREFIX.'c_civility) OR civilite IS NULL)';

$resql = $db->query($sql);
if ($resql) {
	if ($db->num_rows($resql)) {
		print '<BR><BR>';
		while ( $obj = $db->fetch_object($resql) ) {
			print 'agefodd_stagiaire id:'.$obj->rowid.',civilite: '.$obj->civilite.' n existe pas dans la table c_civility<BR>';
		}
		print '<BR><BR><BR>Suggestion de correction : DELETE FROM '.MAIN_DB_PREFIX.'agefodd_stagiaire WHERE (civilite NOT IN (SELECT code FROM '.MAIN_DB_PREFIX.'c_civility) OR civilite IS NULL))<BR><BR><BR>';
	}
}else {
	dol_print_error($db);
}

//Stagiaire sans société lié
$sql = 'SELECT rowid, nom, prenom FROM '.MAIN_DB_PREFIX.'agefodd_stagiaire WHERE (fk_soc NOT IN (SELECT rowid FROM '.MAIN_DB_PREFIX.'societe) OR fk_soc IS NULL)';

$resql = $db->query($sql);
if ($resql) {
	if ($db->num_rows($resql)) {
		print '<BR><BR>';
		while ( $obj = $db->fetch_object($resql) ) {
			print 'participants '.$obj->nom.' / '.$obj->prenom.' dans '.MAIN_DB_PREFIX.'agefodd_stagiaire qui n a plus de société associer<BR>';
		}
		print '<BR><BR><BR>Suggestion de correction :  DELETE FROM '.MAIN_DB_PREFIX.'agefodd_session_stagiaire  WHERE (fk_stagiaire IN (SELECT rowid FROM '.MAIN_DB_PREFIX.'agefodd_stagiaire WHERE (fk_soc NOT IN (SELECT rowid FROM '.MAIN_DB_PREFIX.'societe) OR fk_soc IS NULL)));';
		print '<BR>DELETE FROM '.MAIN_DB_PREFIX.'agefodd_stagiaire WHERE (fk_soc NOT IN (SELECT rowid FROM '.MAIN_DB_PREFIX.'societe) OR fk_soc IS NULL)<BR><BR><BR>';
	}
}else {
	dol_print_error($db);
}

//Session sans formation catalogue....
$sql = 'SELECT rowid FROM llx_agefodd_session WHERE fk_formation_catalogue NOT IN (SELECT rowid FROM llx_agefodd_formation_catalogue)';

$resql = $db->query($sql);
if ($resql) {
	if ($db->num_rows($resql)) {
		print '<BR><BR>';
		while ( $obj = $db->fetch_object($resql) ) {
			print 'Session '.$obj->rowid.' dans '.MAIN_DB_PREFIX.'agefodd_session qui non une formation qui n existe plus<BR>';
		}
		print '<BR><BR><BR>Suggestion de correction : DELETE FROM '.MAIN_DB_PREFIX.'agefodd_stagiaire WHERE (fk_soc NOT IN (SELECT rowid FROM '.MAIN_DB_PREFIX.'societe) OR fk_soc IS NULL);<BR><BR><BR>';



	}
}else {
	dol_print_error($db);
}

//Agefoddsessioncontact


$sql = 'SELECT rowid FROM '.MAIN_DB_PREFIX.'agefodd_session_contact WHERE fk_agefodd_contact NOT IN (SELECT rowid FROM '.MAIN_DB_PREFIX.'agefodd_contact)';

$resql = $db->query($sql);
if ($resql) {
	if ($db->num_rows($resql)) {
		print '<BR><BR>';
		while ( $obj = $db->fetch_object($resql) ) {
			print 'Session contact'.$obj->rowid.' dans '.MAIN_DB_PREFIX.'agefodd_session_contact qui non un contact agefodd qui n existe plus<BR>';
		}
		print '<BR><BR><BR>Suggestion de correction : DELETE FROM '.MAIN_DB_PREFIX.'agefodd_session_contact WHERE fk_agefodd_contact NOT IN (SELECT rowid FROM '.MAIN_DB_PREFIX.'agefodd_contact)<BR><BR><BR>';
	}
}else {
	dol_print_error($db);
}

//Collation, PS: il existe aussi un script dans abricot pour ça.
$sql = 'SELECT CONCAT(\'ALTER TABLE \', TABLE_NAME,\' CONVERT TO CHARACTER SET utf8 COLLATE '.$dolibarr_main_db_collation.';\') AS    mySQL
        FROM INFORMATION_SCHEMA.TABLES
        WHERE TABLE_SCHEMA= "'.$dolibarr_main_db_name.'"
                AND TABLE_TYPE="BASE TABLE"
                AND TABLE_COLLATION != \''.$dolibarr_main_db_collation.'\'
                AND TABLE_NAME LIKE \''.MAIN_DB_PREFIX.'agefodd%\' ';
//echo $sql;
$resql = $db->query($sql);
if ($resql) {
    if ($db->num_rows($resql)) {

        print 'Certaines tables ne sont pas en collation utf8';
        print '<BR><BR><BR>Suggestion de correction<BR><BR>';

        print '<BR>SET foreign_key_checks = 0;';
        while ( $obj = $db->fetch_object($resql) ) {
            print $obj->mySQL.'<BR>';
        }
        print '<BR>SET foreign_key_checks = 1;<BR><BR><BR>';


    }
}else {
    dol_print_error($db);
}


_datec_check(MAIN_DB_PREFIX.'agefodd_session_formateur', 'datec');
_datec_check(MAIN_DB_PREFIX.'agefodd_session_formateur_calendrier', 'datec');
_datec_check(MAIN_DB_PREFIX.'agefodd_formateur_category', 'datec');
_datec_check(MAIN_DB_PREFIX.'agefodd_session_adminsitu', 'datea');

print 'Si pas de message, normalement tout est bon, sinon appliquer les recommendations en conscience ;-)';

llxFooter();
$db->close();


function _datec_check($table, $datefield){

    global $db;
    // datec agefodd_session_formateur calendrier
    $sql = 'SELECT COUNT(*) as nb FROM '.$table.' WHERE CAST('.$datefield.' AS CHAR(20)) = \'0000-00-00 00:00:00\';';
    //echo $sql;
    $resql = $db->query($sql);
    if ($resql) {
        if ($db->num_rows($resql)) {
            $obj = $db->fetch_object($resql) ;

            if($obj->nb>0){

                print 'Certaines lignes de la table '.$table.' utilisent une valeur de date incompatible ';
                print '<BR>Suggestion de correction';
                print '<BR>ALTER TABLE '.$table.' CHANGE '.$datefield.' '.$datefield.' DATETIME NULL DEFAULT NULL;';
                print '<BR>UPDATE '.$table.' SET '.$datefield.' = NULL   WHERE CAST('.$datefield.' AS CHAR(20)) = \'0000-00-00 00:00:00\'; <BR><BR><BR>';

            }

        }
    }else {
        dol_print_error($db);
    }

}
