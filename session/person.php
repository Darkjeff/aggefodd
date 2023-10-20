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
 * \file agefodd/session/person.php
 * \ingroup agefodd
 * \brief trainees of session
 */
$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");

require_once ('../class/agsession.class.php');
require_once ('../class/agefodd_signature.class.php');
require_once (DOL_DOCUMENT_ROOT . '/contact/class/contact.class.php');
require_once ('../class/html.formagefodd.class.php');
require_once ('../lib/agefodd.lib.php');
require_once ('../class/agefodd_session_stagiaire.class.php');
require_once ('../class/agefodd_opca.class.php');
require_once ('../class/agefodd_session_stagiaire_heures.class.php');
require_once ('../class/agefodd_session_calendrier.class.php');
dol_include_once('/agefodd/class/agefodd_stagiaire_certif.class.php');

// Security check
if (! $user->rights->agefodd->lire) {
	accessforbidden();
}
$newToken = function_exists('newToken') ? newToken() : $_SESSION['newtoken'];

$hookmanager->initHooks(array(
	'agefoddsessionsubscribers'
));

$action = GETPOST('action', 'alpha');
if(empty($action)) {
	// Le mode par defaut devient l'édition
	$action = 'edit';
}


$id = GETPOST('id', 'int');
$confirm = GETPOST('confirm', 'alpha');
$stag_update_x = GETPOST('stag_update_x', 'alpha');
$stag_add_x = GETPOST('stag_add_x', 'alpha');
$stag_remove_x = GETPOST('stag_remove', 'alpha');
$modstagid = GETPOST('modstagid', 'int');
$newstag = GETPOST('newstag', 'none');
$edithours = ( bool ) GETPOST('edithours', 'none');

$form_update_x = GETPOST('form_update_x', 'alpha');
$form_add_x = GETPOST('form_add_x', 'alpha');
$period_add = GETPOST('period_add_x', 'alpha');
$period_update = GETPOST('period_update_x', 'alpha');
$newform_var = GETPOST('newform', 'none');
$opsid_var = GETPOST('opsid', 'none');
$form_remove_var = GETPOST('form_remove', 'none');
$period_remove = GETPOST('period_remove', 'none');
$newperiod = GETPOST('newperiod', 'none');
$formid = GETPOST('formid', 'int');
$cancel = GETPOST('cancel', 'none');
$source = GETPOST('source', 'alpha');
$SECUREKEY = GETPOST('securekey'); // Secure key
$idstagiaire = GETPOST('fk_stagiaire', 'int');
$removedfile = GETPOST('removedfile', 'none');
$addfile = GETPOST('addfile', 'none');
$session = new Agsession($db);
$resultsession = $session->fetch($id);
$backtoppage = $_SERVER['PHP_SELF'].'?action=edit&id='.$id;

if (empty($source)) {
	$source = 'agefodd_agsession';
}

// For MultiCompany module.
// Do not use GETPOST here, function is not defined and define must be done before including main.inc.php
// Because 2 entities can have the same ref.
$entity = (!empty($_GET['entity']) ? (int)$_GET['entity'] : (!empty($_POST['entity']) ? (int)$_POST['entity'] : 1));
if (is_numeric($entity)) {
	define('DOLENTITY', $entity);
}


if ($formid == - 1) {
	$formid = 0;
}

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

$parameters = array(
	'id' => $id,
	'stag_update_x'=>$stag_update_x,
	'stag_add_x'=>$stag_add_x,
	'stag_remove_x'=>$stag_remove_x,
	'modstagid'=>$modstagid,
	'newstag'=>$newstag,
	'edithours'=>$edithours,

);

$langs->loadLangs(array('main'));

$reshook = $hookmanager->executeHooks('doActions', $parameters, $agf, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0)
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
if (GETPOST('modelselected', 'none')) {
	$action = GETPOST('pre_action', 'none');
}

/**
 * Actions formateur
 */

$calendrier = new Agefodd_sesscalendar($db);
if (! empty($id)) {
	$result = $calendrier->fetch_all($id);
	if ($result<0) {
		setEventMessages(null,$calendrier->errors,'errors');

	}
}


$delete_calsel = GETPOST('deletecalsel_x', 'alpha');
if (! empty($delete_calsel)) {
	$action = 'delete_calsel';
}

/*
	 * Add file in email form
	 */
if (!empty($addfile)) {
	if (isset($_FILES['addedfile'])) {
		require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';

		// Set tmp user directory TODO Use a dedicated directory for temp mails files
		$vardir = $conf->user->dir_output . '/' . $user->id;
		$upload_dir_tmp = $vardir . '/temp';

		$mesg = dol_add_file_process($upload_dir_tmp, 0, 0, 'addedfile', '', null, $formmail->trackid);
	}

	$action = 'presend';
}

/*
 * Remove file in email form
 */
