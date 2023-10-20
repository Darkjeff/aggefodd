<?php

/**
 * \file agefodd/session/card.php
 * \ingroup agefodd
 * \brief card of session
 */
$res = @include('../../main.inc.php'); // For root directory
if(! $res) $res = @include('../../../main.inc.php'); // For "custom" directory
if(! $res) die('Include of main fails');

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

// Security check
if(! $user->rights->agefodd->lire) {
    accessforbidden();
}

$hookmanager->initHooks([
    'agefoddsessioncalendarsignature'
]);

$langs->load('bills');

$newToken = function_exists('newToken') ? newToken() : $_SESSION['newtoken'];

$action = GETPOST('action', 'alpha');
$confirm = GETPOST('confirm', 'alpha');
$id = GETPOST('id', 'int');
$massaction = GETPOST('massaction', 'alpha');
$toselect = GETPOST('toselect', 'array');
$personid = GETPOST('personid', 'int');
$person_type = GETPOST('person_type', 'string');

$agf = new Agsession($db);
$extrafields = new ExtraFields($db);
$extralabels = $extrafields->fetch_name_optionals_label($agf->table_element);

$parameters = ['id' => $id];
$reshook = $hookmanager->executeHooks('doActions', $parameters, $agf, $action); // Note that $action and $object may have been modified by some hooks
if($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

/*
 * Actions delete session
 */

$cancel = GETPOST('cancel', 'none');

if($action === 'delete' && $confirm === 'yes') {
	if(!empty($toselect)) {
		$nb_deleted=0;
		foreach ($toselect as $id_creneau) {
			$upload_dir = $conf->agefodd->multidir_output[$conf->entity] . "/" . $id . '/creneau-' . $id_creneau . '/';
			$filename = "signature_" . $person_type . "-" . $personid . ".png";
			$signature = new AgefoddSignature($db);
			$res_array = $signature->fetchAll('', '', 0, 0, ['customsql' => ' fk_calendrier = '.((int)$id_creneau).' AND fk_person = '.((int)$personid).' AND person_type = "'.$db->escape($person_type).'" ']);
			if(!empty($res_array) && !empty($res_array[key($res_array)])) {
				$res_array[key($res_array)]->delete($user);
				dol_delete_file($upload_dir . $filename);
				$nb_deleted++;
			}
		}
		if($nb_deleted > 0) {
			setEventMessage($langs->trans('CreneauxSignatureDeleted', $nb_deleted));
			header('Location: '.$_SERVER['PHP_SELF'].'?id='.$id.'&personid='.$personid.'&person_type='.$person_type);
			exit;
		}
	}
}

/*
 * View
 */

llxHeader('', $langs->trans('AgfSessionDetail'), '', '', '', '', [
    '/agefodd/includes/lib.js'
], []);
$form = new Form($db);
$formAgefodd = new FormAgefodd($db);

/*
 * Action create
 */

// Display session card
if($id) {
    $agf = new Agsession($db);
    $result = $agf->fetch($id);

    if($result > 0) {
        if(! (empty($agf->id))) {
            $head = session_prepare_head($agf);

            dol_fiche_head($head, 'signatures', $langs->trans('AgfSessionDetail'), 0, 'group');

            dol_agefodd_banner_tab($agf, 'id', '', 0);

            dol_fiche_end();

            $arrayofaction = [];
            $arrayofaction['sign'] = $langs->trans('SignCalendrier');
            $arrayofaction['predelete'] = $langs->trans('DeleteSignatureCalendrier');

			if(empty($toselect)) {
				$massactionbutton = $formAgefodd->selectMassAction('', $arrayofaction);
			}

			// obligatoire de mettre l'action ici car le canvas de signature doit être au dessus tu tableau comme chaque massaction
			if(! empty($massaction)) {
				switch($massaction) {
					case 'sign':
						$signature = new AgefoddSignature($db);
						$more_hidden_inputs='<input type="hidden" id="personid" value="'.$personid.'"><input type="hidden" id="person_type" value="'.$person_type.'">';
						$signature->getFormSignatureCreneau($toselect, $agf, $personid, $person_type);
						break;
				}
			}

            if($person_type == 'trainer') {
                $trainer = new Agefodd_teacher($db);
                $trainer->fetch($personid);
                $person_info = $trainer->getNomUrl();
                $person_type_title = $langs->trans('AgfTrainer');
            } else {
                $stagiaire = new Agefodd_stagiaire($db);
                $stagiaire->fetch($personid);
                $person_info = $stagiaire->getNomUrl();
                $person_type_title = $langs->trans('AgfFichePresByTraineeTraineeTitleM');
            }


			$agf->displaySignTimeSlotForm($personid, $person_type, $person_type_title, $person_info);

            $parameters = [];
            $reshook = $hookmanager->executeHooks('formObjectOptions', $parameters, $agf, $action); // Note that $action and $object may have been modified by hook
            print $hookmanager->resPrint;
        }
        else {
            print $langs->trans('AgfNoSession');
        }
    }
    else {
        setEventMessage($agf->error, 'errors');
    }
}
else {
    print $langs->trans('AgfNoSession');
}

$backtopage = dol_buildpath('/agefodd/session/person.php', 1) . '?id=' . ($id > 0 ? $id : '__ID__');
print '<div class="tabsAction">';
		print '<a class="butAction" href="' . $backtopage . '" title="' . $langs->trans('agfReturnPerson') . '">' . $langs->trans('agfReturnPerson') . '</a>';
print '</div>';


llxFooter();
$db->close();
