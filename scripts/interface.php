<?php
if (!defined('NOCSRFCHECK')) define('NOCSRFCHECK', 1);
if (!defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL', 1);

$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");


dol_include_once('/core/lib/functions.lib.php');


global $db;

/*
 * Action
 */
$data = $_POST;
$data['result'] = 0; // by default if no action result is false
$data['errorMsg'] = ''; // default message for errors
$data['msg'] = '';


// do action from GETPOST ...
if(GETPOST('action', 'none'))
{
	$action = GETPOST('action', 'none');

	if($action=='setAgefoddTrainingAdmlevelHierarchy'){
		if (! $user->rights->agefodd->agefodd_formation_catalogue->creer){
			$data['result'] = -1; // by default if no action result is false
			$data['errorMsg'] = $langs->trans("ErrorForbidden"); // default message for errors
		}
		else{

			$data['result'] = _updateAgefoddTrainingAdmlevelHierarchy($data['items'],0, 0, 0, $data['errorMsg']);
			if($data['result']>0){
				$data['msg'] =  $langs->transnoentities('Updated') . ' : ' .  $data['result'];
			}
		}
	}
	if($action=='setAgefoddAdminAdmlevelHierarchy'){
		if (! $user->rights->agefodd->admin && ! $user->admin){
			$data['result'] = -1; // by default if no action result is false
			$data['errorMsg'] = $langs->trans("ErrorForbidden"); // default message for errors
		}
		else{

			$data['result'] = _updateAgefoddSessionAdmlevelHierarchy($data['items'],0, 0, 0, $data['errorMsg']);
			if($data['result']>0){
				$data['msg'] =  $langs->transnoentities('Updated') . ' : ' .  $data['result'];
			}
		}
	}
}

echo json_encode($data);


/**
 * @param $TItem
 * @param int $parent
 * @param int $parentIndice
 * @param int $deep need to be compatible with ancien systeme it level_rank in database
 * @param string $errorMsg
 * @param int $updated
 * @return int
 */
function _updateAgefoddTrainingAdmlevelHierarchy($TItem, $parent = 0, $parentIndice = 0, $deep = 0, &$errorMsg = '', &$updated = 0){
    global $db;

    if(!is_array($TItem)){
        $errorMsg.= 'Error : invalid format'."/n";
        return -1;
    }

    if(empty($TItem)){
        return 0;
    }

    foreach ($TItem as $item){
        if(empty($item['id'])){
            $errorMsg.= 'Error : invalid format id missing : '.$item['id']."/n";
            return -1;
        }

        $item['id'] = str_replace("item_", "", $item['id']);
        if(empty($item['id']) || !is_numeric($item['id'])){
            $errorMsg.= 'Error : invalid format id'."/n";
            return -1;
        }

        $item['id'] = intval($item['id']);

        if(!isset($item['order'])){
            $errorMsg.= 'Error : invalid format order missing'."/n";
            return -1;
        }

        // ok c'est pas top mais il faut que ce soit compatible avec l'ancien systeme bizarre au moins le temps de la transition

        $indice = $parentIndice + intval($item['order']) + 1;
        if(empty($parentIndice)) {
            $indice = (intval($item['order']) + 1) * 100;
        }

        if(!empty($item['children']) && is_array($item['children'])){
            $res = _updateAgefoddTrainingAdmlevelHierarchy($item['children'], $item['id'] , $indice,intval($deep) + 1, $errorMsg, $updated );
            if($res<0){
                return -1;
            }
        }

        // Update request
        $sql = "UPDATE " . MAIN_DB_PREFIX . "agefodd_training_admlevel SET";

        $sql .= " level_rank=" . intval($deep). ",";



        $sql .= " indice=" . intval($indice) . ",";
        $sql .= " fk_parent_level=" . intval($parent);

        $sql .= " WHERE rowid=" . $item['id'];
        $db->begin();
        $resql = $db->query($sql);

        dol_syslog(
            "updateAgefoddTrainingAdmlevelHierarchy '" . $sql
            ,LOG_ERR
        );


        if($resql>0){
            $db->commit();
            $updated++;
        }
        else{
            $errorMsg.= 'Error : update data base'."/n";
            return -1;
            $db->rollback();
        }
    }

    return $updated;
}


/**
 * @param $TItem
 * @param int $parent
 * @param int $parentIndice
 * @param int $deep need to be compatible with ancien systeme it level_rank in database
 * @param string $errorMsg
 * @param int $updated
 * @return int
 */
function _updateAgefoddSessionAdmlevelHierarchy($TItem, $parent = 0, $parentIndice = 0, $deep = 0, &$errorMsg = '', &$updated = 0){
	global $db;

	if(!is_array($TItem)){
		$errorMsg.= 'Error : invalid format'."/n";
		return -1;
	}

	if(empty($TItem)){
		return 0;
	}

	foreach ($TItem as $item){
		if(empty($item['id'])){
			$errorMsg.= 'Error : invalid format id missing : '.$item['id']."/n";
			return -1;
		}

		$item['id'] = str_replace("item_", "", $item['id']);
		if(empty($item['id']) || !is_numeric($item['id'])){
			$errorMsg.= 'Error : invalid format id'."/n";
			return -1;
		}

		$item['id'] = intval($item['id']);

		if(!isset($item['order'])){
			$errorMsg.= 'Error : invalid format order missing'."/n";
			return -1;
		}

		// ok c'est pas top mais il faut que ce soit compatible avec l'ancien systeme bizarre au moins le temps de la transition

		$indice = $parentIndice + intval($item['order']) + 1;
		if(empty($parentIndice)) {
			$indice = (intval($item['order']) + 1) * 100;
		}

		if(!empty($item['children']) && is_array($item['children'])){
			$res = _updateAgefoddSessionAdmlevelHierarchy($item['children'], $item['id'] , $indice,intval($deep) + 1, $errorMsg, $updated );
			if($res<0){
				return -1;
			}
		}

		// Update request
		$sql = "UPDATE " . MAIN_DB_PREFIX . "agefodd_session_admlevel SET";

		$sql .= " level_rank=" . intval($deep). ",";



		$sql .= " indice=" . intval($indice) . ",";
		$sql .= " fk_parent_level=" . intval($parent);

		$sql .= " WHERE rowid=" . $item['id'];
		$db->begin();
		$resql = $db->query($sql);

		dol_syslog(
			"updateAgefoddAdminAdmlevelHierarchy '" . $sql
			,LOG_ERR
		);


		if($resql>0){
			$db->commit();
			$updated++;
		}
		else{
			$errorMsg.= 'Error : update data base'."/n";
			return -1;
			$db->rollback();
		}
	}

	return $updated;
}
