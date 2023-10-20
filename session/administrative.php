<?php
/*
 * Copyright (C) 2009-2010	Erick Bullier	<eb.dev@ebiconsulting.fr>
 * Copyright (C) 2010-2011	Regis Houssin	<regis@dolibarr.fr>
 * Copyright (C) 2012-2016 Florian Henry <florian.henry@open-concept.pro>
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
 * \file agefodd/session/administrative.php
 * \ingroup agefodd
 * \brief administrative task of session
 */
$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");

require_once (__DIR__ . '/../class/agefodd_sessadm.class.php');
require_once (__DIR__ . '/../class/agefodd_session_admlevel.class.php');
require_once (__DIR__ . '/../class/agsession.class.php');
require_once (__DIR__ . '/../class/agefodd_training_admlevel.class.php');
require_once (__DIR__ . '/../class/agefodd_session_admlevel.class.php');
require_once (__DIR__ . '/../class/html.formagefodd.class.php');
require_once (__DIR__ . '/../lib/agefodd.lib.php');
require_once (DOL_DOCUMENT_ROOT . '/core/lib/date.lib.php');
require_once (DOL_DOCUMENT_ROOT . '/core/lib/functions.lib.php');
require_once __DIR__ .'/../lib/retroCompatibility.lib.php';

// Security check
if (! $user->rights->agefodd->lire)
	accessforbidden();

$action = GETPOST('action', 'alpha');
$confirm = GETPOST('confirm', 'alpha');
$id = GETPOST('id', 'int');
$actid = GETPOST('actid', 'int');

$newToken = function_exists('newToken') ? newToken() : $_SESSION['newtoken'];

/*
 * Actions delete
*/
if ($action == 'confirm_delete' && $confirm == "yes" && $user->rights->agefodd->creer) {
	$agf = new Agefodd_sessadm($db);
	$result = $agf->remove($actid);

	if ($result > 0) {
		Header("Location: " . $_SERVER ['PHP_SELF'] . "?id=" . $id);
		exit();
	} else {
		setEventMessage($agf->error, 'errors');
	}
}

/*
 * Action update
*/
if ($action == 'update' && $user->rights->agefodd->creer) {
	if (! $_POST ["cancel"] && ! $_POST ["delete"]) {
		$agf = new Agefodd_sessadm($db);

		$result = $agf->fetch($actid);

		$agf->datea = dol_mktime(0, 0, 0, GETPOST('dateamonth', 'int'), GETPOST('dateaday', 'int'), GETPOST('dateayear', 'int'));
		$agf->dated = dol_mktime(0, 0, 0, GETPOST('dadmonth', 'int'), GETPOST('dadday', 'int'), GETPOST('dadyear', 'int'));
		$agf->datef = dol_mktime(0, 0, 0, GETPOST('dafmonth', 'int'), GETPOST('dafday', 'int'), GETPOST('dafyear', 'int'));
		$agf->notes = GETPOST('notes', 'alpha');
		$result = $agf->update($user);

		if ($result > 0) {
			Header("Location: " . $_SERVER ['PHP_SELF'] . "?id=" . $id);
			exit();
		} else {
			setEventMessage($agf->error, 'errors');
		}
	} elseif ($_POST ["delete"]) {
		Header('Location:' . $_SERVER ['PHP_SELF'] . '?id=' . $id . '&action=edit&delete=1&actid=' . $actid);
		exit();
	} else {
		Header("Location: " . $_SERVER ['PHP_SELF'] . "?id=" . $id);
		exit();
	}
}

if ($action == 'confirm_replicateconftraining' && $confirm == 'yes') {
	$agf_level = new Agefodd_sessadm($db);
	$result = $agf_level->remove_all($id);
	if ($result < 0) {
		setEventMessage($agf_level->error, 'errors');
	}

	$agf_session = new Agsession($db);
	$res = $agf_session->fetch($id);
	$result = $agf_session->createAdmLevelForSession($user);
	if ($result < 0) {
		setEventMessages(null,$agf_session->errors, 'errors');
	}
}

