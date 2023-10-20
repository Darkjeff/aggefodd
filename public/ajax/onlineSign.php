<?php
/*
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
 *	\file       /htdocs/core/ajax/onlineSign.php
 *	\brief      File to make Ajax action on Knowledge Management
 */

if (!defined('NOTOKENRENEWAL')) {
	define('NOTOKENRENEWAL', '1'); // Disables token renewal
}
if (!defined('NOREQUIREHTML')) {
	define('NOREQUIREHTML', '1');
}
if (!defined('NOREQUIREAJAX')) {
	define('NOREQUIREAJAX', '1');
}
// Needed for create other object with workflow
/*if (!defined('NOREQUIRESOC')) {
	define('NOREQUIRESOC', '1');
}*/
if (!defined('NOCSRFCHECK')) {
	define('NOCSRFCHECK', '1');
}
// Do not check anti CSRF attack test
if (!defined('NOREQUIREMENU')) {
	define('NOREQUIREMENU', '1');
}
// If there is no need to load and show top and left menu
if (!defined("NOLOGIN")) {
	define("NOLOGIN", '1');
}
if (!defined('NOIPCHECK')) {
	define('NOIPCHECK', '1'); // Do not check IP defined into conf $dolibarr_main_restrict_ip
}
if (!defined('NOBROWSERNOTIF')) {
	define('NOBROWSERNOTIF', '1');
}

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--; $j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res && file_exists('../../../../main.inc.php')) {
	$res = @include '../../../../main.inc.php';
}
if (!$res) {
	die("Include of main fails");
}
require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/functions.lib.php';
require_once __DIR__ . '/../../class/agefodd_signature.class.php';

$action = GETPOST('action', 'aZ09');

$signature = GETPOST('signaturebase64');
//$ref = GETPOST('ref', 'aZ09');
$fk_session = GETPOST('fk_session', 'int');
$fk_person = GETPOST('fk_person', 'int');
$person_type = GETPOST('person_type', 'alpha');
$TCreneauxToSign = GETPOST('TCreneauxToSign', 'alpha');
//$mode = GETPOST('mode', 'aZ09');
$entity = GETPOSTINT('entity');
$SECUREKEY = GETPOST('securekey', 'alpha'); // Secure key

$error = $nb_creneaux_created = $nb_signatures_creneaux_already_exist = 0;
$response = "";

//$type = $mode;

// Check securitykey
//$securekeyseed = '';
$securekeyseed = getDolGlobalString('AGEFODD_ONLINE_SIGNATURE_SECURITY_TOKEN');

if (empty($SECUREKEY) || !dol_verifyHash($securekeyseed.$fk_session.$fk_person.$person_type.(empty($conf->multicompany->enabled) ? '' : $entity), $SECUREKEY, '0')) {
	http_response_code(403);
	print 'Bad value for securitykey. Value provided '.dol_escape_htmltag($SECUREKEY).' does not match expected value for this person and this slot';
	exit(-1);
}


/*
 * Actions
 */

if ($action == "importSignature") {
	if (!empty($signature) && $signature[0] == "image/png;base64" && !empty($TCreneauxToSign)) {
			$TabCrenaeuxToSign = explode(',', $TCreneauxToSign);
			$signature = $signature[1];
			$data = base64_decode($signature);

			require_once DOL_DOCUMENT_ROOT . '/core/lib/pdf.lib.php';

			$db->begin();
			if (is_array($TabCrenaeuxToSign)){
				foreach ($TabCrenaeuxToSign as $id_creneau) {

					// Création de la ligne dans la table llx_agefodd_session_trainee_path_img_signature_calendrier
					if (!$error) {

						$signature = new AgefoddSignature($db);
						$res_array = $signature->fetchAll('', '', 0, 0, ['customsql' => ' fk_calendrier = ' . ((int)$id_creneau) . ' AND fk_person = ' . ((int)$fk_person) . ' AND person_type = "' . $db->escape($person_type) . '" ']);

						if (!empty($res_array)) { // La signature existe déjà

							$nb_signatures_creneaux_already_exist++;

						} else { // On crée la signature dans la table

							// Création du fichier image de la signature
							$resfile = $signature->createSignatureFile($data, $fk_session, $id_creneau, $fk_person, $person_type);

							if ($resfile <= 0) {
								$error++;
								$response = implode(',', $signature->errors);
								break;
							}

							$online_sign_ip = getUserRemoteIP();
							$online_sign_name = '';        // TODO Ask name on form to sign
							$TBrowserInfos = getBrowserInfo($_SERVER['HTTP_USER_AGENT']);

							$signature->dates = $signature->datec = dol_now();
							$signature->fk_session = $fk_session;
							$signature->fk_calendrier = $id_creneau;
							$signature->navigateur = $TBrowserInfos['browsername'];
							$signature->ip = $online_sign_ip;
							$signature->fk_person = $fk_person;
							$signature->person_type = $person_type;
							$signature->entity = $entity;

							if ($signature->create($user) <= 0) {
								$error++;
								$response = 'error sql';
								break;
							} else {

								$nb_creneaux_created++;
							}
						}

					}
				}
			}
	} else {
		$error++;
		$response = 'error signature_not_found';
	}
}

if ($error) {
	$db->rollback();
	http_response_code(501);
} else {
	$db->commit();
	$langs->load('agefodd@agefodd');
	if($nb_creneaux_created > 0) {
		setEventMessage($langs->trans('CreneauxSignatureOK', $nb_creneaux_created));
	}
	if($nb_signatures_creneaux_already_exist > 0) {
		setEventMessage($langs->trans('CreneauxSignatureAlreadySigned', $nb_signatures_creneaux_already_exist), 'warnings');
	}
	$response = "success";
}

echo $response;
