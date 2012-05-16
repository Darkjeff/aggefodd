<?php
/* Copyright (C) 2009-2010	Erick Bullier	<eb.dev@ebiconsulting.fr>
 * Copyright (C) 2010-2011	Regis Houssin	<regis@dolibarr.fr>
 * Copyright (C) 2012       Florian Henry  	<florian.henry@open-concept.pro>
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
 * 	\file       /agefodd/admin/agefodd.php
 *	\ingroup    agefodd
 *	\brief      agefood module setup page
 *	\version    $Id$
 */

$res=@include("../../main.inc.php");				// For root directory
if (! $res) $res=@include("../../../main.inc.php");	// For "custom" directory

dol_include_once('/agefodd/training/class/agefodd_formation_catalogue.class.php');
dol_include_once('/agefodd/admin/class/agefodd_session_admlevel.class.php');
dol_include_once('/agefodd/lib/agefodd.lib.php');
require_once(DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php");

$langs->load("admin");
$langs->load('agefodd@agefodd');

if (!$user->admin) accessforbidden();

$value = GETPOST('value','alpha');
$action = GETPOST('action','alpha');
$label = GETPOST('label','alpha');

if ($action == 'updateMask')
{
	$maskconsttraining=GETPOST('maskconstproject','alpha');
	$masktraining=GETPOST('maskproject','alpha');

	if ($maskconsttraining)  $res = dolibarr_set_const($db,$maskconsttraining,$masktraining,'chaine',0,'',$conf->entity);

	if (! $res > 0) $error++;

	if (! $error)
	{
		$mesg = "<font class=\"ok\">".$langs->trans("SetupSaved")."</font>";
	}
	else
	{
		$mesg = "<font class=\"error\">".$langs->trans("Error")."</font>";
	}
}

if ($action == 'sessionlevel_create')
{
	$agf = new Agefodd_session_admlevel($db);
	
	$parent_level = GETPOST('parent_level','int');
	
	if (!empty($parent_level))
	{
		$agf->fk_parent_level = $parent_level;
		
		$agf_static = new Agefodd_session_admlevel($db);
		$result_stat = $agf_static->fetch($agf->fk_parent_level);
		
		if ($result_stat > 0)
		{
			if (!empty($agf_static->id))
			{
				$agf->level_rank = $agf_static->level_rank + 1;
				$agf->indice = ebi_get_adm_get_next_indice_action($agf_static->id);
			}
			else
			{	//no parent : This case may not occur but we never know
				$agf->indice = (ebi_get_adm_level_number() + 1) . '00';
				$agf->level_rank = 0;
			}
		}
		else
		{
			dol_syslog("Agefodd::agefodd error=".$result_stat->error, LOG_ERR);
			$mesg = '<div class="error">'.$result_stat->error.'</div>';
		}
	}
	else
	{
		//no parent
		$agf->fk_parent_level = 0;
		$agf->indice = (ebi_get_adm_level_number() + 1) . '00';
		$agf->level_rank = 0;
	}
	
	$agf->intitule = GETPOST('intitule','alpha');
	$agf->delais_alerte = GETPOST('delai','int');
	
	if ($agf->level_rank>3)
	{
		$mesg = '<div class="error">'.$langs->trans("AgfAdminNoMoreThan3Level").'</div>';
	}
	else
	{
		$result = $agf->create($user->id);
		
		if ($result1!=1)
		{
			dol_syslog("Agefodd::agefodd error=".$agf->error, LOG_ERR);
			$mesg = '<div class="error">'.$agf->error.'</div>';
		}
	}
		
	
}

if ($action == 'sessionlevel_update')
{
	$agf = new Agefodd_session_admlevel($db);
	
	$id = GETPOST('id','int');
	$parent_level = GETPOST('parent_level','int');
	
	$result = $agf->fetch($id);
	
	if ($result > 0)
	{
	
		//Up level of action
		if (GETPOST('sesslevel_up_x'))
		{
			$result2 = $agf->shift_indice($user->id,'less');
			if ($result1!=1)
			{
				dol_syslog("Agefodd::agefodd error=".$agf->error, LOG_ERR);
				$mesg = '<div class="error">'.$agf->error.'</div>';
			}
		}
		
		//Down level of action
		if (GETPOST('sesslevel_down_x'))
		{
			$result1 = $agf->shift_indice($user->id,'more');
			if ($result1!=1)
			{
				dol_syslog("Agefodd::agefodd error=".$agf->error, LOG_ERR);
				$mesg = '<div class="error">'.$agf->error.'</div>';
			}
		}
		
		//Update action
		if (GETPOST('sesslevel_update_x'))
		{
			$agf->intitule = GETPOST('intitule','alpha');
			$agf->delais_alerte = GETPOST('delai','int');
			
			if (!empty($parent_level))
			{
				if ($parent_level!=$agf->fk_parent_level)
				{
					$agf->fk_parent_level = $parent_level;
				
					$agf_static = new Agefodd_session_admlevel($db);
					$result_stat = $agf_static->fetch($agf->fk_parent_level);
				
					if ($result_stat > 0)
					{
						if (!empty($agf_static->id))
						{
							$agf->level_rank = $agf_static->level_rank + 1;
							$agf->indice = ebi_get_adm_get_next_indice_action($agf_static->id);
						}
						else
						{	//no parent : This case may not occur but we never know
							$agf->indice = (ebi_get_adm_level_number() + 1) . '00';
							$agf->level_rank = 0;
						}
					}
					else
					{
						dol_syslog("Agefodd::agefodd error=".$result_stat->error, LOG_ERR);
						$mesg = '<div class="error">'.$result_stat->error.'</div>';
					}
				}
			}
			else
			{
				//no parent
				$agf->fk_parent_level = 0;
				$agf->indice = (ebi_get_adm_level_number() + 1) . '00';
				$agf->level_rank = 0;
			}
			
			if ($agf->level_rank>3)
			{
				$mesg = '<div class="error">'.$langs->trans("AgfAdminNoMoreThan3Level").'</div>';
			}
			else
			{
				$result1 = $agf->update($user->id);
				if ($result1!=1)
				{
					dol_syslog("Agefodd::agefodd error=".$agf->error, LOG_ERR);
					$mesg = '<div class="error">'.$agf->error.'</div>';
				}
			}
		}
		
		//Delete action
		if (GETPOST('sesslevel_remove_x'))
		{
			
			$result = $agf->delete($user->id);
			if ($result!=1)
			{
				dol_syslog("Agefodd::agefodd error=".$agf->error, LOG_ERR);
				$mesg = '<div class="error">'.$agf->error.'</div>';
			}
		}
	}
	else
	{
		$mesg = '<div class="error">This action do not exists</div>';
	}
}

/*
 *  Admin Form
 *
 */

llxHeader();

$form=new Form($db);

dol_htmloutput_mesg($mesg);

$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';
print_fiche_titre($langs->trans("AgefoddSetupDesc"),$linkback,'setup');

// Agefodd numbering module
print_titre($langs->trans("AgfAdminTrainingNumber"));
print '<br>';
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td width="100px">'.$langs->trans("Name").'</td>';
print '<td>'.$langs->trans("Description").'</td>';
print '<td>'.$langs->trans("Example").'</td>';
print '<td align="center" width="60px">'.$langs->trans("Activated").'</td>';
print '<td align="center" width="80px">'.$langs->trans("Infos").'</td>';
print "</tr>\n";

clearstatcache();

$dirmodels=array_merge(array('/'),(array) $conf->modules_parts['models']);

foreach ($dirmodels as $reldir)
{
	$dir = dol_buildpath("/agefodd/core/modules/agefodd/");
	
	if (is_dir($dir))
	{
		$handle = opendir($dir);
		if (is_resource($handle))
		{
			$var=true;
			
			while (($file = readdir($handle))!==false)
			{
				if (preg_match('/^(mod_.*)\.php$/i',$file,$reg))
				{
					$file = $reg[1];
					$classname = substr($file,4);

					require_once($dir.$file.".php");

					$module = new $file;
					
					// Show modules according to features level
					if ($module->version == 'development'  && $conf->global->MAIN_FEATURES_LEVEL < 2) continue;
					if ($module->version == 'experimental' && $conf->global->MAIN_FEATURES_LEVEL < 1) continue;

					if ($module->isEnabled())
					{	
						$var=!$var;
						print '<tr '.$bc[$var].'><td>'.$module->nom."</td><td>\n";
						print $module->info();
						print '</td>';

						// Show example of numbering module
						print '<td nowrap="nowrap">';
						$tmp=$module->getExample();
						if (preg_match('/^Error/',$tmp)) {
							$langs->load("errors"); print '<div class="error">'.$langs->trans($tmp).'</div>';
						}
						elseif ($tmp=='NotConfigured') print $langs->trans($tmp);
						else print $tmp;
						print '</td>'."\n";

						print '<td align="center">';
						if ($conf->global->AGF_ADDON == 'mod_'.$classname)
						{
							print img_picto($langs->trans("Activated"),'switch_on');
						}
						else
						{
							print '<a href="'.$_SERVER["PHP_SELF"].'?action=setmod&amp;value=mod_'.$classname.'" alt="'.$langs->trans("Default").'">'.img_picto($langs->trans("Disabled"),'switch_off').'</a>';
						}
						print '</td>';

						$agf=new Agefodd($db);
						$agf->initAsSpecimen();

						// Info
						$htmltooltip='';
						$htmltooltip.=''.$langs->trans("Version").': <b>'.$module->getVersion().'</b><br>';
						$nextval=$module->getNextValue($mysoc,$agf);
						if ("$nextval" != $langs->trans("AgfNotAvailable"))	// Keep " on nextval
						{
							$htmltooltip.=''.$langs->trans("NextValue").': ';
							if ($nextval)
							{
								$htmltooltip.=$nextval.'<br>';
							}
							else
							{
								$htmltooltip.=$langs->trans($module->error).'<br>';
							}
						}

						print '<td align="center">';
						print $form->textwithpicto('',$htmltooltip,1,0);
						print '</td>';

						print '</tr>';
					}
				}
			}
			closedir($handle);
		}
	}
}

print '</table><br>';

//Admin Session level administation

$admlevel = new Agefodd_session_admlevel($db);
$result0 = $admlevel->fetch_all();


print_titre($langs->trans("AgfAdminSessionLevel"));

// Agefodd numbering module
if ($result0>0)
{
	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre">';
	print '<td width="10px"></td>';
	print '<td>'.$langs->trans("AgfIntitule").'</td>';
	print '<td>'.$langs->trans("AgfParentLevel").'</td>';
	print '<td>'.$langs->trans("AgfDelaiSessionLevel").'</td>';
	print '<td></td>';
	print "</tr>\n";
	
	$var=true;
	foreach ($admlevel->line as $line)
	{   
		$var=!$var;
		$toplevel='';
		print '<form name="SessionLevel_update_'.$line->rowid.'" action="'.$_SERVER['PHP_SELF'].'" method="POST">'."\n";
		print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">'."\n";
		print '<input type="hidden" name="id" value="'.$line->rowid.'">'."\n";
		print '<input type="hidden" name="action" value="sessionlevel_update">'."\n";
		print '<tr '.$bc[$var].'>';
		
		print '<td>';
		if ($line->indice!=ebi_get_adm_indice_per_rank($line->level_rank,$line->fk_parent_level,'MIN'))
		{
			print '<input type="image" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/1uparrow.png" border="0" name="sesslevel_up" alt="'.$langs->trans("Save").'">';
		}
		if ($line->indice!=ebi_get_adm_indice_per_rank($line->level_rank,$line->fk_parent_level,'MAX'))
		{
			print '<input type="image" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/1downarrow.png" border="0" name="sesslevel_down" alt="'.$langs->trans("Save").'">';
		}
		print '</td>';
		
		print '<td>'.str_repeat('&nbsp;&nbsp;&nbsp;',$line->level_rank).'<input type="text" name="intitule" value="'.$line->intitule.'" size="30"/></td>';
		print '<td>'.ebi_select_action_session_adm($line->fk_parent_level,'parent_level',$line->rowid).'</td>';
		print '<td><input type="text" name="delai" value="'.$line->alerte.'"/></td>';
		print '<td><input type="image" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/edit.png" border="0" name="sesslevel_update" alt="'.$langs->trans("Save").'">';
 		print '<input type="image" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/delete.png" border="0" name="sesslevel_remove" alt="'.$langs->trans("Delete").'"></td>';
		print '</tr>';
		print '</form>';
	}
	print '<form name="SessionLevel_create" action="'.$_SERVER['PHP_SELF'].'" method="POST">'."\n";
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">'."\n";
	print '<input type="hidden" name="action" value="sessionlevel_create">'."\n";
	print '<tr>';
	print '<td></td>';
	print '<td><input type="text" name="intitule" value="" size="30"/></td>';
	print '<td>'.ebi_select_action_session_adm('','parent_level').'</td>';
	print '<td><input type="text" name="delai" value=""/></td>';
	print '<td><input type="image" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/edit_add.png" border="0" name="sesslevel_update" alt="'.$langs->trans("Save").'"></td>';
	print '</tr>';
	print '</form>';

}
else
{
	print '<div class="error">'.$admlevel->error.'</div>';
}
print '</table><br>';


$db->close();

llxFooter('$Date: 2010-03-21 21:28:31 +0100 (dim. 21 mars 2010) $ - $Revision: 46 $');
?>
