<?php
/* Copyright (C) 2009-2010	Erick Bullier	<eb.dev@ebiconsulting.fr>
 * Copyright (C) 2010-2011	Regis Houssin	<regis@dolibarr.fr>
 * Copyright (C) 2012-2014	Florian Henry	<florian.henry@open-concept.pro>
 * Copyright (C) 2012		JF FERRY	<jfefe@aternatik.fr>
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
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 */

/**
 * \file agefodd/session/subscribers.php
 * \ingroup agefodd
 * \brief trainees of session
 */
$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");

require_once ('../class/agsession.class.php');
require_once (DOL_DOCUMENT_ROOT . '/contact/class/contact.class.php');
require_once ('../class/html.formagefodd.class.php');
require_once ('../lib/agefodd.lib.php');
require_once ('../class/agefodd_session_stagiaire.class.php');
require_once ('../class/agefodd_opca.class.php');
require_once ('../class/agefodd_session_stagiaire_heures.class.php');
require_once ('../class/agefodd_session_calendrier.class.php');

// Security check
if (! $user->rights->agefodd->lire)
	accessforbidden();

$action = GETPOST('action', 'alpha');
$id = GETPOST('id', 'int');
$confirm = GETPOST('confirm', 'alpha');
$stag_update_x = GETPOST('stag_update_x', 'alpha');
$stag_add_x = GETPOST('stag_add_x', 'alpha');
$stag_remove_x = GETPOST('stag_remove', 'alpha');
$modstagid = GETPOST('modstagid', 'int');
$newstag = GETPOST('newstag');
$edithours = ( bool ) GETPOST('edithours');

$fk_soc_requester = GETPOST('fk_soc_requester', 'int');
if ($fk_soc_requester < 0) {
	$fk_soc_requester = 0;
}
$fk_soc_link = GETPOST('fk_soc_link', 'int');
if ($fk_soc_link < 0) {
	$fk_soc_link = 0;
}
$fk_socpeople_sign = GETPOST('fk_socpeople_sign', 'int');
if ($fk_socpeople_sign < 0) {
	$fk_socpeople_sign = 0;
}

if ($action == 'edit' && ($user->rights->agefodd->creer | $user->rights->agefodd->modifier)) {

	if ($stag_update_x > 0) {
		$agf = new Agsession($db);

		$agfsta = new Agefodd_session_stagiaire($db);
		$agfsta->fetch(GETPOST('stagerowid', 'int'));

		$agfsta->fk_session_agefodd = GETPOST('sessid', 'int');
		$agfsta->fk_soc_link = $fk_soc_link;
		$agfsta->fk_soc_requester = $fk_soc_requester;
		$agfsta->fk_socpeople_sign = $fk_socpeople_sign;
		$agfsta->fk_stagiaire = GETPOST('stagiaire', 'int');
		$agfsta->fk_agefodd_stagiaire_type = GETPOST('stagiaire_type', 'int');

		if (! empty($conf->global->AGF_USE_REAL_HOURS) && $agfsta->status_in_session !== GETPOST('stagiaire_session_status', 'int') && GETPOST('stagiaire_session_status', 'int') == 4) {
			$part = true;
		}
		
		$agfsta->status_in_session = GETPOST('stagiaire_session_status', 'int');
		
		$agfsta->hour_foad = GETPOST('hour_foad', 'int');

		if ($agfsta->update($user) > 0) {

			if (! empty($conf->global->AGF_USE_REAL_HOURS) && $agfsta->status_in_session !== 4) {

				$agfssh = new Agefoddsessionstagiaireheures($db);
				$agfssh->fetch_all_by_session(GETPOST('sessid', 'int'), GETPOST('stagerowid', 'int'));
				foreach ( $agfssh->lines as $heures ) {
					$heures->delete($user);
				}
			}

			$redirect = true;
			$result = $agf->fetch(GETPOST('sessid', 'int'));

			if ($result > 0) {

				// TODO : si session inter => ajout des infos OPCA dans la table
				if ($agf->type_session == 1) {

					$opca = new Agefodd_opca($db);
					/*
					 *  Test si les infos existent déjà
					 * -> si OUI alors on update
					 * -> si NON on crée l'entrée dans la table
					 */
					$opca->id_opca_trainee = $opca->getOpcaForTraineeInSession(GETPOST('fk_soc_trainee', 'int'), GETPOST('sessid', 'int'), $agfsta->id);

					$opca->fk_session_trainee = $agfsta->id;
					$opca->fk_soc_trainee = GETPOST('fk_soc_trainee', 'int');
					$opca->fk_session_agefodd = GETPOST('sessid', 'int');
					$opca->is_date_ask_OPCA = GETPOST('isdateaskOPCA', 'int');
					$opca->date_ask_OPCA = dol_mktime(0, 0, 0, GETPOST('ask_OPCAmonth', 'int'), GETPOST('ask_OPCAday', 'int'), GETPOST('ask_OPCAyear', 'int'));
					$opca->is_OPCA = GETPOST('isOPCA', 'int');
					$opca->fk_soc_OPCA = GETPOST('fksocOPCA', 'int');
					$opca->fk_socpeople_OPCA = GETPOST('fksocpeopleOPCA', 'int');
					$opca->num_OPCA_soc = GETPOST('numOPCAsoc', 'alpha');
					$opca->num_OPCA_file = GETPOST('numOPCAFile', 'alpha');

					if ($opca->id_opca_trainee > 0) {
						$opca->id = $opca->id_opca_trainee;
						$result = $opca->update($user);
						if ($result > 0) {
							setEventMessage($langs->trans('Save'), 'mesgs');
						} else {
							setEventMessage($opca->error, 'errors');
							$redirect = false;
						}
					} else {
						$result = $opca->create($user);
						if ($result > 0) {
							setEventMessage($langs->trans('Save'), 'mesgs');
						} else {
							setEventMessage($opca->error, 'errors');
							$redirect = false;
						}
					}
				}
			} else {
				setEventMessage($agf->error, 'errors');
			}
			if ($part) {
				require_once ('../class/agefodd_stagiaire.class.php');
				$stag = new Agefodd_stagiaire($db);
				$stag->fetch($agfsta->fk_stagiaire);

				setEventMessage($langs->trans('AgfEditReelHours', $stag->nom . ' ' . $stag->prenom), 'warnings');
				Header("Location: " . $_SERVER['PHP_SELF'] . "?action=edit&id=" . $id . "&edithours=true");
				exit();
			}
			if ($redirect) {
				Header("Location: " . $_SERVER['PHP_SELF'] . "?action=edit&id=" . $id);
				exit();
			}
		} else {
			setEventMessage($agfsta->error, 'errors');
		}
	}

	if ($stag_add_x > 0) {

		$agf = new Agefodd_session_stagiaire($db);

		$agf->fk_session_agefodd = GETPOST('sessid', 'int');
		$agf->fk_soc_link = $fk_soc_link;
		$agf->fk_soc_requester = $fk_soc_requester;
		$agf->fk_stagiaire = GETPOST('stagiaire', 'int');
		$agf->fk_agefodd_stagiaire_type = GETPOST('stagiaire_type', 'int');
		$agf->status_in_session = GETPOST('stagiaire_session_status', 'int');
		$agf->fk_socpeople_sign = $fk_socpeople_sign;
		$agf->hour_foad = GETPOST('hour_foad', 'int');

		$result = $agf->create($user);

		if ($result > 0) {
			Header("Location: " . $_SERVER['PHP_SELF'] . "?action=edit&id=" . $id);
			exit();
		} else {
			setEventMessage($agf->error, 'errors');
		}
	}
}

/*
 * Action editrealhours
 */
