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
 * \file agefodd/session/card.php
 * \ingroup agefodd
 * \brief card of session
 */
$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");

require_once ('../class/agsession.class.php');
require_once ('../class/agefodd_sessadm.class.php');
require_once ('../class/agefodd_session_admlevel.class.php');
require_once ('../class/html.formagefodd.class.php');
require_once ('../class/agefodd_session_calendrier.class.php');
require_once ('../class/agefodd_calendrier.class.php');
require_once ('../class/agefodd_session_formateur.class.php');
require_once ('../class/agefodd_session_stagiaire.class.php');
require_once ('../class/agefodd_session_element.class.php');
require_once (DOL_DOCUMENT_ROOT . '/contact/class/contact.class.php');
require_once ('../lib/agefodd.lib.php');
require_once (DOL_DOCUMENT_ROOT . '/core/lib/date.lib.php');
require_once (DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php');
require_once ('../class/agefodd_formation_catalogue.class.php');
require_once ('../class/agefodd_opca.class.php');

// Security check
if (! $user->rights->agefodd->lire) {
	accessforbidden();
}

$action = GETPOST('action', 'alpha');
$confirm = GETPOST('confirm', 'alpha');
$id = GETPOST('id', 'int');
$arch = GETPOST('arch', 'int');
$anchor = GETPOST('anchor');

$agf = new Agsession($db);
$extrafields = new ExtraFields($db);
$extralabels = $extrafields->fetch_name_optionals_label($agf->table_element);

/*
 * Actions delete session
 */

$period_update = GETPOST('period_update', 'int');
$cancel = GETPOST('cancel');
$saveandclose = GETPOST('saveandclose');
$modperiod = GETPOST('modperiod', 'int');
$period_remove = GETPOST('period_remove', 'int');
$period_remove_all = GETPOST('period_remove_all', 'int');

$delete_calsel = GETPOST('deletecalsel_x', 'alpha');
if (! empty($delete_calsel)) {
	$action = 'delete_calsel';
}

/*
 * Actions delete period
 */

if ($action == 'confirm_delete_period' && $confirm == "yes" && $user->rights->agefodd->creer) {

	$agf = new Agefodd_sesscalendar($db);
	$result = $agf->remove($modperiod);

	if ($result > 0) {
	    if (! empty($conf->global->AGF_USE_REAL_HOURS)){
	        // nettoyage des heures réelles
	        $sql = "DELETE FROM " . MAIN_DB_PREFIX . "agefodd_session_stagiaire_heures";
	        $sql.= " WHERE fk_calendrier = " . $modperiod;

	        $db->query($sql);
	    }

		Header("Location: " . $_SERVER['PHP_SELF'] . "?action=edit&id=" . $id . '&anchor=period');
		exit();
	} else {
		setEventMessage($agf->error, 'errors');
	}
}

if ($action == 'confirm_delete_period_all' && $confirm == "yes" && $user->rights->agefodd->creer) {

	$agf = new Agefodd_sesscalendar($db);
	$result = $agf->fetch_all($id);
	if ($result < 0) {
		setEventMessage($agf->error, 'errors');
	} else {
		foreach ( $agf->lines as $line ) {
			$agf_line = new Agefodd_sesscalendar($db);
			$result = $agf_line->remove($line->id);
			if ($result < 0) {
				setEventMessage($agf_line->error, 'errors');
			}
		}
	}

	if ($result > 0) {
	    if (! empty($conf->global->AGF_USE_REAL_HOURS)){
	        // nettoyage des heures réelles
	        $sql = "DELETE FROM " . MAIN_DB_PREFIX . "agefodd_session_stagiaire_heures";
	        $sql.= " WHERE fk_session = " . $id;

	        $db->query($sql);
	    }
		Header("Location: " . $_SERVER['PHP_SELF'] . "?action=edit&id=" . $id . '&anchor=period');
		exit();
	} else {
		setEventMessage($agf->error, 'errors');
	}
}

/*
 * Action update
 * - Calendar update
 * - trainer update
 */
if ($action == 'edit' && ($user->rights->agefodd->creer || $user->rights->agefodd->modifier)) {

	if (! empty($period_update)) {

		$date_session = dol_mktime(0, 0, 0, GETPOST('datemonth', 'int'), GETPOST('dateday', 'int'), GETPOST('dateyear', 'int'));

		$heure_tmp_arr = array();

		$heured_tmp = GETPOST('dated', 'alpha');
		if (! empty($heured_tmp)) {
			$heure_tmp_arr = explode(':', $heured_tmp);
			$heured = dol_mktime($heure_tmp_arr[0], $heure_tmp_arr[1], 0, GETPOST('datemonth', 'int'), GETPOST('dateday', 'int'), GETPOST('dateyear', 'int'));
		}

		$heuref_tmp = GETPOST('datef', 'alpha');
		if (! empty($heuref_tmp)) {
			$heure_tmp_arr = explode(':', $heuref_tmp);
			$heuref = dol_mktime($heure_tmp_arr[0], $heure_tmp_arr[1], 0, GETPOST('datemonth', 'int'), GETPOST('dateday', 'int'), GETPOST('dateyear', 'int'));
		}
		
		$agf = new Agefodd_sesscalendar($db);
		$result = $agf->fetch($modperiod);

		if (! empty($modperiod))
			$agf->id = $modperiod;
		if (! empty($date_session))
			$agf->date_session = $date_session;
		if (! empty($heured))
			$agf->heured = $heured;
		if (! empty($heuref))
			$agf->heuref = $heuref;

		$agf->calendrier_type = GETPOST('code_c_session_calendrier_type');
		$result = $agf->update($user);

		if ($result > 0) {
			Header("Location: " . $_SERVER['PHP_SELF'] . "?action=edit&id=" . $id . "&anchor=period");
			exit();
		} else {
			setEventMessage($agf->error, 'errors');
		}
	}

	$period_add = GETPOST('period_add');
	if (! empty($period_add)) {
		$error = 0;
		$error_message = '';

		// From template
		$idtemplate_array = GETPOST('fromtemplate');
		$TCodeSessionCalendrierType = GETPOST('code_c_session_calendrier_type');
		if (is_array($idtemplate_array)) {
			foreach ( $idtemplate_array as $index => $idtemplate ) {

				$agf = new Agefodd_sesscalendar($db);

				$agf->sessid = GETPOST('sessid', 'int');
				$agf->date_session = dol_mktime(0, 0, 0, GETPOST('datemonth', 'int'), GETPOST('dateday', 'int'), GETPOST('dateyear', 'int'));

				$agf->calendrier_type = $TCodeSessionCalendrierType[$index];
				
				$tmpl_calendar = new Agefoddcalendrier($db);
				$result = $tmpl_calendar->fetch($idtemplate);
				$tmpldate = dol_mktime(0, 0, 0, GETPOST('datetmplmonth', 'int'), GETPOST('datetmplday', 'int'), GETPOST('datetmplyear', 'int'));
				if ($tmpl_calendar->day_session != 1) {
					$tmpldate = dol_time_plus_duree($tmpldate, (($tmpl_calendar->day_session) - 1), 'd');
				}

				$agf->date_session = $tmpldate;

				$heure_tmp_arr = explode(':', $tmpl_calendar->heured);
				$agf->heured = dol_mktime($heure_tmp_arr[0], $heure_tmp_arr[1], 0, dol_print_date($agf->date_session, "%m"), dol_print_date($agf->date_session, "%d"), dol_print_date($agf->date_session, "%Y"));

				$heure_tmp_arr = explode(':', $tmpl_calendar->heuref);
				$agf->heuref = dol_mktime($heure_tmp_arr[0], $heure_tmp_arr[1], 0, dol_print_date($agf->date_session, "%m"), dol_print_date($agf->date_session, "%d"), dol_print_date($agf->date_session, "%Y"));

				$result = $agf->create($user);
				if ($result < 0) {
					$error ++;
					$error_message .= $agf->error;
				}
			}
		} else {

			$agf = new Agefodd_sesscalendar($db);

			$agf->sessid = GETPOST('sessid', 'int');
			$agf->date_session = dol_mktime(0, 0, 0, GETPOST('datenewmonth', 'int'), GETPOST('datenewday', 'int'), GETPOST('datenewyear', 'int'));
			$agf->calendrier_type = GETPOST('calendrier_type');
			// From calendar selection
			$heure_tmp_arr = array();

			$heured_tmp = GETPOST('datenewd', 'alpha');
			if (! empty($heured_tmp)) {
				$heure_tmp_arr = explode(':', $heured_tmp);
				$agf->heured = dol_mktime($heure_tmp_arr[0], $heure_tmp_arr[1], 0, GETPOST('datenewmonth', 'int'), GETPOST('datenewday', 'int'), GETPOST('datenewyear', 'int'));
			}

			$heuref_tmp = GETPOST('datenewf', 'alpha');
			if (! empty($heuref_tmp)) {
				$heure_tmp_arr = explode(':', $heuref_tmp);
				$agf->heuref = dol_mktime($heure_tmp_arr[0], $heure_tmp_arr[1], 0, GETPOST('datenewmonth', 'int'), GETPOST('datenewday', 'int'), GETPOST('datenewyear', 'int'));
			}

			$result = $agf->create($user);
			if ($result < 0) {
				$error ++;
				$error_message = $agf->error;
			}
		}

		if (! $error) {
			Header("Location: " . $_SERVER['PHP_SELF'] . "?action=edit&id=" . $id . "&anchor=period");
			exit();
		} else {
			setEventMessage($error_message, 'errors');
		}
	}

	$period_daytodate = GETPOST('period_daytodate');
	if (! empty($period_daytodate)) {
		require_once DOL_DOCUMENT_ROOT . '/core/lib/date.lib.php';

		$error = 0;
		$error_message = '';

		// From template
		$calendrier_type = GETPOST('code_c_session_calendrier_type');
		$weekday = GETPOST('fromdaytodate', 'array');
		$datedaytodate1d = GETPOST('datedaytodate1d', 'alpha');
		$datedaytodate1f = GETPOST('datedaytodate1f', 'alpha');
		$datedaytodate2d = GETPOST('datedaytodate2d', 'alpha');
		$datedaytodate2f = GETPOST('datedaytodate2f', 'alpha');
		$fromdaytodate = GETPOST('fromdaytodate', 'array');
		if (is_array($weekday)) {

			$datestart = dol_mktime(0, 0, 0, GETPOST('datedaytodatestartmonth', 'int'), GETPOST('datedaytodatestartday', 'int'), GETPOST('datedaytodatestartyear', 'int'));
			$dateend = dol_mktime(0, 0, 0, GETPOST('datedaytodateendmonth', 'int'), GETPOST('datedaytodateendday', 'int'), GETPOST('datedaytodateendyear', 'int'));

			$treatmentdate = $datestart;
			while ( $treatmentdate <= $dateend ) {
				$weekday_num = dol_print_date($treatmentdate, '%w');
				if (in_array($weekday_num, $weekday)) {
					$agf = new Agefodd_sesscalendar($db);
					$agf->sessid = GETPOST('sessid', 'int');
					$agf->date_session = $treatmentdate;
					$agf->calendrier_type = $calendrier_type;
					if (! empty($datedaytodate1d)) {
						$heure_tmp_arr = explode(':', $datedaytodate1d);
						$agf->heured = dol_mktime($heure_tmp_arr[0], $heure_tmp_arr[1], 0, dol_print_date($treatmentdate, "%m"), dol_print_date($treatmentdate, "%d"), dol_print_date($treatmentdate, "%Y"));
					}
					if (! empty($datedaytodate1f)) {
						$heure_tmp_arr = explode(':', $datedaytodate1f);
						$agf->heuref = dol_mktime($heure_tmp_arr[0], $heure_tmp_arr[1], 0, dol_print_date($treatmentdate, "%m"), dol_print_date($treatmentdate, "%d"), dol_print_date($treatmentdate, "%Y"));
					}
					$result = $agf->create($user);
					if ($result < 0) {
						$error ++;
						$error_message .= $agf->error;
					}

					if (! empty($datedaytodate2d) && ! empty($datedaytodate2f)) {
						$agf = new Agefodd_sesscalendar($db);
						$agf->sessid = GETPOST('sessid', 'int');
						$agf->date_session = $treatmentdate;
						$agf->calendrier_type = $calendrier_type;
						$heure_tmp_arr = explode(':', $datedaytodate2d);
						$agf->heured = dol_mktime($heure_tmp_arr[0], $heure_tmp_arr[1], 0, dol_print_date($treatmentdate, "%m"), dol_print_date($treatmentdate, "%d"), dol_print_date($treatmentdate, "%Y"));
						$heure_tmp_arr = explode(':', $datedaytodate2f);
						$agf->heuref = dol_mktime($heure_tmp_arr[0], $heure_tmp_arr[1], 0, dol_print_date($treatmentdate, "%m"), dol_print_date($treatmentdate, "%d"), dol_print_date($treatmentdate, "%Y"));

						$result = $agf->create($user);
						if ($result < 0) {
							$error ++;
							$error_message .= $agf->error;
						}
					}
				}
				$treatmentdate = dol_time_plus_duree($treatmentdate, '1', 'd');
			}
		}

		if (! $error) {
			Header("Location: " . $_SERVER['PHP_SELF'] . "?action=edit&id=" . $id . "&anchor=period");
			exit();
		} else {
			setEventMessage($error_message, 'errors');
		}
	}
}

if ($action == 'delete_calsel' && ($user->rights->agefodd->creer || $user->rights->agefodd->modifier)) {
	$deleteselcal = GETPOST('deleteselcal', 'array');
	if (count($deleteselcal) > 0) {
		foreach ( $deleteselcal as $lineid ) {
			$calrem = new Agefodd_sesscalendar($db);
			$result = $calrem->remove($lineid);
			if ($result < 0) {
				setEventMessage($calrem->error, 'errors');
				$error ++;
			}
		}
	}
	if (! $error) {
		Header("Location: " . $_SERVER['PHP_SELF'] . "?action=edit&id=" . $id . '&anchor=period');
		exit();
	}
}

if ($action=='setdates' && ($user->rights->agefodd->creer || $user->rights->agefodd->modifier) && !empty($id)) {

	$agf = new Agsession($db);
	$result = $agf->fetch($id);

	$agf->dated = dol_mktime(0, 0, 0, GETPOST('dadmonth', 'int'), GETPOST('dadday', 'int'), GETPOST('dadyear', 'int'));
	$agf->datef = dol_mktime(0, 0, 0, GETPOST('dafmonth', 'int'), GETPOST('dafday', 'int'), GETPOST('dafyear', 'int'));

	if ($agf->dated > $agf->datef) {
		$error ++;
		setEventMessage($langs->trans('AgfSessionDateErrors'), 'errors');
	}
	if (empty($error)) {
		$result = $agf->update($user);
		if ($result < 0) {
			setEventMessage($agf->error, 'errors');
		}
	}
	$anchor='period';
}

/*
 * View
 */

llxHeader('', $langs->trans("AgfSessionDetail"), '', '', '', '', array(
		'/agefodd/includes/lib.js'
), array());
$form = new Form($db);
$formAgefodd = new FormAgefodd($db);

if (!empty($anchor)) {
print '<script type="text/javascript">
						jQuery(document).ready(function () {
							jQuery(function() {' . "\n";
print '				 $(\'html, body\').animate({scrollTop: $("#' . $anchor . '").offset().top-20}, 500,\'easeInOutCubic\');';
print '			});
					});
					</script> ';
}

/*
 * Action create
 */

// Display session card
if ($id) {
	$agf = new Agsession($db);
	$result = $agf->fetch($id);

	if ($result > 0) {
		if (! (empty($agf->id))) {
			$head = session_prepare_head($agf);

			dol_fiche_head($head, 'calendar', $langs->trans("AgfCalendrier"), 0, 'calendarday');

			dol_agefodd_banner_tab($agf, 'id');
			print '<div class="underbanner clearboth"></div>';

			/*
			 * Calendar management
			 */
			print_barre_liste($langs->trans("AgfCalendrier"), "", "", "", "", "", '', 0);
			print '<span id="period"></span>';

			print '<form name="obj_update" action="' . $_SERVER['PHP_SELF'] . '?id=' . $id . '"  method="POST">' . "\n";
			print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">' . "\n";
			print '<input type="hidden" name="action" value="setdates">' . "\n";
			print '<input type="hidden" name="sessid" value="' . $agf->id . '">' . "\n";
			print '<strong>' . $langs->trans('AgfUpdatesCalendarDates') . '</strong>';
			print '<table class="border">';
			print '<tr><td>' . $langs->trans("AgfDateDebut") . '</td><td>';
			$form->select_date($agf->dated, 'dad', '', '', '', 'update');
			print '</td>';
			print '<td rowspan="2"><input type="image" src="' . dol_buildpath('/agefodd/img/save.png', 1) . '" border="0" align="absmiddle" name="setdates" alt="' . $langs->trans("AgfModSave") . '"></td></tr>';

			print '<tr><td>' . $langs->trans("AgfDateFin") . '</td><td>';
			$form->select_date($agf->datef, 'daf', '', '', '', 'update');
			print '</td></tr>';
			print '</table>';
			print '</form>';

			/*
			 * Confirm delete calendar
			 */
			if (! empty($period_remove)) {
				// Param url = id de la periode à supprimer - id session
				print $form->formconfirm($_SERVER['PHP_SELF'] . '?modperiod=' . $modperiod . '&id=' . $id, $langs->trans("AgfDeletePeriod"), $langs->trans("AgfConfirmDeletePeriod"), "confirm_delete_period", '', '', 1);
			}
			if (! empty($period_remove_all)) {
				// Param url = id de la periode à supprimer - id session
				print $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $id, $langs->trans("AgfAllDeletePeriod"), $langs->trans("AgfConfirmAllDeletePeriod"), "confirm_delete_period_all", '', '', 1);
			}
			print '<div class="tabBar">';
			print '<form name="obj_update" action="' . $_SERVER['PHP_SELF'] . '?action=edit&id=' . $id . '"  method="POST">' . "\n";
			print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">' . "\n";
			print '<input type="hidden" name="action" value="edit">' . "\n";
			print '<input type="hidden" name="sessid" value="' . $agf->id . '">' . "\n";
			print load_fiche_titre($langs->trans('AgfCalendarDates'), '', '');
			print '<table class="noborder period" width="100%" id="period">';

			$calendrier = new Agefodd_sesscalendar($db);
			$calendrier->fetch_all($agf->id);
			$blocNumber = count($calendrier->lines);
			
			$old_date = 0;
			$duree = 0;
			print '<tr class="liste_titre">';
			print '<th width="15%" class="liste_titre">' . $langs->trans('Date') . '</th>';
			print '<th width="35%" class="liste_titre">' . $langs->trans('Hours') . '</th>';
			print '<th class="liste_titre">' . $langs->trans('AgfCalendarType') . '</th>';
			if ($user->rights->agefodd->modifier)
			{
				print '<th width="1%" class="liste_titre linecoledit center">&nbsp;</th>';
				print '<th width="1%" class="liste_titre linecoldelete center">&nbsp;</th>';
				print '<th class="liste_titre center">';
				print '<input type="image" src="' . img_picto($langs->trans("Delete"), 'delete', '', false, 1) . '" border="0" align="absmiddle" name="deletecalsel" title="' . $langs->trans("AgfDeleteOnlySelectedLines") . '" alt="' . $langs->trans("AgfDeleteOnlySelectedLines") . '">';
				print '</th>';
			}
			print '</tr>';

			for($i = 0; $i < $blocNumber; $i ++) {
				if ($calendrier->lines[$i]->id == $modperiod && ! empty($period_remove))
					print '<tr bgcolor="#d5baa8">' . "\n";
				else
					print '<tr>' . "\n";

					// print '<input type="hidden" name="modperiod" value="' . . '">' . "\n";
					// print '<input type="hidden" name="anchor" value="period">' . "\n";

				if ($calendrier->lines[$i]->id == $modperiod && ! ! empty($period_remove)) {
					print '<td  width="20%">' . $langs->trans("AgfPeriodDate") . ' ';
					$form->select_date($calendrier->lines[$i]->date_session, 'date', '', '', '', 'obj_update_' . $i);
					print '</td>';
					print '<td width="150px" nowrap>' . $langs->trans("AgfPeriodTimeB") . ' ';
					print $formAgefodd->select_time(dol_print_date($calendrier->lines[$i]->heured, 'hour'), 'dated');
					print ' - ' . $langs->trans("AgfPeriodTimeE") . ' ';
					print $formAgefodd->select_time(dol_print_date($calendrier->lines[$i]->heuref, 'hour'), 'datef');
					print '</td>';

					print '<td>'.$formAgefodd->select_calendrier_type($calendrier->lines[$i]->calendrier_type).'</td>';

					if ($user->rights->agefodd->modifier)
					{
						print '<td class="linecoledit center">';
						print '<input type="image" src="' . dol_buildpath('/agefodd/img/save.png', 1) . '" border="0" align="absmiddle" name="period_update" alt="' . $langs->trans("AgfModSave") . '">';
						print '<input type="hidden" name="modperiod" value="' . $calendrier->lines[$i]->id . '">';
						print '<input type="hidden" name="period_update" value="1">';
						print '</td>';
						print '<td class="linecoldelete center">&nbsp;</td>';
						print '<td class="center" width="1%">&nbsp;</td>';
					}

				} else {
					print '<td width="20%">' . dol_print_date($calendrier->lines[$i]->date_session, 'daytext') . '</td>';
					print '<td  width="150px">' . dol_print_date($calendrier->lines[$i]->heured, 'hour') . ' - ' . dol_print_date($calendrier->lines[$i]->heuref, 'hour').'</td>';						
					print '<td>'.$calendrier->lines[$i]->calendrier_type_label.'</td>';
					if ($user->rights->agefodd->modifier)
					{
						print '<td class="linecoledit center"><a href="' . $_SERVER['PHP_SELF'] . '?action=edit&id=' . $id . '&modperiod=' . $calendrier->lines[$i]->id . '&anchor=period">' . img_picto($langs->trans("Save"), 'edit') . '</a></td>';
						print '<td class="linecoldelete center"><a href="' . $_SERVER['PHP_SELF'] . '?action=edit&id=' . $id . '&period_remove=1&modperiod=' . $calendrier->lines[$i]->id . '">' . img_picto($langs->trans("Delete"), 'delete') . '</a></td>';
						print '<td class="center" width="1%"><input type="checkbox" name="deleteselcal[]" value="' . $calendrier->lines[$i]->id . '"/></td>';
					}
				}

				// We calculated the total session duration time
				$duree += ($calendrier->lines[$i]->heuref - $calendrier->lines[$i]->heured);

				print '</tr>' . "\n";
			}

			if ((($agf->duree_session * 3600) != $duree) && (empty($conf->global->AGF_NOT_DISPLAY_WARNING_TIME_SESSION))) {
				print '<tr><td colspan="4" align="center">'.img_warning();
				if (($agf->duree_session * 3600) < $duree)
					print $langs->trans("AgfCalendarSup");
				if (($agf->duree_session * 3600) > $duree)
					print $langs->trans("AgfCalendarInf");
				$min = floor($duree / 60);
				$rmin = sprintf("%02d", $min % 60);
				$hour = floor($min / 60);
				print ' (' . $langs->trans("AgfCalendarDureeProgrammee") . ': ' . $hour . ':' . $rmin . ', ';
				print $langs->trans("AgfCalendarDureeThéorique") . ' : ' . ($agf->duree_session) . ':00).</td></tr>';
			}
			
			
			print '</table>';
			if (!empty($calendrier->lines))
			{
				print '<div class="tabsAction">';
				print '<div class="inline-block divButAction">';
				if ($user->rights->agefodd->creer) {
					print '<a class="butActionDelete" href="' . $_SERVER['PHP_SELF'] . '?action=edit&id=' . $id . '&period_remove_all=1">' . $langs->trans('AgfAllDeletePeriod') . '</a>';
				}
				print '</div>';
				print '</div>';
			}
			print '</form>';

			print '<br /><br />';
			// Fiels for new periodes

			print '<div class="fichehalfleft">';
			print load_fiche_titre($langs->trans('AgfCalendarFromTemplate'), '', '');

			print '<form name="obj_update" action="' . $_SERVER['PHP_SELF'] . '?action=edit&id=' . $id . '"  method="POST">' . "\n";
			print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">' . "\n";
			print '<input type="hidden" name="action" value="edit">' . "\n";
			print '<input type="hidden" name="sessid" value="' . $agf->id . '">' . "\n";
			
			print '<table class="noborder period" width="100%" id="period_create">';
			// Add new line from template
			$tmpl_calendar = new Agefoddcalendrier($db);
			$result = $tmpl_calendar->fetch_all();
			if ($result) {
				print '<tr class="liste_titre_filter">';
				print '<td class="liste_titre">&nbsp;</td>';
				print '<td class="liste_titre">'.$formAgefodd->select_calendrier_type('', 'up', true, 'onclick="$(\'#period_create .select_calendrier_type\').val(this.value);"').'</td>';
				print '<td class="liste_titre">&nbsp;</td>';
				print '</tr">';
				
				print '<tr class="liste_titre">';
				print '<th>'.$langs->trans('Date').' - '.$langs->trans('Hours').'</th>';
				print '<th>'.$langs->trans('AgfCalendarType').'</th>';
				print '<th width="5%">&nbsp;</th>';
				print '</tr>';
				
				print '<tr style="display:none;"><td>';
				print '<input type="hidden" name="periodid" value="' . $stagiaires->lines[$i]->stagerowid . '">' . "\n";
				print '<input type="hidden" id="datetmplday"   name="datetmplday"   value="' . dol_print_date($agf->dated, "%d") . '">' . "\n";
				print '<input type="hidden" id="datetmplmonth" name="datetmplmonth" value="' . dol_print_date($agf->dated, "%m") . '">' . "\n";
				print '<input type="hidden" id="datetmplyear"  name="datetmplyear"  value="' . dol_print_date($agf->dated, "%Y") . '">' . "\n";
				print '<td></tr>';

				$tmli = 0;
				foreach ( $tmpl_calendar->lines as $line ) {
					if (empty($agf->dated)) {
						$dated = dol_now();
					} else {
						$dated = $agf->dated;
					}
					if ($line->day_session != 1) {
						$tmpldate = dol_time_plus_duree($dated, (($line->day_session) - 1), 'd');
					} else {
						$tmpldate = $dated;
					}

					if ($tmpldate <= $agf->datef) {
						print '<tr>';
						print '<td class="nowrap">';
						print dol_print_date($tmpldate, 'daytext') . ' ' . $line->heured . ' - ' . $line->heuref;
						print '</td>';
						print '<td>'.$formAgefodd->select_calendrier_type('', 'code_c_session_calendrier_type[]').'</td>';
						print '<td class="center"><input type="checkbox" name="fromtemplate[]" id="fromtemplate" value="' . $line->id . '"/></td>';
						print '</tr>';
					}
					$tmli ++;
				}
				print '</td>';

				print '</tr>' . "\n";
			}

			print '</table>';
			if (!empty($tmpl_calendar->lines))
			{
				print '<div class="tabsAction">';
				print '<div class="inline-block divButAction">';
				if ($user->rights->agefodd->creer) {
					print '<input type="submit" class="butAction" name="period_add" value="'.$langs->trans('AgfNewPeriod').'">';
				}
				print '</div>';
				print '</div>';
			}
			
			print '</form>';

			print '</div>'; // fin fichehalfleft

			print '<div class="fichehalfright">';
			print '<div class="ficheaddleft">';
			print load_fiche_titre($langs->trans('AgfNewPeriodDayToDate'), '', '');
			
			print '<form name="obj_update" action="' . $_SERVER['PHP_SELF'] . '?action=edit&id=' . $id . '"  method="POST">' . "\n";
			print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">' . "\n";
			print '<input type="hidden" name="action" value="edit">' . "\n";
			print '<input type="hidden" name="sessid" value="' . $agf->id . '">' . "\n";
			
			print '<table class="noborder period" width="100%" id="period_create_from_date">';
			
			print '<tr class="liste_titre">';
			print '<th class="liste_titre">'.$langs->trans('AgfWeekdayModels').'</th>';
			print '<th class="liste_titre">'.$langs->trans('Dates').'</th>';
			print '<th class="liste_titre">'.$langs->trans('Hours').'</th>';
			print '<th class="liste_titre">'.$langs->trans('AgfCalendarType').'</th>';
			print '</tr>';

			print '<tr>';
			print '<td>';
			$TWeekDay = array(1,2,3,4,5,6,0);
			foreach ($TWeekDay as $daynum )
			{
				if ($conf->global->{'AGF_WEEKADAY' . $daynum} == 1) {
					$checked = ' checked="checked" ';
				} else {
					$checked = '';
				}
				print '<p><input type="checkbox" ' . $checked . ' name="fromdaytodate[]" id="fromdaytodate" value="' . $daynum . '"/>' . $langs->trans('Day' . $daynum).'</p>';
			}
			print '</td>';

			print '<td>' . $langs->trans("AgfPDFFichePres9") . ' ';
			$form->select_date($agf->dated, 'datedaytodatestart');
			print $langs->trans("AgfPDFFichePres10") . ' ';
			$form->select_date($agf->datef, 'datedaytodateend');
			print '</td>';

			print '<td>';

			print $langs->trans("AgfPeriodTimeB"). ' ';
			print $formAgefodd->select_time(empty($datedaytodate1d) ? $conf->global->AGF_1DAYSHIFT : $datedaytodate1d, 'datedaytodate1d');

			print $langs->trans("AgfPeriodTimeE"). ' ';
			print $formAgefodd->select_time(empty($datedaytodate1f) ? $conf->global->AGF_2DAYSHIFT : $datedaytodate1f, 'datedaytodate1f');
			print '<br />';
			print $langs->trans("AgfPeriodTimeB") . ' ';
			print $formAgefodd->select_time(empty($datedaytodate2d) ? $conf->global->AGF_3DAYSHIFT : $datedaytodate2d, 'datedaytodate2d');

			print $langs->trans("AgfPeriodTimeE"). ' ';
			print $formAgefodd->select_time(empty($datedaytodate2f) ? $conf->global->AGF_4DAYSHIFT : $datedaytodate2f, 'datedaytodate2f');
			print '</td>';

			print '<td>'.$formAgefodd->select_calendrier_type('', 'code_c_session_calendrier_type').'</td>';
			
			print '</tr>';
			print '</table>';

			if ($user->rights->agefodd->creer)
			{
				print '<div class="tabsAction">';
				print '<div class="inline-block divButAction">';
				print '<input type="submit" class="butAction" name="period_daytodate" value="'.$langs->trans('AgfNewPeriod').'">';
				print '</div>';
				print '</div>';
			}
			print '</form>';

			print '</div>'; // fin ficheaddleft
			print '</div>'; // fin fichehalfright

			
			print '<form name="obj_update" action="' . $_SERVER['PHP_SELF'] . '?action=edit&id=' . $id . '"  method="POST">' . "\n";
			print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">' . "\n";
			print '<input type="hidden" name="action" value="edit">' . "\n";
			print '<input type="hidden" name="sessid" value="' . $agf->id . '">' . "\n";
			
			print load_fiche_titre($langs->trans('AgfNewPeriodFromScratch'), '', '');
			print '<table class="noborder period" width="100%" id="period_create_by_period">';


			print '<tr class="liste_titre">';
			print '<th class="liste_titre">'.$langs->trans('Date').'</th>';
			print '<th class="liste_titre">'.$langs->trans('HourStart').'</th>';
			print '<th class="liste_titre">'.$langs->trans('EndHour').'</th>';
			print '<th class="liste_titre">'.$langs->trans('AgfCalendarType').'</th>';
			print '</tr>';

			print '<tr>';
			print '<td>';
			$form->select_date($agf->dated, 'datenew', '', '', '', 'newperiod');
			print '</td>';
			print '<td>';
			print $formAgefodd->select_time('08:00', 'datenewd');
			print '</td>';
			print '<td>';
			print $formAgefodd->select_time('18:00', 'datenewf');
			print '</td>';
			print '<td>'.$formAgefodd->select_calendrier_type('', 'calendrier_type').'</td>';
			print '</tr>' . "\n";

			print '</table>';

			if ($user->rights->agefodd->creer)
			{
				print '<div class="tabsAction">';
				print '<div class="inline-block divButAction">';
				print '<input type="submit" class="butAction" name="period_add" value="'.$langs->trans('AgfNewPeriod').'">';
				print '</div>';
				print '</div>';
			}

			print '</form>';
			print '<div style="clear:both"></div>';
			print '</div>';
		} else {
			print $langs->trans('AgfNoSession');
		}
	} else {
		setEventMessage($agf->error, 'errors');
	}
} else {
	print $langs->trans('AgfNoSession');
}

llxFooter();
$db->close();
