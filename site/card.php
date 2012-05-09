<?php
/* Copyright (C) 2009-2010	Erick Bullier	<eb.dev@ebiconsulting.fr>
 * Copyright (C) 2010-2011	Regis Houssin	<regis@dolibarr.fr>
 * Copyright (C) 2012       Florian Henry   	<florian.henry@open-concept.pro>
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
 *  \file       	/agefodd/site/card.php $
 *  \brief      	Page fiche site de formation
 *  \version		$Id$
 */

$res=@include("../../main.inc.php");				// For root directory
if (! $res) $res=@include("../../../main.inc.php");	// For "custom" directory


dol_include_once('/agefodd/site/class/agefodd_session_place.class.php');
dol_include_once('/agefodd/lib/agefodd.lib.php');
require_once(DOL_DOCUMENT_ROOT."/core/class/html.formcompany.class.php");

// Security check
if (!$user->rights->agefodd->lire) accessforbidden();

$mesg = '';

$action=GETPOST('action','alpha');
$confirm=GETPOST('action','alpha');
$id=GETPOST('id','int');
$arch=GETPOST('arch','int');

/*
 * Actions delete
 */
if ($action == 'confirm_delete' && $confirm == "yes" && $user->rights->agefodd->creer)
{
	$agf = new Agefodd_session_place($db);
	$result = $agf->remove($id);
	
	if ($result > 0)
	{
		Header ( "Location: list.php");
		exit;
	}
	else
	{
		dol_syslog("agefodd::site::card error=".$agf->error, LOG_ERR);
		$mesg='<div class="error">'.$langs->trans("AgfDeleteErr").':'.$agf->error.'</div>';
	}
}


/*
 * Actions archive/active
 */
if ($action == 'arch_confirm_delete' && $user->rights->agefodd->creer)
{
	if ($confirm == "yes")
	{
		$agf = new Agefodd_session_place($db);
	
		$result = $agf->fetch($id);
	
		$agf->archive = $arch;
		$result = $agf->update($user->id);
	
		if ($result > 0)
		{
			Header ( "Location: ".$_SERVER['PHP_SELF']."?id=".$id);
			exit;
		}
		else
		{
			dol_syslog("agefodd::site::card error=".$agf->error, LOG_ERR);
			$mesg='<div class="error">'.$agf->error.'</div>';
		}
	
	}
	else
	{
		Header ( "Location: ".$_SERVER['PHP_SELF']."?id=".$id);
		exit;
	}
}

/*
 * Action update (fiche site de formation)
 */
if ($action == 'update' && $user->rights->agefodd->creer)
{
	if (! $_POST["cancel"])
	{
		$agf = new Agefodd_session_place($db);

		$result = $agf->fetch($id);

		$agf->ref = GETPOST('ref','alpha');
		$agf->adresse = GETPOST('adresse','alpha');
		$agf->cp = GETPOST('cp','alpha');
		$agf->ville = GETPOST('ville','alpha');
		$agf->pays = GETPOST('pays','alpha');
		$agf->tel = GETPOST('phone','alpha');
		$agf->fk_societe = GETPOST('societe','int');
		$agf->notes = GETPOST('notes','alpha');
		$result = $agf->update($user->id);

		if ($result > 0)
		{
			Header ( "Location: ".$_SERVER['PHP_SELF']."?id=".$id);
			exit;
		}
		else
		{
			dol_syslog("agefodd::site::card error=".$agf->error, LOG_ERR);
			$mesg='<div class="error">'.$agf->error.'</div>';
		}

	}
	else
	{
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
		$agf = new Agefodd_session_place($db);

		$agf->ref = GETPOST('ref','alpha');
		$agf->adresse = GETPOST('adresse','alpha');
		$agf->cp = GETPOST('cp','alpha');
		$agf->ville = GETPOST('ville','alpha');
		$agf->pays = GETPOST('pays','alpha');
		$agf->tel = GETPOST('phone','alpha');
		$agf->fk_societe = GETPOST('societe','int');
		$agf->notes = GETPOST('notes','alpha');
		$result = $agf->create($user->id);

		if ($result > 0)
		{
			Header ( "Location: ".$_SERVER['PHP_SELF']."?action=edit&id=".$result);
			exit;
		}
		else
		{
			dol_syslog("agefodd::site::card error=".$agf->error, LOG_ERR);
			$mesg='<div class="error">'.$agf->error.'</div>';
		}

	}
	else
	{
		Header ( "Location: ".$_SERVER['PHP_SELF']."?id=".$id);
		exit;
	}
}