if ($action == 'editrealhours') {

	$hours = GETPOST('realhours');
	$sessid = ( int ) GETPOST('id');
	$edit = GETPOST('edit');

	if (! empty($hours)) {
		$calendrier = new Agefodd_sesscalendar($db);
		$calendrier->fetch_all($sessid);
		$dureeCalendrier = 0;
		foreach ( $calendrier->lines as $horaire ) {
			$dureeCalendrier += (( float ) $horaire->heuref - ( float ) $horaire->heured) / 3600;
		}

		foreach ( $hours as $key => $value ) {

		    foreach ( $value as $creneaux => $heures ) {
		        $heures = preg_replace('/,/', '.', $heures);
		        $agf = new Agefoddsessionstagiaireheures($db);
		        $result = $agf->fetch_by_session($id, $key, $creneaux);
		        if ($result < 0) {
		            setEventMessage($agf->error, 'error');
		            break;
		        } elseif ($result) {
	                // édition d'heure existante
	                if ($agf->heures !== $heures) {
	                    $agf->heures = ( float ) $heures;
	                    $agf->update($user);
	                }
	            } else {
	                // création d'heure
	                $agf->fk_stagiaire = $key;
	                $agf->fk_calendrier = $creneaux;
	                $agf->fk_session = $id;
	                $agf->heures = ( float ) $heures;
	                $agf->create($user);
	            }
		    }
		    
		    $totalheures = array_sum($value);
		    if (( float ) $dureeCalendrier == ( float ) $totalheures) {
		        // stagiaire entièrement présent
		        $stagiaire = new Agefodd_session_stagiaire($db);
		        $stagiaire->fetch_by_trainee($sessid, $key);
		        $stagiaire->status_in_session = 3;
		        $stagiaire->update($user);
		    } elseif (!empty($totalheures)) {
		        // stagiaire partiellement présent
		        $stagiaire = new Agefodd_session_stagiaire($db);
		        $stagiaire->fetch_by_trainee($sessid, $key);
		        $stagiaire->status_in_session = 4;
		        $stagiaire->update($user);
		    } elseif (empty($totalheures)) {
		        $stagiaire = new Agefodd_session_stagiaire($db);
		        $stagiaire->fetch_by_trainee($sessid, $key);
		        $stagiaire->status_in_session = 5;
		        $stagiaire->update($user);
		    }
		    
		}
	}
	Header("Location: " . $_SERVER['PHP_SELF'] . "?action=edit&id=" . $id);
	exit();
}

/*
 * Actions delete stagiaire
 */

if ($action == 'confirm_delete_stag' && $confirm == "yes" && ($user->rights->agefodd->creer || $user->rights->agefodd->modifier)) {
	$stagerowid = GETPOST('stagerowid', 'int');

	$agf = new Agefodd_session_stagiaire($db);
	$agf->id = $stagerowid;
	$result = $agf->delete($user);

	if ($result > 0) {
		// s'il y a des heures réelles saisies pour ce stagiaire, on les supprime
		$heures = new Agefoddsessionstagiaireheures($db);
		$heures->fetch_all_by_session($agf->id, $stagerowid);
		foreach ( $heures->lines as $creneaux ) {
			$creneaux->delete($user);
		}
		Header("Location: " . $_SERVER['PHP_SELF'] . "?action=edit&id=" . $id);
		exit();
	} else {
		setEventMessage($agf->error, 'errors');
	}
}

/*
 * Action update info OPCA
 */
if ($action == 'update_subrogation' && ($user->rights->agefodd->creer || $user->rights->agefodd->modifier)) {
	if (! $_POST["cancel"]) {
		$error = 0;

		$agf = new Agsession($db);

		$res = $agf->fetch($id);
		if ($res > 0) {
			$isOPCA = GETPOST('isOPCA', 'int');
			if (! empty($isOPCA)) {
				$agf->is_OPCA = $isOPCA;
			} else {
				$agf->is_OPCA = 0;
			}

			$fksocpeopleOPCA = GETPOST('fksocpeopleOPCA', 'int');
			$agf->fk_socpeople_OPCA = $fksocpeopleOPCA;
			$fksocOPCA = GETPOST('fksocOPCA', 'int');
			if (! empty($fksocOPCA)) {
				$agf->fk_soc_OPCA = $fksocOPCA;
			}

			$agf->num_OPCA_soc = GETPOST('numOPCAsoc', 'alpha');
			$agf->num_OPCA_file = GETPOST('numOPCAFile', 'alpha');

			$agf->date_ask_OPCA = dol_mktime(0, 0, 0, GETPOST('ask_OPCAmonth', 'int'), GETPOST('ask_OPCAday', 'int'), GETPOST('ask_OPCAyear', 'int'));
			if ($agf->date_ask_OPCA == '') {
				$isdateaskOPCA = 0;
			} else {
				$isdateressite = GETPOST('isdateaskOPCA', 'int');
			}
			$agf->is_date_ask_OPCA = $isdateressite;

			if ($error == 0) {
				$result = $agf->update($user);
				if ($result > 0) {
					setEventMessage($langs->trans('Save'), 'mesgs');
					if ($_POST['saveandclose'] != '') {
						Header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $id);
					} else {
						Header("Location: " . $_SERVER['PHP_SELF'] . "?action=edit&id=" . $id);
					}
					exit();
				} else {
					setEventMessage($agf->error, 'errors');
				}
			} else {
				if ($_POST['saveandclose'] != '') {
					$action = '';
				} else {
					$action = 'edit_subrogation';
				}
			}
		} else {
			setEventMessage($agf->error, 'errors');
		}
	}
}
if ($action == 'updatetraineestatus') {
	$agf = new Agsession($db);
	$result = $agf->fetch($id);
	if ($result < 0) {
		setEventMessage($agf->error, 'errors');
	} else {
		$stagiaires = new Agefodd_session_stagiaire($db);
		$stagiaires->fk_session_agefodd = $agf->id;
		$result = $stagiaires->update_status_by_soc($user, 1, 0, GETPOST('statusinsession', 'int'));
		if ($result < 0) {
			setEventMessage($stagiaires->error, 'errors');
		} else {
			if (! empty($conf->global->AGF_USE_REAL_HOURS)) {
				$stagiaires->fetch_stagiaire_per_session($agf->id);
				foreach ( $stagiaires->lines as $trainee ) {
					$heures = new Agefoddsessionstagiaireheures($db);
					$heures->fetch_all_by_session($agf->id, $trainee->id);
					foreach ( $heures->lines as $creneaux ) {
						$creneaux->delete($user);
					}
				}
			}

			Header("Location: " . $_SERVER['PHP_SELF'] . "?action=edit&id=" . $id);
			exit();
		}
	}
}

/*
 * View
 */
$arrayofcss = array(
		'/agefodd/css/agefodd.css'
);
llxHeader($head, $langs->trans("AgfSessionDetail"), '', '', '', '', '', $arrayofcss, '');

$form = new Form($db);
$formAgefodd = new FormAgefodd($db);