if($action == 'update_archive' && $user->rights->agefodd->creer) {
    global $langs, $db;

    $agf_training_admlevel = new Agefodd_training_admlevel($db);
    $agf_session_admlevel = new Agefodd_session_admlevel($db);

    $updateReady = false; // par defaut

    $agf = new Agefodd_sessadm($db);
    $result = $agf->fetch($actid);
    if(empty($result)) {
        dol_print_error($db);
    }

    // Check si le fichier est noté comme obligatoire dans l'admin du module ou bien dans l'onglet tâche administrative d'un recueil
    $mandatory_file = $agf_training_admlevel->mandatory_file || $agf_session_admlevel->mandatory_file || $agf->mandatory_file;

    // Le fichier est obligatoire sur cette ligne et la ligne n'est pas validée
    if($mandatory_file && $agf->archive != 1) {

        $TfileTypeAllowed = [

            'txt' => 'text/plain',
            'csv' => 'text/csv',
            'xml' => 'application/xml',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',

            // Adobe
            'pdf' => 'application/pdf',

            // Images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',

            // Open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',

            // Ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint'
        ];

        // Fichier pas encore envoyé
        if(GETPOST('confirmfilesend', 'int') == 0) {
            $action = 'add_archive_file_mandatory';
        } else {
            if(isset($_FILES['mandatoryfile'])) {

                $mandatoryFileName = $_FILES['mandatoryfile']['name'];
                if(!empty($_FILES['mandatoryfile']['name'])) {
                    $mandatoryFileType = mime_content_type($_FILES['mandatoryfile']['tmp_name']);
                }

                if($mandatoryFileType != '') {
                    if(in_array($mandatoryFileType, $TfileTypeAllowed)) {
                        // On le place à la racine de documents/agefodd/idSession si non il apparaît pas dans les fichiers joints
                        $dest_folder = '/agefodd/'.$id;
                        if(dol_mkdir($dest_folder, DOL_DATA_ROOT ) < 0) {
							setEventMessage('ErrorCreateDestinationFolderForX', $mandatoryFileName);
							$action = 'add_archive_file_mandatory';
                        } else {
                            $dest_file = DOL_DATA_ROOT . $dest_folder . '/'. $mandatoryFileName;
                            if(dol_move_uploaded_file($_FILES['mandatoryfile']['tmp_name'], $dest_file, 1) > 0){
                                $updateReady = true;
								$agf->file_name = $mandatoryFileName;
                            } else {
								setEventMessage('ErrorSaveFileX', $mandatoryFileName);
                            }
                        }
                    } else {
                        setEventMessage($langs->trans("ErrorBadFileFormat"), "errors");
                        $action = 'add_archive_file_mandatory';
                    }
                } else {
                    setEventMessage("FileEmpty", "warnings");
                    $action = 'add_archive_file_mandatory';
                }
            } else {
                setEventMessage("FileEmpty", "warnings");
                $action = 'add_archive_file_mandatory';
            }
        }
    } else { // Si le fichier n'est pas obligatoire, on valide direct la ligne
        $updateReady = true;
    }

    if($updateReady) {
        if($agf->archive == 1) {
            $agf->archive = 0;
			$agf->file_name = '';
        }
        else {
            $agf->archive = 1;
        }
        $agf->datef = dol_mktime(0, 0, 0, dol_print_date(dol_now(), '%m'), dol_print_date(dol_now(), '%d'), dol_print_date(dol_now(), '%Y'));

        $result = $agf->update($user);

        if($result > 0) {
            Header("Location: ".$_SERVER ['PHP_SELF']."?id=".$id);
            exit();
        }
        else {
            setEventMessage($agf->error, 'errors');
        }
    }
}

if ($action == 'validall' && $user->rights->agefodd->creer) {
	$agf = new Agefodd_sessadm($db);

	$result = $agf->setAllStatus($user,$id,1);
	if ($result > 0) {
		Header("Location: " . $_SERVER ['PHP_SELF'] . "?id=" . $id);
		exit();
	} else {
		setEventMessage($agf->error, 'errors');
	}
}

