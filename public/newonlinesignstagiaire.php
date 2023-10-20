<?php
/* Copyright (C) 2001-2002	Rodolphe Quiedeville	<rodolphe@quiedeville.org>
 * Copyright (C) 2006-2017	Laurent Destailleur		<eldy@users.sourceforge.net>
 * Copyright (C) 2009-2012	Regis Houssin			<regis.houssin@inodbox.com>
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
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *        \file       htdocs/public/onlinesign/newonlinesign.php
 *        \ingroup    core
 *        \brief      File to offer a way to make an online signature for a particular Dolibarr entity
 *                    Example of URL: https://localhost/public/onlinesign/newonlinesign.php?ref=PR...
 */

if (!defined('NOLOGIN')) {
	define('NOLOGIN', 1); // This means this output page does not require to be logged.
}
if (!defined('NOCSRFCHECK')) {
	define('NOCSRFCHECK', 1); // We accept to go on this page from external web site.
}
if (!defined('NOIPCHECK')) {
	define('NOIPCHECK', '1'); // Do not check IP defined into conf $dolibarr_main_restrict_ip
}
if (!defined('NOBROWSERNOTIF')) {
	define('NOBROWSERNOTIF', '1');
}

// For MultiCompany module.
// Do not use GETPOST here, function is not defined and define must be done before including main.inc.php
// Because 2 entities can have the same ref.
$entity = (!empty($_GET['entity']) ? (int)$_GET['entity'] : (!empty($_POST['entity']) ? (int)$_POST['entity'] : 1));
if (is_numeric($entity)) {
	define('DOLENTITY', $entity);
}

$res = 0;
if(! $res && file_exists('../main.inc.php')) {
	$res = require '../main.inc.php';
}
if(! $res && file_exists('../../main.inc.php')) {
	$res = require '../../main.inc.php';
}
if(! $res && file_exists('../../../main.inc.php')) {
	$res = require '../../../main.inc.php';
}
if(! $res) {
	die('Include of main fails');
}
require_once DOL_DOCUMENT_ROOT . '/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/payments.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';