if (! empty($id)) {
	$agf = new Agsession($db);
	$agf_opca = new Agefodd_opca($db);
	$result = $agf->fetch($id);

	$head = session_prepare_head($agf);

	dol_fiche_head($head, 'subscribers', $langs->trans("AgfSessionDetail"), 0, 'group');

	if ($action == 'edit') {

		// Put user on the right action block after reload
		if (! empty($modstagid) && $action == 'edit') {
			print '<script type="text/javascript">
					jQuery(document).ready(function () {
						jQuery(function() {
							$(\'html, body\').animate({scrollTop: $("#modstagid' . $modstagid . '").offset().top}, 500,\'easeInOutCubic\');
						});
					});
					</script> ';
		} elseif (! empty($newstag)) {
			print '<script type="text/javascript">
					jQuery(document).ready(function () {
						jQuery(function() {
							$(\'html, body\').animate({scrollTop: $("#newstag").offset().top}, 500,\'easeInOutCubic\');
						});
					});
					</script> ';
		} elseif ($edithours) {
			print '<script type="text/javascript">
					jQuery(document).ready(function () {
						jQuery(function() {
							$(\'html, body\').animate({scrollTop: $("#editrealhours").offset().top}, 500,\'easeInOutCubic\');
						});
					});
					</script> ';
		} else {
			print '<script type="text/javascript">
					jQuery(document).ready(function () {
						jQuery(function() {
							$(\'html, body\').animate({scrollTop: $("#modsta").offset().top}, 500,\'easeInOutCubic\');
						});
					});
					</script> ';
		}

		/*
		 * Confirm delete
		 */
		if ($stag_remove_x) {
			// Param url = id de la ligne stagiaire dans session - id session
			print $form->formconfirm($_SERVER['PHP_SELF'] . "?stagerowid=" . GETPOST('stagerowid', 'int') . '&id=' . $id, $langs->trans("AgfDeleteStag"), $langs->trans("AgfConfirmDeleteStag"), "confirm_delete_stag", '', '', 1);
		}

		print '<div width=100% align="center" style="margin: 0 0 3px 0;">';
		print $formAgefodd->level_graph(ebi_get_adm_lastFinishLevel($id), ebi_get_level_number($id), $langs->trans("AgfAdmLevel"));
		print '</div>';

		// Print session card
		$agf->printSessionInfo();

		print '&nbsp';
		if (is_array($agf->array_options) && key_exists('options_use_subro_inter', $agf->array_options) && ! empty($agf->array_options['options_use_subro_inter'])) {
			$agf->type_session = 1;
		}

		/*
		 * Manage funding for intra enterprise
		 */
		if (! $agf->type_session > 0 && ! empty($conf->global->AGF_MANAGE_OPCA)) {
			print '&nbsp';
			print '<table class="border" width="100%">';
			print '<tr><td>' . $langs->trans("AgfSubrocation") . '</td>';
			if ($agf->is_OPCA == 1) {
				$isOPCA = ' checked="checked" ';
			} else {
				$isOPCA = '';
			}
			print '<td><input type="checkbox" class="flat" disabled="disabled" readonly="readonly" ' . $isOPCA . '/></td></tr>';

			print '<tr><td width="20%">' . $langs->trans("AgfOPCAName") . '</td>';
			print '	<td>';
			if (DOL_VERSION < 6.0) {
				print '<a href="' . dol_buildpath('/societe/soc.php', 1) . '?socid=' . $agf->fk_soc_OPCA . '">' . $agf->soc_OPCA_name . '</a>';
			} else {
				print '<a href="' . dol_buildpath('/societe/card.php', 1) . '?socid=' . $agf->fk_soc_OPCA . '">' . $agf->soc_OPCA_name . '</a>';
			}
			print '</td></tr>';

			print '<tr><td width="20%">' . $langs->trans("AgfOPCAAdress") . '</td>';
			print '	<td>';
			print dol_print_address($agf->OPCA_adress, 'gmap', 'thirdparty', 0);
			print '</td></tr>';

			print '<tr><td width="20%">' . $langs->trans("AgfOPCAContact") . '</td>';
			print '	<td>';
			print '<a href="' . dol_buildpath('/contact/card.php', 1) . '?id=' . $agf->fk_socpeople_OPCA . '">' . $agf->contact_name_OPCA . '</a>';
			print '</td></tr>';

			print '<tr><td width="20%">' . $langs->trans("AgfOPCANumClient") . '</td>';
			print '<td>';
			print $agf->num_OPCA_soc;
			print '</td></tr>';

			print '<tr><td width="20%">' . $langs->trans("AgfOPCADateDemande") . '</td>';
			if ($agf->is_date_ask_OPCA == 1) {
				$chckisDtOPCA = 'checked="checked"';
			}
			print '<td><input type="checkbox" class="flat" disabled="disabled" readonly="readonly" name="isdateaskOPCA" value="1" ' . $chckisDtOPCA . ' />';
			print dol_print_date($agf->date_ask_OPCA, 'daytext');
			print '</td></tr>';

			print '<tr><td width="20%">' . $langs->trans("AgfOPCANumFile") . '</td>';
			print '<td>';
			print $agf->num_OPCA_file;
			print '</td></tr>';

			print '</table>';
		}

		/*
		 * Tableau d'édition des heures réelles
		 */
		if (! empty($conf->global->AGF_USE_REAL_HOURS)&& $edithours) {
			print '<br><form id="editrealhours" name="editrealhours" action="' . $_SERVER['PHP_SELF'] . '?action=editrealhours&id=' . $id . '"  method="POST">' . "\n";
			print '<input type="hidden" name="action" value="editrealhours">';

			$calendrier = new Agefodd_sesscalendar($db);
			$calendrier->fetch_all($agf->id);
			$blocNumber = count($calendrier->lines);
			$dureeCalendrier = 0;
			foreach ( $calendrier->lines as $horaire ) {
				$dureeCalendrier += ($horaire->heuref - $horaire->heured) / 3600;
			}

			print '<table class="noborder" width="100%">';
			print '<tr class="liste_titre">';
			print '<th>' . $langs->trans('AgfParticipants') . '</th><th colspan="' . $blocNumber . '" align="center">' . $langs->trans('AgfSchedules') . '</th><th align="center">' . $langs->trans('AgfTraineeHours') . '</th>';
			print '</tr>';
			print '<tr class="liste_titre"><th></th>';

			for($i = 0; $i < $blocNumber; $i ++) {
				print '<th align="center">' . dol_print_date($calendrier->lines[$i]->date_session, '%d/%m/%Y') . '<br>' . dol_print_date($calendrier->lines[$i]->heured, 'hour') . ' - ' . dol_print_date($calendrier->lines[$i]->heuref, 'hour') . '</th>';
			}
			print '<th></th></tr>';

			$stagiaires = new Agefodd_session_stagiaire($db);
			$stagiaires->fetch_stagiaire_per_session($agf->id);
			$nbstag = count($stagiaires->lines);

			for($i = 0; $i < $nbstag; $i ++) {
				print '<tr><td>' . strtoupper($stagiaires->lines[$i]->nom) . ' ' . ucfirst($stagiaires->lines[$i]->prenom);
				print '<br>' . $stagiaires->LibStatut($stagiaires->lines[$i]->status_in_session, 4);
				print '<br><a class="button fillin" href="#">Remplir</a>&nbsp;<a class="button fillout" href="#">Vider</a>';
				print '</td>';
				$agfssh = new Agefoddsessionstagiaireheures($db);
				if ($blocNumber > 0){
				    for($j = 0; $j < $blocNumber; $j ++) {
				        $defaultvalue = ($calendrier->lines[$j]->heuref - $calendrier->lines[$j]->heured) / 3600;
				        if (in_array($stagiaires->lines[$i]->status_in_session, array(3, 4))){
        					$result = $agfssh->fetch_by_session($id, $stagiaires->lines[$i]->id, $calendrier->lines[$j]->id);
        					if($calendrier->lines[$j]->date_session < dol_now()){
            					if ($result > 0) {
            						$val = $agfssh->heures;
            					} else {
            					    $val = $defaultvalue;
            					}
        					} else {
        					    $val = 0;
        					}
    					} else {
    					    $val = 0;
    					}
    
    					print '<td align="center"><input name="realhours[' . $stagiaires->lines[$i]->id . '][' . $calendrier->lines[$j]->id . ']" type="text" size="5" value="' . $val . '" data-default="'.(($calendrier->lines[$j]->date_session < dol_now()) ? $defaultvalue : 0).'" '.(($calendrier->lines[$j]->date_session < dol_now()) ? '' : 'disabled').'></td>';
    				}
				} else {
				    print '<td align="center">'. (($i == 0) ? $langs->trans("AgfNoCalendar") : '') .'</td>';
				}
				
				$total = $agfssh->heures_stagiaire($id, $stagiaires->lines[$i]->id);
				print '<td align="center">' . $total . '</td>';
				print '</tr>';
			}

			print '</table>';
			print '<div class="tabsAction"><input type="submit" class="butAction" value="' . $langs->trans('Save') . '">';
			print '<a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?action=edit&id=' . $id . '">' . $langs->trans('Cancel') . '</a></div>';
			print '</form>';
			print "<script type=\"text/javascript\">
					$(document).ready(function () {
						$('.fillin').click(function(e){
                            e.preventDefault();
                            console.log($(this).parent().parent().find('input'));
                            $(this).parent().parent().find('input').each(function(){
                                $(this).val($(this).attr('data-default'));
                            });
                        });

                        $('.fillout').click(function(e){
                            e.preventDefault();
                            console.log($(this).parent().parent().find('input'));
                            $(this).parent().parent().find('input').each(function(){
                                $(this).val(0);
                            });
                        });
					});
					</script> ";
			llxFooter();
			exit();
		}

		print '<div class="tabBar">';
		print '<form name="obj_update" action="' . $_SERVER['PHP_SELF'] . '?action=edit&id=' . $id . '"  method="POST">' . "\n";
		print '<table class="border" width="100%">';

		/*
		 *  Block update trainne info
		 *
		 */
		$stagiaires = new Agefodd_session_stagiaire($db);
		if (! empty($conf->global->AGF_DISPLAY_TRAINEE_GROUP_BY_STATUS)) {
			$resulttrainee = $stagiaires->fetch_stagiaire_per_session($agf->id, null, 0, 'ss.status_in_session,sa.nom');
		} else {
			$resulttrainee = $stagiaires->fetch_stagiaire_per_session($agf->id);
		}

		if ($resulttrainee < 0) {
			setEventMessage($stagiaires->error, 'errors');
		}
		$nbstag = count($stagiaires->lines);
		if ($nbstag > 0) {
			$fk_soc_used = array();
			for($i = 0; $i < $nbstag; $i ++) {

				if ($stagiaires->lines[$i]->id == $modstagid && ! empty($stag_remove_x))
					print '<tr bgcolor="#d5baa8">';
				else
					print '<tr>';

				print '<td width="3%" align="center"><a name="modsta" id="modsta"></a><a name="modstagid' . $stagiaires->lines[$i]->id . '" id="modstagid' . $stagiaires->lines[$i]->id . '"></a>' . ($i + 1) . '</td>';

				if ($stagiaires->lines[$i]->id == $modstagid && empty($stag_remove_x)) {
					print '<td colspan="2" >';
					print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">' . "\n";
					print '<input type="hidden" name="sessid" value="' . $stagiaires->lines[$i]->sessid . '">' . "\n";
					print '<input type="hidden" name="stagerowid" value="' . $stagiaires->lines[$i]->stagerowid . '">' . "\n";
					print '<input type="hidden" name="modstagid" value="' . $stagiaires->lines[$i]->id . '">' . "\n";
					print '<input type="hidden" name="fk_soc_trainee" value="' . $stagiaires->lines[$i]->socid . '">' . "\n";
					print '<label for="' . $htmlname . '" style="width:45%; display: inline-block;margin-left:5px;">' . $langs->trans('AgfSelectStagiaire') . '</label>';

					print $formAgefodd->select_stagiaire($stagiaires->lines[$i]->id, 'stagiaire', '(s.rowid NOT IN (SELECT fk_stagiaire FROM ' . MAIN_DB_PREFIX . 'agefodd_session_stagiaire WHERE fk_session_agefodd=' . $id . ')) OR (s.rowid=' . $stagiaires->lines[$i]->id . ')');

					if (empty($conf->global->AGF_SESSION_TRAINEE_STATUS_AUTO) || $agf->datef <= dol_now()) {
						print $formAgefodd->select_stagiaire_session_status('stagiaire_session_status', $stagiaires->lines[$i]->status_in_session);
					} else {
						print $stagiaires->LibStatut($stagiaires->lines[$i]->status_in_session, 4);
						print '<input type="hidden" name="stagiaire_session_status" value="' . $stagiaires->lines[$i]->status_in_session . '">';
					}

					print '<br>' . $langs->trans('AgfTraineeSocDocUse') . ' ';
					print $form->select_thirdparty_list($stagiaires->lines[$i]->fk_soc_link, 'fk_soc_link', '', 'SelectThirdParty', 1, 0);
					print '<br>' . $langs->trans('AgfTypeRequester') . ' ';
					print $form->select_thirdparty_list($stagiaires->lines[$i]->fk_soc_requester, 'fk_soc_requester', '', 'SelectThirdParty', 1, 0);
					if (! empty($conf->global->AGF_MANAGE_BPF)) {
						print '<br>' . $langs->trans('AgfHourFOAD') . ' ';
						print '<input size="4" type="text" class="flat" id="hour_foad" name="hour_foad" value="' . $stagiaires->lines[$i]->hour_foad . '" />';
					}

					if ($agf->type_session == 1) {
						print '<br>' . $langs->trans('AgfContactSign') . ' ';
						$form->select_contacts($stagiaires->lines[$i]->socid, $fk_socpeople_sign, 'fk_socpeople_sign', 1, '', '', 1, '', 1);
					}
					/*
					 * Manage trainee Funding for inter-enterprise
					 * Display only if first of the thridparty list
					 *
					 */
					if ($agf->type_session == 1 && ! $_POST['cancel'] && ! empty($conf->global->AGF_MANAGE_OPCA)) {
						$agf_opca->getOpcaForTraineeInSession($stagiaires->lines[$i]->socid, $agf->id, $stagiaires->lines[$i]->stagerowid);
						print '<table class="noborder noshadow" width="100%" id="form_subrogation">';
						print '<tr class="noborder"><td  class="noborder" width="45%">' . $langs->trans("AgfSubrocation") . '</td>';
						if ($agf_opca->is_OPCA == 1) {
							$chckisOPCA = 'checked="checked"';
						} else {
							$chckisOPCA = '';
						}
						print '<td><input type="checkbox" class="flat" name="isOPCA" value="1" ' . $chckisOPCA . '" /></td></tr>';

						print '<tr><td>' . $langs->trans("AgfOPCAName") . '</td>';
						print '	<td>';
						$events = array();
						$events[] = array(
								'method' => 'getContacts',
								'url' => dol_buildpath('/core/ajax/contacts.php', 1),
								'htmlname' => 'fksocpeopleOPCA',
								'params' => array(
										'add-customer-contact' => 'disabled'
								)
						);
						print $form->select_thirdparty_list($agf_opca->fk_soc_OPCA, 'fksocOPCA', '(s.client IN (1,2))', 'SelectThirdParty', 1, 0, $events);
						print '</td></tr>';

						print '<tr><td>' . $langs->trans("AgfOPCAContact") . '</td>';
						print '	<td>';
						if (! empty($agf_opca->fk_soc_OPCA)) {
							$form->select_contacts($agf_opca->fk_soc_OPCA, $agf_opca->fk_socpeople_OPCA, 'fksocpeopleOPCA', 1, '', '', 1, '', 1);
						} else {
							print '<select class="flat" id="fksocpeopleOPCA" name="fksocpeopleOPCA">';
							print '<option value="0">' . $langs->trans("AgfDefSocNeed") . '</option>';
							print '</select>';
						}
						print '</td></tr>';

						print '<tr><td width="20%">' . $langs->trans("AgfOPCANumClient") . '</td>';
						print '<td><input size="30" type="text" class="flat" name="numOPCAsoc" value="' . $agf_opca->num_OPCA_soc . '" /></td></tr>';

						print '<tr><td width="20%">' . $langs->trans("AgfOPCADateDemande") . '</td>';
						if ($agf_opca->is_date_ask_OPCA == 1) {
							$chckisDtOPCA = 'checked="checked"';
						} else {
							$chckisDtOPCA = '';
						}
						print '<td><table class="nobordernopadding"><tr><td>';
						print '<input type="checkbox" class="flat" name="isdateaskOPCA" value="1" ' . $chckisDtOPCA . ' /></td>';
						print '<td>';
						print $form->select_date($agf_opca->date_ask_OPCA, 'ask_OPCA', '', '', 1, 'update', 1, 1);
						print '</td><td>';
						print $form->textwithpicto('', $langs->trans("AgfDateCheckbox"));
						print '</td></tr></table>';
						print '</td></tr>';

						print '<tr><td width="20%">' . $langs->trans("AgfOPCANumFile") . '</td>';
						print '<td><input size="30" type="text" class="flat" name="numOPCAFile" value="' . $agf_opca->num_OPCA_file . '" /></td></tr>';

						print '</table>';
					}

					if (! empty($conf->global->AGF_USE_STAGIAIRE_TYPE)) {
						print '</td><td valign="top">' . $formAgefodd->select_type_stagiaire($stagiaires->lines[$i]->typeid, 'stagiaire_type', '', 1);
					}
					if ($user->rights->agefodd->modifier) {
						print '</td><td><input type="image" src="' . dol_buildpath('/agefodd/img/save.png', 1) . '" border="0" align="absmiddle" name="stag_update" alt="' . $langs->trans("AgfModSave") . '" ">';
					}
					print '</td>';
				} else {
					print '<td width="30%">';
					// info trainee
					if (strtolower($stagiaires->lines[$i]->nom) == "undefined") {
						print $langs->trans("AgfUndefinedStagiaire");
					} else {
						$trainee_info = '<a href="' . dol_buildpath('/agefodd/trainee/card.php', 1) . '?id=' . $stagiaires->lines[$i]->id . '">';
						$trainee_info .= img_object($langs->trans("ShowContact"), "contact") . ' ';
						$trainee_info .= strtoupper($stagiaires->lines[$i]->nom) . ' ' . ucfirst($stagiaires->lines[$i]->prenom) . '</a>';
						$contact_static = new Contact($db);
						$contact_static->civility_id = $stagiaires->lines[$i]->civilite;
						$trainee_info .= ' (' . $contact_static->getCivilityLabel() . ')';

						if ($agf->type_session == 1 && ! empty($conf->global->AGF_MANAGE_OPCA)) {
							print '<table class="nobordernopadding" width="100%"><tr class="noborder"><td colspan="2">';
							print $trainee_info . ' ' . $stagiaires->LibStatut($stagiaires->lines[$i]->status_in_session, 4);
							print '</td></tr>';

							$agf_opca->getOpcaForTraineeInSession($stagiaires->lines[$i]->socid, $agf->id, $stagiaires->lines[$i]->stagerowid);
							print '<tr class="noborder"><td  class="noborder" width="45%">' . $langs->trans("AgfSubrocation") . '</td>';
							if ($agf_opca->is_OPCA == 1) {
								$chckisOPCA = 'checked="checked"';
							} else {
								$chckisOPCA = '';
							}
							print '<td><input type="checkbox" class="flat" name="isOPCA" value="1" ' . $chckisOPCA . '" disabled="disabled" readonly="readonly"/></td></tr>';

							print '<tr><td>' . $langs->trans("AgfOPCAName") . '</td>';
							print '	<td>';
							if (DOL_VERSION < 6.0) {
								print '<a href="' . dol_buildpath('/societe/soc.php', 1) . '?socid=' . $agf_opca->fk_soc_OPCA . '">' . $agf_opca->soc_OPCA_name . '</a>';
							} else {
								print '<a href="' . dol_buildpath('/societe/card.php', 1) . '?socid=' . $agf_opca->fk_soc_OPCA . '">' . $agf_opca->soc_OPCA_name . '</a>';
							}
							print '</td></tr>';

							print '<tr><td>' . $langs->trans("AgfOPCAContact") . '</td>';
							print '	<td>';
							print '<a href="' . dol_buildpath('/contact/card.php', 1) . '?id=' . $agf_opca->fk_socpeople_OPCA . '">' . $agf_opca->contact_name_OPCA . '</a>';
							print '</td></tr>';

							print '<tr><td width="20%">' . $langs->trans("AgfOPCANumClient") . '</td>';
							print '<td>' . $agf_opca->num_OPCA_soc . '</td></tr>';

							print '<tr><td width="20%">' . $langs->trans("AgfOPCADateDemande") . '</td>';
							if ($agf_opca->is_date_ask_OPCA == 1) {
								$chckisDtOPCA = 'checked="checked"';
							} else {
								$chckisDtOPCA = '';
							}
							print '<td><table class="nobordernopadding"><tr><td>';
							print '<input type="checkbox" class="flat" name="isdateaskOPCA" disabled="disabled" readonly="readonly" value="1" ' . $chckisDtOPCA . ' /></td>';
							print '<td>';
							print dol_print_date($agf_opca->date_ask_OPCA, 'daytext');
							print '</td><td>';
							print '</td></tr></table>';
							print '</td></tr>';

							print '<tr><td width="20%">' . $langs->trans("AgfOPCANumFile") . '</td>';
							print '<td>' . $agf_opca->num_OPCA_file . '</td></tr>';

							print '</table>';
						} else {
							print $trainee_info . ' ' . $stagiaires->LibStatut($stagiaires->lines[$i]->status_in_session, 4);
							if (! empty($stagiaires->lines[$i]->hour_foad)) {
								print '<br>' . $langs->trans('AgfHourFOAD') . ' : ' . $stagiaires->lines[$i]->hour_foad . ' ' . $langs->trans('Hour') . '(s)';
							}
						}
					}
					print '</td>';
					print '<td width="20%" style="border-left: 0px;">';
					// Display thridparty link with trainee
					if ($stagiaires->lines[$i]->socid) {
						print '<a href="' . DOL_URL_ROOT . '/comm/card.php?socid=' . $stagiaires->lines[$i]->socid . '">';
						print img_object($langs->trans("ShowCompany"), "company");
						if (! empty($stagiaires->lines[$i]->soccode))
							print ' ' . $stagiaires->lines[$i]->soccode . '-';
						print ' ' . dol_trunc($stagiaires->lines[$i]->socname, 20) . '</a>';
					} else {
						print '&nbsp;';
					}
					if (! empty($conf->global->AGF_USE_STAGIAIRE_TYPE)) {
						print '</td><td width="20%" style="border-left: 0px;">' . stripslashes($stagiaires->lines[$i]->type);
					}
					print '</td>';

					// Infos thirdparty linked for doc
					print '<td style="border-left: 0px;">';
					if (! empty($stagiaires->lines[$i]->fk_soc_link)) {
						$socstatic = new Societe($db);
						$socstatic->fetch($stagiaires->lines[$i]->fk_soc_link);
						if (! empty($socstatic->id)) {
							print $langs->trans('AgfTraineeSocDocUse') . ':' . $socstatic->getNomUrl(1);
						}
					} else {
						print '&nbsp;';
					}
					if (! empty($stagiaires->lines[$i]->fk_soc_requester)) {
						$socstatic = new Societe($db);
						$socstatic->fetch($stagiaires->lines[$i]->fk_soc_requester);
						if (! empty($socstatic->id)) {
							print '<br>' . $langs->trans('AgfTypeRequester') . ':' . $socstatic->getNomUrl(1);
						}
					} else {
						print '&nbsp;';
					}
					if (! empty($stagiaires->lines[$i]->fk_socpeople_sign)) {
						$contactstatic = new Contact($db);
						$contactstatic->fetch($stagiaires->lines[$i]->fk_socpeople_sign);
						if (! empty($contactstatic->id)) {
							print '<br>' . $langs->trans('AgfContactSign') . ':' . $contactstatic->getNomUrl(1);
						}
					} else {
						print '&nbsp;';
					}
					print '</td>';
					if (! empty($conf->global->AGF_USE_REAL_HOURS)) {
						require_once ('../class/agefodd_session_stagiaire_heures.class.php');
						$agfssh = new Agefoddsessionstagiaireheures($db);
						print '<td>' . $langs->trans('AgfTraineeHours') . ' : ' . $agfssh->heures_stagiaire($id, $stagiaires->lines[$i]->id) . 'h</td>';
					}

					print '<td>';
					if ($user->rights->agefodd->modifier) {
						print '<a href="' . $_SERVER['PHP_SELF'] . '?action=edit&id=' . $id . '&modstagid=' . $stagiaires->lines[$i]->id . '">' . img_picto($langs->trans("Save"), 'edit') . '</a>';
					}
					print '&nbsp;';
					if ($user->rights->agefodd->creer || $user->rights->agefodd->modifier) {
						print '<a href="' . $_SERVER['PHP_SELF'] . '?action=edit&id=' . $id . '&modstagid=' . $stagiaires->lines[$i]->id . '&stag_remove=1&stagerowid=' . $stagiaires->lines[$i]->stagerowid . '">' . img_picto($langs->trans("Delete"), 'delete') . '</a>';
					}
					print '</td>' . "\n";
				}

				print '</tr>' . "\n";
			}
		}

		// New trainee
		if (! empty($newstag)) {
			print '<tr>';
			print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">' . "\n";
			print '<input type="hidden" name="sessid" value="' . $agf->id . '">' . "\n";
			print '<input type="hidden" name="stagerowid" value="' . $stagiaires->lines[$i]->stagerowid . '">' . "\n";
			print '<td width="20px" align="center"><a name="newstag" id="newstag"></a>' . ($i + 1) . '</td>';
			print '<td colspan="2" width="500px">';
			print '<label for="' . $htmlname . '" style="display: inline-block;margin-left:5px;">' . $langs->trans('AgfSelectStagiaire') . '</label>';
			print $formAgefodd->select_stagiaire('', 'stagiaire', 's.rowid NOT IN (SELECT fk_stagiaire FROM ' . MAIN_DB_PREFIX . 'agefodd_session_stagiaire WHERE fk_session_agefodd=' . $id . ')', 1);

			if (! empty($conf->global->AGF_USE_STAGIAIRE_TYPE)) {
				print $formAgefodd->select_type_stagiaire($conf->global->AGF_DEFAULT_STAGIAIRE_TYPE, 'stagiaire_type');
			}
			if (empty($conf->global->AGF_SESSION_TRAINEE_STATUS_AUTO) || $agf->datef <= dol_now()) {
				print $formAgefodd->select_stagiaire_session_status('stagiaire_session_status', 0);
			}

			print '<br>' . $langs->trans('AgfTraineeSocDocUse') . ' ';
			print $form->select_thirdparty_list(0, 'fk_soc_link', '', 'SelectThirdParty', 1, 0);
			print '<br>' . $langs->trans('AgfTypeRequester') . ' ';
			print $form->select_thirdparty_list(0, 'fk_soc_requester', '', 'SelectThirdParty', 1, 0);
			if (! empty($conf->global->AGF_MANAGE_BPF)) {
				print '<br>' . $langs->trans('AgfHourFOAD') . ' ';
				print '<input size="4" type="text" class="flat" id="hour_foad" name="hour_foad" value="' . GETPOST('hour_load') . '" />';
			}

			if ($user->rights->agefodd->modifier) {
				print '</td><td><input type="image" src="' . dol_buildpath('/agefodd/img/save.png', 1) . '" border="0" align="absmiddle" name="stag_add" alt="' . $langs->trans("AgfModSave") . '" ">';
			}
			print '</td>';
			print '</form>';
			print '</tr>' . "\n";
			// If session are intra entreprise then send Socid on create trainee
			if ($agf->type_session == 0 && ! empty($agf->fk_soc)) {
				$param_socid = '&societe=' . $agf->fk_soc;
			} else {
				$param_socid = '';
			}
		}

		print '</table>';
		print '</form>' . "\n";
		if (empty($newstag)) {
			print '</div>';
			print '<br>';
			print '<form name="newstag" action="' . $_SERVER['PHP_SELF'] . '?action=edit&id=' . $id . '"  method="POST">' . "\n";
			print '<table style="border:0;" width="100%">';
			print '<tr><td align="right">';
			print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">' . "\n";
			print '<input type="hidden" name="action" value="edit">' . "\n";
			print '<input type="hidden" name="newstag" value="1">' . "\n";
			print '<input type="submit" class="butAction" value="' . $langs->trans("AgfStagiaireAdd") . '">';
			// If session are intra entreprise then send Socid on create trainee
			if ($agf->type_session == 0 && ! empty($agf->fk_soc)) {
				$param_socid = '&societe=' . $agf->fk_soc;
			} else {
				$param_socid = '';
			}
			print
					'<a class="butAction" href="../trainee/card.php?action=create' . $param_socid . '&session_id=' . $id . '&url_back=' . urlencode($_SERVER['PHP_SELF'] . '?action=edit&id=' . $id) . '" title="' . $langs->trans('AgfNewParticipantLinkInfo') . '">' . $langs->trans('AgfNewParticipant') . '</a>';

			if ($conf->global->AGF_MANAGE_OPCA) {
				if ($user->rights->agefodd->creer && ! $agf->type_session > 0) {
					print '<a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?action=edit_subrogation&id=' . $id . '">' . $langs->trans('AgfModifySubrogation') . '</a>';
				} else {
					if ($agf->type_session)
						$title = ' / ' . $langs->trans('AgfAvailableForIntraOnly');
					print '<a class="butActionRefused" href="#" title="' . dol_escape_htmltag($langs->trans("NotAllowed")) . $title . '">' . $langs->trans('AgfModifySubrogation') . '</a>';
				}
			}

			if ((empty($conf->global->AGF_SESSION_TRAINEE_STATUS_AUTO) || $agf->datef <= dol_now()) && $nbstag > 0) {
				print '<br><br>';
				foreach ( $stagiaires->labelstatut_short as $statuskey => $statuslabelshort ) {
					if ($statuskey == 0 || $statuskey == 2 || $statuskey == 3 || $statuskey == 5 || $statuskey == 6) {
						print
								'<a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?action=updatetraineestatus&id=' . $id . '&statusinsession=' . $statuskey . '" title="' . $langs->trans('AgfSetTrainneStatusTo') . ' ' . $statuslabelshort . '">' . $langs->trans('AgfSetTrainneStatusTo') . ' ' . $statuslabelshort . '</a>';
					}
				}
			}
			if (! empty($conf->global->AGF_USE_REAL_HOURS) && ! empty($agf->nb_stagiaire))
				print '<br><br><a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?action=edit&id=' . $id . '&edithours=true">' . $langs->trans('AgfModifyTraineeHours') . '</a>';
			print '</td></tr>';
		} else {
			print '<br>';
			print '<table style="border:0;" width="100%">';
			print '<tr><td align="right">';
			print
					'<a class="butAction" href="../trainee/card.php?action=create' . $param_socid . '&session_id=' . $id . '&url_back=' . urlencode($_SERVER['PHP_SELF'] . '?action=edit&id=' . $id) . '" title="' . $langs->trans('AgfNewParticipantLinkInfo') . '">' . $langs->trans('AgfNewParticipant') . '</a>';
			print '</td></tr>';
			print '</table>';
		}
		print '</table>';
		print '</form>';
		print '</div>';
	} else {
		// Display View mode

		print '<div width=100% align="center" style="margin: 0 0 3px 0;">';
		print $formAgefodd->level_graph(ebi_get_adm_lastFinishLevel($id), ebi_get_level_number($id), $langs->trans("AgfAdmLevel"));
		print '</div>';

		// Print session card
		$agf->printSessionInfo();

		print '&nbsp';

		if (is_array($agf->array_options) && key_exists('options_use_subro_inter', $agf->array_options) && ! empty($agf->array_options['options_use_subro_inter'])) {
			$agf->type_session = 1;
		}

		/*
		 * Manage funding for intra-enterprise session
		 */
		if (! $agf->type_session > 0) {
			if ($action == "edit_subrogation" && $agf->type_session == 0 && ! empty($conf->global->AGF_MANAGE_OPCA)) {
				print '</div>';

				print_barre_liste($langs->trans("AgfGestSubrocation"), "", "", "", "", "", '', 0);
				print '<div class="tabBar">';

				print '<form name="add" action="' . $_SERVER['PHP_SELF'] . '" method="POST">' . "\n";
				print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
				print '<input type="hidden" name="action" value="update_subrogation">';
				print '<input type="hidden" name="id" value="' . $agf->id . '">';
				print '<table class="border" width="100%">';
				print '<tr><td width="20%">' . $langs->trans("AgfSubrocation") . '</td>';
				if ($agf->is_OPCA == 1) {
					$chckisOPCA = 'checked="checked"';
				}
				print '<td><input type="checkbox" class="flat" name="isOPCA" value="1" ' . $chckisOPCA . '" /></td></tr>';

				print '<tr><td width="20%">' . $langs->trans("AgfOPCAName") . '</td>';
				print '	<td>';
				$events = array();
				$events[] = array(
						'method' => 'getContacts',
						'url' => dol_buildpath('/core/ajax/contacts.php', 1),
						'htmlname' => 'fksocpeopleOPCA',
						'params' => array(
								'add-customer-contact' => 'disabled'
						)
				);
				print $form->select_thirdparty_list($agf->fk_soc_OPCA, 'fksocOPCA', '(s.client IN (1,2,3))', 'SelectThirdParty', 1, 0, $events);

				// Print biller choice;
				$socbiller = new Societe($db);
				$socbiller->fetch($agf->fk_soc);
				print '</td></tr>';

				print '<tr><td width="20%">' . $langs->trans("AgfOPCAContact") . '</td>';
				print '	<td>';
				if (! empty($agf->fk_soc_OPCA)) {
					$form->select_contacts($agf->fk_soc_OPCA, $agf->fk_socpeople_OPCA, 'fksocpeopleOPCA', 1, '', '', 1, '', 1);
				} else {
					print '<select class="flat" id="fksocpeopleOPCA" name="fksocpeopleOPCA">';
					print '<option value="0">' . $langs->trans("AgfDefSocNeed") . '</option>';
					print '</select>';
				}
				print '</td></tr>';

				print '<tr><td width="20%">' . $langs->trans("AgfOPCANumClient") . '</td>';
				print '<td><input size="30" type="text" class="flat" name="numOPCAsoc" value="' . $agf->num_OPCA_soc . '" /></td></tr>';

				print '<tr><td width="20%">' . $langs->trans("AgfOPCADateDemande") . '</td>';
				if ($agf->is_date_ask_OPCA == 1) {
					$chckisDtOPCA = 'checked="checked"';
				}
				print '<td><table class="nobordernopadding"><tr><td>';
				print '<input type="checkbox" class="flat" name="isdateaskOPCA" value="1" ' . $chckisDtOPCA . ' /></td>';
				print '<td>';
				print $form->select_date($agf->date_ask_OPCA, 'ask_OPCA', '', '', 1, 'update', 1, 1);
				print '</td><td>';
				print $form->textwithpicto('', $langs->trans("AgfDateCheckbox"));
				print '</td></tr></table>';
				print '</td></tr>';

				print '<tr><td width="20%">' . $langs->trans("AgfOPCANumFile") . '</td>';
				print '<td><input size="30" type="text" class="flat" name="numOPCAFile" value="' . $agf->num_OPCA_file . '" /></td></tr>';

				print '<tr><td align="center" colspan=2>';
				print '<input type="submit" class="butAction" value="' . $langs->trans("Save") . '"> &nbsp; ';
				print '<input type="submit" name="cancel" class="butActionDelete" value="' . $langs->trans("Cancel") . '">';
				print '</td></tr>';

				print '</table></div>';
			} elseif (! empty($conf->global->AGF_MANAGE_OPCA)) {
				/*
				 * Display funding information
				 */

				print '&nbsp';
				print '<table class="border" width="100%">';
				print '<tr><td>' . $langs->trans("AgfSubrocation") . '</td>';
				if ($agf->is_OPCA == 1) {
					$isOPCA = ' checked="checked" ';
				} else {
					$isOPCA = '';
				}
				print '<td><input type="checkbox" class="flat" disabled="disabled" readonly="readonly" ' . $isOPCA . '/></td></tr>';

				print '<tr><td width="20%">' . $langs->trans("AgfOPCAName") . '</td>';
				print '	<td>';
				if (DOL_VERSION < 6.0) {
					print '<a href="' . dol_buildpath('/societe/soc.php', 1) . '?socid=' . $agf->fk_soc_OPCA . '">' . $agf->soc_OPCA_name . '</a>';
				} else {
					print '<a href="' . dol_buildpath('/societe/card.php', 1) . '?socid=' . $agf->fk_soc_OPCA . '">' . $agf->soc_OPCA_name . '</a>';
				}
				print '</td></tr>';

				print '<tr><td width="20%">' . $langs->trans("AgfOPCAAdress") . '</td>';
				print '	<td>';
				print dol_print_address($agf->OPCA_adress, 'gmap', 'thirdparty', 0);
				print '</td></tr>';

				print '<tr><td width="20%">' . $langs->trans("AgfOPCAContact") . '</td>';
				print '	<td>';
				print '<a href="' . dol_buildpath('/contact/card.php', 1) . '?id=' . $agf->fk_socpeople_OPCA . '">' . $agf->contact_name_OPCA . '</a>';
				print '</td></tr>';

				print '<tr><td width="20%">' . $langs->trans("AgfOPCANumClient") . '</td>';
				print '<td>';
				print $agf->num_OPCA_soc;
				print '</td></tr>';

				print '<tr><td width="20%">' . $langs->trans("AgfOPCADateDemande") . '</td>';
				if ($agf->is_date_ask_OPCA == 1) {
					$chckisDtOPCA = 'checked="checked"';
				} else {
					$chckisDtOPCA = '';
				}
				print '<td><input type="checkbox" class="flat" disabled="disabled" readonly="readonly" name="isdateaskOPCA" value="1" ' . $chckisDtOPCA . ' />';
				print dol_print_date($agf->date_ask_OPCA, 'daytext');
				print '</td></tr>';

				print '<tr><td width="20%">' . $langs->trans("AgfOPCANumFile") . '</td>';
				print '<td>';
				print $agf->num_OPCA_file;
				print '</td></tr>';

				print '</table>';
			}
		}

		/*
		 * Manage trainee
		 */

		print '&nbsp';
		print '<table class="border" width="100%">';

		$stagiaires = new Agefodd_session_stagiaire($db);
		if (! empty($conf->global->AGF_DISPLAY_TRAINEE_GROUP_BY_STATUS)) {
			$resulttrainee = $stagiaires->fetch_stagiaire_per_session($agf->id, null, 0, 'ss.status_in_session,sa.nom');
		} else {
			$resulttrainee = $stagiaires->fetch_stagiaire_per_session($agf->id);
		}

		if ($resulttrainee < 0) {
			setEventMessage($stagiaires->error, 'errors');
		}
		$nbstag = count($stagiaires->lines);
		print '<tr><td  width="20%" valign="top" ';
		if ($nbstag < 1) {
			print '>' . $langs->trans("AgfParticipants") . '</td>';
			print '<td style="text-decoration: blink;">' . $langs->trans("AgfNobody") . '</td></tr>';
		} else {
			print ' rowspan=' . ($nbstag) . '>' . $langs->trans("AgfParticipants");
			if ($nbstag > 1)
				print ' (' . $nbstag . ')';
			print '</td>';

			for($i = 0; $i < $nbstag; $i ++) {
				print '<td witdth="20px" align="center">' . ($i + 1) . '</td>';
				print '<td width="400px"style="border-right: 0px;">';
				// Infos stagiaires
				if (strtolower($stagiaires->lines[$i]->nom) == "undefined") {
					print $langs->trans("AgfUndefinedStagiaire");
				} else {
					$trainee_info = '<a href="' . dol_buildpath('/agefodd/trainee/card.php', 1) . '?id=' . $stagiaires->lines[$i]->id . '">';
					$trainee_info .= img_object($langs->trans("ShowContact"), "contact") . ' ';
					$trainee_info .= strtoupper($stagiaires->lines[$i]->nom) . ' ' . ucfirst($stagiaires->lines[$i]->prenom) . '</a>';
					$contact_static = new Contact($db);
					$contact_static->civility_id = $stagiaires->lines[$i]->civilite;
					$trainee_info .= ' (' . $contact_static->getCivilityLabel() . ')';

					if ($agf->type_session == 1 && ! empty($conf->global->AGF_MANAGE_OPCA)) {
						print '<table class="nobordernopadding" width="100%"><tr class="noborder"><td colspan="2">';
						print $trainee_info . ' ' . $stagiaires->LibStatut($stagiaires->lines[$i]->status_in_session, 4);
						print '</td></tr>';

						$agf_opca->getOpcaForTraineeInSession($stagiaires->lines[$i]->socid, $agf->id, $stagiaires->lines[$i]->stagerowid);
						print '<tr class="noborder"><td  class="noborder" width="45%">' . $langs->trans("AgfSubrocation") . '</td>';
						if ($agf_opca->is_OPCA == 1) {
							$chckisOPCA = 'checked="checked"';
						} else {
							$chckisOPCA = '';
						}
						print '<td><input type="checkbox" class="flat" name="isOPCA" value="1" ' . $chckisOPCA . '" disabled="disabled" readonly="readonly"/></td></tr>';

						print '<tr><td>' . $langs->trans("AgfOPCAName") . '</td>';
						print '	<td>';
						if (DOL_VERSION < 6.0) {
							print '<a href="' . dol_buildpath('/societe/soc.php', 1) . '?socid=' . $agf_opca->fk_soc_OPCA . '">' . $agf_opca->soc_OPCA_name . '</a>';
						} else {
							print '<a href="' . dol_buildpath('/societe/card.php', 1) . '?socid=' . $agf_opca->fk_soc_OPCA . '">' . $agf_opca->soc_OPCA_name . '</a>';
						}
						print '</td></tr>';

						print '<tr><td>' . $langs->trans("AgfOPCAContact") . '</td>';
						print '	<td>';
						print '<a href="' . dol_buildpath('/contact/card.php', 1) . '?id=' . $agf_opca->fk_socpeople_OPCA . '">' . $agf_opca->contact_name_OPCA . '</a>';
						print '</td></tr>';

						print '<tr><td width="20%">' . $langs->trans("AgfOPCANumClient") . '</td>';
						print '<td>' . $agf_opca->num_OPCA_soc . '</td></tr>';

						print '<tr><td width="20%">' . $langs->trans("AgfOPCADateDemande") . '</td>';
						if ($agf_opca->is_date_ask_OPCA == 1) {
							$chckisDtOPCA = 'checked="checked"';
						} else {
							$chckisDtOPCA = '';
						}
						print '<td><table class="nobordernopadding"><tr><td>';
						print '<input type="checkbox" class="flat" name="isdateaskOPCA" disabled="disabled" readonly="readonly" value="1" ' . $chckisDtOPCA . ' /></td>';
						print '<td>';
						print dol_print_date($agf_opca->date_ask_OPCA, 'daytext');
						print '</td><td>';
						print '</td></tr></table>';
						print '</td></tr>';

						print '<tr><td width="20%">' . $langs->trans("AgfOPCANumFile") . '</td>';
						print '<td>' . $agf_opca->num_OPCA_file . '</td></tr>';

						print '</table>';
					} else {
						print $trainee_info . ' ' . $stagiaires->LibStatut($stagiaires->lines[$i]->status_in_session, 4);
						if (! empty($stagiaires->lines[$i]->hour_foad) && ! empty($conf->global->AGF_MANAGE_BPF)) {
							print '<br>' . $langs->trans('AgfHourFOAD') . ' : ' . $stagiaires->lines[$i]->hour_foad . ' ' . $langs->trans('Hour') . '(s)';
						}
					}
				}
				print '</td>';
				print '<td style="border-left: 0px; border-right: 0px;">';
				// Infos organisme de rattachement
				if ($stagiaires->lines[$i]->socid) {
					print '<a href="' . DOL_URL_ROOT . '/comm/card.php?socid=' . $stagiaires->lines[$i]->socid . '">';
					print img_object($langs->trans("ShowCompany"), "company");
					if (! empty($stagiaires->lines[$i]->soccode))
						print ' ' . $stagiaires->lines[$i]->soccode . '-';
					print ' ' . dol_trunc($stagiaires->lines[$i]->socname, 20) . '</a>';
				} else {
					print '&nbsp;';
				}
				print '</td>';
				print '<td style="border-left: 0px;">';
				// Infos mode de financement
				if (($stagiaires->lines[$i]->type) && (! empty($conf->global->AGF_USE_STAGIAIRE_TYPE))) {
					print '<div class=adminaction>';
					print '<span>' . stripslashes($stagiaires->lines[$i]->type) . '</span></div>';
				} else {
					print '&nbsp;';
				}
				print '</td>';

				// Infos thirdparty linked for doc
				print '<td style="border-left: 0px;">';
				if (! empty($stagiaires->lines[$i]->fk_soc_link)) {
					$socstatic = new Societe($db);
					$socstatic->fetch($stagiaires->lines[$i]->fk_soc_link);
					if (! empty($socstatic->id)) {
						print $langs->trans('AgfTraineeSocDocUse') . ':' . $socstatic->getNomUrl(1);
					}
				} else {
					print '&nbsp;';
				}
				if (! empty($stagiaires->lines[$i]->fk_soc_requester)) {
					$socstatic = new Societe($db);
					$socstatic->fetch($stagiaires->lines[$i]->fk_soc_requester);
					if (! empty($socstatic->id)) {
						print '<br>' . $langs->trans('AgfTypeRequester') . ':' . $socstatic->getNomUrl(1);
					}
				} else {
					print '&nbsp;';
				}
				if (! empty($stagiaires->lines[$i]->fk_socpeople_sign)) {
					$contactstatic = new Contact($db);
					$contactstatic->fetch($stagiaires->lines[$i]->fk_socpeople_sign);
					if (! empty($socstatic->id)) {
						print '<br>' . $langs->trans('AgfContactSign') . ':' . $contactstatic->getNomUrl(1);
					}
				} else {
					print '&nbsp;';
				}
				print '</td>';
				print "</tr>\n";
			}
		}
		print "</table>";
		print '</div>';
	}
}

/*
 * Action tabs
 *
 */

print '<div class="tabsAction">';

if ($action != 'create' && $action != 'edit' && $action != "edit_subrogation" && (! empty($agf->id))) {
	if (($user->rights->agefodd->creer || $user->rights->agefodd->modifier) && $agf->status != 4) {
		print '<a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?action=edit&id=' . $id . '">' . $langs->trans('AgfModifyTrainee') . '</a>';
	} else {
		print '<a class="butActionRefused" href="#" title="' . dol_escape_htmltag($langs->trans("NotAllowed")) . '">' . $langs->trans('AgfModifyTrainee') . '</a>';
	}

	if (($user->rights->agefodd->creer || $user->rights->agefodd->modifier) && ! $agf->type_session > 0 && ! empty($conf->global->AGF_MANAGE_OPCA)) {
		print '<a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?action=edit_subrogation&id=' . $id . '">' . $langs->trans('AgfModifySubrogation') . '</a>';
	} else {
		if ($agf->type_session)
			$title = ' / ' . $langs->trans('AgfAvailableForIntraOnly');
		print '<a class="butActionRefused" href="#" title="' . dol_escape_htmltag($langs->trans("NotAllowed")) . $title . '">' . $langs->trans('AgfModifySubrogation') . '</a>';
	}
}

print '</div>';

llxFooter();
$db->close();