/*
 * View
 */

llxHeader();

$form = new Form($db);


dol_htmloutput_mesg($mesg);

/*
 * Action create
 */
if ($action == 'create' && $user->rights->agefodd->creer)
{
	$formcompany = new FormCompany($db);
	print_fiche_titre($langs->trans("AgfCreatePlace"));

	print '<form name="create" action="'.$_SERVER['PHP_SELF'].'" method="POST">'."\n";
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">'."\n";
	print '<input type="hidden" name="action" value="create">'."\n";

	print '<table class="border" width="100%">'."\n";

	print '<tr><td><span class="fieldrequired">'.$langs->trans("AgfSessPlaceCode").'</span></td>';
	print '<td><input name="ref" class="flat" size="50" value=""></td></tr>';

	print '<tr><td><span class="fieldrequired">'.$langs->trans("Societe").'</span></td>';
	print '<td>'.ebi_select_societe("").'</td></tr>';

	print '<tr><td>'.$langs->trans("Address").'</td>';
	print '<td><input name="adresse" class="flat" size="50" value=""></td></tr>';
	
	print '<tr><td>'.$langs->trans('CP').'</td><td>';
	print $formcompany->select_ziptown('','cp',array('ville','pays'),6).'</tr>';
	print '<tr></td><td>'.$langs->trans('Ville').'</td><td>';
	print $formcompany->select_ziptown('','ville',array('cp','pays')).'</tr>';
	
	/*print '<tr><td>'.$langs->trans("CP").'</td>';
	print '<td><input name="cp" class="flat" size="50" value=""></td></tr>';
	
	print '<tr><td>'.$langs->trans("Ville").'</td>';
	print '<td><input name="ville" class="flat" size="50" value=""></td></tr>';*/
	
	
	print '<tr><td>'.$langs->trans("Pays").'</td>';
	print '<td>'.$form->select_country('','pays').'</td></tr>';
	
	
	print '<tr><td>'.$langs->trans("Phone").'</td>';
	print '<td><input name="phone" class="flat" size="50" value=""></td></tr>';

	print '<tr><td valign="top">'.$langs->trans("AgfNote").'</td>';
	print '<td><textarea name="notes" rows="3" cols="0" class="flat" style="width:360px;"></textarea></td></tr>';
	print '</table>';
	print '</div>';


	print '<table style=noborder align="right">';
	print '<tr><td align="center" colspan=2>';
	print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?action=create_confirm&id='.$id.'">'.$langs->trans('Save').'</a>';
	print '<input type="submit" name="cancel" class="butActionDelete" value="'.$langs->trans("Cancel").'">';
	print '</td></tr>';
	print '</table>';
	print '</form>';

}
else
{
	// Affichage de la fiche "site de formation"
	if ($id)
	{
		$agf = new Agefodd_session_place($db);
		$result = $agf->fetch($id);

		if ($result)
		{
			// Affichage en mode "édition"
			if ($action == 'edit')
			{
				$head = site_prepare_head($agf);
				
				dol_fiche_head($head, 'card', $langs->trans("AgfSessPlace"), 0, 'user');

				print '<form name="update" action="s_place.php" method="post">'."\n";
				print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">'."\n";
				print '<input type="hidden" name="action" value="update">'."\n";
				print '<input type="hidden" name="id" value="'.$id.'">'."\n";

				print '<table class="border" width="100%">'."\n";
				print '<tr><td width="20%">'.$langs->trans("Id").'</td>';
				print '<td>'.$agf->id.'</td></tr>';

				print '<tr><td>'.$langs->trans("AgfSessPlaceCode").'</td>';
				print '<td><input name="ref" class="flat" size="50" value="'.$agf->ref.'"></td></tr>';

				print '<tr><td>'.$langs->trans("Societe").'</td>';
				print '<td>'.ebi_select_societe($agf->socid,"societe").'</td></tr>';

				print '<tr><td>'.$langs->trans("Address").'</td>';
				print '<td><input name="adresse" class="flat" size="50" value="'.$agf->adresse.'"></td></tr>';
				
				print '<tr><td>'.$langs->trans("CP").'</td>';
				print '<td><input name="cp" class="flat" size="50" value="'.$agf->cp.'"></td></tr>';
				
				print '<tr><td>'.$langs->trans("Ville").'</td>';
				print '<td><input name="ville" class="flat" size="50" value="'.$agf->ville.'"></td></tr>';
				
				print '<tr><td>'.$langs->trans("Pays").'</td>';
				print '<td>'.ebi_select_pays($agf->pays).'</td></tr>';
				
				print '<tr><td>'.$langs->trans("Phone").'</td>';
				print '<td><input name="phone" class="flat" size="50" value="'.$agf->tel.'"></td></tr>';

				print '<tr><td valign="top">'.$langs->trans("AgfNote").'</td>';
				if (!empty($agf->notes)) $notes = nl2br($agf->notes);
				else $notes =  $langs->trans("AgfUndefinedNote");
				print '<td><textarea name="notes" rows="3" cols="0" class="flat" style="width:360px;">'.$agf->notes.'</textarea></td></tr>';


				print '</table>';
				print '</div>';
				print '<table style=noborder align="right">';
				print '<tr><td align="center" colspan=2>';
				print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?action=create_confirm&id='.$id.'">'.$langs->trans('Save').'</a>';
				print '<input type="submit" name="cancel" class="butActionDelete" value="'.$langs->trans("Cancel").'">';
				print '</td></tr>';
				print '</table>';
				print '</form>';
					
				print '</div>'."\n";
			}
			else
			{
				// Affichage en mode "consultation"
				
				$head = site_prepare_head($agf);
				
				dol_fiche_head($head, 'card', $langs->trans("AgfSessPlace"), 0, 'user');

				/*
				 * Confirmation de la suppression
				 */
				if ($action == 'delete')
				{
					$ret=$form->form_confirm($_SERVER['PHP_SELF']."?id=".$id,$langs->trans("AgfDeletePlace"),$langs->trans("AgfConfirmDeletePlace"),"confirm_delete");
					if ($ret == 'html') print '<br>';
				}
				/*
				* Confirmation de l'archivage/activation suppression
				*/
				if (isset($_GET["arch"]))
				{
					$ret=$form->form_confirm($_SERVER['PHP_SELF']."?arch=".$_GET["arch"]."&id=".$id,$langs->trans("AgfFormationArchiveChange"),$langs->trans("AgfConfirmArchiveChange"),"arch_confirm_delete");
					if ($ret == 'html') print '<br>';
				}

				print '<table class="border" width="100%">';

				print '<tr><td width="20%">'.$langs->trans("Id").'</td>';
				print '<td>'.$agf->id.'</td></tr>';

				print '<tr><td>'.$langs->trans("AgfSessPlaceCode").'</td>';
				print '<td>'.$agf->ref.'</td></tr>';

				print '<tr><td valign="top">'.$langs->trans("Company").'</td><td>';
				if ($agf->socid)
				{
				    print '<a href="'.DOL_URL_ROOT.'/comm/fiche.php?socid='.$agf->socid.'">';
				    print img_object($langs->trans("ShowCompany"),"company").' '.dol_trunc($agf->socname,20).'</a>';
				}
				else
				{
				    print '&nbsp;';
				}

				print '<tr><td rowspan=3 valign="top">'.$langs->trans("Address").'</td>';
				print '<td>'.$agf->adresse.'</td></tr>';

				print '<tr>';
				print '<td>'.$agf->cp.' - '.$agf->ville.'</td></tr>';

				print '<tr>';
				print '<td>'.$agf->pays.'</td></tr>';
				
				print '</td></tr>';
				
				print '<tr><td>'.$langs->trans("Phone").'</td>';
				print '<td>'.dol_print_phone($agf->tel).'</td></tr>';

				print '<tr><td valign="top">'.$langs->trans("AgfNotes").'</td>';
				print '<td>'.nl2br($agf->notes).'</td></tr>';

				print "</table>";

				print '</div>';
			}

		}
		else
		{
			dol_print_error($db);
		}
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
	if ($agf->archive == 0)
	{
		$button = $langs->trans('AgfArchiver');
		$arch = 1;
	}
	else
	{
		$button = $langs->trans('AgfActiver');
		$arch = 0;
	}
	if ($user->rights->agefodd->modifier)
	{
		print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?arch='.$arch.'&id='.$id.'">'.$button.'</a>';
	}
	else
	{
		print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotAllowed")).'">'.$button.'</a>';
	}


}

print '</div>';

llxFooter('$Date: 2010-03-30 20:58:28 +0200 (mar. 30 mars 2010) $ - $Revision: 54 $');
?>