if (!empty($removedfile)) {
	require_once(DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php');

	// Set tmp user directory
	$vardir = $conf->user->dir_output . '/' . $user->id;
	$upload_dir_tmp = $vardir . '/temp';

	// TODO Delete only files that was uploaded from email form
	$mesg = dol_remove_file_process($removedfile, 0, 0, $formmail->trackid);

	$action = 'presend';
}
if ($action == 'send' && $cancel) {
	header('Location: ' . $_SERVER['PHP_SELF'] . '?action=edit&id=' . $id);
	exit();
}
if ($action == 'send' && empty($addfile) && empty($removedfile) && $user->rights->agefodd->creer && !$cancel) {
	$object = new Agsession($db);
	$result = $object->fetch($id);

	if ($result <= 0){
		setEventMessage('AgsessionNotFound','errors');
		exit;
	}

	$agf_trainee = new Agefodd_session_stagiaire($db);
	$agf_trainer = new Agefodd_session_formateur($db);
	$result = $agf_trainee->fetch_stagiaire_per_session($id);
	$result_trainer = $agf_trainer->fetch_formateur_per_session($id);

	if ($result <= 0 && $result_trainer <= 0){
		setEventMessage('AgsessionParticipantNotFound','errors');
		exit;
	}

	$langs->load('mails');
	$urltouse = DOL_MAIN_URL_ROOT;
	$out = $urltouse.'/public/newonlinesignstagiaire.php?&ref='.$object->ref.'&id='.$object->id.'&fk_stagiaire='.$agf_trainee->id;


	$messageTemplate = GETPOST('message', 'none');
	$subjectTemplate = GETPOST('subject', 'none');
	$from = $user->getFullName($langs) . ' <' . $user->email . '>';

	//$subject = GETPOST('subject', 'none');

	$TStagiaire = $agf_trainee->lines;
	$TFormateur = $agf_trainer->lines;
	$TParticipant = array_merge($TStagiaire,$TFormateur);


	if ($result < 0 && $result_trainer < 0) {
		setEventMessage($agf_trainee->error, 'errors');
		setEventMessage($agf_trainer->error, 'errors');
	}
	// tableau des fichiers envoyés aux stagiaires
	$TSentFile = array();

	if (!empty($TParticipant)){
		foreach ($TParticipant as $line) {

			$substitutionarray = getCommonSubstitutionArray($langs, 0, null, $object);
			complete_substitutions_array($substitutionarray, $langs, $object);

			// Check securitykey
			$securekeyseed = '';

			$type = $source;

			//Construction du lien par participant avec un securekey
			$urltouse = DOL_MAIN_URL_ROOT;
			$securekeyseed = isset($conf->global->AGEFODD_ONLINE_SIGNATURE_SECURITY_TOKEN) ? $conf->global->AGEFODD_ONLINE_SIGNATURE_SECURITY_TOKEN : '';
			$type = 'agefodd_agsession';

			if (get_class($line) == 'AgfTraineeSessionLine'){

				$securekeystagiaire = dol_hash($securekeyseed . $object->id . $line->id . 'trainee' . (empty($conf->multicompany->enabled) ? '' : $entity), '0');

				$out = dol_buildpath('/agefodd/public/newonlinesignstagiaire.php?ref=' . $object->ref . '&id=' . $object->id . '&fk_stagiaire=' . $line->id . '&securekey=' . $securekeystagiaire, 2);
				$out = '<a href="' . $out . '">' . $langs->trans('LinkOnlineSign') . '</a>';

				//Ajout valeur clé substitution
				$substitutionarray['__SESSION_PARTICIPANT__'] = $line->nom . ' ' . $line->prenom;
				$substitutionarray['__PARTICIPANT_CIVILITY__'] = $line->civilite;
			}

			if (get_class($line) == 'AgfSessionTrainer'){

				if (!empty($line->userid)){
					$userTrainer = new User($db);
					$result = $userTrainer->fetch($line->userid);
					if ($result < 0){
						setEventMessage($userTrainer->error, 'errors');
					}
				}

				if (!empty($line->socpeopleid)){
					$userTrainer = new Contact($db);
					$result = $userTrainer->fetch($line->socpeopleid);
					if ($result < 0){
						setEventMessage($userTrainer->error, 'errors');
					}
				}


				$securekeystagiaire = dol_hash($securekeyseed . $object->id . $line->formid . 'trainer' . (empty($conf->multicompany->enabled) ? '' : $entity), '0');

				$out = dol_buildpath('/agefodd/public/newonlinesignstagiaire.php?ref=' . $object->ref . '&id=' . $object->id . '&fk_formateur=' . $line->formid . '&securekey=' . $securekeystagiaire, 2);
				$out = '<a href="' . $out . '">' . $langs->trans('LinkOnlineSign') . '</a>';

				//Ajout valeur clé substitution
				$substitutionarray['__SESSION_PARTICIPANT__'] = $line->lastname . ' ' . $line->firstname;
				$substitutionarray['__PARTICIPANT_CIVILITY__'] = $userTrainer->civility_code;
			}

			//Ajout valeur clé substitution
			$substitutionarray['__LIEN_SIGNATURE_PAR_PARTICIPANT__'] = $out;
			$substitutionarray['__SESSION_REF__'] = $object->formintitule;


			$message = make_substitutions($messageTemplate, $substitutionarray, $langs);
			$subject = make_substitutions($subjectTemplate, $substitutionarray, $langs);

			$agf_trainee = new Agefodd_stagiaire($db);
			$resultTrainee = $agf_trainee->fetch($line->id);


			$companyid = $contact_trainee->socid;
			if (!empty($agf_trainee->fk_socpeople)) {
				$contactid = $agf_trainee->fk_socpeople;
			} else {
				$contactid = 0;
			}

			//Perapre data for trigeer action comm
			$object->socid = $companyid;
			$object->sendtoid = $contactid;

			$send_email = $agf_trainee->mail;

			$sendmail_check = true;


			$parameters = array(
				'contact_trainee' => & $contact_trainee,
				'subject' => & $subject,
				'send_email' => & $send_email,
				'from' => & $from,
				'message' => & $message,
				'filepath' => & $filepath,
				'mimetype' => & $mimetype,
				'filename' => & $filename,
				'sendtocc' => & $sendtocc,
				'sendmail_check' => & $sendmail_check
			);

			$reshook = $hookmanager->executeHooks('sendMassmail', $parameters, $agf_trainee, $action); // Note that $action and $object may have been modified by some hooks
			if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
			if (empty($reshook)) {

				if ($sendmail_check == true) {
					// Create form object
					include_once(DOL_DOCUMENT_ROOT . '/core/class/html.formmail.class.php');
					$formmail = new FormMail($db);

					if (!empty($conf->global->FCKEDITOR_ENABLE_MAIL)) {
						$message = str_replace("\n", '<br />', $message);
					}

					// Envoi de la fiche
					require_once(DOL_DOCUMENT_ROOT . '/core/class/CMailFile.class.php');
					$attachedfiles = $formmail->get_attached_files();
					$filepath = $attachedfiles['paths'];
					$filename = $attachedfiles['names'];
					$mimetype = $attachedfiles['mimes'];
                    if (!empty($conf->global->MAIN_MAIL_ADD_INLINE_IMAGES_IF_DATA)) {
                        $upload_dir_tmp = DOL_DATA_ROOT.'/mail/img';
                        $mailfile = new CMailFile($subject, $send_email, $from, $message, $filepath, $mimetype, $filename, $sendtocc, '', 1, -1, '', '', '', '', 'standard', '', $upload_dir_tmp);
                    }
                    else
                    {
                        $mailfile = new CMailFile($subject, $send_email, $from, $message, $filepath, $mimetype, $filename, $sendtocc, '', 1, -1);
                    }
					if ($mailfile->error) {
						setEventMessage($mailfile->error, 'errors');
					} else {
						$result = $mailfile->sendfile();
						if ($result) {
							setEventMessage($langs->trans('MailSuccessfulySent', $mailfile->getValidAddress($from, 2), $mailfile->getValidAddress($send_email, 2)), 'mesgs');

							$error = 0;

							$object->actiontypecode = $actiontypecode;
							$object->actionmsg = $actionmsg;
							$object->actionmsg2 = $actionmsg2;
							$object->fk_element = $object->id;
							$object->elementtype = $object->element;

							/* Appel des triggers */
							include_once(DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php');
							$interface = new Interfaces($db);


							$result = $interface->run_triggers('SIGNLINK_SENTBYMAIL', $object, $user, $langs, $conf);

							if ($result < 0) {
								$error++;
								$object->errors = $interface->errors;
							}
							// Fin appel triggers

							if ($error) {
								setEventMessage($object->errors, 'errors');
							} else {
								$i++;
								$action = '';
							}
						} else {
							$langs->load('other');
							if ($mailfile->error) {
								setEventMessage($langs->transnoentities('ErrorFailedToSendMail', dol_escape_htmltag($from), $send_email) . ':<br/>' . $mailfile->error, 'errors');
								dol_syslog($langs->trans('ErrorFailedToSendMail', $from, $send_email) . ' : ' . $mailfile->error);
							} else {
								setEventMessage('No mail sent. Feature is disabled by option MAIN_DISABLE_ALL_MAILS', 'errors');
							}
						}
					}
				}
			}
		}
	}


	$parameters = array(
		'TSentFile' => $TSentFile,
		'from' => $from,
		'mimetype' => $mimetype,
		'sendmail_check' => &$sendmail_check
	);
	$reshook = $hookmanager->executeHooks('afterSendMassMail', $parameters, $agf_session, $action); // Note that $action and $object may have been modified by some hooks
	if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
	//redirection
	header('Location: ' . $_SERVER['PHP_SELF'] . '?action=edit&id=' . $id);
	exit();
}


/*
 * Actions delete formateur
 */

if ($action == 'confirm_delete_form' && $confirm == "yes" && $user->rights->agefodd->modifier) {
	$obsid = GETPOST('opsid', 'int');

	$agf = new Agefodd_session_formateur($db);
	$result = $agf->remove($obsid);

	if ($result > 0) {
		Header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $id . '&action=edit');
		exit();
	} else {
		setEventMessage($agf->error, 'errors');
	}
}

if ($action == 'edit' && $user->rights->agefodd->modifier) {

	if (empty($formid) && (!empty($form_update_x)||  !empty($form_add_x))) {
		setEventMessage($langs->trans('ErrorFieldRequired', $langs->trans('AgfFormateur')), 'errors');
		$form_update_x = 0;
		$form_add_x = 0;
	}

	if (!empty($form_update_x)) {

		$agf = new Agefodd_session_formateur($db);

		$agf->opsid = GETPOST('opsid', 'int');
		$agf->formid = $formid;
		$agf->trainer_status = GETPOST('trainerstatus', 'int');
		$agf->trainer_type = GETPOST('trainertype', 'int');
		$result = $agf->update($user);

		if ($result > 0) {
			header("Location: " . $_SERVER['PHP_SELF'] . "?action=edit&id=" . $id);
			exit();
		} else {
			setEventMessage($agf->error, 'errors');
		}
	}

	if (!empty($form_add_x)) {
		$agf = new Agefodd_session_formateur($db);

		$agf->sessid = GETPOST('sessid', 'int');
		$agf->formid = $formid;
		$agf->trainer_status = GETPOST('trainerstatus', 'int');
		$agf->trainer_type = GETPOST('trainertype', 'int');
		$result = $agf->create($user);

		if ($result > 0) {
			$TSessCalendarId = GETPOST('TSessCalendarId', 'array');
			if (! empty($TSessCalendarId)) {
				foreach ( $TSessCalendarId as $fk_agefodd_session_calendrier ) {
					$agefodd_sesscalendar = new Agefodd_sesscalendar($db);
					$agefodd_sesscalendar->fetch($fk_agefodd_session_calendrier);

					$agf_cal = new Agefoddsessionformateurcalendrier($db);
					$agf_cal->sessid = $agf->sessid;
					$agf_cal->fk_agefodd_session_formateur = $agf->id;
					$agf_cal->trainer_cost = 0; // price2num(GETPOST('trainer_cost', 'alpha'), 'MU');
					$agf_cal->date_session = $agefodd_sesscalendar->date_session;
					$agf_cal->status=$agefodd_sesscalendar->status;

					$agf_cal->heured = $agefodd_sesscalendar->heured;
					$agf_cal->heuref = $agefodd_sesscalendar->heuref;

					// Test if trainer is already book for another training
					if ($agf_cal->checkTrainerBook($agf->formid) == 0) {
						$result = $agf_cal->create($user);
						if ($result < 0) {
							setEventMessage($agf_cal->error, 'errors');
						}
					}

					if (! empty($agf_cal->errors))
						setEventMessage($agf_cal->errors, 'errors');
					if (! empty($agf_cal->warnings))
						setEventMessage($agf_cal->warnings, 'warnings');
				}
			}

			header("Location: " . $_SERVER['PHP_SELF'] . "?action=edit&id=" . $id);
			exit();
		} else {
			setEventMessage($agf->error, 'errors');
		}
	}
}


// Ajout et mise à jour de calendrier
if ($action == 'edit_calendrier' && $user->rights->agefodd->modifier) {

	if (! empty($period_add)) {
		$error = 0;
		$error_message = array();
		$warning_message = array();

		$agf_cal = new Agefoddsessionformateurcalendrier($db);

		$agf_cal->sessid = GETPOST('sessid', 'int');
		$agf_cal->fk_agefodd_session_formateur = GETPOST('fk_agefodd_session_formateur', 'int');
		$agf_cal->trainer_cost = price2num(GETPOST('trainer_cost', 'alpha'), 'MU');
		$agf_cal->date_session = dol_mktime(0, 0, 0, GETPOST('datemonth', 'int'), GETPOST('dateday', 'int'), GETPOST('dateyear', 'int'));
		$agf_cal->status=GETPOST('calendar_trainer_status', 'int');

		// From calendar selection
		$heure_tmp_arr = array();

		$heured_tmp = GETPOST('dated', 'alpha');
		if (! empty($heured_tmp)) {
			$heure_tmp_arr = explode(':', $heured_tmp);
			$agf_cal->heured = dol_mktime($heure_tmp_arr[0], $heure_tmp_arr[1], 0, GETPOST('datemonth', 'int'), GETPOST('dateday', 'int'), GETPOST('dateyear', 'int'));
		}

		$heuref_tmp = GETPOST('datef', 'alpha');
		if (! empty($heuref_tmp)) {
			$heure_tmp_arr = explode(':', $heuref_tmp);
			$agf_cal->heuref = dol_mktime($heure_tmp_arr[0], $heure_tmp_arr[1], 0, GETPOST('datemonth', 'int'), GETPOST('dateday', 'int'), GETPOST('dateyear', 'int'));
		}

		// Test if trainer is already book for another training
		if ($agf_cal->checkTrainerBook(GETPOST('trainerid', 'int')) == 0) {
			$result = $agf_cal->create($user);
			if ($result < 0) {
				$error ++;
				setEventMessage($agf_cal->error, 'errors');
			}
		} else {
			$error ++;
		}

		if (! empty($agf_cal->errors)) {
			$error ++;
			setEventMessages(null, $agf_cal->errors, 'errors');
		}
		if (! empty($agf_cal->warnings)) {
			setEventMessages(null, $agf_cal->warnings, 'warnings');
		}

		if (! $error) {
			header('Location: ' . $_SERVER['PHP_SELF'] . '?action=edit_calendrier&id=' . $id);
			exit();
		}
	}

	if (! empty($period_update)) {

		$modperiod = GETPOST('modperiod', 'int');
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

		$trainer_cost = price2num(GETPOST('trainer_cost', 'alpha'), 'MU');
		$fk_agefodd_session_formateur = GETPOST('fk_agefodd_session_formateur', 'int');

		$agf_cal = new Agefoddsessionformateurcalendrier($db);
		$result = $agf_cal->fetch($modperiod);

		$agf_cal->sessid = GETPOST('sessid', 'int');

		// Je récupère le/les calendrier participants avant modificatino du calendrier formateur
		$TCalendrier = _getCalendrierFromCalendrierFormateur($agf_cal, true, true);

		if (! empty($modperiod))
			$agf_cal->id = $modperiod;
		if (! empty($date_session))
			$agf_cal->date_session = $date_session;
		if (! empty($heured))
			$agf_cal->heured = $heured;
		if (! empty($heuref))
			$agf_cal->heuref = $heuref;
		if (! empty($trainer_cost))
			$agf_cal->trainer_cost = $trainer_cost;
		if (! empty($fk_agefodd_session_formateur))
			$agf_cal->fk_agefodd_session_formateur = $fk_agefodd_session_formateur;

		$agf_cal->status=GETPOST('calendar_trainer_status', 'int');

		// Test if trainer is already book for another training
		$result = $agf_cal->fetch_all_by_trainer(GETPOST('trainerid', 'int'));
		if ($result < 0) {
			$error ++;
			$error_message[] = $agf_cal->error;
		} else {
			foreach ( $agf_cal->lines as $line ) {
				if (! empty($line->trainer_status_in_session) && $line->trainer_status_in_session != 6) {
					if ((($agf_cal->heured <= $line->heured && $agf_cal->heuref >= $line->heuref) || ($agf_cal->heured >= $line->heured && $agf_cal->heuref <= $line->heuref) || ($agf_cal->heured <= $line->heured && $agf_cal->heuref <= $line->heuref && $agf_cal->heuref > $line->heured) || ($agf_cal->heured >= $line->heured && $agf_cal->heuref >= $line->heuref && $agf_cal->heured < $line->heuref)) && $line->fk_session != $id) {
						if (! empty($conf->global->AGF_ONLY_WARNING_ON_TRAINER_AVAILABILITY)) {
							$warning_message[] = $langs->trans('AgfTrainerlAreadybookAtThisTime') . '(<a href=' . dol_buildpath('/agefodd/session/person.php', 1) . '?id=' . $line->fk_session . ' target="_blanck">' . $line->fk_session . '</a>)<br>';
						} else {
							$error ++;
							$error_message[] = $langs->trans('AgfTrainerlAreadybookAtThisTime') . '(<a href=' . dol_buildpath('/agefodd/session/person.php', 1) . '?id=' . $line->fk_session . ' target="_blanck">' . $line->fk_session . '</a>)<br>';
						}
					}
				}
			}
		}

		if (! $error) {
			$result = $agf_cal->update($user);
			if ($result < 0) {
				$error ++;
				$error_message[] = $agf_cal->error;
			}
			else
			{
				if (!empty($TCalendrier) && is_array($TCalendrier))
				{
					$agf_calendrier = $TCalendrier[0];
					$agf_calendrier->date_session = $agf_cal->date_session;
					$agf_calendrier->heured = $agf_cal->heured;
					$agf_calendrier->heuref = $agf_cal->heuref;
					$agf_calendrier->status = $agf_cal->status;
//                    $agf_calendrier->calendrier_type = $code_c_session_calendrier_type;
					$r=$agf_calendrier->update($user);
				}
				elseif (is_string($TCalendrier))
				{
					setEventMessage($TCalendrier, 'errors');
				}
			}
		}

		if (count($warning_message) > 0) {
			setEventMessages(null, $warning_message, 'warnings');
		}

		if (! $error) {
			Header("Location: " . $_SERVER['PHP_SELF'] . "?action=edit_calendrier&id=" . $id);
			exit();
		} else {
			setEventMessages(null, $error_message, 'errors');
		}
	}

	$copysessioncalendar = GETPOST('copysessioncalendar', 'none');
	if (! empty($copysessioncalendar)) {

		$fk_agefodd_session_formateur = GETPOST('fk_agefodd_session_formateur', 'int');

		// Delete all time already inputed
		$agf_cal = new Agefoddsessionformateurcalendrier($db);
		$agf_cal->fetch_all($fk_agefodd_session_formateur);
		if (is_array($agf_cal->lines) && count($agf_cal->lines) > 0) {
			foreach ( $agf_cal->lines as $line ) {
				$delteobject = new Agefoddsessionformateurcalendrier($db);
				$delteobject->remove($line->id);
			}
		}

		// Create as many as session caldendar
		$agf_session_cal = new Agefodd_sesscalendar($db);
		$agf_session_cal->fetch_all($id);
		if (is_array($agf_session_cal->lines) && count($agf_session_cal->lines) > 0) {
			foreach ( $agf_session_cal->lines as $line ) {

				$agf_cal = new Agefoddsessionformateurcalendrier($db);

				$agf_cal->sessid = $id;
				$agf_cal->fk_agefodd_session_formateur = $fk_agefodd_session_formateur;

				$agf_cal->date_session = $line->date_session;

				$agf_cal->heured = $line->heured;
				$agf_cal->heuref = $line->heuref;

				$agf_cal->status = $line->status;

				// Test if trainer is already book for another training
				$result = $agf_cal->fetch_all_by_trainer(GETPOST('trainerid', 'int'));
				if ($result < 0) {
					$error ++;
					$error_message[] = $agf_cal->error;
				}

				foreach ( $agf_cal->lines as $line ) {
					if (! empty($line->trainer_status_in_session) && $line->trainer_status_in_session != 6) {

						if (($agf_cal->heured <= $line->heured && $agf_cal->heuref >= $line->heuref) || ($agf_cal->heured >= $line->heured && $agf_cal->heuref <= $line->heuref) || ($agf_cal->heured <= $line->heured && $agf_cal->heuref <= $line->heuref && $agf_cal->heuref > $line->heured) || ($agf_cal->heured >= $line->heured && $agf_cal->heuref >= $line->heuref && $agf_cal->heured < $line->heuref)) {
							if (! empty($conf->global->AGF_ONLY_WARNING_ON_TRAINER_AVAILABILITY)) {
								$warning_message[] = $langs->trans('AgfTrainerlAreadybookAtThisTime') . '(<a href=' . dol_buildpath('/agefodd/session/person.php', 1) . '?id=' . $line->fk_session . ' target="_blanck">' . $line->fk_session . '</a>)<br>';
							} else {
								$error ++;
								$error_message[] = $langs->trans('AgfTrainerlAreadybookAtThisTime') . '(<a href=' . dol_buildpath('/agefodd/session/person.php', 1) . '?id=' . $line->fk_session . ' target="_blanck">' . $line->fk_session . '</a>)<br>';
							}
						}
					}
				}

				if (! $error) {

					$result = $agf_cal->create($user);
					if ($result < 0) {
						$error ++;
						$error_message[] = $agf_cal->error;
					}
				}
			}
		}

		if (! empty($warning_message)) {
			setEventMessages(null, $warning_message, 'warnings');
		}

		if (! $error) {
			Header("Location: " . $_SERVER['PHP_SELF'] . "?action=edit_calendrier&id=" . $id);
			exit();
		} else {
			setEventMessages(null, $error_message, 'errors');
		}
	}
}

if ($action == 'delete_calsel') {
	$deleteselcal = GETPOST('deleteselcal', 'array');
	if (count($deleteselcal) > 0) {
		foreach ( $deleteselcal as $lineid ) {
			$agf = new Agefoddsessionformateurcalendrier($db);
			$result = $agf->remove($lineid);
			if ($result < 0) {
				setEventMessage($agf->error, 'errors');
				$error ++;
			}
		}
	}
	if (! $error) {
		Header("Location: " . $_SERVER['PHP_SELF'] . "?action=edit_calendrier&id=" . $id);
		exit();
	}
}

/*
 * Actions delete period
 */

if ($action == 'confirm_delete_period' && $confirm == "yes" && $user->rights->agefodd->modifier) {
	$modperiod = GETPOST('modperiod', 'int');

	$agf = new Agefoddsessionformateurcalendrier($db);
	$result = $agf->remove($modperiod);

	if ($result > 0) {
		Header("Location: " . $_SERVER['PHP_SELF'] . "?action=edit_calendrier&id=" . $id);
		exit();
	} else {
		setEventMessage($agf->error, 'errors');
	}
}

/**
 * Actions stagiaires
 */

if ($action == 'edit' && ($user->rights->agefodd->creer | $user->rights->agefodd->modifier)) {

	if (!empty($stag_update_x)) {
		$agf = new Agsession($db);

		$agfsta = new Agefodd_session_stagiaire($db);
		$agfsta->fetch(GETPOST('stagerowid', 'int'));

		$agfsta->fk_session_agefodd = GETPOST('sessid', 'int');
		$agfsta->fk_soc_link = $fk_soc_link;
		$agfsta->fk_soc_requester = $fk_soc_requester;
		$agfsta->fk_socpeople_sign = $fk_socpeople_sign;
		$agfsta->fk_stagiaire = GETPOST('stagiaire', 'int');
		$agfsta->fk_agefodd_stagiaire_type = GETPOST('stagiaire_type', 'int');

		if (! empty($conf->global->AGF_USE_REAL_HOURS) && $agfsta->status_in_session !== GETPOST('stagiaire_session_status', 'int') && GETPOST('stagiaire_session_status', 'int') == Agefodd_session_stagiaire::STATUS_IN_SESSION_PARTIALLY_PRESENT) {
			$part = true;
		}

		$agfsta->status_in_session = GETPOST('stagiaire_session_status', 'int');
		$agfsta->hour_foad = GETPOST('hour_foad', 'int');
		$agfsta->comment = GETPOST('comment', 'alpha');

		if ($agfsta->update($user) > 0) {

			if (! empty($conf->global->AGF_USE_REAL_HOURS) && GETPOST('stagiaire_session_status', 'int') != Agefodd_session_stagiaire::STATUS_IN_SESSION_PARTIALLY_PRESENT) {
				$heures = new Agefoddsessionstagiaireheures($db);
				$result = $heures->setRealTimeAccordingTraineeStatus($user, GETPOST('sessid', 'int'), $agfsta->fk_stagiaire);
				if ($result < 0) {
					setEventMessage($heures->error, 'errors');
				}
				if (! empty($conf->global->AGF_SESSION_TRAINEE_STATUS_AUTO) && $agf->datef > dol_now()) {
					$result = $heures->setStatusAccordingTime($user, GETPOST('sessid', 'int'), $agfsta->fk_stagiaire);
					if ($result < 0) {
						setEventMessage($heures->error, 'errors');
					} elseif(!empty($result) && $result<>$agfsta->status_in_session) {
						$sta = new Agefodd_stagiaire($db);
						$res = $sta->fetch($agfsta->fk_stagiaire);
						if ($res < 0) {
							setEventMessage($sta->error, 'errors');
						}
						setEventMessage($langs->trans('AgfStatusRecalculateWithRealTime',$sta->nom.' '.$sta->prenom), 'warnings');
					}
				}
			}

			$redirect = true;
			$result = $agf->fetch(GETPOST('sessid', 'int'));

			if ($result > 0) {

				if (is_array($agf->array_options) && key_exists('options_use_subro_inter', $agf->array_options) && ! empty($agf->array_options['options_use_subro_inter'])) {
					$agf->type_session = 1;
				}

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
							setEventMessage($langs->trans('RecordSaved'), 'mesgs');
						} else {
							setEventMessage($opca->error, 'errors');
							$redirect = false;
						}
					} else {
						$result = $opca->create($user);
						if ($result > 0) {
							setEventMessage($langs->trans('RecordSaved'), 'mesgs');
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
		$agf->comment = GETPOST('comment', 'alpha');

		require_once ('../class/agefodd_stagiaire.class.php');
		$stag = new Agefodd_stagiaire($db);
		$stag->fetch($agf->fk_stagiaire);
		$agf->fk_soc = $stag->socid;

		$result = $agf->create($user);

		if ($result > 0) {
			Header("Location: " . $_SERVER['PHP_SELF'] . "?action=edit&id=" . $id);
			exit();
		} else {
			setEventMessage($agf->error, 'errors');
		}
	}
}

if ($action == 'remove_opcafksocOPCA') {

	$agf = new Agsession($db);

	$result = $agf->fetch(GETPOST('sessid', 'int'));

	if ($result > 0) {

		if (is_array($agf->array_options) && key_exists('options_use_subro_inter', $agf->array_options) && ! empty($agf->array_options['options_use_subro_inter'])) {
			$agf->type_session = 1;
		}

		if ($agf->type_session == 1) {

			if (is_array($agf->array_options) && key_exists('options_use_subro_inter', $agf->array_options) && ! empty($agf->array_options['options_use_subro_inter'])) {
				$agf->type_session = 1;
			}

			$agfsta = new Agefodd_session_stagiaire($db);
			$agfsta->fetch(GETPOST('stagerowid', 'int'));

			$opca = new Agefodd_opca($db);
			/*
			 *  Test si les infos existent déjà
			 * -> si OUI alors on update
			 * -> si NON on crée l'entrée dans la table
			 */
			$rowid_opca_trainee = $opca->getOpcaForTraineeInSession(GETPOST('fk_soc_trainee', 'int'), GETPOST('sessid', 'int'), $agfsta->id);
			if (! empty($rowid_opca_trainee)) {
				$opca->id = $rowid_opca_trainee;
				$result = $opca->delete($user);

				if ($result > 0) {
					Header("Location: " . $_SERVER['PHP_SELF'] . "?action=edit&id=" . $agf->id . '&modstagid=' . GETPOST('modstagid', 'int'));
					exit();
				} else {
					setEventMessage($agf->error, 'errors');
				}
			}
		}
	} else {
		setEventMessage($agf->error, 'errors');
	}
}

/*
 * Action editrealhours
 */
if ($action == 'editrealhours') {

	$hours = GETPOST('realhours', 'none');
	$sessid = ( int ) GETPOST('id', 'none');
	$edit = GETPOST('edit', 'none');

	if (! empty($hours)) {

		foreach ( $hours as $staId => $creneauxData ) {

			foreach ( $creneauxData as $creneaux => $heures ) {
				$heures = preg_replace('/,/', '.', $heures);
				$agf = new Agefoddsessionstagiaireheures($db);
				$result = $agf->fetch_by_session($id, $staId, $creneaux);
				if ($result < 0) {
					setEventMessage($agf->error, 'error');
					break;
				} elseif ($result) {
					if ($heures == '') {
						$res = $agf->delete($user);
					} elseif ($agf->heures !== $heures) {
						// édition d'heure existante
						$agf->heures = ( float ) $heures;
						$res = $agf->update($user);
					}
					if ($res < 0) {
						setEventMessage($agf->error, 'error');
						break;
					}
				} else {
					// création d'heure
					$agf->fk_stagiaire = $staId;
					$agf->fk_calendrier = $creneaux;
					$agf->fk_session = $id;
					$agf->heures = ( float ) $heures;
					$res = $agf->create($user);
					if ($res < 0) {
						setEventMessage($agf->error, 'error');
						break;
					}
				}
			}
			$agf = new Agefoddsessionstagiaireheures($db);
			$result = $agf->setStatusAccordingTime($user, $id, $staId);
			if ($result < 0) {
				setEventMessage($agf->error, 'error');
				break;
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
		// supprimer le certificat du stagiaire supprimé
		$agf_certif = new Agefodd_stagiaire_certif($db);
		$result = $agf_certif->fetch_all('', '', 0, 0, array(
			't.fk_session_agefodd' => $id,
			't.fk_session_stagiaire' => $stagerowid
		));
		foreach ( $agf_certif->lines as $cert ) {
			$cert->delete($user);
		}

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
if (GETPOST('update_subrogation', 'int') && ($user->rights->agefodd->creer || $user->rights->agefodd->modifier)) {
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

			if ($error == 0) {
				$result = $agf->update($user);
				if ($result > 0) {
					setEventMessage($langs->trans('RecordSaved'), 'mesgs');
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
		$statusinsession = GETPOST('statusinsession', 'int');
		$stagiaires = new Agefodd_session_stagiaire($db);
		$stagiaires->fk_session_agefodd = $agf->id;
		$result = $stagiaires->update_status_by_soc($user, 1, 0, $statusinsession);
		if ($result < 0) {
			setEventMessage($stagiaires->error, 'errors');
		} else {
			$part=false;
			if (! empty($conf->global->AGF_USE_REAL_HOURS)) {
				$result = $stagiaires->fetch_stagiaire_per_session($agf->id);
				if ($result < 0) {
					setEventMessage($stagiaires->error, 'errors');
				} else {
					foreach ($stagiaires->lines as $trainee) {
						if ($statusinsession == Agefodd_session_stagiaire::STATUS_IN_SESSION_PARTIALLY_PRESENT) {
							setEventMessage($langs->trans('AgfEditReelHours', $trainee->nom . ' ' . $trainee->prenom), 'warnings');
							$part=true;
						} else {
							$heures = new Agefoddsessionstagiaireheures($db);
							$result = $heures->setRealTimeAccordingTraineeStatus($user, $agf->id, $trainee->id);
							if ($result < 0) {
								setEventMessages($heures->error, 'errors');
							}

							if (! empty($conf->global->AGF_SESSION_TRAINEE_STATUS_AUTO) && $agf->datef > dol_now()) {
								$result = $heures->setStatusAccordingTime($user, $agf->id, $trainee->id);
								if ($result < 0) {
									setEventMessage($heures->error, 'errors');
								} elseif (!empty($result) && $result <> $statusinsession) {
									$sta = new Agefodd_stagiaire($db);
									$res = $sta->fetch($trainee->id);
									if ($res < 0) {
										setEventMessage($sta->error, 'errors');
									}
									setEventMessage($langs->trans('AgfStatusRecalculateWithRealTime', $sta->nom . ' ' . $sta->prenom), 'warnings');
								}
							}
						}
					}
				}
			}

			if ($part) {
				Header("Location: " . $_SERVER['PHP_SELF'] . "?action=edit&id=" . $id . "&edithours=true");
				exit();
			} else {
				Header("Location: " . $_SERVER['PHP_SELF'] . "?action=edit&id=" . $id);
				exit();
			}
		}
	}
}

if ($action == 'remove_fksocOPCA' && $user->rights->agefodd->modifier) {

	$agf = new Agsession($db);
	$result = $agf->fetch($id);
	unset($agf->fk_soc_OPCA);
	unset($agf->fk_socpeople_OPCA);
	$result = $agf->update($user);

	if ($result > 0) {
		Header("Location: " . $_SERVER['PHP_SELF'] . "?action=edit&id=" . $id);
		exit();
	} else {
		setEventMessage($agf->error, 'errors');
	}
}
$arrayofcss = array(
	'/agefodd/css/agefodd.css'
);
llxHeader('', $langs->trans("AgfSessionDetail"), '', '', '', '', '', $arrayofcss, '');

$form = new Form($db);
$formAgefodd = new FormAgefodd($db);

/*
 * View Trainers
 */
if (! empty($id)) {
	$agf = new Agsession($db);
	$result = $agf->fetch($id);

	$head = session_prepare_head($agf);

	dol_fiche_head($head, 'person', $langs->trans("AgfSessionDetail"), 0, 'group');

	dol_agefodd_banner_tab($agf, 'action=edit&id');
	dol_fiche_end();

	print load_fiche_titre($langs->trans("AgfFormateur"), '', '');

	/*
	 * Confirm delete calendar
	 */

	if (! empty($period_remove)) {
		// Param url = id de la periode à supprimer - id session
		print $form->formconfirm($_SERVER['PHP_SELF'] . '?modperiod=' . GETPOST('modperiod', 'none') . '&id=' . $id, $langs->trans("AgfDeletePeriod"), $langs->trans("AgfConfirmDeletePeriod"), "confirm_delete_period", '', '', 1);
	}

	$rowf_var = GETPOST('rowf', 'none');
	$trainerid_var = GETPOST('trainerid', 'none');
	if ($action == 'edit_calendrier' && (! empty($rowf_var) || ! empty($trainerid_var))) {

		$anchroid = empty($rowf_var) ? $trainerid_var : $rowf_var;

		print '<script type="text/javascript">
						jQuery(document).ready(function () {
							jQuery(function() {' . "\n";
		print '				 $(\'html, body\').animate({scrollTop: $("#anchorrowf' . $anchroid . '").offset().top-20}, 500,\'easeInOutCubic\');';
		print '			});
					});
					</script> ';
	}

	if ($action == 'edit' || $action == 'edit_subrogation' || $action == 'presend') {

		/*
		 * Les pages participants et formateurs sont bizarrement foutues, le mode de base est edit et la plupart des actions sont passées via un autre paramètre url que action, sauf quand on édite la subrogation,
		 * dans ce cas là il est nécessaire d'indiquer au bloc des formateurs qu'il s'agit d'un mode vue pour le bloc des formateurs
		 */

		if(!empty($opsid_var)) {

			print '<script type="text/javascript">
					jQuery(document).ready(function () {
						jQuery(function() {' . "\n";
			if (!empty($newform_var)) {
				print '				 $(\'html, body\').animate({scrollTop: $("#anchornewform").offset().top}, 500,\'easeInOutCubic\');';
			} elseif (!empty($opsid_var) && empty($form_remove_var)) {
				print '				 $(\'html, body\').animate({scrollTop: $("#anchoropsid' . GETPOST('opsid', 'none') . '").offset().top-150}, 500,\'easeInOutCubic\');';
			}
			print '			});
					});
					</script> ';

		}

		/*
		 * Confirm Delete
		 */
		if (! empty($form_remove_var)) {
			// Param url = id de la ligne formateur dans session - id session
			print $form->formconfirm($_SERVER['PHP_SELF'] . "?opsid=" . GETPOST('opsid', 'none') . '&id=' . $id, $langs->trans("AgfDeleteForm"), $langs->trans("AgfConfirmDeleteForm"), "confirm_delete_form", '', '', 1);
		}

		if(!empty($opsid_var) || GETPOST('newform', 'int') > 0) {
			print '<form name="form_update" action="' . $_SERVER['PHP_SELF'] . '?action=edit&amp;id=' . $id . '"  method="POST">' . "\n";
			print '<input type="hidden" name="token" value="' . $newToken . '">' . "\n";
			print '<input type="hidden" name="action" value="edit">' . "\n";
			print '<input type="hidden" name="sessid" value="' . $id . '">' . "\n";
		}

		print '<table class="noborder" width="100%">' . "\n";

		print '<tr class="liste_titre">';
		print '<th class="liste_titre">&nbsp;</th>';
		print '<th class="liste_titre name">Nom</th>';
		if (empty($user->rights->agefodd->session->trainer)) {
			print '<th class="liste_titre status">Statut</th>';
		}
		if (! empty($conf->global->AGF_DOL_TRAINER_AGENDA)) {
			print '<th class="liste_titre temps_total_prog">Temps total programme</th>';
			print '<th class="liste_titre temps_prog">Temps programme</th>';
		}
		// HEADER FORMATEUR col signatures et nb signé
		if (getDolGlobalInt('AGF_DISPLAY_SIGNATURE_TRAINEE') && !empty($session->array_options['options_agefodd_onlinesign'])){
			print '<th nowrap class="liste_titre liste_nb_sign">'. $langs->trans("nbsignature") . '</th>'  . "\n";
		}
		print '<th class="liste_titre actions">&nbsp;</th>';
		print '</tr>' . "\n";

		// Create as many as session caldendar
		$agf_session_cal = new Agefodd_sesscalendar($db);
		$result = $agf_session_cal->fetch_all($agf->id);
		if ($result < 0) {
			setEventMessages(null, $agf_session_cal->errors, 'errors');
		}

		// Display edit and update trainer
		$formateurs = new Agefodd_session_formateur($db);
		$nbform = $formateurs->fetch_formateur_per_session($agf->id);
		if ($nbform > 0) {
			for($i = 0; $i < $nbform; $i ++) {
				if ($formateurs->lines[$i]->opsid == GETPOST('opsid', 'none') && ! empty($form_remove_var))
					print '<tr class="oddeven" style="background:#d5baa8">';
				else
					print '<tr class="oddeven">' . "\n";

				print '<td width="20px" align="center">' . ($i + 1);
				print '<a id="anchoropsid' . $formateurs->lines[$i]->opsid . '" name="anchoropsid' . $formateurs->lines[$i]->opsid . '" href="#anchoropsid' . $formateurs->lines[$i]->opsid . '"></a>';
				print '</td>' . "\n";

				// Edit line

				if ($formateurs->lines[$i]->opsid == GETPOST('opsid', 'none') && empty($form_remove_var)) {
					print '<td class="name">' . "\n";
					print '<input type="hidden" name="opsid" value="' . $formateurs->lines[$i]->opsid . '">' . "\n";

                    $filter = ['excludeContributors' => [
                        "sessid" => $id
                    ]];
                    if ($conf->global->AGF_FILTER_TRAINER_TRAINING) {
                        $filter['excludeContributors']['training'] = $agf->formid;
                    }

					print $formAgefodd->select_formateur($formateurs->lines[$i]->formid, "formid", $filter);
					if (! empty($conf->global->AGF_USE_FORMATEUR_TYPE)) {
						print '&nbsp;';
						print $formAgefodd->select_type_formateur($formateurs->lines[$i]->trainer_type, "trainertype", ' active=1 ');
					}
					print '</td>' . "\n";

					if (! $user->rights->agefodd->session->trainer) {
						print '<td class="status">' . "\n";
						print $formAgefodd->select_trainer_session_status('trainerstatus', $formateurs->lines[$i]->trainer_status);
					}

					print '</td>' . "\n";

					if (! empty($conf->global->AGF_DOL_TRAINER_AGENDA)) {
						print '<td class="temps_total_prog">&nbsp;</td>' . "\n";
						print '<td class="temps_prog">&nbsp;</td>' . "\n";
					}
					//   col signatures et nb signé
					if (getDolGlobalInt('AGF_DISPLAY_SIGNATURE_TRAINEE') && !empty($session->array_options['options_agefodd_onlinesign'])){
						printTDSignatureContent($calendrier, $agf, $formateurs->lines[$i]->formid,  'trainer');
					}
					print '<td class="actions" align="right">' . "\n";
					if ($user->rights->agefodd->modifier) {

						print '<input type="submit"  name="form_update_x" class="butAction" value=" &#128427; ' . $langs->trans("AgfModSave") .'"/>';
					}
					print '</td>' . "\n";
				} else {
					// trainer info
					if (strtolower($formateurs->lines[$i]->lastname) == "undefined") {
						print '<td class="name">' . $langs->trans("AgfUndefinedTrainer") . '</td>' . "\n";
						if (! $user->rights->agefodd->session->trainer) {
							print '<td class="status">&nbsp;</td>' . "\n";
						}
						print '<td class="temps_total_prog">&nbsp;</td>' . "\n";
						print '<td class="temps_prog">&nbsp;</td>' . "\n";
					} else {
						print '<td class="name">' . "\n";
						print '<a href="' . dol_buildpath('/agefodd/trainer/card.php', 1) . '?id=' . $formateurs->lines[$i]->formid . '">' . "\n";
						print img_object($langs->trans("ShowContact"), "contact") . ' ';
						print strtoupper($formateurs->lines[$i]->lastname) . ' ' . ucfirst($formateurs->lines[$i]->firstname) . '</a>' . "\n";

						if (! empty($conf->global->AGF_USE_FORMATEUR_TYPE)) {
							print '<br />';
							print $formateurs->lines[$i]->trainer_type_label;
						}

						print '</td>' . "\n";

						if (empty($user->rights->agefodd->session->trainer)) {
							print '<td class="status">' . $formateurs->lines[$i]->getLibStatut(2) . '</td>' . "\n";
						}

						$totaltimetrainer = '';
						$hourhtml = '';
						if ($conf->global->AGF_DOL_TRAINER_AGENDA) {
							// Calculate time past in session
							$trainer_calendar = new Agefoddsessionformateurcalendrier($db);
							$result = $trainer_calendar->fetch_all($formateurs->lines[$i]->opsid);
							if ($result < 0) {
								setEventMessage($trainer_calendar->error, 'errors');
							}

							if (! empty($trainer_calendar->lines)) {

								$hourhtml .= '<table class="nobordernopadding">' . "\n";
								$blocNumber = count($trainer_calendar->lines);
								$old_date = 0;
								$totaltime = 0;

								for($j = 0; $j < $blocNumber; $j ++) {
									// Find if time is solo plateform for trainee
									$platform_time = false;
									if (is_array($agf_session_cal->lines) && count($agf_session_cal->lines) > 0) {
										foreach ( $agf_session_cal->lines as $line_cal ) {
											if (
												$line_cal->calendrier_type == 'AGF_TYPE_PLATF' &&
												(
													(
														$trainer_calendar->lines[$j]->heured <= $line_cal->heured &&
														$trainer_calendar->lines[$j]->heuref >= $line_cal->heuref
													)
													||
													(
														$trainer_calendar->lines[$j]->heured >= $line_cal->heured &&
														$trainer_calendar->lines[$j]->heuref <= $line_cal->heuref
													)
													||
													(
														$trainer_calendar->lines[$j]->heured <= $line_cal->heured &&
														$trainer_calendar->lines[$j]->heuref <= $line_cal->heuref &&
														$trainer_calendar->lines[$j]->heuref > $line_cal->heured
													)
													||
													(
														$trainer_calendar->lines[$j]->heured >= $line_cal->heured &&
														$trainer_calendar->lines[$j]->heuref >= $line_cal->heuref &&
														$trainer_calendar->lines[$j]->heured < $line_cal->heuref
													)
												)
											) {
												$platform_time = true;
												break;
											}
										}
									}

									if ($trainer_calendar->lines[$j]->status == Agefoddsessionformateurcalendrier::STATUS_CANCELED || $platform_time)
										continue;
									$totaltime += $trainer_calendar->lines[$j]->heuref - $trainer_calendar->lines[$j]->heured;

									if ($j > 6) {
										$styledisplay = " style=\"display:none\" class=\"otherdatetrainer\" ";
									} else {
										$styledisplay = " ";
									}

									$hourhtml .= '<tr ' . $styledisplay . '>' . "\n";
									$hourhtml .= '<td width="100px">' . "\n";
									$hourhtml .= dol_print_date($trainer_calendar->lines[$j]->date_session, 'daytextshort') . '</td>' . "\n";
									$hourhtml .= '<td width="100px">' . "\n";
									if (empty($user->rights->agefodd->session->trainer)) {
										$hourDisplay = dol_print_date($trainer_calendar->lines[$j]->heured, 'hour') . ' - ' . dol_print_date($trainer_calendar->lines[$j]->heuref, 'hour');
										$hourhtml .= _isTrainerFreeBadge($hourDisplay, $trainer_calendar->lines[$j], $formateurs->lines[$i]->formid);
									}
									$hourhtml.= '<td>'.Agefodd_sesscalendar::getStaticLibStatut($trainer_calendar->lines[$j]->status, 0).'</td>'."\n";
									$hourhtml .= '</td></tr>' . "\n";
								}

								if ($blocNumber > 6) {
									$hourhtml .= '<tr><td colapsn="2" style="font-weight: bold; font-size:150%; cursor:pointer" class="switchtimetrainer">+</td></tr>';
								}
								$hourhtml .= '</table>' . "\n";

								$min = floor($totaltime / 60);
								$rmin = sprintf("%02d", $min % 60);
								$hour = floor($min / 60);

								$totaltimetrainer = '(' . $hour . ':' . $rmin . ')';
							}
							print '<td class="temps_total_prog">' . $totaltimetrainer . '</td>' . "\n";
							print '<td class="temps_prog">' . $hourhtml . '</td>' . "\n";
						}

						// FORMATEUR ligne date col signatures et nb signé
						if (getDolGlobalInt('AGF_DISPLAY_SIGNATURE_TRAINEE') && !empty($session->array_options['options_agefodd_onlinesign'])){
							printTDSignatureContent($calendrier, $agf, $formateurs->lines[$i]->formid, 'trainer');
						}
					}

					print '<td class="action" align="right">' . "\n";

					if (!empty($user->rights->agefodd->modifier) && empty($user->rights->agefodd->session->trainer)) {
						print
							'<a href="' . dol_buildpath('/agefodd/session/person.php', 1) . '?action=edit&amp;sessid=' . $formateurs->lines[$i]->sessid . '&amp;opsid=' . $formateurs->lines[$i]->opsid . '&amp;id=' . $id . '&amp;form_edit=1">' . img_picto($langs->trans("Edit"), 'edit') . '</a>' . "\n";
					}
					print '&nbsp;';
					if (!empty($user->rights->agefodd->modifier) && empty($user->rights->agefodd->session->trainer)) {
						print
							'<a href="' . dol_buildpath('/agefodd/session/person.php', 1) . '?action=edit&amp;sessid=' . $formateurs->lines[$i]->sessid . '&amp;opsid=' . $formateurs->lines[$i]->opsid . '&amp;id=' . $id . '&amp;form_remove=1">' . img_picto($langs->trans("Delete"), 'delete') . '</a>' . "\n";
					}
					if (!empty($user->rights->agefodd->modifier) && ! empty($conf->global->AGF_DOL_TRAINER_AGENDA) && empty($user->rights->agefodd->session->trainer)) {
						print '&nbsp;';
						print '<a href="' . dol_buildpath('/agefodd/session/person.php', 1) . '?action=edit_calendrier&amp;id=' . $id . '&amp;rowf=' . $formateurs->lines[$i]->formid . '">' . img_picto($langs->trans('Time'), 'calendar', '', false, 0, 0, '', 'valignmiddle') . '</a>' . "\n";

					}
					print '</td>' . "\n";
				}

				print '</tr>' . "\n";
			}

			print '<script>' . "\n";
			print '$(document).ready(function () { ' . "\n";
			print '		$(".switchtimetrainer").click(function(){' . "\n";
			print '         $(this).parent().parent().find(".otherdatetrainer").each(function(){$(this).toggle()});';
			print '			if ($(this).text()==\'+\') { ' . "\n";
			print '				$(this).text(\'-\'); ' . "\n";
			print '			}else { ' . "\n";
			print '				$(this).text(\'+\'); ' . "\n";
			print '			} ' . "\n";
			print '			' . "\n";
			print '		});' . "\n";
			print '});' . "\n";
			print '</script>' . "\n";
		}

		// New trainers
		if (! empty($newform_var) && ! empty($user->rights->agefodd->modifier)) {
			print '<tr class="oddeven newline">' . "\n";

			print '<td width="20px" align="center"><a id="anchornewform" name="anchornewform"/>' . ($i + 1) . '</td>';
			print '<td class="name nowrap">';

            $filter = ['excludeContributors' => [
                "sessid" => $id
            ]];
            if ($conf->global->AGF_FILTER_TRAINER_TRAINING) {
                $filter['excludeContributors']['training'] = $agf->formid;
			}
			print $formAgefodd->select_formateur(!empty($formateurs->lines[$i]) ? $formateurs->lines[$i]->formid : '', "formid", $filter, 1);
			if (! empty($conf->global->AGF_USE_FORMATEUR_TYPE)) {
				print '&nbsp;';
				print $formAgefodd->select_type_formateur($conf->global->AGF_DEFAULT_FORMATEUR_TYPE, "trainertype", ' active=1 ');
			}
			print '</td>' . "\n";

			if (empty($user->rights->agefodd->session->trainer)) {
				print '<td class="status">' . "\n";
				print $formAgefodd->select_trainer_session_status('trainerstatus', !empty($formateurs->lines[$i]) ? $formateurs->lines[$i]->trainer_status : '');
				print '</td>' . "\n";
			}

			if (! empty($conf->global->AGF_DOL_TRAINER_AGENDA)) {
				print '<td class="temps_total_prog">&nbsp;</td>' . "\n";
				print '<td class="temps_prog">&nbsp;</td>' . "\n";
			}
			//   col signatures et nb signé
			if (getDolGlobalInt('AGF_DISPLAY_SIGNATURE_TRAINEE') && !empty($session->array_options['options_agefodd_onlinesign'])) {
				print '<td class="liste_nb_sign"></td>' . "\n";
			}
			print '<td class="actions" align="right">';
			if (!empty($user->rights->agefodd->modifier)) {
				print '<input type="submit" name="form_add_x" class="butAction" value=" &#128427; ' . $langs->trans("AgfModSave") .'"/>';
			}
			print '</td>' . "\n";

			print '</tr>' . "\n";
			if ($calendrier->fetch_all($id) > 0) {
				print '<tr class="">' . "\n";
				$colspan = 4; // num ligne / name / status / actions
				if (! empty($conf->global->AGF_DOL_TRAINER_AGENDA))
					$colspan += 2; // temps_total_prog / temps_prog
				if (empty($user->rights->agefodd->session->trainer)) {
					$colspan --;
				}
				print '<td><input type="checkbox" onclick="$(\'input[name^=TSessCalendarId\').prop(\'checked\', this.checked)" /></td>' . "\n";
				print '<td colspan="' . $colspan . '">' . "\n";

				print '<ul class="nocellnopadd">' . "\n"; // tmenu / nocellnopadd
				foreach ( $calendrier->lines as &$agefodd_sesscalendar ) {
					print
						'<li><input type="checkbox" name="TSessCalendarId[]" value="' . $agefodd_sesscalendar->id . '"> ' . dol_print_date($agefodd_sesscalendar->date_session, 'daytext') . ' [' . dol_print_date($agefodd_sesscalendar->heured, 'hour') . ' - ' . dol_print_date(
							$agefodd_sesscalendar->heuref, 'hour') . ']</li>';
				}
				print '</ul>' . "\n";

				print '</td>' . "\n";
				//   col signatures et nb signé
				if (getDolGlobalInt('AGF_DISPLAY_SIGNATURE_TRAINEE') && !empty($session->array_options['options_agefodd_onlinesign'])) {
					print '<td class="liste_nb_sign"></td>' . "\n";
				}
				print '</tr>' . "\n";
			} else {
				setEventMessages(null, $calendrier->errors,'errors');
			}
		}

		print '</table>' . "\n";
		if(!empty($opsid_var) || GETPOST('newform', 'int') > 0) {
			print '</form>';
		}
	} else {
		// Display view mode
		print '<table class="noborder" width="100%">' . "\n";

		print '<tr class="liste_titre">' . "\n";
		print '<th class="liste_titre name">Nom</th>' . "\n";
		print '<th class="liste_titre status">Statut</th>' . "\n";
		if (! empty($conf->global->AGF_DOL_TRAINER_AGENDA)) {
			print '<th class="liste_titre temps_total_prog">&nbsp;</th>' . "\n";
			print '<th class="liste_titre temps_total">&nbsp;</th>' . "\n";
		}
		// HEADER FORMATEUR col signatures et nb signé
		if (getDolGlobalInt('AGF_DISPLAY_SIGNATURE_TRAINEE') && !empty($session->array_options['options_agefodd_onlinesign'])){
			print '<th nowrap class="liste_titre liste_nb_sign">'. $langs->trans("nbsignature") . '</th>'  . "\n";
		}
		print '</tr>';

		$agf_session_cal = new Agefodd_sesscalendar($db);
		$result = $agf_session_cal->fetch_all($agf->id);
		if ($result < 0) {
			setEventMessages(null, $agf_session_cal->errors, 'errors');
		}

		$formateurs = new Agefodd_session_formateur($db);
		$nbform = $formateurs->fetch_formateur_per_session($agf->id);

		if ($nbform < 1) {
			print '<td style="text-decoration: blink;"><br /><br />' . $langs->trans("AgfNobody") . '</td></tr>' . "\n";
			print '<table style="border:0;" width="100%">';
			print '<tr><td align="right">';
			print '<form name="newform" action="' . $_SERVER['PHP_SELF'] . '?action=edit&amp;id=' . $id . '"  method="POST">' . "\n";
			print '<input type="hidden" name="token" value="' . $newToken . '">' . "\n";
			print '<input type="hidden" name="action" value="edit">' . "\n";
			print '<input type="hidden" name="newform" value="1">' . "\n";
			print '<input type="submit" class="butAction" value="' . $langs->trans("AgfFormateurAdd") . '">' . "\n";
			print '</form></td></tr>' . "\n";
			print '</table>' . "\n";
		} else {

			for($i = 0; $i < $nbform; $i ++) {
				print '<tr class="oddeven">';

				// Trainer name
				print '<td class="name">';
				print '<a id="anchoropsid' . $formateurs->lines[$i]->opsid . '" name="anchoropsid' . $formateurs->lines[$i]->opsid . '" href="#anchoropsid' . $formateurs->lines[$i]->opsid . '"></a>';
				print '<a id="anchorrowf' . $formateurs->lines[$i]->formid . '" name="anchorrowf' . $formateurs->lines[$i]->formid . '" href="#anchorrowf' . $formateurs->lines[$i]->formid . '"></a>';
				print '<a href="' . dol_buildpath('/agefodd/trainer/card.php', 1) . '?id=' . $formateurs->lines[$i]->formid . '">';
				print img_object($langs->trans("ShowContact"), "contact") . ' ';
				print strtoupper($formateurs->lines[$i]->lastname) . ' ' . ucfirst($formateurs->lines[$i]->firstname) . '</a>';
				print '</td>';

				// Trainer status
				print '<td class="status">' . $formateurs->lines[$i]->getLibStatut(2) . '</td>';

				if (! empty($conf->global->AGF_DOL_TRAINER_AGENDA)) {
					print '<td class="temps_total_prog">';
					// Calculate time past in session
					$trainer_calendar = new Agefoddsessionformateurcalendrier($db);
					$result = $trainer_calendar->fetch_all($formateurs->lines[$i]->opsid);
					if ($result < 0) {
						setEventMessage($trainer_calendar->error, 'errors');
					}

					$totaltime = 0;
					foreach ( $trainer_calendar->lines as $line_trainer_calendar ) {
						// Find if time is solo plateform for trainee
						$platform_time = false;
						if ($result > 0 && is_array($agf_session_cal->lines) && count($agf_session_cal->lines) > 0) {
							foreach ( $agf_session_cal->lines as $line_cal ) {
								if (
									$line_cal->calendrier_type == 'AGF_TYPE_PLATF' &&
									(
										(
											$line_trainer_calendar->heured <= $line_cal->heured &&
											$line_trainer_calendar->heuref >= $line_cal->heuref
										)
										||
										(
											$line_trainer_calendar->heured >= $line_cal->heured &&
											$line_trainer_calendar->heuref <= $line_cal->heuref
										)
										||
										(
											$line_trainer_calendar->heured <= $line_cal->heured &&
											$line_trainer_calendar->heuref <= $line_cal->heuref &&
											$line_trainer_calendar->heuref > $line_cal->heured
										)
										||
										(
											$line_trainer_calendar->heured >= $line_cal->heured &&
											$line_trainer_calendar->heuref >= $line_cal->heuref &&
											$line_trainer_calendar->heured < $line_cal->heuref
										)
									)
								) {
									$platform_time = true;
									break;
								}
							}
						}

						if (!$platform_time) $totaltime += $line_trainer_calendar->heuref - $line_trainer_calendar->heured;
					}
					$min = floor($totaltime / 60);
					$rmin = sprintf("%02d", $min % 60);
					$hour = floor($min / 60);

					print '(' . $hour . ':' . $rmin . ')';
					print '</td>';
				}

				print '<td class="edit_agenda">';
				if (! empty($conf->global->AGF_DOL_TRAINER_AGENDA)) {
					/* Time management */
					$calendrier_formateur = new Agefoddsessionformateurcalendrier($db);
					$calendrier_formateur->fetch_all($formateurs->lines[$i]->opsid);
					$blocNumber = count($calendrier_formateur->lines);

					if ($blocNumber < 1 && ! (empty($newperiod))) {
						print '<span style="color:red;">' . $langs->trans("AgfNoCalendar") . '</span>';
					} else {
						// print '<td>';
						print '<form name="trainer_calendrier_update" action="' . $_SERVER['PHP_SELF'] . '?id=' . $id . '"  method="POST">' . "\n";
						print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">' . "\n";
						print '<input type="hidden" name="sessid" value="' . $id . '">' . "\n";

						print '<table width="100%" class="border">';

						print '<tr class="liste_titre">';
						print '<th class="liste_titre">';
						if ($user->rights->agefodd->modifier) {
							print '<input type="image" src="' . img_picto($langs->trans("Delete"), 'delete', '', false, 1) . '" border="0" align="absmiddle" name="deletecalsel" title="' . $langs->trans("AgfDeleteOnlySelectedLines") . '" alt="' . $langs->trans("AgfDeleteOnlySelectedLines") . '">';
						}
						print '</th>';
						print '<th class="liste_titre">' . $langs->trans('Date') . '</th>';
						print '<th class="liste_titre">' . $langs->trans('Hours') . '</th>';
						print '<th class="liste_titre">' . $langs->trans('Status') . '</th>';
						// Trainer cost is fully managed into cost management not here
						if (empty($conf->global->AGF_ADVANCE_COST_MANAGEMENT)) {
							print '<th class="liste_titre">' . $langs->trans('AgfTrainerCostHour') . '</th>';
						}
						print '<th class="liste_titre">' . $langs->trans('Edit') . '</th>';

						print '</tr>';

						$old_date = 0;
						$duree = 0;
						for($j = 0; $j < $blocNumber; $j ++) {
							if ($calendrier_formateur->lines[$j]->id == GETPOST('modperiod', 'none') && ! empty($period_remove))
								print '<tr bgcolor="#d5baa8">' . "\n";
							else
								print '<tr>' . "\n";

							if ($calendrier_formateur->lines[$j]->id == GETPOST('modperiod', 'none') && empty($period_remove)) {
								// Delete select case not display here
								print '<td></td>' . "\n";

								print '<td  width="20%">' . $langs->trans("AgfPeriodDate") . ' ' . "\n";
								$form->select_date($calendrier_formateur->lines[$j]->date_session, 'date', '', '', '', 'obj_update_' . $j);

								print '<input type="hidden" name="token" value="'.$newToken.'">';
								print '<input type="hidden" name="action" value="edit_calendrier">' . "\n";
								print '<input type="hidden" name="fk_agefodd_session_formateur" value="' . $formateurs->lines[$i]->opsid . '">' . "\n";
								print '<input type="hidden" name="periodid" value="' . $calendrier_formateur->lines[$j]->stagerowid . '">' . "\n";
								print '<input type="hidden" name="trainerid" value="' . $formateurs->lines[$i]->formid . '">' . "\n";
								print '<input type="hidden" name="modperiod" value="' . $calendrier_formateur->lines[$j]->id . '">' . "\n";

								print '</td>' . "\n";
								print '<td width="40%;" >' . $langs->trans("AgfPeriodTimeB") . ' ' . "\n";
								print $formAgefodd->select_time(dol_print_date($calendrier_formateur->lines[$j]->heured, 'hour'), 'dated');
								print ' - ' . $langs->trans("AgfPeriodTimeE") . ' ';
								print $formAgefodd->select_time(dol_print_date($calendrier_formateur->lines[$j]->heuref, 'hour'), 'datef');
								print '</td>' . "\n";
								print '<td>' . $formAgefodd->select_calendrier_status($calendrier_formateur->lines[$j]->status, 'calendar_trainer_status') . '</td>';

								// Trainer cost is fully managed into cost management not here
								if (empty($conf->global->AGF_ADVANCE_COST_MANAGEMENT)) {
									// Coût horaire
									print '<td width="20%"> <input type="text" size="10" name="trainer_cost" value="' . price($calendrier_formateur->lines[$i]->trainer_cost) . '"/>' . $langs->getCurrencySymbol($conf->currency) . '</td>' . "\n";
								}
								if ($user->rights->agefodd->modifier) {
									print '<td class="col-actions"><input type="submit" name="period_update_x" class="butAction" value=" &#128427; ' . $langs->trans("AgfModSave") .'"/>';
								}
							} else {
								print '<td width="1%;">';
								if ($user->rights->agefodd->modifier) {
									print '<input type="checkbox" name="deleteselcal[]" value="' . $calendrier_formateur->lines[$j]->id . '"/>';
								}
								print '</td>' . "\n";
								print '<td width="20%">' . dol_print_date($calendrier_formateur->lines[$j]->date_session, 'daytext') . '</td>' . "\n";
								$hourDisplay = dol_print_date($calendrier_formateur->lines[$j]->heured, 'hour') . ' - ' . dol_print_date($calendrier_formateur->lines[$j]->heuref, 'hour');
								$hourDisplay = _isTrainerFreeBadge($hourDisplay, $calendrier_formateur->lines[$j], $formateurs->lines[$i]->opsid);
								print '<td  width="40%">' . $hourDisplay  . '</td>';
								print '<td>' . Agefodd_sesscalendar::getStaticLibStatut($calendrier_formateur->lines[$j]->status, 0) . '</td>';

								// Trainer cost is fully managed into cost management not here
								if (empty($conf->global->AGF_ADVANCE_COST_MANAGEMENT)) {
									// Coût horaire
									print '<td>' . price($calendrier_formateur->lines[$j]->trainer_cost, 0, $langs) . ' ' . $langs->getCurrencySymbol($conf->currency) . '</td>' . "\n";
								}

								print '<td width="30%;">';
								if ($user->rights->agefodd->modifier) {
									print
										'<a href="' . dol_buildpath('/agefodd/session/person.php', 1) . '?action=edit_calendrier&amp;sessid=' . $id . '&amp;modperiod=' . $calendrier_formateur->lines[$j]->id . '&amp;trainerid=' . $formateurs->lines[$i]->formid . '&amp;id=' . $id . '&amp;period_edit=1">' . img_picto(
											$langs->trans("Edit"), 'edit') . '</a>' . "\n";
								}
								print '&nbsp;';
								if ($user->rights->agefodd->creer) {
									print
										'<a href="' . dol_buildpath('/agefodd/session/person.php', 1) . '?action=edit_calendrier&amp;sessid=' . $id . '&amp;modperiod=' . $calendrier_formateur->lines[$j]->id . '&amp;trainerid=' . $formateurs->lines[$i]->formid . '&amp;id=' . $id . '&amp;period_remove=1">' . img_picto(
											$langs->trans("Delete"), 'delete') . '</a>' . "\n";
								}
								print '</td>' . "\n";
							}

							// We calculated the total session duration time
							$duree += ($calendrier_formateur->lines[$j]->heuref - $calendrier_formateur->lines[$j]->heured);

							print '</tr>' . "\n";
						}

						// Fiels for new periodes
						if (! empty($newperiod)) {
							print '<td align="right">';
							print '<form name="newperiod" action="' . $_SERVER['PHP_SELF'] . '?action=edit_calendrier&id=' . $id . '"  method="POST">' . "\n";
							print '<input type="hidden" name="token" value="' . $newToken . '">' . "\n";
							print '<input type="hidden" name="action" value="edit_calendrier">' . "\n";
							print '<input type="hidden" name="newperiod" value="1">' . "\n";
							print '<input type="submit" class="butAction" value="' . $langs->trans("AgfPeriodAdd") . '">' . "\n";
							print '</form>' . "\n";
							print '</td>' . "\n";
						} else {
							if ($action == "edit_calendrier" && GETPOST('rowf', 'none') == $formateurs->lines[$i]->formid) {
								print '<tr>';
								print '<td></td>';
								print '<td  width="300px">';
								print '<input type="hidden" name="token" value="'.$newToken.'">';
								print '<input type="hidden" name="action" value="edit_calendrier">' . "\n";
								print '<input type="hidden" name="sessid" value="' . $agf->id . '">' . "\n";
								print '<input type="hidden" name="fk_agefodd_session_formateur" value="' . $formateurs->lines[$i]->opsid . '">' . "\n";
								print '<input type="hidden" name="periodid" value="' . $calendrier_formateur->lines[$j]->stagerowid . '">' . "\n";
								print '<input type="hidden" name="trainerid" value="' . $formateurs->lines[$i]->formid . '">' . "\n";
								print '<input type="hidden" id="datetmplday"   name="datetmplday"   value="' . dol_print_date($agf->dated, "%d") . '">' . "\n";
								print '<input type="hidden" id="datetmplmonth" name="datetmplmonth" value="' . dol_print_date($agf->dated, "%m") . '">' . "\n";
								print '<input type="hidden" id="datetmplyear"  name="datetmplyear"  value="' . dol_print_date($agf->dated, "%Y") . '">' . "\n";
								$form->select_date($agf->dated, 'date', '', '', '', 'newperiod');
								print '</td>';
								print '<td width="400px">' . $langs->trans("AgfPeriodTimeB") . ' ';
								print $formAgefodd->select_time('08:00', 'dated');
								print $langs->trans("AgfPeriodTimeE") . ' ';
								print $formAgefodd->select_time('18:00', 'datef');
								print '</td>';
								print '<td>' . $formAgefodd->select_calendrier_status($conf->global->AGF_DEFAULT_TRAINER_CALENDAR_STATUS, 'calendar_trainer_status') . '</td>';
								// Trainer cost is fully managed into cost management not here
								if (empty($conf->global->AGF_ADVANCE_COST_MANAGEMENT)) {
									// Coût horaire
									print '<td width="20%"><input type="text" size="10" name="trainer_cost" /></td>';
								}
								if ($user->rights->agefodd->modifier) {
									print '<td class="col-actions"><input type="submit" name="period_add_x" class="butAction" value=" &#128427; ' . $langs->trans("AgfModSave") .'"/>';
								}

								print '</tr>' . "\n";
								print '<tr><td colspan="6"><input class="button" type="submit" value="' . $langs->trans('AgfEraseWithSessionCalendar') . '" name="copysessioncalendar"></td></tr>' . "\n";
							} else {
								print '<tr><td colspan="6"><a href="' . $_SERVER['PHP_SELF'] . '?action=edit_calendrier&amp;id=' . $agf->id . '&amp;rowf=' . $formateurs->lines[$i]->formid . '">' . "\n";
								print img_picto($langs->trans("Add"), dol_buildpath('/agefodd/img/new.png', 1), '', true, 0) . '</a>' . "\n";
								print '</td></tr>';
							}
						}
						print '</table>' . "\n";
						print '</form>' . "\n";
						// print '</td>' . "\n";
					}
				}

				if (getDolGlobalInt('AGF_DISPLAY_SIGNATURE_TRAINEE') && !empty($session->array_options['options_agefodd_onlinesign'])){
					printTDSignatureContent($calendrier, $agf, $formateurs->lines[$i]->formid, 'trainer');
				}
				print '</td>';

				print "</tr>\n";
			}
		}
		print "</table>" . "\n";
	}
}

/*
 * Action tabs
 *
 */

print '<div class="tabsAction">';

if ($action != 'create' && $action != 'edit' && $action != 'edit_subrogation'&& $action != 'presend' && (! empty($agf->id)) && $nbform >= 1) {
	if ($user->rights->agefodd->modifier) {
		print '<a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?action=edit&amp;id=' . $id . '">' . $langs->trans('Cancel') . '</a>';
	} else {
		print '<a class="butActionRefused" href="#" title="' . dol_escape_htmltag($langs->trans("NotAllowed")) . '">' . $langs->trans('Modify') . '</a>';
	}

	if (! $user->rights->agefodd->session->trainer) {
		if ($user->rights->agefodd->modifier) {
			print '<a class="butAction" href="' . dol_buildpath('/agefodd/session/send_docs.php', 1) . '?id=' . $id . '&action=presend_trainer_doc&mode=init">' . $langs->trans('AgfSendDocuments') . '</a>';
		} else {
			print '<a class="butActionRefused" href="#" title="' . dol_escape_htmltag($langs->trans("NotAllowed")) . '">' . $langs->trans('AgfSendDocuments') . '</a>';
		}
	}
}
if (($action == 'edit' || $action == 'edit_subrogation') && $newform_var < 1) {
	if (empty($user->rights->agefodd->session->trainer)) {
		if (!empty($user->rights->agefodd->modifier)) {
			print '<a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?action=edit&amp;id=' . $id . '&newform=1">' . $langs->trans("AgfFormateurAdd") . '</a>';
		} else {
			print '<a class="butActionRefused" href="#" title="' . dol_escape_htmltag($langs->trans("NotAllowed")) . '">' . $langs->trans('Modify') . '</a>';
		}
	}

	if (empty($user->rights->agefodd->session->trainer)) {
		if (!empty($user->rights->agefodd->modifier)) {
			print '<a class="butAction" href="' . dol_buildpath('/agefodd/session/send_docs.php', 1) . '?id=' . $id . '&action=presend_trainer_doc&mode=init">' . $langs->trans('AgfSendDocuments') . '</a>';
		} else {
			print '<a class="butActionRefused" href="#" title="' . dol_escape_htmltag($langs->trans("NotAllowed")) . '">' . $langs->trans('AgfSendDocuments') . '</a>';
		}
	}
}
if ($action == 'edit' && $newform_var >= 1) {
	print '<a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?action=edit&amp;id=' . $id . '">' . $langs->trans('Cancel') . '</a>';
}
print '</div>';


function _isTrainerFreeBadge($hourDisplay, $line, $fk_trainer)
{
	global $langs;

	$errorsStatus = $warningsStatus = 'default';
	if($line->status != Agefoddsessionformateurcalendrier::STATUS_DRAFT){
		$warningsStatus = array();
	}

	$isTrainerFree = Agefoddsessionformateurcalendrier::isTrainerFree($fk_trainer, $line->heured, $line->heuref, $line->id, $errorsStatus, $warningsStatus);
	if(!$isTrainerFree->isFree)
	{
		if($isTrainerFree->errors > 0){
			$hourDisplay = '<span class="classfortooltip badge badge-danger" title="'.$langs->trans('TrainerNotFree').'" ><i class="fa fa-exclamation-circle"></i> '.$hourDisplay .'</span>';
		} elseif ($isTrainerFree->warnings > 0){
			$hourDisplay = '<span class="classfortooltip badge badge-warning" title="'.$langs->trans('TrainerCouldBeNotFree').'" ><i class="fa fa-exclamation-triangle"></i> '.$hourDisplay .'</span>';
		}
	}

	return $hourDisplay;
}

/**
 * Affiche le lien vers la page de signature des créneaux et le récapitulatif du nombre de créneaux déjà signés sur le nombre total
 *
 * @param	Object		$calendrier_session	Liste des créneaux pour cette session
 * @param	Object		$session			Objet session
 * @param	int 		$fk_person			ID du formateur ou stagiaire
 * @param	int			$person_type		Type (trainee ou trainer)
 * @return void
 */
function printTDSignatureContent(&$calendrier_session, &$session, $fk_person, $person_type) {

	global $db, $langs;

	$signature = new AgefoddSignature($db);
	$res_array = $signature->fetchAll('', '', 0, 0, ['customsql' => ' fk_session = '.((int)$session->id).' AND fk_person = '.((int)$fk_person).' AND person_type = "'.$person_type.'" ']);
	print '<td class="liste_nb_sign">';
	$can_sign=true;
	$link = dol_buildpath('/agefodd/session/signature_page.php', 1) . '?id=' . $session->id . '&amp;personid=' . $fk_person . '&amp;person_type='.$person_type;
	$title = $langs->trans("ClicTosign", $person_type === 'trainer' ? strtolower($langs->trans('AgfTrainer')) : strtolower($langs->trans('AgfFichePresByTraineeTraineeTitleM')));
	if(in_array($session->status, array(Agsession::STATUS_ARCHIVED, Agsession::STATUS_REALISED, Agsession::STATUS_NOT_REALISED))) {
		$can_sign=false;
		$link = '#';
		$title = $langs->trans('AgfNotSignatureWithThisStatusSession');
	}
	print '<a href="' . $link . '" '.(!$can_sign ? 'onclick="return false;"' : '').'>' . img_picto($title, 'agefodd_signature@agefodd','class="pictofixedwidth"') . '</a>' . "\n";
	print '&nbsp<span title="'.$langs->trans('SignaturesDetail', count($res_array), count($calendrier_session->lines)).'">('.count($res_array).'/'.count($calendrier_session->lines).')</span>';
	print '</td>' . "\n";

}

print '<br><hr><br>';

/*
 * View Subscribers
 */

if (! empty($id)) {
	$agf_opca = new Agefodd_opca($db);

	print load_fiche_titre($langs->trans("AgfMenuActStagiaire"), '', '');

	if ($action == 'edit_subrogation' || $action == 'presend') {
		print '<script type="text/javascript">
						jQuery(document).ready(function () {
							jQuery(function() {
								$(\'html, body\').animate({scrollTop: $("#add_subrogation").offset().top}, 500,\'easeInOutCubic\');
							});
						});
						</script> ';
	}

	if ($action == 'edit' || $action === 'edit_calendrier' || $action == 'presend') {

		// Put user on the right action block after reload
		if (! empty($modstagid) && $action == 'edit' && empty($stag_remove_x)) {
			print '<script type="text/javascript">
					jQuery(document).ready(function () {
						jQuery(function() {
							$(\'html, body\').animate({scrollTop: $("#modstagid' . $modstagid . '").offset().top-150}, 500,\'easeInOutCubic\');
						});
					});
					</script> ';
		} elseif (! empty($newstag)) {
			print '<script type="text/javascript">
					jQuery(document).ready(function () {
						jQuery(function() {
							$(\'html, body\').animate({scrollTop: $("#search_stagiaire").offset().top-50}, 500,\'easeInOutCubic\');
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
			// Scroll par défaut en bas de la page, n'a plus de sens maintenant que les formateurs et les participants sont sur la même page
//			print '<script type="text/javascript">
//					jQuery(document).ready(function () {
//						jQuery(function() {
//							$(\'html, body\').animate({scrollTop: $("#modsta").offset().top}, 500,\'easeInOutCubic\');
//						});
//					});
//					</script> ';
		}

		/*
		 * Confirm delete
		 */
		if ($stag_remove_x) {
			// Param url = id de la ligne stagiaire dans session - id session
			print $form->formconfirm($_SERVER['PHP_SELF'] . "?stagerowid=" . GETPOST('stagerowid', 'int') . '&id=' . $id, $langs->trans("AgfDeleteStag"), $langs->trans("AgfConfirmDeleteStag"), "confirm_delete_stag", '', '', 1);
		}

//		print '<div class="underbanner clearboth"></div>';

		if (is_array($agf->array_options) && key_exists('options_use_subro_inter', $agf->array_options) && ! empty($agf->array_options['options_use_subro_inter'])) {
			$agf->type_session = 1;
		}

		/*
		 * Manage funding for intra enterprise
		 */
		if (! $agf->type_session > 0 && ! empty($conf->global->AGF_MANAGE_OPCA)) {
//			print '&nbsp';
			print '<table class="noborder" width="100%">';
			print '<tr class="liste_titre">';
			print '<th colspan="2">'.$langs->trans('BlocSubrogation').'</th>';
			print '</tr>';
			print '<tr><td>' . $langs->trans("AgfSubrocation") . '</td>';
			if ($agf->is_OPCA == 1) {
				$isOPCA = ' checked="checked" ';
			} else {
				$isOPCA = '';
			}
			print '<td><input type="checkbox" class="flat" disabled="disabled" readonly="readonly" ' . $isOPCA . '/></td></tr>';

			print '<tr><td width="20%">' . $langs->trans("AgfOPCAName") . '</td>';
			print '	<td>';
			if (floatval(DOL_VERSION) < 6.0) {
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

			print '<td>';
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
		if (! empty($conf->global->AGF_USE_REAL_HOURS) && $edithours) {
			print '<br><form id="editrealhours" name="editrealhours" action="' . $_SERVER['PHP_SELF'] . '?action=editrealhours&id=' . $id . '"  method="POST">' . "\n";
			print '<input type="hidden" name="token" value="'.$newToken.'">';
			print '<input type="hidden" name="action" value="editrealhours">';

			$calendrier = new Agefodd_sesscalendar($db);
			$calendrier->fetch_all($agf->id);
			$blocNumber = count($calendrier->lines);
			$dureeCalendrier = 0;
			foreach ( $calendrier->lines as $horaire ) {
				if (in_array($horaire->status,$calendrier->statusCountTime)) {
					$dureeCalendrier += ($horaire->heuref - $horaire->heured) / 3600;
				}
			}

			print '<table class="noborder" width="100%">';
			print '<tr class="liste_titre">';
			print '<th>' . $langs->trans('AgfParticipants') . '</th><th colspan="' . $blocNumber . '" align="center">' . $langs->trans('AgfSchedules') . '</th><th align="center">' . $langs->trans('AgfTraineeHours') . '</th>';
			print '</tr>';
			print '<tr class="liste_titre"><th></th>';
			if ($blocNumber > 0) {
				for($i = 0; $i < $blocNumber; $i ++) {
					print '<th align="center">' . dol_print_date($calendrier->lines[$i]->date_session, '%d/%m/%Y') . '<br>' . dol_print_date($calendrier->lines[$i]->heured, 'hour');
					print ' - ' . dol_print_date($calendrier->lines[$i]->heuref, 'hour');
					print (!empty($calendrier->lines[$i]->calendrier_type_label) ? "<br>" . $calendrier->lines[$i]->calendrier_type_label : "");
					print "<br>" . Agefodd_sesscalendar::getStaticLibStatut($calendrier->lines[$i]->status) . '</th>';
				}
			} else {
				print '<th align="center">' . $langs->trans("AgfNoCalendar") . '</th>';
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
				if ($blocNumber > 0) {
					for($j = 0; $j < $blocNumber; $j ++) {
						$defaultvalue = ($calendrier->lines[$j]->heuref - $calendrier->lines[$j]->heured) / 3600;
						$warning = false;
						$result = $agfssh->fetch_by_session($id, $stagiaires->lines[$i]->id, $calendrier->lines[$j]->id);
						if ($calendrier->lines[$j]->date_session < dol_now()) {
							if ($result > 0) {
								$val = $agfssh->heures;
							} else {
								$val = $defaultvalue;
								$warning = true;
								if ($stagiaires->lines[$i]->status_in_session==Agefodd_session_stagiaire::STATUS_IN_SESSION_NOT_PRESENT) {
									$val='';
									$warning = false;
								}

							}
						} else {
							$val = '';
						}

						print '<td align="center">';
						print '<input name="realhours[' . $stagiaires->lines[$i]->id . '][' . $calendrier->lines[$j]->id . ']" ';
						print '	   type="text" size="5" value="' . $val . '" data-default="' . (($calendrier->lines[$j]->date_session < dol_now()) ? $defaultvalue : 0) . '"';
						print (($calendrier->lines[$j]->date_session >= dol_now() || $calendrier->lines[$j]->status==Agefodd_sesscalendar::STATUS_DRAFT) ? 'disabled' : '');
						print '>';
						print ($warning ? img_warning($langs->trans('AgfWarningTheoreticalValue')) : '');
						print ($calendrier->lines[$j]->status==Agefodd_sesscalendar::STATUS_DRAFT ? img_info($langs->trans('AgfTimeInfoStatusDraft')) : '');
						print ($calendrier->lines[$j]->date_session >= dol_now() ? img_info($langs->trans('AgfTimeInfoStatusFuture')) : '');
						print '</td>';

					}
				} else {
					print '<td align="center">' . (($i == 0) ? $langs->trans("AgfNoCalendar") : '') . '</td>';
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

//		print '<div class="tabBar">';

		/*
		 *  Block update trainne info
		 *
		 */

		$langs->load('agfexternalaccess@agefodd');
		print '<form name="obj_update" action="' . $_SERVER['PHP_SELF'] . '?action=edit&id=' . $id . '"  method="POST">' . "\n";
		print '<table class="noborder centpercent agf-trainee-table-list"  >';

		print '<tr class="liste_titre">';

		print '<th></th>';
		print '<th>'.$langs->trans('Name').'</th>';
		print '<th>'.$langs->trans('AgfStatus').'</th>';
		print '<th>'.$langs->trans('AgfDateAdd').'</th>';
		print '<th>'.$langs->trans('AgfAddedBy').'</th>';
		print '<th>'.$langs->trans('Company').'</th>';
		if (! empty($conf->global->AGF_USE_STAGIAIRE_TYPE)) {
			print '<th>'.$langs->trans('AgfPublicTrainee').'</th>';
		}

		print '<th></th>';
		//picto.png@mymodule
		// HEADER PARTICIPANTS col signatures et nb signé
		if (getDolGlobalInt('AGF_DISPLAY_SIGNATURE_TRAINEE') && !empty($session->array_options['options_agefodd_onlinesign'])){
			print '<th nowrap class="liste_titre liste_nb_sign">'. $langs->trans("nbsignature") . '</th>'  . "\n";
		}
		if(getDolGlobalInt('AGF_USE_REAL_HOURS')) {
			print '<th></th>';
		}
		print '<th></th>';

		print '</tr>';

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
			$var = false;
			for($i = 0; $i < $nbstag; $i ++) {
				$var = ! $var;
				$lineClass = '';

				$thisLineIsInEditMode = false;
				if ($stagiaires->lines[$i]->id == $modstagid && empty($stag_remove_x)) {
					$thisLineIsInEditMode = true;
					$lineClass = ' --editmode';
				}

				print '<tr class="oddeven agf-trainee-line '.$lineClass.'">';

				print '<td class="col-line-number"><a name="modsta" id="modsta"></a><a name="modstagid' . $stagiaires->lines[$i]->id . '" id="modstagid' . $stagiaires->lines[$i]->id . '"></a>' . ($i + 1) . '</td>';

				if ($thisLineIsInEditMode) {
					$colsp=6;
					if(getDolGlobalInt('AGF_DISPLAY_SIGNATURE_TRAINEE') && !empty($session->array_options['options_agefodd_onlinesign'])) $colsp++;
					if(getDolGlobalInt('AGF_USE_REAL_HOURS')) $colsp++;
					print '<td colspan="'.$colsp.'">';
					print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">' . "\n";
					print '<input type="hidden" name="sessid" value="' . $stagiaires->lines[$i]->sessid . '">' . "\n";
					print '<input type="hidden" name="stagerowid" value="' . $stagiaires->lines[$i]->stagerowid . '">' . "\n";
					print '<input type="hidden" name="modstagid" value="' . $stagiaires->lines[$i]->id . '">' . "\n";
					print '<input type="hidden" name="fk_soc_trainee" value="' . $stagiaires->lines[$i]->socid . '">' . "\n";


					print '<table >';
					print '<tbody>';

					print '<tr id="traineeselect">';
					print '		<td class="col-title">';
					print '			<label for="stagiaire">' . $langs->trans('AgfSelectStagiaire') . '</label>';
					print '		</td>';
					print '		<td class="col-input">';
					print '<i class="fa fa-search" ></i>';
					print $formAgefodd->select_stagiaire($stagiaires->lines[$i]->id, 'stagiaire', '(s.rowid NOT IN (SELECT fk_stagiaire FROM ' . MAIN_DB_PREFIX . 'agefodd_session_stagiaire WHERE fk_session_agefodd=' . $id . ')) OR (s.rowid=' . $stagiaires->lines[$i]->id . ')');
					print '		</td>';
					print '</tr>';


					print '<tr>';
					print '		<td class="col-title">';
					print $langs->trans('Status');
					print '		</td>';
					print '		<td class="col-input">';
					if (empty($conf->global->AGF_SESSION_TRAINEE_STATUS_AUTO) || $agf->datef <= dol_now()) {
						print $formAgefodd->select_stagiaire_session_status('stagiaire_session_status', $stagiaires->lines[$i]->status_in_session, $agf);
					} else {
						print $stagiaires->LibStatut($stagiaires->lines[$i]->status_in_session, 4);
						print '<input type="hidden" name="stagiaire_session_status" value="' . dol_htmlentities($stagiaires->lines[$i]->status_in_session,ENT_QUOTES) . '">';
					}
					print '		</td>';
					print '</tr>';


					print '<tr>';
					print '		<td class="col-title">';
					print  $langs->trans('AgfSessionTraineeComment');
					print '		</td>';
					print '		<td class="col-input">';
					print '<input type="text" class="flat" id="comment" name="comment" value="' . dol_htmlentities($stagiaires->lines[$i]->comment,ENT_QUOTES) . '" />';
					print '		</td>';
					print '</tr>';



					print '<tr>';
					print '		<td class="col-title">';
					print  $langs->trans('AgfTraineeSocDocUse') . ' ';
					print '		</td>';
					print '		<td class="col-input">';
					print $form->select_company($stagiaires->lines[$i]->fk_soc_link, 'fk_soc_link', '', 'SelectThirdParty', 1, 0);
					print '		</td>';
					print '</tr>';

					print '<tr>';
					print '		<td class="col-title">';
					print  $langs->trans('AgfTypeRequester') . ' ';
					print '		</td>';
					print '		<td class="col-input">';
					print $form->select_company($stagiaires->lines[$i]->fk_soc_requester, 'fk_soc_requester', '', 'SelectThirdParty', 1, 0);
					print '		</td>';
					print '</tr>';



					if (! empty($conf->global->AGF_MANAGE_BPF)) {
						print '<tr>';
						print '		<td class="col-title">';
						print  $langs->trans('AgfHourFOAD') . ' ';
						print '		</td>';
						print '		<td class="col-input">';
						print '<input size="4" type="text" class="flat" id="hour_foad" name="hour_foad" value="' . $stagiaires->lines[$i]->hour_foad . '" />';
						print '		</td>';
						print '</tr>';
					}

					if ($agf->type_session == 1) {
						print '<tr>';
						print '		<td class="col-title">';
						print $langs->trans('AgfContactSign') . ' ';
						print '		</td>';
						print '		<td class="col-input">';
						// todo change deprecated function
						$form->select_contacts($stagiaires->lines[$i]->socid, (! empty($fk_socpeople_sign) ? $fk_socpeople_sign : $stagiaires->lines[$i]->fk_socpeople_sign), 'fk_socpeople_sign', 1, '', '', 1, '', 1);
						print '		</td>';
						print '</tr>';
					}


					print '<tbody>';
					print '</table>';



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
						$htmlname_thirdparty='fksocOPCA';
						print $form->select_company($agf_opca->fk_soc_OPCA, $htmlname_thirdparty, '(s.client IN (1,2))', 'SelectThirdParty', 1, 0);
						$events[]=array('showempty' => 1, 'method' => 'getContacts', 'url' => dol_buildpath('/core/ajax/contacts.php',1), 'htmlname' => 'fksocpeopleOPCA', 'params' => array('add-customer-contact' => 'disabled'));
						//Select contact regarding comapny
						if (count($events))
						{

							print '<script type="text/javascript">

								jQuery(document).ready(function() {
									$("#'.$htmlname_thirdparty.'").change(function() {
										var obj = '.json_encode($events).';
										$.each(obj, function(key,values) {
											if (values.method.length) {
												runJsCodeForEvent'.$htmlname_thirdparty.'(values);
											}
										});
										/* Clean contact */
										$("div#s2id_contactid>a>span").html(\'\');
									});

									// Function used to execute events when search_htmlname change
									function runJsCodeForEvent'.$htmlname_thirdparty.'(obj) {
										var id = $("#'.$htmlname_thirdparty.'").val();
										var method = obj.method;
										var url = obj.url;
										var htmlname = obj.htmlname;
										var showempty = obj.showempty;
										console.log("Run runJsCodeForEvent-'.$htmlname_thirdparty.' from selectCompaniesForNewContact id="+id+" method="+method+" showempty="+showempty+" url="+url+" htmlname="+htmlname);
										$.getJSON(url,
											{
												action: method,
												id: id,
												htmlname: htmlname,
												showempty: showempty
											},
											function(response) {
												if (response != null)
												{
													console.log("Change select#"+htmlname+" with content "+response.value)
													$.each(obj.params, function(key,action) {
														if (key.length) {
															var num = response.num;
															if (num > 0) {
																$("#" + key).removeAttr(action);
															} else {
																$("#" + key).attr(action, action);
															}
														}
													});
													$("select#" + htmlname).html(response.value);
												}
											}
										);
									};
								});
								</script>';
						}
						if (! empty($agf_opca->fk_soc_OPCA) && ! empty($conf->global->COMPANY_USE_SEARCH_TO_SELECT)) {
							print
								'<a href="' . $_SERVER['PHP_SELF'] . '?sessid=' . $agf->id . '&amp;action=remove_opcafksocOPCA&amp;stagerowid=' . $stagiaires->lines[$i]->stagerowid . '&amp;fk_soc_trainee=' . $stagiaires->lines[$i]->socid . '&amp;modstagid=' . $stagiaires->lines[$i]->id . '&amp;token='.$newToken.'">' . img_delete(
									$langs->trans('Delete')) . '</a>';
						}
						print '</td></tr>';

						print '<tr><td>' . $langs->trans("AgfOPCAContact") . '</td>';
						print '	<td>';
						$form->select_contacts(($agf_opca->fk_soc_OPCA > 0 ? $agf_opca->fk_soc_OPCA : -1), $agf_opca->fk_socpeople_OPCA, 'fksocpeopleOPCA', ((floatval(DOL_VERSION) < 8.0)?1:3), '', '', 0, 'minwidth100imp');
						print '</td></tr>';

						print '<tr><td width="20%">' . $langs->trans("AgfOPCANumClient") . '</td>';
						print '<td><input size="30" type="text" class="flat" name="numOPCAsoc" value="' . $agf_opca->num_OPCA_soc . '" /></td></tr>';

						print '<tr><td width="20%">' . $langs->trans("AgfOPCADateDemande") . '</td>';

						print '<td><table class="nobordernopadding"><tr>';
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
						print '</td><td valign="top">' . $langs->trans('AgfPublicTrainee') . ' ' . $formAgefodd->select_type_stagiaire($stagiaires->lines[$i]->typeid, 'stagiaire_type', '', 1);
					}
					if ($user->rights->agefodd->modifier) {
						print '</td><td class="col-actions"><input type="submit" name="stag_update_x" class="butAction" value=" &#128427; ' . $langs->trans("AgfModSave") .'"/>';
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
						$contact_static->civility_code = $stagiaires->lines[$i]->civilite;
						$trainee_info .= ' (' . $contact_static->getCivilityLabel() . ')';

						if ($agf->type_session == 1 && ! empty($conf->global->AGF_MANAGE_OPCA)) {
							print '<table class="nobordernopadding" width="80%"><tr class="noborder"><td>';
							print $trainee_info;
							print '</td>';
							print '</tr>';

							$agf_opca->getOpcaForTraineeInSession($stagiaires->lines[$i]->socid, $agf->id, $stagiaires->lines[$i]->stagerowid);
							print '<tr class="noborder"><td  class="noborder" width="100%">' . $langs->trans("AgfSubrocation") . '</td>';
							if ($agf_opca->is_OPCA == 1) {
								$chckisOPCA = 'checked="checked"';
							} else {
								$chckisOPCA = '';
							}
							print '<td><input type="checkbox" class="flat" name="isOPCA" value="1" ' . $chckisOPCA . '" disabled="disabled" readonly="readonly"/></td></tr>';

							print '<tr><td>' . $langs->trans("AgfOPCAName") . '</td>';
							print '	<td>';
							if (floatval(DOL_VERSION) < 6.0) {
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

							print '<td><table class="nobordernopadding"><tr>';
							print '<td>';
							print dol_print_date($agf_opca->date_ask_OPCA, 'daytext');
							print '</td><td>';
							print '</td></tr></table>';
							print '</td></tr>';

							print '<tr><td width="20%">' . $langs->trans("AgfOPCANumFile") . '</td>';
							print '<td>' . $agf_opca->num_OPCA_file . '</td></tr>';

							print '</table>';
						} else {
							print $trainee_info;
							if (! empty($stagiaires->lines[$i]->hour_foad)) {
								print '<br>' . $langs->trans('AgfHourFOAD') . ' : ' . $stagiaires->lines[$i]->hour_foad . ' ' . $langs->trans('Hour') . '(s)';
							}
							if (!empty($stagiaires->lines[$i]->comment)) {
								print '<br>' . $langs->trans('AgfSessionTraineeComment') . ' : ' . $stagiaires->lines[$i]->comment;
							}
						}
					}
					print '</td>';

					print '<td nowrap>'.$stagiaires->LibStatut($stagiaires->lines[$i]->status_in_session, 4).'</td>';

					$sql = "SELECT fk_stagiaire AS id_stagiaire, fk_session_agefodd AS session, datec, fk_user_author";
					$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session_stagiaire";
					$sql .= " WHERE fk_stagiaire = " . $stagiaires->lines[$i]->id;
					$sql .= " AND fk_session_agefodd = " . $stagiaires->lines[$i]->sessid;
					$resql = $db->query($sql);
					if ($resql) {
						$obj = $db->fetch_object($resql);
						//Récupération des données du tiers ayant inscript le participant
						$userAuthor = new User($db);
						$userAuthor->fetch($obj->fk_user_author);
						print '<td width="10%">' . date('d-m-Y H:m:s', strtotime($obj->datec)) . '</td>';
						print '<td width="10%">' . $userAuthor->getNomUrl(1) . '</td>';
					} else {
						dol_print_error($db);
					}

					print '<td width="10%" style="border-left: 0px;">';
					// Display thridparty link with trainee
					if (! empty($stagiaires->lines[$i]->socid)) {
						$socstatic = new Societe($db);
						$socstatic->fetch($stagiaires->lines[$i]->socid);
						if (! empty($socstatic->id)) {
							print $socstatic->getNomUrl(1);
						}
					} else {
						print '&nbsp;';
					}
					if (! empty($conf->global->AGF_USE_STAGIAIRE_TYPE)) {
						print '</td><td width="15%" style="border-left: 0px;" class="traineefin">' . stripslashes($stagiaires->lines[$i]->type);
					}
					print '</td>';

					// Infos thirdparty linked for doc
					print '<td width="15%" style="border-left: 0px;" class="traineefk_soc_link">';
					if (! empty($stagiaires->lines[$i]->fk_soc_link)) {
						$socstatic = new Societe($db);
						$socstatic->fetch($stagiaires->lines[$i]->fk_soc_link);
						if (! empty($socstatic->id)) {
							print '<div>' . $langs->trans('AgfTraineeSocDocUse') . ' :<br/>' . $socstatic->getNomUrl(1) . '</div><br/>';
						}
					}

					if (! empty($stagiaires->lines[$i]->fk_soc_requester)) {
						$socstatic = new Societe($db);
						$socstatic->fetch($stagiaires->lines[$i]->fk_soc_requester);
						if (! empty($socstatic->id)) {
							print '<div>' . $langs->trans('AgfTypeRequester') . ' :<br/>' . $socstatic->getNomUrl(1) . '</div><br/>';
						}
					}

					if (! empty($stagiaires->lines[$i]->fk_socpeople_sign)) {
						$contactstatic = new Contact($db);
						$contactstatic->fetch($stagiaires->lines[$i]->fk_socpeople_sign);
						if (! empty($contactstatic->id)) {
							print '<div>' . $langs->trans('AgfContactSign') . ' :<br/>' . $contactstatic->getNomUrl(1) . '</div><br/>';
						}
					}
					// PARTICIPANT ligne date col signatures et nb signé
					if (getDolGlobalInt('AGF_DISPLAY_SIGNATURE_TRAINEE') && !empty($session->array_options['options_agefodd_onlinesign'])){
						printTDSignatureContent($calendrier, $agf, $stagiaires->lines[$i]->id, 'trainee');
					}
					print '</td>';
					if (! empty($conf->global->AGF_USE_REAL_HOURS)) {
						require_once ('../class/agefodd_session_stagiaire_heures.class.php');
						$agfssh = new Agefoddsessionstagiaireheures($db);
						print '<td class="col-trainee-time nowrap"><span class="classfortooltip" title="' . $langs->trans('AgfTraineeHours') . '" ><i class="fa fa-clock"></i>&nbsp;<strong>' . $agfssh->heures_stagiaire($id, $stagiaires->lines[$i]->id) . '</strong></span></td>';
					}

					print '<td class="col-actions">';
					if ($user->rights->agefodd->creer || $user->rights->agefodd->modifier) {
						print '<a class="edit-link"  href="' . $_SERVER['PHP_SELF'] . '?action=edit&id=' . $id . '&modstagid=' . $stagiaires->lines[$i]->id . '">' . img_picto($langs->trans("Edit"), 'edit') . '</a>';
					}

					if ($user->rights->agefodd->creer || $user->rights->agefodd->modifier) {
						print '<a class="delete-link" href="' . $_SERVER['PHP_SELF'] . '?action=edit&id=' . $id . '&modstagid=' . $stagiaires->lines[$i]->id . '&stag_remove=1&stagerowid=' . $stagiaires->lines[$i]->stagerowid . '">' . img_picto($langs->trans("Delete"), 'delete') . '</a>';
					}
					print '</td>' . "\n";

				}

				print '</tr>' . "\n";
			}
		}

		print '</table>';



		// New trainee
		if (! empty($newstag) && $action != 'presend') {
			print '<div class="new-trainee-form-container">';
			$token = $_SESSION['newtoken'];
			if(function_exists('newToken')) {
				$token = newToken();
			}

			// If session are intra entreprise then send Socid on create trainee
			if ($agf->type_session == 0 && ! empty($agf->fk_soc)) {
				$param_socid = '&societe=' . $agf->fk_soc;
			} else {
				$param_socid = '';
			}

			print '<input type="hidden" name="token" value="' . $token . '">' . "\n";
			print '<input type="hidden" name="sessid" value="' . $agf->id . '">' . "\n";
			$fk_stagiaire = 0;
			if(!empty($stagiaires->lines[$i])) $fk_stagiaire = $stagiaires->lines[$i]->stagerowid;
			print '<input type="hidden" name="stagerowid" value="' . $fk_stagiaire . '">' . "\n";

			print '<h4 id="newstag" class="new-trainee-form-tile">'.$langs->trans('AgfStagiaireAdd').'</h4>';

			print '<table >';
			print '<tbody>';

			print '<tr id="traineeselect">';
			print '		<td class="col-title">';
			print '			<label for="stagiaire" style="display: inline-block;margin-left:5px;">' . $langs->trans('AgfSelectStagiaire') . '</label>';
			print '		</td>';
			print '		<td class="col-input">';
			print '<i class="fa fa-search" ></i> '.$formAgefodd->select_stagiaire('', 'stagiaire', 's.rowid NOT IN (SELECT fk_stagiaire FROM ' . MAIN_DB_PREFIX . 'agefodd_session_stagiaire WHERE fk_session_agefodd=' . $id . ')', 1);

			if (($user->rights->agefodd->creer || $user->rights->agefodd->modifier) && $agf->status != 4) {
				$tooltip = '<strong>'.$langs->trans('AgfNewParticipant').'</strong><br/>'.$langs->trans('AgfNewParticipantLinkInfo');
				print '<a class="classfortooltip" href="'.dol_buildpath('agefodd/trainee/card.php', 1).'?action=create' . $param_socid . '&session_id=' . $id . '&url_back=' . urlencode($_SERVER['PHP_SELF'] . '?action=edit&id=' . $id) . '" title="' . $tooltip . '"><span class="fa fa-plus-circle valignmiddle paddingleft" ></span></a>';
			}

			print '		</td>';
			print '</tr>';

			if (! empty($conf->global->AGF_USE_STAGIAIRE_TYPE)) {
				print '<tr id="traineeotherinfotype">';
				print '		<td class="col-title">';
				print $langs->trans('AgfPublicTrainee');
				print '		</td>';
				print '		<td class="col-input">';
				print $formAgefodd->select_type_stagiaire($conf->global->AGF_DEFAULT_STAGIAIRE_TYPE, 'stagiaire_type');
				print '		</td>';
				print '</tr>';
			}

			if (empty($conf->global->AGF_SESSION_TRAINEE_STATUS_AUTO) || $agf->datef <= dol_now()) {
				print '<tr id="traineeotherinfostatus">';
				print '		<td class="col-title">';
				print $langs->trans('Status');
				print '		</td>';
				print '		<td class="col-input">';
				print $formAgefodd->select_stagiaire_session_status('stagiaire_session_status', 0, $agf);
				print '		</td>';
				print '</tr>';
			}


			print '<tr id="traineeotherinfoDocUse">';
			print '		<td class="col-title">';
			print $langs->trans('AgfTraineeSocDocUse');
			print '		</td>';
			print '		<td class="col-input">';
			print $form->select_company(0, 'fk_soc_link', '', 'SelectThirdParty', 1, 0);
			print '		</td>';
			print '</tr>';



			print '<tr id="traineeotherinfoTypeRequester">';
			print '		<td class="col-title">';
			print $langs->trans('AgfTypeRequester');
			print '		</td>';
			print '		<td class="col-input">';
			print $form->select_company(0, 'fk_soc_requester', '', 'SelectThirdParty', 1, 0);
			print '		</td>';
			print '</tr>';

			if (! empty($conf->global->AGF_MANAGE_BPF)) {
				print '<tr id="traineeotherinfoManageBpf">';
				print '		<td class="col-title">';
				print $langs->trans('AgfHourFOAD');
				print '		</td>';
				print '		<td class="col-input">';
				$imputValue = GETPOST('hour_load', 'none');
				print '<input size="4" type="text" class="flat" id="hour_foad" name="hour_foad" value="' . dol_htmlentities($imputValue, ENT_QUOTES) . '" />';
				print '		</td>';
				print '</tr>';
			}





			print '<tr>';
			print '		<td class="col-title">';
			print $langs->trans('AgfSessionTraineeComment');
			print '		</td>';
			print '		<td class="col-input">';
			$imputValue = GETPOST('comment', 'alpha');
			print '<input type="text" maxlength="255" class="flat" id="comment" name="comment" value="' . dol_htmlentities($imputValue, ENT_QUOTES) . '" />';
			print '		</td>';
			print '</tr>';

			print '</tbody>';


			print '<tfoot>';

			if ($user->rights->agefodd->modifier) {
				print '<tr id="traineeothersubmit">';
				print '		<td class="col-title">';
				print '		</td>';
				print '		<td class="col-input">';
				print '<button class="button" type="submit" name="stag_add_x" value="1" >' . $langs->trans("AgfModAddStagiaire") . '</button>';
				print '<a class="button" href="' . $_SERVER['PHP_SELF'] . '?action=edit&id=' . $id . '" >' . $langs->trans("Cancel") . '</a>';

				print '		</td>';
				print '</tr>';
			}

			print '</tfoot>';

			print '</table>';

			print '</div>' . "\n";

		}



		print '</form>' . "\n";
		if (empty($newstag)) {
//			print '</div>';
			print '<br>';

			print '<div class="tabsAction">';
			if (($user->rights->agefodd->creer || $user->rights->agefodd->modifier) && $agf->status != 4) {
				print '<a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?action=edit&id=' . $id . '&newstag=1" " title="' . $langs->trans('AgfStagiaireAdd') . '">' . $langs->trans('AgfStagiaireAdd') . '</a>';
			}

			if (($user->rights->agefodd->creer || $user->rights->agefodd->modifier) && $agf->status != 4 && getDolGlobalInt('AGF_DISPLAY_SIGNATURE_TRAINEE') && !empty($session->array_options['options_agefodd_onlinesign'])) {
				print '<a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?id=' . $id . '&ref=' . $session->ref . '&action=presend&mode=init&token=' . newToken() . '&newstag=1#sendmail" " title="' . $langs->trans('AgfStagiaireSendEmailOnlineSign') . '">' . $langs->trans('AgfStagiaireSendEmailOnlineSign') . '</a>';
			}


			// If session are intra entreprise then send Socid on create trainee
			if ($agf->type_session == 0 && ! empty($agf->fk_soc)) {
				$param_socid = '&societe=' . $agf->fk_soc;
			} else {
				$param_socid = '';
			}

			if (($user->rights->agefodd->creer || $user->rights->agefodd->modifier) && $agf->status != 4) {
				print '<a class="butAction" href="'.dol_buildpath('agefodd/trainee/card.php', 1).'?action=create' . $param_socid . '&session_id=' . $id . '&url_back=' . urlencode($_SERVER['PHP_SELF'] . '?action=edit&id=' . $id) . '" title="' . $langs->trans('AgfNewParticipantLinkInfo') . '">' . $langs->trans('AgfNewParticipant') . '</a>';
			}

			if ($conf->global->AGF_MANAGE_OPCA) {
				if ($user->rights->agefodd->creer && ! $agf->type_session > 0) {
					if (($user->rights->agefodd->creer || $user->rights->agefodd->modifier) && $agf->status != 4) {
						print '<a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?action=edit_subrogation&id=' . $id . '">' . $langs->trans('AgfModifySubrogation') . '</a>';
					}
				} else {
					if ($agf->type_session)
						$title = ' / ' . $langs->trans('AgfAvailableForIntraOnly');
					if (($user->rights->agefodd->creer || $user->rights->agefodd->modifier) && $agf->status != 4) {
						print '<a class="butActionRefused" href="#" title="' . dol_escape_htmltag($langs->trans("NotAllowed")) . $title . '">' . $langs->trans('AgfModifySubrogation') . '</a>';
					}
				}
			}

			if (! empty($conf->global->AGF_USE_REAL_HOURS) && ! empty($agf->nb_stagiaire))
				if (($user->rights->agefodd->creer || $user->rights->agefodd->modifier) && $agf->status != 4) {
					print '<a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?action=edit&id=' . $id . '&edithours=true">' . $langs->trans('AgfModifyTraineeHours') . '</a>';
				}

			if ((empty($conf->global->AGF_SESSION_TRAINEE_STATUS_AUTO) || $agf->datef <= dol_now()) && $nbstag > 0 && ($user->rights->agefodd->creer || $user->rights->agefodd->modifier)) {
				print '<br><br>';
				print '<form name="add" action="' . $_SERVER['PHP_SELF'] . '?id=' . $id. '" method="POST">' . "\n";
				print '<input type="hidden" name="token" value="' . $newToken . '">';
				print '<input type="hidden" name="action" value="updatetraineestatus">' . "\n";
				$optionStatus='';
				$cal = new Agefodd_sesscalendar($db);
				$res = $cal->fetch_all($id);
				if ($res < 0) {
					setEventMessage($cal->error, 'errors');
				} else {
					if (is_array($cal->lines) && count($cal->lines)>0) {
						$dateToTest = $cal->lines[0]->heured;
					} else {
						$dateToTest = $agf->dated;
					}
				}

				foreach ($stagiaires->labelstatut_short as $statuskey => $statuslabelshort)
				{
					if($dateToTest >= dol_now() && in_array($statuskey, $stagiaires->statusAvalaibleForFuture)) {
						$optionStatus.= '<option value="'.$statuskey.'">'. $statuslabelshort.'</option>';
					} elseif ($dateToTest <= dol_now() && in_array($statuskey, $stagiaires->statusAvalaibleForPast)) {
						$optionStatus.= '<option value="'.$statuskey.'">'. $statuslabelshort;
						$optionStatus.='</option>';
					}
				}
				if (!empty($optionStatus)) {
					print '<select class="flat updatetraineestatus" name="statusinsession" id="statusinsession">';
					print $optionStatus;
					print '</select>';
					print img_warning($langs->trans('AgfWarnStatusLimited'));

					if (!empty($stagiaires->statusDeleteTime) && ! empty($conf->global->AGF_USE_REAL_HOURS)) {
						print '<div style="display:none" id="warningdelete">'.img_warning($langs->trans('AgfWarnTimeWillBeDelete')).$langs->trans('AgfWarnTimeWillBeDelete').'</div>';
						print '<script type="text/javascript">
								$(document).ready(function() {
								    var stawarning = ' . json_encode($stagiaires->statusDeleteTime) . ';
									$("#statusinsession").change(function() {
										if (stawarning.indexOf(parseInt($(this).val()))!==-1) {
											$("#warningdelete").show();
										} else {
											$("#warningdelete").hide();
										}
									});
								});
							</script>';
					}

					print '<input type="submit" class="butAction" name="changestatusinsession" value="' . $langs->trans('AgfSetTrainneStatusTo') . '">';
				}
				print '</form>';
			}

			print '</div>';
		} else {
			print '<br>';
			print '<div class="tabsAction">';
			if (!$newstag=1) {
				print '<a class="butAction" href="'.dol_buildpath('agefodd/trainee/card.php', 1).'?action=create' . $param_socid . '&session_id=' . $id . '&url_back=' . urlencode($_SERVER['PHP_SELF'] . '?action=edit&id=' . $id) . '" title="' . $langs->trans('AgfNewParticipantLinkInfo') . '">' . $langs->trans('AgfNewParticipant') . '</a>';
			}
			print '</div>';
		}
//		print '</div>';
	} else {
		// Display View mode

//		dol_agefodd_banner_tab($agf, 'id');
		print '<div class="underbanner clearboth"></div>';

		if (is_array($agf->array_options) && key_exists('options_use_subro_inter', $agf->array_options) && ! empty($agf->array_options['options_use_subro_inter'])) {
			$agf->type_session = 1;
		}

		/*
		 * Manage funding for intra-enterprise session
		 */
		if (! $agf->type_session > 0) {
			//Intra entreprise
			if ($action == "edit_subrogation" && $agf->type_session == 0 && ! empty($conf->global->AGF_MANAGE_OPCA)) {

//				print_barre_liste($langs->trans("AgfGestSubrocation"), "", "", "", "", "", '', 0);

				print '<form id="add_subrogation" name="add_subrogation" action="' . $_SERVER['PHP_SELF'] . '" method="POST">' . "\n";
				print '<input type="hidden" name="token" value="' . $newToken . '">';
				print '<input type="hidden" name="update_subrogation" value="1">';
				print '<input type="hidden" name="id" value="' . $agf->id . '">';
				print '<table class="noborder" width="100%">';
				print '<tr class="liste_titre">';
				print '<th colspan="2">'.$langs->trans('BlocSubrogation').'</th>';
				print '</tr>';
				print '<tr><td style="min-width:20%">' . $langs->trans("AgfSubrocation") . '</td>';
				if ($agf->is_OPCA == 1) {
					$chckisOPCA = 'checked="checked"';
				}
				print '<td><input type="checkbox" class="flat" name="isOPCA" value="1" ' . $chckisOPCA . '" /></td></tr>';

				print '<tr><td width="20%">' . $langs->trans("AgfOPCAName") . '</td>';
				print '	<td>';
				$htmlname_thirdparty='fksocOPCA';
				print $form->select_company($agf->fk_soc_OPCA, $htmlname_thirdparty, '(s.client IN (1,2,3))', 'SelectThirdParty', 1, 0);
				$events[]=array('method' => 'getContacts', 'url' => dol_buildpath('/core/ajax/contacts.php',1), 'htmlname' => 'fksocpeopleOPCA', 'params' => array('add-customer-contact' => 'disabled'));
				//Select contact regarding comapny
				if (count($events))
				{
					print '<script type="text/javascript">
								jQuery(document).ready(function() {
									$("#'.$htmlname_thirdparty.'").change(function() {
										var obj = '.json_encode($events).';
										$.each(obj, function(key,values) {
											if (values.method.length) {
												runJsCodeForEvent'.$htmlname_thirdparty.'(values);
											}
										});
										/* Clean contact */
										$("div#s2id_contactid>a>span").html(\'\');
									});

									// Function used to execute events when search_htmlname change
									function runJsCodeForEvent'.$htmlname_thirdparty.'(obj) {
										var id = $("#'.$htmlname_thirdparty.'").val();
										var method = obj.method;
										var url = obj.url;
										var htmlname = obj.htmlname;
										var showempty = obj.showempty;
										console.log("Run runJsCodeForEvent-'.$htmlname_thirdparty.' from selectCompaniesForNewContact id="+id+" method="+method+" showempty="+showempty+" url="+url+" htmlname="+htmlname);
										$.getJSON(url,
											{
												action: method,
												id: id,
												htmlname: htmlname
											},
											function(response) {
												if (response != null)
												{
													console.log("Change select#"+htmlname+" with content "+response.value)
													$.each(obj.params, function(key,action) {
														if (key.length) {
															var num = response.num;
															if (num > 0) {
																$("#" + key).removeAttr(action);
															} else {
																$("#" + key).attr(action, action);
															}
														}
													});
													$("select#" + htmlname).html(response.value);
												}
											}
										);
									};
								});
								</script>';
				}
				if (! empty($agf->fk_soc_OPCA) && ! empty($conf->global->COMPANY_USE_SEARCH_TO_SELECT)) {
					print '<a href="' . $_SERVER['PHP_SELF'] . '?id=' . $agf->id . '&amp;action=remove_fksocOPCA&token='.$newToken.'">' . img_delete($langs->trans('Delete')) . '</a>';
				}
				// Print biller choice;
				$socbiller = new Societe($db);
				$socbiller->fetch($agf->fk_soc);
				print '</td></tr>';

				print '<tr><td width="20%">' . $langs->trans("AgfOPCAContact") . '</td>';
				print '	<td>';
				$form->select_contacts(($agf->fk_soc_OPCA > 0 ? $agf->fk_soc_OPCA : -1), $agf->fk_socpeople_OPCA, 'fksocpeopleOPCA', ((floatval(DOL_VERSION) < 8.0)?1:3), '', '', 0, 'minwidth100imp');
				print '</td></tr>';

				print '<tr><td width="20%">' . $langs->trans("AgfOPCANumClient") . '</td>';
				print '<td><input size="30" type="text" class="flat" name="numOPCAsoc" value="' . $agf->num_OPCA_soc . '" /></td></tr>';

				print '<tr><td width="20%">' . $langs->trans("AgfOPCADateDemande") . '</td>';

				print '<td><table class="nobordernopadding"><tr>';
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
				print '<a class="butActionDelete" href="' . $_SERVER['PHP_SELF'] . '?id=' . $id . '">' . $langs->trans('Cancel') . '</a></div>';
				print '</td></tr>';

				print '</table>';
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
				if (floatval(DOL_VERSION) < 6.0) {
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

				print '<td>';
				print dol_print_date($agf->date_ask_OPCA, 'daytext');
				print '</td></tr>';

				print '<tr><td width="20%">' . $langs->trans("AgfOPCANumFile") . '</td>';
				print '<td>';
				print $agf->num_OPCA_file;
				print '</td></tr>';

				print '</table>';
			}
		}
	}
}
$reshook = $hookmanager->executeHooks('formObjectOptions', $parameters, $agf, $action); // Note that $action and $object may have been modified by hook
print $hookmanager->resPrint;

if ($action == 'presend') {

	require_once '../lib/agefodd_document.lib.php';

	print '<div id="formmailbeforetitle" name="formmailbeforetitle"></div>';
	print '<div class="clearboth"></div>';
	print '<br>';
	print load_fiche_titre($langs->trans('agfSendEmailOnlineSign'));

	print dol_get_fiche_head('');

	/** @var string $delayedhtmlcontent */
	$agf_trainee = new Agefodd_session_stagiaire($db);
	$agf_trainer = new Agefodd_session_formateur($db);

	$result = $agf_trainee->fetch_stagiaire_per_session($id);
	$result_trainer = $agf_trainer->fetch_formateur_per_session($id);

	if (!$result && !$result_trainer) dol_print_error($db);

	$TStagiaire = $agf_trainee->lines;
	$TFormateur = $agf_trainer->lines;
	$TParticipant = array_merge($TStagiaire,$TFormateur);

	$typeModele = 'agf_online_sign';
	$presendmassmail = presend_mail_online_sign($agf, $typeModele, $TParticipant);
	print $presendmassmail;
	print dol_get_fiche_end();

}


llxFooter();
$db->close();
