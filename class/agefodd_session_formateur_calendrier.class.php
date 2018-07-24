<?php
/*
 * Copyright (C) 2012-2016 Florian Henry <florian.henry@open-concept.pro>
 * Copyright (C) 2013 Jean-François Ferry	<jfefe@aternatik.fr>
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file agefodd/class/agefodd_session_formateur_calendrier.class.php
 * \brief Manage calendar for traner by session
 */

// Put here all includes required by your class file
require_once (DOL_DOCUMENT_ROOT . "/core/class/commonobject.class.php");
require_once ("agefodd_formateur.class.php");
// require_once(DOL_DOCUMENT_ROOT."/societe/class/societe.class.php");
// require_once(DOL_DOCUMENT_ROOT."/product/class/product.class.php");

/**
 * Put here description of your class
 */
class Agefoddsessionformateurcalendrier extends CommonObject {
	public $error; // !< To return error code (or message)
	public $errors = array (); // !< To return several error codes (or messages)
	public $element = 'agefodd_sessionformateurcalendrier'; // !< Id that identify managed objects
	public $table_element = 'agefodd_session_formateur_calendrier'; // !< Name of table without prefix where object is stored
	public $id;
	public $fk_agefodd_session_formateur;
	public $date_session = '';
	public $heured = '';
	public $heuref = '';
	public $trainer_cost;
	public $trainer_status;
	public $trainer_status_in_session;
	public $fk_actioncomm;
	public $fk_user_author;
	public $datec = '';
	public $fk_user_mod;
	public $tms = '';
	public $status = 0;
	public $lines = array ();

	
	const STATUS_DRAFT = 0;
	const STATUS_CONFIRMED = 1;
	const STATUS_ABANDONED = -1;
	/**
	 * Constructor
	 *
	 * @param DoliDb $db handler
	 */
	public function __construct($db) {
		$this->db = $db;
		return 1;
	}