//---------------------------------------------
require_once('../class/agsession.class.php');
require_once('../class/html.formagefodd.class.php');
require_once('../class/agefodd_session_calendrier.class.php');
require_once('../class/agefodd_calendrier.class.php');
require_once('../class/agefodd_session_formateur.class.php');
require_once('../class/agefodd_session_stagiaire.class.php');
require_once('../class/agefodd_session_element.class.php');
require_once('../lib/agefodd.lib.php');
require_once __DIR__ . '/../class/agefodd_signature.class.php';
require_once(DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php');
require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/functions.lib.php';
//---------------------------------------------


// Load translation files
$langs->loadLangs(array('main', 'other', 'dict', 'bills', 'companies', 'errors', 'members', 'paybox', 'propal'));

// Security check
// No check on module enabled. Done later according to $validpaymentmethod

// Get parameters
$action = GETPOST('action', 'aZ09');
$cancel = GETPOST('cancel', 'alpha');
$cancelsignature = GETPOST('cancel_signature','alpha');
$id = GETPOST('id', 'int');
$personid = GETPOST('personid', 'int');
$person_type = GETPOST('person_type', 'alpha');

if (!empty($person_type))
{
    if ($person_type == 'trainee')
    {
        $idstagiaire = $personid;
    }
    elseif ($person_type == 'trainer')
    {
        $idformateur = $personid;
    }
}

$idstagiaire = GETPOST('fk_stagiaire', 'int');
$idformateur = GETPOST('fk_formateur', 'int');
$newToken = function_exists('newToken') ? newToken() : $_SESSION['newtoken'];
$toselect = GETPOST('toselect', 'array');
$formAgefodd = new FormAgefodd($db);
$agf = new Agsession($db);
$result = $agf->fetch($id);
if($result <= 0) {
    dol_print_error($db);
    exit;
}


$SECUREKEY = GETPOST('securekey'); // Secure key


$source = GETPOST('source', 'alpha');

$ref = $REF = GETPOST('ref', 'alpha');

if (!empty($cancelsignature && !empty($idstagiaire))) {
	header('Location: ' . $_SERVER['PHP_SELF'].'?ref='.$ref.'&id='.$id.'&fk_stagiaire='.$idstagiaire.'&securekey='.$SECUREKEY);
	exit;
}

if (!empty($cancelsignature && !empty($idformateur))) {
	header('Location: ' . $_SERVER['PHP_SELF'].'?ref='.$ref.'&id='.$id.'&fk_formateur='.$idformateur.'&securekey='.$SECUREKEY);
	exit;
}

if (empty($source)) {
	$source = 'agefodd_agsession';
}


// Complete urls for post treatment


if (!empty($source)) {
	$urlok .= 'source=' . urlencode($source) . '&';
	$urlko .= 'source=' . urlencode($source) . '&';
}
if (!empty($REF)) {
	$urlok .= 'ref=' . urlencode($REF) . '&';
	$urlko .= 'ref=' . urlencode($REF) . '&';
}
if (!empty($SECUREKEY)) {
	$urlok .= 'securekey=' . urlencode($SECUREKEY) . '&';
	$urlko .= 'securekey=' . urlencode($SECUREKEY) . '&';
}
if (!empty($entity)) {
	$urlok .= 'entity=' . urlencode($entity) . '&';
	$urlko .= 'entity=' . urlencode($entity) . '&';
}
$urlok = preg_replace('/&$/', '', $urlok); // Remove last &
$urlko = preg_replace('/&$/', '', $urlko); // Remove last &

if ($source == 'agefodd_agsession') {
	require_once DOL_DOCUMENT_ROOT . '/comm/propal/class/propal.class.php';
	dol_include_once('/agefodd/class/agefodd_stagiaire.class.php');
	dol_include_once('/agefodd/class/agefodd_formateur.class.php');
	if (!empty($idstagiaire)){
		$objectstagiaire = new Agefodd_stagiaire($db);
		$stagiaire = $objectstagiaire->fetch($idstagiaire);
		if ($stagiaire < 0){
			setEventMessage($objectstagiaire->error, 'errors');
		}
		$creditor = $objectstagiaire->prenom.' '.$objectstagiaire->nom;
		$idparticipant = $idstagiaire;
		$typeparticipant = 'trainee';
	}

	if (!empty($idformateur)){
		$objectformateur = new Agefodd_teacher($db);

		$formateur = $objectformateur->fetch($idformateur);
		if ($formateur < 0){
			setEventMessage($objectformateur->error, 'errors');
		}
		$creditor = $objectformateur->name.' '.$objectformateur->firstname;
		$idparticipant = $idformateur;
		$typeparticipant = 'trainer';
	}

} else {
	accessforbidden('Bad value for source');
	exit;
}

// Check securitykey
$securekeyseed = isset($conf->global->AGEFODD_ONLINE_SIGNATURE_SECURITY_TOKEN) ? $conf->global->AGEFODD_ONLINE_SIGNATURE_SECURITY_TOKEN : '';

if (!dol_verifyHash($securekeyseed .$id . $idparticipant. $typeparticipant .  (empty($conf->multicompany->enabled) ? '' : $entity), $SECUREKEY, '0')) {
	http_response_code(403);
	print 'Bad value for securitykey. Value provided ' . dol_escape_htmltag($SECUREKEY) . ' does not match expected value for ref=' . dol_escape_htmltag($ref);
	exit(-1);
}


/*
 * View
 */

$form = new Form($db);
$head = '';
if (!empty($conf->global->MAIN_SIGN_CSS_URL)) {
	$head = '<link rel="stylesheet" type="text/css" href="' . $conf->global->MAIN_SIGN_CSS_URL . '?lang=' . $langs->defaultlang . '">' . "\n";
}

$conf->dol_hide_topmenu = 1;
$conf->dol_hide_leftmenu = 1;

$replacemainarea = (empty($conf->dol_hide_leftmenu) ? '<div>' : '') . '<div>';

unset($conf->modules_parts['css']);
unset($conf->modules_parts['js']);

llxHeader($head, $langs->trans('OnlineSignature'), '', '', 0, 0, '', '', '', 'onlinepaymentbody', $replacemainarea, 1);



print '<span id="dolpaymentspan"></span>' . "\n";
print '<div class="center">' . "\n";
print '<form id="dolpaymentform" class="center" name="paymentform" action="' . $_SERVER['PHP_SELF'] . '" method="POST">' . "\n";
print '<input type="hidden" name="token" value="' . newToken() . '">' . "\n";
print '<input type="hidden" name="action" value="dosign">' . "\n";
print '<input type="hidden" name="securekey" value="' . $SECUREKEY . '">' . "\n";
print '<input type="hidden" name="entity" value="' . $entity . '" />';
print '<input type="hidden" name="fk_stagiaire" value="' . $idstagiaire . '" />';
print '<input type="hidden" name="fk_formateur" value="' . $idformateur . '" />';
print '<input type="hidden" name="id" value="' . $id . '" />';
print "\n";
print '<!-- Form to sign -->' . "\n";

print '<table id="dolpublictable" summary="Payment form" class="center">' . "\n";

// Show logo (search order: logo defined by ONLINE_SIGN_LOGO_suffix, then ONLINE_SIGN_LOGO_, then small company logo, large company logo, theme logo, common logo)
// Define logo and logosmall
$logosmall = $mysoc->logo_small;
$logo = $mysoc->logo;
$paramlogo = 'ONLINE_SIGN_LOGO_' . $suffix;
if (!empty($conf->global->$paramlogo)) {
	$logosmall = $conf->global->$paramlogo;
} elseif (!empty($conf->global->ONLINE_SIGN_LOGO)) {
	$logosmall = $conf->global->ONLINE_SIGN_LOGO;
}
//print '<!-- Show logo (logosmall='.$logosmall.' logo='.$logo.') -->'."\n";
// Define urllogo
$urllogo = '';
$urllogofull = '';

if (!empty($logosmall) && is_readable($conf->mycompany->dir_output . '/logos/thumbs/' . $logosmall)) {
	$urllogo = DOL_URL_ROOT . '/viewimage.php?modulepart=mycompany&amp;entity=' . $conf->entity . '&amp;file=' . urlencode('logos/thumbs/' . $logosmall);
	$urllogofull = $dolibarr_main_url_root . '/viewimage.php?modulepart=mycompany&entity=' . $conf->entity . '&file=' . urlencode('logos/thumbs/' . $logosmall);
} elseif (!empty($logo) && is_readable($conf->mycompany->dir_output . '/logos/' . $logo)) {
	$urllogo = DOL_URL_ROOT . '/viewimage.php?modulepart=mycompany&amp;entity=' . $conf->entity . '&amp;file=' . urlencode('logos/' . $logo);
	$urllogofull = $dolibarr_main_url_root . '/viewimage.php?modulepart=mycompany&entity=' . $conf->entity . '&file=' . urlencode('logos/' . $logo);
}
// Output html code for logo
if ($urllogo) {
	print '<div class="backgreypublicpayment">';
	print '<div class="logopublicpayment">';
	print '<img id="dolpaymentlogo" src="' . $urllogo . '"';
	print '>';
	print '</div>';
	if (empty($conf->global->MAIN_HIDE_POWERED_BY)) {
		print '<div class="poweredbypublicpayment opacitymedium right"><a class="poweredbyhref" href="https://www.dolibarr.org?utm_medium=website&utm_source=poweredby" target="dolibarr" rel="noopener">' . $langs->trans('PoweredBy') . '<br><img class="poweredbyimg" src="' . DOL_URL_ROOT . '/theme/dolibarr_logo.svg" width="80px"></a></div>';
	}
	print '</div>';
}

// Output introduction text
$text = '';
if (!empty($conf->global->ONLINE_SIGN_NEWFORM_TEXT)) {
	$reg = array();
	if (preg_match('/^\((.*)\)$/', $conf->global->ONLINE_SIGN_NEWFORM_TEXT, $reg)) {
		$text .= $langs->trans($reg[1]) . "<br>\n";
	} else {
		$text .= $conf->global->ONLINE_SIGN_NEWFORM_TEXT . "<br>\n";
	}
	$text = '<tr><td align="center"><br>' . $text . '<br></td></tr>' . "\n";
}
if (empty($text)) {
	$text .= '<tr><td class="textpublicpayment"><br><strong>' . $langs->trans('agfWelcomeOnOnlineSignaturePage', $agf->ref) . '</strong></td></tr>' . "\n";
	$text .= '<tr><td class="textpublicpayment opacitymedium">' . $langs->trans('agfThisScreenAllowsYouToSignDocFrom', $creditor) . '<br><br></td></tr>' . "\n";
}
print $text;

// Output payment summary form
print '<tr><td align="center" style="min-width: 900px;">';
print '<table with="100%" id="tablepublicpayment">';
print '<tr><td align="left" colspan="2" class="opacitymedium">' . $langs->trans('agfThisIsInformationOnDocumentToSign') . ' :</td></tr>' . "\n";

$error = 0;

// Creditor
print '<tr class="CTableRow2"><td class="CTableRow2">' . $langs->trans('AgfFichePresByParticipantTitleM');
print '</td><td class="CTableRow2">';
print '<b>' . $creditor . '</b>';
print '<input type="hidden" name="creditor" value="' . $creditor . '">';
print '</td></tr>' . "\n";

// Session
print '<tr class="CTableRow2"><td class="CTableRow2">' . $langs->trans('AgfSessionDetail');
print '</td><td class="CTableRow2">';
print '<b>' . $agf->ref . '</b>';
print '</td></tr>' . "\n";


print '<input type="hidden" name="source" value="' . GETPOST('source', 'alpha') . '">';
print '<input type="hidden" name="ref" value="' . $agf->ref . '">';
print '</td></tr>' . "\n";


if ($id && !(empty($agf->id))) {

	$arrayofaction = [];
	$arrayofaction['sign'] = $langs->trans('SignCalendrier');
	//$arrayofaction['predelete'] = $langs->trans('DeleteSignatureCalendrier');

	if (empty($toselect)) {
		$massactionbutton = $formAgefodd->selectMassAction('', $arrayofaction);
	}

	// obligatoire de mettre l'action ici car le canvas de signature doit être au dessus tu tableau comme chaque massaction
	if (!empty($massaction)) {
		switch ($massaction) {
			case 'sign':
				$signature = new AgefoddSignature($db);
				$more_hidden_inputs = '<input type="hidden" id="personid" value="' . $personid . '"><input type="hidden" id="person_type" value="' . $person_type . '">';
				$signature->getFormSignatureCreneau($toselect, $agf, $personid, $person_type);
				break;
		}
	}
	$agf->displaySignTimeSlotForm($idparticipant, $typeparticipant, $langs->trans('AgfFichePresByParticipantTitleM'));

} else {
	setEventMessage($agf->error, 'errors');
}

print '</table>' . "\n";
print "\n";


print '</td></tr>' . "\n";
print '<tr><td class="center">';

if (!empty($idstagiaire)){
	$partcipant = '&fk_stagiaire=';
}

if (!empty($idformateur)){
	$partcipant = '&fk_formateur=';
}

if ($action == 'dosign' && empty($cancel)) {
	$urlMainPage = $_SERVER['PHP_SELF'].'?ref='.$ref.'&id='.$id.$partcipant.$idparticipant;
	$signature = new AgefoddSignature($db);
	$signature->getFormSignatureCreneau($toselect, $agf, $idparticipant, $typeparticipant);
}

print '</td></tr>' . "\n";
print '</table>' . "\n";
print '</form>' . "\n";
print '</div>' . "\n";
print '<br>';

if (version_compare(DOL_VERSION, '18.0.0', '<'))
{
    htmlPrintOnlinePaymentFooter($mysoc, $langs);
}
else
{
    htmlPrintOnlineFooter($mysoc, $langs);
}

llxFooter('', 'public');

$db->close();
