<?php
/* Copyright (C) 2007-2008	Laurent Destailleur	<eldy@users.sourceforge.net>
 * Copyright (C) 2009-2010	Erick Bullier		<eb.dev@ebiconsulting.fr>
 * Copyright (C) 2012-2014		Florian Henry			<florian.henry@open-concept.pro>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file agefodd/class/agefodd_session_catalogue.class.php
 * \ingroup agefodd
 * \brief Manage training object
 */
require_once DOL_DOCUMENT_ROOT . "/core/class/commonobject.class.php";


class SessionCatalogue extends Formation
{
	public $error;
	public $errors = array ();
	public $element = 'agefodd_session_catalogue';
	public $table_element = 'agefodd_session_catalogue';
	public $ismultientitymanaged = 1; // 0=No test on entity, 1=Test with field entity, 2=Test with link by societe


	/**
	 * Constructor
	 *
	 * @param DoliDb $db handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
		return 1;
	}

	/**
	 * Create object into database
	 *
	 * @param User $user that create
	 * @param int $notrigger triggers after, 1=disable triggers
	 * @return int <0 if KO, Id of created object if OK
	 */
	public function create($user, $notrigger = 0)
	{
		global $conf, $langs;
		$error = 0;

		// Clean parameters
		if (isset($this->intitule))
			$this->intitule = $this->db->escape(trim($this->intitule));
		if (isset($this->public))
			$this->public = $this->db->escape(trim($this->public));
		if (isset($this->methode))
			$this->methode = $this->db->escape(trim($this->methode));
		if (isset($this->prerequis))
			$this->prerequis = $this->db->escape(trim($this->prerequis));
		if (isset($this->but))
			$this->but = $this->db->escape(trim($this->but));
		if (isset($this->note1))
			$this->note1 = $this->db->escape(trim($this->note1));
		if (isset($this->note2))
			$this->note2 = $this->db->escape(trim($this->note2));
		if (isset($this->programme))
			$this->programme = $this->db->escape(trim($this->programme));
		if (isset($this->pedago_usage))
			$this->pedago_usage = $this->db->escape(trim($this->pedago_usage));
		if (isset($this->sanction))
			$this->sanction = $this->db->escape(trim($this->sanction));
		if (isset($this->certif_duration))
			$this->certif_duration = $this->db->escape(trim($this->certif_duration));
		if (isset($this->ref_interne))
			$this->ref_interne = $this->db->escape(trim($this->ref_interne));
		if (isset($this->qr_code_info))
			$this->qr_code_info = $this->db->escape(trim($this->qr_code_info));

		if (empty($this->duree))
			$this->duree = 0;

		if ($this->fk_c_category == - 1)
			$this->fk_c_category = 0;

		if ($this->fk_c_category_bpf == - 1)
			$this->fk_c_category_bpf = 0;



		// Insert request
		$sql = "INSERT INTO " . MAIN_DB_PREFIX . "agefodd_session_catalogue(";
		$sql .= "datec,ref, ref_interne,intitule, duree, nb_place, public, methode, prerequis, but,";
		$sql .= "programme, note1, note2, fk_user_author,fk_user_mod,entity,";
		$sql .= "fk_product,nb_subscribe_min,fk_c_category,certif_duration";
		$sql .= ",pedago_usage";
		$sql .= ",sanction";
		$sql .= ",qr_code_info";
		$sql .= ",fk_c_category_bpf";
		$sql .= ",tms";
		$sql .= ",accessibility_handicap";
		$sql .= ",fk_nature_action_code";
		$sql .= ",fk_session";
		$sql .= ") VALUES (";
		$sql .= "'" . $this->db->idate(dol_now()) . "', ";
		$sql .= " " . (! isset($this->ref_obj) ? 'NULL' : "'" . $this->ref_obj . "'") . ",";
		$sql .= " " . (! isset($this->ref_interne) ? 'NULL' : "'" . $this->ref_interne . "'") . ",";
		$sql .= " " . (! isset($this->intitule) ? 'NULL' : "'" . $this->intitule . "'") . ",";
		$sql .= " " . (! isset($this->duree) ? 'NULL' : $this->duree) . ",";
		$sql .= " " . (empty($this->nb_place) ? 'NULL' : $this->nb_place) . ",";
		$sql .= " " . (! isset($this->public) ? 'NULL' : "'" . $this->public . "'") . ",";
		$sql .= " " . (! isset($this->methode) ? 'NULL' : "'" . $this->methode . "'") . ",";
		$sql .= " " . (! isset($this->prerequis) ? 'NULL' : "'" . $this->prerequis . "'") . ",";
		$sql .= " " . (! isset($this->but) ? 'NULL' : "'" . $this->but . "'") . ",";
		$sql .= " " . (! isset($this->programme) ? 'NULL' : "'" . $this->programme . "'") . ",";
		$sql .= " " . (! isset($this->note1) ? 'NULL' : "'" . $this->note1 . "'") . ",";
		$sql .= " " . (! isset($this->note2) ? 'NULL' : "'" . $this->note2 . "'") . ",";
		$sql .= " " . $user->id . ',';
		$sql .= " " . $user->id . ',';
		$sql .= " " . $conf->entity . ', ';
		$sql .= " " . (empty($this->fk_product) ? 'null' : $this->fk_product) . ', ';
		$sql .= " " . (empty($this->nb_subscribe_min) ? "null" : $this->nb_subscribe_min) . ', ';
		$sql .= " " . (empty($this->fk_c_category) ? "null" : $this->fk_c_category) . ', ';
		$sql .= " " . (empty($this->certif_duration) ? "null" : "'" . $this->certif_duration . "'") . ', ';
		$sql .= " " . (empty($this->pedago_usage) ? "null" : "'" . $this->pedago_usage . "'") . ', ';
		$sql .= " " . (empty($this->sanction) ? "null" : "'" . $this->sanction . "'") . ', ';
		$sql .= " " . (empty($this->qr_code_info) ? "null" : "'" . $this->qr_code_info . "'") . ', ';
		$sql .= " " . (empty($this->fk_c_category_bpf) ? "null" : $this->fk_c_category_bpf) . ', ';
		$sql .= " '".$this->db->idate(time())."' ,";
		$sql .= " " . (empty($this->accessibility_handicap) ? 0 : $this->accessibility_handicap) . ',' ;
		$sql .= " " . (empty($this->fk_nature_action_code) ? "null" : $this->fk_nature_action_code). ',' ;
		$sql .= " " . (empty($this->fk_session) ? "null" : $this->fk_session) ;
		$sql .= ")";
		$this->db->begin();
		dol_syslog(get_class($this) . "::create ", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (! $resql) {
			$error ++;
			$this->errors[] = "Error " . $this->db->lasterror();
		}
		if (! $error) {
			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX . "agefodd_session_catalogue");
			if (! $notrigger) {
				// Uncomment this and change MYOBJECT to your own tag if you
				// want this action call a trigger.

				// // Call triggers
				// include_once(DOL_DOCUMENT_ROOT . "/interfaces.class.php");
				// $interface=new Interfaces($this->db);
				// $result=$interface->run_triggers('MYOBJECT_CREATE',$this,$user,$langs,$conf);
				// if ($result < 0) { $error++; $this->errors=$interface->errors; }
				// // End call triggers
			}
		}
		// For avoid conflicts if trigger used
		if (empty($conf->global->MAIN_EXTRAFIELDS_DISABLED)) {
			$result = $this->insertExtraFields();
			if ($result < 0) {
				$error ++;
			}
		}

		if (! $error && ! $notrigger) {
			// Call trigger
			$result=$this->call_trigger('AGEFODD_SESSION_CATALOGUE_CREATE', $user);
			if ($result < 0) { $error++; }
			// End call triggers
		}

		// Commit or rollback
		if ($error) {
			foreach ($this->errors as $errmsg) {
				dol_syslog(get_class($this) . "::create " . $errmsg, LOG_ERR);
				$this->error .= ($this->error ? ', ' . $errmsg : $errmsg);
			}
			$this->db->rollback();
			return - 1 * $error;
		} else {
			$this->db->commit();
			return $this->id;
		}
	}

