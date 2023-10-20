<?php
/*
 * Copyright (C) 2009-2010	Erick Bullier	<eb.dev@ebiconsulting.fr>
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 */

/**
 */
$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");

require_once ('../class/agsession.class.php');
require_once ('../class/agefodd_formation_catalogue.class.php');
require_once('../class/agefodd_session_catalogue.class.php');
require_once ('../class/agefodd_stagiaire_certif.class.php');
require_once (DOL_DOCUMENT_ROOT . '/contact/class/contact.class.php');
require_once ('../class/html.formagefodd.class.php');
require_once ('../lib/agefodd.lib.php');
require_once ('../class/agefodd_session_stagiaire.class.php');
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/doleditor.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';

$newToken = function_exists('newToken') ? newToken() : $_SESSION['newtoken'];

// Security check
if (! $user->rights->agefodd->lire)
	accessforbidden();

$action = GETPOST('action', 'alpha');
$id = GETPOST('id', 'int');
$confirm = GETPOST('confirm', 'alpha');
$objpedamodif = GETPOST('objpedamodif', 'int');
$objc = GETPOST('objc', 'int');
$qr_code_info = GETPOST('qr_code_info', 'alpha');

$ref = GETPOST('ref', 'alpha');
$ref_obj = GETPOST('ref_obj', 'alpha');
$ref_interne = GETPOST('ref_interne', 'alpha');
$intitule = GETPOST('intitule', 'alpha');
$duree = GETPOST('duree', 'alpha');
$note_private = GETPOST('note_private', 'restricthtml');
$note_public = GETPOST('note_public', 'restricthtml');
$fk_formation_catalogue = GETPOST('fk_formation_catalogue', 'int');
$priorite = GETPOST('priorite', 'alpha');
$lines = GETPOST('lines', 'int');
$trainers = GETPOST('trainers', 'alpha');
$accessHandicap = GETPOSTISSET('AccessHandicap', 'int');

$fk_session = GETPOST('fk_session', 'int');
$fk_product = GETPOST('fk_product', 'int');

if (! empty($conf->global->AGF_FCKEDITOR_ENABLE_TRAINING)) {
	$public = dol_htmlcleanlastbr(GETPOST('public', 'none'));
	$methode = dol_htmlcleanlastbr(GETPOST('methode', 'none'));
	$note1 = dol_htmlcleanlastbr(GETPOST('note1', 'none'));
	$note2 = dol_htmlcleanlastbr(GETPOST('note2', 'none'));
	$prerequis = dol_htmlcleanlastbr(GETPOST('prerequis', 'none'));
	$but = dol_htmlcleanlastbr(GETPOST('but', 'none'));
	$programme = dol_htmlcleanlastbr(GETPOST('programme', 'none'));
	$pedago_usage = dol_htmlcleanlastbr(GETPOST('pedago_usage', 'none'));
	$sanction = dol_htmlcleanlastbr(GETPOST('sanction', 'none'));
} else {
	$public = GETPOST('public', 'alpha');
	$methode = GETPOST('methode', 'alpha');
	$note1 = GETPOST('note1', 'alpha');
	$note2 = GETPOST('note2', 'alpha');
	$prerequis = GETPOST('prerequis', 'alpha');
	$but = GETPOST('but', 'alpha');
	$programme = GETPOST('programme', 'alpha');
	$pedago_usage = GETPOST('pedago_usage', 'alpha');
	$sanction = GETPOST('sanction', 'alpha');
}

$cloneDisplayed = false;

$agsession = new Agsession($db);
$sessionCatalogue = new SessionCatalogue($db);

if (!empty($id))
{
	$res = $agsession->fetch($id);
	if(!$res)
		dol_print_error($db);
	else
	{
		$res = $sessionCatalogue->fetchSessionCatalogue($id);
		if ($res > 0) $cloneDisplayed = true;
		else if ($res < 0) setEventMessage($sessionCatalogue->error, 'errors');

		$extrafields = new ExtraFields($db);
		$extralabels = $extrafields->fetch_name_optionals_label($sessionCatalogue->table_element);
        if(floatval(DOL_VERSION) >= 17) {
            $extrafields->attribute_type = $extrafields->attributes[$sessionCatalogue->table_element]['type'];
            $extrafields->attribute_size = $extrafields->attributes[$sessionCatalogue->table_element]['size'];
            $extrafields->attribute_unique = $extrafields->attributes[$sessionCatalogue->table_element]['unique'];
            $extrafields->attribute_required = $extrafields->attributes[$sessionCatalogue->table_element]['required'];
            $extrafields->attribute_label = $extrafields->attributes[$sessionCatalogue->table_element]['label'];
        }
	}
}

$hookmanager->initHooks(array('sessionCatalogueCard'));