/*
 * Action create
*/
if ($action == 'create_confirm' && $user->rights->agefodd->creer) {
	if (! $_POST ["cancel"]) {
		$agf = new Agefodd_sessadm($db);

		$parent_level = GETPOST('action_level', 'int');
		$agf->fk_agefodd_session_admlevel = 0;

		$sql = 'SELECT MAX(fk_agefodd_session_admlevel) as max FROM '.MAIN_DB_PREFIX.'agefodd_session_adminsitu WHERE `fk_agefodd_session` = ' . $id;
		$res = $db->query($sql);
		if($res){
		    $obj = $db->fetch_object($res);
		    if ($obj->max !== NULL) $agf->fk_agefodd_session_admlevel = ((int)$obj->max) + 1;
		}

		$agf->fk_agefodd_session = $id;
		$agf->delais_alerte = 0;
		$agf->archive = 0;
		$agf->intitule = GETPOST('intitule', 'alpha');
		$agf->datea = dol_mktime(0, 0, 0, GETPOST('dateamonth', 'int'), GETPOST('dateaday', 'int'), GETPOST('dateayear', 'int'));
		$agf->dated = dol_mktime(0, 0, 0, GETPOST('dadmonth', 'int'), GETPOST('dadday', 'int'), GETPOST('dadyear', 'int'));
		$agf->datef = dol_mktime(0, 0, 0, GETPOST('dafmonth', 'int'), GETPOST('dafday', 'int'), GETPOST('dafyear', 'int'));
		$agf->notes = GETPOST('notes', 'alpha');

		// Set good indice and level rank
		if (! empty($parent_level)) {
			$agf->fk_parent_level = $parent_level;

			$agf_static = new Agefodd_sessadm($db);
			$result_stat = $agf_static->fetch($parent_level);

			if ($result_stat > 0) {
				if (! empty($agf_static->id)) {
					$agf->level_rank = $agf_static->level_rank + 1;
					$agf->indice = ebi_get_next_indice_action($agf_static->id, $id);
				} else { // no parent : This case may not occur but we never know
					$agf->indice = (ebi_get_level_number($id) + 1) . '00';
					$agf->level_rank = 0;
				}
			} else {
				setEventMessage($agf_static->error, 'errors');
			}
		} else {
			// no parent
			$agf->fk_parent_level = 0;
			$agf->indice = (ebi_get_level_number($id) + 1) . '00';
			$agf->level_rank = 0;
		}

		$result = $agf->create($user);

		if ($result < 0) {
			setEventMessage($agf->error, 'errors');
		} else {
			Header("Location: " . $_SERVER ['PHP_SELF'] . "?id=" . $id);
			exit();
		}
	} else {
		Header("Location: " . $_SERVER ['PHP_SELF'] . "?id=" . $id);
		exit();
	}
}

/*
 * View
*/

llxHeader('', $langs->trans("AgfSessionDetail"));

$form = new Form($db);
$formAgefodd = new FormAgefodd($db);

