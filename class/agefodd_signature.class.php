<?php
/* Copyright (C) 2017  Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2022 Gauthier Verdol <gauthier.verdol@atm-consulting.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file        class/agefoddsignature.class.php
 * \ingroup     forma
 * \brief       This file is a CRUD class file for AgefoddSignature (Create/Read/Update/Delete)
 */

// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';
//require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
//require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';

/**
 * Class for AgefoddSignature
 */
class AgefoddSignature extends CommonObject
{
	/**
	 * @var string ID of module.
	 */
	public $module = 'forma';

	/**
	 * @var string ID to identify managed object.
	 */
	public $element = 'agefoddsignature';

	/**
	 * @var string Name of table without prefix where object is stored. This is also the key used for extrafields management.
	 */
	public $table_element = 'agefodd_session_trainee_path_img_signature_calendrier';

	/**
	 * @var int  Does this object support multicompany module ?
	 * 0=No test on entity, 1=Test with field entity, 'field@table'=Test with link by field@table
	 */
	public $ismultientitymanaged = 0;

	/**
	 * @var int  Does object support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 0;

	/**
	 * @var string String with name of icon for agefoddsignature. Must be a 'fa-xxx' fontawesome code (or 'fa-xxx_fa_color_size') or 'agefoddsignature@forma' if picto is file 'img/object_agefoddsignature.png'.
	 */
	public $picto = 'fa-file';


	/**
	 *  'type' field format:
	 *  	'integer', 'integer:ObjectClass:PathToClass[:AddCreateButtonOrNot[:Filter[:Sortfield]]]',
	 *  	'select' (list of values are in 'options'),
	 *  	'sellist:TableName:LabelFieldName[:KeyFieldName[:KeyFieldParent[:Filter[:Sortfield]]]]',
	 *  	'chkbxlst:...',
	 *  	'varchar(x)',
	 *  	'text', 'text:none', 'html',
	 *   	'double(24,8)', 'real', 'price',
	 *  	'date', 'datetime', 'timestamp', 'duration',
	 *  	'boolean', 'checkbox', 'radio', 'array',
	 *  	'mail', 'phone', 'url', 'password', 'ip'
	 *		Note: Filter can be a string like "(t.ref:like:'SO-%') or (t.date_creation:<:'20160101') or (t.nature:is:NULL)"
	 *  'label' the translation key.
	 *  'picto' is code of a picto to show before value in forms
	 *  'enabled' is a condition when the field must be managed (Example: 1 or '$conf->global->MY_SETUP_PARAM' or '!empty($conf->multicurrency->enabled)' ...)
	 *  'position' is the sort order of field.
	 *  'notnull' is set to 1 if not null in database. Set to -1 if we must set data to null if empty ('' or 0).
	 *  'visible' says if field is visible in list (Examples: 0=Not visible, 1=Visible on list and create/update/view forms, 2=Visible on list only, 3=Visible on create/update/view form only (not list), 4=Visible on list and update/view form only (not create). 5=Visible on list and view only (not create/not update). Using a negative value means field is not shown by default on list but can be selected for viewing)
	 *  'noteditable' says if field is not editable (1 or 0)
	 *  'default' is a default value for creation (can still be overwrote by the Setup of Default Values if field is editable in creation form). Note: If default is set to '(PROV)' and field is 'ref', the default value will be set to '(PROVid)' where id is rowid when a new record is created.
	 *  'index' if we want an index in database.
	 *  'foreignkey'=>'tablename.field' if the field is a foreign key (it is recommanded to name the field fk_...).
	 *  'searchall' is 1 if we want to search in this field when making a search from the quick search button.
	 *  'isameasure' must be set to 1 or 2 if field can be used for measure. Field type must be summable like integer or double(24,8). Use 1 in most cases, or 2 if you don't want to see the column total into list (for example for percentage)
	 *  'css' and 'cssview' and 'csslist' is the CSS style to use on field. 'css' is used in creation and update. 'cssview' is used in view mode. 'csslist' is used for columns in lists. For example: 'css'=>'minwidth300 maxwidth500 widthcentpercentminusx', 'cssview'=>'wordbreak', 'csslist'=>'tdoverflowmax200'
	 *  'help' is a 'TranslationString' to use to show a tooltip on field. You can also use 'TranslationString:keyfortooltiponlick' for a tooltip on click.
	 *  'showoncombobox' if value of the field must be visible into the label of the combobox that list record
	 *  'disabled' is 1 if we want to have the field locked by a 'disabled' attribute. In most cases, this is never set into the definition of $fields into class, but is set dynamically by some part of code.
	 *  'arrayofkeyval' to set a list of values if type is a list of predefined values. For example: array("0"=>"Draft","1"=>"Active","-1"=>"Cancel"). Note that type can be 'integer' or 'varchar'
	 *  'autofocusoncreate' to have field having the focus on a create form. Only 1 field should have this property set to 1.
	 *  'comment' is not used. You can store here any text of your choice. It is not used by application.
	 *	'validate' is 1 if need to validate with $this->validateField()
	 *  'copytoclipboard' is 1 or 2 to allow to add a picto to copy value into clipboard (1=picto after label, 2=picto after value)
	 *
	 *  Note: To have value dynamic, you can set value to 0 in definition and edit the value on the fly into the constructor.
	 */