$parameters = array('session' => $agsession);
// Note that $action and $object may be modified by some hooks
$reshook = $hookmanager->executeHooks('doActions', $parameters, $sessionCatalogue, $action);
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook)){

	$error = 0;

	/*
	 * Action update (fiche de formation)
	 */
	if ($action == 'update' && $user->rights->agefodd->agefodd_formation_catalogue->creer) {
		if (! $_POST["cancel"]) {

			$sessionCatalogue->ref = $ref;
			$sessionCatalogue->ref_interne = $ref_interne;
			$sessionCatalogue->intitule = $intitule;
			$sessionCatalogue->fk_product = $fk_product;
			$sessionCatalogue->qr_code_info = $qr_code_info;

			if (! empty($conf->global->AGF_MANAGE_CERTIF)) {
				$certif_year = GETPOST('certif_year', 'int');
				$certif_month = GETPOST('certif_month', 'int');
				$certif_day = GETPOST('certif_day', 'int');
				$sessionCatalogue->certif_duration = $certif_year . ':' . $certif_month . ':' . $certif_day;
			}

			if(empty($sessionCatalogue->id))
			{
				/* Il n'y a pas de clone, on le crée avec les informations nouvellement saisies dans le formulaire de modification
				   On crée également les objectifs pédagogiques issus du recueil standard */
				$formation = new Formation($db);
				$res = $formation->fetch($agsession->fk_formation_catalogue);

				$sessionCatalogue->ref_obj = $ref_obj;
				$sessionCatalogue->public = $public;
				$sessionCatalogue->methode = $methode;
				$sessionCatalogue->prerequis = $prerequis;
				$sessionCatalogue->but = $but;
				$sessionCatalogue->duree = $duree;
				$sessionCatalogue->programme = $programme;
				$sessionCatalogue->pedago_usage = $pedago_usage;
				$sessionCatalogue->sanction = $sanction;
				$sessionCatalogue->note1 = $note1;
				$sessionCatalogue->note2 = $note2;
				$sessionCatalogue->note_private = $note_private;
				$sessionCatalogue->note_public = $note_public;
				$sessionCatalogue->fk_formation_catalogue = $formation->id;
				$sessionCatalogue->priorite = $priorite;
				$sessionCatalogue->lines = $lines;
				$sessionCatalogue->trainers = $trainers;
				$sessionCatalogue->accessibility_handicap = $accessHandicap ? '1' : '0';
				$sessionCatalogue->fk_session = $id;

				$extrafields->setOptionalsFromPost($extralabels, $sessionCatalogue);

				$resClone = $sessionCatalogue->create($user);

				/* Récupère les objectifs pédagogiques du recueil standard dans llx_agefodd_formation_objectifs_peda
 				   et qui les copie dans la base llx_agefodd_session_catalogue_objectifs_peda ligne par ligne */
				$resultCloneObjPeda = $sessionCatalogue->cloneObjPeda();

				if($resClone > 0 && $resultCloneObjPeda > 0) $action = 'view';
				else
				{
					setEventMessage($langs->trans('AgfErrorCatalogueCloneCreation'), 'error');
					$error++;
				}
			}
			else if ($sessionCatalogue->id > 0)
			{
				// Il y a deja un clone, on le met à jour

				$formation = new Formation($db);
				$res = $formation->fetch($agsession->fk_formation_catalogue);

				$sessionCatalogue->public = $public;
				$sessionCatalogue->methode = $methode;
				$sessionCatalogue->duree = $duree;
				$sessionCatalogue->note1 = $note1;
				$sessionCatalogue->note2 = $note2;
				$sessionCatalogue->prerequis = $prerequis;
				$sessionCatalogue->but = $but;
				$sessionCatalogue->programme = $programme;
				$sessionCatalogue->pedago_usage = $pedago_usage;
				$sessionCatalogue->sanction = $sanction;
				$sessionCatalogue->accessibility_handicap = $accessHandicap ? '1' : '0';
				$extrafields->setOptionalsFromPost($extralabels, $sessionCatalogue);

				$resUpdate = $sessionCatalogue->update($user);
				if($resUpdate > 0) $action = 'view';
				else
				{
					$error++;
				}
			}

			if (empty($error)) {
				Header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $id);
				exit();
			}

		} else {
			Header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $id);
			exit();
		}
	}

	/*
	 * Action ajax_obj_update (objectif pedagogique)
	 */
	if ($action == "ajax_obj_update" && $user->rights->agefodd->agefodd_formation_catalogue->creer) {
		$newObjectifs = GETPOST('pedago', 'array');

		if (empty($sessionCatalogue->id)) // pas de clone trouvé
		{
			$formation = new Formation($db);
			$res = $formation->fetch($agsession->fk_formation_catalogue);

			if (empty($ref)) $sessionCatalogue->ref = $formation->ref;

			$sessionCatalogue->fk_product = $formation->fk_product;
			$sessionCatalogue->qr_code_info = $formation->qr_code_info;
			$sessionCatalogue->ref_obj = $formation->ref_obj;
			$sessionCatalogue->ref_interne = $formation->ref_interne;
			$sessionCatalogue->intitule = $formation->intitule;
			$sessionCatalogue->public = $formation->public;
			$sessionCatalogue->duree = $formation->duree;
			$sessionCatalogue->methode = $formation->methode;
			$sessionCatalogue->prerequis = $formation->prerequis;
			$sessionCatalogue->but = $formation->but;
			$sessionCatalogue->programme = $formation->programme;
			$sessionCatalogue->pedago_usage = $formation->pedago_usage;
			$sessionCatalogue->sanction = $formation->sanction;
			$sessionCatalogue->note1 = $formation->note1;
			$sessionCatalogue->note2 = $formation->note2;
			$sessionCatalogue->note_private = $formation->note_private;
			$sessionCatalogue->note_public = $formation->note_public;
			$sessionCatalogue->fk_formation_catalogue = $formation->id;
			$sessionCatalogue->priorite = $formation->priorite;
			$sessionCatalogue->certif_duration = $formation->certif_duration;
			$sessionCatalogue->lines = $formation->lines;
			$sessionCatalogue->trainers = $formation->trainers;
			$sessionCatalogue->accessibility_handicap = $formation->accessibility_handicap;
			$sessionCatalogue->fk_session = $id;
			$sessionCatalogue->array_options = $formation->array_options;

			$idClone = $sessionCatalogue->create($user);
		}

		$sessionCatalogue->remove_objpeda($sessionCatalogue->id);

		if (!empty($newObjectifs))
		{
			foreach ($newObjectifs as $objectif)
			{
				$sessionCatalogue->intitule = $objectif['intitule'];
				$sessionCatalogue->priorite = (int)$objectif['priorite'];

				$result = $sessionCatalogue->create_objpeda($user);
			}
		}

		Header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $id);
                exit();
	}

	/*
	 * Action generate fiche pédagogique
	 */
	if ($action == 'fichepeda' && $user->rights->agefodd->agefodd_formation_catalogue->creer) {

		$result = $sessionCatalogue->generatePDAByLink();

		if($result <= 0){
			$outputlangs = $langs;
			$newlang = GETPOST('lang_id', 'alpha');
			if ($conf->global->MAIN_MULTILANGS && empty($newlang))
				$newlang = $object->thirdparty->default_lang;
			if (! empty($newlang)) {
				$outputlangs = new Translate("", $conf);
				$outputlangs->setDefaultLang($newlang);
			}
			$addRecueil = $cloneDisplayed ? 'recueil_'.$id : $agsession->fk_formation_catalogue;
			$file = 'fiche_pedago_'. $addRecueil . '.pdf';
			$model = 'fiche_pedago';

			// this configuration variable is designed like
			// standard_model_name:new_model_name&standard_model_name:new_model_name&....
			if (! empty($conf->global->AGF_PDF_MODEL_OVERRIDE)) {
				$modelarray = explode('&', $conf->global->AGF_PDF_MODEL_OVERRIDE);
				if (is_array($modelarray) && count($modelarray) > 0) {
					foreach ( $modelarray as $modeloveride ) {
						$modeloverridearray = explode(':', $modeloveride);
						if (is_array($modeloverridearray) && count($modeloverridearray) > 0) {
							if ($modeloverridearray[0] == $model) {
								$model = $modeloverridearray[1];
							}
						}
					}
				}
			}

			$result = agf_pdf_create($db, $id, '', $model, $outputlangs, $file, 0);
		}
		if ($result > 0) {
			Header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $id);
			exit();
		} else {
			setEventMessage($sessionCatalogue->error, 'errors');
		}
	}

	// on ne prend pas en compte la modification des modules de formation pour l'instant
	/*if ($action == 'fichepedamodule' && $user->rights->agefodd->agefodd_formation_catalogue->creer) {
		// Define output language
		$outputlangs = $langs;
		$newlang = GETPOST('lang_id', 'alpha');
		if ($conf->global->MAIN_MULTILANGS && empty($newlang))
			$newlang = $object->thirdparty->default_lang;
		if (! empty($newlang)) {
			$outputlangs = new Translate("", $conf);
			$outputlangs->setDefaultLang($newlang);
		}
		$model = 'fiche_pedago_modules';
		$file = $model . '_recueil_' . $id . '.pdf';

		// this configuration variable is designed like
		// standard_model_name:new_model_name&standard_model_name:new_model_name&....
		if (! empty($conf->global->AGF_PDF_MODEL_OVERRIDE)) {
			$modelarray = explode('&', $conf->global->AGF_PDF_MODEL_OVERRIDE);
			if (is_array($modelarray) && count($modelarray) > 0) {
				foreach ( $modelarray as $modeloveride ) {
					$modeloverridearray = explode(':', $modeloveride);
					if (is_array($modeloverridearray) && count($modeloverridearray) > 0) {
						if ($modeloverridearray[0] == $model) {
							$model = $modeloverridearray[1];
						}
					}
				}
			}
		}

		$result = agf_pdf_create($db, $id, '', $model, $outputlangs, $file, 0);

		if ($result > 0) {
			Header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $id);
			exit();
		} else {
			setEventMessage($sessionCatalogue->error, 'errors');
		}
	}*/
	// Delete file
	if ($action == 'remove_file' && $user->rights->agefodd->agefodd_formation_catalogue->supprimer)
	{
		require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

		if (empty($sessionCatalogue->id) || ! $sessionCatalogue->id > 0) {
			// Reload to get all modified line records and be ready for hooks
			$ret = $sessionCatalogue->fetchSessionCatalogue($id);
		}

		$langs->load('other');
		$filetodelete = GETPOST('file','alpha');
		$file =	$conf->agefodd->dir_output	. '/' .	$filetodelete;
		$ret = dol_delete_file($file,0,0,0, $sessionCatalogue);
		if ($ret) setEventMessages($langs->trans('FileWasRemoved', $filetodelete), null, 'mesgs');
		else setEventMessages($langs->trans('ErrorFailToDeleteFile', $filetodelete), null, 'errors');

		// Make a redirect to avoid to keep the remove_file into the url that create side effects
		header('Location: ' . $_SERVER['PHP_SELF'] . '?id=' . $id);
		exit();
	}


	// Resyncronize from standard catalogue
	if ($action == 'reset_from_standard_catalogue')
	{
		/*
		 * Explication: cette action sert à récupérer les informations du recueil standard
		 * En supprimant le clone, le système passera dans la condition "pas de clone" et va donc
		 * afficher les informations du recueil standard
		*/

		$idClone = $sessionCatalogue->fetchSessionCatalogue($id);
		if($idClone == -1)
			dol_print_error($db);

		//On supprime les champs du clone
		$resRemoveCatalogueClone = $sessionCatalogue->remove($idClone);
		if($resRemoveCatalogueClone == -1)
			dol_print_error($db);

		//On supprime les objectifs pédagogiques du clone
		$resRemoveObjPedaClone = $sessionCatalogue->remove_objpeda($sessionCatalogue->id);
		if($resRemoveObjPedaClone == -1)
			dol_print_error($db);

		if ($cloneDisplayed) $filetodelete = 'fiche_pedago_recueil_'.$agsession->id.'.pdf';
		$file =	$conf->agefodd->dir_output	. '/' .	$filetodelete;
		$ret = dol_delete_file($file,0,0,0, $sessionCatalogue);

		// Make a redirect to avoid to keep the remove_file into the url that create side effects
		header('Location: ' . $_SERVER['PHP_SELF'] . '?id=' . $id);
		exit();
	}
}



