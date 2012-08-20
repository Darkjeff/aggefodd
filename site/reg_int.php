<?php
/* Copyright (C) 2009-2010	Erick Bullier	<eb.dev@ebiconsulting.fr>
 * Copyright (C) 2010-2011	Regis Houssin	<regis@dolibarr.fr>
 * Copyright (C) 2012       Florian Henry   <florian.henry@open-concept.pro>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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
 *  \file       	/agefodd/site/reg_int.php $
 *  \brief      	Page fiche site de formation
 *  \version		$Id$
 */
error_reporting(E_ALL);
ini_set('display_errors', true);
ini_set('html_errors', false);

$res=@include("../../main.inc.php");				// For root directory
if (! $res) $res=@include("../../../main.inc.php");	// For "custom" directory

dol_include_once('/agefodd/site/class/agefodd_place.class.php');
dol_include_once('/agefodd/site/class/agefodd_reginterieur.class.php');
dol_include_once('/agefodd/lib/agefodd.lib.php');

// Security check
if (!$user->rights->agefodd->lire) accessforbidden();

$mesg = '';

$action=GETPOST('action','alpha');
$confirm=GETPOST('confirm','alpha');
$id=GETPOST('id','int');
$idreg=GETPOST('idreg','int');

/*
 * Actions delete
 */
if ($action == 'confirm_delete' && $confirm == "yes" && $user->rights->agefodd->creer)
{
	$agf = new Agefodd_reg_interieur($db);
	$agf->id=$idreg;
	$result = $agf->delete($user);
	
	if ($result > 0)
		{
			
			$agf_place=new Agefodd_place($db);
			$result_place = $agf_place->fetch($id);
			$agf_place->fk_reg_interieur='';
			$result = $agf_place->update($user->id);
			
			if ($result > 0) {
				Header ( "Location: ".$_SERVER['PHP_SELF']."?action=edit&id=".$result);
				exit;
			}
			else
			{
				dol_syslog("agefodd::site::reg_int error=".$agf_place->error, LOG_ERR);
				$mesg='<div class="error">'.$agf_place->error.'</div>';
			}
	}
	else
	{
		dol_syslog("agefodd::site::reg_int error=".$agf->error, LOG_ERR);
		$mesg='<div class="error">'.$langs->trans("AgfDeleteErr").':'.$agf->error.'</div>';
	}
}

/*
 * Action update (fiche site de formation)
 */
if ($action == 'update' && $user->rights->agefodd->creer)
{
	if (! $_POST["cancel"])
	{
		$agf = new Agefodd_reg_interieur($db);

		$result = $agf->fetch($idreg);
		if ($result > 0)
		{
			$agf->reg_int = GETPOST('reg_int');
			$agf->notes = GETPOST('notes');
			$result = $agf->update($user);
	
			if ($result > 0)
			{
				Header ( "Location: ".$_SERVER['PHP_SELF']."?id=".$id);
				exit;
			}
			else
			{
				dol_syslog("agefodd::site::reg_int error=".$agf->error, LOG_ERR);
				$mesg='<div class="error">'.$agf->error.'</div>';
			}
		}
		else
		{
			dol_syslog("agefodd::site::reg_int error=".$agf->error, LOG_ERR);
			$mesg='<div class="error">'.$agf->error.'</div>';
		}
	}
	else {
		Header ( "Location: ".$_SERVER['PHP_SELF']."?id=".$id);
		exit;
	}
}


/*
 * Action create (fiche site de formation)
 */

if ($action == 'create_confirm' && $user->rights->agefodd->creer)
{
	if (! $_POST["cancel"])
	{
		$agf = new Agefodd_reg_interieur($db);

		$agf->reg_int = GETPOST('reg_int');
		$agf->notes = GETPOST('notes');
		$result = $agf->create($user);
		

		if ($result > 0)
		{
			
			$agf_place=new Agefodd_place($db);
			$result_place = $agf_place->fetch($id);
			$agf_place->fk_reg_interieur=$result;
			$result = $agf_place->update($user->id);
			
			if ($result > 0) {
				Header ( "Location: ".$_SERVER['PHP_SELF']."?id=".$result);
				exit;
			}
			else
			{
				dol_syslog("agefodd::site::reg_int error=".$agf_place->error, LOG_ERR);
				$mesg='<div class="error">'.$agf_place->error.'</div>';
			}
			
		}
		else
		{
			dol_syslog("agefodd::site::reg_int error=".$agf->error, LOG_ERR);
			$mesg='<div class="error">'.$agf->error.'</div>';
		}

	}
	else
	{
		Header ( "Location: list.php");
		exit;
	}
}



/*
 * View
 */

llxHeader();

$form = new Form($db);

dol_htmloutput_mesg($mesg);

$agf_place = new Agefodd_place($db);
$result_place = $agf_place->fetch($id);

if ($agf_place->fk_reg_interieur) {
	$agf = new Agefodd_reg_interieur($db);
	$result_regint = $agf->fetch($agf_place->fk_reg_interieur);
}
else {
	$action = 'create';
}

$head = site_prepare_head($agf_place);
	
dol_fiche_head($head, 'reg_int', $langs->trans("AgfRegInt"), 0, 'address');

/*
 * Action create
 */
