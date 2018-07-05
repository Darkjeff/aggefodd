<?php
/* Copyright (C) 2001-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2003      Eric Seigne          <erics@rycks.com>
 * Copyright (C) 2004-2014 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (C) 2011      Juanjo Menent        <jmenent@2byte.es>
 * Copyright (C) 2014      Cedric GROSS         <c.gross@kreiz-it.fr>
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
 * \file htdocs/comm/action/peruser.php
 * \ingroup agenda
 * \brief Tab of calendar events per user
 */
$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");
require_once DOL_DOCUMENT_ROOT . '/comm/action/class/actioncomm.class.php';
require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT . '/user/class/usergroup.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/date.lib.php';
require_once '../lib/agefodd.lib.php';

require_once '../class/html.formagefodd.class.php';
require_once '../class/agefodd_formateur.class.php';


$type = GETPOST("type");

$filter_commercial = GETPOST('commercial', 'int');
$filter_customer = GETPOST('fk_soc', 'array');
$filter_contact = GETPOST('contact', 'int');
$filter_trainer = GETPOST('trainerid', 'int');
$filter_type_session = GETPOST('type_session', 'int');
$filter_location = GETPOST('location', 'int');
$filter_trainee = GETPOST('traineeid', 'int');
$display_only_trainer_filter = GETPOST('displayonlytrainerfilter', 'int');
$filterdatestart = dol_mktime(0, 0, 0, GETPOST('dt_start_filtermonth','int'), GETPOST('dt_start_filterday','int'), GETPOST('dt_start_filteryear','int'));
$filter_session_status = GETPOST('search_session_status', 'array');

if ($type == 'trainer' || $type == 'trainerext') {
	$canedit = 0;
	$filter_trainer=$user->id;
} else {
	if (! $user->rights->agefodd->agenda) {
		accessforbidden();
	}
}
if ($type == 'trainerext' && !empty($user->contact_id)) {
	//In this case this is an external trainer
	$agf_trainer = new Agefodd_teacher($db);
	$result=$agf_trainer->fetch_all('', '', '', '', 0, array('f.fk_socpeople'=>$user->contact_id));
	if ($result<0) {
		setEventMessages(null,$agf_trainer->errors,'errors');
	} else {
		if (is_array($agf_trainer->lines)&& count($agf_trainer->lines)>0) {
			$filter_trainer=$agf_trainer->lines[0]->id;
		} else {
			accessforbidden();
		}
	}
}



if ($filter_commercial == - 1) {
	$filter_commercial = 0;
}
if ($filter_customer == - 1) {
	$filter_customer = 0;
}
if ($filter_contact == - 1) {
	$filter_contact = 0;
}
if ($filter_trainer == - 1) {
	$filter_trainer = 0;
}
if ($filter_type_session == - 1) {
	$filter_type_session = '';
}
if ($filter_location == - 1) {
	$filter_location = '';
}
if ($filter_trainee == -1) {
	$filter_trainee=0;
}

$type = GETPOST('type');

if (! $user->rights->agefodd->agendalocation)
		accessforbidden();

$onlysession = GETPOST('onlysession', 'int');
if ($onlysession != '0') {
	$onlysession = 1;
}

$year = GETPOST("year", "int") ? GETPOST("year", "int") : date("Y");
$month = GETPOST("month", "int") ? GETPOST("month", "int") : date("m");
$week = GETPOST("week", "int") ? GETPOST("week", "int") : date("W");
$day = GETPOST("day", "int") ? GETPOST("day", "int") : date("d");


if (!empty($filterdatestart)) {
	$year=GETPOST('dt_start_filteryear','int');
	$month=GETPOST('dt_start_filtermonth','int');
	$day=GETPOST('dt_start_filterday','int');
}


$action = 'show_perlocation'; // We use 'show_week' mode

$langs->load("users");
$langs->load("agenda");
$langs->load("other");
$langs->load("commercial");

// Initialize technical object to manage hooks of thirdparties. Note that conf->hooks_modules contains array array
$hookmanager->initHooks(array (
		'agenda'
		,'agefodd_agenda'
));

/*
 * Actions
 */


/*
 * View
 */

$help_url = 'EN:Module_Agenda_En|FR:Module_Agenda|ES:M&oacute;dulo_Agenda';
llxHeader('', $langs->trans("Agenda"), $help_url);

$form = new Form($db);
$companystatic = new Societe($db);
$formagefodd = new FormAgefodd($db);

$now = dol_now();
$nowarray = dol_getdate($now);
$nowyear = $nowarray['year'];
$nowmonth = $nowarray['mon'];
$nowday = $nowarray['mday'];

// Define list of all external calendars (global setup)
$listofextcals = array ();

$tmp = empty($conf->global->MAIN_DEFAULT_WORKING_HOURS) ? '9-18' : $conf->global->MAIN_DEFAULT_WORKING_HOURS;
$tmparray = explode('-', $tmp);
$begin_h = GETPOST('begin_h') != '' ? GETPOST('begin_h', 'int') : ($tmparray[0] != '' ? $tmparray[0] : 9);
$end_h = GETPOST('end_h') ? GETPOST('end_h') : ($tmparray[1] != '' ? $tmparray[1] : 18);
if ($begin_h < 0 || $begin_h > 23)
	$begin_h = 9;
if ($end_h < 1 || $end_h > 24)
	$end_h = 18;
if ($end_h <= $begin_h)
	$end_h = $begin_h + 1;

$tmp = empty($conf->global->MAIN_DEFAULT_WORKING_DAYS) ? '1-5' : $conf->global->MAIN_DEFAULT_WORKING_DAYS;
$tmparray = explode('-', $tmp);
$begin_d = GETPOST('begin_d') ? GETPOST('begin_d', 'int') : ($tmparray[0] != '' ? $tmparray[0] : 1);
$end_d = GETPOST('end_d') ? GETPOST('end_d') : ($tmparray[1] != '' ? $tmparray[1] : 5);
if ($begin_d < 1 || $begin_d > 7)
	$begin_d = 1;
