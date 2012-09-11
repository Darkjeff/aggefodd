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

dol_include_once('/agefodd/class/agefodd_formation_catalogue.class.php');
dol_include_once('/agefodd/class/agefodd_session_admlevel.class.php');
dol_include_once('/agefodd/class/html.formagefodd.class.php');
dol_include_once('/agefodd/lib/agefodd.lib.php');
require_once(DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php");

$langs->load("admin");
$langs->load('agefodd@agefodd');

if (!$user->admin) accessforbidden();

$action = GETPOST('action','alpha');

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

if ($action == 'setvar')
{
	$use_typestag=GETPOST('AGF_USE_STAGIAIRE_TYPE','int');
	$res = dolibarr_set_const($db, 'AGF_USE_STAGIAIRE_TYPE', $use_typestag,'yesno',0,'',$conf->entity);
	if (! $res > 0) $error++;
	
	$def_typestag=GETPOST('AGF_DEFAULT_STAGIAIRE_TYPE','int');
	if (!empty($def_typestag))
	{
		$res = dolibarr_set_const($db, 'AGF_DEFAULT_STAGIAIRE_TYPE', $def_typestag,'chaine',0,'',$conf->entity);
		if (! $res > 0) $error++;
	}
	
	$pref_val=GETPOST('AGF_ORGANISME_PREF','alpha');
	$res = dolibarr_set_const($db, 'AGF_ORGANISME_PREF', $pref_val,'chaine',0,'',$conf->entity);
	if (! $res > 0) $error++;
	
	$num_org=GETPOST('AGF_ORGANISME_NUM','alpha');
	$res = dolibarr_set_const($db, 'AGF_ORGANISME_NUM', $num_org,'chaine',0,'',$conf->entity);
	if (! $res > 0) $error++;
	
	$org_rep=GETPOST('AGF_ORGANISME_REPRESENTANT','alpha');
	$res = dolibarr_set_const($db, 'AGF_ORGANISME_REPRESENTANT', $org_rep,'chaine',0,'',$conf->entity);
	if (! $res > 0) $error++;
	
	$usesearch_training=GETPOST('AGF_TRAINING_USE_SEARCH_TO_SELECT','alpha');
	$res = dolibarr_set_const($db, 'AGF_TRAINING_USE_SEARCH_TO_SELECT', $usesearch_training,'chaine',0,'',$conf->entity);
	if (! $res > 0) $error++;
	
	$usesearch_trainer=GETPOST('AGF_TRAINER_USE_SEARCH_TO_SELECT','alpha');
	$res = dolibarr_set_const($db, 'AGF_TRAINER_USE_SEARCH_TO_SELECT', $usesearch_trainer,'chaine',0,'',$conf->entity);
	if (! $res > 0) $error++;
	
	$usesearch_trainee=GETPOST('AGF_TRAINEE_USE_SEARCH_TO_SELECT','alpha');
	$res = dolibarr_set_const($db, 'AGF_TRAINEE_USE_SEARCH_TO_SELECT', $usesearch_trainee,'chaine',0,'',$conf->entity);
	if (! $res > 0) $error++;
	
	$usesearch_site=GETPOST('AGF_SITE_USE_SEARCH_TO_SELECT','alpha');
	$res = dolibarr_set_const($db, 'AGF_SITE_USE_SEARCH_TO_SELECT', $usesearch_site,'chaine',0,'',$conf->entity);
	if (! $res > 0) $error++;
	
	$usesearch_stagstype=GETPOST('AGF_STAGTYPE_USE_SEARCH_TO_SELECT','alpha');
	$res = dolibarr_set_const($db, 'AGF_STAGTYPE_USE_SEARCH_TO_SELECT', $usesearch_stagstype,'chaine',0,'',$conf->entity);
	if (! $res > 0) $error++;
	
	$usesearch_contact=GETPOST('AGF_CONTACT_USE_SEARCH_TO_SELECT','alpha');
	$res = dolibarr_set_const($db, 'AGF_CONTACT_USE_SEARCH_TO_SELECT', $usesearch_contact,'chaine',0,'',$conf->entity);
	if (! $res > 0) $error++;
	
	$use_dol_contact=GETPOST('AGF_CONTACT_DOL_SESSION','alpha');
	$res = dolibarr_set_const($db, 'AGF_CONTACT_DOL_SESSION', $use_dol_contact,'chaine',0,'',$conf->entity);
	if (! $res > 0) $error++;
	
	$nb_num_list=GETPOST('AGF_NUM_LIST','int');
	$res = dolibarr_set_const($db, 'AGF_NUM_LIST', $nb_num_list,'chaine',0,'',$conf->entity);
	if (! $res > 0) $error++;
	
	
	$usedolibarr_agenda=GETPOST('AGF_DOL_AGENDA','alpha');
	if ($usedolibarr_agenda && !$conf->global->MAIN_MODULE_AGENDA) {
		$msg=$langs->trans("AgfAgendaModuleNedeed");
		$error++;
	}
	else {
		$res = dolibarr_set_const($db, 'AGF_DOL_AGENDA', $usedolibarr_agenda,'chaine',0,'',$conf->entity);
	}
	
	if (! $res > 0) $error++;
	
	if (! $error)
	{
		$mesg = "<font class=\"ok\">".$langs->trans("SetupSaved")."</font>";
	}
	else
	{
		$mesg = "<font class=\"error\">".$langs->trans("Error")." ".$msg."</font>";
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
		$result = $agf->create($user);
		
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
			$result2 = $agf->shift_indice($user,'less');
			if ($result1!=1)
			{
				dol_syslog("Agefodd::agefodd error=".$agf->error, LOG_ERR);
				$mesg = '<div class="error">'.$agf->error.'</div>';
			}
		}
		
		//Down level of action
		if (GETPOST('sesslevel_down_x'))
		{
			$result1 = $agf->shift_indice($user,'more');
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
				$result1 = $agf->update($user);
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
			
			$result = $agf->delete($user);
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
$formAgefodd=new FormAgefodd($db);

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


// Admin var of module
print_titre($langs->trans("AgfAdmVar"));

print '<table class="noborder" width="100%">';

print '<form method="post" action="'.$_SERVER['PHP_SELF'].'">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="setvar">';

print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Name").'</td>';
print '<td width="400px">'.$langs->trans("Valeur").'</td>';
print '<td></td>';
print "</tr>\n";

//Prefecture d\'enregistrement
print '<tr class="pair"><td>'.$langs->trans("AgfPrefNom").'</td>';
print '<td align="left">';
print '<input type="text"   name="AGF_ORGANISME_PREF" value="'.$conf->global->AGF_ORGANISME_PREF.'" size="20" ></td>';
print '<td align="center">';
print $form->textwithpicto('',$langs->trans("AgfPrefNomHelp"),1,'help');
print '</td>';
print '</tr>';

//Numerot d\'enregistrement a la prefecture
print '<tr class="impair"><td>'.$langs->trans("AgfPrefNum").'</td>';
print '<td align="left">';
print '<input type="text"   name="AGF_ORGANISME_NUM" value="'.$conf->global->AGF_ORGANISME_NUM.'" size="20" ></td>';
print '<td align="center">';
print $form->textwithpicto('',$langs->trans("AgfPrefNumHelp"),1,'help');
print '</td>';
print '</tr>';

//Representant de la societé de formation
print '<tr class="pair"><td>'.$langs->trans("AgfRepresant").'</td>';
print '<td align="left">';
print '<input type="text" name="AGF_ORGANISME_REPRESENTANT" value="'.$conf->global->AGF_ORGANISME_REPRESENTANT.'" size="20" ></td>';
print '<td align="center">';
print $form->textwithpicto('',$langs->trans("AgfRepresantHelp"),1,'help');
print '</td>';
print '</tr>';

//Nombre d'element dans les list
print '<tr class="pair"><td>'.$langs->trans("AgfNbElemList").'</td>';
print '<td align="left">';
print '<input type="text" name="AGF_NUM_LIST" value="'.$conf->global->AGF_NUM_LIST.'" size="5" ></td>';
print '<td align="center">';
print $form->textwithpicto('',$langs->trans("AgfNbElemListHelp"),1,'help');
print '</td>';
print '</tr>';

//Utilisation d'un type de stagaire
print '<tr class="pair"><td>'.$langs->trans("AgfUseStagType").'</td>';
print '<td align="left">';
$arrval=array('0'=>$langs->trans("No"),	'1'=>$langs->trans("Yes"));
print $form->selectarray("AGF_USE_STAGIAIRE_TYPE",$arrval,$conf->global->AGF_USE_STAGIAIRE_TYPE);
print '</td>';
print '<td align="center">';
print $form->textwithpicto('',$langs->trans("AgfUseStagTypeHelp"),1,'help');
print '</td>';
print '</tr>';

//Utilisation du contact agefodd ou dolibarr a la creation de la session
print '<tr class="pair"><td>'.$langs->trans("AgfUseSessionDolContact").'</td>';
print '<td align="left">';
$arrval=array('0'=>$langs->trans("No"),	'1'=>$langs->trans("Yes"));
print $form->selectarray("AGF_CONTACT_DOL_SESSION",$arrval,$conf->global->AGF_CONTACT_DOL_SESSION);
print '</td>';
print '<td align="center">';
print $form->textwithpicto('',$langs->trans("AgfUseSessionDolContactHelp"),1,'help');
print '</td>';
print '</tr>';

if ($conf->global->AGF_USE_STAGIAIRE_TYPE)
{
	//Type de stagaire par defaut
	print '<tr class="pair"><td>'.$langs->trans("AgfUseStagTypeDefault").'</td>';
	print '<td align="left">';
	print $formAgefodd->select_type_stagiaire($conf->global->AGF_DEFAULT_STAGIAIRE_TYPE, 'AGF_DEFAULT_STAGIAIRE_TYPE');
	print '</td>';
	print '<td align="center">';
	print '</td>';
	print '</tr>';
}

// utilisation formulaire Ajax sur choix training
print '<tr class="impair">';
print '<td>'.$langs->trans("AgfUseSearchToSelectTraining").'</td>';
if (! $conf->use_javascript_ajax)
{
	print '<td nowrap="nowrap" align="right" colspan="2">';
	print $langs->trans("NotAvailableWhenAjaxDisabled");
	print '</td>';
}
else
{
	print '<td align="left">';
	$arrval=array('0'=>$langs->trans("No"),	'1'=>$langs->trans("Yes"));
	print $form->selectarray("AGF_TRAINING_USE_SEARCH_TO_SELECT",$arrval,$conf->global->AGF_TRAINING_USE_SEARCH_TO_SELECT);
	print '</td>';
}
print '<td>&nbsp;</td>';
print '</tr>';

// utilisation formulaire Ajax sur choix trainer
print '<tr class="pair">';
print '<td>'.$langs->trans("AgfUseSearchToSelectTrainer").'</td>';
if (! $conf->use_javascript_ajax)
{
	print '<td nowrap="nowrap" align="right" colspan="2">';
	print $langs->trans("NotAvailableWhenAjaxDisabled");
	print '</td>';
}
else
{
	print '<td align="left">';
	$arrval=array('0'=>$langs->trans("No"),	'1'=>$langs->trans("Yes"));
	print $form->selectarray("AGF_TRAINER_USE_SEARCH_TO_SELECT",$arrval,$conf->global->AGF_TRAINER_USE_SEARCH_TO_SELECT);
	print '</td>';
}
print '<td>&nbsp;</td>';
print '</tr>';

// utilisation formulaire Ajax sur choix trainee
print '<tr class="impair">';
print '<td>'.$langs->trans("AgfUseSearchToSelectTrainee").'</td>';
if (! $conf->use_javascript_ajax)
{
	print '<td nowrap="nowrap" align="right" colspan="2">';
	print $langs->trans("NotAvailableWhenAjaxDisabled");
	print '</td>';
}
else
{
	print '<td  align="left">';
	$arrval=array('0'=>$langs->trans("No"),	'1'=>$langs->trans("Yes"));
	print $form->selectarray("AGF_TRAINEE_USE_SEARCH_TO_SELECT",$arrval,$conf->global->AGF_TRAINEE_USE_SEARCH_TO_SELECT);
	print '</td>';
}
print '<td>&nbsp;</td>';
print '</tr>';

// utilisation formulaire Ajax sur choix site
print '<tr class="pair">';
print '<td>'.$langs->trans("AgfUseSearchToSelectSite").'</td>';
if (! $conf->use_javascript_ajax)
{
	print '<td nowrap="nowrap" align="right" colspan="2">';
	print $langs->trans("NotAvailableWhenAjaxDisabled");
	print '</td>';
}
else
{
	print '<td align="left">';
	$arrval=array('0'=>$langs->trans("No"),	'1'=>$langs->trans("Yes"));
	print $form->selectarray("AGF_SITE_USE_SEARCH_TO_SELECT",$arrval,$conf->global->AGF_SITE_USE_SEARCH_TO_SELECT);
	print '</td>';
}
print '<td>&nbsp;</td>';
print '</tr>';

if ($conf->global->AGF_USE_STAGIAIRE_TYPE)
{
	// utilisation formulaire Ajax sur choix type de stagiaire
	print '<tr class="impair">';
	print '<td>'.$langs->trans("AgfUseSearchToSelectStagType").'</td>';
	if (! $conf->use_javascript_ajax)
	{
		print '<td nowrap="nowrap" align="right" colspan="2">';
		print $langs->trans("NotAvailableWhenAjaxDisabled");
		print '</td>';
	}
	else
	{
		print '<td align="left">';
		$arrval=array('0'=>$langs->trans("No"),	'1'=>$langs->trans("Yes"));
		print $form->selectarray("AGF_STAGTYPE_USE_SEARCH_TO_SELECT",$arrval,$conf->global->AGF_STAGTYPE_USE_SEARCH_TO_SELECT);
		print '</td>';
	}
	print '<td>&nbsp;</td>';
	print '</tr>';
}

//Lors de la creation de session -> creation d'un evenement dans l'agenda Dolibarr
print '<tr class="pair"><td>'.$langs->trans("AgfAgendaModuleUse").'</td>';
print '<td align="left">';
$arrval=array('0'=>$langs->trans("No"),	'1'=>$langs->trans("Yes"));
print $form->selectarray("AGF_DOL_AGENDA",$arrval,$conf->global->AGF_DOL_AGENDA);
print '</td>';
print '<td align="center">';
print '</td>';
print '</tr>';

// utilisation formulaire Ajax sur choix site
print '<tr class="impair">';
print '<td>'.$langs->trans("AgfUseSearchToSelectContact").'</td>';
if (! $conf->use_javascript_ajax)
{
	print '<td nowrap="nowrap" align="right" colspan="2">';
	print $langs->trans("NotAvailableWhenAjaxDisabled");
	print '</td>';
}
else
{
	print '<td align="left">';
	$arrval=array('0'=>$langs->trans("No"),	'1'=>$langs->trans("Yes"));
	print $form->selectarray("AGF_CONTACT_USE_SEARCH_TO_SELECT",$arrval,$conf->global->AGF_CONTACT_USE_SEARCH_TO_SELECT);
	print '</td>';
}
print '<td>&nbsp;</td>';
print '</tr>';

print '<tr class="pair"><td colspan="3" align="right"><input type="submit" class="button" value="'.$langs->trans("Save").'"></td>';
print '</tr>';

print '</table><br>';
print '</form>';

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
		print '<td>'.$formAgefodd->select_action_session_adm($line->fk_parent_level,'parent_level',$line->rowid).'</td>';
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
	print '<td>'.$formAgefodd->select_action_session_adm('','parent_level').'</td>';
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
