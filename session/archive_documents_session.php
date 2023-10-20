<?php

$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");

require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
require_once ('../class/agsession.class.php');
require_once ('../lib/agefodd.lib.php');
global $db, $user , $langs , $conf;

// Load translation files required by the page
$langs->load("ArchiveDocumentsSessionDesc");

// Security check
$canreaduser = ($user->admin);
if (!$canreaduser) accessforbidden();

$timeDateStart = dol_now();
$timeDateEnd = dol_now();
$action = GETPOST("action", 'none');
$dateStartMonth = GETPOST('dateStartmonth', 'int');
$dateStartday = GETPOST('dateStartday', 'int');
$dateStartyear = GETPOST('dateStartyear', 'int');
$dateEndmonth = GETPOST('dateEndmonth', 'int');
$dateEndday = GETPOST('dateEndday', 'int');
$dateEndyear = GETPOST('dateEndyear', 'int');

if ($dateStartyear > 0){
	$timeDateStart = dol_mktime(0, 0, 0, GETPOST('dateStartmonth', 'int'), GETPOST('dateStartday', 'int'), GETPOST('dateStartyear', 'int'));
}

if ($dateEndyear > 0){
	$timeDateEnd = dol_mktime(0, 0, 0, GETPOST('dateEndmonth', 'int'), GETPOST('dateEndday', 'int'), GETPOST('dateEndyear', 'int'));
}

$form = new Form($db);
llxHeader('', $langs->trans("ArchiveArea"));

// Actions
if ($action == 'archiveDocuments'){
	$sql = 'SELECT * FROM ' . MAIN_DB_PREFIX . 'agefodd_session';
	$sql.= " WHERE datef BETWEEN '" . date('Y-m-d',$timeDateStart)  . "' AND '" . date('Y-m-d',$timeDateEnd) . "'";
	$sql.= " AND entity IN (".getEntity('agefodd_session',1).")";

	$resql = $db->query($sql);

	if ($resql && ($db->num_rows($resql) > 0)) {
		@set_time_limit(0);
		$nbarch = 0;
		while ($obj = $db->fetch_object($resql)) {
			$session = new Agsession($db);
			$res = $session->fetch($obj->rowid);
			if ($res > 0) {
				$resArchive = $session->zipDocumentsSession();
				if($resArchive > 0){
					$nbarch++;
				}elseif($resArchive < 0){
					setEventMessage($session->errors, 'errors');
				}
			}
		}

		setEventMessage($langs->trans('SessionsDocumentsArchived',$nbarch));
	}else{
		setEventMessage($langs->trans('AnyoneSessionForArchive'),'errors');
	}
}

// View
print load_fiche_titre($langs->trans("ArchiveArea"));

print '<br>';
print $langs->trans("SelectDateArchiveDocumentsSession").'<br>';
print '<br>';
print '<form action="archive_documents_session.php" method="post" style="display: table">';
print '<div style="display: table-row">';
print '<label style="display: table-cell; padding-right: 10px">' . $langs->trans("AgfDatePeriodStart") . '</label>';
print $form->selectDate($timeDateStart,'dateStart');
print '</div>';
print '<div style="display: table-row">';
print '<label style="display: table-cell">' . $langs->trans("AgfDatePeriodEnd") . '</label>';
print $form->selectDate($timeDateEnd,'dateEnd');
print '</div>';
print '<br><br>';
print '<button name="action" class="butAction" value="searchArchiveDocuments">' . $langs->trans("AgfSearch") . '</button>';
print $form->textwithtooltip( $langs->trans('Information'), $langs->trans('UpdatePending'),2,1,img_help(1,''));
print '</form>';
print '<br><br>';


//actions

if ($action == 'searchArchiveDocuments') {

	$sql = "SELECT * FROM " . MAIN_DB_PREFIX . "agefodd_session";
	$sql.= " WHERE datef BETWEEN '" . date('Y-m-d',$timeDateStart)  . "' AND '" . date('Y-m-d',$timeDateEnd) . "'";
	$sql.= " AND entity IN (".getEntity('agefodd_session',1).")";

	$resql = $db->query($sql);


	print load_fiche_titre($langs->trans('AgfSearchResults'),'','generic',0,'');

	if ($resql) {
		if (($db->num_rows($resql) > 0)) {
			$nbSessions = $db->num_rows($resql) . ' '.$langs->trans('AgfSessionReadyArchive');

			print '<div class="center">';
			print '<a class="butAction " href="' . $_SERVER ['PHP_SELF'] . '?action=archiveDocuments&dateStartmonth=' . $dateStartMonth . '&dateStartday=' . $dateStartday . '&dateStartyear=' . $dateStartyear . '&dateEndday=' . $dateEndday . '&dateEndmonth=' . $dateEndmonth . '&dateEndyear=' . $dateEndyear . '">' . $langs->trans('AgfArchiveConfirm') . '</a>';
			print '<br><br>';
			print '</div>';
			print '<div>';
			print '<b>'.$langs->trans('AgfNumSessionToArchiveForSelectedPeriod').' ( '.$nbSessions.' )';
			print '<br><br>';
			print '</div>';
			print '<table class="noborder liste center" >';
			print '<tr class="liste_titre">';
			print '<th class="center">' .$langs->trans('Reference') .  '</th>';
			print '<th class="center"> ' . $langs->trans('AgfDateSessionStart') .  '</th>';
			print '<th class="center">' . $langs->trans('AgfDateSessionEnd')  . '</th>';
			print '<th class="center">' .$langs->trans('AgfStatusSession'). '</th>';
			print '<th class="center">' .$langs->trans('Documents') .'</th>';
			print '</tr>';

			while ($obj = $db->fetch_object($resql)) {
				$session = new Agsession($db);
				$res = $session->fetch($obj->rowid);
				if ($res > 0) {
					$haveArchive = false;
					$nbSession++;
					$diroutput = $conf->agefodd->multidir_output[$session->entity] . '/' . $session->id;
					$nbFiles = 0;


					$archiveCanonicalName = 'archive_' . $session->ref;
					$archiveFileName = $archiveCanonicalName . '.zip';


					if (is_dir($diroutput)) {
						$scandir = scandir($diroutput);
						if (!empty($scandir)) {
							foreach ($scandir as $fichier) {
								if ($fichier != '.' && $fichier != '..' && !preg_match('/^thumbs[a-zA-Z]*/', $fichier)) $nbFiles++;

								if ($archiveFileName == $fichier) {
									$haveArchive = true;
								}
							}
						}
					}

					print '<tr>';

					print '		<td>' . $session->getNomUrl(1, '', '', 'ref') . '</td>';
					print '		<td> ' . dol_print_date($session->dated, 'day') . '</td>';
					print '		<td>' . dol_print_date($session->datef, 'day') . '</td>';
					print '		<td>' . $session->getLibStatut() . '</td>';

					print '		<td class="center">';
					if ($haveArchive && $nbFiles == 1) {
						print '<span class="badge badge-success" >' . $archiveFileName . '</span>';
					} elseif ($haveArchive) {
						print '<span class="badge badge-info" >' . $archiveFileName . '</span> + <span class="badge badge-warning" >' . ($nbFiles - 1) . ' ' . $langs->trans('AgfSessionFiles') . '</span>';
					} else {
						print '<span class="badge badge-warning" >' . $nbFiles . ' ' . $langs->trans('AgfSessionFiles') . '</span>';
					}
					print '		</td>';
					print'</tr>';
				}
			}
			print '</table>';
		} else {
			print $langs->trans('AgfNoSessionToArchive');
		}
	}
}
// End of page
llxFooter();