if ($end_d < 1 || $end_d > 7)
	$end_d = 7;
if ($end_d < $begin_d)
	$end_d = $begin_d + 1;

// print 'xx'.$prev_year.'-'.$prev_month.'-'.$prev_day;
// print 'xx'.$next_year.'-'.$next_month.'-'.$next_day;

$title = $langs->trans("DoneAndToDoActions");
if ($status == 'done')
	$title = $langs->trans("DoneActions");
if ($status == 'todo')
	$title = $langs->trans("ToDoActions");

$param = '';
if ($actioncode || isset($_GET['actioncode']) || isset($_POST['actioncode']))
	$param .= "&amp;actioncode=" . $actioncode;
if ($status || isset($_GET['status']) || isset($_POST['status']))
	$param .= "&amp;status=" . $status;
if ($filter)
	$param .= "&amp;filter=" . $filter;
if ($usergroup)
	$param .= "&amp;usergroup=" . $usergroup;
if ($socid)
	$param .= "&amp;socid=" . $socid;
if ($showbirthday)
	$param .= "&amp;showbirthday=1";
if ($pid)
	$param .= "&amp;projectid=" . $pid;
if ($type)
	$param .= "&amp;type=" . $type;
if ($action == 'show_day' || $action == 'show_week' || $action == 'show_month' || $action != 'show_peruser')
	$param .= '&amp;action=' . $action;
if (!empty($filter_commercial)) {
	$param .= "&amp;commercial=" . $filter_commercial;
}
if (!empty($filter_customer)) {
	foreach ($filter_customer as $fk_soc) $param .= "&amp;fk_soc[]=" . $fk_soc;
}
if (!empty($filter_contact)) {
	$param .= "&amp;contact=" . $filter_contact;
}
if (!empty($filter_commercial)) {
	$param .= "&amp;commercial=" . $filter_commercial;
}
if (!empty($filter_trainer)) {
	$param .= "&amp;trainerid=" . $filter_trainer;
}
if (!empty($filter_type_session)) {
	$param .= "&amp;type_session=" . $filter_type_session;
}
if (!empty($filter_location)) {
	$param .= "&amp;location=" . $filter_location;
}
if (! empty($filter_trainee)) {
	$param .= '&traineeid=' . $filter_trainee;
}
if (is_array($filter_session_status) && count($filter_session_status)>0){
	foreach($filter_session_status as $val) {
		$param .= '&search_session_status[]=' . $val;
	}

}
$param .= "&amp;maxprint=" . $maxprint;

$prev = dol_get_first_day_week($day, $month, $year);
// print "day=".$day." month=".$month." year=".$year;
// var_dump($prev); exit;
$prev_year = $prev['prev_year'];
$prev_month = $prev['prev_month'];
$prev_day = $prev['prev_day'];
$first_day = $prev['first_day'];
$first_month = $prev['month'];
$first_year = $prev['year'];

$week = $prev['week'];

$day = ( int ) $day;
$next = dol_get_next_week($first_day, $week, $first_month, $first_year);
$next_year = $next['year'];
$next_month = $next['month'];
$next_day = $next['day'];

// Define firstdaytoshow and lastdaytoshow (warning: lastdaytoshow is last second to show + 1)
$firstdaytoshow = dol_mktime(0, 0, 0, $first_month, $first_day, $first_year);
$lastdaytoshow = dol_time_plus_duree($firstdaytoshow, 7, 'd');
// print $firstday.'-'.$first_month.'-'.$first_year;
// print dol_print_date($firstdaytoshow,'dayhour');
// print dol_print_date($lastdaytoshow,'dayhour');

$max_day_in_month = date("t", dol_mktime(0, 0, 0, $month, 1, $year));

$tmpday = $first_day;

$nav = "<a href=\"?year=" . $prev_year . "&amp;month=" . $prev_month . "&amp;day=" . $prev_day . $param . "\">" . img_previous($langs->trans("Previous")) . "</a>\n";
$nav .= " <span id=\"month_name\">" . dol_print_date(dol_mktime(0, 0, 0, $first_month, $first_day, $first_year), "%Y") . ", " . $langs->trans("Week") . " " . $week;
$nav .= " </span>\n";
$nav .= "<a href=\"?year=" . $next_year . "&amp;month=" . $next_month . "&amp;day=" . $next_day . $param . "\">" . img_next($langs->trans("Next")) . "</a>\n";
$nav .= " &nbsp; (<a href=\"?year=" . $nowyear . "&amp;month=" . $nowmonth . "&amp;day=" . $nowday . $param . "\">" . $langs->trans("Today") . "</a>)";
$picto = 'calendarweek';

// Must be after the nav definition
$param .= '&year=' . $year . '&month=' . $month . ($day ? '&day=' . $day : '');
// print 'x'.$param;


$tabactive = 'cardperlocation';

$paramnoaction = preg_replace('/action=[a-z_]+/', '', $param);
$canedit = 1;

// TODO agf_calendars_prepare_head($paramnoaction); pour la version master
$head = agf_calendars_prepare_head($paramnoaction);

dol_fiche_head($head, $tabactive, $langs->trans('Agenda'), 0, 'action');
$formagefodd->agenda_filter($form, $year, $month, $day, $filter_commercial, $filter_customer, $filter_contact, $filter_trainer, $canedit, $filterdatestart, '', $onlysession, $filter_type_session, $display_only_trainer_filter, $filter_location, $action, $filter_session_status, $filter_trainee, true);

// TODO remove si intégration dans master
?>
<script type="text/javascript">
	$(function() {
		$('form[name=listactionsfilter] input[name=viewcal]').hide();
		$('form[name=listactionsfilter] input[name=viewday]').hide();
		$('form[name=listactionsfilter] input[name=viewlist]').hide();
		$('form[name=listactionsfilter] img[class=hideonsmartphone]').hide();
	});
</script>
<?php

dol_fiche_end();