/*
 * View
*/

llxHeader('', $langs->trans("AgfCatalogue"));

$form = new Form($db);
$formAgefodd = new FormAgefodd($db);

if (! empty($id)) {

	if (empty($sessionCatalogue->id))
	{
		//J'ai pas de clone
		$sessionCatalogue = new Formation($db);
		$sessionCatalogue->fetch($agsession->fk_formation_catalogue);
	}

	$extrafields = new ExtraFields($db);
	$extralabels = $extrafields->fetch_name_optionals_label($sessionCatalogue->table_element);
    if(floatval(DOL_VERSION) >= 17) {
        $extrafields->attribute_type = $extrafields->attributes[$sessionCatalogue->table_element]['type'];
        $extrafields->attribute_size = $extrafields->attributes[$sessionCatalogue->table_element]['size'];
        $extrafields->attribute_unique = $extrafields->attributes[$sessionCatalogue->table_element]['unique'];
        $extrafields->attribute_required = $extrafields->attributes[$sessionCatalogue->table_element]['required'];
        $extrafields->attribute_label = $extrafields->attributes[$sessionCatalogue->table_element]['label'];
    }
	$head = session_prepare_head($agsession);

	dol_fiche_head($head, 'catalogue', $langs->trans("AgfSessionDetail"), 0, 'group');

	dol_agefodd_banner_tab($agsession, 'id');

	$formagefodd = new FormAgefodd($db);
	$formother = new FormOther($db);

	// View session_catalogue card
	if (! empty($id)) {

			$objectifPedagogique = new SessionCatalogue($db);

			//On teste l'existence d'un clone pour fetcher: soit la formation, soit la session
			$iscloned = $objectifPedagogique->isCloned('', '', '', '', '', array(' AND sc.fk_session = '.$id));
			if(empty($iscloned))
				$result_peda = $objectifPedagogique->fetch_objpeda_per_formation($agsession->fk_formation_catalogue);
			else if ($iscloned > 0)
				$result_peda = $objectifPedagogique->fetch_objpeda_per_session_catalogue($objectifPedagogique->session_catalogue[0]->id);

			if ($result_peda < 0)
				setEventMessage($objectifPedagogique->error, 'errors');

			// Affichage en mode "édition"
			if ($action == 'edit') {

				if ($objpedamodif == 1) {
					print '<script type="text/javascript">
				jQuery(document).ready(function () {
					jQuery(function() {' . "\n";
					if (! empty($objc)) {
						print '		$(\'html, body\').animate({scrollTop: $("#priorite_new").offset().top}, 500,\'easeInOutCubic\');' . "\n";
					} else {
						print '		$(\'html, body\').animate({scrollTop: $("#obj_peda").offset().top}, 500,\'easeInOutCubic\');' . "\n";
					}
					print '	});
				});
				</script> ';
				}

				print '<form name="update" action="' . $_SERVER['PHP_SELF'] . '" method="POST">' . "\n";
				print '<input type="hidden" name="token" value="' . $newToken . '">';
				print '<input type="hidden" name="action" value="update">';
				print '<input type="hidden" name="id" value="' . $id . '">';
				print '<input type="hidden" name="intitule" value="' . dol_htmlentities($sessionCatalogue->intitule, ENT_QUOTES) . '">';
				print '<input type="hidden" name="duree" value="' . $sessionCatalogue->duree . '">';
				print '<input type="hidden" name="fk_product" value="' . $sessionCatalogue->fk_product . '">';

				print '<table class="border" width="100%">';

				if (! empty($conf->global->AGF_MANAGE_CERTIF)) {
					print '<tr><td width="20%">' . $langs->trans("AgfCertificateDuration") . '</td><td>';
					print $formagefodd->select_duration_agf($sessionCatalogue->certif_duration, 'certif');
					print '</td></tr>';
				}

				if ($user->admin)
					print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"), 1);
				print "</td></tr>";

				print '<tr>';
				print '<td valign="top">' . $langs->trans("AgfPublic") . '</td><td>';
				$doleditor = new DolEditor('public', $sessionCatalogue->public, '', 160, 'dolibarr_notes', 'In', true, false, $conf->global->AGF_FCKEDITOR_ENABLE_TRAINING, 4, 90);
				$doleditor->Create();
				print '</td></tr>';

				print '<tr><td valign="top">' . $langs->trans("AgfMethode") . '</td><td>';
				$doleditor = new DolEditor('methode', $sessionCatalogue->methode, '', 160, 'dolibarr_notes', 'In', true, false, $conf->global->AGF_FCKEDITOR_ENABLE_TRAINING, 4, 90);
				$doleditor->Create();
				print '</td></tr>';

				print '<tr><td valign="top">' . $langs->trans("AgfDocNeeded") . '</td><td>';
				$doleditor = new DolEditor('note1', $sessionCatalogue->note1, '', 160, 'dolibarr_notes', 'In', true, false, $conf->global->AGF_FCKEDITOR_ENABLE_TRAINING, 4, 90);
				$doleditor->Create();
				print '</td></tr>';

				print '<tr><td valign="top">' . $langs->trans("AgfEquiNeeded") . '</td><td>';
				$doleditor = new DolEditor('note2', $sessionCatalogue->note2, '', 160, 'dolibarr_notes', 'In', true, false, $conf->global->AGF_FCKEDITOR_ENABLE_TRAINING, 4, 90);
				$doleditor->Create();
				print '</td></tr>';

				print '<tr><td valign="top">' . $langs->trans("AgfPrerequis") . '</td><td>';
				$doleditor = new DolEditor('prerequis', $sessionCatalogue->prerequis, '', 160, 'dolibarr_notes', 'In', true, false, $conf->global->AGF_FCKEDITOR_ENABLE_TRAINING, 4, 90);
				$doleditor->Create();
				print '</td></tr>';

				print '<tr><td valign="top">' . $langs->trans("AgfBut") . '</td><td>';
				$doleditor = new DolEditor('but', $sessionCatalogue->but, '', 160, 'dolibarr_notes', 'In', true, false, $conf->global->AGF_FCKEDITOR_ENABLE_TRAINING, 4, 90);
				$doleditor->Create();
				print '</td></tr>';

				print '<tr><td valign="top">' . $langs->trans("AgfProgramme") . '</td><td colspan=3>';
				$doleditor = new DolEditor('programme', $sessionCatalogue->programme, '', 160, 'dolibarr_notes', 'In', true, false, $conf->global->AGF_FCKEDITOR_ENABLE_TRAINING, 4, 90);
				$doleditor->Create();
				print "</td></tr>";

				print '<tr><td valign="top">' . $langs->trans("AgfPedagoUsage") . '</td><td>';
				$doleditor = new DolEditor('pedago_usage', $sessionCatalogue->pedago_usage, '', 160, 'dolibarr_notes', 'In', true, false, $conf->global->AGF_FCKEDITOR_ENABLE_TRAINING, 4, 90);
				$doleditor->Create();
				print "</td></tr>";

				print '<tr><td valign="top">' . $langs->trans("AgfSanction") . '</td><td>';
				$doleditor = new DolEditor('sanction', $sessionCatalogue->sanction, '', 160, 'dolibarr_notes', 'In', true, false, $conf->global->AGF_FCKEDITOR_ENABLE_TRAINING, 4, 90);
				$doleditor->Create();
				print "</td></tr>";

				if ($conf->global->AGF_MANAGE_CERTIF) {
					print '<tr><td valign="top">' . $langs->trans("AgfQRCodeCertifInfo") . '</td><td>';
					print '<input name="qr_code_info" class="flat" size="50" value="' . $sessionCatalogue->qr_code_info . '"></td></tr>';
				}

				print '<tr><td valign="top">' . $langs->trans("AccessHandicap") . '</td>';
				$checked = $sessionCatalogue->accessibility_handicap == 1 ? "checked" : "";
				print '<td><input type="checkbox" id="AccessHandicap" name="AccessHandicap" '. $checked . ' /></td></tr>';

				if (! empty($extrafields->attribute_label)) {
					print $sessionCatalogue->showOptionals($extrafields, 'edit');
				}

				print '</table>';
				print '</div>';

				print '<table style=noborder align="right">';
				print '<tr><td align="center" colspan=2>';
				print '<input type="submit" class="butAction" value="' . $langs->trans("Save") . '"> &nbsp; ';
				print '<input type="submit" name="cancel" class="butActionDelete" value="' . $langs->trans("Cancel") . '">';
				print '</td></tr>';

				print '</table>';
				print '</form>';

			} else {
				/*
				 * Display
				 */

				print '<div class="underbanner clearboth"></div>';

				// confirm delete
				if ($action == 'delete') {
					print $form->formconfirm($_SERVER['PHP_SELF'] . "?id=" . $id, $langs->trans("AgfDeleteOps"), $langs->trans("AgfConfirmDeleteOps"), "confirm_delete", '', '', 1);
				}

				// Confirm clone
				if ($action == 'clone') {
					$formquestion = '';

					if (!empty($conf->global->AGF_USE_TRAINING_MODULE)) {
						$formquestion = array('text' => $langs->trans("ConfirmClone"));
						$formquestion[] = array(
							'type' => 'checkbox',
							'name' => 'clone_training_modules',
							'label' => $langs->trans("AgfCloneTrainingModules"),
							'value' => 0
						);
					}

					print $form->formconfirm($_SERVER['PHP_SELF'] . "?id=" . $id, $langs->trans("CloneTraining"), $langs->trans("ConfirmCloneTraining"), "confirm_clone", $formquestion, '', 1);
				}

				// confirm archive
				if ($action == 'archive' || $action == 'active') {
					if ($action == 'archive')
						$value = 1;
					if ($action == 'active')
						$value = 0;

					print $form->formconfirm($_SERVER['PHP_SELF'] . "?arch=" . $value . "&id=" . $id, $langs->trans("AgfFormationArchiveChange"), $langs->trans("AgfConfirmArchiveChange"), "arch_confirm_delete", '', '', 1);
				}

				print '<table class="border" width="100%">';

				if (! empty($conf->global->AGF_MANAGE_CERTIF)) {
					print '<tr><td width="20%">' . $langs->trans("AgfCertificateDuration") . '</td><td>';
					if (! empty($sessionCatalogue->certif_duration)) {
						$duration_array = explode(':', $sessionCatalogue->certif_duration);
						$year = $duration_array[0];
						$month = $duration_array[1];
						$day = $duration_array[2];
					} else {
						$year = $month = $day = 0;
					}

					print $year . ' ' . $langs->trans('Year') . '(s) ' . $month . ' ' . $langs->trans('Month') . '(s) ' . $day . ' ' . $langs->trans('Day') . '(s)';
					print '</td></tr>';
				}

				print '<tr><td valign="top">' . $langs->trans("AgfPublic") . '</td><td colspan=2>';
				if (! empty($conf->global->AGF_FCKEDITOR_ENABLE_TRAINING)) {
					print $sessionCatalogue->public;
				} else {
					print stripslashes(nl2br($sessionCatalogue->public));
				}
				print '</td></tr>';

				print '<tr><td valign="top">' . $langs->trans("AgfMethode") . '</td><td colspan=2>';
				if (! empty($conf->global->AGF_FCKEDITOR_ENABLE_TRAINING)) {
					print $sessionCatalogue->methode;
				} else {
					print stripslashes(nl2br($sessionCatalogue->methode));
				}
				print '</td></tr>';

				print '<tr><td valign="top">' . $langs->trans("AgfDocNeeded") . '</td><td colspan=2>';
				if (! empty($conf->global->AGF_FCKEDITOR_ENABLE_TRAINING)) {
					print $sessionCatalogue->note1;
				} else {
					print stripslashes(nl2br($sessionCatalogue->note1));
				}
				print '</td></tr>';

				print '<tr><td valign="top">' . $langs->trans("AgfEquiNeeded") . '</td><td colspan=2>';
				if (! empty($conf->global->AGF_FCKEDITOR_ENABLE_TRAINING)) {
					print $sessionCatalogue->note2;
				} else {
					print stripslashes(nl2br($sessionCatalogue->note2));
				}
				print '</td></tr>';

				print '<tr><td valign="top">' . $langs->trans("AgfPrerequis") . '</td><td colspan=2>';
				if (! empty($conf->global->AGF_FCKEDITOR_ENABLE_TRAINING)) {
					$prerequis = $sessionCatalogue->prerequis;
				} else {
					$prerequis = stripslashes(nl2br($sessionCatalogue->prerequis));
				}
				if (empty($sessionCatalogue->prerequis))
					$prerequis = $langs->trans("AgfUndefinedPrerequis");
				print stripslashes($prerequis) . '</td></tr>';

				print '<tr><td valign="top">' . $langs->trans("AgfBut") . '</td><td colspan=2>';
				if (! empty($conf->global->AGF_FCKEDITOR_ENABLE_TRAINING)) {
					$but = $sessionCatalogue->but;
				} else {
					$but = stripslashes(nl2br($sessionCatalogue->but));
				}
				if (empty($sessionCatalogue->but))
					$but = $langs->trans("AgfUndefinedBut");
				print $but . '</td></tr>';

				print '<tr><td valign="top">' . $langs->trans("AgfPedagoUsage") . '</td><td colspan=2>';
				if (! empty($conf->global->AGF_FCKEDITOR_ENABLE_TRAINING)) {
					$but = $sessionCatalogue->pedago_usage;
				} else {
					$but = stripslashes(nl2br($sessionCatalogue->pedago_usage));
				}
				print $but . '</td></tr>';

				print '<tr><td valign="top">' . $langs->trans("AgfSanction") . '</td><td colspan=2>';
				if (! empty($conf->global->AGF_FCKEDITOR_ENABLE_TRAINING)) {
					$but = $sessionCatalogue->sanction;
				} else {
					$but = stripslashes(nl2br($sessionCatalogue->sanction));
				}
				print $but . '</td></tr>';

				if ($conf->global->AGF_MANAGE_CERTIF) {
					print '<tr><td>' . $langs->trans("AgfQRCodeCertifInfo") . '</td><td colspan=2>';
					if (!empty($sessionCatalogue->qr_code_info)) {
						dol_include_once('/agefodd/class/tcpdfbarcode_agefodd.modules.php');
						$qr_code = new modTcpdfbarcode_agefood;
						$qr_code->is2d=true;
						$result=$qr_code->writeBarCode($sessionCatalogue->qr_code_info,'QRCODE','Y',1,0,$sessionCatalogue->id);
						// Generate on the fly and output barcode with generator
						$url=DOL_URL_ROOT.'/viewimage.php?modulepart=barcode&amp;generator=tcpdfbarcode&amp;code='.urlencode($sessionCatalogue->qr_code_info).'&amp;encoding=QRCODE';
						//print $url;
						print '<img src="'.$url.'" title="'.$sessionCatalogue->qr_code_info.'" border="0">';
					}
					print '</td></tr>';
				}

				// view Access Handicap
				print '<tr><td valign="top">' . $langs->trans("AccessHandicap") . '</td>';
				$checked = $sessionCatalogue->accessibility_handicap == 1 ? "checked" : "";
				print '<td><input type="checkbox" id="AccessHandicap" name="AccessHandicap" '.$checked.' disabled /></td></tr>';

				if (! empty($extrafields->attribute_label)) {
					print $sessionCatalogue->showOptionals($extrafields);
				}

				print '<script type="text/javascript">' . "\n";
				print 'function DivStatus( div_){' . "\n";
				print '	var Obj = document.getElementById( div_);' . "\n";
				print '	if( Obj.style.display=="none"){' . "\n";
				print '		Obj.style.display ="block";' . "\n";
				print '	}' . "\n";
				print '	else{' . "\n";
				print '		Obj.style.display="none";' . "\n";
				print '	}' . "\n";
				print '}' . "\n";
				print '</script>' . "\n";

				print '<tr class="liste_titre"><td valign="top">' . $langs->trans("AgfProgramme") . $form->textwithpicto('', $langs->trans("AgfProgrammeHelp"), 1, 'help') . '</td>';
				print '<td align="left" colspan=2>';
				print '<a href="javascript:DivStatus(\'prog\');" title="afficher detail" style="font-size:14px;">+</a></td></tr>';
				if (! empty($conf->global->AGF_FCKEDITOR_ENABLE_TRAINING)) {
					$programme = $sessionCatalogue->programme;
				} else {
					$programme = stripslashes(nl2br($sessionCatalogue->programme));
				}
				if (empty($sessionCatalogue->programme))
					$programme = $langs->trans("AgfUndefinedProg");
				print '<tr><td></td><td><div id="prog" style="display:none;">' . $programme . '</div></td></tr>';

				$object_modules = new Agefoddformationcataloguemodules($db);
				$result = $object_modules->fetchAll('ASC', 'sort_order', 0, 0, array(
					't.fk_formation_catalogue' => $id
				));
				if (count($object_modules->lines) > 0) {
					print '<script type="text/javascript">' . "\n";
					print 'function DivStatus( div_){' . "\n";
					print '	var Obj = document.getElementById( div_);' . "\n";
					print '	if( Obj.style.display=="none"){' . "\n";
					print '		Obj.style.display ="block";' . "\n";
					print '	}' . "\n";
					print '	else{' . "\n";
					print '		Obj.style.display="none";' . "\n";
					print '	}' . "\n";
					print '}' . "\n";
					print '</script>' . "\n";

					print '<tr class="liste_titre"><td valign="top">' . $langs->trans("AgfProgrammeModules") . $form->textwithpicto('', $langs->trans("AgfProgrammeModulesHelp"), 1, 'help') . '</td>';
					print '<td align="left" colspan=2>';
					print '<a href="javascript:DivStatus(\'progmod\');" title="afficher detail" style="font-size:14px;">+</a></td></tr>';
					$programme = '';
					foreach ( $object_modules->lines as $line_mod ) {
						$programme .= $line_mod->title . '<br />';
					}
					print '<tr><td></td><td><div id="progmod" style="display:none;">' . $programme . '</div></td></tr>';
				}

				print '</table>';
				print '&nbsp';
				print '<table class="border" width="100%">';
				print '<tr class="liste_titre"><td colspan=3>' . $langs->trans("AgfObjPeda") . '</td></tr>';

				if (!empty($objectifPedagogique->lines))
				{
					foreach ($objectifPedagogique->lines as $line) {

						print '<tr>';
						print '<td width="40" align="center">' . $line->priorite . '</td>';
						print '<td>' . stripslashes($line->intitule) . '</td>';
						print "</tr>\n";
					}
				}

				print "</table>";
				?>
				<script>
					$(document).ready(function() {
						$('#modifyPedago').click(function(e) {
							e.preventDefault();
							listepedago();
						});

						function listepedago(){

							if($('#pedagoModal').length==0) {
								$('body').append('<div id="pedagoModal" title="<?php echo $langs->transnoentities('AgfObjPeda'); ?>"></div>');
							}

							$.ajax({
								url : "<?php echo dol_buildpath('/agefodd/scripts/pedagoajax.php',1); ?>"
								,data:{
									put: 'printformCatalogue'
									,idTraining: '<?php echo $id; ?>'
									,istraining: false

								}
								,method:"post"
								,dataType:'json'
							}).done(function(data) {
								$('#pedagoModal').html(data.form);
							});

							$('#pedagoModal').dialog({
								modal:true,
								width:'50%'
							});

						}
					});
				</script>
				<?php
				print '&nbsp';
				print '<table class="border" width="100%">';
				print '<tr class="liste_titre"><td colspan=3>' . $langs->trans("AgfLinkedDocuments") . '</td></tr>';

				$sessionCatalogue->generatePDAByLink();
				$addRecueil = $cloneDisplayed ? 'recueil_'.$id : $sessionCatalogue->id;
				$filename = 'fiche_pedago_'. $addRecueil . '.pdf';
				$filedir  = $conf->agefodd->dir_output;
				$filepath = $filedir . '/' . $filename;
				if (is_file($filepath)) {
					// afficher
					$legende = $langs->trans("AgfDocOpen");
					print '<tr><td width="200" align="center">' . $langs->trans("AgfFichePedagogique") . '</td><td> ';
					print '<a href="' . DOL_URL_ROOT . '/document.php?modulepart=agefodd&file=' . $filename . '" alt="' . $legende . '" title="' . $legende . '">';
					print img_picto($filename . ':' . $filename, 'pdf2', 'class="valignmiddle"') . '</a>';
					if (function_exists('getAdvancedPreviewUrl')) {
						$urladvanced = getAdvancedPreviewUrl('agefodd', 'fiche_pedago_' . $addRecueil . '.pdf');
						if ($urladvanced) print '&nbsp;&nbsp;<a data-ajax="false" href="'.$urladvanced.'" title="' . $langs->trans("Preview"). '">'.img_picto('', 'detail', 'class="valignmiddle"').'</a>';
					}
					if ($user->rights->agefodd->agefodd_formation_catalogue->supprimer) {
						print '<a href="' . $_SERVER["PHP_SELF"] . "?id=" . $agsession->id . '&amp;action=remove_file&amp;file=' . urlencode($filename) . '">' . img_picto($langs->trans('Delete'), 'delete') . '</a>';
					}
					print '</td></tr>';
				}

				$filename = 'fiche_pedago_modules_' . $id . '.pdf';
				$filedir  = $conf->agefodd->dir_output;
				$filepath = $filedir . '/' . $filename;
				if (is_file($filepath) && (!empty($conf->global->AGF_USE_TRAINING_MODULE))) {
					$legende = $langs->trans("AgfDocOpen");
					print '<tr><td width="200" align="center">' . $langs->trans("AgfFichePedagogiqueModule") . '</td><td> ';
					print '<a href="' . DOL_URL_ROOT . '/document.php?modulepart=agefodd&file=' . $filename . '" alt="' . $legende . '" title="' . $legende . '">';
					print img_picto($filename . ':' . $filename, 'pdf2', 'class="valignmiddle"') . '</a>';
					if (function_exists('getAdvancedPreviewUrl')) {
						$urladvanced = getAdvancedPreviewUrl('agefodd', 'fiche_pedago_modules_' . $id . '.pdf');
						if ($urladvanced) print '&nbsp;&nbsp;<a data-ajax="false" href="'.$urladvanced.'" title="' . $langs->trans("Preview"). '">'.img_picto('', 'detail', 'class="valignmiddle"').'</a>';
					}
					if ($user->rights->agefodd->agefodd_formation_catalogue->supprimer) {
						print '<a href="' . $_SERVER["PHP_SELF"] . "?id=" . $sessionCatalogue->id . '&amp;action=remove_file&amp;file=' . urlencode($filename) . '">' . img_picto($langs->trans('Delete'), 'delete') . '</a>';
					}
					print '</td></tr>';
				}

				print '</table>';

				print '</div>';
			}

	}


	/*
	 * Action tabs
	 *
	 */

	print '<div class="tabsAction">';
	$parameters=array();
	$reshook=$hookmanager->executeHooks('addMoreActionsButtons',$parameters,$sessionCatalogue,$action);    // Note that $action and $object may have been modified by hook
	if (empty($reshook)){

		if ($action != 'create' && $action != 'edit') {

			if ($user->rights->agefodd->agefodd_formation_catalogue->creer) {
				print '<a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?action=edit&id=' . $id . '">' . $langs->trans('Modify') . '</a>';
				print '<a class="butAction" href="#" id="modifyPedago">' . $langs->trans('AgfUpdateObjPeda') . '</a>';
				if ($cloneDisplayed)
					print '<a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?action=reset_from_standard_catalogue&id=' . $id . '">' . $langs->trans('AgfResyncroniseFromStandardCatalogue') . '</a>';
				else
					print '<a class="butActionRefused" href="#" title="' . dol_escape_htmltag($langs->trans("AgfSameAsOriginal")) . '">' . $langs->trans('AgfResyncroniseFromStandardCatalogue') . '</a>';
			} else {
				print '<a class="butActionRefused" href="#" title="' . dol_escape_htmltag($langs->trans("NotAllowed")) . '">' . $langs->trans('Modify') . '</a>';
				print '<a class="butActionRefused" href="#" title="' . dol_escape_htmltag($langs->trans("NotAllowed")) . '">' . $langs->trans('AgfUpdateObjPeda') . '</a>';
				print '<a class="butActionRefused" href="#" title="' . dol_escape_htmltag($langs->trans("NotAllowed")) . '">' . $langs->trans('AgfResyncroniseFromStandardCatalogue') . '</a>';
			}

			if ($user->rights->agefodd->agefodd_formation_catalogue->creer) {
				print '<a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?action=fichepeda&id=' . $id . '">' . $langs->trans('AgfPrintFichePedago') . '</a>';
				/*if (!empty($conf->global->AGF_USE_TRAINING_MODULE)) {
					print '<a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?action=fichepedamodule&id=' . $id . '">' . $langs->trans('AgfPrintFichePedagoModules') . '</a>';
				}*/
			} else {
				print '<a class="butActionRefused" href="#" title="' . dol_escape_htmltag($langs->trans("NotAllowed")) . '">' . $langs->trans('AgfPrintFichePedago') . '</a>';
			}
		}

	}

	print '</div>';
}

llxFooter();
$db->close();