	/**
	 * Load object in memory from database
	 *
	 * @param int $id object
	 * @param string $ref useless in this case but necessary because of heritage
	 * @return int <0 if KO, >0 if OK
	 */
	public function fetch($id, $ref = '')
	{
		$sql = "SELECT";
		$sql .= " sc.rowid, sc.entity, sc.ref, sc.ref_interne, sc.intitule, sc.duree, sc.nb_place,";
		$sql .= " sc.public, sc.methode, sc.prerequis, sc.but, sc.programme, sc.archive, sc.note1, sc.note2 ";
		$sql .= " ,sc.note_private, sc.note_public, sc.fk_product,sc.nb_subscribe_min,sc.fk_c_category";
		$sql .= " ,sc.certif_duration";
		$sql .= " ,sc.pedago_usage";
		$sql .= " ,sc.sanction";
		$sql .= " ,sc.color";
		$sql .= " ,sc.qr_code_info";
		$sql .= " ,sc.fk_c_category_bpf";
		$sql .= " ,sc.accessibility_handicap";
		$sql .= " ,sc.fk_nature_action_code";
		$sql .= " ,sc.fk_session";
		$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session_catalogue as sc";
		$sql .= " WHERE sc.rowid = " . $id;
		$sql .= " AND sc.entity IN (" . getEntity('agefodd'/*agsession*/) . ")";

		dol_syslog(get_class($this) . "::fetch ", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				$this->id = $obj->rowid;
				// I know twice affactation...
				$this->rowid = $obj->rowid;
				// use for next prev ref
				$this->ref = $obj->rowid;
				// use for next prev ref
				$this->ref_obj = $obj->ref;
				$this->entity = $obj->entity;
				$this->ref_interne = $obj->ref_interne;
				$this->intitule = stripslashes($obj->intitule);
				$this->duree = $obj->duree;
				$this->nb_place = $obj->nb_place;
				$this->public = stripslashes($obj->public);
				$this->methode = stripslashes($obj->methode);
				$this->prerequis = stripslashes($obj->prerequis);
				$this->but = stripslashes($obj->but);
				$this->programme = stripslashes($obj->programme);
				$this->note1 = stripslashes($obj->note1);
				$this->note2 = stripslashes($obj->note2);
				$this->archive = $obj->archive;
				$this->note_private = $obj->note_private;
				$this->note_public = $obj->note_public;
				$this->fk_product = $obj->fk_product;
				$this->nb_subscribe_min = $obj->nb_subscribe_min;
				$this->fk_c_category = $obj->fk_c_category;
				if (! empty($obj->catcode) || ! empty($obj->catlib)) {
					$this->category_lib = $obj->catcode . ' - ' . $obj->catlib;
				}
				$this->fk_c_category_bpf = $obj->fk_c_category_bpf;
				if (! empty($obj->catcodebpf) || ! empty($obj->catlibbpf)) {
					$this->category_lib_bpf = $obj->catcodebpf. ' - ' . $obj->catlibbpf;
				}
				$this->certif_duration = $obj->certif_duration;
				$this->pedago_usage = $obj->pedago_usage;
				$this->sanction = $obj->sanction;
				$this->color = $obj->color;
				$this->qr_code_info = $obj->qr_code_info;
				$this->accessibility_handicap = $obj->accessibility_handicap;
				$this->fk_nature_action_code = $obj->fk_nature_action_code;
				$this->fk_session = $obj->fk_session;
				require_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';
				$extrafields = new ExtraFields($this->db);
				$extralabels = $extrafields->fetch_name_optionals_label($this->table_element, true);
				if (count($extralabels) > 0) {
					$this->fetch_optionals($this->id, $extralabels);
				}
			}
			$this->db->free($resql);

			return $this->id;
		} else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::fetch " . $this->error, LOG_ERR);
			return - 1;
		}
	}

	/**
	 * Give information on the object
	 *
	 * @param int $id object
	 * @return int <0 if KO, >0 if OK
	 */
	public function info($id)
	{
		$sql = "SELECT";
		$sql .= " c.rowid, c.entity, c.datec, c.tms, c.fk_user_author, c.fk_user_mod ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session_catalogue as c";
		$sql .= " WHERE c.rowid = " . $id;

		dol_syslog(get_class($this) . "::fetch ", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				$this->id = $obj->rowid;
				$this->entity = $obj->entity;
				$this->date_creation = $this->db->jdate($obj->datec);
				$this->date_modification = $this->db->jdate($obj->tms);
				$this->user_creation = $obj->fk_user_author;
				$this->user_modification = $obj->fk_user_mod;
			}
			$this->db->free($resql);

			return 1;
		} else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::fetch " . $this->error, LOG_ERR);
			return - 1;
		}
	}

	/**
	 * Update object into database
	 *
	 * @param User $user that modify
	 * @param int $notrigger triggers after, 1=disable triggers
	 * @return int <0 if KO, >0 if OK
	 */
	public function update($user, $notrigger = 0)
	{
		global $conf, $langs;
		$error = 0;

		// Clean parameters
		$this->intitule = $this->db->escape(trim($this->intitule));
		$this->ref_obj = $this->db->escape(trim($this->ref_obj));
		$this->ref_interne = $this->db->escape(trim($this->ref_interne));
		$this->public = $this->db->escape(trim($this->public));
		$this->methode = $this->db->escape(trim($this->methode));
		$this->prerequis = $this->db->escape(trim($this->prerequis));
		$this->but = $this->db->escape(trim($this->but));
		$this->programme = $this->db->escape(trim($this->programme));
		$this->pedago_usage = $this->db->escape(trim($this->pedago_usage));
		$this->sanction = $this->db->escape(trim($this->sanction));
		$this->note1 = $this->db->escape(trim($this->note1));
		$this->note2 = $this->db->escape(trim($this->note2));
		$this->certif_duration = $this->db->escape(trim($this->certif_duration));

		$this->fk_nature_action_code = $this->fk_nature_action_code;
		$this->fk_session = $this->fk_session;
		if (isset($this->color)) {
			$this->color = trim($this->color);
		}
		if (isset($this->qr_code_info)) {
			$this->qr_code_info = trim($this->qr_code_info);
		}
		if ($this->fk_c_category == - 1) {
			$this->fk_c_category = 0;
		}
		if ($this->fk_c_category_bpf == - 1) {
			$this->fk_c_category_bpf= 0;
		}

		$this->fk_c_category = $this->db->escape(trim($this->fk_c_category));
		$this->fk_c_category_bpf= $this->db->escape(trim($this->fk_c_category_bpf));

		// Check parameters
		// Put here code to add control on parameters values
		if (empty($this->duree))
			$this->duree = 0;

		// Update request
		if (! isset($this->archive))
			$this->archive = 0;
		$sql = "UPDATE " . MAIN_DB_PREFIX . "agefodd_session_catalogue SET";
		$sql .= " ref=" . (isset($this->ref_obj) ? "'" . $this->ref_obj . "'" : "null") . ",";
		$sql .= " ref_interne=" . (isset($this->ref_interne) ? "'" . $this->ref_interne . "'" : "null") . ",";
		$sql .= " intitule=" . (isset($this->intitule) ? "'" . $this->intitule . "'" : "null") . ",";
		$sql .= " duree=" . (isset($this->duree) ? price2num($this->duree) : "null") . ",";
		$sql .= " public=" . (isset($this->public) ? "'" . $this->public . "'" : "null") . ",";
		$sql .= " methode=" . (isset($this->methode) ? "'" . $this->methode . "'" : "null") . ",";
		$sql .= " prerequis=" . (isset($this->prerequis) ? "'" . $this->prerequis . "'" : "null") . ",";
		$sql .= " but=" . (isset($this->but) ? "'" . $this->but . "'" : "null") . ",";
		$sql .= " programme=" . (isset($this->programme) ? "'" . $this->programme . "'" : "null") . ",";
		$sql .= " pedago_usage=" . (isset($this->pedago_usage) ? "'" . $this->pedago_usage . "'" : "null") . ",";
		$sql .= " sanction=" . (isset($this->sanction) ? "'" . $this->sanction . "'" : "null") . ",";
		$sql .= " note1=" . (isset($this->note1) ? "'" . $this->note1 . "'" : "null") . ",";
		$sql .= " note2=" . (isset($this->note2) ? "'" . $this->note2 . "'" : "null") . ",";
		$sql .= " fk_user_mod=" . $user->id . ",";
		$sql .= " certif_duration=" . (! empty($this->certif_duration) ? "'" . $this->certif_duration . "'" : "null") . ",";
		$sql .= " qr_code_info=" . (! empty($this->qr_code_info) ? "'" . $this->qr_code_info . "'" : "null")  . ",";
		$sql .= " accessibility_handicap =" . (!empty($this->accessibility_handicap) ?   $this->accessibility_handicap  : "0"). "";

		$sql .= " WHERE fk_session = " . (! empty($this->fk_session) ? "" . $this->fk_session . "" : "null"). "";

		$this->db->begin();

		dol_syslog(get_class($this) . "::update ", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (! $resql) {
			$error ++;
			$this->errors[] = "Error " . $this->db->lasterror();
		}

		// For avoid conflicts if trigger used
		if (empty($conf->global->MAIN_EXTRAFIELDS_DISABLED)) {
			$result = $this->insertExtraFields();
			if ($result < 0) {
				$error ++;
			}
		}

		if (! $error && ! $notrigger) {
			// Call trigger
			$result=$this->call_trigger('AGEFODD_SESSION_CATALOGUE_UPDATE', $user);
			if ($result < 0) { $error++; }
			// End call triggers
		}

		// Commit or rollback
		if ($error) {
			foreach ($this->errors as $errmsg) {
				dol_syslog(get_class($this) . "::update " . $errmsg, LOG_ERR);
				$this->error .= ($this->error ? ', ' . $errmsg : $errmsg);
			}
			$this->db->rollback();
			return - 1 * $error;
		} else {
			$this->db->commit();
			return 1;
		}
	}

	/**
	 * Delete object in database
	 *
	 * @param int $id to delete
	 * @param int $notrigger triggers after, 1=disable triggers
	 * @return int if KO, >0 if OK
	 */
	public function remove($id, $notrigger = 0)
	{
		global $conf, $user;

		$error = 0;

		$sql = "DELETE FROM " . MAIN_DB_PREFIX . "agefodd_session_catalogue";
		$sql .= " WHERE rowid = " . intval($id);

		dol_syslog(get_class($this) . "::remove ", LOG_DEBUG);
		$resql = $this->db->query($sql);

		if (! $resql) {
			$error ++;
			$this->errors[] = "Error " . $this->db->lasterror();
		}

		// Removed extrafields
		if (! $error) {
			// For avoid conflicts if trigger used
			if (empty($conf->global->MAIN_EXTRAFIELDS_DISABLED)) {
				$this->id = $id;
				$result = $this->deleteExtraFields();
				if ($result < 0) {
					$error ++;
					dol_syslog(get_class($this) . "::delete erreur " . $error . " " . $this->error, LOG_ERR);
				}
			}
		}

		if (! $error && ! $notrigger) {
			// Call trigger
			$result=$this->call_trigger('AGEFODD_SESSION_CATALOGUE_DELETE', $user);
			if ($result < 0) { $error++; }
			// End call triggers
		}

		if (! $error) {
			return 1;
		} else {
			$this->error = $this->db->lasterror();
			return - 1;
		}
	}

	/**
	 * Create pegagogic goal
	 *
	 * @param User $user that creates
	 * @param int $notrigger triggers after, 1=disable triggers
	 * @return int <0 if KO, >0 if OK
	 */
	public function create_objpeda($user, $notrigger = 0)
	{
		global $conf, $langs;
		$error = 0;

		// Clean parameters
		$this->intitule = $this->db->escape($this->intitule);
		$this->db->begin();

		// Check parameters
		// Put here code to add control on parameters value

		// Insert request
		$sql = "INSERT INTO " . MAIN_DB_PREFIX . "agefodd_session_catalogue_objectifs_peda(";
		$sql .= "fk_session_catalogue, intitule, priorite, fk_user_author,fk_user_mod,datec";
		$sql .= ") VALUES (";
		$sql .= " " . $this->id . ', ';
		$sql .= "'" . $this->db->escape($this->intitule) . "', ";
		$sql .= " " . intval($this->priorite) . ", ";
		$sql .= " " . $user->id . ',';
		$sql .= " " . $user->id . ',';
		$sql .= "'" . $this->db->idate(dol_now()) . "'";
		$sql .= ")";


		dol_syslog(get_class($this) . "::create ", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (! $resql) {
			$error ++;
			$this->errors[] = "Error " . $this->db->lasterror();
		}
		if (! $error) {
			if (! $notrigger) {
				// Uncomment this and change MYOBJECT to your own tag if you
				// want this action call a trigger.

				// // Call triggers
				// include_once(DOL_DOCUMENT_ROOT . "/interfaces.class.php");
				// $interface=new Interfaces($this->db);
				// $result=$interface->run_triggers('MYOBJECT_CREATE',$this,$user,$langs,$conf);
				// if ($result < 0) { $error++; $this->errors=$interface->errors; }
				// // End call triggers
			}
		}

		// Commit or rollback
		if ($error) {
			foreach ($this->errors as $errmsg) {
				dol_syslog(get_class($this) . "::create " . $errmsg, LOG_ERR);
				$this->error .= ($this->error ? ', ' . $errmsg : $errmsg);
			}
			$this->db->rollback();
			return - 1 * $error;
		} else {
			$this->db->commit();
			return $this->id;
		}
	}

	/**
	 * Load object in memory from database
	 *
	 * @param int $id of object
	 * @return int >0 if OK, 0 if not found, <0 if KO
	 */
	public function fetch_objpeda($id)
	{
		$sql = "SELECT";
		$sql .= " o.intitule, o.priorite, o.fk_formation_catalogue";
		$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session_catalogue_objectifs_peda";
		$sql .= " as o";
		$sql .= " WHERE o.rowid = " . intval($id);

		dol_syslog(get_class($this) . "::fetch ", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				$this->id = $id;
				$this->fk_formation_catalogue = $obj->fk_formation_catalogue;
				$this->intitule = stripslashes($obj->intitule);
				$this->priorite = $obj->priorite;
			} else return 0;
			$this->db->free($resql);

			return 1;
		} else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::fetch " . $this->error, LOG_ERR);
			return - 1;
		}
	}

	/**
	 * 		Load object in memory from database
	 *
	 * 		@param  int $fk_formation_catalogue
	 * 		@return int  Number of lines added to $this->lines, -1 if KO
	 * 		@throws Exception
	 */
	public function fetch_objpeda_per_session_catalogue($fk_session_catalogue)
	{

		$sql = "SELECT";
		$sql .= " o.rowid, o.intitule, o.priorite, o.fk_session_catalogue, o.tms, o.fk_user_author";
		$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session_catalogue_objectifs_peda AS o";
		$sql .= " WHERE o.fk_session_catalogue = " . $fk_session_catalogue;
		$sql .= " ORDER BY o.priorite ASC";

		dol_syslog(get_class($this) . "::fetch_objpeda_per_formation ", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			$this->lines = array();
			$num = $this->db->num_rows($resql);
			$i = 0;

			while ( $i < $num ) {
				$obj = $this->db->fetch_object($resql);

				$line = new AgfObjPedaLine();

				$line->id = $obj->rowid;
				$line->fk_formation_catalogue = $obj->fk_formation_catalogue;
				$line->intitule = stripslashes($obj->intitule);
				$line->priorite = $obj->priorite;

				$this->lines[$i] = $line;

				$i ++;
			}
			$this->db->free($resql);
			return $num;
		} else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::fetch_objpeda_per_formation " . $this->error, LOG_ERR);
			return - 1;
		}
	}


	/**
	 *
	 * 		Récupère les objectifs pédagogiques du recueil standard dans llx_agefodd_formation_objectifs_peda
	 * 		et qui les copie dans la nouvelle base llx_agefodd_session_catalogue_objectifs_peda ligne par ligne
	 *
	 * 		@return int  -1 if KO, 1 if OK
	 */
	public function cloneObjPeda()
	{
		$this->db->begin();

		// On insère dans la table des objectifs pédagogiques de clone, les informations des objectifs pédagogiques du recueil standard
		$sql = "INSERT INTO " . MAIN_DB_PREFIX . "agefodd_session_catalogue_objectifs_peda(";
		$sql .= "fk_session_catalogue, intitule, priorite, fk_user_author,fk_user_mod,datec";
		$sql .= ") SELECT '".$this->id."', intitule, priorite, fk_user_author,fk_user_mod,datec";
		$sql .= " FROM ".MAIN_DB_PREFIX. "agefodd_formation_objectifs_peda";
		$sql .= " WHERE fk_formation_catalogue = $this->fk_formation_catalogue";

		$resql = $this->db->query($sql);

		if (! $resql) {
			$this->errors[] = "Error " . $this->db->lasterror();
			$this->db->rollback();
			return -1;
		} else $this->db->commit();
		return 1;
	}

	/**
	 * Update object into database
	 *
	 * @param User $user that modify
	 * @param int $notrigger triggers after, 1=disable triggers
	 * @return int <0 if KO, >0 if OK
	 */
	public function update_objpeda($user, $notrigger = 0)
	{
		global $conf, $langs;
		$error = 0;

		// Clean parameters
		$this->intitule = $this->db->escape(trim($this->intitule));

		// Check parameters
		// Put here code to add control on parameters values

		// Update request
		$sql = "UPDATE " . MAIN_DB_PREFIX . "agefodd_session_catalogue_objectifs_peda SET";
		$sql .= " fk_formation_catalogue=" . intval($this->fk_formation_catalogue) . ",";
		$sql .= " intitule='" . $this->intitule . "',";
		$sql .= " fk_user_mod=" . intval($user->id) . ",";
		$sql .= " priorite=" . intval($this->priorite) . " ";
		$sql .= " WHERE rowid = " . intval($this->id);

		$this->db->begin();

		dol_syslog(get_class($this) . "::update ", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (! $resql) {
			$error ++;
			$this->errors[] = "Error " . $this->db->lasterror();
		}
		if (! $error) {
			if (! $notrigger) {
				// Uncomment this and change MYOBJECT to your own tag if you
				// want this action call a trigger.

				// // Call triggers
				// include_once(DOL_DOCUMENT_ROOT . "/interfaces.class.php");
				// $interface=new Interfaces($this->db);
				// $result=$interface->run_triggers('MYOBJECT_MODIFY',$this,$user,$langs,$conf);
				// if ($result < 0) { $error++; $this->errors=$interface->errors; }
				// // End call triggers
			}
		}

		// Commit or rollback
		if ($error) {
			foreach ($this->errors as $errmsg) {
				dol_syslog(get_class($this) . "::update " . $errmsg, LOG_ERR);
				$this->error .= ($this->error ? ', ' . $errmsg : $errmsg);
			}
			$this->db->rollback();
			return - 1 * $error;
		} else {
			$this->db->commit();
			return 1;
		}
	}

	/**
	 * Delete object in database
	 *
	 * @param int $id to delete
	 * @return int if KO, >0 if OK
	 */
	public function remove_objpeda($id = null)
	{
		$sql = "DELETE FROM " . MAIN_DB_PREFIX . "agefodd_session_catalogue_objectifs_peda";
		$sql .= " WHERE fk_session_catalogue = " . intval($this->id);

		dol_syslog(get_class($this) . "::remove ", LOG_DEBUG);
		$resql = $this->db->query($sql);

		if ($resql) {
			return 1;
		} else {
			$this->error = $this->db->lasterror();
			return - 1;
		}
	}

	/**
	 * Initialise object with example values
	 * Id must be 0 if object instance is a specimen
	 *
	 * @return void
	 */
	public function initAsSpecimen()
	{
		$this->id = 0;
		$this->ref = '';
		$this->intitule = '';
		$this->duree = '';
		$this->public = '';
		$this->methode = '';
		$this->prerequis = '';
		$this->programme = '';
		$this->pedago_usage = '';
		$this->sanction = '';
		$this->archive = '';
	}

	/**
	 * Return description of training
	 *
	 * @return string translated description
	 */
	public function getToolTip()
	{
		global $langs;

		$langs->load("admin");
		$langs->load("agefodd@agefodd");

		$s  = '<b>' . $langs->trans("AgfTraining") . '</b>:<u>' . $this->intitule . ':</u><br>';
		$s .= '<br>';
		$s .= $langs->trans("AgfDuree") . ' : ' . $this->duree . ' H <br>';
		$s .= $langs->trans("AgfPublic") . ' : ' . $this->public . '<br>';
		$s .= $langs->trans("AgfMethode") . ' : ' . $this->methode . '<br>';

		$s .= '<br>';

		return $s;
	}

	/**
	 * Fetch le clone de la formation au catalogue s'il existe
	 *
	 * 	@param int $fk_session
	 *
	 *  @return int <0 if KO, ID of fetched SessionCatalogue if OK, 0 if not found
	 */
	public function fetchSessionCatalogue($fk_session)
	{
		$sql = "SELECT sc.rowid";
		$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session_catalogue as sc";
		$sql .= " WHERE 1=1";
		$sql .= " AND sc.entity IN (" . getEntity('agefodd'/*agsession*/) . ")";
		$sql .= " AND sc.fk_session =" . intval($fk_session);

		$resql = $this->db->query($sql);

		if ($resql) {
			$num = $this->db->num_rows($resql);

			if ($num) {
				$obj = $this->db->fetch_object($resql);
				if (!empty($obj->rowid)) {
					return $this->fetch($obj->rowid);
				}
			} else {
				return 0;
			}
		} else {
			$this->error = $this->db->lasterror();
			return -1;
		}
	}

	/**
	 * 		Checks if there is a clone or not
	 *
	 * 		@param string $sortorder Sort Order
	 * 		@param string $sortfield Sort field
	 * 		@param int $limit offset limit
	 * 		@param int $offset offset limit
	 * 		@param int $arch archive
	 * 		@param array $filter array of filter where clause
	 * 		@param array $array_options_keys extrafields to fetch
	 * 		@return int  number of fetched lines, <0 if KO
	 */
	public function isCloned($sortorder, $sortfield, $limit, $offset, $arch = 0, $filter = array(), $array_options_keys = array())
	{
		if (empty($array_options_keys)) {
			require_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';
			$extrafields = new ExtraFields($this->db);
			$extrafields->fetch_name_optionals_label($this->table_element);
			if (is_array($extrafields->attributes[$this->table_element]['label'])) {
				$array_options_keys = array_keys($extrafields->attributes[$this->table_element]['label']);
			}
		}

		$sql = "SELECT sc.rowid";

		if (is_array($array_options_keys) && count($array_options_keys) > 0) {
			foreach ($array_options_keys as $key) {
				$sql .= ', ef.' . $key;
			}
		}
		$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session_catalogue as sc";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "agefodd_session_catalogue_extrafields as ef";
		$sql .= " ON ef.fk_object = sc.rowid";

		$sql .= " WHERE 1=1";
		$sql .= " AND sc.entity IN (" . getEntity('agefodd'/*agsession*/) . ")";
		// Manage filter
		if (! empty($filter)) {
			foreach ($filter as $key => $value) {
					$sql.= $value;
			}
		}

		dol_syslog(get_class($this) . "::fetch_all ", LOG_DEBUG);

		$resql = $this->db->query($sql);
		if ($resql) {
			$this->lines = array ();
			$num = $this->db->num_rows($resql);
			$i = 0;

			if ($num) {
				while ( $i < $num ) {
					$obj = $this->db->fetch_object($resql);

					$line = new self($this->db);
					$line->fetch($obj->rowid);
					$this->session_catalogue[$i] = $line;

					$i ++;
				}
			}
			$this->db->free($resql);
			return $num;
		} else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::fetch_all " . $this->error, LOG_ERR);
			return - 1;
		}
	}

	/**
	 * Load an object from its id and create a new one in database
	 *
	 * @param int $fromid of object to clone
	 * @return int id of clone
	 */
	public function createFromClone($fromid)
	{
		global $user, $conf;

		$error = 0;

		$object = new Formation($this->db);

		$this->db->begin();

		// Load source object
		$result = $object->fetch($fromid);
		if ($result < 0) {
			$this->error = $object->error;
			$error ++;
		}

		$defaultref = '';
		$obj = empty($conf->global->AGF_ADDON) ? 'mod_agefodd_simple' : $conf->global->AGF_ADDON;
		$path_rel = dol_buildpath('/agefodd/core/modules/agefodd/' . $conf->global->AGF_ADDON . '.php');
		if (! empty($conf->global->AGF_ADDON) && is_readable($path_rel)) {
			dol_include_once('/agefodd/core/modules/agefodd/' . $conf->global->AGF_ADDON . '.php');
			$modAgefodd = new $obj();
			$defaultref = $modAgefodd->getNextValue(null, $this);
		}

		if (is_numeric($defaultref) && $defaultref <= 0)
			$defaultref = '';

		$object->ref_obj = $defaultref;

		// Create clone
		$result = $object->create($user);
		// Other options
		if ($result < 0) {
			$this->errors[] = $object->error;
			$error ++;
		}

		$newid = $object->id;

		$result = $object->createAdmLevelForTraining($user);
		// Other options
		if ($result < 0) {
			$this->errors[] = $object->error;
			$error ++;
		}

		$source = new Formation($this->db);
		$result_peda = $source->fetch_objpeda_per_formation($fromid);
		if ($result_peda < 0) {
			$this->errors[] = $source->error;
			$error ++;
		}
		foreach ($source->lines as $line) {
			$object->intitule = $line->intitule;
			$object->priorite = $line->priorite;
			$object->fk_formation_catalogue = $newid;

			$result_peda = $object->create_objpeda($user);
			if ($result_peda < 0) {
				$this->errors[] = $object->error;
				$error ++;
			}
		}

		if ($conf->global->AGF_FILTER_TRAINER_TRAINING) {
			$source->id = $fromid;
			$result_trainer = $source->fetchTrainer();
			if ($result_trainer < 0) {
				$this->errors[] = $source->error;
				$error ++;
			}
			$trainer_array = array();
			foreach ($source->trainers as $trainer) {
				$trainer_array[$trainer->id] = $trainer->id;
			}
			$object->id = $newid;
			$result_trainer = $object->setTrainingTrainer($trainer_array, $user);
			if ($result_trainer < 0) {
				$this->errors[] = $object->error;
				$error ++;
			}
		}

		// End
		if (empty($error)) {
			$this->db->commit();
			return $newid;
		} else {
			foreach ($this->errors as $errmsg) {
				dol_syslog(get_class($this) . "::createFromClone " . $errmsg, LOG_ERR);
				$this->error .= ($this->error ? ', ' . $errmsg : $errmsg);
			}
			$this->db->rollback();
			return - 1;
		}
	}

	/**
	 *
	 * @param string $label
	 * @return string
	 */
	public function getNomUrl($label = 'all')
	{
		$link = dol_buildpath('/agefodd/training/card.php', 1);
		if ($label == 'all') {
			return '<a href="' . $link . '?id=' . $this->id . '">' . $this->ref . ((! empty($this->ref_interne)) ? ' (' . $this->ref_interne . ') ' : ' ') . $this->intitule . '</a>';
		} else {
			return '<a href="' . $link . '?id=' . $this->id . '">' . $this->$label . '</a>';
		}
	}

	/*
	 * Function to generate pdf program by link
	 * cette fonction recherche dans les liens URL de l'onglet "fichiers joints" celui intitulé "PRG", et sauvegarde la cible de ce lien sous forme de fichier PDF.
	 *
	 * @return int  1 if successful, 0 if not found
	 */
	function generatePDAByLink()
	{
		global $conf;
		require_once DOL_DOCUMENT_ROOT . '/core/class/link.class.php';
		$link = new Link($this->db);
		$links = array();
		$link->fetchAll($links, $this->element, $this->id);

		if (!empty($links)) {
			foreach ($links as $link) {
				if ($link->label=="PRG") {
					$fopen = fopen($link->url, 'r');
					if ($fopen !== false) {
						file_put_contents($conf->agefodd->dir_output . '/' . 'fiche_pedago_recueil_' . $this->id . '.pdf', $fopen);
					}
					return 1;
				}
			}
		}
		return 0;
	}
}