$showextcals = $listofextcals;
// Legend

$link = '';
print_fiche_titre('', $link . ' &nbsp; &nbsp; ' . $nav, '');

// Get event in an array
$eventarray = array ();


$sql = '
	SELECT DISTINCT a.id, a.label, a.datep, a.datep2, a.percent, a.fk_user_author, a.fk_user_action, a.transparency, a.priority, a.fulldayevent,
			a.location, a.fk_soc, a.fk_contact, a.fk_contact,
			ca.code,
			agf.rowid AS sessionid, agf.type_session AS sessiontype,
			agf_status.code AS sessionstatus,
			agf_place.ref_interne AS lieu,
			trainer_session.trainer_status,
			trainer.rowid as trainerid

	FROM '.MAIN_DB_PREFIX.'actioncomm a
	INNER JOIN ' . MAIN_DB_PREFIX . 'c_actioncomm as ca ON (a.fk_action = ca.id)

	INNER JOIN ' . MAIN_DB_PREFIX . 'agefodd_session as agf ON (agf.rowid = a.fk_element)
	INNER JOIN ' . MAIN_DB_PREFIX . 'agefodd_session_status_type as agf_status ON (agf.status = agf_status.rowid  AND agf_status.code<>\'NOT\')
	INNER JOIN ' . MAIN_DB_PREFIX . 'agefodd_place as agf_place ON (agf.fk_session_place = agf_place.rowid)

	INNER JOIN ' . MAIN_DB_PREFIX . 'agefodd_session_formateur as trainer_session ON (agf.rowid = trainer_session.fk_session)
	INNER JOIN ' . MAIN_DB_PREFIX . 'agefodd_formateur as trainer ON (trainer_session.fk_agefodd_formateur = trainer.rowid)';

if (! empty($filter_commercial)) {
	$sql .= ' INNER JOIN '.MAIN_DB_PREFIX.'agefodd_session_commercial as salesman ON (agf.rowid = salesman.fk_session_agefodd) ';
}
if (! empty($filter_contact)) {
	$sql .= " INNER JOIN " . MAIN_DB_PREFIX . 'agefodd_session_contact as contact_session ON agf.rowid = contact_session.fk_session_agefodd ';
	$sql .= " INNER JOIN " . MAIN_DB_PREFIX . 'agefodd_contact as contact ON contact_session.fk_agefodd_contact = contact.rowid ';
}
if (! empty($filter_trainer)) {
	if (! empty($conf->global->AGF_DOL_TRAINER_AGENDA)) {
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_formateur_calendrier as trainercal ON (trainercal.fk_agefodd_session_formateur = trainer_session.rowid) ";
		$sql .= " AND trainercal.fk_actioncomm=a.id";
	}
	$sql .= " LEFT OUTER JOIN " . MAIN_DB_PREFIX . 'societe as socsess ON agf.fk_soc = socsess.rowid ';
}
if (! empty($filter_trainee)) {
	$sql .= " INNER JOIN " . MAIN_DB_PREFIX . 'agefodd_session_stagiaire as trainee_session ON (agf.rowid = trainee_session.fk_session_agefodd) ';
}

$sql.= '
	WHERE a.entity IN (' . getEntity('agsession') . ')
	AND a.elementtype="agefodd_agsession"';

if (!empty($filter_session_status))
{
	$sql.= ' AND agf.status IN ('.implode(',', $filter_session_status).')';
}

if (! empty($filter_trainee)) {
	$sql .= " AND trainee_session.fk_stagiaire=".$filter_trainee;
}

if (! empty($filter_commercial)) {
	$sql .= ' AND salesman.fk_user_com=' . $filter_commercial;
}
if (! empty($filter_customer)) {
	$sql .= " AND agf.fk_soc IN (" .implode(',', $filter_customer).")";
}
if (! empty($filter_contact)) {

	if ($conf->global->AGF_CONTACT_DOL_SESSION) {
		$sql .= " AND contact.fk_socpeople=" . $filter_contact;
	} else {
		$sql .= " AND contact.rowid=" . $filter_contact;
	}
}
if (! empty($filter_trainer)) {

	if ($type == 'trainer') {
		$sql .= " AND trainer.fk_user=" . $filter_trainer;
	} else {
		$sql .= " AND trainer_session.fk_agefodd_formateur=" . $filter_trainer;
	}
} else {
	$sql .= " AND ca.code<>'AC_AGF_SESST'";
}
if (! empty($onlysession) && empty($filter_trainer)) {
	$sql .= " AND ca.code='AC_AGF_SESS'";
}
if ($filter_type_session != '') {
	$sql .= " AND agf.type_session=" . $filter_type_session;
}
if (! empty($filter_location)) {
	$sql .= " AND agf.fk_session_place=" . $filter_location;
}
if ($action == 'show_day') {
	$sql .= " AND (";
	$sql .= " (a.datep BETWEEN '" . $db->idate(dol_mktime(0, 0, 0, $month, $day, $year)) . "'";
	$sql .= " AND '" . $db->idate(dol_mktime(23, 59, 59, $month, $day, $year)) . "')";
	$sql .= " OR ";
	$sql .= " (a.datep2 BETWEEN '" . $db->idate(dol_mktime(0, 0, 0, $month, $day, $year)) . "'";
	$sql .= " AND '" . $db->idate(dol_mktime(23, 59, 59, $month, $day, $year)) . "')";
	$sql .= " OR ";
	$sql .= " (a.datep < '" . $db->idate(dol_mktime(0, 0, 0, $month, $day, $year)) . "'";
	$sql .= " AND a.datep2 > '" . $db->idate(dol_mktime(23, 59, 59, $month, $day, $year)) . "')";
	$sql .= ')';
} else {
	// To limit array
	$sql .= " AND (";
	$sql .= " (a.datep BETWEEN '" . $db->idate(dol_mktime(0, 0, 0, $month, 1, $year) - (60 * 60 * 24 * 7)) . "'"; // Start 7 days before
	$sql .= " AND '" . $db->idate(dol_mktime(23, 59, 59, $month, 28, $year) + (60 * 60 * 24 * 10)) . "')"; // End 7 days after + 3 to go from 28 to 31
	$sql .= " OR ";
	$sql .= " (a.datep2 BETWEEN '" . $db->idate(dol_mktime(0, 0, 0, $month, 1, $year) - (60 * 60 * 24 * 7)) . "'";
	$sql .= " AND '" . $db->idate(dol_mktime(23, 59, 59, $month, 28, $year) + (60 * 60 * 24 * 10)) . "')";
	$sql .= " OR ";
	$sql .= " (a.datep < '" . $db->idate(dol_mktime(0, 0, 0, $month, 1, $year) - (60 * 60 * 24 * 7)) . "'";
	$sql .= " AND a.datep2 > '" . $db->idate(dol_mktime(23, 59, 59, $month, 28, $year) + (60 * 60 * 24 * 10)) . "')";
	$sql .= ')';
}

$parameters=array('from' => 'perlocation', 'target' => 'first_query');
$reshook=$hookmanager->executeHooks('printFieldListWhere',$parameters);    // Note that $action and $object may have been modified by hook
$sql.=$hookmanager->resPrint;

//$sql.= ' ORDER BY agf_place.ref_interne ASC';


/*
 *
 * SELECT DISTINCT a.id, a.label, a.datep, a.datep2, a.percent, a.fk_user_author, a.fk_user_action, a.transparency, a.priority, a.fulldayevent, a.location, a.fk_soc, a.fk_contact, a.fk_contact, ca.code, agf.rowid AS sessionid, agf.type_session AS sessiontype, agf_status.code AS sessionstatus, agf_place.ref_interne AS lieu, trainer_session.trainer_status, trainer.rowid as trainerid
FROM llx_actioncomm a
INNER JOIN llx_c_actioncomm as ca ON (a.fk_action = ca.id)
INNER JOIN llx_agefodd_session as agf ON (agf.rowid = a.fk_element)
INNER JOIN llx_agefodd_session_status_type as agf_status ON (agf.status = agf_status.rowid)
INNER JOIN llx_agefodd_place as agf_place ON (agf.fk_session_place = agf_place.rowid AND ref_interne LIKE 'Akteos%')
INNER JOIN llx_agefodd_session_formateur as trainer_session ON (agf.rowid = trainer_session.fk_session)
INNER JOIN llx_agefodd_formateur as trainer ON (trainer_session.fk_agefodd_formateur = trainer.rowid)
WHERE a.entity IN (1)
AND a.elementtype="agefodd_agsession"
AND ca.code<>'AC_AGF_SESST'
AND ca.code='AC_AGF_SESS'
AND ( (a.datep BETWEEN '20160725000000' AND '20160907235959') OR (a.datep2 BETWEEN '20160725000000' AND '20160907235959') OR (a.datep < '20160725000000' AND a.datep2 > '20160907235959'))

UNION

SELECT DISTINCT a.id, a.label, a.datep, a.datep2, a.percent, a.fk_user_author, a.fk_user_action, a.transparency, a.priority, a.fulldayevent, a.location, a.fk_soc, a.fk_contact, a.fk_contact, ca.code, ae.act_sess AS sessionid, null AS sessiontype, null AS sessionstatus, agf_place.ref_interne AS lieu, null, null
FROM llx_actioncomm a
INNER JOIN llx_c_actioncomm as ca ON (a.fk_action = ca.id)
INNER JOIN llx_actioncomm_extrafields as ae ON (a.id = ae.fk_object)
INNER JOIN llx_agefodd_place as agf_place ON (ae.location = agf_place.rowid AND ref_interne LIKE 'Akteos%')
WHERE a.entity IN (1)
AND (a.elementtype<>"agefodd_agsession" OR a.elementtype IS NULL)
AND ca.code<>'AC_AGF_SESST'
AND ca.code<>'AC_AGF_SESS'
AND ( (a.datep BETWEEN '20160725000000' AND '20160907235959') OR (a.datep2 BETWEEN '20160725000000' AND '20160907235959') OR (a.datep < '20160725000000' AND a.datep2 > '20160907235959'))
 *
 */

// TODO remove echo
//echo $sql.'<br />';
//echo $sql;exit;
$TLieu = array();
dol_syslog("agefodd/agenda/perlocation.php", LOG_DEBUG);
$resql = $db->query($sql);
if ($resql) {
	$num = $db->num_rows($resql);
	$i = 0;
	while ( $i < $num ) {
		$obj = $db->fetch_object($resql);

		// Discard auto action if option is on
		if (! empty($conf->global->AGENDA_ALWAYS_HIDE_AUTO) && $obj->code == 'AC_OTH_AUTO') {
			$i ++;
			continue;
		}

		$TLieu[$obj->lieu]++;

		// Create a new object action
		$event = new ActionComm($db);
		$event->id = $obj->id;

		$event->lieu = $obj->lieu;

		$event->datep = $db->jdate($obj->datep); // datep and datef are GMT date
		$event->datef = $db->jdate($obj->datep2);
		$event->type_code = $obj->code;
		$event->type_color = $obj->color;
		// $event->libelle=$obj->label; // deprecated
		$event->label = $obj->label;
		$event->percentage = $obj->percent;
		// $event->author->id=$obj->fk_user_author; // user id of creator
		$event->authorid = $obj->fk_user_author; // user id of creator
		$event->userownerid = $obj->fk_user_action; // user id of owner
		// TODO à utiliser pour la version master
		//$event->fetch_userassigned(); // This load $event->userassigned
		$event->priority = $obj->priority;
		$event->fulldayevent = $obj->fulldayevent;
		$event->location = $obj->location;
		$event->transparency = $obj->transparency;

		$event->sessionid = $obj->sessionid;
		$event->sessiontype = $obj->sessiontype;
		$event->sessionstatus = $obj->sessionstatus;
		$event->trainer_status = $obj->trainer_status;
		$event->trainerid = $obj->trainerid;

		$event->socid = $obj->fk_soc;
		$event->contactid = $obj->fk_contact;
		// $event->societe->id=$obj->fk_soc; // deprecated
		// $event->contact->id=$obj->fk_contact; // deprecated

		$event->fk_element = $obj->fk_element;
		$event->elementtype = $obj->elementtype;

		// Defined date_start_in_calendar and date_end_in_calendar property
		// They are date start and end of action but modified to not be outside calendar view.
		if ($event->percentage <= 0) {
			$event->date_start_in_calendar = $event->datep;
			if ($event->datef != '' && $event->datef >= $event->datep)
				$event->date_end_in_calendar = $event->datef;
			else
				$event->date_end_in_calendar = $event->datep;
		} else {
			$event->date_start_in_calendar = $event->datep;
			if ($event->datef != '' && $event->datef >= $event->datep)
				$event->date_end_in_calendar = $event->datef;
			else
				$event->date_end_in_calendar = $event->datep;
		}
		// Define ponctual property
		if ($event->date_start_in_calendar == $event->date_end_in_calendar) {
			$event->ponctuel = 1;
		}

		// Check values
		if ($event->date_end_in_calendar < $firstdaytoshow || $event->date_start_in_calendar >= $lastdaytoshow) {
			// This record is out of visible range
		} else {
			if ($event->date_start_in_calendar < $firstdaytoshow)
				$event->date_start_in_calendar = $firstdaytoshow;
			if ($event->date_end_in_calendar >= $lastdaytoshow)
				$event->date_end_in_calendar = ($lastdaytoshow - 1);

				// Add an entry in actionarray for each day
			$daycursor = $event->date_start_in_calendar;
			$annee = date('Y', $daycursor);
			$mois = date('m', $daycursor);
			$jour = date('d', $daycursor);

			// Loop on each day covered by action to prepare an index to show on calendar
			$loop = true;
			$j = 0;
			$daykey = dol_mktime(0, 0, 0, $mois, $jour, $annee);
			do {
				// if ($event->id==408) print 'daykey='.$daykey.' '.$event->datep.' '.$event->datef.'<br>';

				$eventarray[$daykey][] = $event;
				$j ++;

				$daykey += 60 * 60 * 24;
				if ($daykey > $event->date_end_in_calendar)
					$loop = false;
			} while ( $loop );

			// print 'Event '.$i.' id='.$event->id.' (start='.dol_print_date($event->datep).'-end='.dol_print_date($event->datef);
			// print ' startincalendar='.dol_print_date($event->date_start_in_calendar).'-endincalendar='.dol_print_date($event->date_end_in_calendar).') was added in '.$j.' different index key of array<br>';
		}
		$i ++;
	}
} else {
	dol_print_error($db);
}
//var_dump($eventarray);exit;
$maxnbofchar = 18;
$cachethirdparties = array ();
$cachecontacts = array ();

// Define theme_datacolor array
$color_file = DOL_DOCUMENT_ROOT . "/theme/" . $conf->theme . "/graph-color.php";
if (is_readable($color_file)) {
	include_once $color_file;
}
if (! is_array($theme_datacolor))
	$theme_datacolor = array (
			array (
					120,
					130,
					150
			),
			array (
					200,
					160,
					180
			),
			array (
					190,
					190,
					220
			)
	);

$newparam = $param; // newparam is for birthday links
$newparam = preg_replace('/showbirthday=/i', 'showbirthday_=', $newparam); // To avoid replacement when replace day= is done
$newparam = preg_replace('/action=show_month&?/i', '', $newparam);
$newparam = preg_replace('/action=show_week&?/i', '', $newparam);
$newparam = preg_replace('/day=[0-9]+&?/i', '', $newparam);
$newparam = preg_replace('/month=[0-9]+&?/i', '', $newparam);
$newparam = preg_replace('/year=[0-9]+&?/i', '', $newparam);
$newparam = preg_replace('/viewweek=[0-9]+&?/i', '', $newparam);
$newparam = preg_replace('/showbirthday_=/i', 'showbirthday=', $newparam); // Restore correct parameter
$newparam .= '&viewweek=1';

echo '<form id="move_event" action="" method="POST"><input type="hidden" name="action" value="mupdate">';
echo '<input type="hidden" name="backtopage" value="' . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'] . '">';
echo '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
echo '<input type="hidden" name="newdate" id="newdate">';
echo '</form>';

// Line header with list of days

// print "begin_d=".$begin_d." end_d=".$end_d;

echo '<table width="100%" class="nocellnopadd cal_month">';

echo '<tr class="liste_titre">';
echo '<td></td>';
$i = 0; // 0 = sunday,
while ( $i < 7 ) {
	if (($i + 1) < $begin_d || ($i + 1) > $end_d) {
		$i ++;
		continue;
	}
	echo '<td align="center" colspan="' . ($end_h - $begin_h) . '">';
	echo $langs->trans("Day" . (($i + (isset($conf->global->MAIN_START_WEEK) ? $conf->global->MAIN_START_WEEK : 1)) % 7));
	print "<br>";
	if ($i)
		print dol_print_date(dol_time_plus_duree($firstdaytoshow, $i, 'd'), 'day');
	else
		print dol_print_date($firstdaytoshow, 'day');
	echo "</td>\n";
	$i ++;
}
echo "</tr>\n";

echo '<tr class="liste_titre">';
echo '<td></td>';
$i = 0;
while ( $i < 7 ) {
	if (($i + 1) < $begin_d || ($i + 1) > $end_d) {
		$i ++;
		continue;
	}
	for($h = $begin_h; $h < $end_h; $h ++) {
		echo '<td align="center">';
		print '<small style="font-family: courier">' . sprintf("%02d", $h) . '</small>';
		print "</td>";
	}
	echo "</td>\n";
	$i ++;
}
echo "</tr>\n";

// Load array of colors by type
$colorsbytype = array ();
$labelbytype = array ();
//TODO à décommenter pour la version master
/*$sql = "SELECT code, color, libelle FROM " . MAIN_DB_PREFIX . "c_actioncomm";
$resql = $db->query($sql);var_dump($sql);
while ( $obj = $db->fetch_object($resql) ) {
	$colorsbytype[$obj->code] = $obj->color;
	$labelbytype[$obj->code] = $obj->libelle;
}*/

// Loop on each user to show calendar
$todayarray = dol_getdate($now, 'fast');
$sav = $tmpday;
$showheader = true;
$var = false;
foreach ($TLieu as $lieu => $nb_event)
{
	$var = ! $var;
	echo "<tr>";
	echo '<td width="15%" class="cal_current_month cal_peruserviewname"' . ($var ? ' style="background: #F8F8F8"' : '') . '>' . $lieu .'</td>';

	$tmpday = $sav;

	// Lopp on each day of week
	$i = 0;
	for($iter_day = 0; $iter_day < 8; $iter_day ++) {
		if (($i + 1) < $begin_d || ($i + 1) > $end_d) {
			$i ++;
			continue;
		}

		// Show days of the current week
		$curtime = dol_time_plus_duree($firstdaytoshow, $iter_day, 'd');
		$tmparray = dol_getdate($curtime, 'fast');
		$tmpday = $tmparray['mday'];
		$tmpmonth = $tmparray['mon'];
		$tmpyear = $tmparray['year'];

		$style = 'cal_current_month';
		if ($iter_day == 6)
			$style .= ' cal_other_month';
		$today = 0;
		if ($todayarray['mday'] == $tmpday && $todayarray['mon'] == $tmpmonth && $todayarray['year'] == $tmpyear)
			$today = 1;
		if ($today)
			$style = 'cal_today_peruser';

		show_day_events2($lieu, $tmpday, $tmpmonth, $tmpyear, $monthshown, $style, $eventarray, 0, $maxnbofchar, $newparam, 1, 300, $showheader, $colorsbytype, $var);

		$i ++;
	}
	echo "</tr>\n";
	$showheader = false;
}

echo "</table>\n";

/*if (! empty($conf->global->AGENDA_USE_EVENT_TYPE)) {
	$langs->load("commercial");
	print '<br>' . $langs->trans("Legend") . ': <br>';
	foreach ( $colorsbytype as $code => $color ) {
		if ($color) {
			print '<div style="float: left; padding: 2px; margin-right: 6px;"><div style="' . ($color ? 'background: #' . $color . ';' : '') . 'width:16px; float: left; margin-right: 4px;">&nbsp;</div>';
			print $langs->trans("Action" . $code) != "Action" . $code ? $langs->trans("Action" . $code) : $labelbytype[$code];
			// print $code;
			print '</div>';
		}
	}
	// $color=sprintf("%02x%02x%02x",$theme_datacolor[0][0],$theme_datacolor[0][1],$theme_datacolor[0][2]);
	print '<div style="float: left; padding: 2px; margin-right: 6px;"><div class="peruser_busy" style="width:16px; float: left; margin-right: 4px;">&nbsp;</div>';
	print $langs->trans("Other");
	print '</div>';
	// TODO Show this if at least one cumulated event
	 print '<div style="float: left; padding: 2px; margin-right: 6px;"><div style="background: #222222; width:16px; float: left; margin-right: 4px;">&nbsp;</div>';
	 print $langs->trans("SeveralEvents");
	 print '</div>';

}*/

// Add js code to manage click on a box
print '<script type="text/javascript" language="javascript">
jQuery(document).ready(function() {
	jQuery(".onclickopenref").click(function() {
		var sessionid = $(this).data("sessionid");
		var userid = $(this).data("trainerid");
		var year = $(this).data("year");
		var month = $(this).data("month");
		var day = $(this).data("day");
		var hour = $(this).data("hour");
		var min = $(this).data("min");
		var ids = $(this).data("ids-event");

		if (sessionid != "undefined" && sessionid > 0)
		{
			/* alert(\'session found\'); */
			url = "' . dol_buildpath('/agefodd/session/card.php?id=',1). '"+sessionid;
			window.location.href = url;
		}
		else if (userid == "-1") {
			/* alert(\'normal event\'); */
			ids = ids.toString();
			var idsarray=ids.split(\',\');
			if (idsarray.length>0) {
			   url = "' . DOL_URL_ROOT . '/comm/action/card.php?id="+idsarray[0];
			} else {
				url = "' . DOL_URL_ROOT . '/comm/action/card.php?id="+ids;
			}
			window.location.href = url;
		}
		else
		{
			/* alert(\'no event\'); */
			url = "' . DOL_URL_ROOT . '/comm/action/card.php?action=create&assignedtouser="+userid+"&datep="+year+month+day+hour+min+"00&backtopage=' . urlencode($_SERVER["PHP_SELF"] . '?year=' . $year . '&month=' . $month . '&day=' . $day) . '"
			window.location.href = url;
		}

	});
});
</script>';

llxFooter();

$db->close();

/**
 * Show event of a particular day for a user
 *
 * @param string $lieu
 * @param int $day
 * @param int $month
 * @param int $year
 * @param int $monthshown month shown in calendar view
 * @param string $style to use for this day
 * @param array $eventarray of events
 * @param int $maxprint of actions to show each day on month view (0 means no limit)
 * @param int $maxnbofchar of characters to show for event line
 * @param string $newparam on current URL
 * @param int $showinfo extended information (used by day view)
 * @param int $minheight height for each event. 60px by default.
 * @param boolean $showheader
 * @param array $colorsbytype colors by type
 * @param bool $var false for alternat style on tr/td
 * @return void
 */
function show_day_events2($lieu, $day, $month, $year, $monthshown, $style, &$eventarray, $maxprint = 0, $maxnbofchar = 16, $newparam = '', $showinfo = 0, $minheight = 60, $showheader = false, $colorsbytype = array(), $var = false) {
	global $db,$user;
	global $user, $conf, $langs, $hookmanager, $action;
	global $filter, $filtert, $status, $actioncode; // Filters used into search form
	global $theme_datacolor; // Array with a list of different we can use (come from theme)
	global $cachethirdparties, $cachecontacts, $colorindexused;
	global $begin_h, $end_h;


	$cases1 = array (); // Color first half hour
	$cases2 = array (); // Color second half hour

	$curtime = dol_mktime(0, 0, 0, $month, $day, $year);

	$i = 0;
	$nummytasks = 0;
	$numother = 0;
	$numbirthday = 0;
	$numical = 0;
	$numicals = array ();
	$ymd = sprintf("%04d", $year) . sprintf("%02d", $month) . sprintf("%02d", $day);

	$nextindextouse = is_array($colorindexused) ? count($colorindexused) : 0; // At first run, this is 0, so fist user has 0, next 1, ...
	                                          // if ($username->id && $day==1) var_dump($eventarray);

	// We are in a particular day for $username, now we scan all events
	foreach ( $eventarray as $daykey => $notused ) {
		$annee = date('Y', $daykey);
		$mois = date('m', $daykey);
		$jour = date('d', $daykey);
		// print $annee.'-'.$mois.'-'.$jour.' '.$year.'-'.$month.'-'.$day."<br>\n";

		// Is it the day we are looking for when calling function ?
		if ($day == $jour && $month == $mois && $year == $annee) {
			// Scan all event for this date
			foreach ( $eventarray[$daykey] as $index => $event ) {
				// $keysofuserassigned=array_keys($event->userassigned);

				if ($lieu != $event->lieu)
					continue; // We discard record if event is from another user than user we want to show
						          // if ($username->id != $event->userownerid) continue; // We discard record if event is from another user than user we want to show

				// Define $color (Hex string like '0088FF') and $cssclass of event
				if (isset($event->sessionstatus)) {
					if (isset($event->sessionstatus)) {
						if ($event->sessionstatus == 'ENV')
							$color = '6666ff';
						if ($event->sessionstatus == 'CONF')
							$color = '66ff99';
						if ($event->sessionstatus == 'NOT')
							$color = 'ff6666';
						if ($event->sessionstatus == 'ARCH')
							$color = 'c0c0c0';
					} else {
						$color = 'c0c0c0';
					}
				} else {
				if ($event->trainer_status == 0)
					$color = 'F8F816';
				if ($event->trainer_status == 1)
					$color = '66ff99';
				if ($event->trainer_status == 2)
					$color = '33ff33';
				if ($event->trainer_status == 3)
					$color = '3366ff';
				if ($event->trainer_status == 4)
					$color = '33ccff';
				if ($event->trainer_status == 5)
					$color = 'cc6600';
				if ($event->trainer_status == 6)
					$color = 'cc0000';
				}
					// $cssclass=$cssclass.' '.$cssclass.'_day_'.$ymd;

				// Define all rects with event (cases1 is first half hour, cases2 is second half hour)
					// var_dump($event);
				for($h = $begin_h; $h < $end_h; $h ++) {
					// if ($username->id == 1 && $day==1) print 'h='.$h;
					$newcolor = ''; // init
					if (empty($event->fulldayevent)) {
						$a = dol_mktime(( int ) $h, 0, 0, $month, $day, $year, false, false);
						$b = dol_mktime(( int ) $h, 30, 0, $month, $day, $year, false, false);
						$c = dol_mktime(( int ) $h + 1, 0, 0, $month, $day, $year, false, false);

						$dateendtouse = $event->date_end_in_calendar;
						if ($dateendtouse == $event->date_start_in_calendar)
							$dateendtouse ++;

							// print dol_print_date($event->date_start_in_calendar,'dayhour').'-'.dol_print_date($a,'dayhour').'-'.dol_print_date($b,'dayhour').'<br>';

						if ($event->date_start_in_calendar < $b && $dateendtouse > $a) {
							$busy = $event->transparency;
							$cases1[$h][$event->id]['busy'] = $busy;
							$cases1[$h][$event->id]['string'] = dol_print_date($event->date_start_in_calendar, 'dayhour');
							if ($event->date_end_in_calendar && $event->date_end_in_calendar != $event->date_start_in_calendar) {
								$tmpa = dol_getdate($event->date_start_in_calendar, true);
								$tmpb = dol_getdate($event->date_end_in_calendar, true);
								if ($tmpa['mday'] == $tmpb['mday'] && $tmpa['mon'] == $tmpb['mon'] && $tmpa['year'] == $tmpb['year'])
									$cases1[$h][$event->id]['string'] .= '-' . dol_print_date($event->date_end_in_calendar, 'hour');
								else
									$cases1[$h][$event->id]['string'] .= '-' . dol_print_date($event->date_end_in_calendar, 'dayhour');
							}
							$cases1[$h][$event->id]['string'] .= ' - ' . $event->label;
							$cases1[$h][$event->id]['typecode'] = $event->type_code;
							if ($event->socid) {
								// $cases1[$h][$event->id]['string'].='xxx';
							}
							$cases1[$h][$event->id]['color'] = $color;
						}
						if ($event->date_start_in_calendar < $c && $dateendtouse > $b) {
							$busy = $event->transparency;
							$cases2[$h][$event->id]['busy'] = $busy;
							$cases2[$h][$event->id]['string'] = dol_print_date($event->date_start_in_calendar, 'dayhour');
							if ($event->date_end_in_calendar && $event->date_end_in_calendar != $event->date_start_in_calendar) {
								$tmpa = dol_getdate($event->date_start_in_calendar, true);
								$tmpb = dol_getdate($event->date_end_in_calendar, true);
								if ($tmpa['mday'] == $tmpb['mday'] && $tmpa['mon'] == $tmpb['mon'] && $tmpa['year'] == $tmpb['year'])
									$cases2[$h][$event->id]['string'] .= '-' . dol_print_date($event->date_end_in_calendar, 'hour');
								else
									$cases2[$h][$event->id]['string'] .= '-' . dol_print_date($event->date_end_in_calendar, 'dayhour');
							}
							$cases2[$h][$event->id]['string'] .= ' - ' . $event->label;
							$cases2[$h][$event->id]['typecode'] = $event->type_code;
							if ($event->socid) {
								// $cases2[$h][$event->id]['string'].='xxx';
							}
							$cases2[$h][$event->id]['color'] = $color;
						}
					} else {
						$busy = $event->transparency;
						$cases1[$h][$event->id]['busy'] = $busy;
						$cases2[$h][$event->id]['busy'] = $busy;
						$cases1[$h][$event->id]['string'] = $event->label;
						$cases2[$h][$event->id]['string'] = $event->label;
						$cases1[$h][$event->id]['typecode'] = $event->type_code;
						$cases2[$h][$event->id]['typecode'] = $event->type_code;
						$cases1[$h][$event->id]['color'] = $color;
						$cases2[$h][$event->id]['color'] = $color;
					}
				}
			}

			break; // We found the date we were looking for. No need to search anymore.
		}
	}

	for($h = $begin_h; $h < $end_h; $h ++) {
		$color1 = '';
		$color2 = '';
		$style1 = '';
		$style2 = '';
		$string1 = '&nbsp;';
		$string2 = '&nbsp;';
		$title1 = '';
		$title2 = '';

		if (isset($cases1[$h]) && $cases1[$h] != '') {
			// $title1.=count($cases1[$h]).' '.(count($cases1[$h])==1?$langs->trans("Event"):$langs->trans("Events"));
			if (count($cases1[$h]) > 1)
				$title1 .= count($cases1[$h]) . ' ' . (count($cases1[$h]) == 1 ? $langs->trans("Event") : $langs->trans("Events"));
			$string1 = '&nbsp;';
			if (empty($conf->global->AGENDA_NO_TRANSPARENT_ON_NOT_BUSY))
				$style1 = 'peruser_notbusy';
			else
				$style1 = 'peruser_busy';
			foreach ( $cases1[$h] as $id => $ev ) {
				if ($ev['busy'])
					$style1 = 'peruser_busy';
			}
		}
		if (isset($cases2[$h]) && $cases2[$h] != '') {
			// $title2.=count($cases2[$h]).' '.(count($cases2[$h])==1?$langs->trans("Event"):$langs->trans("Events"));
			if (count($cases2[$h]) > 1)
				$title2 .= count($cases2[$h]) . ' ' . (count($cases2[$h]) == 1 ? $langs->trans("Event") : $langs->trans("Events"));
			$string2 = '&nbsp;';
			if (empty($conf->global->AGENDA_NO_TRANSPARENT_ON_NOT_BUSY))
				$style2 = 'peruser_notbusy';
			else
				$style2 = 'peruser_busy';
			foreach ( $cases2[$h] as $id => $ev ) {
				if ($ev['busy'])
					$style2 = 'peruser_busy';
			}
		}

		if ($h == $begin_h)
			echo '<td class="' . $style . '_peruserleft cal_peruser"' . ($var ? ' style="background: #F8F8F8"' : '') . '>';
		else
			echo '<td class="' . $style . ' cal_peruser"' . ($var ? ' style="background: #F8F8F8"' : '') . '>';

		if (!empty($cases1[$h])) {
			$output = array_slice($cases1[$h], 0, 1);
			if ($output[0]['string'])
				$title1 .= ($title1 ? ' - ' : '') . $output[0]['string'];
			if ($output[0]['color'])
				$color1 = $output[0]['color'];
		}

			// 1 seul evenement
		if (!empty($cases2[$h])) {
			$output = array_slice($cases2[$h], 0, 1);
			if ($output[0]['string'])
				$title2 .= ($title2 ? ' - ' : '') . $output[0]['string'];
			if ($output[0]['color'])
				$color2 = $output[0]['color'];
		}

		$ids1 = '';
		$ids2 = '';

		if (!empty($cases1[$h]) && count($cases1[$h]) && array_keys($cases1[$h]))
			$ids1 = join(',', array_keys($cases1[$h]));
		if (!empty($cases2[$h]) && count($cases2[$h]) && array_keys($cases2[$h]))
			$ids2 = join(',', array_keys($cases2[$h]));
			// var_dump($cases1[$h]);
		print '<table class="nobordernopadding" width="100%">';
		print '<tr><td ' . ($color1 ? 'style="background: #' . $color1 . ';"' : '') . 'class="' . ($style1 ? $style1 . ' ' : '') . 'onclickopenref' . ($title1 ? ' cursorpointer' : '') . '" data-sessionid="'.$event->sessionid.'" data-trainerid="'.$event->trainerid.'" data-year="'.sprintf("%04d", $year).'" data-month="'.sprintf("%02d", $month).'" data-day="'.sprintf("%02d", $day).'" data-hour="'.sprintf("%02d", $h).'" data-min="00" data-ids-event="'.($ids1 ? $ids1 : 'none').'" ' . ($title1 ? ' title="' . $title1 . '"' : '') . '>';
		print $string1;
		print '</td><td ' . ($color2 ? 'style="background: #' . $color2 . ';"' : '') . 'class="' . ($style2 ? $style2 . ' ' : '') . 'onclickopenref' . ($title1 ? ' cursorpointer' : '') . '" data-sessionid="'.$event->sessionid.'" data-trainerid="'.$event->trainerid.'" data-year="'.sprintf("%04d", $year).'" data-month="'.sprintf("%02d", $month).'" data-day="'.sprintf("%02d", $day).'" data-hour="'.sprintf("%02d", $h).'" data-min="00" data-ids-event="'.($ids1 ? $ids1 : 'none').'" ' . ($title2 ? ' title="' . $title2 . '"' : '') . '>';
		print $string2;
		print '</td></tr>';
		print '</table>';
		print '</td>';
	}
}