	// BEGIN MODULEBUILDER PROPERTIES
	/**
	 * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
	 */
	/*
	 * Note Got : Ici le détail des fields (affichage, position etc...) n'a aucune importance car il n'existe aucune card de signature.
	 * J'ai utilisé le module builder pour bénéficier d'une classe dans laquelle j'ai pu mettre mes fonctions spécifiques (Exemple getFormSignatureCreneau())
	 */
	public $fields=array(
		'rowid' => array('type'=>'integer', 'label'=>'TechnicalID', 'enabled'=>'1', 'position'=>1, 'notnull'=>1, 'visible'=>0, 'noteditable'=>'1', 'index'=>1, 'css'=>'left', 'comment'=>"Id"),
		'entity' => array('type'=>'integer', 'label'=>'Entity', 'enabled'=>'1', 'position'=>10, 'notnull'=>1, 'visible'=>0, 'default'=>'1', 'index'=>1,),
		'tms' => array('type'=>'timestamp', 'label'=>'DateModification', 'enabled'=>'1', 'position'=>100, 'notnull'=>0, 'visible'=>-2,),
		'datec' => array('type'=>'datetime', 'label'=>'DateCreation', 'enabled'=>'1', 'position'=>80, 'notnull'=>1, 'visible'=>-2,),
		'fk_person' => array('type'=>'integer', 'label'=>'id participant ou formateur', 'enabled'=>'1', 'position'=>20, 'notnull'=>1, 'visible'=>0,),
		'person_type' => array('type'=>'varchar(7)', 'label'=>'trainee ou trainer', 'enabled'=>'1', 'position'=>30, 'notnull'=>1, 'visible'=>0,),
		'fk_session' => array('type'=>'integer', 'label'=>'ID Session', 'enabled'=>'1', 'position'=>40, 'notnull'=>1, 'visible'=>0,),
		'fk_calendrier' => array('type'=>'integer', 'label'=>'ID Session', 'enabled'=>'1', 'position'=>50, 'notnull'=>1, 'visible'=>0,),
		'ip' => array('type'=>'varchar(255)', 'label'=>'Adresse IP', 'enabled'=>'1', 'position'=>60, 'notnull'=>0, 'visible'=>0,),
		'navigateur' => array('type'=>'varchar(255)', 'label'=>'Navigateur', 'enabled'=>'1', 'position'=>70, 'notnull'=>0, 'visible'=>0,),
		'dates' => array('type'=>'datetime', 'label'=>'Date signature', 'enabled'=>'1', 'position'=>90, 'notnull'=>1, 'visible'=>0,),
		'path' => array('type'=>'varchar(255)', 'label'=>'Lien image', 'enabled'=>'1', 'position'=>110, 'notnull'=>1, 'visible'=>0,),
	);
	public $rowid;
	public $entity;
	public $tms;
	public $datec;
	public $fk_person;
	public $person_type;
	public $fk_session;
	public $fk_calendrier;
	public $ip;
	public $navigateur;
	public $dates;
	public $path;
	// END MODULEBUILDER PROPERTIES


	// If this object has a subtable with lines

	// /**
	//  * @var string    Name of subtable line
	//  */
	// public $table_element_line = 'forma_agefoddsignatureline';

	// /**
	//  * @var string    Field with ID of parent key if this object has a parent
	//  */
	// public $fk_element = 'fk_agefoddsignature';

	// /**
	//  * @var string    Name of subtable class that manage subtable lines
	//  */
	// public $class_element_line = 'AgefoddSignatureline';