if ($user->rights->agefodd->lire) {
	// Display administrative task
	if ($id) {
		// View mode
		$agf_session = new Agsession($db);
		$res = $agf_session->fetch($id);

		$head = session_prepare_head($agf_session);

		dol_fiche_head($head, 'administrative', $langs->trans("AgfSessionDetail"), 0, 'bill');

		$agf = new Agefodd_sessadm($db);
        $result = $agf->fetch($actid);

        if ($action == 'add_archive_file_mandatory' && $user->rights->agefodd->creer) {


            print '<div id="form-import-mandatory-file-dialog-container" style="display: none;" title="'.dol_escape_htmltag($langs->trans('UploadMandatoryFile')).'" >';
            print     '<form id="form-import-mandatory-file" action="' . $_SERVER['PHP_SELF']. '" method="post" enctype="multipart/form-data">';
            print '<input type="hidden" name="token" value="'.$newToken.'">';
            print          '<input type="hidden" name="action" value="update_archive"/>';
            print          '<input type="hidden" name="id" value="'. $agf_session->id .'"/>';
            print          '<input type="hidden" name="actid" value="'. $agf->id .'"/>';
            print          '<input type="hidden" name="confirmfilesend" value="1"/>';
            print          '<input type="file" name="mandatoryfile" id="mandatoryfile" />';
            print          '<input type="hidden" name="MAX_FILE_SIZE" value="'.(empty($conf->global->MAIN_UPLOAD_DOC) ? '2048' : intval($conf->global->MAIN_UPLOAD_DOC)*1024).'" /> ';
            print     '</form>';
            print '</div>';
            print '<script>';
            print '$( function() {';
            print     '$( "#form-import-mandatory-file-dialog-container" ).dialog({
              resizable: false,
              height: "auto",
              width: 400,
              modal: true,
              buttons: {
                "Importer": function() {
                  $( this ).dialog( "close" );
                  $( "#form-import-mandatory-file" ).submit();
                },
                Cancel: function() {
                  $( this ).dialog( "close" );
                }
              }
            });';
            print '} );';
            print '</script>';
        }

		if ($action == 'replicateconftraining' ) {
		    require_once DOL_DOCUMENT_ROOT.'/core/class/html.formadmin.class.php';
		    $form = new Form($db);
		    print $form->formconfirm($_SERVER["PHP_SELF"]."?id=".$id, $langs->trans('AgfReplaceByTrainingLevel'), $langs->trans('AgfReplaceByTrainingLevelHelp'), "confirm_replicateconftraining", '', '', 1);
		}

		// Creation card
		if ($action == 'create') {
			print '<form name="create_confirm" action="administrative.php" method="post">' . "\n";
			print '<input type="hidden" name="token" value="' . $newToken . '">' . "\n";
			print '<input type="hidden" name="action" value="create_confirm">' . "\n";
			print '<input type="hidden" name="id" value="' . $id . '">' . "\n";

			print '<table class="border" width="100%">';

			print '<tr><td>' . $langs->trans("AgfSessAdmIntitule") . '</td>';
			print '<td><input name="intitule" class="flat" size="50" value=""/></td></tr>';

			print '<tr><td valign="top">' . $langs->trans("AgfParentLevel") . '</td>';
			print '<td>' . $formAgefodd->select_action_session($id) . '</td></tr>';

			print '<tr><td valign="top">' . $langs->trans("AgfSessAdmDateLimit") . '</td><td>';
			$form->select_date('', 'datea', '', '', '', 'create_confirm');
			print '</td></tr>';

			print '<tr><td valign="top">' . $langs->trans("AgfSessDateDebut") . '</td><td>';
			$form->select_date('', 'dad', '', '', '', 'create_confirm');
			print '</td></tr>';

			print '<tr><td valign="top">' . $langs->trans("AgfSessDateFin") . '</td><td>';
			$form->select_date('', 'daf', '', '', '', 'create_confirm');
			print '</td></tr>';

			print '<tr><td valign="top">' . $langs->trans("AgfNote") . '</td>';
			print '<td><textarea name="notes" rows="3" cols="0" class="flat" style="width:360px;"></textarea></td></tr>';

			print '</table>';
			print '</div>';

			print '<table style=noborder align="right">';
			print '<tr><td align="center" colspan=2>';
			print '<input type="submit" class="butAction" value="' . $langs->trans("Save") . '"> &nbsp; ';
			print '<input type="submit" name="cancel" class="butActionDelete" value="' . $langs->trans("Cancel") . '"> &nbsp; ';
			print '</td></tr>';

			print '</table>';
			print '</form>';
		} 		// Display edit mode
		elseif ($action == 'edit') {
			$result = $agf->fetch($actid);

			/*
			 * Delete confirm
			*/
			if (GETPOST('delete', 'int') == '1') {
				print $form->formconfirm("administrative.php?id=" . $id . "&actid=" . $actid, $langs->trans("AgfDeleteOps"), $langs->trans("AgfConfirmDeleteAction"), "confirm_delete", '', '', 1);
			}
			print '<form name="update" action="administrative.php" method="post">' . "\n";
			print '<input type="hidden" name="token" value="' . $newToken . '">' . "\n";
			print '<input type="hidden" name="action" value="update">' . "\n";
			print '<input type="hidden" name="id" value="' . $id . '">' . "\n";
			print '<input type="hidden" name="actid" value="' . $agf->id . '">' . "\n";

			print '<table class="border" width="100%">';

			print "<tr>";
			print '<td td width="300px">' . $langs->trans("Ref") . '</td><td>' . $agf->id . '</td></tr>';

			print '<tr><td>' . $langs->trans("AgfSessAdmIntitule") . '</td>';
			print '<td>' . $agf->intitule . '</td></tr>';

			print '<tr><td valign="top">' . $langs->trans("AgfSessAdmDateLimit") . '</td><td>';
			$form->select_date($agf->datea, 'datea', '', '', '', 'update');
			print '</td></tr>';

			print '<tr><td valign="top">' . $langs->trans("AgfSessDateDebut") . '</td><td>';
			$form->select_date($agf->dated, 'dad', '', '', '', 'update');
			print '</td></tr>';
			print '<tr><td valign="top">' . $langs->trans("AgfSessDateFin") . '</td><td>';
			$form->select_date($agf->datef, 'daf', '', '', '', 'update');
			print '</td></tr>';

			print '<tr><td valign="top">' . $langs->trans("AgfNote") . '</td>';
			print '<td><textarea name="notes" rows="3" cols="0" class="flat" style="width:360px;">' . $agf->notes . '</textarea></td></tr>';

			print '</table>';
			print '</div>';

			print '<table style=noborder align="right">';
			print '<tr><td align="center" colspan=2>';
			print '<input type="submit" class="butAction" value="' . $langs->trans("Save") . '"> &nbsp; ';
			print '<input type="submit" name="cancel" class="butActionDelete" value="' . $langs->trans("Cancel") . '"> &nbsp; ';
			print '<input type="submit" name="delete" class="butActionDelete" value="' . $langs->trans("Delete") . '">';
			print '</td></tr>';

			print '</table>';
			print '</form>';
		} else {
			// Display view mode
			$sess_adm = new Agefodd_sessadm($db);
			$result = $sess_adm->fetch_all($id);

			dol_agefodd_banner_tab($agf_session, 'id');
			print '<div class="underbanner clearboth"></div>';

			print '<table width="100%" class="border">';

			if ($result > 0) {

				$i = 0;
				$mandatoryFilePending = 0;
				foreach ( $sess_adm->lines as $line ) {

					if ($line->level_rank == '0' && $i != 0) {
						print '<tr  style="border-style:none"><td colspan="6" style="border-style:none">&nbsp;</td></tr>';
					}

					if ($line->level_rank == '0') {

						print '<tr align="center" style="border-style:none">';

                        print '<td colspan="2" style="border-style:none">&nbsp;</td>';
                        print '<td width="150px" style="border-style:none">' . $langs->trans("AttachedFile") . '</td>';
                        print '<td width="150px" style="border-style:none">' . $langs->trans("Commentaire") . '</td>';
                        print '<td width="150px" style="border-style:none">' . $langs->trans("User") . '</td>';
						print '<td width="150px" style="border-style:none">' . $langs->trans("AgfLimitDate") . '</td>';
						print '<td width="150px" style="border-style:none">' . $langs->trans("AgfDateDebut") . '</td>';
						print '<td width="150px" style="border-style:none">' . $langs->trans("AgfDateFin") . '</td>';
						print '<td style="border-style:none"></td>';
						print '</tr>';
					}
					print '<tr style="color:#000000;border:1px;border-style:solid">';

					$bgcolor = '#d5baa8'; // Default color

					// 8 day before alert date
					if (dol_now() > dol_time_plus_duree($line->datea, - 8, 'd'))
						$bgcolor = '#ffe27d';

						// 3 day before alert day
					if (dol_now() > dol_time_plus_duree($line->datea, - 3, 'd'))
						$bgcolor = 'orange';

						// if alert date is past then RED
					if (dol_now() > $line->datea && empty($line->delais_alerte_end))
						$bgcolor = 'red';

						// if end date is in the past and task is mark as done , the task is done
					if (! empty($line->archive))
						$bgcolor = 'green';

					if ((dol_now() > dol_time_plus_duree($line->datef, $line->delais_alerte_end, 'd')) && (empty($line->archive)))
						$bgcolor = 'red';

					if ($sess_adm->has_child($line->id)) {
						$bgcolor = '';
					}
					print '<td width="10px" bgcolor="' . $bgcolor . '">&nbsp;</td>';

					print '<td style="border-right-style: none;"><a href="' . dol_buildpath('/agefodd/session/administrative.php', 1) . '?action=edit&id=' . $id . '&actid=' . $line->id . '">';
					print str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $line->level_rank) . $line->intitule . '</a></td>';


                    if (! empty($line->mandatory_file) ) {
                        print '<td class="adminaction center" style="border-left-style: none; width: auto;">';
						$dest_folder = '/agefodd/'.$id;
						$relative_file_path = $id.'/'.$line->file_name;
                        $dest_file = DOL_DATA_ROOT . $dest_folder . '/'. $line->file_name;
						if(!empty($line->file_name) && file_exists($dest_file)){
							$urlFile = DOL_MAIN_URL_ROOT .'/document.php'.'?modulepart=agefodd&entity='.$sess_adm->entity.'&file='.urlencode($relative_file_path);
							print '<a target="_blank" href="' .$urlFile . '">';
							print $line->file_name;
							print  '</a>';

							$tmparray = getAdvancedPreviewUrl('agefodd', $relative_file_path, 1, 'entity='.$sess_adm->entity);
							if ($tmparray && $tmparray['url'])
							{
								$linkPreview = '<a href="'.$tmparray['url'].'"'.($tmparray['css'] ? ' class="'.$tmparray['css'].'"' : '').($tmparray['mime'] ? ' mime="'.$tmparray['mime'].'"' : '').($tmparray['target'] ? ' target="'.$tmparray['target'].'"' : '').'>';
								$linkPreview .= '<i class="fa fa-search-plus paddingright" style="color: gray"></i>';
								$linkPreview .= '</a>';
								print '&nbsp;'.$linkPreview;
							}
						}
						elseif(!empty($line->file_name)){
							print '<span class="classfortooltip" title="'.dol_escape_htmltag($langs->trans('FileXDeleted', $line->file_name)). '" style="text-decoration: line-through; font-weight:bold; color: #dc5d00;">'
								. '<span class="fa fa-warning"></span>&nbsp;'
								.$line->file_name
								.'</span>';
						}





                    } else {
						print '<td style="border-left: 0px; width:auto;">&nbsp;</td>';
					}

					// Affichage éventuelle des notes
					if (! empty($line->notes)) {
						print '<td class="adminaction" style="border-left-style: none; width: auto; text-align: right" valign="top">';
						print '<img src="' . DOL_URL_ROOT . '/theme/' . $conf->theme . '/img/recent.png" border="0" align="absmiddle" hspace="6px" >';
						print '<span>' . wordwrap(stripslashes($line->notes), 50, "<br />", 1) . '</span></td>';
					} else
						print '<td style="border-left: 0px; width:auto;">&nbsp;</td>';

				    // Utilisateur qui a validé l'action administrative
					if ($line->archive) {
					    $u = new User($db);
					    $u->fetch($line->fk_user_mod);
					    print '<td width="150px" align="center" valign="top">'.$u->getNomUrl(1).'</td>';
					} else {
					    print '<td></td>';
					}

					if (! $sess_adm->has_child($line->id)) {
						// Affichage des différentes dates
						print '<td width="150px" align="center" valign="top">';
						if ($bgcolor == 'red')
							print '<font style="color:' . $bgcolor . '">';
						print dol_print_date($line->datea, 'daytext');
						if ($bgcolor == 'red')
							print '</font>';
						print '</td>';
						print '<td width="150px" align="center" valign="top">' . dol_print_date($line->dated, 'daytext') . '</td>';
						print '<td width="150px" align="center" valign="top">' . dol_print_date($line->datef, 'daytext') . '</td>';

						// Status Line
						if ($line->archive) {
							$txtalt = $langs->trans("AgfTerminatedNoPoint");
							$src_state = dol_buildpath('/agefodd/img/ok.png', 1);
                            $htmlState = '<img alt="' . $txtalt . '" src="' . $src_state . '"/>';
						} else {

                            $txtalt = $langs->trans("AgfTerminatedPoint");
                            $src_state = dol_buildpath('/agefodd/img/next.png', 1);
                            $htmlState = '<img style="vertical-align: middle;" alt="' . $txtalt . '" src="' . $src_state . '"/>';

                            if($line->mandatory_file){
								$mandatoryFilePending++;
                                $htmlState = '<i class="icon-mandatory-file  fa fa-file-upload"></i>&nbsp;'.$htmlState; // todo tooltip
                            }
						}

						print '<td align="center" valign="top">';
						if ($user->rights->agefodd->modifier) {
							print '<a href="' . $_SERVER ['PHP_SELF'] . '?action=update_archive&id=' . $id . '&token=' . $newToken . '&actid=' . $line->id . '">'
                                .$htmlState
                                .'</a>';
						}
						print '</td>';
					} else {
						if (!$line->archive && $line->mandatory_file){
							$mandatoryFilePending++;
						}

						print '<td colspan="4"></td>';
					}
					print '</tr>';

					$i ++;
				}
			} elseif (empty($result)){
			    print '<tr><td style="text-align:center">'.$langs->trans('AgfErrNoAdminTasksFound').'</td></tr>';
			} else {
			    print '<tr><td style="text-align:center">'.$langs->trans('AgfErrFetchAdminTasks').'</td></tr>';
			    setEventMessage($sess_adm->error,'errors');
			}

			print '</table>';
			print '&nbsp;';

			print '<table align="center" noborder><tr>';
			print '<td width="10px" bgcolor="green"><td>' . $langs->trans("AgfTerminatedPoint") . '&nbsp</td>';
			print '<td width="10px" bgcolor="#ffe27d"><td>' . $langs->trans("AgfXDaysBeforeAlert") . '&nbsp;</td>';
			print '<td width="10px" bgcolor="orange"><td>' . $langs->trans("AgfYDaysBeforeAlert") . '&nbsp</td>';
			print '<td width="10px" bgcolor="red"><td>' . $langs->trans("AgfAlertDay") . '&nbsp</td>';
			print '</tr></table>';

			print '</div>';
		}
	}
}