	/**
	 * Create object into database
	 *
	 * @param User $user that creates
	 * @param int $notrigger triggers after, 1=disable triggers
	 * @return int <0 if KO, Id of created object if OK
	 */
	public function create($user, $notrigger = 0) {
		global $conf, $langs;
		$error = 0;

		// Clean parameters

		if (isset($this->fk_agefodd_session_formateur))
			$this->fk_agefodd_session_formateur = trim($this->fk_agefodd_session_formateur);
		if (isset($this->trainer_cost))
			$this->trainer_cost = trim($this->trainer_cost);
		if (isset($this->trainer_status))
			$this->trainer_cost = trim($this->trainer_status);
		if (isset($this->fk_actioncomm))
			$this->fk_actioncomm = trim($this->fk_actioncomm);
		if (isset($this->fk_user_author))
			$this->fk_user_author = trim($this->fk_user_author);
		if (isset($this->fk_user_mod))
			$this->fk_user_mod = trim($this->fk_user_mod);
		if (!is_numeric($this->status)) $this->status = 0;
			// Check parameters
			// Put here code to add control on parameters values

		if (! empty($conf->global->AGF_DOL_TRAINER_AGENDA)) {
			
			$result = $this->createAction($user);
			
			if ($result <= 0) {
				$error ++;
				$this->errors[] = "Error " . $this->db->lasterror();
			} else {
				$this->fk_actioncomm = $result;
			}
		}

		// Insert request
		$sql = "INSERT INTO " . MAIN_DB_PREFIX . "agefodd_session_formateur_calendrier(";
		$sql .= "entity,";
		$sql .= "fk_agefodd_session_formateur,";
		$sql .= "date_session,";
		$sql .= "heured,";
		$sql .= "heuref,";
		$sql .= "trainer_cost,";
		$sql .= "trainer_status,";
		$sql .= "fk_actioncomm,";
		$sql .= "fk_user_author,";
		$sql .= "datec,";
		$sql .= "fk_user_mod,";
		$sql .= "status";
		$sql .= ") VALUES (";

		$sql .= " '" . $conf->entity . "',";
		$sql .= " " . (! isset($this->fk_agefodd_session_formateur) ? 'NULL' : "'" . $this->fk_agefodd_session_formateur . "'") . ",";
		$sql .= " " . (! isset($this->date_session) || dol_strlen($this->date_session) == 0 ? 'NULL' : "'" . $this->db->idate($this->date_session) . "'") . ",";
		$sql .= " " . (! isset($this->heured) || dol_strlen($this->heured) == 0 ? 'NULL' : "'" . $this->db->escape($this->db->idate($this->heured)) . "'") . ",";
		$sql .= " " . (! isset($this->heuref) || dol_strlen($this->heuref) == 0 ? 'NULL' : "'" . $this->db->escape($this->db->idate($this->heuref)) . "'") . ",";
		$sql .= " " . (! isset($this->trainer_cost) ? 'NULL' : "'" . $this->db->escape($this->trainer_cost) . "'") . ",";
		$sql .= " " . (! isset($this->trainer_status) ? 'NULL' : $this->db->escape($this->trainer_status)) . ",";
		$sql .= " " . (! isset($this->fk_actioncomm) ? 'NULL' : "'" . $this->fk_actioncomm . "'") . ",";
		$sql .= " " . (! isset($this->fk_user_author) ? $user->id : "'" . $this->fk_user_author . "'") . ",";
		$sql .= " '" . (! isset($this->datec) || dol_strlen($this->datec) == 0 ? $this->db->idate(dol_now()) : $this->db->idate($this->datec)) . "',";
		$sql .= " " . (! isset($this->fk_user_mod) ? $user->id : "'" . $this->fk_user_mod . "'") . ",";
		$sql .= " " . $this->status;
		$sql .= ")";
		$this->db->begin();
		
		dol_syslog(get_class($this) . "::create", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (! $resql) {
			$error ++;
			
			$this->errors[] = "Error " . $this->db->lasterror();
		}

		if (! $error) {
			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX . "agefodd_session_formateur_calendrier");

			if (! $notrigger) {
				// Uncomment this and change MYOBJECT to your own tag if you
				// want this action calls a trigger.

				// // Call triggers
				// include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
				// $interface=new Interfaces($this->db);
				// $result=$interface->run_triggers('MYOBJECT_CREATE',$this,$user,$langs,$conf);
				// if ($result < 0) { $error++; $this->errors=$interface->errors; }
				// // End call triggers
			}
		}

		// Commit or rollback
		if ($error) {
			foreach ( $this->errors as $errmsg ) {
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
	 * Load object in memory from the database
	 *
	 * @param int $id object
	 * @return int <0 if KO, >0 if OK
	 */
	public function fetch($id) {
		global $langs;
		$sql = "SELECT";
		$sql .= " t.rowid,";

		$sql .= " t.fk_agefodd_session_formateur,";
		$sql .= " t.date_session,";
		$sql .= " t.heured,";
		$sql .= " t.heuref,";
		$sql .= " t.trainer_cost,";
		$sql .= " t.trainer_status,";
		$sql .= " t.fk_actioncomm,";
		$sql .= " t.fk_user_author,";
		$sql .= " t.datec,";
		$sql .= " t.fk_user_mod,";
		$sql .= " t.tms,";
		$sql .= " t.status,";
		$sql .= " f.fk_session";

		$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session_formateur_calendrier as t";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_formateur as f ON (f.rowid=t.fk_agefodd_session_formateur)";
		$sql .= " WHERE t.rowid = " . $id;

		dol_syslog(get_class($this) . "::fetch", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);

				$this->id = $obj->rowid;

				$this->fk_agefodd_session_formateur = $obj->fk_agefodd_session_formateur;
				$this->date_session = $this->db->jdate($obj->date_session);
				$this->heured = $this->db->jdate($obj->heured);
				$this->heuref = $this->db->jdate($obj->heuref);
				$this->trainer_cost = $obj->trainer_cost;
				$this->trainer_status = $obj->trainer_status;
				$this->fk_actioncomm = $obj->fk_actioncomm;
				$this->fk_user_author = $obj->fk_user_author;
				$this->datec = $this->db->jdate($obj->datec);
				$this->fk_user_mod = $obj->fk_user_mod;
				$this->tms = $this->db->jdate($obj->tms);
				$this->status = $obj->status;
				$this->sessid = $obj->fk_session;
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
	 * Load object in memory from database
	 *
	 * @param int $actionid object
	 * @return int <0 if KO, >0 if OK
	 */
	public function fetch_by_action($actionid) {
		global $langs;

		$sql = "SELECT";
		$sql .= " s.rowid, s.date_session, s.heured, s.heuref, s.fk_actioncomm, s.fk_agefodd_session_formateur,s.trainer_cost,s.trainer_status, s.status";
		$sql .= " ,f.fk_session ";
		$sql .= " ,f.trainer_status as trainer_status_in_session";
		$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session_formateur_calendrier as s";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_formateur as f ON  f.rowid=s.fk_agefodd_session_formateur";
		$sql .= " WHERE s.fk_actioncomm = " . $actionid;

		dol_syslog(get_class($this) . "::fetch_by_action", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				$this->id = $obj->rowid;
				$this->fk_agefodd_session_formateur = $obj->fk_agefodd_session_formateur;
				$this->date_session = $this->db->jdate($obj->date_session);
				$this->heured = $this->db->jdate($obj->heured);
				$this->heuref = $this->db->jdate($obj->heuref);
				$this->sessid = $obj->fk_session;
				$this->trainer_cost = $obj->trainer_cost;
				$this->trainer_status = $obj->trainer_status;
				$this->status = $obj->status;
				$this->fk_actioncomm = $obj->fk_actioncomm;
				$this->trainer_status_in_session = $obj->trainer_status_in_session;
			}
			$this->db->free($resql);

			return 1;
		} else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::fetch_by_action " . $this->error, LOG_ERR);
			return - 1;
		}
	}

	/**
	 * Load object in memory from database
	 *
	 * @param int $id of session
	 * @return int <0 if KO, >0 if OK
	 */
	public function fetch_all($id) {
		global $langs;

		$sql = "SELECT ";
		$sql .= "s.rowid,";
		$sql .= "s.fk_agefodd_session_formateur,";
		$sql .= "s.date_session,";
		$sql .= "s.heured,";
		$sql .= "s.heuref,";
		$sql .= "s.trainer_cost,";
		$sql .= "s.trainer_status,";
		$sql .= "s.fk_actioncomm,";
		$sql .= "s.fk_user_author,";
		$sql .= "s.status,";
		$sql .= "sf.fk_session,";
		$sql .= "sf.trainer_status as trainer_status_in_session";
		$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session_formateur_calendrier as s";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_formateur as sf ON sf.rowid=s.fk_agefodd_session_formateur";
		$sql .= " WHERE s.fk_agefodd_session_formateur = " . $id;
		$sql .= " ORDER BY s.date_session ASC, s.heured ASC";

		dol_syslog(get_class($this) . "::fetch_all", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			$this->lines = array ();
			$num = $this->db->num_rows($resql);
			$i = 0;
			for($i = 0; $i < $num; $i ++) {
				$line = new AgefoddcalendrierformateurLines();

				$obj = $this->db->fetch_object($resql);

				$line->id = $obj->rowid;
				$line->date_session = $this->db->jdate($obj->date_session);
				$line->fk_agefodd_session_formateur = $obj->fk_agefodd_session_formateur;
				$line->heured = $this->db->jdate($obj->heured);
				$line->heuref = $this->db->jdate($obj->heuref);
				$line->trainer_cost = $obj->trainer_cost;
				$line->trainer_status = $obj->trainer_status;
				$line->fk_actioncomm = $obj->fk_actioncomm;
				$line->fk_user_author = $obj->fk_user_author;
				$line->status = $obj->status;
				$line->fk_session = $obj->fk_session;
				$line->trainer_status_in_session = $obj->trainer_status_in_session;

				$this->lines[$i] = $line;
			}
			$this->db->free($resql);
			return 1;
		} else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::fetch_all " . $this->error, LOG_ERR);
			return - 1;
		}
	}

	/**
	 * Load object in memory from database
	 *
	 * @param int $id of session
	 * @return int <0 if KO, >0 if OK
	 */
	public function fetch_all_by_trainer($id) {
		global $langs;

		$sql = "SELECT ";
		$sql .= "s.rowid,";
		$sql .= "s.fk_agefodd_session_formateur,";
		$sql .= "s.date_session,";
		$sql .= "s.heured,";
		$sql .= "s.heuref,";
		$sql .= "s.trainer_cost,";
		$sql .= "s.trainer_status,";
		$sql .= "s.fk_actioncomm,";
		$sql .= "s.fk_user_author,";
		$sql .= "s.status,";
		$sql .= "sf.fk_session,";
		$sql .= "sf.trainer_status as trainer_status_in_session";
		$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session_formateur_calendrier as s";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_formateur as sf ON sf.rowid=s.fk_agefodd_session_formateur";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_formateur as trainer ON trainer.rowid=sf.fk_agefodd_formateur";
		$sql .= " WHERE trainer.rowid = " . $id;
		$sql .= " ORDER BY s.date_session ASC, s.heured ASC";

		dol_syslog(get_class($this) . "::".__METHOD__, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			$this->lines = array ();
			$num = $this->db->num_rows($resql);
			$i = 0;
			for($i = 0; $i < $num; $i ++) {
				$line = new AgefoddcalendrierformateurLines();

				$obj = $this->db->fetch_object($resql);

				$line->id = $obj->rowid;
				$line->date_session = $this->db->jdate($obj->date_session);
				$line->fk_agefodd_session_formateur = $obj->fk_agefodd_session_formateur;
				$line->heured = $this->db->jdate($obj->heured);
				$line->heuref = $this->db->jdate($obj->heuref);
				$line->trainer_cost = $obj->trainer_cost;
				$line->trainer_status = $obj->trainer_status;
				$line->fk_actioncomm = $obj->fk_actioncomm;
				$line->fk_user_author = $obj->fk_user_author;
				$line->status = $obj->status;
				$line->fk_session = $obj->fk_session;
				$line->trainer_status_in_session = $obj->trainer_status_in_session;

				$this->lines[$i] = $line;
			}
			$this->db->free($resql);
			return 1;
		} else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::fetch_all_by_trainer " . $this->error, LOG_ERR);
			return - 1;
		}
	}

