<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$res = @include ("../../main.inc.php"); // For root directory
if (!$res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (!$res)
	die("Include of main fails");

require_once DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php";
require_once DOL_DOCUMENT_ROOT."/core/lib/images.lib.php";
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once '../class/html.formagefodd.class.php';
require_once '../lib/agefodd.lib.php';

$langs->load("admin");
$langs->load('agefodd@agefodd');

$newToken = function_exists('newToken') ? newToken() : $_SESSION['newtoken'];

$action = GETPOST("action", 'none');
if ($action == 'save_multicompany_shared_conf')
{
	$multicompanypriceshare = GETPOST('multicompany-agefodd', 'array');
	$dao = new DaoMulticompany($db);
	$dao->getEntities();

	foreach ($dao->entities as $entity)
	{
		$entity->options['sharings']['agefodd'] = array();
		$entity->update($entity->id, $user);
	}

	if (!empty($multicompanypriceshare))
	{

		foreach ($multicompanypriceshare as $entityId => $shared)
		{

			//'MULTICOMPANY_'.strtoupper($element).'_SHARING_ENABLED
			if (is_array($shared))
			{
				$shared = array_map('intval', $shared);


				if ($dao->fetch($entityId) > 0)
				{
					$dao->options['sharings']['agefodd'] = $shared;
					if ($dao->update($entityId, $user) < 1)
					{
						setEventMessage('Error');
					}
				}
			}
		}
	}
}

/*
 * Paramètre de getEntity disponible grâce à ces valeurs :
 * - &element=agefodd_base (recueil formation, lieu, formateur)
 * - &element=agefodd_session (session, participants)
 */
if($action == 'display_agefodd_conf_on_multicompany_conf') {
    global $db;

    $nameOfObject = GETPOST('element', 'alpha');
	$agefoddBase = '';

	if ($nameOfObject == 'agefodd_session' && $conf->global->MULTICOMPANY_AGSESSION_SHARING_ENABLED) {
		$agefoddBase = 'agefodd_base';
		dolibarr_set_const($db, 'MULTICOMPANY_AGEFODD_BASE_SHARING_ENABLED', 1, 'chaine', 1, '', 0);
	}
    if($conf->global->MULTICOMPANY_EXTERNAL_MODULES_SHARING) {
        $TMulticompanySharingAgefodd = json_decode($conf->global->MULTICOMPANY_EXTERNAL_MODULES_SHARING);

        $TMulticompanySharingAgefodd[0]->sharingelements->$nameOfObject = [];
        $TMulticompanySharingAgefodd[0]->sharingmodulename->$nameOfObject = 'agefodd';

		if ($agefoddBase) {
			$TMulticompanySharingAgefodd[0]->sharingelements->$agefoddBase = [];
			$TMulticompanySharingAgefodd[0]->sharingmodulename->$agefoddBase = 'agefodd';
		}
    }
    else {
        $TMulticompanySharingAgefodd = [];
        $TMulticompanySharingAgefodd[0] = new stdClass();

        $TMulticompanySharingAgefodd[0]->sharingelements = new stdClass();
        $TMulticompanySharingAgefodd[0]->sharingelements->$nameOfObject = [];

        $TMulticompanySharingAgefodd[0]->sharingmodulename = new stdClass();
        $TMulticompanySharingAgefodd[0]->sharingmodulename->$nameOfObject = 'agefodd';

		if ($agefoddBase) {
			$TMulticompanySharingAgefodd = [];
			$TMulticompanySharingAgefodd[0] = new stdClass();

			$TMulticompanySharingAgefodd[0]->sharingelements = new stdClass();
			$TMulticompanySharingAgefodd[0]->sharingelements->$agefoddBase = [];

			$TMulticompanySharingAgefodd[0]->sharingmodulename = new stdClass();
			$TMulticompanySharingAgefodd[0]->sharingmodulename->$agefoddBase = 'agefodd';
		}
    }
    $conf->global->MULTICOMPANY_EXTERNAL_MODULES_SHARING = json_encode($TMulticompanySharingAgefodd);

    dolibarr_set_const($db, 'MULTICOMPANY_EXTERNAL_MODULES_SHARING', $conf->global->MULTICOMPANY_EXTERNAL_MODULES_SHARING, 'chaine', 1, '', 0);
}



$extrajs = $extracss = array();
if (!empty($conf->multicompany->enabled) && !empty($conf->global->MULTICOMPANY_SHARINGS_ENABLED))
{
	$extrajs = array(
		'/multicompany/inc/multiselect/js/ui.multiselect.js',
	);
	$extracss = array(
		'/multicompany/inc/multiselect/css/ui.multiselect.css',
	);
}

llxHeader('', $langs->trans('AgefoddSetupMulticompany'), '', '', '', '', $extrajs, $extracss);



$linkback = '<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';
print_fiche_titre($langs->trans("AgefoddSetupMulticompany"), $linkback, 'setup');

// Configuration header
$head = agefodd_admin_prepare_head();
dol_fiche_head($head, 'multicompany', $langs->trans("Module103000Name"), -2, "agefodd@agefodd");



if (!empty($conf->multicompany->enabled) && !empty($conf->global->MULTICOMPANY_SHARINGS_ENABLED))
{

	print '<br><br>';

	//var_dump($mc);
	print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
	print '<input type="hidden" name="token" value="'.$newToken.'">';
	print '<input type="hidden" name="action" value="save_multicompany_shared_conf">';
	print '<input type="hidden" name="action" value="display_agefodd_conf_on_multicompany_conf">';

	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre">';
	print '<td>'.$langs->trans("Multicompany").'</td>'."\n";
	print '<td align="center" ></td>';
	print '</tr>';

    // Activer le partage des formations (recueil, lieux et formateurs) entre entités
	$element = 'agefodd_base';
	$moduleSharingEnabled = 'MULTICOMPANY_'.strtoupper($element).'_SHARING_ENABLED';

	print '<tr class="oddeven" >';
	print '<td align="left" >';
	print $langs->trans("ActivateSharing");
	print '</td>';
	print '<td align="center" >';
	print ajax_constantonoff($moduleSharingEnabled, array(), 0);
    print '<a href="?action=display_agefodd_conf_on_multicompany_conf&element=agefodd_base"><i class="fas fa-sync" title="'.$langs->trans("ActivateSharing").'"></i></a>';
    print $form->textwithpicto('', $langs->trans("HelperMulticompanyAgefodd"), 1, 'help');
    print '</td>';
	print '</tr>';

    // Activer le partage des sessions entre entités
    $element = 'agefodd_session';
    $moduleSharingEnabled = 'MULTICOMPANY_'.strtoupper($element).'_SHARING_ENABLED';

    print '<tr class="oddeven" >';
    print '<td align="left" >';
    print $langs->trans("ActivateSharingSession");
    print '</td>';
    print '<td align="center" >';
    print ajax_constantonoff($moduleSharingEnabled, array(), 0);
    print '<a href="?action=display_agefodd_conf_on_multicompany_conf&element=agefodd_session"><i class="fas fa-sync" title="'.$langs->trans("ActivateSharingSession").'"></i></a>';
    print $form->textwithpicto('', $langs->trans("HelperMulticompanyAgefodd"), 1, 'help');
    print '</td>';
    print '</tr>';

	$m = new ActionsMulticompany($db);

	$dao = new DaoMulticompany($db);
	$dao->getEntities();

	/*
	 * Renseigner le tableau Tconfs avec le nom des confs AGEFODD qui ne commencent pas par AGF_
	 * → supprime toutes les confs agefodd qui ne sont pas sur l'entité principale
	 */
	$mainEntity = $dao->entities[0]->id;
	if (!empty($conf->global->MULTICOMPANY_BACKWARD_COMPATIBILITY) && $conf->entity == $mainEntity) {
		$Tconfs = ['RELATION_LINK_SELECTED_ON_THRIDPARTY_TRAINING_SESSION'];
		foreach ($Tconfs as &$localConf) {
			$localConf = '"'.$localConf.'"';
		}
		unset($localConf);
		$sql = "DELETE FROM ".MAIN_DB_PREFIX."const WHERE entity > 1";
		$sql.=" AND (name LIKE 'AGF_%'";
		$sql.=" OR name IN (".implode(',' ,$Tconfs)."))";
		$db->query($sql);
	}
}


llxFooter();

/**
 * 	Return multiselect list of entities.
 *
 * 	@param	string	$htmlname	Name of select
 * 	@param	DaoMulticompany	$current	Current entity to manage
 * 	@param	string	$option		Option
 * 	@return	string
 */
function _multiselect_entities($htmlname, $current, $option = '', $sharingElement = '')
{
	global $conf, $langs, $db;

	$dao = new DaoMulticompany($db);
	$dao->getEntities();

	$sharingElement = !empty($sharingElement) ? $sharingElement : $htmlname;

	$return = '<select id="'.$htmlname.'" class="multiselect" multiple="multiple" name="'.$htmlname.'[]" '.$option.'>';
	if (is_array($dao->entities))
	{
		foreach ($dao->entities as $entity)
		{
			if (is_object($current) && $current->id != $entity->id && $entity->active == 1)
			{

				$return .= '<option value="'.$entity->id.'" ';
				if (is_array($current->options['sharings'][$sharingElement]) && in_array($entity->id, $current->options['sharings'][$sharingElement]))
				{
					$return .= 'selected="selected"';
				}
				$return .= '>';
				$return .= $entity->label;
				if (empty($entity->visible))
				{
					$return .= ' ('.$langs->trans('Hidden').')';
				}
				$return .= '</option>';
			}
		}
	}
	$return .= '</select>';

	return $return;
}