/*
 * Action tabs
*
*/

print '<div class="tabsAction">';

if ($action != 'create' && $action != 'edit' && $action != 'update') {
	if ($user->rights->agefodd->creer) {

		$validAllRight = true;
		$btnParams = array();
		if(!empty($mandatoryFilePending)){
			$validAllRight = false;
			$btnParams = array('attr' =>array('class'=>'classfortooltip', 'title' => $langs->trans('CantValidateMandatoryFilePending', $mandatoryFilePending)));
		}
		print dolGetButtonAction($langs->trans('AgfAllValide'), '', 'default', $_SERVER ['PHP_SELF'] . '?action=validall&id=' . $id , 'validate-all-admin-task', $validAllRight, $btnParams);

        if(!empty($conf->global->AGF_CREATE_ADMINISTRATIVE_TASK_FROM_ADMINISTRATIVE_TASKS_PAGE)) {
            print '<a class="butAction" href="' . $_SERVER ['PHP_SELF'] . '?action=create&id=' . $id . '">' . $langs->trans('Create') . '</a>';
        }
		print '<a class="butAction" href="' . $_SERVER ['PHP_SELF'] . '?action=replicateconftraining&id=' . $id . '" title="' . $langs->trans('AgfReplaceByTrainingLevelHelp') . '">' . $langs->trans('AgfReplaceByTrainingLevel') . '</a>';
	} else {
		print '<a class="butActionRefused" href="#" title="' . dol_escape_htmltag($langs->trans("NotAllowed")) . '">' . $langs->trans('Modify') . '</a>';
	}
}

print '</div>';

llxFooter();
$db->close();