	/**
	 * Update object into database
	 *
	 * @param User $user that modifies
	 * @param int $notrigger triggers after, 1=disable triggers
	 * @return int <0 if KO, >0 if OK
	 */
	public function update($user = 0, $notrigger = 0) {
		global $conf, $langs;
		$error = 0;

		// Clean parameters

		if (isset($this->fk_agefodd_session_formateur))
			$this->fk_agefodd_session_formateur = trim($this->fk_agefodd_session_formateur);
		if (isset($this->trainer_cost))
			$this->trainer_cost = trim($this->trainer_cost);
		if (isset($this->trainer_status))
			$this->trainer_status = trim($this->trainer_status);
		if (isset($this->fk_actioncomm))
			$this->fk_actioncomm = trim($this->fk_actioncomm);
		if (isset($this->fk_user_author))
			$this->fk_user_author = trim($this->fk_user_author);
		if (isset($this->fk_user_mod))
			$this->fk_user_mod = trim($this->fk_user_mod);
		if (!is_numeric($this->status)) $this->status = 0;
			// Check parameters
			// Put here code to add a control on parameters values

			if (! empty($conf->global->AGF_DOL_TRAINER_AGENDA)) {
				$result = $this->updateAction($user);
				if ($result <= 0) {
					$error ++;
					$this->errors[] = "Error " . $this->db->lasterror();
				} else {
					$this->fk_actioncomm = $result;
				}
			}


		// Update request
		$sql = "UPDATE " . MAIN_DB_PREFIX . "agefodd_session_formateur_calendrier SET";

		$sql .= " fk_agefodd_session_formateur=" . (isset($this->fk_agefodd_session_formateur) ? $this->fk_agefodd_session_formateur : "null") . ",";
		$sql .= " date_session=" . (dol_strlen($this->date_session) != 0 ? "'" . $this->db->idate($this->date_session) . "'" : 'null') . ",";
		$sql .= " heured=" . (dol_strlen($this->heured) != 0 ? "'" . $this->db->idate($this->heured) . "'" : 'null') . ",";
		$sql .= " heuref=" . (dol_strlen($this->heuref) != 0 ? "'" . $this->db->idate($this->heuref) . "'" : 'null') . ",";
		$sql .= " trainer_cost=" . (isset($this->trainer_cost) ? "'".$this->trainer_cost."'" : "null") . ",";
		$sql .= " trainer_status=" . (isset($this->trainer_status) ? $this->trainer_status : "null") . ",";
		$sql .= " fk_actioncomm=" . (isset($this->fk_actioncomm) ? $this->fk_actioncomm : "null") . ",";
		$sql .= " fk_user_author=" . (isset($this->fk_user_author) ? $this->fk_user_author : "null") . ",";
		$sql .= " datec=" . (dol_strlen($this->datec) != 0 ? "'" . $this->db->idate($this->datec) . "'" : 'null') . ",";
		$sql .= " fk_user_mod=" . (isset($this->fk_user_mod) ? $this->fk_user_mod : "null") . ",";
		$sql .= " tms=" . (dol_strlen($this->tms) != 0 ? "'" . $this->db->idate($this->tms) . "'" : 'null') . ",";
		$sql .= " status=" . $this->status . "";

		$sql .= " WHERE rowid=" . $this->id;

		$this->db->begin();

		dol_syslog(get_class($this) . "::update", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (! $resql) {
			$error ++;
			$this->errors[] = "Error " . $this->db->lasterror();
		}

		if (! $error) {
			if (! $notrigger) {
				// Uncomment this and change MYOBJECT to your own tag if you
				// want this action calls a trigger.

				// // Call triggers
				// include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
				// $interface=new Interfaces($this->db);
				// $result=$interface->run_triggers('MYOBJECT_MODIFY',$this,$user,$langs,$conf);
				// if ($result < 0) { $error++; $this->errors=$interface->errors; }
				// // End call triggers
			}
		}

		// Commit or rollback
		if ($error) {
			foreach ( $this->errors as $errmsg ) {
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

	public function delete($user)
	{
		$error = 0;
		
		dol_syslog(get_class($this) . "::delete", LOG_DEBUG);
		
		$this->db->begin();
		
		if (! empty($this->fk_actioncomm))
		{
			require_once DOL_DOCUMENT_ROOT.'/comm/action/class/actioncomm.class.php';
			$action = new ActionComm($this->db);
			$action->id = $this->fk_actioncomm;
			$r=$action->delete();
			if ($r < 0) $error++;
		}
		
		if (!$error)
		{
			if (is_callable('parent::deleteCommon'))
			{
				if (parent::deleteCommon($user) < 0) $error++;
			}
			else
			{
				$sql = "DELETE FROM " . MAIN_DB_PREFIX . "agefodd_session_formateur_calendrier";
				$sql .= " WHERE rowid = " . $this->id;
				$resql = $this->db->query($sql);
				if (!$resql)
				{
					$error++;
					$this->error = $this->db->lasterror();
				}
			}
		}
		
		if (!$error)
		{
			$this->db->commit();
			return 1;
		}
		else
		{
			$this->db->rollback();
			$this->error = $this->db->lasterror();
			return -1 * $error;
		}
	}
	
	/**
	 * Delete object in database
	 *
	 * @param int $id to delete
	 * @return int <0 if KO, >0 if OK
	 */
	public function remove($id) {
		global $user;
		$result = $this->fetch($id);
		return $this->delete($user);
	}

	/**
	 * Create Action in Dolibarr Agenda
	 *
	 * @param int			fk_session_place Location of session
	 * @param User $user that modify
	 */
	public function createAction($user) {
		global $conf, $langs;

		$error = 0;

		dol_include_once('/comm/action/class/actioncomm.class.php');
		dol_include_once('/agefodd/class/agsession.class.php');
		dol_include_once('/agefodd/class/agefodd_session_formateur.class.php');

		require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
		require_once DOL_DOCUMENT_ROOT . '/contact/class/contact.class.php';
		require_once DOL_DOCUMENT_ROOT . '/user/class/user.class.php';

		$action = new ActionComm($this->db);
		$session = new Agsession($this->db);

		$formateur_session = new Agefodd_session_formateur($this->db);
		$result = $formateur_session->fetch($this->fk_agefodd_session_formateur);
		if ($result < 0) {
			$error ++;
		}

		$formateur = new Agefodd_teacher($this->db);
		$result = $formateur->fetch($formateur_session->formid);
		
		if ($result < 0) {
			$error ++;
		}

		$result = $session->fetch($this->sessid);
		if ($result < 0) {
			$error ++;
		}

		$action->label = $session->formintitule . '(' . $session->formref . ')'.'('.$session->id.')';
		$action->location = $session->placecode;
		$action->datep = $this->heured;
		$action->datef = $this->heuref;
		$action->author = $user; // User saving action
		$action->fk_element = $session->id;
		$action->elementtype = $session->element;
		$action->type_code = 'AC_AGF_SESST';
		$action->userownerid = $user->id;
		$action->percentage = -1;

		// Si le formateur est un contact alors sur l'évenement : « Evénement concernant la société » = fournisseur
		// Sinon si le formateur est un user alors « Action affectée » = user correspondant.
		if ($formateur->fk_user) {
			$userstat = new User($this->db);
			$ret = $userstat->fetch($formateur->fk_user);
			if ($ret) {
				$action->usertodo = $userstat;
				$action->userassigned = array (
						$userstat->id => array (
								'id' => $userstat->id
						)
				);
				$action->userownerid = $formateur->fk_user;
				$action->userassigned = array($formateur->fk_user);
			}
		} else {
			$contactstat = new Contact($this->db);
			$ret = $contactstat->fetch($formateur->fk_socpeople);
			if ($ret) {
				$action->contact = $contactstat;

				$companystat = new Societe($this->db);
				$ret = $companystat->fetch($contactstat->socid);
				if ($ret)
					$action->societe = $companystat;
					$action->socid = $contactstat->socid;
			}
		}
		
		if ($error == 0) {


			if (method_exists($action, 'create')) {
				$result = $action->create($user);
			} else {
				//For backward compatibility
				$result = $action->add($user);
			}

			if ($result < 0) {
				$error ++;
				dol_syslog(get_class($this) . "::createAction " . $action->error, LOG_ERR);
				return - 1;
			} else {
				return $result;
			}
		} else {
			dol_syslog(get_class($this) . "::createAction " . $action->error, LOG_ERR);
			return - 1;
		}
	}

	/**
	 * update Action in Dolibarr Agenda
	 *
	 * @param User $user that modify
	 */
	public function updateAction($user) {
		global $conf, $langs;

		$error = 0;

		dol_include_once('/comm/action/class/actioncomm.class.php');
		dol_include_once('/agefodd/class/agsession.class.php');

		$action = new ActionComm($this->db);
		$session = new Agsession($this->db);

		$result = $session->fetch($this->sessid);
		if ($result < 0) {
			$error ++;
		}

		$result = $action->fetch($this->fk_actioncomm);
		if ($result < 0) {
			$error ++;
		}

		$result = $action->fetch_userassigned();
		if ($result < 0) {
			$error ++;
		}

		if ($error == 0) {

			if ($action->id == $this->fk_actioncomm) {

				$action->label = $session->formintitule . '(' . $session->formref . ')';
				$action->location = $session->placecode;
				$action->datep = $this->heured;
				$action->datef = $this->heuref;
				$action->type_code = 'AC_AGF_SESST';

				$result = $action->update($user,1);
				$return_id = $this->fk_actioncomm;
			} else {
				$result = $this->createAction($user);
				$return_id= $result;
			}

			if ($result < 0) {
				$error ++;

				dol_syslog(get_class($this) . "::updateAction " . $action->error, LOG_ERR);
				return - 1;
			} else {
				return $return_id;
			}
		} else {
			dol_syslog(get_class($this) . "::updateAction " . $action->error, LOG_ERR);
			return - 1;
		}
	}

	/**
	 * Load an object from its id and create a new one in database
	 *
	 * @param int $fromid of object to clone
	 * @return int id of clone
	 */
	public function createFromClone($fromid) {
		global $user, $langs;

		$error = 0;

		$object = new Agefoddsessionformateurcalendrier($this->db);

		$this->db->begin();

		// Load source object
		$object->fetch($fromid);
		$object->id = 0;
		$object->statut = 0;

		// Clear fields
		// ...

		// Create clone
		$result = $object->create($user);

		// Other options
		if ($result < 0) {
			$this->error = $object->error;
			$error ++;
		}

		if (! $error) {
		}

		// End
		if (! $error) {
			$this->db->commit();
			return $object->id;
		} else {
			$this->db->rollback();
			return - 1;
		}
	}

	/**
	 * Initialise object with example values
	 * Id must be 0 if object instance is a specimen
	 *
	 * @return void
	 */
	public function initAsSpecimen() {
		$this->id = 0;

		$this->fk_agefodd_session_formateur = '';
		$this->date_session = '';
		$this->heured = '';
		$this->heuref = '';
		$this->trainer_cost = '';
		$this->fk_actioncomm = '';
		$this->fk_user_author = '';
		$this->datec = '';
		$this->fk_user_mod = '';
		$this->tms = '';
	}
	
	public static function getStaticLibStatut($status, $mode=0)
	{
		global $langs;
		
		$out = '';
		if ($status == self::STATUS_DRAFT)
		{
			if ($mode == 1) $out.= img_picto('', 'statut0').' ';
			$out.= $langs->trans('AgfStatusCalendar_draft');
		}
		else if ($status == self::STATUS_CONFIRMED)
		{
			if ($mode == 1) $out.= img_picto('', 'statut4').' ';
			$out.= $langs->trans('AgfStatusCalendar_confirmed');
		}
		else if ($status == self::STATUS_ABANDONED)
		{
			if ($mode == 1) $out.= img_picto('', 'statut6').' ';
			$out.= $langs->trans('AgfStatusCalendar_abandoned');
		}
		
		return $out;
	}
	
	function getLibStatut($mode = 0){
	    return self::getStaticLibStatut($this->status, $mode);
	}
}
class AgefoddcalendrierformateurLines {
	public $id;
	public $date_session;
	public $fk_agefodd_session_formateur;
	public $heured;
	public $heuref;
	public $trainer_cost;
	public $trainer_status;
	public $fk_actioncomm;
	public $fk_user_author;
	public $fk_session;
}
?>
