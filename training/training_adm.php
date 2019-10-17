<?php
/*
 * Copyright (C) 2013-2016 Florian Henry <florian.henry@open-concept.pro>
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
 * \file /agefodd/training/training_adm.php
 * \ingroup agefodd
 * \brief agefood agefodd admin training task by trainig
 */
$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory

require_once ('../class/agefodd_session_admlevel.class.php');
require_once ('../class/agefodd_training_admlevel.class.php');
require_once ('../class/agefodd_formation_catalogue.class.php');
require_once ('../class/html.formagefodd.class.php');
require_once ('../lib/agefodd.lib.php');
require_once (DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php");
require_once __DIR__ .'/../lib/retroCompatibility.lib.php';
$langs->load("admin");
$langs->load('agefodd@agefodd');

// Security check
if (! $user->rights->agefodd->agefodd_formation_catalogue->lire)
	accessforbidden();

$action = GETPOST('action', 'alpha');
$confirm = GETPOST('confirm', 'alpha');
$id = GETPOST('id', 'int');
$trainingid = GETPOST('trainingid', 'int');
$parent_level = GETPOST('parent_level', 'int');

if (empty($trainingid)) {
	$trainingid = $id;
}

if ($action == 'sessionlevel_create') {
	$agf = new Agefodd_training_admlevel($db);

	if (! empty($parent_level)) {
		$agf->fk_parent_level = $parent_level;

		$agf_static = new Agefodd_training_admlevel($db);
		$result_stat = $agf_static->fetch($agf->fk_parent_level);

		if ($result_stat > 0) {
			if (! empty($agf_static->id)) {
				$agf->level_rank = $agf_static->level_rank + 1;
				$agf->indice = ebi_get_adm_training_get_next_indice_action($agf_static->id);
			} else { // no parent : This case may not occur but we never know
				$agf->indice = (ebi_get_adm_training_level_number() + 1) . '00';
				$agf->level_rank = 0;
			}
		} else {
			setEventMessage($agf_static->error, 'errors');
		}
	} else {
		// no parent
		$agf->fk_parent_level = 0;
		$agf->indice = (ebi_get_adm_training_level_number() + 1) . '00';
		$agf->level_rank = 0;
	}

	$agf->fk_training = $trainingid;
	$agf->intitule = GETPOST('intitule', 'alpha');
	$agf->delais_alerte = GETPOST('delai', 'int');
	$agf->delais_alerte_end = GETPOST('delai_end', 'int');

	if ($agf->level_rank > 3) {
		setEventMessage($langs->trans("AgfAdminNoMoreThan3Level"), 'errors');
	} else {
		$result = $agf->create($user);

		if ($result1 != 1) {
			setEventMessage($agf->error, 'errors');
		}
	}
}

if ($action == 'replicateconfadmin') {
	$agf_adminlevel = new Agefodd_training_admlevel($db);
	$agf_adminlevel->fk_training = $id;
	$result = $agf_adminlevel->delete_training_task($user);
	if ($result < 0) {
		setEventMessage($agf_adminlevel->error, 'errors');
	}

	$agf = new Formation($db);
	$result = $agf->fetch($trainingid);
	$result = $agf->createAdmLevelForTraining($user);
	if ($result < 0) {
		setEventMessage($agf->error, 'errors');
	}
}
$updatedRowId = 0;
if ($action == 'sessionlevel_update') {
	$agf = new Agefodd_training_admlevel($db);

	$result = $agf->fetch($id);

	if ($result > 0) {
		// Up level of action
		if (GETPOST('sesslevel_up_x') || GETPOST('sesslevel_up')) {
			$result2 = $agf->shift_indice($user, 'less');
			$updatedRowId = $id;
			if ($result1 != 1){
				setEventMessage($agf->error, 'errors');
			}
		}

		// Down level of action
		if (GETPOST('sesslevel_down_x') || GETPOST('sesslevel_down')) {
			$result1 = $agf->shift_indice($user, 'more');
			$updatedRowId = $id;
			if ($result1 != 1) {
				setEventMessage($agf->error, 'errors');
			}
		}

		// Update action
		if (GETPOST('sesslevel_update_x')) {
			$agf->intitule = GETPOST('intitule', 'alpha');
			$agf->delais_alerte = GETPOST('delai', 'int');
			$agf->delais_alerte_end = GETPOST('delai_end', 'int');
			$updatedRowId = $id;
			if (! empty($parent_level)) {
				if ($parent_level != $agf->fk_parent_level) {
					$agf->fk_parent_level = $parent_level;

					$agf_static = new Agefodd_training_admlevel($db);
					$result_stat = $agf_static->fetch($agf->fk_parent_level);

					if ($result_stat > 0) {
						if (! empty($agf_static->id)) {
							$agf->level_rank = $agf_static->level_rank + 1;
							$agf->indice = ebi_get_adm_training_get_next_indice_action($agf_static->id);
						} else { // no parent : This case may not occur but we never know
							$agf->indice = (ebi_get_adm_training_level_number() + 1) . '00';
							$agf->level_rank = 0;
						}
					} else {
						setEventMessage($agf_static->error, 'errors');
					}
				}
			} else {
				// no parent
				$agf->fk_parent_level = 0;
				$agf->level_rank = 0;
			}

			if ($agf->level_rank > 3) {
				setEventMessage($langs->trans("AgfAdminNoMoreThan3Level"), 'errors');
			} else {
				$result1 = $agf->update($user);
				if ($result1 != 1) {
					setEventMessage($agf_static->error, 'errors');
				}
			}
		}

		// Delete action
		if (GETPOST('sesslevel_remove_x')) {

			$result = $agf->delete($user);
			if ($result != 1) {
				setEventMessage($agf_static->error, 'errors');
			}
		}
	} else {
		setEventMessage('This action do not exists', 'errors');
	}
}

/*
 * View
*/
$title = $langs->trans("AgfCatalogAdminTask");
llxHeader('', $title);

$form = new Form($db);
$formAgefodd = new FormAgefodd($db);

$agf = new Formation($db);
$result = $agf->fetch($trainingid);

$head = training_prepare_head($agf);

dol_fiche_head($head, 'trainingadmtask', $langs->trans("AgfCatalogDetail"), 1, 'label');

dol_agefodd_banner_tab($agf, 'id');
dol_fiche_end(1);
print '<div class="underbanner clearboth"></div>';

$admlevel = new Agefodd_training_admlevel($db);
$result0 = $admlevel->fetch_all($trainingid);

$url = $_SERVER ['PHP_SELF'].'?token=' . $_SESSION ['newtoken'].'&trainingid=' . $trainingid;
$morehtmlright = '';

if ($result0 > 0) {
	// ne sert à rien si aucun resultats
	if ($action != 'sort' && function_exists('dolGetButtonTitle')) {
		$morehtmlright .= dolGetButtonTitle($langs->trans('AgfSortMode'), '', 'fa fa-sort', $url . '&action=sort');
	}

	if ($action === 'sort' && function_exists('dolGetButtonTitle')) {
		$morehtmlright .= dolGetButtonTitle($langs->trans('AgfViewMode'), '', 'fa fa-sort', $url . '&action=view');
	}
}

print load_fiche_titre($langs->trans("AgfAdminTrainingLevel"), $morehtmlright);



if($action==='sort'){
	$TNested = $admlevel->fetch_all_children_nested($trainingid, 0);

	print '<div id="ajaxResults" ></div>';
	print _displaySortableNestedItems($TNested, 'sortableLists');
	print ajax_dialog('test','$message',$w=350,$h=150);
	print '<script src="'.dol_buildpath('agefodd/js/jquery-sortable-lists.min.js',1).'" ></script>';
	print '<link rel="stylesheet" href="'.dol_buildpath('agefodd/css/sortable.css',1).'" >';
	print '	
	<script type="text/javascript">
	$(function()
	{
		var options = {
		    insertZone: 5, // This property defines the distance from the left, which determines if item will be inserted outside(before/after) or inside of another item.
		
		    placeholderClass: \'agf-sortable-list__item--placeholder\',
	        // or like a jQuery css object
			//placeholderCss: {\'background-color\': \'#ff8\'},
			hintClass: \'agf-sortable-list__item--hint\',
	        // or like a jQuery css object
			//hintCss: {\'background-color\':\'#bbf\'},
			onChange: function( cEl )
			{
			    
                $("#ajaxResults").html("");
                
				$.ajax({
                    url: "'.dol_buildpath('agefodd/scripts/interface.php?action=setAgefoddTrainingAdmlevelHierarchy',1).'",
                    method: "POST",
                    data: {
                        \'items\' : $(\'#sortableLists\').sortableListsToHierarchy()
                    },
                    dataType: "json",
                    
                    // La fonction à apeller si la requête aboutie
                    success: function (data) {
                        // Loading data
                        console.log(data);
                        if(data.result > 0 ){
                           // ok case
                           $("#ajaxResults").html(\'<span class="badge badge-success">\' + data.msg + \'</span>\');
                        }
                        else if(data.result < 0 ){
                           // error case
                           $("#ajaxResults").html(\'<span class="badge badge-danger">\' + data.errorMsg + \'</span>\');
                        }
                        else{
                           // nothing to do ? 
                        }
                    },
                    // La fonction à appeler si la requête n\'a pas abouti
                    error: function( jqXHR, textStatus ) {
                        alert( "Request failed: " + textStatus );
                    }
                });
			},
			complete: function( cEl )
			{
                 
               
                
			},
			isAllowed: function( cEl, hint, target )
			{
			    
			    return true;
			
				// Be carefull if you test some ul/ol elements here.
				// Sometimes ul/ols are dynamically generated and so they have not some attributes as natural ul/ols.
				// Be careful also if the hint is not visible. It has only display none so it is at the previouse place where it was before(excluding first moves before showing).
//				if( target.data(\'module\') === \'c\' && cEl.data(\'module\') !== \'c\' )
//				{
//					hint.css(\'background-color\', \'#ff9999\');
//					return false;
//				}
//				else
//				{
//					hint.css(\'background-color\', \'#99ff99\');
//					return true;
//				}
			},
			opener: {
				active: true,
				as: \'html\',  // if as is not set plugin uses background image
				close: \'<i class="fa fa-minus c3"></i>\',  // or \'fa-minus c3\',  // or \'./imgs/Remove2.png\',
				open: \'<i class="fa fa-plus"></i>\',  // or \'fa-plus\',  // or\'./imgs/Add2.png\',
				openerCss: {
					\'display\': \'inline-block\',
					//\'width\': \'18px\', \'height\': \'18px\',
					\'float\': \'left\',
					\'margin-left\': \'-35px\',
					\'margin-right\': \'5px\',
					//\'background-position\': \'center center\', \'background-repeat\': \'no-repeat\',
					\'font-size\': \'1.1em\'
				}
			},
			ignoreClass: \'clickable\',
			
            insertZonePlus: true,
		};
		
	
		$(\'#sortableLists\').sortableLists( options );

		function popTrainingAdmFormDialog(id)
		{
		    var dialogBox = jQuery("#dialog-info");
		    
		    var width = window.width();
		    var height = window.height();
		    
		    if(width > 700){
		        width = 700;
		    }
		    else{
		        
		    }
		    
		    if(height > 600){
		        height = 600;
		    }
		    
		    dialogBox.dialog({
	        resizable: true,
	        height: ,
	        width:'.$w.',
	        modal: true,
	        buttons: {
					Ok: function() {
						jQuery(this).dialog(\'close\');
					}
				}
	    	});
		}
	
	});


	</script>';


	$newtitle=dol_textishtml($title)?dol_string_nohtmltag($title,1):$title;
	$msg= '<div id="dialog-info" title="'.dol_escape_htmltag($newtitle).'">';
	$msg.= $message;
	$msg.= '</div>'."\n";
	$msg.= '<script type="text/javascript">
    jQuery(function() {
        jQuery("#dialog-info").dialog({
	        resizable: false,
	        height:'.$h.',
	        width:'.$w.',
	        modal: true,
	        buttons: {
	        	Ok: function() {
					jQuery(this).dialog(\'close\');
				}
	        }
	    });
	});
	</script>';
}
else{

    print '<style type="text/css">
        tr.updated-row,tr.updated-row td{
            background: #d1e9f1 !important;
        }
    </style>';

    $TNested = $admlevel->fetch_all_children_nested($trainingid, 0);

	print '<table class="noborder noshadow" width="100%">';

	if (!empty($TNested)) {


		print '<thead>';
		print '<tr class="liste_titre nodrag nodrop">';
		print '<th colspan="2">' . $langs->trans("AgfIntitule") . '</th>';

		print '<th>' . $langs->trans("AgfDelaiSessionLevel") . '</th>';
		print '<th>' . $langs->trans("AgfDelaiSessionLevelEnd") . '</th>';
		print '<th></th>';
		print "</tr>\n";
		print '</thead>';

		print '<tbody>';

        print _displayEditableNestedItems($TNested);
		
		print '</tbody>';
	}

	print '<tfoot>';

	print '<tr class="liste_titre nodrag nodrop">';
	print '<th>' . $langs->trans("AgfIntitule") . '</th>';
	print '<th>' . $langs->trans("AgfParentLevel") . '</th>';
	print '<th>' . $langs->trans("AgfDelaiSessionLevel") . '</th>';
	print '<th>' . $langs->trans("AgfDelaiSessionLevelEnd") . '</th>';
	print '<th></th>';
	print "</tr>\n";

	print '<tr class="oddeven nodrag nodrop">';
	print '<form name="SessionLevel_create" action="' . $_SERVER ['PHP_SELF'] . '" method="POST">' . "\n";
	print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">' . "\n";
	print '<input type="hidden" name="action" value="sessionlevel_create">' . "\n";
	print '<input type="hidden" name="trainingid" value="' . $trainingid . '">' . "\n";
	print '<td>' . $langs->trans("Add") . ' <input type="text" name="intitule" value="" size="30" placeholder="' . $langs->trans("AgfIntitule") . '"/></td>';
	print '<td>' . $formAgefodd->select_action_training_adm('', 'parent_level', 0, $trainingid) . '</td>';
	print '<td><input type="number" step="1" name="delai" value=""/></td>';
	print '<td><input type="number" step="1" name="delai_end" value=""/></td>';
	print '<td><input type="image" src="' . DOL_URL_ROOT . '/theme/' . $conf->theme . '/img/edit_add.png" border="0" name="sesslevel_update" alt="' . $langs->trans("Save") . '"></td>';
	print '</form>';
	print '</tr>';
	print '</tfoot>';
	print '</table><br>';

	print '<div class="tabsAction">';
	print '<a class="butAction" href="' . $_SERVER ['PHP_SELF'] . '?action=replicateconfadmin&id=' . $trainingid . '" title="' . $langs->trans('AgfReplaceByAdminLevelHelp') . '">' . $langs->trans('AgfReplaceByAdminLevel') . '</a>';
	print '</div>';

}

llxFooter();
$db->close();

function _displaySortableNestedItems($TNested, $htmlId=''){
	global $langs;
	if(!empty($TNested) && is_array($TNested)){
		$out = '<ul id="'.$htmlId.'" class="agf-sortable-list" >';
		foreach ($TNested as $k => $v){
			$object = $v['object'];
			/**
			 * @var $object Agefodd_training_admlevel
			 */

			if(empty($object->id)) $object->id = $object->rowid;

			$out.= '<li id="item_'.$object->id.'" class="agf-sortable-list__item" data-id="'.$object->id.'" >';
			$out.= '<div class="agf-sortable-list__item__title  move">';
				$out.= '<div class="agf-sortable-list__item__title__flex">';

				$out.= '<div class="agf-sortable-list__item__title__col">';
				$out.= dol_htmlentities($object->intitule);
				$out.= '</div>';

				$out.= '<div class="agf-sortable-list__item__title__col -day-alert">';
					$out.= '<span class="classfortooltip" title="'.$langs->trans("AgfDelaiSessionLevel").'" >';
					$out.= '<i class="fa fa-hourglass-start"></i> ' . $object->alerte .' '.$langs->trans('days');
					$out.= '</span>';
				$out.= '</div>';

				$out.= '<div class="agf-sortable-list__item__title__col -day-alert">';
					$out.= '<span class="classfortooltip"  title="'.$langs->trans("AgfDelaiSessionLevelEnd").'">';
					$out.= '<i class="fa fa-hourglass-end"></i> ' . $object->alerte_end .' '.$langs->trans('days');
					$out.= '</span>';
				$out.= '</div>';

				$out.= '<div class="agf-sortable-list__item__title__col -action clickable">';

					$out.= '<a href="" class="classfortooltip agf-sortable-list__item__title__button clickable"  title="' . $langs->trans("Edit") . '">';
					$out.= '<i class="fa fa-pencil clickable"></i>';
					$out.= '</a>';

					$out.= '<a href="" class="classfortooltip agf-sortable-list__item__title__button clickable"  title="' . $langs->trans("Delete") . '">';
					$out.= '<i class="fa fa-trash clickable"></i>';
					$out.= '</a>';
				$out.= '</div>';

				$out.= '</div>';
            $out.= '</div>';
			$out.= _displaySortableNestedItems($v['children']);
			$out.= '</li>';
		}
		$out.= '</ul>';
		return $out;
	}
	else{
		return '';
	}
}

function _displayEditableNestedItems($TNested){
    global $updatedRowId, $formAgefodd, $conf, $langs, $trainingid;

    $out = '';

    if(!empty($TNested) && is_array($TNested)){
        
        foreach ($TNested as $k => $v){
            $line = $v['object'];
            /**
             * @var $object Agefodd_training_admlevel
             */

            if(empty($line->id)) $line->id = $line->rowid;

            /**
             * @var $line Agefodd_training_admlevel
             */

            $rowClass = '';
            if ($updatedRowId == $line->rowid) {
                $rowClass = 'updated-row';
            }

            $out.= '<tr id="row-' . $line->rowid . '" class="oddeven ' . $rowClass . '" data-rowid="' . $line->rowid . '" >';
            $out.= '<form name="SessionLevel_update_' . $line->rowid . '" action="' . $_SERVER ['PHP_SELF'] . '#row-' . $line->rowid . '" method="POST">' . "\n";
            $out.= '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">' . "\n";
            $out.= '<input type="hidden" name="id" value="' . $line->rowid . '">' . "\n";
            $out.= '<input type="hidden" name="action" value="sessionlevel_update">' . "\n";
            $out.= '<input type="hidden" name="trainingid" value="' . $trainingid . '">' . "\n";


            $out.= '<td colspan="2">';

            $out.= str_repeat('&nbsp;&nbsp;&nbsp;', $line->level_rank);
            if (!empty($line->level_rank)) {
                $out.= '&#8627;';
            }
            $out.= '<input type="text" name="intitule" value="' . $line->intitule . '" size="30"/></td>';
            //$out.= '<td>' . $formAgefodd->select_action_training_adm($line->fk_parent_level, 'parent_level', $line->rowid, $trainingid) . '</td>';
            $out.= '<td><input type="number" step="1" name="delai" value="' . $line->alerte . '"/></td>';
            $out.= '<td><input type="number" step="1" name="delai_end" value="' . $line->alerte_end . '"/></td>';
            $out.= '<td class="right"><input type="image" src="' . DOL_URL_ROOT . '/theme/' . $conf->theme . '/img/edit.png" border="0" name="sesslevel_update" alt="' . $langs->trans("Save") . '">';
            $out.= '<input type="image" src="' . DOL_URL_ROOT . '/theme/' . $conf->theme . '/img/delete.png" border="0" name="sesslevel_remove" alt="' . $langs->trans("Delete") . '"></td>';
            $out.= '</form>';
            $out.= '</tr>';

            $out.= _displayEditableNestedItems($v['children']);
            
        }
        
        return $out;
    }
    else{
        return '';
    }
}
