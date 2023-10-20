<?php

$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	$res = @include ("../../../../main.inc.php"); // For "custom" directory
if (! $res)
	$res = @include ("../../../../../main.inc.php"); // For "custom" directory
if (! $res)
	$res = @include ("../../../../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");

require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
global $db, $user, $mc;

$form = new Form($db);

/*
 * script sharing referent users between entities
 */
if ($user->admin) {
	print '<html>';
	print '<body>';
	print '<a>Share users between entities by adding them into a group user</a><br><br>';
	print '<a>Entity reference : Sharing informations / fk_user group : group id receiving users</a><br><br>';
	print '<form action="" method="post" style="display: table">';
	print '<p style="display: table-row">';
	print '<label style="display: table-cell; padding-right: 10px">Entity reference : </label>';
	print $mc->select_entities($mc->id, 'entityRef', '', false, false, false, false, true);
	print '</p>';
	print '<p style="display: table-row">';
	print '<label style="display: table-cell">User group : </label>';
	print $form->select_dolgroups('', 'fkusergroup', 1);
	print '</p>';
	print '<br>';
	print '<button name="action" value="sendscript">Envoyer</button>';
	print '</form>';
	print '</body>';
	print '</html>';

	$action = GETPOST('action', 'aZ09');
	$entityRef = GETPOST('entityRef', 'int');
	$fkusergroup = GETPOST('fkusergroup', 'int');

	if ($action == 'sendscript') {
		if (empty($action)) {
			setEventMessage('Action is null');
		}

		$db->begin();
		$sql = /** @lang SQL */
			'INSERT IGNORE INTO '.MAIN_DB_PREFIX.'usergroup_user (entity, fk_user, fk_usergroup)'
			. ' 	SELECT e.rowid, u.rowid, '.$fkusergroup
			. ' 	FROM '.MAIN_DB_PREFIX.'entity AS e'
			. ' 	JOIN '.MAIN_DB_PREFIX.'user AS u ON u.rowid IN ('
			. '          SELECT `value`'
			. '          FROM '.MAIN_DB_PREFIX.'const AS const'
			. '          WHERE name IN ("AGF_DEFAULT_MENTOR_ADMIN", "AGF_DEFAULT_MENTOR_PEDAGO", "AGF_DEFAULT_MENTOR_HANDICAP")'
			. '          AND const.entity = '.$entityRef.')'
			. ' 	WHERE e.active = 1 AND e.visible = 1';

		$resql = $db->query($sql);

		if ($resql) {
			$db->commit();
			print '<a>Users succesfully added to your custom mentor group for all entities</a><br><br>';
			print $sql;
		} else {
			$db->rollback();
		}
	}
} else {
	print '<a>Only admin user can use this script</a>';
}

