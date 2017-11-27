<?php
/*
 * Copyright (C) 2009-2010	Erick Bullier	<eb.dev@ebiconsulting.fr>
 * Copyright (C) 2010-2011	Regis Houssin	<regis@dolibarr.fr>
 * Copyright (C) 2012-2016 Florian Henry <florian.henry@open-concept.pro>
 * Copyright (C) 2012 JF FERRY <jfefe@aternatik.fr>
 *
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
 * \file agefodd/session/send_docs.php
 * \ingroup agefodd
 * \brief Sending docuemnt screen
 */
$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");
dol_include_once('/agefodd/class/agefodd_formation_catalogue.class.php');

require_once ('../class/agsession.class.php');
require_once ('../class/agefodd_opca.class.php');
require_once ('../class/agefodd_sessadm.class.php');
require_once ('../class/agefodd_session_element.class.php');
require_once ('../class/agefodd_convention.class.php');
require_once ('../core/modules/agefodd/modules_agefodd.php');
require_once ('../class/html.formagefodd.class.php');
require_once ('../lib/agefodd.lib.php');
require_once ('../class/html.formagefodd.class.php');
require_once ('../class/html.formagefoddsenddocs.class.php');
require_once (DOL_DOCUMENT_ROOT . '/commande/class/commande.class.php');
require_once (DOL_DOCUMENT_ROOT . '/contact/class/contact.class.php');
require_once ('../lib/agefodd_document.lib.php');
require_once (DOL_DOCUMENT_ROOT . '/core/class/html.formmail.class.php');
require_once ('../class/agefodd_session_formateur.class.php');
require_once (DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php');
require_once ('../class/agefodd_session_stagiaire.class.php');
require_once ('../class/agefodd_formateur.class.php');

// Security check
if (! $user->rights->agefodd->lire)
	accessforbidden();

$action = GETPOST('action', 'alpha');
$pre_action = GETPOST('pre_action', 'alpha');
$id = GETPOST('id', 'int');
$socid = GETPOST('socid', 'int');
$sessiontrainerid = GETPOST('sessiontrainerid', 'int');
$removedfile = GETPOST('removedfile');
$addfile = GETPOST('addfile');
$cancel = GETPOST('cancel');

$form = new Form($db);
$formmail = new FormAgefoddsenddocs($db);
$formAgefodd = new FormAgefodd($db);

if (GETPOST('modelselected')) {
	$action = GETPOST('pre_action');
}

/*
 * Envoi document unique
 */
if ($action == 'send' && empty($addfile) && empty($removedfile) && empty($cancel)) {
	$langs->load('mails');

	$send_to = GETPOST('sendto', 'alpha');
	$receiver = GETPOST('receiver');

	$action = $pre_action;

	$object = new Agsession($db);
	$result = $object->fetch($id);

	if ($result > 0) {
		$result = $object->fetch_thirdparty();

		$sendto = array();
		if (! empty($send_to)) {
			// Le destinataire a ete fourni via le champ libre
			$sendto = array(
					$send_to
			);
			$sendtoid = 0;
		} elseif (is_array($receiver)) {
			$receiver = $receiver;
			foreach ( $receiver as $socpeople_id ) {
				// Initialisation donnees
				$contactstatic = new Contact($db);
				$contactstatic->fetch($socpeople_id);
				if ($contactstatic->email != '') {
					$sendto[$socpeople_id] = trim($contactstatic->firstname . " " . $contactstatic->lastname) . " <" . $contactstatic->email . ">";
				}
			}
		}
		if (is_array($sendto) && count($sendto) > 0) {
			$langs->load("commercial");

			$from = GETPOST('fromname') . ' <' . GETPOST('frommail') . '>';
			$replyto = GETPOST('replytoname') . ' <' . GETPOST('replytomail') . '>';
			$message = GETPOST('message');
			$sendtocc = GETPOST('sendtocc');
			$deliveryreceipt = GETPOST('deliveryreceipt');

			// Envoi du mail + trigger pour chaque contact
			$i = 0;
			foreach ( $sendto as $send_contact_id => $send_email ) {

				$models = GETPOST('models', 'alpha');

				$subject = GETPOST('subject');
				// Initialisation donnees
				$contactstatic = new Contact($db);
				$contactstatic->fetch($send_contact_id);

				if ($models == 'fiche_pedago') {
					if (empty($subject))
						$langs->transnoentities('AgfFichePedagogique') . ' ' . $object->ref;
					$actiontypecode = 'AC_AGF_PEDAG';
					$actionmsg = $langs->trans('MailSentBy') . ' ' . $from . ' ' . $langs->trans('To') . ' ' . $send_email . ".\n";
					if ($message) {
						$actionmsg .= $langs->trans('MailTopic') . ": " . $subject . "\n";
						$actionmsg .= $langs->trans('TextUsedInTheMessageBody') . ":\n";
						$actionmsg .= $message;
					}
					$actionmsg2 = $langs->trans('ActionFICHEPEDAGO_SENTBYMAIL');
				}
				if ($models == 'mission_trainer') {
					if (empty($subject))
						$langs->transnoentities('AgfTrainerMissionLetter') . ' ' . $object->ref;
					$actiontypecode = 'AC_AGF_MISTR';
					$actionmsg = $langs->trans('MailSentBy') . ' ' . $from . ' ' . $langs->trans('To') . ' ' . $send_email . ".\n";
					if ($message) {
						$actionmsg .= $langs->trans('MailTopic') . ": " . $subject . "\n";
						$actionmsg .= $langs->trans('TextUsedInTheMessageBody') . ":\n";
						$actionmsg .= $message;
					}
					$actionmsg2 = $langs->trans('ActionAC_AGF_MISTR');
				}
				if ($models == 'trainer_doc') {
					if (empty($subject))
						$langs->transnoentities('AgfTrainerDocLetter') . ' ' . $object->ref;
					$actiontypecode = 'AC_AGF_DOCTR';
					$actionmsg = $langs->trans('MailSentBy') . ' ' . $from . ' ' . $langs->trans('To') . ' ' . $send_email . ".\n";
					if ($message) {
						$actionmsg .= $langs->trans('MailTopic') . ": " . $subject . "\n";
						$actionmsg .= $langs->trans('TextUsedInTheMessageBody') . ":\n";
						$actionmsg .= $message;
					}
					$actionmsg2 = $langs->trans('ActionAC_AGF_MISTR');
				} elseif ($models == 'fiche_presence') {
					if (empty($subject))
						$langs->transnoentities('AgfFichePresence') . ' ' . $object->ref;
					$actiontypecode = 'AC_AGF_PRES';
					$actionmsg = $langs->trans('MailSentBy') . ' ' . $from . ' ' . $langs->trans('To') . ' ' . $send_email . ".\n";
					if ($message) {
						$actionmsg .= $langs->trans('MailTopic') . ": " . $subject . "\n";
						$actionmsg .= $langs->trans('TextUsedInTheMessageBody') . ":\n";
						$actionmsg .= $message;
					}
					$actionmsg2 = $langs->trans('ActionFICHEPRESENCE_SENTBYMAIL');
				} elseif ($models == 'fiche_presence_direct') {
					if (empty($subject))
						$langs->transnoentities('AgfFichePresenceDirect') . ' ' . $object->ref;
					$actiontypecode = 'AC_AGF_PRES';
					$actionmsg = $langs->trans('MailSentBy') . ' ' . $from . ' ' . $langs->trans('To') . ' ' . $send_email . ".\n";
					if ($message) {
						$actionmsg .= $langs->trans('MailTopic') . ": " . $subject . "\n";
						$actionmsg .= $langs->trans('TextUsedInTheMessageBody') . ":\n";
						$actionmsg .= $message;
					}
					$actionmsg2 = $langs->trans('ActionFICHEPRESENCE_SENTBYMAIL');
				} elseif ($models == 'fiche_presence_empty') {
					if (empty($subject))
						$langs->transnoentities('AgfFichePresenceEmpty') . ' ' . $object->ref;
						$actiontypecode = 'AC_AGF_PRES';
						$actionmsg = $langs->trans('MailSentBy') . ' ' . $from . ' ' . $langs->trans('To') . ' ' . $send_email . ".\n";
						if ($message) {
							$actionmsg .= $langs->trans('MailTopic') . ": " . $subject . "\n";
							$actionmsg .= $langs->trans('TextUsedInTheMessageBody') . ":\n";
							$actionmsg .= $message;
						}
						$actionmsg2 = $langs->trans('ActionFICHEPRESENCE_SENTBYMAIL');
				}elseif ($models == 'convention') {
					if (empty($subject))
						$langs->transnoentities('AgfConvention') . ' ' . $object->ref;
					$actiontypecode = 'AC_AGF_CONVE';
					$actionmsg = $langs->trans('MailSentBy') . ' ' . $from . ' ' . $langs->trans('To') . ' ' . $send_email . ".\n";
					if ($message) {
						$actionmsg .= $langs->trans('MailTopic') . ": " . $subject . "\n";
						$actionmsg .= $langs->trans('TextUsedInTheMessageBody') . ":\n";
						$actionmsg .= $message;
					}
					$actionmsg2 = $langs->trans('ActionCONVENTION_SENTBYMAIL');
				} elseif ($models == 'attestation') {
					if (empty($subject))
						$langs->transnoentities('AgfAttestation') . ' ' . $object->ref;
					$actiontypecode = 'AC_AGF_ATTES';
					$actionmsg = $langs->trans('MailSentBy') . ' ' . $from . ' ' . $langs->trans('To') . ' ' . $send_email . ".\n";
					if ($message) {
						$actionmsg .= $langs->trans('MailTopic') . ": " . $subject . "\n";
						$actionmsg .= $langs->trans('TextUsedInTheMessageBody') . ":\n";
						$actionmsg .= $message;
					}
					$actionmsg2 = $langs->trans('ActionATTESTATION_SENTBYMAIL');
				} elseif ($models == 'cloture') {
					if (empty($subject))
						$langs->transnoentities('AgfDossierCloture') . ' ' . $object->ref;
					$actiontypecode = 'AC_AGF_CLOT';
					$actionmsg = $langs->trans('MailSentBy') . ' ' . $from . ' ' . $langs->trans('To') . ' ' . $send_email . ".\n";
					if ($message) {
						$actionmsg .= $langs->trans('MailTopic') . ": " . $subject . "\n";
						$actionmsg .= $langs->trans('TextUsedInTheMessageBody') . ":\n";
						$actionmsg .= $message;
					}
					$actionmsg2 = $langs->trans('ActionCLOTURE_SENTBYMAIL');
				} elseif ($models == 'conseils') {
					if (empty($subject))
						$langs->transnoentities('AgfConseilsPratique') . ' ' . $object->ref;
					$actiontypecode = 'AC_AGF_CONSE';
					$actionmsg = $langs->trans('MailSentBy') . ' ' . $from . ' ' . $langs->trans('To') . ' ' . $send_email . ".\n";
					if ($message) {
						$actionmsg .= $langs->trans('MailTopic') . ": " . $subject . "\n";
						$actionmsg .= $langs->trans('TextUsedInTheMessageBody') . ":\n";
						$actionmsg .= $message;
					}
					$actionmsg2 = $langs->trans('ActionCONSEILS_SENTBYMAIL');
				} elseif ($models == 'convocation') {
					if (empty($subject))
						$langs->transnoentities('AgfPDFConvocation') . ' ' . $object->ref;
					$actiontypecode = 'AC_AGF_CONVO';
					$actionmsg = $langs->trans('MailSentBy') . ' ' . $from . ' ' . $langs->trans('To') . ' ' . $send_email . ".\n";
					if ($message) {
						$actionmsg .= $langs->trans('MailTopic') . ": " . $subject . "\n";
						$actionmsg .= $langs->trans('TextUsedInTheMessageBody') . ":\n";
						$actionmsg .= $message;
					}
					$actionmsg2 = $langs->trans('ActionCONVOCATION_SENTBYMAIL');
				} elseif ($models == 'courrier-accueil') {
					if (empty($subject))
						$langs->transnoentities('AgfCourrierAccueil') . ' ' . $object->ref;
					$actiontypecode = 'AC_AGF_ACCUE';
					$actionmsg = $langs->trans('MailSentBy') . ' ' . $from . ' ' . $langs->trans('To') . ' ' . $send_email . ".\n";
					if ($message) {
						$actionmsg .= $langs->trans('MailTopic') . ": " . $subject . "\n";
						$actionmsg .= $langs->trans('TextUsedInTheMessageBody') . ":\n";
						$actionmsg .= $message;
					}
					$actionmsg2 = $langs->trans('ActionACCUEIL_SENTBYMAIL');
				}elseif ($models == 'attestationendtraining') {
					if (empty($subject))
						$langs->transnoentities('AgfAttestation') . ' ' . $object->ref;
					$actiontypecode = 'AC_AGF_ATTES';
					$actionmsg = $langs->trans('MailSentBy') . ' ' . $from . ' ' . $langs->trans('To') . ' ' . $send_email . ".\n";
					if ($message) {
						$actionmsg .= $langs->trans('MailTopic') . ": " . $subject . "\n";
						$actionmsg .= $langs->trans('TextUsedInTheMessageBody') . ":\n";
						$actionmsg .= $message;
					}
					$actionmsg2 = $langs->trans('ActionACCUEIL_SENTBYMAIL');
				}

				// Create form object
				include_once (DOL_DOCUMENT_ROOT . '/core/class/html.formmail.class.php');
				$formmail = new FormMail($db);

				$attachedfiles = $formmail->get_attached_files();
				$filepath = $attachedfiles['paths'];
				$filename = $attachedfiles['names'];
				$mimetype = $attachedfiles['mimes'];

				// Envoi de la fiche
				require_once (DOL_DOCUMENT_ROOT . '/core/class/CMailFile.class.php');
				$mailfile = new CMailFile($subject, $send_email, $from, $message, $filepath, $mimetype, $filename, $sendtocc, '', $deliveryreceipt, - 1);
				if ($mailfile->error) {
					setEventMessage($mailfile->error, 'errors');
				} else {
					$result = $mailfile->sendfile();
					if ($result) {
						setEventMessage($langs->trans('MailSuccessfulySent', $mailfile->getValidAddress($from, 2), $mailfile->getValidAddress($send_email, 2)), 'mesgs');

						$error = 0;
						$socid_action = ($contactstatic->socid > 0 ? $contactstatic->socid : ($socid > 0 ? $socid : $object->fk_soc));
						$object->socid = $socid_action;
						$object->sendtoid = $send_contact_id;
						$object->actiontypecode = $actiontypecode;
						$object->actionmsg = $actionmsg;
						$object->actionmsg2 = $actionmsg2;
						$object->fk_element = $object->id;
						$object->elementtype = $object->element;

						/* Appel des triggers */
						include_once (DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php");
						$interface = new Interfaces($db);
						$models = GETPOST('models', 'alpha');
						if ($models == 'fiche_pedago') {
							$result = $interface->run_triggers('FICHEPEDAGO_SENTBYMAIL', $object, $user, $langs, $conf);
						} elseif ($models == 'fiche_presence' || $models == 'fiche_presence_direct'|| $models == 'fiche_presence_empty') {
							$result = $interface->run_triggers('FICHEPRESENCE_SENTBYMAIL', $object, $user, $langs, $conf);
						} elseif ($models == 'convention') {
							$result = $interface->run_triggers('CONVENTION_SENTBYMAIL', $object, $user, $langs, $conf);
						} elseif ($models == 'attestation') {
							$result = $interface->run_triggers('ATTESTATION_SENTBYMAIL', $object, $user, $langs, $conf);
						} elseif ($models == 'convocation') {
							$result = $interface->run_triggers('CONVOCATION_SENTBYMAIL', $object, $user, $langs, $conf);
						} elseif ($models == 'conseils') {
							$result = $interface->run_triggers('CONSEILS_SENTBYMAIL', $object, $user, $langs, $conf);
						} elseif ($models == 'cloture') {
							$result = $interface->run_triggers('CLOTURE_SENTBYMAIL', $object, $user, $langs, $conf);
						} elseif ($models == 'courrier-accueil') {
							$result = $interface->run_triggers('ACCUEIL_SENTBYMAIL', $object, $user, $langs, $conf);
						} elseif ($models == 'mission_trainer') {
							$result = $interface->run_triggers('MISTR_SENTBYMAIL', $object, $user, $langs, $conf);
						} elseif ($models == 'trainer_doc') {
							$result = $interface->run_triggers('DOCTR_SENTBYMAIL', $object, $user, $langs, $conf);
						}elseif ($models == 'attestationendtraining') {
							$result = $interface->run_triggers('ATTESTATIONENDTRAINING_SENTBYMAIL', $object, $user, $langs, $conf);
						}
						if ($result < 0) {
							$error ++;
							$object->errors = $interface->errors;
						}
						// Fin appel triggers

						if ($error) {
							setEventMessage($object->errors, 'errors');
						} else {
							$i ++;
							$action = '';
						}
					} else {
						$langs->load("other");
						if ($mailfile->error) {
							setEventMessage($langs->trans('ErrorFailedToSendMail', $from, $send_email), 'errors');
							dol_syslog($langs->trans('ErrorFailedToSendMail', $from, $send_email) . ' : ' . $mailfile->error);
						} else {
							setEventMessage('No mail sent. Feature is disabled by option MAIN_DISABLE_ALL_MAILS', 'errors');
						}
					}
				}
			}
		} else {
			$langs->load("other");
			setEventMessage($langs->trans('ErrorMailRecipientIsEmpty'), 'errors');
			dol_syslog('Recipient email is empty', LOG_ERR);
			$action = $pre_action;
		}
	}
}

/*
 * Remove file in email form
 */
if (! empty($removedfile)) {
	require_once (DOL_DOCUMENT_ROOT . "/core/lib/files.lib.php");

	// Set tmp user directory
	$vardir = $conf->user->dir_output . "/" . $user->id;
	$upload_dir_tmp = $vardir . '/temp';

	// TODO Delete only files that was uploaded from email form
	$mesg = dol_remove_file_process($removedfile, 0, 1);

	$action = $pre_action;
}

/*
 * Add file in email form
 */
if (! empty($addfile)) {
	require_once (DOL_DOCUMENT_ROOT . "/core/lib/files.lib.php");

	// Set tmp user directory TODO Use a dedicated directory for temp mails files
	$vardir = $conf->user->dir_output . "/" . $user->id;
	$upload_dir_tmp = $vardir . '/temp';

	$mesg = dol_add_file_process($upload_dir_tmp, 0, 0);

	$action = $pre_action;
}

$extrajs = array(
		'/agefodd/includes/multiselect/js/ui.multiselect.js'
);
$extracss = array(
		'/agefodd/includes/multiselect/css/ui.multiselect.css',
		'/agefodd/css/agefodd.css'
);

llxHeader('', $langs->trans("AgfSendCommonDocs"), '', '', '', '', $extrajs, $extracss);

print '<script type="text/javascript" language="javascript">
	jQuery(document).ready(function() {
	jQuery.extend($.ui.multiselect.locale, {
	addAll:\'' . $langs->transnoentities("AddAll") . '\',
		removeAll:\'' . $langs->transnoentities("RemoveAll") . '\',
			itemsCount:\'' . $langs->transnoentities("ItemsCount") . '\'
});
				jQuery(function(){
				jQuery("#receiver").addClass("multiselect").attr("multiple","multiple").attr("name","receiver[]");
				jQuery(".multiselect").multiselect({sortable: false, searchable: false});
});
});
				</script>';

if (! empty($id)) {
	$agf = new Agsession($db);
	$agf->fetch($id);

	$result = $agf->fetch_societe_per_session($id);

	if ($result) {
		$idform = $agf->formid;

		// Display consult
		$head = session_prepare_head($agf);

		dol_fiche_head($head, 'send_docs', $langs->trans("AgfSessionDetail"), 0, 'generic');

		/*
		 * Confirm delete
		 */
		if ($action == 'delete') {
			print $form->formconfirm($_SERVER['PHP_SELF'] . "?id=" . $id, $langs->trans("AgfDeleteOps"), $langs->trans("AgfConfirmDeleteOps"), "confirm_delete");
		}

		print '<div width=100% align="center" style="margin: 0 0 3px 0;">' . "\n";
		print $formAgefodd->level_graph(ebi_get_adm_lastFinishLevel($id), ebi_get_level_number($id), $langs->trans("AgfAdmLevel"));
		print '</div>' . "\n";

		$agf->printSessionInfo();
		print '</div>' . "\n";

		/*
		 * Formulaire d'envoi des documents
		 */
		if ($action == 'presend_pedago' || $action == 'presend_presence' || $action == 'presend_presence_direct' || $action == 'presend_presence_empty'
				|| $action == 'presend_convention' || $action == 'presend_attestation' || $action == 'presend_cloture'
				|| $action == 'presend_convocation' || $action == 'presend_conseils' || $action == 'presend_accueil' || $action == 'presend_mission_trainer'
				|| $action == 'presend_trainer_doc' || $action == 'presend_attestationendtraining') {

			if ($action == 'presend_presence') {
				$filename = 'fiche_presence_' . $agf->id . '.pdf';
			} elseif ($action == 'presend_presence_direct') {
				$filename = 'fiche_presence_direct_' . $agf->id . '.pdf';
			} elseif ($action == 'presend_presence_empty') {
				$filename = 'fiche_presence_empty_' . $agf->id . '.pdf';
			}elseif ($action == 'presend_pedago') {
				
				$agfTraining = new Agefodd($db);
				$agfTraining->fetch($agf->fk_formation_catalogue);
				$agfTraining->generatePDAByLink();
				$filename = 'fiche_pedago_' . $agf->fk_formation_catalogue . '.pdf';
			} elseif ($action == 'presend_convention') {
				$conv_id = GETPOST('convid', 'int');
				// For backwoard compatibilty check convention file name with id of convention
				if (is_file($conf->agefodd->dir_output . '/' . 'convention_' . $agf->id . '_' . $socid . '.pdf')) {
					$filename = 'convention_' . $agf->id . '_' . $socid . '.pdf';
				} elseif (is_file($conf->agefodd->dir_output . '/' . 'convention_' . $agf->id . '_' . $socid . '_' . $conv_id . '.pdf')) {
					$filename = 'convention_' . $agf->id . '_' . $socid . '_' . $conv_id . '.pdf';
				}
			} elseif ($action == 'presend_attestation') {
				$filename = 'attestation_' . $agf->id . '_' . $socid . '.pdf';
			} elseif ($action == 'presend_conseils') {
				$filename = 'conseils_' . $agf->fk_formation_catalogue . '.pdf';
			} elseif ($action == 'presend_accueil') {
				$filename = 'courrier-accueil_' . $agf->id . '_' . $socid . '.pdf';
			} elseif ($action == 'presend_mission_trainer') {
				$filename = 'mission_trainer_' . $sessiontrainerid . '.pdf';
			}elseif ($action == 'presend_attestationendtraining') {
				$filename = 'attestationendtraining_' . $agf->id . '_' . $socid . '.pdf';
			}

			if ($filename) {
				$file = $conf->agefodd->dir_output . '/' . $filename;
			}

			// Init list of files
			if (GETPOST("mode") == 'init') {
				$formmail->clear_attached_files();

				if ($action == 'presend_convention') {
					$formmail->add_attached_files($file, basename($file), dol_mimetype($file));

					if (!empty($conf->global->AGF_ADD_PROGRAM_TO_CONVMAIL)) {
						// Ajout fiche péda
						$agfTraining = new Agefodd($db);
						$agfTraining->fetch($agf->fk_formation_catalogue);
						$agfTraining->generatePDAByLink();
						$filename = 'fiche_pedago_' . $agf->fk_formation_catalogue . '.pdf';
						$file = $conf->agefodd->dir_output . '/' . $filename;
						if (file_exists($file)) {
							$formmail->add_attached_files($file, basename($file), dol_mimetype($file));
						}
					}
					$filename = 'conseils_' . $agf->id . '.pdf';
					$file = $conf->agefodd->dir_output . '/' . $filename;
					if (file_exists($file)) {
						$formmail->add_attached_files($file, basename($file), dol_mimetype($file));
					}

					$filename = 'fiche_presence_' . $agf->id . '.pdf';
					$file = $conf->agefodd->dir_output . '/' . $filename;
					if (file_exists($file)) {
						$formmail->add_attached_files($file, basename($file), dol_mimetype($file));
					}
					$filename = 'fiche_presence_direct_' . $agf->id . '.pdf';
					$file = $conf->agefodd->dir_output . '/' . $filename;
					if (file_exists($file)) {
						$formmail->add_attached_files($file, basename($file), dol_mimetype($file));
					}
					$filename = 'fiche_presence_empty_' . $agf->id . '.pdf';
					$file = $conf->agefodd->dir_output . '/' . $filename;
					if (file_exists($file)) {
						$formmail->add_attached_files($file, basename($file), dol_mimetype($file));
					}
					$filename = 'fiche_presence_empty_' . $agf->id . '.pdf';
					$file = $conf->agefodd->dir_output . '/' . $filename;
					if (file_exists($file)) {
						$formmail->add_attached_files($file, basename($file), dol_mimetype($file));
					}
					$filename = 'fiche_presence_trainee_' . $agf->id . '.pdf';
					$file = $conf->agefodd->dir_output . '/' . $filename;
					if (file_exists($file)) {
						$formmail->add_attached_files($file, basename($file), dol_mimetype($file));
					}
					$filename = 'fiche_presence_trainee_direct_' . $agf->id . '.pdf';
					$file = $conf->agefodd->dir_output . '/' . $filename;
					if (file_exists($file)) {
						$formmail->add_attached_files($file, basename($file), dol_mimetype($file));
					}
					$filename = 'fiche_presence_landscape_' . $agf->id . '.pdf';
					$file = $conf->agefodd->dir_output . '/' . $filename;
					if (file_exists($file)) {
						$formmail->add_attached_files($file, basename($file), dol_mimetype($file));
					}
					$filename = 'fiche_remise_eval_' . $agf->id . '.pdf';
					$file = $conf->agefodd->dir_output . '/' . $filename;
					if (file_exists($file)) {
						$formmail->add_attached_files($file, basename($file), dol_mimetype($file));
					}
				} elseif ($action == 'presend_presence') {
					$formmail->add_attached_files($file, basename($file), dol_mimetype($file));
					// Ajout fiche péda
					$filename = 'fiche_evaluation_' . $agf->id . '.pdf';
					$file = $conf->agefodd->dir_output . '/' . $filename;
					if (file_exists($file))
						$formmail->add_attached_files($file, basename($file), dol_mimetype($file));
				} elseif ($action == 'presend_presence_direct') {
					$formmail->add_attached_files($file, basename($file), dol_mimetype($file));
					// Ajout fiche péda
					$filename = 'fiche_evaluation_' . $agf->id . '.pdf';
					$file = $conf->agefodd->dir_output . '/' . $filename;
					if (file_exists($file))
						$formmail->add_attached_files($file, basename($file), dol_mimetype($file));
				} elseif ($action == 'presend_presence_empty') {
					$formmail->add_attached_files($file, basename($file), dol_mimetype($file));
					// Ajout fiche péda
					$filename = 'fiche_evaluation_' . $agf->id . '.pdf';
					$file = $conf->agefodd->dir_output . '/' . $filename;
					if (file_exists($file))
						$formmail->add_attached_files($file, basename($file), dol_mimetype($file));
				}elseif ($action == 'presend_mission_trainer') {
					$formmail->add_attached_files($file, basename($file), dol_mimetype($file));
					// Ajout fiche péda
					$filename = 'presend_mission_trainer_' . $sessiontrainerid . '.pdf';
					$file = $conf->agefodd->dir_output . '/' . $filename;
					if (file_exists($file))
						$formmail->add_attached_files($file, basename($file), dol_mimetype($file));
				} elseif ($action == 'presend_trainer_doc') {

					// Add all presence sheet
					$filename = 'fiche_presence_' . $agf->id . '.pdf';
					$file = $conf->agefodd->dir_output . '/' . $filename;
					if (file_exists($file)) {
						$formmail->add_attached_files($file, basename($file), dol_mimetype($file));
					}
					$filename = 'fiche_presence_direct_' . $agf->id . '.pdf';
					$file = $conf->agefodd->dir_output . '/' . $filename;
					if (file_exists($file)) {
						$formmail->add_attached_files($file, basename($file), dol_mimetype($file));
					}
					$filename = 'fiche_presence_empty_' . $agf->id . '.pdf';
					$file = $conf->agefodd->dir_output . '/' . $filename;
					if (file_exists($file)) {
						$formmail->add_attached_files($file, basename($file), dol_mimetype($file));
					}
					$filename = 'fiche_presence_trainee_' . $agf->id . '.pdf';
					$file = $conf->agefodd->dir_output . '/' . $filename;
					if (file_exists($file)) {
						$formmail->add_attached_files($file, basename($file), dol_mimetype($file));
					}
					$filename = 'fiche_presence_trainee_direct_' . $agf->id . '.pdf';
					$file = $conf->agefodd->dir_output . '/' . $filename;
					if (file_exists($file)) {
						$formmail->add_attached_files($file, basename($file), dol_mimetype($file));
					}
					$filename = 'fiche_presence_landscape_' . $agf->id . '.pdf';
					$file = $conf->agefodd->dir_output . '/' . $filename;
					if (file_exists($file)) {
						$formmail->add_attached_files($file, basename($file), dol_mimetype($file));
					}

					$filename = 'fiche_evaluation_' . $agf->id . '.pdf';
					$file = $conf->agefodd->dir_output . '/' . $filename;
					if (file_exists($file)) {
						$formmail->add_attached_files($file, basename($file), dol_mimetype($file));
					}

					// add doc from attached files of training
					$upload_dir = $conf->agefodd->dir_output . "/training/" . $agf->formid;
					$filearray = dol_dir_list($upload_dir, "files", 0, '', '\.meta$', $sortfield, (strtolower($sortorder) == 'desc' ? SORT_DESC : SORT_ASC), 1);
					if (is_array($filearray) && count($filearray) > 0) {
						foreach ( $filearray as $filedetail ) {
							if (file_exists($filedetail['fullname'])) {
								$formmail->add_attached_files($filedetail['fullname'], basename($filedetail['fullname']), dol_mimetype($filedetail['fullname']));
							}
						}
					}

					// Attach attestation presence
					$filename = 'attestationendtraining_empty_' . $agf->id . '.pdf';
					$file = $conf->agefodd->dir_output . '/' . $filename;
					if (file_exists($file)) {
						$formmail->add_attached_files($file, basename($file), dol_mimetype($file));
					}

					// And add attestation by soc
					$agf_soc = new Agsession($db);
					$agf_soc->fetch($agf->id);

					$result_soc = $agf_soc->fetch_societe_per_session($agf->id);
					if ($result_soc > 0) {
						foreach ( $agf_soc->lines as $socline ) {
							// Attach attestation presence
							$filename = 'attestationendtraining_' . $agf->id . '_' . $socline->socid . '.pdf';
							$file = $conf->agefodd->dir_output . '/' . $filename;
							if (file_exists($file)) {
								$formmail->add_attached_files($file, basename($file), dol_mimetype($file));
							}
						}
					}
				} elseif ($action == 'presend_accueil') {
					$formmail->add_attached_files($file, basename($file), dol_mimetype($file));
					// Ajout conseil pratique
					$filename = 'conseils_' . $agf->id . '.pdf';
					$file = $conf->agefodd->dir_output . '/' . $filename;
					if (file_exists($file))
						$formmail->add_attached_files($file, basename($file), dol_mimetype($file));
					else
						print '<div class="error">' . $langs->trans('AgfConseilNotExists') . '</div>';
						// Ajout fiche péda
					$agfTraining = new Agefodd($db);
					$agfTraining->fetch($agf->fk_formation_catalogue);
					$agfTraining->generatePDAByLink();
					$filename = 'fiche_pedago_' . $agf->fk_formation_catalogue . '.pdf';
					$file = $conf->agefodd->dir_output . '/' . $filename;
					if (file_exists($file))
						$formmail->add_attached_files($file, basename($file), dol_mimetype($file));

						$filename = 'convocation_' . $agf->id . '_' . $socid . '.pdf';
					$file = $conf->agefodd->dir_output . '/' . $filename;
					if (file_exists($file)) {
						$formmail->add_attached_files($file, basename($file), dol_mimetype($file));
					}
				} elseif ($action == 'presend_attestation') {

					$formmail->add_attached_files($file, basename($file), dol_mimetype($file));

					if (! empty($conf->global->AGF_MANAGE_CERTIF)) {
						// Add certificate A4
						$filename = 'certificateA4_' . $agf->id . '_' . $socid . '.pdf';
						$file = $conf->agefodd->dir_output . '/' . $filename;
						if (file_exists($file)) {
							$formmail->add_attached_files($file, basename($file), dol_mimetype($file));
						}

						// Add certificate card
						$filename = 'certificatecard_' . $agf->id . '_' . $socid . '.pdf';
						$file = $conf->agefodd->dir_output . '/' . $filename;
						if (file_exists($file)) {
							$formmail->add_attached_files($file, basename($file), dol_mimetype($file));
						}
					}
				} elseif ($action == 'presend_cloture') {
					$formmail->add_attached_files($file, basename($file), dol_mimetype($file));

					// Ajout attestations de présence
					$filename = 'attestation_' . $agf->id . '_' . $socid . '.pdf';
					$file = $conf->agefodd->dir_output . '/' . $filename;
					if (file_exists($file)) {
						$formmail->add_attached_files($file, basename($file), dol_mimetype($file));
					} else {
						print '<div class="error">' . $langs->trans('AgfAttestationNotExists') . '</div>';
					}

					// Ajout Fichier courrier cloture
					$filename = 'courrier-cloture_' . $agf->id . '_' . $socid . '.pdf';
					$file = $conf->agefodd->dir_output . '/' . $filename;
					if (file_exists($file)) {
						$formmail->add_attached_files($file, basename($file), dol_mimetype($file));
					} else {
						print '<div class="error">' . $langs->trans('AgfCourrierClotureNotExists') . '</div>';
					}

					// add doc from attached files of training
					$upload_dir = $conf->agefodd->dir_output . "/training/" . $agf->formid;
					$filearray = dol_dir_list($upload_dir, "files", 0, '', '\.meta$', $sortfield, (strtolower($sortorder) == 'desc' ? SORT_DESC : SORT_ASC), 1);
					if (is_array($filearray) && count($filearray) > 0) {
						foreach ( $filearray as $filedetail ) {
							if (file_exists($filedetail['fullname'])) {
								$formmail->add_attached_files($filedetail['fullname'], basename($filedetail['fullname']), dol_mimetype($filedetail['fullname']));
							}
						}
					}

					// Ajout facture
					$agf_fac = new Agefodd_session_element($db);
					$result = $agf_fac->fetch_by_session_by_thirdparty($id, $socid);
					foreach ( $agf_fac->lines as $line ) {
						if ($line->element_type == 'invoice' && ! empty($line->facnumber)) {
							$filename = $line->facnumber . '/' . $line->facnumber . '.pdf';
							$file = $conf->facture->dir_output . '/' . $filename;
							if (file_exists($file)) {
								$formmail->add_attached_files($file, basename($file), dol_mimetype($file));
							} else {
								print '<div class="error">' . $langs->trans('AgfInvoiceNotExists') . '</div>';
							}
						}
					}

					// Ajout fiche pédago
					$agfTraining = new Agefodd($db);
					$agfTraining->fetch($agf->fk_formation_catalogue);
					$agfTraining->generatePDAByLink();
					$filename = 'fiche_pedago_' . $agf->fk_formation_catalogue . '.pdf';
					$file = $conf->agefodd->dir_output . '/' . $filename;
					if (file_exists($file)) {
						$formmail->add_attached_files($file, basename($file), dol_mimetype($file));
					} else {
						print '<div class="error">' . $langs->trans('AgfFichePedagoNotExists') . '</div>';
					}
				} elseif ($action == 'presend_convocation') {
					$filename = 'convocation_' . $agf->id . '_' . $socid . '.pdf';
					$file = $conf->agefodd->dir_output . '/' . $filename;
					if (file_exists($file)) {
						$formmail->add_attached_files($file, basename($file), dol_mimetype($file));
					}
					// Ajout fiche péda
					$agfTraining = new Agefodd($db);
					$agfTraining->fetch($agf->fk_formation_catalogue);
					$agfTraining->generatePDAByLink();
					$filename = 'fiche_pedago_' . $agf->fk_formation_catalogue . '.pdf';
					$file = $conf->agefodd->dir_output . '/' . $filename;
					if (file_exists($file)) {
						$formmail->add_attached_files($file, basename($file), dol_mimetype($file));
					}
					$filename = 'conseils_' . $agf->id . '.pdf';
					$file = $conf->agefodd->dir_output . '/' . $filename;
					if (file_exists($file)) {
						$formmail->add_attached_files($file, basename($file), dol_mimetype($file));
					}
				} elseif ($action == 'presend_attestationendtraining') {

					$formmail->add_attached_files($file, basename($file), dol_mimetype($file));

					if (! empty($conf->global->AGF_MANAGE_CERTIF)) {
						// Add certificate A4
						$filename = 'certificateA4_' . $agf->id . '_' . $socid . '.pdf';
						$file = $conf->agefodd->dir_output . '/' . $filename;
						if (file_exists($file)) {
							$formmail->add_attached_files($file, basename($file), dol_mimetype($file));
						}

						// Add certificate card
						$filename = 'certificatecard_' . $agf->id . '_' . $socid . '.pdf';
						$file = $conf->agefodd->dir_output . '/' . $filename;
						if (file_exists($file)) {
							$formmail->add_attached_files($file, basename($file), dol_mimetype($file));
						}
					}
				}else {
					$formmail->add_attached_files($file, basename($file), dol_mimetype($file));
				}
			}
			if (! empty($socid)) {
				$formmail->param['socid'] = $socid;
			}
			$formmail->fromtype = 'user';
			$formmail->fromid = $user->id;
			$formmail->fromname = $user->getFullName($langs);
			$formmail->frommail = $user->email;
			$formmail->withfrom = 1;
			// $formmail->withto=(!GETPOST('sendto','alpha'))?1:explode(',',GETPOST('sendto','alpha'));
			// $formmail->withtosocid=($agf->fk_soc > 0?$agf->fk_soc:$socid);
			$formmail->withtocc = 1;
			$formmail->withtoccsocid = 0;
			$formmail->withtoccc = $conf->global->MAIN_EMAIL_USECCC;
			$formmail->withtocccsocid = 0;
			$formmail->withfile = 2;

			$formmail->withdeliveryreceipt = 1;
			$formmail->withdeliveryreceiptreadonly = 0;
			$formmail->withcancel = 1;

			$withtocompanyname= array();

			/*--------------------------------------------------------------
			 *
			 * Définition des destinataires selon type de document demandé
			 *
			 *-------------------------------------------------------------*/
			if ($action == 'presend_presence' ) {
				$formmail->withtopic = $langs->trans('AgfSendFeuillePresence', '__FORMINTITULE__');
				$formmail->withbody = $langs->trans('AgfSendFeuillePresenceBody', '__FORMINTITULE__');
				$formmail->param['models'] = 'fiche_presence';
				$formmail->param['pre_action'] = 'presend_presence';

				// Feuille de présence peut être aux formateurs
				$agftrainersess = new Agefodd_session_formateur($db);
				$num = $agftrainersess->fetch_formateur_per_session($id);
				$withto = array();
				$withtoname = array();

				if ($num > 0) {
					foreach ( $agftrainersess->lines as $formateur ) {
						if ($formateur->email != '')
							$withto[$formateur->socpeopleid] = $formateur->lastname . ' ' . $formateur->firstname . ' - ' . $formateur->email . ' (' . $langs->trans('AgfFormateur') . ')';
							$withtoname[$formateur->socpeopleid]=$formateur->lastname . ' ' . $formateur->firstname;
					}
				}

				// feuille de présence peut être envoyé à l'opca
				if ($agf->type_session && $socid) {
					$agf_opca = new Agefodd_opca($db);
					$result_opca = $agf_opca->getOpcaForTraineeInSession($socid, $id);
					if (! $result_opca) {
						$mesg = $langs->trans('AgfSendWarningNoMailOpca');
						$style_mesg = 'warnings';
					} else {
						$contactstatic = new Contact($db);
						$contactstatic->fetch($agf_opca->fk_socpeople_OPCA);
						if (! empty($contactstatic->email)) {
							$withto[$agf_opca->fk_socpeople_OPCA] = $contactstatic->lastname . ' ' . $contactstatic->firstname . ' - ' . $contactstatic->email . ' (' . $langs->trans('AgfMailTypeContactOPCA') . ')';
							$withtoname[$agf_opca->fk_socpeople_OPCA]=$contactstatic->getFullName($langs);
							if (!empty($contactstatic->socname)) {
								$withtocompanyname[$contactstatic->socid]=$contactstatic->socname;
							}
						} else {
							$mesg = $langs->trans('AgfSendWarningNoMailOpca');
							$style_mesg = 'warnings';
						}
					}
				} else {
					$contactstatic = new Contact($db);
					$contactstatic->fetch($agf->fk_socpeople_OPCA);
					if (! empty($contactstatic->email)) {
						$withto[$agf->fk_socpeople_OPCA] = $contactstatic->lastname . ' ' . $contactstatic->firstname . ' - ' . $contactstatic->email . ' (' . $langs->trans('AgfMailTypeContactOPCA') . ')';
						$withtoname[$agf->fk_socpeople_OPCA]=$contactstatic->getFullName($langs);
						if (!empty($contactstatic->socname)) {
							$withtocompanyname[$contactstatic->socid]=$contactstatic->socname;
						}
					} else {
						$mesg = $langs->trans('AgfSendWarningNoMailOpca');
						$style_mesg = 'warnings';
					}
				}

				// Contact client
				if ($agf->sourcecontactid > 0) {
					$contactstatic = new Contact($db);
					$contactstatic->fetch($agf->sourcecontactid);
					$withto[$agf->sourcecontactid] = $contactstatic->lastname . ' ' . $contactstatic->firstname . ' - ' . $contactstatic->email . ' (' . $langs->trans('AgfSessionContact') . ')';
					$withtoname[$agf->sourcecontactid]=$contactstatic->getFullName($langs);
					if (!empty($contactstatic->socname)) {
						$withtocompanyname[$contactstatic->socid]=$contactstatic->socname;
					}
				}

				// All customer contact with client
				if (! empty($agf->fk_soc)) {
					$socstatic = new Societe($db);
					$socstatic->id = $agf->fk_soc;
					$soc_contact = $socstatic->contact_property_array('email');
					foreach ( $soc_contact as $id => $mail ) {
						$contactstatic = new Contact($db);
						$contactstatic->fetch($id);
						if (! empty($contactstatic->email)) {
							$withto[$id] = $contactstatic->lastname . ' ' . $contactstatic->firstname . ' - ' . $contactstatic->email . ' (' . $langs->trans('AgfSessionContact') . ')';
							$withtoname[$id]=$contactstatic->getFullName($langs);
							if (!empty($contactstatic->socname)) {
								$withtocompanyname[$contactstatic->socid]=$contactstatic->socname;
							}
						}
					}
				}

				$formmail->withto = $withto;
				$formmail->withtofree = 1;
				$formmail->withfile = 2;
			} elseif ($action == 'presend_presence_direct') {

				$formmail->withtopic = $langs->trans('AgfSendFeuillePresence', '__FORMINTITULE__');
				$formmail->withbody = $langs->trans('AgfSendFeuillePresenceBody', '__FORMINTITULE__');
				$formmail->param['models'] = 'fiche_presence_direct';
				$formmail->param['pre_action'] = 'presend_presence_direct';

				// Feuille de présence peut être aux formateurs
				$agftrainersess = new Agefodd_session_formateur($db);
				$num = $agftrainersess->fetch_formateur_per_session($id);
				$withto = array();
				if ($num > 0) {
					foreach ( $agftrainersess->lines as $formateur ) {
						if ($formateur->email != '')
							$withto[$formateur->socpeopleid] = $formateur->lastname . ' ' . $formateur->firstname . ' - ' . $formateur->email . ' (' . $langs->trans('AgfFormateur') . ')';
							$withtoname[$formateur->socpeopleid]=$formateur->lastname . ' ' . $formateur->firstname;
					}
				}

				// feuille de présence peut être envoyé à l'opca
				if ($agf->type_session && $socid) {
					$agf_opca = new Agefodd_opca($db);
					$result_opca = $agf_opca->getOpcaForTraineeInSession($socid, $id);
					if (! $result_opca) {
						$mesg = $langs->trans('AgfSendWarningNoMailOpca');
						$style_mesg = 'warnings';
					} else {
						$contactstatic = new Contact($db);
						$contactstatic->fetch($agf_opca->fk_socpeople_OPCA);
						if (! empty($contactstatic->email)) {
							$withto[$agf_opca->fk_socpeople_OPCA] = $contactstatic->lastname . ' ' . $contactstatic->firstname . ' - ' . $contactstatic->email . ' (' . $langs->trans('AgfMailTypeContactOPCA') . ')';
							$withtoname[$agf_opca->fk_socpeople_OPCA]=$contactstatic->getFullName($langs);
							if (!empty($contactstatic->socname)) {
								$withtocompanyname[$contactstatic->socid]=$contactstatic->socname;
							}
						} else {
							$mesg = $langs->trans('AgfSendWarningNoMailOpca');
							$style_mesg = 'warnings';
						}
					}
				} else {
					$contactstatic = new Contact($db);
					$contactstatic->fetch($agf->fk_socpeople_OPCA);
					if (! empty($contactstatic->email)) {
						$withto[$agf->fk_socpeople_OPCA] = $contactstatic->lastname . ' ' . $contactstatic->firstname . ' - ' . $contactstatic->email . ' (' . $langs->trans('AgfMailTypeContactOPCA') . ')';
						$withtoname[$agf->fk_socpeople_OPCA]=$contactstatic->getFullName($langs);
						if (!empty($contactstatic->socname)) {
							$withtocompanyname[$contactstatic->socid]=$contactstatic->socname;
						}
					} else {
						$mesg = $langs->trans('AgfSendWarningNoMailOpca');
						$style_mesg = 'warnings';
					}
				}

				// Contact client
				if ($agf->sourcecontactid > 0) {
					$contactstatic = new Contact($db);
					$contactstatic->fetch($agf->sourcecontactid);
					$withto[$agf->sourcecontactid] = $contactstatic->lastname . ' ' . $contactstatic->firstname . ' - ' . $contactstatic->email . ' (' . $langs->trans('AgfSessionContact') . ')';
					$withtoname[$agf->sourcecontactid]=$contactstatic->getFullName($langs);
					if (!empty($contactstatic->socname)) {
						$withtocompanyname[$contactstatic->socid]=$contactstatic->socname;
					}
				}

				// All customer contact with client
				if (! empty($agf->fk_soc)) {
					$socstatic = new Societe($db);
					$socstatic->id = $agf->fk_soc;
					$soc_contact = $socstatic->contact_property_array('email');
					foreach ( $soc_contact as $id => $mail ) {
						$contactstatic = new Contact($db);
						$contactstatic->fetch($id);
						if (! empty($contactstatic->email)) {
							$withto[$id] = $contactstatic->lastname . ' ' . $contactstatic->firstname . ' - ' . $contactstatic->email . ' (' . $langs->trans('AgfSessionContact') . ')';
							$withtoname[$id]=$contactstatic->getFullName($langs);
							if (!empty($contactstatic->socname)) {
								$withtocompanyname[$contactstatic->socid]=$contactstatic->socname;
							}
						}
					}
				}

				$formmail->withto = $withto;
				$formmail->withtofree = 1;
				$formmail->withfile = 2;
			} elseif ($action == 'presend_presence_empty') {
				$formmail->withtopic = $langs->trans('AgfSendFeuillePresence', '__FORMINTITULE__');
				$formmail->withbody = $langs->trans('AgfSendFeuillePresenceBody', '__FORMINTITULE__');
				$formmail->param['models'] = 'fiche_presence_empty';
				$formmail->param['pre_action'] = 'presend_presence_empty';

				// Feuille de présence peut être aux formateurs
				$agftrainersess = new Agefodd_session_formateur($db);
				$num = $agftrainersess->fetch_formateur_per_session($id);
				$withto = array();
				$withtoname = array();
				if ($num > 0) {
					foreach ( $agftrainersess->lines as $formateur ) {
						if ($formateur->email != '')
							$withto[$formateur->socpeopleid] = $formateur->lastname . ' ' . $formateur->firstname . ' - ' . $formateur->email . ' (' . $langs->trans('AgfFormateur') . ')';
							$withtoname[$formateur->socpeopleid] = $formateur->lastname . ' ' . $formateur->firstname;
					}
				}

				// feuille de présence peut être envoyé à l'opca
				if ($agf->type_session && $socid) {
					$agf_opca = new Agefodd_opca($db);
					$result_opca = $agf_opca->getOpcaForTraineeInSession($socid, $id);
					if (! $result_opca) {
						$mesg = $langs->trans('AgfSendWarningNoMailOpca');
						$style_mesg = 'warnings';
					} else {
						$contactstatic = new Contact($db);
						$contactstatic->fetch($agf_opca->fk_socpeople_OPCA);
						if (! empty($contactstatic->email)) {
							$withto[$agf_opca->fk_socpeople_OPCA] = $contactstatic->lastname . ' ' . $contactstatic->firstname . ' - ' . $contactstatic->email . ' (' . $langs->trans('AgfMailTypeContactOPCA') . ')';
							$withtoname[$agf_opca->fk_socpeople_OPCA] = $contactstatic->getFullName($langs);
							if (!empty($contactstatic->socname)) {
								$withtocompanyname[$contactstatic->socid]=$contactstatic->socname;
							}
						} else {
							$mesg = $langs->trans('AgfSendWarningNoMailOpca');
							$style_mesg = 'warnings';
						}
					}
				} else {
					$contactstatic = new Contact($db);
					$contactstatic->fetch($agf->fk_socpeople_OPCA);
					if (! empty($contactstatic->email)) {
						$withto[$agf->fk_socpeople_OPCA] = $contactstatic->lastname . ' ' . $contactstatic->firstname . ' - ' . $contactstatic->email . ' (' . $langs->trans('AgfMailTypeContactOPCA') . ')';
						$withtoname[$agf->fk_socpeople_OPCA] = $contactstatic->getFullName($langs);
						if (!empty($contactstatic->socname)) {
							$withtocompanyname[$contactstatic->socid]=$contactstatic->socname;
						}
					} else {
						$mesg = $langs->trans('AgfSendWarningNoMailOpca');
						$style_mesg = 'warnings';
					}
				}

				// Contact client
				if ($agf->sourcecontactid > 0) {
					$contactstatic = new Contact($db);
					$contactstatic->fetch($agf->sourcecontactid);
					$withto[$agf->sourcecontactid] = $contactstatic->lastname . ' ' . $contactstatic->firstname . ' - ' . $contactstatic->email . ' (' . $langs->trans('AgfSessionContact') . ')';
					$withtoname[$agf->sourcecontactid] = $contactstatic->getFullName($langs);
					if (!empty($contactstatic->socname)) {
						$withtocompanyname[$contactstatic->socid]=$contactstatic->socname;
					}
				}

				// All customer contact with client
				if (! empty($agf->fk_soc)) {
					$socstatic = new Societe($db);
					$socstatic->id = $agf->fk_soc;
					$soc_contact = $socstatic->contact_property_array('email');
					foreach ( $soc_contact as $id => $mail ) {
						$contactstatic = new Contact($db);
						$contactstatic->fetch($id);
						if (! empty($contactstatic->email)) {
							$withto[$id] = $contactstatic->lastname . ' ' . $contactstatic->firstname . ' - ' . $contactstatic->email . ' (' . $langs->trans('AgfSessionContact') . ')';
							$withtoname[$id] = $contactstatic->getFullName($langs);
							if (!empty($contactstatic->socname)) {
								$withtocompanyname[$contactstatic->socid]=$contactstatic->socname;
							}
						}
					}
				}

				$formmail->withto = $withto;
				$formmail->withtofree = 1;
				$formmail->withfile = 2;
			}elseif ($action == 'presend_pedago') {

				// Feuille de présence peut être aux formateurs
				$agftrainersess = new Agefodd_session_formateur($db);
				$num = $agftrainersess->fetch_formateur_per_session($id);
				$withto = array();
				$withtoname = array();
				if ($num > 0) {
					foreach ( $agftrainersess->lines as $formateur ) {
						if ($formateur->email != '')
							$withto[$formateur->socpeopleid] = $formateur->lastname . ' ' . $formateur->firstname . ' - ' . $formateur->email . ' (' . $langs->trans('AgfFormateur') . ')';
							$withtoname[$formateur->socpeopleid] = $formateur->lastname . ' ' . $formateur->firstname;
					}
				}

				// Contact client
				if ($agf->sourcecontactid > 0) {
					$contactstatic = new Contact($db);
					$contactstatic->fetch($agf->sourcecontactid);
					$withto[$agf->sourcecontactid] = $contactstatic->lastname . ' ' . $contactstatic->firstname . ' - ' . $contactstatic->email . ' (' . $langs->trans('AgfSessionContact') . ')';
					$withtoname[$agf->sourcecontactid] = $contactstatic->getFullName($langs);
					if (!empty($contactstatic->socname)) {
						$withtocompanyname[$contactstatic->socid]=$contactstatic->socname;
					}
				}

				// All customer contact with client
				if (! empty($agf->fk_soc)) {
					$socstatic = new Societe($db);
					$socstatic->id = $agf->fk_soc;
					$soc_contact = $socstatic->contact_property_array('email');
					foreach ( $soc_contact as $id => $mail ) {
						$contactstatic = new Contact($db);
						$contactstatic->fetch($id);
						if (! empty($contactstatic->email)) {
							$withto[$id] = $contactstatic->lastname . ' ' . $contactstatic->firstname . ' - ' . $contactstatic->email . ' (' . $langs->trans('AgfSessionContact') . ')';
							$withtoname[$id] = $contactstatic->getFullName($langs);
							if (!empty($contactstatic->socname)) {
								$withtocompanyname[$contactstatic->socid]=$contactstatic->socname;
							}
						}
					}
				}

				$formmail->withto = $withto;
				$formmail->withtopic = $langs->trans('AgfSendFichePedagogique', '__FORMINTITULE__');
				$formmail->withbody = $langs->trans('AgfSendFichePedagogiqueBody', '__FORMINTITULE__');
				$formmail->param['models'] = 'fiche_pedago';
				$formmail->param['pre_action'] = 'presend_pedago';
				$formmail->withtofree = 1;
			} elseif ($action == 'presend_mission_trainer') {
				$formmail->withtopic = $langs->trans('AgfSendFicheMissionTrainer', '__FORMINTITULE__');
				$formmail->withbody = $langs->trans('AgfSendFicheMissionTrainerBody', '__FORMINTITULE__');
				$formmail->param['models'] = 'mission_trainer';
				$formmail->param['pre_action'] = 'presend_mission_trainer';

				$agf_trainer_session = new Agefodd_session_formateur($db);
				$result = $agf_trainer_session->fetch($sessiontrainerid);
				if ($result < 0) {
					setEventMessage($agf_trainer_session->error, 'errors');
				} elseif (! empty($agf_trainer_session->formid)) {
					$withto = array();
					$withtoname= array();
					$agf_trainer = new Agefodd_teacher($db);
					$agf_trainer->fetch($agf_trainer_session->formid);
					$withto[$agf_trainer->fk_socpeople] = $agf_trainer->lastname . ' ' . $agf_trainer->name . ' - ' . $agf_trainer->email;
					$withtoname[$agf_trainer->fk_socpeople] = $agf_trainer->lastname . ' ' . $agf_trainer->name;
				}

				if (! empty($withto)) {
					$formmail->withto = $withto;
				}
				$formmail->withtofree = 1;
				$formmail->withfile = 2;
			} elseif ($action == 'presend_trainer_doc') {

				$formmail->withtopic = $langs->trans('AgfSendFicheDocTrainer', '__FORMINTITULE__');
				$formmail->withbody = $langs->trans('AgfSendFicheDocTrainerBody', '__FORMINTITULE__');
				$formmail->param['models'] = 'trainer_doc';
				$formmail->param['pre_action'] = 'presend_trainer_doc';

				$withto = array();
				$withtoname= array();

				if (empty($sessiontrainerid)) {
					// No trainer send in parameters send to all trainer
					$agf_trainer_session = new Agefodd_session_formateur($db);
					$result = $agf_trainer_session->fetch_formateur_per_session($agf->id);
					if ($result < 0) {
						setEventMessage($agf_trainer_session->error, 'errors');
					} elseif (is_array($agf_trainer_session->lines) && count($agf_trainer_session->lines) > 0) {

						$agf_trainer = new Agefodd_teacher($db);
						foreach ( $agf_trainer_session->lines as $linetrainer ) {
							$agf_trainer->fetch($linetrainer->opsid);
							$withto[$linetrainer->socpeopleid] = $linetrainer->lastname . ' ' . $linetrainer->name . ' - ' . $linetrainer->email;
							$withtoname[$linetrainer->socpeopleid] = $linetrainer->lastname . ' ' . $linetrainer->name;
						}
					}
				} else {
					$agf_trainer_session = new Agefodd_session_formateur($db);
					$result = $agf_trainer_session->fetch($sessiontrainerid);
					if ($result < 0) {
						setEventMessage($agf_trainer_session->error, 'errors');
					} elseif (! empty($agf_trainer_session->formid)) {
						$agf_trainer = new Agefodd_teacher($db);
						$agf_trainer->fetch($agf_trainer_session->formid);
						$withto[$agf_trainer->fk_socpeople] = $agf_trainer->lastname . ' ' . $agf_trainer->name . ' - ' . $agf_trainer->email;
						$withtoname[$agf_trainer->socpeopleid] = $agf_trainer->lastname . ' ' . $agf_trainer->name;
					}
				}

				if (! empty($withto)) {
					$formmail->withto = $withto;
				}
				$formmail->withtofree = 1;
				$formmail->withfile = 2;
			} elseif ($action == 'presend_convention') {

				$formmail->withtopic = $langs->trans('AgfSendConvention', '__FORMINTITULE__');
				// $formmail->withbody = $langs->trans('AgfSendConventionBody', '__FORMINTITULE__');

				$date_courier_conv = $agf->libSessionDate();

				$formmail->withbody = $langs->transnoentities('AgfPDFCourrierAcceuil4') . "\n\n\n";
				$formmail->withbody .= $langs->transnoentities('AgfPDFCourrierConv1Email') . "\n";
				$formmail->withbody .= '« ';
				if (! empty($agf->intitule_custo)) {
					$formmail->withbody .= $agf->intitule_custo;
				} else {
					$formmail->withbody .= $agf->formintitule;
				}
				$formmail->withbody .= " » " . $langs->transnoentities('AgfPDFCourrierConv2') . " " . $date_courier_conv . ".\n\n";
				$formmail->withbody .= $langs->transnoentities('AgfPDFCourrierConv3') . "\n\n";
				$formmail->withbody .= $langs->transnoentities('AgfPDFCourrierAcceuil11') . "\n\n";
				$formmail->withbody .= $langs->transnoentities('AgfPDFCourrierAcceuil13');

				$formmail->param['models'] = 'convention';
				$formmail->param['pre_action'] = 'presend_convention';

				$withto = array();
				$withtoname= array();

				// Convention peut être envoyé à l'opca ou au commanditaire

				// feuille de présence peut être envoyé à l'opca
				if ($agf->type_session && $socid) {
					$agf_opca = new Agefodd_opca($db);
					$result_opca = $agf_opca->getOpcaForTraineeInSession($socid, $id);
					if (! $result_opca) {
						$mesg = $langs->trans('AgfSendWarningNoMailOpca');
						$style_mesg = 'warnings';
					} else {
						$contactstatic = new Contact($db);
						$contactstatic->fetch($agf_opca->fk_socpeople_OPCA);
						if (! empty($contactstatic->email)) {
							$withto[$agf_opca->fk_socpeople_OPCA] = $contactstatic->lastname . ' ' . $contactstatic->firstname . ' - ' . $contactstatic->email . ' (' . $langs->trans('AgfMailTypeContactOPCA') . ')';
							$withtoname[$agf_opca->fk_socpeople_OPCA] = $contactstatic->getFullName($langs);
							if (!empty($contactstatic->socname)) {
								$withtocompanyname[$contactstatic->socid]=$contactstatic->socname;
							}
						} else {
							$mesg = $langs->trans('AgfSendWarningNoMailOpca');
							$style_mesg = 'warnings';
						}
					}
				} else {
					$contactstatic = new Contact($db);
					$contactstatic->fetch($agf->fk_socpeople_OPCA);
					if (! empty($contactstatic->email)) {
						$withto[$agf->fk_socpeople_OPCA] = $contactstatic->lastname . ' ' . $contactstatic->firstname . ' - ' . $contactstatic->email . ' (' . $langs->trans('AgfMailTypeContactOPCA') . ')';
						$withtoname[$agf->fk_socpeople_OPCA] = $contactstatic->getFullName($langs);
						if (!empty($contactstatic->socname)) {
							$withtocompanyname[$contactstatic->socid]=$contactstatic->socname;
						}
					} else {
						$mesg = $langs->trans('AgfSendWarningNoMailOpca');
						$style_mesg = 'warnings';
					}
				}

				// Contact Commanditaire
				if (! empty($agf->sourcecontactid)) {
					$contactstatic = new Contact($db);
					$contactstatic->fetch($agf->sourcecontactid);
					$withto[$agf->sourcecontactid] = $contactstatic->lastname . ' ' . $contactstatic->firstname . ' - ' . $contactstatic->email . ' (' . $langs->trans('AgfMailTypeContactCommanditaire') . ')';
					$withtoname[$agf->sourcecontactid] = $contactstatic->getFullName($langs);
					if (!empty($contactstatic->socname)) {
						$withtocompanyname[$contactstatic->socid]=$contactstatic->socname;
					}
				}

				// Contact participant
				if ($socid > 0) {
					$socstatic = new Societe($db);
					$socstatic->id = $socid;
					$soc_contact = $socstatic->contact_property_array('email');
					foreach ( $soc_contact as $id => $mail ) {
						$contactstatic = new Contact($db);
						$contactstatic->fetch($id);
						$withto[$id] = $contactstatic->lastname . ' ' . $contactstatic->firstname . ' - ' . $contactstatic->email . ' (' . $langs->trans('AgfMailTypeContactTrainee') . ')';
						$withtoname[$id] = $contactstatic->getFullName($langs);
						if (!empty($contactstatic->socname)) {
							$withtocompanyname[$contactstatic->socid]=$contactstatic->socname;
						}
					}
				}

				// All customer contact with client
				if (! empty($agf->fk_soc)) {
					$socstatic = new Societe($db);
					$socstatic->id = $agf->fk_soc;
					$soc_contact = $socstatic->contact_property_array('email');
					foreach ( $soc_contact as $id => $mail ) {
						$contactstatic = new Contact($db);
						$contactstatic->fetch($id);
						if (! empty($contactstatic->email)) {
							$withto[$id] = $contactstatic->lastname . ' ' . $contactstatic->firstname . ' - ' . $contactstatic->email . ' (' . $langs->trans('AgfSessionContact') . ')';
							$withtoname[$id] = $contactstatic->getFullName($langs);
							if (!empty($contactstatic->socname)) {
								$withtocompanyname[$contactstatic->socid]=$contactstatic->socname;
							}
						}
					}
				}

				if (! empty($withto)) {
					$formmail->withto = $withto;
				}
				$formmail->withtofree = 1;
				$formmail->withfile = 2;
			} elseif ($action == 'presend_attestation') {

				$withto = array();
				$withtoname= array();

				$formmail->withtopic = $langs->trans('AgfSendAttestation', '__FORMINTITULE__');
				$formmail->withbody = $langs->trans('AgfSendAttestationBody', '__FORMINTITULE__');
				$formmail->param['models'] = 'attestation';
				$formmail->param['pre_action'] = 'presend_attestation';

				// Attestation peut être envoyé à l'opca ou au commanditaire if inter-entreprise
				if ($agf->type_session && $socid) {
					$agf_opca = new Agefodd_opca($db);
					$result_opca = $agf_opca->getOpcaForTraineeInSession($socid, $id);
					if (! $result_opca) {
						setEventMessage($langs->trans('AgfSendWarningNoMailOpca'), 'warnings');
					} elseif ($agf_opca->is_OPCA) {
						$contactstatic = new Contact($db);
						$contactstatic->fetch($agf_opca->fk_socpeople_OPCA);
						if (! empty($contactstatic->email)) {
							$withto[$agf_opca->fk_socpeople_OPCA] = $contactstatic->lastname . ' ' . $contactstatic->firstname . ' - ' . $contactstatic->email . ' (' . $langs->trans('AgfMailTypeContactOPCA') . ')';
							$withtoname[$agf_opca->fk_socpeople_OPCA] = $contactstatic->getFullName($langs);
							if (!empty($contactstatic->socname)) {
								$withtocompanyname[$contactstatic->socid]=$contactstatic->socname;
							}
						} else {
							$mesg = $langs->trans('AgfSendWarningNoMailOpca');
							$style_mesg = 'warnings';
						}
					}

					// Contact participant
					if ($socid > 0) {
						$socstatic = new Societe($db);
						$socstatic->id = $socid;
						$soc_contact = $socstatic->contact_property_array('email');
						foreach ( $soc_contact as $id => $mail ) {
							$contactstatic = new Contact($db);
							$contactstatic->fetch($id);
							$withto[$id] = $contactstatic->lastname . ' ' . $contactstatic->firstname . ' - ' . $contactstatic->email . ' (' . $langs->trans('AgfMailTypeContactTrainee') . ')';
							$withtoname[$id] = $contactstatic->getFullName($langs);
							if (!empty($contactstatic->socname)) {
								$withtocompanyname[$contactstatic->socid]=$contactstatic->socname;
							}
						}
					}
				} else {
					if ($agf->is_OPCA) {
						$contactstatic = new Contact($db);
						$contactstatic->fetch($agf->fk_socpeople_OPCA);
						if (! empty($contactstatic->email)) {
							$withto[$agf->fk_socpeople_OPCA] = $contactstatic->lastname . ' ' . $contactstatic->firstname . ' - ' . $contactstatic->email . ' (' . $langs->trans('AgfMailTypeContactOPCA') . ')';
							$withtoname[$agf->fk_socpeople_OPCA] = $contactstatic->getFullName($langs);
							if (!empty($contactstatic->socname)) {
								$withtocompanyname[$contactstatic->socid]=$contactstatic->socname;
							}
						} else {
							$mesg = $langs->trans('AgfSendWarningNoMailOpca');
							$style_mesg = 'warnings';
						}
					}
				}

				// Trainee List
				$agf_trainnee = new Agefodd_session_stagiaire($db);
				$agf_trainnee->fetch_stagiaire_per_session($agf->id, $socid);
				foreach ( $agf_trainnee->lines as $line ) {
					if (! empty($line->email) && (! empty($line->fk_socpeople))) {
						if (! array_key_exists($line->fk_socpeople, $withto)) {
							$withto[$line->fk_socpeople] = $line->nom . ' ' . $line->prenom . ' - ' . $line->email . ' (' . $langs->trans('AgfMailTypeContactCommanditaire') . ')';
							$withtoname[$line->fk_socpeople] = $line->nom . ' ' . $line->prenom;
						}
					}
				}

				// Contact commanditaire
				if (! empty($agf->sourcecontactid)) {
					$contactstatic = new Contact($db);
					$contactstatic->fetch($agf->sourcecontactid);
					$withto[$agf->sourcecontactid] = $contactstatic->lastname . ' ' . $contactstatic->firstname . ' - ' . $contactstatic->email . ' (' . $langs->trans('AgfSessionContact') . ')';
					$withtoname[$agf->sourcecontactid] = $contactstatic->getFullName($langs);
					if (!empty($contactstatic->socname)) {
						$withtocompanyname[$contactstatic->socid]=$contactstatic->socname;
					}
				}

				// All customer contact with client
				if (! empty($agf->fk_soc)) {
					$socstatic = new Societe($db);
					$socstatic->id = $agf->fk_soc;
					$soc_contact = $socstatic->contact_property_array('email');
					foreach ( $soc_contact as $id => $mail ) {
						$contactstatic = new Contact($db);
						$contactstatic->fetch($id);
						if (! empty($contactstatic->email)) {
							$withto[$id] = $contactstatic->lastname . ' ' . $contactstatic->firstname . ' - ' . $contactstatic->email . ' (' . $langs->trans('AgfSessionContact') . ')';
							$withtoname[$id] = $contactstatic->getFullName($langs);
							if (!empty($contactstatic->socname)) {
								$withtocompanyname[$contactstatic->socid]=$contactstatic->socname;
							}
						}
					}
				}

				if (! empty($withto)) {
					$formmail->withto = $withto;
				}
				$formmail->withtofree = 1;
			} elseif ($action == "presend_cloture") {
				$withto = array();
				$withtoname= array();

				$formmail->withtopic = $langs->trans('AgfSendDossierCloture', '__FORMINTITULE__');
				$formmail->withbody = $langs->trans('AgfSendDossierClotureBody', '__FORMINTITULE__');
				$formmail->param['models'] = 'cloture';
				$formmail->param['pre_action'] = 'presend_cloture';

				// Envoi de fichier libre
				$formmail->withfile = 2;

				// Dossier de cloture peut être envoyé au participant ou à l'opca ou au commanditaire
				if ($agf->type_session && $socid) {
					$agf_opca = new Agefodd_opca($db);
					$result_opca = $agf_opca->getOpcaForTraineeInSession($socid, $id);
					if (! $result_opca) {
						setEventMessage($langs->trans('AgfSendWarningNoMailOpca'), 'warnings');
					} elseif ($agf_opca->is_OPCA) {
						$contactstatic = new Contact($db);
						$contactstatic->fetch($agf_opca->fk_socpeople_OPCA);
						if (! empty($contactstatic->email)) {
							$withto[$agf_opca->fk_socpeople_OPCA] = $contactstatic->lastname . ' ' . $contactstatic->firstname . ' - ' . $contactstatic->email . ' (' . $langs->trans('AgfMailTypeContactOPCA') . ')';
							$withtoname[$agf_opca->fk_socpeople_OPCA] = $contactstatic->getFullName($langs);
							if (!empty($contactstatic->socname)) {
								$withtocompanyname[$contactstatic->socid]=$contactstatic->socname;
							}
						} else {
							$mesg = $langs->trans('AgfSendWarningNoMailOpca');
							$style_mesg = 'warnings';
						}
					}

					// Contact participant
					if ($agf->type_session && $socid > 0) {
						$socstatic = new Societe($db);
						$socstatic->id = $socid;
						$soc_contact = $socstatic->contact_property_array('email');
						foreach ( $soc_contact as $id => $mail ) {
							$contactstatic = new Contact($db);
							$contactstatic->fetch($id);
							$withto[$id] = $contactstatic->lastname . ' ' . $contactstatic->firstname . ' - ' . $contactstatic->email . ' (' . $langs->trans('AgfMailTypeContactTrainee') . ')';
							$withtoname[$id] = $contactstatic->getFullName($langs);
							if (!empty($contactstatic->socname)) {
								$withtocompanyname[$contactstatic->socid]=$contactstatic->socname;
							}
						}
					}
				} else {
					if ($agf->is_OPCA) {
						$contactstatic = new Contact($db);
						$contactstatic->fetch($agf->fk_socpeople_OPCA);
						if (! empty($contactstatic->email)) {
							$withto[$agf->fk_socpeople_OPCA] = $contactstatic->lastname . ' ' . $contactstatic->firstname . ' - ' . $contactstatic->email . ' (' . $langs->trans('AgfMailTypeContactOPCA') . ')';
							$withtoname[$agf->fk_socpeople_OPCA] = $contactstatic->getFullName($langs);
							if (!empty($contactstatic->socname)) {
								$withtocompanyname[$contactstatic->socid]=$contactstatic->socname;
							}
						} else {
							$mesg = $langs->trans('AgfSendWarningNoMailOpca');
							$style_mesg = 'warnings';
						}
					}
				}

				// Contact commanditaire
				if (! empty($agf->sourcecontactid)) {
					$contactstatic = new Contact($db);
					$contactstatic->fetch($agf->sourcecontactid);
					$withto[$agf->sourcecontactid] = $contactstatic->lastname . ' ' . $contactstatic->firstname . ' - ' . $contactstatic->email . ' (' . $langs->trans('AgfSessionContact') . ')';
					$withtoname[$agf->sourcecontactid] = $contactstatic->getFullName($langs);
					if (!empty($contactstatic->socname)) {
						$withtocompanyname[$contactstatic->socid]=$contactstatic->socname;
					}
				}

				// All customer contact with client
				if (! empty($agf->fk_soc)) {
					$socstatic = new Societe($db);
					$socstatic->id = $agf->fk_soc;
					$soc_contact = $socstatic->contact_property_array('email');
					foreach ( $soc_contact as $id => $mail ) {
						$contactstatic = new Contact($db);
						$contactstatic->fetch($id);
						if (! empty($contactstatic->email)) {
							$withto[$id] = $contactstatic->lastname . ' ' . $contactstatic->firstname . ' - ' . $contactstatic->email . ' (' . $langs->trans('AgfSessionContact') . ')';
							$withtoname[$id] = $contactstatic->getFullName($langs);
							if (!empty($contactstatic->socname)) {
								$withtocompanyname[$contactstatic->socid]=$contactstatic->socname;
							}
						}
					}
				}
				if (! empty($withto)) {
					$formmail->withto = $withto;
				}
				$formmail->withtofree = 1;
			} elseif ($action == "presend_convocation") {

				$formmail->withtopic = $langs->trans('AgfSendConvocation', '__FORMINTITULE__');
				$formmail->withbody = $langs->trans('AgfSendConvocationBody', '__FORMINTITULE__');
				$formmail->param['models'] = 'convocation';
				$formmail->param['pre_action'] = 'presend_convocation';

				$withto = array();
				$withtoname= array();

				// Envoi de fichier libre
				$formmail->withfile = 2;

				// Dossier de cloture peut être envoyé au participant ou à l'opca ou au commanditaire
				if ($agf->type_session && $socid) {
					$agf_opca = new Agefodd_opca($db);
					$result_opca = $agf_opca->getOpcaForTraineeInSession($socid, $id);
					if (! $result_opca) {
						setEventMessage($langs->trans('AgfSendWarningNoMailOpca'), 'warnings');
					} elseif ($agf_opca->is_OPCA) {
						$contactstatic = new Contact($db);
						$contactstatic->fetch($agf_opca->fk_socpeople_OPCA);
						if (! empty($contactstatic->email)) {
							$withto[$agf_opca->fk_socpeople_OPCA] = $contactstatic->lastname . ' ' . $contactstatic->firstname . ' - ' . $contactstatic->email . ' (' . $langs->trans('AgfMailTypeContactOPCA') . ')';
							$withtoname[$agf_opca->fk_socpeople_OPCA] = $contactstatic->getFullName($langs);
							if (!empty($contactstatic->socname)) {
								$withtocompanyname[$contactstatic->socid]=$contactstatic->socname;
							}
						} else {
							$mesg = $langs->trans('AgfSendWarningNoMailOpca');
							$style_mesg = 'warnings';
						}
					}
				} else {
					if ($agf->is_OPCA) {
						$contactstatic = new Contact($db);
						$contactstatic->fetch($agf->fk_socpeople_OPCA);
						if (! empty($contactstatic->email)) {
							$withto[$agf->fk_socpeople_OPCA] = $contactstatic->lastname . ' ' . $contactstatic->firstname . ' - ' . $contactstatic->email . ' (' . $langs->trans('AgfMailTypeContactOPCA') . ')';
							$withtoname[$agf->fk_socpeople_OPCA] = $contactstatic->getFullName($langs);
							if (!empty($contactstatic->socname)) {
								$withtocompanyname[$contactstatic->socid]=$contactstatic->socname;
							}
						} else {
							$mesg = $langs->trans('AgfSendWarningNoMailOpca');
							$style_mesg = 'warnings';
						}
					}
				}

				// Trainee List
				$agf_trainnee = new Agefodd_session_stagiaire($db);
				$agf_trainnee->fetch_stagiaire_per_session($agf->id, $socid);
				foreach ( $agf_trainnee->lines as $line ) {
					if (! empty($line->email) && (! empty($line->fk_socpeople))) {
						if (! array_key_exists($line->fk_socpeople, $withto)) {
							$withto[$line->fk_socpeople] = $line->nom . ' ' . $line->prenom . ' - ' . $line->email . ' (' . $langs->trans('AgfMailTypeContactTrainee') . ')';
							$withtoname[$line->fk_socpeople] = $line->nom . ' ' . $line->prenom;
						}
						if (!empty($line->socname)) {
							$withtocompanyname[$line->socid]=$line->socname;
						}
					}
				}

				// Contact commanditaire
				if (! empty($agf->sourcecontactid)) {
					$contactstatic = new Contact($db);
					$contactstatic->fetch($agf->sourcecontactid);
					$withto[$agf->sourcecontactid] = $contactstatic->lastname . ' ' . $contactstatic->firstname . ' - ' . $contactstatic->email . ' (' . $langs->trans('AgfSessionContact') . ')';
					$withtoname[$agf->sourcecontactid] = $contactstatic->getFullName($langs);
					if (!empty($contactstatic->socname)) {
						$withtocompanyname[$contactstatic->socid]=$contactstatic->socname;
					}
				}

				// All customer contact with client
				if (! empty($agf->fk_soc)) {
					$socstatic = new Societe($db);
					$socstatic->id = $agf->fk_soc;
					$soc_contact = $socstatic->contact_property_array('email');
					foreach ( $soc_contact as $id => $mail ) {
						$contactstatic = new Contact($db);
						$contactstatic->fetch($id);
						if (! empty($contactstatic->email)) {
							$withto[$id] = $contactstatic->lastname . ' ' . $contactstatic->firstname . ' - ' . $contactstatic->email . ' (' . $langs->trans('AgfSessionContact') . ')';
							$withtoname[$id] = $contactstatic->getFullName($langs);
							if (!empty($contactstatic->socname)) {
								$withtocompanyname[$contactstatic->socid]=$contactstatic->socname;
							}
						}
					}
				}

				if (! empty($withto)) {
					$formmail->withto = $withto;
				}
				$formmail->withtofree = 1;
			} elseif ($action == "presend_conseils") {

				$withto = array();
				$withtoname= array();

				$formmail->withtopic = $langs->trans('AgfSendConseil', '__FORMINTITULE__');
				$formmail->withbody = $langs->trans('AgfSendConseilBody', '__FORMINTITULE__');
				$formmail->param['models'] = 'conseils';
				$formmail->param['pre_action'] = 'presend_conseils';

				// Envoi de fichier libre
				$formmail->withfile = 2;

				// Trainee List
				$agf_trainnee = new Agefodd_session_stagiaire($db);
				$agf_trainnee->fetch_stagiaire_per_session($agf->id, $socid);
				foreach ( $agf_trainnee->lines as $line ) {
					if (! empty($line->email) && (! empty($line->fk_socpeople))) {
						if (! array_key_exists($line->fk_socpeople, $withto)) {
							$withto[$line->fk_socpeople] = $line->nom . ' ' . $line->prenom . ' - ' . $line->email . ' (' . $langs->trans('AgfMailTypeContactTrainee') . ')';
							$withtoname[$line->fk_socpeople] = $line->nom . ' ' . $line->prenom;
						}
					}
				}

				// Contact commanditaire
				if (! empty($agf->sourcecontactid)) {
					$contactstatic = new Contact($db);
					$contactstatic->fetch($agf->sourcecontactid);
					$withto[$agf->sourcecontactid] = $contactstatic->lastname . ' ' . $contactstatic->firstname . ' - ' . $contactstatic->email . ' (' . $langs->trans('AgfSessionContact') . ')';
					$withtoname[$agf->sourcecontactid] = $contactstatic->getFullName($langs);
					if (!empty($contactstatic->socname)) {
						$withtocompanyname[$contactstatic->socid]=$contactstatic->socname;
					}
				}

				// All customer contact with client
				if (! empty($agf->fk_soc)) {
					$socstatic = new Societe($db);
					$socstatic->id = $agf->fk_soc;
					$soc_contact = $socstatic->contact_property_array('email');
					foreach ( $soc_contact as $id => $mail ) {
						$contactstatic = new Contact($db);
						$contactstatic->fetch($id);
						if (! empty($contactstatic->email)) {
							$withto[$id] = $contactstatic->lastname . ' ' . $contactstatic->firstname . ' - ' . $contactstatic->email . ' (' . $langs->trans('AgfSessionContact') . ')';
							$withtoname[$id] = $contactstatic->getFullName($langs);
							if (!empty($contactstatic->socname)) {
								$withtocompanyname[$contactstatic->socid]=$contactstatic->socname;
							}
						}
					}
				}

				if (! empty($withto)) {
					$formmail->withto = $withto;
				}
				$formmail->withtofree = 1;
			} elseif ($action == "presend_accueil") {

				$withto = array();
				$withtoname= array();

				$formmail->withtopic = $langs->trans('AgfSendCourrierAcceuil', '__FORMINTITULE__');
				$formmail->withbody = $langs->trans('AgfSendCourrierAcceuilBody', '__FORMINTITULE__');
				$formmail->param['models'] = 'courrier-accueil';
				$formmail->param['pre_action'] = 'presend_accueil';

				// Envoi de fichier libre
				$formmail->withfile = 2;

				// Trainee List
				$agf_trainnee = new Agefodd_session_stagiaire($db);
				$agf_trainnee->fetch_stagiaire_per_session($agf->id, $socid);
				foreach ( $agf_trainnee->lines as $line ) {
					if (! empty($line->email) && (! empty($line->fk_socpeople))) {
						if (! array_key_exists($line->fk_socpeople, $withto)) {
							$withto[$line->fk_socpeople] = $line->nom . ' ' . $line->prenom . ' - ' . $line->email . ' (' . $langs->trans('AgfMailTypeContactTrainee') . ')';
							$withtoname[$line->fk_socpeople] = $line->nom . ' ' . $line->prenom;
						}
					}
				}

				// Contact commanditaire
				if (! empty($agf->sourcecontactid)) {
					$contactstatic = new Contact($db);
					$contactstatic->fetch($agf->sourcecontactid);
					$withto[$agf->sourcecontactid] = $contactstatic->lastname . ' ' . $contactstatic->firstname . ' - ' . $contactstatic->email . ' (' . $langs->trans('AgfSessionContact') . ')';
					$withtoname[$agf->sourcecontactid] = $contactstatic->getFullName($langs);
					if (!empty($contactstatic->socname)) {
						$withtocompanyname[$contactstatic->socid]=$contactstatic->socname;
					}
				}

				// All customer contact with client
				if (! empty($agf->fk_soc)) {
					$socstatic = new Societe($db);
					$socstatic->id = $agf->fk_soc;
					$soc_contact = $socstatic->contact_property_array('email');
					foreach ( $soc_contact as $id => $mail ) {
						$contactstatic = new Contact($db);
						$contactstatic->fetch($id);
						if (! empty($contactstatic->email)) {
							$withto[$id] = $contactstatic->lastname . ' ' . $contactstatic->firstname . ' - ' . $contactstatic->email . ' (' . $langs->trans('AgfSessionContact') . ')';
							$withtoname[$id] = $contactstatic->getFullName($langs);
							if (!empty($contactstatic->socname)) {
								$withtocompanyname[$contactstatic->socid]=$contactstatic->socname;
							}
						}
					}
				}

				if (! empty($withto)) {
					$formmail->withto = $withto;
				}
				$formmail->withtofree = 1;
			} elseif ($action == 'presend_attestationendtraining') {

				$withto = array();
				$withtoname = array();

				$formmail->withtopic = $langs->trans('AgfSendAttestation', '__FORMINTITULE__');
				$formmail->withbody = $langs->trans('AgfSendAttestationBody', '__FORMINTITULE__');
				$formmail->param['models'] = 'attestationendtraining';
				$formmail->param['pre_action'] = 'presend_attestationendtraining';

				// Attestation peut être envoyé à l'opca ou au commanditaire if inter-entreprise
				if ($agf->type_session && $socid) {
					$agf_opca = new Agefodd_opca($db);
					$result_opca = $agf_opca->getOpcaForTraineeInSession($socid, $id);
					if (! $result_opca) {
						setEventMessage($langs->trans('AgfSendWarningNoMailOpca'), 'warnings');
					} elseif ($agf_opca->is_OPCA) {
						$contactstatic = new Contact($db);
						$contactstatic->fetch($agf_opca->fk_socpeople_OPCA);
						if (! empty($contactstatic->email)) {
							$withto[$agf_opca->fk_socpeople_OPCA] = $contactstatic->lastname . ' ' . $contactstatic->firstname . ' - ' . $contactstatic->email . ' (' . $langs->trans('AgfMailTypeContactOPCA') . ')';
							$withtoname[$agf_opca->fk_socpeople_OPCA] = $contactstatic->getFullName($langs);
							if (!empty($contactstatic->socname)) {
								$withtocompanyname[$contactstatic->socid]=$contactstatic->socname;
							}
						} else {
							$mesg = $langs->trans('AgfSendWarningNoMailOpca');
							$style_mesg = 'warnings';
						}
					}

					// Contact participant
					if ($socid > 0) {
						$socstatic = new Societe($db);
						$socstatic->id = $socid;
						$soc_contact = $socstatic->contact_property_array('email');
						foreach ( $soc_contact as $id => $mail ) {
							$contactstatic = new Contact($db);
							$contactstatic->fetch($id);
							$withto[$id] = $contactstatic->lastname . ' ' . $contactstatic->firstname . ' - ' . $contactstatic->email . ' (' . $langs->trans('AgfMailTypeContactTrainee') . ')';
							$withtoname[$id] = $contactstatic->getFullName($langs);
							if (!empty($contactstatic->socname)) {
								$withtocompanyname[$contactstatic->socid]=$contactstatic->socname;
							}
						}
					}
				} else {
					if ($agf->is_OPCA) {
						$contactstatic = new Contact($db);
						$contactstatic->fetch($agf->fk_socpeople_OPCA);
						if (! empty($contactstatic->email)) {
							$withto[$agf->fk_socpeople_OPCA] = $contactstatic->lastname . ' ' . $contactstatic->firstname . ' - ' . $contactstatic->email . ' (' . $langs->trans('AgfMailTypeContactOPCA') . ')';
							$withtoname[$agf->fk_socpeople_OPCA] = $contactstatic->getFullName($langs);
							if (!empty($contactstatic->socname)) {
								$withtocompanyname[$contactstatic->socid]=$contactstatic->socname;
							}
						} else {
							$mesg = $langs->trans('AgfSendWarningNoMailOpca');
							$style_mesg = 'warnings';
						}
					}
				}

				// Trainee List
				$agf_trainnee = new Agefodd_session_stagiaire($db);
				$agf_trainnee->fetch_stagiaire_per_session($agf->id, $socid);
				foreach ( $agf_trainnee->lines as $line ) {
					if (! empty($line->email) && (! empty($line->fk_socpeople))) {
						if (! array_key_exists($line->fk_socpeople, $withto)) {
							$withto[$line->fk_socpeople] = $line->nom . ' ' . $line->prenom . ' - ' . $line->email . ' (' . $langs->trans('AgfMailTypeContactCommanditaire') . ')';
							$withtoname[$line->fk_socpeople] = $line->nom . ' ' . $line->prenom;
						}
					}
				}

				// Contact commanditaire
				if (! empty($agf->sourcecontactid)) {
					$contactstatic = new Contact($db);
					$contactstatic->fetch($agf->sourcecontactid);
					$withto[$agf->sourcecontactid] = $contactstatic->lastname . ' ' . $contactstatic->firstname . ' - ' . $contactstatic->email . ' (' . $langs->trans('AgfSessionContact') . ')';
					$withtoname[$agf->sourcecontactid] = $contactstatic->getFullName($langs);
					if (!empty($contactstatic->socname)) {
						$withtocompanyname[$contactstatic->socid]=$contactstatic->socname;
					}
				}

				// All customer contact with client
				if (! empty($agf->fk_soc)) {
					$socstatic = new Societe($db);
					$socstatic->id = $agf->fk_soc;
					$soc_contact = $socstatic->contact_property_array('email');
					foreach ( $soc_contact as $id => $mail ) {
						$contactstatic = new Contact($db);
						$contactstatic->fetch($id);
						if (! empty($contactstatic->email)) {
							$withto[$id] = $contactstatic->lastname . ' ' . $contactstatic->firstname . ' - ' . $contactstatic->email . ' (' . $langs->trans('AgfSessionContact') . ')';
							$withtoname[$id] = $contactstatic->getFullName($langs);
							if (!empty($contactstatic->socname)) {
								$withtocompanyname[$contactstatic->socid]=$contactstatic->socname;
							}
						}
					}
				}

				if (! empty($withto)) {
					$formmail->withto = $withto;
				}
				$formmail->withtofree = 1;
			}

			$formmail->withdeliveryreceipt = 1;

			$formmail->withbody .= '\n\n__SIGNATURE__\n';

			if (! empty($conf->global->FCKEDITOR_ENABLE_MAIL)) {
				$formmail->withbody = str_replace('\n', '<BR>', $formmail->withbody);
			}

			// Tableau des substitutions
			if (! empty($agf->intitule_custo)) {
				$formmail->substit ['__FORMINTITULE__'] = $agf->intitule_custo;

			} else {
				$formmail->substit ['__FORMINTITULE__'] = $agf->formintitule;
			}

			$date_conv =$agf->libSessionDate('daytext');
			$formmail->substit ['__FORMDATESESSION__'] =$date_conv;

			$formmail->substit['__SIGNATURE__'] = $user->signature;

			if (is_array($withtocompanyname) && count($withtocompanyname)>0) {
				if (! empty($conf->global->FCKEDITOR_ENABLE_MAIL)) {
					$formmail->substit['__THIRDPARTY_NAME__'] = implode('<BR>',$withtocompanyname);
				} else {
					$formmail->substit['__THIRDPARTY_NAME__'] = implode(', ',$withtocompanyname);
				}
			}

			if (is_array($withtoname) && count($withtoname)>0) {
				if (! empty($conf->global->FCKEDITOR_ENABLE_MAIL)) {
					$formmail->substit['__CONTACTCIVNAME__'] = implode('<BR>',$withtoname);
				} else {
					$formmail->substit['__CONTACTCIVNAME__'] = implode(', ',$withtoname);
				}

			}

			$formmail->substit['__PERSONALIZED__'] = '';

			// Tableau des parametres complementaires
			$formmail->param['action'] = 'send';
			$formmail->param['models_id'] = GETPOST('modelmailselected');
			$formmail->param['id'] = $agf->id;
			$formmail->param['returnurl'] = $_SERVER["PHP_SELF"] . '?id=' . $agf->id;

			if ($action == 'presend_pedago') {
				print_fiche_titre($langs->trans('AgfSendDocuments') . ' ' . $langs->trans('AgfFichePedagogique'), '', dol_buildpath('/agefodd/img/mail_generic.png', 1), 1);
			} elseif ($action == 'presend_presence') {
				print_fiche_titre($langs->trans('AgfSendDocuments') . ' ' . $langs->trans('AgfFichePresence'), '', dol_buildpath('/agefodd/img/mail_generic.png', 1), 1);
				$filename = 'fiche_presence_' . $agf->id . '.pdf';
			} elseif ($action == 'presend_presence_direct') {
				print_fiche_titre($langs->trans('AgfSendDocuments') . ' ' . $langs->trans('AgfFichePresence'), '', dol_buildpath('/agefodd/img/mail_generic.png', 1), 1);
				$filename = 'fiche_presence_direct_' . $agf->id . '.pdf';
			}elseif ($action == 'presend_presence_empty') {
				print_fiche_titre($langs->trans('AgfSendDocuments') . ' ' . $langs->trans('AgfFichePresence'), '', dol_buildpath('/agefodd/img/mail_generic.png', 1), 1);
				$filename = 'fiche_presence_empty_' . $agf->id . '.pdf';
			}elseif ($action == 'presend_convocation') {
				print_fiche_titre($langs->trans('AgfSendDocuments') . ' ' . $langs->trans('AgfPDFConvocation'), '', dol_buildpath('/agefodd/img/mail_generic.png', 1), 1);
			} elseif ($action == 'presend_conseils') {
				print_fiche_titre($langs->trans('AgfSendDocuments') . ' ' . $langs->trans('AgfConseilsPratique'), '', dol_buildpath('/agefodd/img/mail_generic.png', 1), 1);
			} elseif ($action == 'presend_convention') {
				print_fiche_titre($langs->trans('AgfSendDocuments') . ' ' . $langs->trans('AgfConvention'), '', dol_buildpath('/agefodd/img/mail_generic.png', 1), 1);
			} elseif ($action == 'presend_attestation') {
				print_fiche_titre($langs->trans('AgfSendDocuments') . ' ' . $langs->trans('AgfSendAttestation'), '', dol_buildpath('/agefodd/img/mail_generic.png', 1), 1);
			} elseif ($action == 'presend_cloture') {
				print_fiche_titre($langs->trans('AgfSendDocuments') . ' ' . $langs->trans('AgfCourrierCloture'), '', dol_buildpath('/agefodd/img/mail_generic.png', 1), 1);
			} elseif ($action == 'presend_accueil') {
				print_fiche_titre($langs->trans('AgfSendDocuments') . ' ' . $langs->trans('AgfCourrierAcceuil'), '', dol_buildpath('/agefodd/img/mail_generic.png', 1), 1);
			} elseif ($action == 'presend_mission_trainer') {
				print_fiche_titre($langs->trans('AgfSendDocuments') . ' ' . $langs->trans('AgfTrainerMissionLetter'), '', dol_buildpath('/agefodd/img/mail_generic.png', 1), 1);
			} elseif ($action == 'presend_attestationendtraining') {
				print_fiche_titre($langs->trans('AgfSendDocuments') . ' ' . $langs->trans('AgfSendAttestation'), '', dol_buildpath('/agefodd/img/mail_generic.png', 1), 1);
			}
			$formmail->show_form();

			if (! empty($mesg)) {
				setEventMessage($mesg, $style_mesg);
			}
		}

		/*
		 * Envoi fiche pédagogique
		 */
		if (! $action || ! empty($cancel)) {

			dol_htmloutput_mesg($mesg, $mesgs);

			print '<table class="border" width="100%">' . "\n";

			print '<tr class="liste_titre">' . "\n";
			print '<td colspan=3>';
			print $langs->trans("AgfSendCommonDocs") . '</td>' . "\n";
			print '</tr>' . "\n";

			// Avant la formation
			print '<tr><td colspan=3 style="background-color:#d5baa8;">' . $langs->trans("AgfCommonDocs") . '</td></tr>' . "\n";

			include_once (DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php');

			document_send_line($langs->trans("ActionFICHEPEDAGO_SENTBYMAIL"), 'fiche_pedago');
			document_send_line($langs->trans("AgfFichePresence"), 'fiche_presence');
			document_send_line($langs->trans("AgfFichePresenceDirect"), 'fiche_presence_direct');
			document_send_line($langs->trans("AgfFichePresenceEmpty"), 'fiche_presence_empty');
			document_send_line($langs->trans("AgfConseilsPratique"), 'conseils');

			print '</table>' . "\n";
			print '&nbsp;' . "\n";

			$linecount = count($agf->lines);

			for($i = 0; $i < $linecount; $i ++) {
				if (! empty($agf->lines[$i]->socid)) {
					print '<table class="border" width="100%">' . "\n";

					print '<tr class="liste_titre">' . "\n";
					print '<td colspan=3>';
					print '<a href="' . DOL_URL_ROOT . '/comm/card.php?socid=' . $agf->lines[$i]->socid . '">' . $agf->lines[$i]->socname . '</a></td>' . "\n";
					print '</tr>' . "\n";

					document_send_line($langs->trans("AgfPDFConvocation"), "convocation", $agf->lines[$i]->socid);
					document_send_line($langs->trans("AgfConvention"), "convention", $agf->lines[$i]->socid);
					document_send_line($langs->trans("AgfAttestationEndTraining"), "attestationendtraining", $agf->lines[$i]->socid);
					document_send_line($langs->trans("AgfSendAttestation"), "attestation", $agf->lines[$i]->socid);
					document_send_line($langs->trans("AgfCourrierAcceuil"), "accueil", $agf->lines[$i]->socid);
					document_send_line($langs->trans("AgfCourrierCloture"), "cloture", $agf->lines[$i]->socid);

					print '</table>';
					if ($i < $linecount)
						print '&nbsp;' . "\n";
				}
			}

			$agf_trainer = new Agefodd_session_formateur($db);
			$agf_trainer->fetch_formateur_per_session($id);
			if (is_array($agf_trainer->lines) && count($agf_trainer->lines) > 0) {
				print '<table class="border" width="100%">' . "\n";
				foreach ( $agf_trainer->lines as $line ) {
					print '<tr class="liste_titre">' . "\n";
					print '<td colspan=3>';
					print '<a href="' . dol_buildpath('/agefodd/trainer/session.php', 1) . '?id=' . $line->formid . '" name="trainerid' . $line->opsid . '" id="trainerid' . $line->opsid . '">' . $line->lastname . ' ' . $line->fullname . '</a>';
					print '</td>' . "\n";
					print '</tr>' . "\n";
					document_send_line($langs->trans("AgfTrainerMissionLetter"), "mission_trainer", $line->opsid);
					document_send_line($langs->trans("AgfTrainerDocument"), "trainer_doc", $line->opsid);
				}
				print '</table>';
			}
			print '</div>' . "\n";
		}

		print '<div class="tabsAction">';
		if ($action != 'view_actioncomm') {
			print '<a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?action=view_actioncomm&id=' . $id . '">' . $langs->trans('AgfViewActioncomm') . '</a>';
		}

		print '</div>';

		if ($action == 'view_actioncomm') {
			// List of actions on element
			include_once (DOL_DOCUMENT_ROOT . '/core/class/html.formactions.class.php');
			$formactions = new FormAgefodd($db);
			$somethingshown = $formactions->showactions($agf, 'agefodd_agsession', $socid);
		}
	}
}

llxFooter();
$db->close();