	// /**
	//  * @var array	List of child tables. To test if we can delete object.
	//  */
	// protected $childtables = array();

	// /**
	//  * @var array    List of child tables. To know object to delete on cascade.
	//  *               If name matches '@ClassNAme:FilePathClass;ParentFkFieldName' it will
	//  *               call method deleteByParentField(parentId, ParentFkFieldName) to fetch and delete child object
	//  */
	// protected $childtablesoncascade = array('forma_agefoddsignaturedet');

	// /**
	//  * @var AgefoddSignatureLine[]     Array of subtable lines
	//  */
	// public $lines = array();



	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		global $conf, $langs;

		$this->db = $db;

		if (empty($conf->global->MAIN_SHOW_TECHNICAL_ID) && isset($this->fields['rowid']) && !empty($this->fields['ref'])) {
			$this->fields['rowid']['visible'] = 0;
		}
		if (empty($conf->multicompany->enabled) && isset($this->fields['entity'])) {
			$this->fields['entity']['enabled'] = 0;
		}

		// Example to show how to set values of fields definition dynamically
		/*if ($user->rights->forma->agefoddsignature->read) {
			$this->fields['myfield']['visible'] = 1;
			$this->fields['myfield']['noteditable'] = 0;
		}*/

		// Unset fields that are disabled
		foreach ($this->fields as $key => $val) {
			if (isset($val['enabled']) && empty($val['enabled'])) {
				unset($this->fields[$key]);
			}
		}