if ($action == 'create' && $user->rights->agefodd->creer)
{
	print '<form name="create" action="'.$_SERVER['PHP_SELF'].'" method="POST">'."\n";
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">'."\n";
	print '<input type="hidden" name="action" value="create_confirm">'."\n";
	print '<input type="hidden" name="id" value="'.$id.'">'."\n";
	
	print '<table class="border" width="100%">';
	
	print '<tr><td width="20%">'.$langs->trans("Id").'</td>';
	print '<td>'.$form->showrefnav($agf_place,'id	','',1,'rowid','id').'</td></tr>';
	
	print '<tr><td>'.$langs->trans("AgfSessPlaceCode").'</td>';
	print '<td>'.$agf_place->ref_interne.'</td></tr>';

	print '<tr><td valign="top">'.$langs->trans("AgfRegInt").'</td>';
	print '<td><textarea name="reg_int" rows="10" cols="0" class="flat" style="width:560px;"></textarea></td></tr>';
	
	print '<tr><td valign="top">'.$langs->trans("AgfNote").'</td>';
	print '<td><textarea name="notes" rows="3" cols="0" class="flat" style="width:360px;"></textarea></td></tr>';

	print '</table>';
	print '</div>';

	print '<table style=noborder align="right">';
	print '<tr><td align="center" colspan=2>';
	print '<input type="submit" name="importadress" class="butAction" value="'.$langs->trans("Save").'"> &nbsp; ';
	print '<input type="submit" name="cancel" class="butActionDelete" value="'.$langs->trans("Cancel").'">';
	print '</td></tr>';
	print '</table>';
	print '</form>';

}
else
{
	// Affichage de la fiche "site de formation"
	if ($result_place > 0 && $result_regint > 0)
	{
			
		// Affichage en mode "édition"
		if ($action == 'edit')
		{				
			print '<form name="update" action="'.$_SERVER['PHP_SELF'].'" method="post">'."\n";
			print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">'."\n";
			print '<input type="hidden" name="action" value="update">'."\n";
			print '<input type="hidden" name="id" value="'.$id.'">'."\n";
			print '<input type="hidden" name="idreg" value="'.$agf_place->fk_reg_interieur.'">'."\n";

			print '<table class="border" width="100%">';

			print '<tr><td width="20%">'.$langs->trans("Id").'</td>';
			print '<td>'.$form->showrefnav($agf_place,'id	','',1,'rowid','id').'</td></tr>';
			
			print '<tr><td>'.$langs->trans("AgfSessPlaceCode").'</td>';
			print '<td>'.$agf_place->ref_interne.'</td></tr>';
		
			print '<tr><td valign="top">'.$langs->trans("AgfRegInt").'</td>';
			print '<td><textarea name="reg_int" rows="10" cols="0" class="flat" style="width:560px;">'.$agf->reg_int.'</textarea></td></tr>';
			
			print '<tr><td valign="top">'.$langs->trans("AgfNote").'</td>';
			print '<td><textarea name="notes" rows="3" cols="0" class="flat" style="width:360px;">'.$agf->notes.'</textarea></td></tr>';
		
			print '</table>';
			print '</div>';
			print '<table style=noborder align="right">';
			print '<tr><td align="center" colspan=2>';
			print '<input type="submit" class="butAction" value="'.$langs->trans("Save").'"> &nbsp; ';
			print '<input type="submit" name="cancel" class="butActionDelete" value="'.$langs->trans("Cancel").'">';
			print '</td></tr>';
			print '</table>';
			
			print '</form>';
				
			print '</div>'."\n";
		}
		else
		{
			// Affichage en mode "consultation"
			
			/*
			 * Confirmation de la suppression
			 */
			if ($action == 'delete')
			{
				$ret=$form->form_confirm($_SERVER['PHP_SELF']."?id=".$id.'&idreg='.$agf_place->fk_reg_interieur,$langs->trans("AgfDeleteRegint"),$langs->trans("AgfConfirmRegInt"),"confirm_delete",'','',1);
				if ($ret == 'html') print '<br>';
			}

			print '<table class="border" width="100%">';

			print '<tr><td width="20%">'.$langs->trans("Id").'</td>';
			print '<td>'.$form->showrefnav($agf_place,'id	','',1,'rowid','id').'</td></tr>';
			
			print '<tr><td>'.$langs->trans("AgfSessPlaceCode").'</td>';
			print '<td>'.$agf_place->ref_interne.'</td></tr>';
		
			print '<tr><td valign="top">'.$langs->trans("AgfRegInt").'</td>';
			print '<td>'.$agf->reg_int.'</td></tr>';
			
			print '<tr><td valign="top">'.$langs->trans("AgfNote").'</td>';
			print '<td>'.$agf->notes.'</td></tr>';
		
			print '</table>';

			print '</div>';
		}
		
	}
	else
	{
		dol_print_error($db);
	}
}


/*
 * Barre d'actions
 *
 */

print '<div class="tabsAction">';

if ($action != 'create' && $action != 'edit' && $action != 'nfcontact')
{
	if ($user->rights->agefodd->creer)
	{
		print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?action=edit&id='.$id.'">'.$langs->trans('Modify').'</a>';
	}
	else
	{
		print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotAllowed")).'">'.$langs->trans('Modify').'</a>';
	}
	if ($user->rights->agefodd->creer)
	{
		print '<a class="butActionDelete" href="'.$_SERVER['PHP_SELF'].'?action=delete&id='.$id.'">'.$langs->trans('Delete').'</a>';
	}
	else
	{
		print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotAllowed")).'">'.$langs->trans('Delete').'</a>';
	}
}

print '</div>';

llxFooter();
?>