		// Translate some data of arrayofkeyval
		if (is_object($langs)) {
			foreach ($this->fields as $key => $val) {
				if (!empty($val['arrayofkeyval']) && is_array($val['arrayofkeyval'])) {
					foreach ($val['arrayofkeyval'] as $key2 => $val2) {
						$this->fields[$key]['arrayofkeyval'][$key2] = $langs->trans($val2);
					}
				}
			}
		}
	}

	/**
	 * Create object into database
	 *
	 * @param  User $user      User that creates
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, Id of created object if OK
	 */
	public function create(User $user, $notrigger = false)
	{
		$resultcreate = $this->createCommon($user, $notrigger);

		//$resultvalidate = $this->validate($user, $notrigger);

		return $resultcreate;
	}

	/**
	 * Load object in memory from the database
	 *
	 * @param int    $id   Id object
	 * @param string $ref  Ref
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetch($id, $ref = null)
	{
		$result = $this->fetchCommon($id, $ref);
		if ($result > 0 && !empty($this->table_element_line)) {
			$this->fetchLines();
		}
		return $result;
	}

	/**
	 * Load object lines in memory from the database
	 *
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetchLines()
	{
		$this->lines = array();

		$result = $this->fetchLinesCommon();
		return $result;
	}


	/**
	 * Load list of objects in memory from the database.
	 *
	 * @param  string      $sortorder    Sort Order
	 * @param  string      $sortfield    Sort field
	 * @param  int         $limit        limit
	 * @param  int         $offset       Offset
	 * @param  array       $filter       Filter array. Example array('field'=>'valueforlike', 'customurl'=>...)
	 * @param  string      $filtermode   Filter mode (AND or OR)
	 * @return array|int                 int <0 if KO, array of pages if OK
	 */
	public function fetchAll($sortorder = '', $sortfield = '', $limit = 0, $offset = 0, array $filter = array(), $filtermode = 'AND')
	{
		global $conf;

		dol_syslog(__METHOD__, LOG_DEBUG);

		$records = array();

		$sql = "SELECT ";
		$sql .= $this->getFieldList('t');
		$sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element." as t";
		if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 1) {
			$sql .= " WHERE t.entity IN (".getEntity($this->element).")";
		} else {
			$sql .= " WHERE 1 = 1";
		}
		// Manage filter
		$sqlwhere = array();
		if (count($filter) > 0) {
			foreach ($filter as $key => $value) {
				if ($key == 't.rowid') {
					$sqlwhere[] = $key." = ".((int) $value);
				} elseif (in_array($this->fields[$key]['type'], array('date', 'datetime', 'timestamp'))) {
					$sqlwhere[] = $key." = '".$this->db->idate($value)."'";
				} elseif ($key == 'customsql') {
					$sqlwhere[] = $value;
				} elseif (strpos($value, '%') === false) {
					$sqlwhere[] = $key." IN (".$this->db->sanitize($this->db->escape($value)).")";
				} else {
					$sqlwhere[] = $key." LIKE '%".$this->db->escape($value)."%'";
				}
			}
		}
		if (count($sqlwhere) > 0) {
			$sql .= " AND (".implode(" ".$filtermode." ", $sqlwhere).")";
		}

		if (!empty($sortfield)) {
			$sql .= $this->db->order($sortfield, $sortorder);
		}
		if (!empty($limit)) {
			$sql .= $this->db->plimit($limit, $offset);
		}

		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < ($limit ? min($limit, $num) : $num)) {
				$obj = $this->db->fetch_object($resql);

				$record = new self($this->db);
				$record->setVarsFromFetchObj($obj);

				$records[$record->id] = $record;

				$i++;
			}
			$this->db->free($resql);

			return $records;
		} else {
			$this->errors[] = 'Error '.$this->db->lasterror();
			dol_syslog(__METHOD__.' '.join(',', $this->errors), LOG_ERR);

			return -1;
		}
	}

	/**
	 * Update object into database
	 *
	 * @param  User $user      User that modifies
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function update(User $user, $notrigger = false)
	{
		return $this->updateCommon($user, $notrigger);
	}

	/**
	 * Delete object in database
	 *
	 * @param User $user       User that deletes
	 * @param bool $notrigger  false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function delete(User $user, $notrigger = false)
	{
		global $langs;
		$res = dol_delete_file($this->path);
		if(!$res) {
			$this->error = $langs->trans('ErrorFailToDeleteFile', $this->path);
			return -1;
		}
		return $this->deleteCommon($user, $notrigger);
		//return $this->deleteCommon($user, $notrigger, 1);
	}

	/**
	 *  Return a link to the object card (with optionaly the picto)
	 *
	 *  @param  int     $withpicto                  Include picto in link (0=No picto, 1=Include picto into link, 2=Only picto)
	 *  @param  string  $option                     On what the link point to ('nolink', ...)
	 *  @param  int     $notooltip                  1=Disable tooltip
	 *  @param  string  $morecss                    Add more css on link
	 *  @param  int     $save_lastsearch_value      -1=Auto, 0=No save of lastsearch_values when clicking, 1=Save lastsearch_values whenclicking
	 *  @return	string                              String with URL
	 */
	public function getNomUrl($withpicto = 0, $option = '', $notooltip = 0, $morecss = '', $save_lastsearch_value = -1)
	{
		global $conf, $langs, $hookmanager;

		if (!empty($conf->dol_no_mouse_hover)) {
			$notooltip = 1; // Force disable tooltips
		}

		$result = '';

		$label = img_picto('', $this->picto).' <u>'.$langs->trans("AgefoddSignature").'</u>';
		if (isset($this->status)) {
			$label .= ' '.$this->getLibStatut(5);
		}
		$label .= '<br>';
		$label .= '<b>'.$langs->trans('Ref').':</b> '.$this->ref;

		$url = dol_buildpath('/forma/agefoddsignature_card.php', 1).'?id='.$this->id;

		if ($option != 'nolink') {
			// Add param to save lastsearch_values or not
			$add_save_lastsearch_values = ($save_lastsearch_value == 1 ? 1 : 0);
			if ($save_lastsearch_value == -1 && preg_match('/list\.php/', $_SERVER["PHP_SELF"])) {
				$add_save_lastsearch_values = 1;
			}
			if ($url && $add_save_lastsearch_values) {
				$url .= '&save_lastsearch_values=1';
			}
		}

		$linkclose = '';
		if (empty($notooltip)) {
			if (!empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER)) {
				$label = $langs->trans("ShowAgefoddSignature");
				$linkclose .= ' alt="'.dol_escape_htmltag($label, 1).'"';
			}
			$linkclose .= ' title="'.dol_escape_htmltag($label, 1).'"';
			$linkclose .= ' class="classfortooltip'.($morecss ? ' '.$morecss : '').'"';
		} else {
			$linkclose = ($morecss ? ' class="'.$morecss.'"' : '');
		}

		if ($option == 'nolink' || empty($url)) {
			$linkstart = '<span';
		} else {
			$linkstart = '<a href="'.$url.'"';
		}
		$linkstart .= $linkclose.'>';
		if ($option == 'nolink' || empty($url)) {
			$linkend = '</span>';
		} else {
			$linkend = '</a>';
		}

		$result .= $linkstart;

		if (empty($this->showphoto_on_popup)) {
			if ($withpicto) {
				$result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip ? 0 : 1);
			}
		} else {
			if ($withpicto) {
				require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

				list($class, $module) = explode('@', $this->picto);
				$upload_dir = $conf->$module->multidir_output[$conf->entity]."/$class/".dol_sanitizeFileName($this->ref);
				$filearray = dol_dir_list($upload_dir, "files");
				$filename = $filearray[0]['name'];
				if (!empty($filename)) {
					$pospoint = strpos($filearray[0]['name'], '.');

					$pathtophoto = $class.'/'.$this->ref.'/thumbs/'.substr($filename, 0, $pospoint).'_mini'.substr($filename, $pospoint);
					if (empty($conf->global->{strtoupper($module.'_'.$class).'_FORMATLISTPHOTOSASUSERS'})) {
						$result .= '<div class="floatleft inline-block valignmiddle divphotoref"><div class="photoref"><img class="photo'.$module.'" alt="No photo" border="0" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$module.'&entity='.$conf->entity.'&file='.urlencode($pathtophoto).'"></div></div>';
					} else {
						$result .= '<div class="floatleft inline-block valignmiddle divphotoref"><img class="photouserphoto userphoto" alt="No photo" border="0" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$module.'&entity='.$conf->entity.'&file='.urlencode($pathtophoto).'"></div>';
					}

					$result .= '</div>';
				} else {
					$result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip ? 0 : 1);
				}
			}
		}

		if ($withpicto != 2) {
			$result .= $this->ref;
		}

		$result .= $linkend;
		//if ($withpicto != 2) $result.=(($addlabel && $this->label) ? $sep . dol_trunc($this->label, ($addlabel > 1 ? $addlabel : 0)) : '');

		global $action, $hookmanager;
		$hookmanager->initHooks(array('agefoddsignaturedao'));
		$parameters = array('id'=>$this->id, 'getnomurl' => &$result);
		$reshook = $hookmanager->executeHooks('getNomUrl', $parameters, $this, $action); // Note that $action and $object may have been modified by some hooks
		if ($reshook > 0) {
			$result = $hookmanager->resPrint;
		} else {
			$result .= $hookmanager->resPrint;
		}

		return $result;
	}

	/**
	 * @param $fk_session
	 * @param $fk_calendrier
	 * @param $fk_person
	 * @param $person_type
	 * @return mixed
	 */
	public function getPathToImg($fk_session, $fk_calendrier, $fk_person, $person_type = 'trainee'){

		$res_array = $this->fetchAll('', '', 0, 0, ['customsql' => ' fk_calendrier = '.((int)$fk_calendrier).' AND fk_person = '.((int)$fk_person). ' AND fk_session =  ' . ((int) $fk_session ) . ' AND person_type = "'.$this->db->escape($person_type).'" ']);

		return is_array($res_array)  ? array_values($res_array)[0]->path : 0 ;
	}
	/**
	 *  Return the label of the status
	 *
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return	string 			       Label of status
	 */
	public function getLabelStatus($mode = 0)
	{
		return $this->LibStatut($this->status, $mode);
	}

	/**
	 *  Return the label of the status
	 *
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return	string 			       Label of status
	 */
	public function getLibStatut($mode = 0)
	{
		return $this->LibStatut($this->status, $mode);
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Return the status
	 *
	 *  @param	int		$status        Id status
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return string 			       Label of status
	 */
	public function LibStatut($status, $mode = 0)
	{
		// phpcs:enable
		if (empty($this->labelStatus) || empty($this->labelStatusShort)) {
			global $langs;
			//$langs->load("forma@forma");
			$this->labelStatus[self::STATUS_DRAFT] = $langs->transnoentitiesnoconv('Draft');
			$this->labelStatus[self::STATUS_VALIDATED] = $langs->transnoentitiesnoconv('Enabled');
			$this->labelStatus[self::STATUS_CANCELED] = $langs->transnoentitiesnoconv('Disabled');
			$this->labelStatusShort[self::STATUS_DRAFT] = $langs->transnoentitiesnoconv('Draft');
			$this->labelStatusShort[self::STATUS_VALIDATED] = $langs->transnoentitiesnoconv('Enabled');
			$this->labelStatusShort[self::STATUS_CANCELED] = $langs->transnoentitiesnoconv('Disabled');
		}

		$statusType = 'status'.$status;
		//if ($status == self::STATUS_VALIDATED) $statusType = 'status1';
		if ($status == self::STATUS_CANCELED) {
			$statusType = 'status6';
		}

		return dolGetStatus($this->labelStatus[$status], $this->labelStatusShort[$status], '', $statusType, $mode);
	}

	/**
	 *	Load the info information in the object
	 *
	 *	@param  int		$id       Id of object
	 *	@return	void
	 */
	public function info($id)
	{
		$sql = "SELECT rowid,";
		$sql .= " date_creation as datec, tms as datem,";
		$sql .= " fk_user_creat, fk_user_modif";
		$sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element." as t";
		$sql .= " WHERE t.rowid = ".((int) $id);

		$result = $this->db->query($sql);
		if ($result) {
			if ($this->db->num_rows($result)) {
				$obj = $this->db->fetch_object($result);

				$this->id = $obj->rowid;

				$this->user_creation_id = $obj->fk_user_creat;
				$this->user_modification_id = $obj->fk_user_modif;
				if (!empty($obj->fk_user_valid)) {
					$this->user_validation_id = $obj->fk_user_valid;
				}
				$this->date_creation     = $this->db->jdate($obj->datec);
				$this->date_modification = empty($obj->datem) ? '' : $this->db->jdate($obj->datem);
				if (!empty($obj->datev)) {
					$this->date_validation   = empty($obj->datev) ? '' : $this->db->jdate($obj->datev);
				}
			}

			$this->db->free($result);
		} else {
			dol_print_error($this->db);
		}
	}

	/**
	 * 	Create an array of lines
	 *
	 * 	@return array|int		array of lines if OK, <0 if KO
	 */
	public function getLinesArray()
	{
		$this->lines = array();

		$objectline = new AgefoddSignatureLine($this->db);
		$result = $objectline->fetchAll('ASC', 'position', 0, 0, array('customsql'=>'fk_agefoddsignature = '.((int) $this->id)));

		if (is_numeric($result)) {
			$this->error = $objectline->error;
			$this->errors = $objectline->errors;
			return $result;
		} else {
			$this->lines = $result;
			return $this->lines;
		}
	}

	/**
	 * Action executed by scheduler
	 * CAN BE A CRON TASK. In such a case, parameters come from the schedule job setup field 'Parameters'
	 * Use public function doScheduledJob($param1, $param2, ...) to get parameters
	 *
	 * @return	int			0 if OK, <>0 if KO (this function is used also by cron so only 0 is OK)
	 */
	public function doScheduledJob()
	{
		global $conf, $langs;

		//$conf->global->SYSLOG_FILE = 'DOL_DATA_ROOT/dolibarr_mydedicatedlofile.log';

		$error = 0;
		$this->output = '';
		$this->error = '';

		dol_syslog(__METHOD__, LOG_DEBUG);

		$now = dol_now();

		$this->db->begin();

		// ...

		$this->db->commit();

		return $error;
	}

	/**
	 * Function used to replace a trainee id with another one.
	 * This function is meant to be called from replaceTrainee with the appropiate tables
	 *
	 * @param DoliDB $db        Database handler
	 * @param int    $origin_id Old trainee id (the trainee to delete)
	 * @param int    $dest_id   New trainee id (the trainee that will received element of the other)
	 * @return bool                          True if success, False if error
	 */
	public static function replaceTrainee(DoliDB $db, $origin_id, $dest_id) {
		$sql = 'UPDATE '.MAIN_DB_PREFIX.'agefodd_session_trainee_path_img_signature_calendrier SET fk_person = '.((int) $dest_id).'
		 		WHERE fk_person = '.((int) $origin_id).' AND person_type = "trainee"';

		if(! $db->query($sql)) {
			return false;
		}

		return true;
	}

	/**
	 * Display canvas in a form to create a signature
	 *
	 * @param Array $TCreneauxToSign List of windows to sign
	 * @param Object $session session object
	 * @param int $fk_person ID of person (trainer or trainee)
	 * @param int $person_type type of person (trainer or trainee)
	 * @return    void
	 */
	function getFormSignatureCreneau($TCreneauxToSign, &$session, $fk_person, $person_type) {

		global $conf, $langs;

		$langs->load("agefodd@agefodd");

		// Construction d'une clef de sécurité
		$securekeyseed = isset($conf->global->AGEFODD_ONLINE_SIGNATURE_SECURITY_TOKEN) ? $conf->global->AGEFODD_ONLINE_SIGNATURE_SECURITY_TOKEN : '';

		$SECUREKEY = dol_hash($securekeyseed.$session->id.$fk_person.$person_type.(empty($conf->multicompany->enabled) ? '' : $session->entity), '0');

		print '<div style="width:min(90%,600px);" class="tablepublicpayment center">';
		print '<input type="hidden" id="person_type" value="'.$person_type.'">';
		print '<input type="hidden" id="personid" value="'.$fk_person.'">';
		print '<input type="button" class="buttonDelete small" id="clearsignature" value="'.$langs->trans("ClearSignature").'">';
		print '<div id="signature" style="border:solid;background-color:white;"></div>';
		// Do not use class="reposition" here: It breaks the submit and there is a message on top to say it's ok, so going back top is better.
		print '<input type="button" class="button" id="signbutton" value="'.$langs->trans("SignCalendrier").'">';
		print '<input type="submit" class="button" id="cancel_signature" name="cancel_signature" value="'.$langs->trans("Cancel").'">';

		print '</div>';


		if (!empty(GETPOST('fk_formateur','int'))){
			$participant = '&fk_formateur=';
			$typeparticipant = 'trainer';
		}

		if (!empty(GETPOST('fk_stagiaire','int'))){
			$participant = '&fk_stagiaire=';
			$typeparticipant = 'trainee';
		}

		// Add js code managed into the div #signature
		print '<script language="JavaScript" type="text/javascript" src="'.DOL_URL_ROOT.'/includes/jquery/plugins/jSignature/jSignature.js"></script>
		<script type="text/javascript">
		$(document).ready(function() {
		  $("#signature").jSignature({ lineWidth:4, "decor-color": "transparent", width:"100%", height: 250});

		  $("#signature").on("change",function(){
			$("#clearsignature").css("display","");
			$("#signbutton").attr("disabled",false);
			if(!$._data($("#signbutton")[0], "events")){
				$("#signbutton").on("click",function(){
					var signature = $("#signature").jSignature("getData", "image");
					$.ajax({
						type: "POST",
						url: "'.dol_buildpath('/agefodd/public/ajax/onlineSign.php', 1).'",
						dataType: "text",
						data: {
							"action" : "importSignature",
							"signaturebase64" : signature,
							"securekey" : \''.dol_escape_js($SECUREKEY).'\',
							"entity" : \''.dol_escape_htmltag($conf->entity).'\',
							"fk_session" : \''.dol_escape_htmltag($session->id).'\',
							"TCreneauxToSign" : \''.dol_escape_htmltag(implode(',', $TCreneauxToSign)).'\',
							"fk_person" : \''.dol_escape_htmltag($fk_person).'\',
							"person_type" : \''.dol_escape_htmltag($person_type).'\',
						},
						success: function(response) {
							if(response == "success"){
								console.log("Success on saving signature");

								window.location.replace("'.$_SERVER["PHP_SELF"].'?id='.$session->id.'&personid='.$fk_person.'&ref='.$session->ref.'&securekey='.$SECUREKEY.'&person_type='.$person_type.'");
							}else{
								console.error(response);
							}
						},
					});
				});
			}
		  });

		  $("#clearsignature").on("click",function(){
			$("#signature").jSignature("clear");
			$("#signbutton").attr("disabled",true);
		  });

		  $("#signbutton").attr("disabled",true);
		  $("#cancel_signature").on("click", function() {
				document.location.href="'.$_SERVER['PHP_SELF'].'?id='.$session->id.'&personid='.$fk_person.$participant.$fk_person.'&ref='.$session->ref.'&securekey='.$SECUREKEY.'&person_type='.$typeparticipant.'";
		  });
        });
		</script>';

	}

	/**
	 * Return canvas in a form to create a signature specific external access
	 *
	 * @param Array $TCreneauxToSign List of windows to sign
	 * @param Object $session session object
	 * @param int $fk_person ID of person (trainer or trainee)
	 * @param int $person_type type of person (trainer or trainee)
	 * @return    void
	 */
	function getFormSignatureCreneauForExternalAccess($TCreneauxToSign, &$session, $fk_person, $person_type, $context) {

		global $conf, $langs;

		$langs->load("agefodd@agefodd");

		// Construction d'une clef de sécurité
		$securekeyseed = isset($conf->global->AGEFODD_ONLINE_SIGNATURE_SECURITY_TOKEN) ? $conf->global->AGEFODD_ONLINE_SIGNATURE_SECURITY_TOKEN : '';

		$SECUREKEY = dol_hash($securekeyseed.$session->id.$fk_person.$person_type.(empty($conf->multicompany->enabled) ? '' : $session->entity), '0');

		$out='';

		$out.= '<div style="width:min(90%,600px);margin: 0 auto;top:30%;left:50%;position: absolute;transform: translateY(-50%);transform: translateX(-50%);text-align:center;" class="tablepublicpayment center">';
		$out.= '<input type="button" class="btn btn-primary btn-strong" id="clearsignature" value="'.$langs->trans("ClearSignature").'">';
		$out.= '<div id="signature" style="border:solid;background-color:white;"></div>';
		// Do not use class="reposition" here: It breaks the submit and there is a message on top to say it's ok, so going back top is better.
		$out.= '<input type="button" class="btn btn-secondary btn-strong" id="signbutton" value="'.$langs->trans('SignCalendrierPortail').'">';
		$out.= '<input type="submit" class="btn btn-secondary btn-strong" id="cancel_signature" name="cancel_signature" value="'.$langs->trans("Cancel").'">';
		$out.= '</div>';

		// Add js code managed into the div #signature
		$out.= '<script language="JavaScript" type="text/javascript" src="'.$context->getRootUrl().'vendor/jSignature/jSignature.js"></script>
		<script type="text/javascript">
		$(document).ready(function() {
		  $("#signature").jSignature({ lineWidth:4, "decor-color": "transparent", width:"100%", height:250});

		  $("#signature").on("change",function(){
			$("#clearsignature").css("display","");
			$("#signbutton").attr("disabled",false);
			if(!$._data($("#signbutton")[0], "events")){
				$("#signbutton").on("click",function(){
					var signature = $("#signature").jSignature("getData", "image");
					$.ajax({
						type: "POST",
						url: "'.$context->getRootUrl().'script/interface.php'.'",
						dataType: "text",
						data: {
							"action" : "importSignature",
							"signaturebase64" : signature,
							"securekey" : \''.dol_escape_js($SECUREKEY).'\',
							"entity" : \''.dol_escape_htmltag($conf->entity).'\',
							"fk_session" : \''.dol_escape_htmltag($session->id).'\',
							"TCreneauxToSign" : \''.dol_escape_htmltag(implode(',', $TCreneauxToSign)).'\',
							"fk_person" : \''.dol_escape_htmltag($fk_person).'\',
							"person_type" : \''.dol_escape_htmltag($person_type).'\',
						},
						success: function(response) {
							if(response == "success"){
								console.log("Success on saving signature");
								window.location.replace("'.$context->getRootUrl('agefodd_'.($person_type === 'trainee' ? 'trainee_' : '').'session_card', array('sessid'=>$session->id)).'");
							}else{
								console.error(response);
							}
						},
					});
				});
			}
		  });

		  $("#clearsignature").on("click",function(){
			$("#signature").jSignature("clear");
			$("#signbutton").attr("disabled",true);
		  });

		  $("#signbutton").attr("disabled",true);
		  $("#cancel_signature").on("click", function() {
				$(".signature-dialog").remove();
			});
		  });
		</script>';

		return array('response'=>'success', 'data'=>array('html'=>$out));

	}

	public function createSignatureFile(&$data_base64, $fk_session, $fk_calendrier, $fk_person, $person_type) {

		global $conf;

		$upload_dir = $conf->agefodd->multidir_output[$conf->entity] . "/" . $fk_session . '/creneau-' . $fk_calendrier . '/';

		// Création du répertoire
		$filename = "signature_".$person_type."-".$fk_person.".png";
		if (!is_dir($upload_dir)) {
			if (!dol_mkdir($upload_dir)) {
				$this->errors[] = "Error mkdir. Failed to create dir " . $upload_dir;
				return -1;
			}
		}

		// Ajout du fichier dans le répertoire
		$return = file_put_contents($upload_dir . $filename, $data_base64);
		if ($return == false) {
			$this->errors[] = 'Error file_put_content: failed to create signature file.';
			return -2;
		}

		$this->path = $upload_dir . '/' . $filename;

		return 1;

	}

}
