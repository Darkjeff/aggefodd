<?php
/*
 * Copyright (C) 2012-2014 Florian Henry <florian.henry@open-concept.pro>
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
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 */

/**
 * \file /agefodd/class/report_bpf.php
 * \ingroup agefodd
 * \brief File of class to generate report for agefodd
 *        BPF (bilan pédagogique et financier) is a document required for training centers operating in France.
 *            It has a very precise structure and holds details and statistics about the finances and training
 *            activities of the company.
 */
require_once 'agefodd_export_excel.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';

/**
 * Class to build report by customer
 */
class ReportBPF extends AgefoddExportExcel
{
	private $trainer_data = array();
	private $trainee_data = array();
	private $trainee_data_f2 = array();
	private $financial_data = array();
	private $financial_data_c = array();
	private $financial_data_outcome = array();
	public $warnings = array();

	/**
	 * Tableau des requêtes financières (découpées en SELECT, FROM et WHERE)
	 * Ce sont les requêtes de répartition du chiffre d'affaire, donc elles
	 * font des sommes de lignes de factures
	 * @var array $SQL_DEBUG_C
	 */
	public $SQL_DEBUG_C = array();

	/**
	 * Constructor
	 *
	 * @param DoliDB $db handler
	 */
	public function __construct($db, $outputlangs)
	{
		$outputlangs->load('agefodd@agefodd');
		$outputlangs->load("main");

		$sheet_array = array(
				0 => array(
						'name' => 'bpf',
						'title' => $outputlangs->transnoentities('AgfMenuReportBPF')
				)
		);

		$array_column_header = array();

		return parent::__construct($db, $array_column_header, $outputlangs, $sheet_array);
	}

	/**
	 * Output filter line into file
	 *
	 * @return int if KO, >0 if OK
	 */
	public function write_filter($filter)
	{
		dol_syslog(get_class($this) . "::write_filter ");
		// Create a format for the column headings
		try {
			// Manage filter
			if (count($filter) > 0) {
				foreach ($this->sheet_array as $keysheet => $sheet) {
					$this->workbook->setActiveSheetIndex($keysheet);

					foreach ($filter as $key => $value) {
						if ($key == 'search_year') {
							$str_cirteria = $this->outputlangs->transnoentities('Year') . ' ';
							$str_criteria_value = $value;
							$this->workbook->getActiveSheet()->setCellValueByColumnAndRow(0, $this->row[$keysheet], $str_cirteria);
							$this->workbook->getActiveSheet()->setCellValueByColumnAndRow(1, $this->row[$keysheet], $str_criteria_value);
							$this->row[$keysheet] ++;
						}
					}
				}
			}
		} catch ( Exception $e ) {
			$this->error = $e->getMessage();
			return - 1;
		}

		return 1;
	}

	/**
	 * Give complinat file name regarding filter
	 *
	 * @param $filter array an array filter
	 *
	 * @return int <0 if KO, >0 if OK
	 */
	public function getSubTitlFileName($filter)
	{
		$str_sub_name = '';
		if (count($filter) > 0) {
			foreach ($filter as $key => $value) {
				if ($key == 'search_date_start') {
					$str_sub_name .= $this->outputlangs->transnoentities('From');
					$str_sub_name .= dol_print_date($value);
				}
				if ($key == 'search_date_end') {
					$str_sub_name .= $this->outputlangs->transnoentities('to');
					$str_sub_name .= dol_print_date($value);
				}
			}
		}
		$str_sub_name = str_replace(' ', '', $str_sub_name);
		$str_sub_name = str_replace('.', '', $str_sub_name);
		$str_sub_name = dol_sanitizeFileName($str_sub_name);
		return $str_sub_name;
	}

	/**
	 * Wrtire Excel File
	 *
	 * @param $filter array filter array
	 *
	 * @return int <0 if KO, >0 if OK
	 */
	public function write_file($filter)
	{
		$this->outputlangs->load('agefodd@agefodd');

		$this->title = $this->outputlangs->transnoentities('AgfMenuReportBPF');
		$this->subject = $this->outputlangs->transnoentities('AgfMenuReportBPF');
		$this->description = $this->outputlangs->transnoentities('AgfMenuReportBPF');
		$this->keywords = $this->outputlangs->transnoentities('AgfMenuReportBPF');

		$result = $this->open_file($this->file);
		if ($result < 0) {
			return $result;
		}

		// Fetch Financial data Bock C
		$result = $this->fetch_financial_c($filter);
		if ($result < 0) {
			return $result;
		}

		// Contruct header (column name)
		$array_column_header = array();
		$array_column_header[0][1] = array(
				'type' => 'text',
				'title' => $this->outputlangs->transnoentities('AgfReportBPFOrigProd')
		);

		$array_column_header[0][2] = array(
				'type' => 'number',
				'title' => $this->outputlangs->transnoentities('Amount')
		);

		$this->setArrayColumnHeader($array_column_header);

		$result = $this->write_header();
		if ($result < 0) {
			return $result;
		}



		// Ouput Lines
		$line_to_output = array();
		$array_total_output = array();
		if (is_array($this->financial_data) && count($this->financial_data) > 0) {
			foreach ($this->financial_data as $label_type => $financial_data) {
				$line_to_output[1] = $label_type;
				$line_to_output[2] = !empty($financial_data) ? $financial_data : null;

				$array_total_output[1] = $this->outputlangs->transnoentities('Total');
				$array_total_output[2] += $financial_data;

				$result = $this->write_line($line_to_output, 0);
				if ($result < 0) {
					return $result;
				}
			}
			$result = $this->write_line_total($array_total_output, '3d85c6');
			if ($result < 0) {
				return $result;
			}

			// Fetch Financial data Bock d
			$result = $this->fetch_financial_d($filter);
			if ($result < 0) {
				return $result;
			}
		}

		// Contruct header (column name)
		$array_column_header = array();
		$array_column_header[0][1] = array(
				'type' => 'text',
				'title' => $this->outputlangs->transnoentities('AgfReportBPFChargeProd')
		);

		$array_column_header[0][2] = array(
				'type' => 'number',
				'title' => $this->outputlangs->transnoentities('Amount')
		);

		$this->setArrayColumnHeader($array_column_header);

		$result = $this->write_header();
		if ($result < 0) {
			return $result;
		}

		// Ouput Lines
		$line_to_output = array();
		$array_total_output = array();
		if (is_array($this->financial_data_d) && count($this->financial_data_d) > 0) {
			foreach ($this->financial_data_d as $label_type => $financial_data) {
				$line_to_output[1] = $label_type;
				$line_to_output[2] = $financial_data;

				$array_total_output[1] = $this->outputlangs->transnoentities('Total');
				$array_total_output[2] += $financial_data;

				$result = $this->write_line($line_to_output, 0);
				if ($result < 0) {
					return $result;
				}
			}
			$result = $this->write_line_total($array_total_output, '3d85c6');
			if ($result < 0) {
				return $result;
			}
		}

		// Fetch Trainer Block E
		$result = $this->fetch_trainer($filter);
		if ($result < 0) {
			return $result;
		}

		// Contruct header (column name)
		$array_column_header = array();
		$array_column_header[0][1] = array(
				'type' => 'text',
				'title' => $this->outputlangs->transnoentities('AgfReportBPFChaperE')
		);

		$array_column_header[0][2] = array(
				'type' => 'int',
				'title' => $this->outputlangs->transnoentities('AgfFormateurNb')
		);
		$array_column_header[0][3] = array(
				'type' => 'hours',
				'title' => $this->outputlangs->transnoentities('AgfReportBPFNbHour')
		);
		// 'autosize' => 0

		$this->setArrayColumnHeader($array_column_header);

		$result = $this->write_header();
		if ($result < 0) {
			return $result;
		}

		// Ouput Lines
		$line_to_output = array();
		$array_total_output = array();
		$array_total_output[2] = 0;
		$array_total_output[3] = 0;
		if (is_array($this->trainer_data) && count($this->trainer_data) > 0) {
			foreach ($this->trainer_data as $label_type => $trainer_data) {
				$line_to_output[1] = $label_type;
				$line_to_output[2] = $trainer_data['nb'];
				$line_to_output[3] = $trainer_data['time'];

				$array_total_output[1] = 'Total';
				$array_total_output[2] += $trainer_data['nb'];
				$array_total_output[3] += $trainer_data['time'];

				$result = $this->write_line($line_to_output, 0);
				if ($result < 0) {
					return $result;
				}
			}

			$result = $this->write_line_total($array_total_output, '3d85c6');
			if ($result < 0) {
				return $result;
			}
		}

		// Fetch Trainee Block F -1
		$result = $this->fetch_trainee($filter);
		if ($result < 0) {
			return $result;
		}

		// Contruct header (column name)
		$array_column_header = array();
		$array_column_header[0][1] = array(
				'type' => 'text',
				'title' => $this->outputlangs->transnoentities('AgfReportBPFChaperF1')
		);

		$array_column_header[0][2] = array(
				'type' => 'int',
				'title' => $this->outputlangs->transnoentities('AgfReportBPFNbPart')
		);
		$array_column_header[0][3] = array(
				'type' => 'hours',
				'title' => $this->outputlangs->transnoentities('AgfReportBPFNbHeureSta')
		);
		// 'autosize' => 0

		$this->setArrayColumnHeader($array_column_header);

		$result = $this->write_header();
		if ($result < 0) {
			return $result;
		}

		// Ouput Lines
		$line_to_output = array();
		$array_total_output = array();
		if (is_array($this->trainee_data) && count($this->trainee_data) > 0) {
			foreach ($this->trainee_data as $label_type => $trainee_data) {
				$line_to_output[1] = $label_type;
				$line_to_output[2] = $trainee_data['nb'];
				$line_to_output[3] = $trainee_data['time'];
				$array_total_output[1] = 'Total';
				$array_total_output[2] += $trainee_data['nb'];
				$array_total_output[3] += $trainee_data['time'];

				$result = $this->write_line($line_to_output, 0);
				if ($result < 0) {
					return $result;
				}
			}
			$result = $this->write_line_total($array_total_output, '3d85c6');
			if ($result < 0) {
				return $result;
			}
		}

		// Fetch Trainee Block F -2
		$result = $this->fetch_trainee_f2($filter);
		if ($result < 0) {
			return $result;
		}

		// Contruct header (column name)
		$array_column_header = array();
		$array_column_header[0][1] = array(
				'type' => 'text',
				'title' => $this->outputlangs->transnoentities('AgfReportBPFChaperF2')
		);

		$array_column_header[0][2] = array(
				'type' => 'int',
				'title' => $this->outputlangs->transnoentities('AgfReportBPFNbPart')
		);
		$array_column_header[0][3] = array(
				'type' => 'hours',
				'title' => $this->outputlangs->transnoentities('AgfReportBPFNbHeureSta')
		);
		// 'autosize' => 0

		$this->setArrayColumnHeader($array_column_header);

		$result = $this->write_header();
		if ($result < 0) {
			return $result;
		}

		// Ouput Lines
		$line_to_output = array();
		$array_total_output = array();
		if (is_array($this->trainee_data_f2) && count($this->trainee_data_f2) > 0) {
			foreach ($this->trainee_data_f2 as $label_type => $trainee_data) {
				$line_to_output[1] = $label_type;
				$line_to_output[2] = $trainee_data['nb'];
				$line_to_output[3] = $trainee_data['time'];
				$array_total_output[1] = 'Total';
				$array_total_output[2] += $trainee_data['nb'];
				$array_total_output[3] += $trainee_data['time'];

				$result = $this->write_line($line_to_output, 0);
				if ($result < 0) {
					return $result;
				}
			}
			$result = $this->write_line_total($array_total_output, '3d85c6');
			if ($result < 0) {
				return $result;
			}
		}

		// Fetch Trainee Block F -3
		$result = $this->fetch_trainee_f3($filter);
		if ($result < 0) {
			return $result;
		}

		// Contruct header (column name)
		$array_column_header = array();
		$array_column_header[0][1] = array(
				'type' => 'text',
				'title' => $this->outputlangs->transnoentities('AgfReportBPFChaperF3')
		);

		$array_column_header[0][2] = array(
				'type' => 'int',
				'title' => $this->outputlangs->transnoentities('AgfReportBPFNbPart')
		);
		$array_column_header[0][3] = array(
				'type' => 'hours',
				'title' => $this->outputlangs->transnoentities('AgfReportBPFNbHeureSta')
		);
		// 'autosize' => 0

		$this->setArrayColumnHeader($array_column_header);

		$result = $this->write_header();
		if ($result < 0) {
			return $result;
		}

		// Ouput Lines
		$line_to_output = array();
		$array_total_output = array();
		if (is_array($this->trainee_data_f3) && count($this->trainee_data_f3) > 0) {
			foreach ($this->trainee_data_f3 as $label_type => $trainee_data) {
				$line_to_output[1] = $label_type;
				$line_to_output[2] = $trainee_data['nb'];
				$line_to_output[3] = $trainee_data['time'];
				$array_total_output[1] = 'Total';
				$array_total_output[2] += $trainee_data['nb'];
				$array_total_output[3] += $trainee_data['time'];

				$result = $this->write_line($line_to_output, 0);
				if ($result < 0) {
					return $result;
				}
			}
			$result = $this->write_line_total($array_total_output, '3d85c6');
			if ($result < 0) {
				return $result;
			}
		}

		// Fetch Trainee Block F -4
		$result = $this->fetch_trainee_f4($filter);
		if ($result < 0) {
			return $result;
		}

		// Contruct header (column name)
		$array_column_header = array();
		$array_column_header[0][1] = array(
				'type' => 'text',
				'title' => $this->outputlangs->transnoentities('AgfReportBPFChaperF4')
		);

		$array_column_header[0][2] = array(
				'type' => 'int',
				'title' => $this->outputlangs->transnoentities('AgfReportBPFNbPart')
		);
		$array_column_header[0][3] = array(
				'type' => 'hours',
				'title' => $this->outputlangs->transnoentities('AgfReportBPFNbHeureSta')
		);
		// 'autosize' => 0

		$this->setArrayColumnHeader($array_column_header);

		$result = $this->write_header();
		if ($result < 0) {
			return $result;
		}

		// Ouput Lines
		$line_to_output = array();
		$array_total_output = array();
		if (is_array($this->trainee_data_f4) && count($this->trainee_data_f4) > 0) {
			foreach ($this->trainee_data_f4 as $label_type => $trainee_data) {
				$line_to_output[1] = $label_type;
				$line_to_output[2] = $trainee_data['nb'];
				$line_to_output[3] = $trainee_data['time'];
				$array_total_output[1] = 'Total';
				$array_total_output[2] += $trainee_data['nb'];
				$array_total_output[3] += $trainee_data['time'];

				$result = $this->write_line($line_to_output, 0);
				if ($result < 0) {
					return $result;
				}
			}
			$result = $this->write_line_total($array_total_output, '3d85c6');
			if ($result < 0) {
				return $result;
			}
		}

		// Fetch Trainee Block G
		$result = $this->fetch_trainee_g($filter);
		if ($result < 0) {
			return $result;
		}

		// Contruct header (column name)
		$array_column_header = array();
		$array_column_header[0][1] = array(
				'type' => 'text',
				'title' => $this->outputlangs->transnoentities('AgfReportBPFChaperG')
		);

		$array_column_header[0][2] = array(
				'type' => 'int',
				'title' => $this->outputlangs->transnoentities('AgfReportBPFNbPart')
		);
		$array_column_header[0][3] = array(
				'type' => 'hours',
				'title' => $this->outputlangs->transnoentities('AgfReportBPFNbHeureSta')
		);
		// 'autosize' => 0

		$this->setArrayColumnHeader($array_column_header);

		$result = $this->write_header();
		if ($result < 0) {
			return $result;
		}

		// Ouput Lines
		$line_to_output = array();
		$array_total_output = array();
		if (is_array($this->trainee_data_g) && count($this->trainee_data_g) > 0) {
			foreach ($this->trainee_data_g as $label_type => $trainee_data) {
				$line_to_output[1] = $label_type;
				$line_to_output[2] = $trainee_data['nb'];
				$line_to_output[3] = $trainee_data['time'];
				$array_total_output[1] = 'Total';
				$array_total_output[2] += $trainee_data['nb'];
				$array_total_output[3] += $trainee_data['time'];

				$result = $this->write_line($line_to_output, 0);
				if ($result < 0) {
					return $result;
				}
			}
			$result = $this->write_line_total($array_total_output, '3d85c6');
			if ($result < 0) {
				return $result;
			}
		}

		$this->close_file(0, 0, 0);
		return count($this->trainer_data) + count($this->trainee_data) + count($this->financial_data);
		// return 1;
	}

	/**
	 * Load all objects in memory from database
	 *
	 * @param array $filter output
	 * @return int <0 if KO, >0 if OK
	 */
	function fetch_trainee_f2($filter = array())
	{
		global $langs, $conf;

		$key = 'Formés par votre organisme pour son propre compte';
		if (empty($conf->global->AGF_USE_REAL_HOURS)) {
			$sql = "select sesssta.rowid, '1' as typesql,";
			if ($this->db->type == 'pgsql') {
				$sql .= "SUM(TIME_TO_SEC(TIMEDIFF('second',statime.heuref, statime.heured)))/(24*60*60) as timeinsession";
			} else {
				$sql .= "SUM(TIME_TO_SEC(TIMEDIFF(statime.heuref, statime.heured)))/(24*60*60) as timeinsession";
			}
			$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session as sess ";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_stagiaire AS sesssta ON sesssta.fk_session_agefodd=sess.rowid AND sesssta.status_in_session IN (3,4) ";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_stagiaire as sta ON sta.rowid=sesssta.fk_stagiaire ";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_calendrier as statime ON statime.fk_agefodd_session=sess.rowid ";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_formation_catalogue as formation ON formation.rowid=sess.fk_formation_catalogue AND formation.fk_c_category IS NOT NULL AND fk_c_category_bpf IS NOT NULL ";
			$sql .= " WHERE statime.heured >= '" . $this->db->idate($filter['search_date_start']) . "' AND statime.heuref <= '" . $this->db->idate($filter['search_date_end']) . "'";
			$sql .= " AND sess.status IN (5,6)";
			$sql .= " AND COALESCE(sess.fk_socpeople_presta, 0) = 0";
			$sql .= " AND COALESCE(sess.fk_soc_employer, 0) = 0";
			$sql .= " AND sess.entity IN (" . getEntity('agsession') . ")";
			$sql .= " GROUP BY sesssta.rowid";
		} else {
			$sql = "select sesssta.rowid , '2' as typesql,";
			$sql .= "SUM(assh.heures)/24 as timeinsession";
			$sql .= " FROM ".MAIN_DB_PREFIX."agefodd_session_stagiaire_heures as assh";
			$sql .= " INNER JOIN ".MAIN_DB_PREFIX."agefodd_session as sess ON sess.rowid = assh.fk_session";
			$sql .= " INNER JOIN ".MAIN_DB_PREFIX."agefodd_session_stagiaire AS sesssta ON sesssta.fk_session_agefodd=sess.rowid AND sesssta.status_in_session IN (3,4) ";
			$sql .= " AND sesssta.fk_stagiaire=assh.fk_stagiaire";
			$sql .= " INNER JOIN ".MAIN_DB_PREFIX."agefodd_stagiaire as sta ON sta.rowid=sesssta.fk_stagiaire ";
			$sql .= " INNER JOIN ".MAIN_DB_PREFIX."agefodd_session_calendrier as statime ON statime.rowid=assh.fk_calendrier ";
			$sql .= " INNER JOIN ".MAIN_DB_PREFIX."agefodd_formation_catalogue as formation ON formation.rowid=sess.fk_formation_catalogue AND formation.fk_c_category IS NOT NULL AND fk_c_category_bpf IS NOT NULL ";
			$sql .= " WHERE statime.heured >= '".$this->db->idate($filter['search_date_start'])."' AND statime.heuref <= '".$this->db->idate($filter['search_date_end'])."'";
			$sql .= " AND sess.status IN (5,6)";
			$sql .= " AND COALESCE(sess.fk_socpeople_presta, 0) = 0";
			$sql .= " AND COALESCE(sess.fk_soc_employer, 0) = 0";
			$sql .= " AND sess.entity IN (".getEntity('agsession').")";
			$sql .= " GROUP BY sesssta.rowid";
			$sql .= " ORDER BY typesql";
			dol_syslog(get_class($this)." AGF_USE_REAL_HOURS::".__METHOD__."-".$key, LOG_DEBUG);
		}
		dol_syslog(get_class($this)."::".__METHOD__."-".$key, LOG_DEBUG);

		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql)) {
				while ($obj = $this->db->fetch_object($resql)) {
					$this->trainee_data_f2[$key]['nb'][$obj->rowid] = 1;
					$this->trainee_data_f2[$key]['time'][$obj->rowid] = $obj->timeinsession;
				}
				$this->trainee_data_f2[$key]['time'] = array_sum($this->trainee_data_f2[$key]['time']);
			}
		}

		$this->db->free($resql);

		// Add time from FOAD
		$sql = "select count(DISTINCT sesssta.rowid) as cnt, SUM(sesssta.hour_foad)/24 as timeinsession ";
		$sql .= " FROM ".MAIN_DB_PREFIX."agefodd_session as sess ";
		$sql .= " INNER JOIN ".MAIN_DB_PREFIX."agefodd_session_stagiaire AS sesssta ON sesssta.fk_session_agefodd=sess.rowid AND sesssta.status_in_session IN (3,4) ";
		$sql .= " INNER JOIN ".MAIN_DB_PREFIX."agefodd_stagiaire as sta ON sta.rowid=sesssta.fk_stagiaire ";
		$sql .= " INNER JOIN ".MAIN_DB_PREFIX."agefodd_session_calendrier as statime ON statime.fk_agefodd_session=sess.rowid ";
		$sql .= " INNER JOIN ".MAIN_DB_PREFIX."agefodd_formation_catalogue as formation ON formation.rowid=sess.fk_formation_catalogue AND formation.fk_c_category IS NOT NULL AND fk_c_category_bpf IS NOT NULL ";
		$sql .= " WHERE statime.heured >= '".$this->db->idate($filter['search_date_start'])."' AND statime.heuref <= '".$this->db->idate($filter['search_date_end'])."'";
		$sql .= " AND sess.status IN (5,6)";
		$sql .= " AND COALESCE(sess.fk_socpeople_presta, 0) = 0";
		$sql .= " AND COALESCE(sess.fk_soc_employer, 0) = 0";
		$sql .= " AND COALESCE(sesssta.hour_foad, 0) <> 0";
		$sql .= " AND sess.entity IN (".getEntity('agsession').")";

		dol_syslog(get_class($this)." - FOAD ::".__METHOD__."-".$key, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql)) {
				while ($obj = $this->db->fetch_object($resql)) {
					if (array_key_exists($key, $this->trainee_data_f2) && ! empty($obj->timeinsession)) {
						$this->trainee_data_f2[$key]['time'] += $obj->timeinsession;
					} /*else {
						$this->trainee_data_f2[$key]['nb'] = $obj->cnt;
						$this->trainee_data_f2[$key]['time'] = $obj->timeinsession;
					}*/
				}
			}
		} else {
			$this->error = "Error ".$this->db->lasterror();
			dol_syslog(get_class($this)."::".__METHOD__.$this->error, LOG_ERR);
			return -1;
		}
		$this->db->free($resql);

		$key = 'Formés par votre organisme pour le compte d’un autre organisme';
		if (empty($conf->global->AGF_USE_REAL_HOURS)) {
			$sql = "select DISTINCT sesssta.rowid , '1' as typesql,";
			if ($this->db->type == 'pgsql') {
				$sql .= "SUM(TIME_TO_SEC(TIMEDIFF('second',statime.heuref, statime.heured)))/(24*60*60) as timeinsession";
			} else {
				$sql .= "SUM(TIME_TO_SEC(TIMEDIFF(statime.heuref, statime.heured)))/(24*60*60) as timeinsession";
			}
			$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session as sess ";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_stagiaire AS sesssta ON sesssta.fk_session_agefodd=sess.rowid AND sesssta.status_in_session IN (3,4) ";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_stagiaire as sta ON sta.rowid=sesssta.fk_stagiaire ";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_calendrier as statime ON statime.fk_agefodd_session=sess.rowid ";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_formation_catalogue as formation ON formation.rowid=sess.fk_formation_catalogue AND formation.fk_c_category IS NOT NULL AND fk_c_category_bpf IS NOT NULL ";
			$sql .= " WHERE statime.heured >= '" . $this->db->idate($filter['search_date_start']) . "' AND statime.heuref <= '" . $this->db->idate($filter['search_date_end']) . "'";
			$sql .= " AND sess.status IN (5,6)";
			$sql .= " AND COALESCE(sess.fk_socpeople_presta, 0) = 0";
			$sql .= " AND COALESCE(sess.fk_soc_employer, 0) > 0";
			$sql .= " AND sess.entity IN (" . getEntity('agsession') . ")";
			$sql .= " GROUP BY sesssta.rowid";

			dol_syslog(get_class($this) . "::" . __METHOD__ . "-" . $key, LOG_DEBUG);
		} else  {
			//$sql.= ' UNION ';
			$sql = "select sesssta.rowid , '2' as typesql,";
			$sql .= "SUM(assh.heures)/24 as timeinsession";
			$sql .= " FROM ".MAIN_DB_PREFIX."agefodd_session_stagiaire_heures as assh";
			$sql .= " INNER JOIN ".MAIN_DB_PREFIX."agefodd_session as sess ON sess.rowid = assh.fk_session";
			$sql .= " INNER JOIN ".MAIN_DB_PREFIX."agefodd_session_stagiaire AS sesssta ON sesssta.fk_session_agefodd=sess.rowid AND sesssta.status_in_session IN (3,4) ";
			$sql .= " AND sesssta.fk_stagiaire=assh.fk_stagiaire";
			$sql .= " INNER JOIN ".MAIN_DB_PREFIX."agefodd_session_calendrier as statime ON statime.rowid=assh.fk_calendrier ";
			$sql .= " INNER JOIN ".MAIN_DB_PREFIX."agefodd_formation_catalogue as formation ON formation.rowid=sess.fk_formation_catalogue AND formation.fk_c_category IS NOT NULL AND fk_c_category_bpf IS NOT NULL ";
			$sql .= " WHERE statime.heured >= '".$this->db->idate($filter['search_date_start'])."' AND statime.heuref <= '".$this->db->idate($filter['search_date_end'])."'";
			$sql .= " AND sess.status IN (5,6)";
			$sql .= " AND COALESCE(sess.fk_socpeople_presta, 0) = 0";
			$sql .= " AND COALESCE(sess.fk_soc_employer, 0) > 0";
			$sql .= " AND sess.entity IN (".getEntity('agsession').")";
			$sql .= " GROUP BY sesssta.rowid";
			$sql .= " ORDER BY typesql";

			dol_syslog(get_class($this)." AGF_USE_REAL_HOURS::".__METHOD__."-".$key, LOG_DEBUG);
		}
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql)) {
				while ($obj = $this->db->fetch_object($resql)) {
					$this->trainee_data_f2[$key]['nb'][$obj->rowid] = 1;
					$this->trainee_data_f2[$key]['time'][$obj->rowid] = $obj->timeinsession;
				}
				$this->trainee_data_f2[$key]['time'] = array_sum($this->trainee_data_f2[$key]['time']);

			}
		}
		if (!empty($this->trainee_data_f2)) {
			foreach ($this->trainee_data_f2 as &$data_f2) {
				$data_f2['nb'] = count($data_f2['nb']);
				/*if (!empty($data_f2['nb'])) {
					$data_f2['time'] = $data_f2['time'] / $data_f2['nb'];
				}*/
			}
		}
		$this->db->free($resql);

		// Add time from FOAD
		$sql = "select count(DISTINCT sesssta.rowid) as cnt, SUM(sesssta.hour_foad)/24 as timeinsession ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session as sess ";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_stagiaire AS sesssta ON sesssta.fk_session_agefodd=sess.rowid AND sesssta.status_in_session IN (3,4) ";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_stagiaire as sta ON sta.rowid=sesssta.fk_stagiaire ";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_calendrier as statime ON statime.fk_agefodd_session=sess.rowid ";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_formation_catalogue as formation ON formation.rowid=sess.fk_formation_catalogue AND formation.fk_c_category IS NOT NULL AND fk_c_category_bpf IS NOT NULL ";
		$sql .= " WHERE statime.heured >= '" . $this->db->idate($filter['search_date_start']) . "' AND statime.heuref <= '" . $this->db->idate($filter['search_date_end']) . "'";
		$sql .= " AND sess.status IN (5,6)";
		$sql .= " AND COALESCE(sesssta.hour_foad, 0) <> 0";
		$sql .= " AND COALESCE(sess.fk_socpeople_presta, 0) = 0";
		$sql .= " AND COALESCE(sess.fk_soc_employer, 0) > 0";
		$sql .= " AND sess.entity IN (".getEntity('agsession').")";

		dol_syslog(get_class($this) . " - FOAD ::" . __METHOD__, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql)) {
				while ( $obj = $this->db->fetch_object($resql) ) {
					if (array_key_exists($key, $this->trainee_data_f2) && !empty($obj->timeinsession)) {
						$this->trainee_data_f2[$key]['time'] += $obj->timeinsession;
					} /*else {
						$this->trainee_data_f2[$key]['nb'] = $obj->cnt;
						$this->trainee_data_f2[$key]['time'] = $obj->timeinsession;
					}*/
				}
			}
		} else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::" . __METHOD__ . $this->error, LOG_ERR);
			return - 1;
		}
		$this->db->free($resql);
	}

	/**
	 * Load all objects in memory from database
	 *
	 * @param array $filter output
	 * @return int <0 if KO, >0 if OK
	 */
	function fetch_trainee_f3($filter = array())
	{
		global $langs, $conf;
		if (empty($conf->global->AGF_USE_REAL_HOURS)) {
			$sql = "select catform.intitule, sesssta.rowid, '1' as typesql, ";
			if ($this->db->type == 'pgsql') {
				$sql .= "SUM(TIME_TO_SEC(TIMEDIFF('second',statime.heuref, statime.heured)))/(24*60*60) as timeinsession";
			} else {
				$sql .= "SUM(TIME_TO_SEC(TIMEDIFF(statime.heuref, statime.heured)))/(24*60*60) as timeinsession";
			}
			$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session as sess ";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_stagiaire AS sesssta ON sesssta.fk_session_agefodd=sess.rowid AND sesssta.status_in_session IN (3,4) ";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_stagiaire as sta ON sta.rowid=sesssta.fk_stagiaire ";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_calendrier as statime ON statime.fk_agefodd_session=sess.rowid ";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_formation_catalogue as formation ON (formation.rowid=sess.fk_formation_catalogue AND formation.fk_c_category IS NOT NULL AND fk_c_category_bpf IS NOT NULL) ";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_formation_catalogue_type_bpf as catform ON catform.rowid=formation.fk_c_category_bpf ";
			$sql .= " WHERE statime.heured >= '" . $this->db->idate($filter['search_date_start']) . "' AND statime.heuref <= '" . $this->db->idate($filter['search_date_end']) . "'";
			$sql .= " AND COALESCE(sess.fk_socpeople_presta, 0) = 0";
			$sql .= " AND sess.status IN (5,6)";
			$sql .= " AND sess.entity IN (" . getEntity('agsession') . ")";
			$sql .= " GROUP BY catform.intitule, sesssta.rowid";
		} else {
			//$sql .= " UNION ";
			$sql = "select catform.intitule, sesssta.rowid,  '2' as typesql, ";
			$sql .= "SUM(assh.heures)/24 as timeinsession";
			$sql .= " FROM ".MAIN_DB_PREFIX."agefodd_session_stagiaire_heures as assh";
			$sql .= " INNER JOIN ".MAIN_DB_PREFIX."agefodd_session as sess ON sess.rowid = assh.fk_session";
			$sql .= " INNER JOIN ".MAIN_DB_PREFIX."agefodd_session_stagiaire AS sesssta ON sesssta.fk_session_agefodd=sess.rowid AND sesssta.status_in_session IN (3,4)";
			$sql .= " AND sesssta.fk_stagiaire=assh.fk_stagiaire";
			$sql .= " INNER JOIN ".MAIN_DB_PREFIX."agefodd_stagiaire as sta ON sta.rowid=sesssta.fk_stagiaire ";
			$sql .= " INNER JOIN ".MAIN_DB_PREFIX."agefodd_session_calendrier as statime ON statime.rowid=assh.fk_calendrier ";
			$sql .= " INNER JOIN ".MAIN_DB_PREFIX."agefodd_formation_catalogue as formation ON formation.rowid=sess.fk_formation_catalogue AND formation.fk_c_category IS NOT NULL AND fk_c_category_bpf IS NOT NULL";
			$sql .= " INNER JOIN ".MAIN_DB_PREFIX."agefodd_formation_catalogue_type_bpf as catform ON catform.rowid=formation.fk_c_category_bpf ";
			$sql .= " WHERE statime.heured >= '".$this->db->idate($filter['search_date_start'])."' AND statime.heuref <= '".$this->db->idate($filter['search_date_end'])."'";
			$sql .= " AND sess.status IN (5,6)";
			$sql .= " AND COALESCE(sess.fk_socpeople_presta, 0) = 0";
			$sql .= " AND sess.entity IN (".getEntity('agsession').")";
			$sql .= " GROUP BY catform.intitule,  sesssta.rowid";
			$sql .= " ORDER BY typesql";

			dol_syslog(get_class($this)." AGF_USE_REAL_HOURS::".__METHOD__, LOG_DEBUG);
		}
		dol_syslog(get_class($this)."::".__METHOD__, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql)) {
				while ($obj = $this->db->fetch_object($resql)) {
					$this->trainee_data_f3[$obj->intitule]['nb'][$obj->rowid] = 1;
					$this->trainee_data_f3[$obj->intitule]['time'][$obj->rowid] = $obj->timeinsession;
				}

				foreach ($this->trainee_data_f3 as &$data_f3) {
					$data_f3['nb'] = count($data_f3['nb']);
					$data_f3['time'] = array_sum($data_f3['time']);
					/*if (!empty($data_f3['nb'])) {
						$data_f3['time'] = $data_f3['time'] / $data_f3['nb'];
					}*/
				}
			}
		}
		$this->db->free($resql);

		// Add time from FOAD

		$sql = "select SUM(sesssta.hour_foad)/24 as timeinsession, catform.intitule ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session as sess ";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_stagiaire AS sesssta ON sesssta.fk_session_agefodd=sess.rowid AND sesssta.status_in_session IN (3,4) ";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_stagiaire as sta ON sta.rowid=sesssta.fk_stagiaire ";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_calendrier as statime ON statime.fk_agefodd_session=sess.rowid ";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_formation_catalogue as formation ON formation.rowid=sess.fk_formation_catalogue ";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_formation_catalogue_type_bpf as catform ON catform.rowid=formation.fk_c_category_bpf ";
		$sql .= " WHERE statime.heured >= '" . $this->db->idate($filter['search_date_start']) . "' AND statime.heuref <= '" . $this->db->idate($filter['search_date_end']) . "'";
		$sql .= " AND sess.status IN (5,6)";
		$sql .= " AND COALESCE(sess.fk_socpeople_presta, 0) = 0";
		$sql .= " AND COALESCE(sesssta.hour_foad, 0) <> 0";
		$sql .= " AND sess.entity IN (".getEntity('agsession').")";
		$sql .= " GROUP BY catform.intitule";

		dol_syslog(get_class($this) . " - FOAD ::" . __METHOD__, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql)) {
				while ( $obj = $this->db->fetch_object($resql) ) {
					if (array_key_exists($obj->intitule, $this->trainee_data_f3)  && !empty($obj->timeinsession)) {
						$this->trainee_data_f3[$obj->intitule]['time'] += $obj->timeinsession;
					} /*else {
						$this->trainee_data_f3[$obj->intitule]['nb'] = $obj->cnt;
						$this->trainee_data_f3[$obj->intitule]['time'] = $obj->timeinsession;
					}*/
				}
			}
		} else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::" . __METHOD__ . $this->error, LOG_ERR);
			return - 1;
		}
		$this->db->free($resql);
	}

	/**
	 * Load all objects in memory from database
	 *
	 * @param array $filter output
	 * @return int <0 if KO, >0 if OK
	 */
	function fetch_trainee_f4($filter = array())
	{
		global $langs, $conf;
		//TODO typesql

		if (empty($conf->global->AGF_USE_REAL_HOURS)) {
			$sql = "select CONCAT(catform.code , '-', catform.intitule) as intitule, '1' as typesql,";
			if ($this->db->type == 'pgsql') {
				$sql .= "SUM(TIME_TO_SEC(TIMEDIFF('second',statime.heuref, statime.heured)))/(24*60*60) as timeinsession";
			} else {
				$sql .= "SUM(TIME_TO_SEC(TIMEDIFF(statime.heuref, statime.heured)))/(24*60*60) as timeinsession";
			}
			$sql .= "  ,sesssta.rowid";
			$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session as sess ";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_stagiaire AS sesssta ON sesssta.fk_session_agefodd=sess.rowid AND sesssta.status_in_session IN (3,4) ";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_stagiaire as sta ON sta.rowid=sesssta.fk_stagiaire ";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_calendrier as statime ON statime.fk_agefodd_session=sess.rowid ";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_formation_catalogue as formation ON formation.rowid=sess.fk_formation_catalogue AND formation.fk_c_category IS NOT NULL AND fk_c_category_bpf IS NOT NULL";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_formation_catalogue_type as catform ON catform.rowid=formation.fk_c_category ";
			$sql .= " WHERE statime.heured >= '" . $this->db->idate($filter['search_date_start']) . "' AND statime.heuref <= '" . $this->db->idate($filter['search_date_end']) . "'";
			$sql .= " AND sess.status IN (5,6)";
			$sql .= " AND COALESCE(sess.fk_socpeople_presta, 0) = 0";
			$sql .= " AND sess.entity IN (" . getEntity('agsession') . ")";
			$sql .= " GROUP BY CONCAT(catform.code , '-', catform.intitule), sesssta.rowid";
		} else {
			$sql = "select CONCAT(catform.code , '-', catform.intitule) as intitule, '2' as typesql,";
			$sql .= "SUM(assh.heures)/24 as timeinsession";
			$sql .= " ,sesssta.rowid";
			$sql .= " FROM ".MAIN_DB_PREFIX."agefodd_session_stagiaire_heures as assh";
			$sql .= " INNER JOIN ".MAIN_DB_PREFIX."agefodd_session as sess ON sess.rowid = assh.fk_session";
			$sql .= " INNER JOIN ".MAIN_DB_PREFIX."agefodd_session_stagiaire AS sesssta ON sesssta.fk_session_agefodd=sess.rowid AND sesssta.status_in_session IN (3,4)";
			$sql .= " AND sesssta.fk_stagiaire=assh.fk_stagiaire";
			$sql .= " INNER JOIN ".MAIN_DB_PREFIX."agefodd_session_calendrier as statime ON statime.rowid=assh.fk_calendrier ";
			$sql .= " INNER JOIN ".MAIN_DB_PREFIX."agefodd_formation_catalogue as formation ON formation.rowid=sess.fk_formation_catalogue AND formation.fk_c_category IS NOT NULL AND fk_c_category_bpf IS NOT NULL";
			$sql .= " INNER JOIN ".MAIN_DB_PREFIX."agefodd_formation_catalogue_type as catform ON catform.rowid=formation.fk_c_category ";
			$sql .= " INNER JOIN ".MAIN_DB_PREFIX."agefodd_stagiaire as sta ON sta.rowid=sesssta.fk_stagiaire ";
			$sql .= " WHERE statime.heured >= '".$this->db->idate($filter['search_date_start'])."' AND statime.heuref <= '".$this->db->idate($filter['search_date_end'])."'";
			$sql .= " AND sess.status IN (5,6)";
			$sql .= " AND COALESCE(sess.fk_socpeople_presta, 0) = 0";
			$sql .= " AND sess.entity IN (".getEntity('agsession').")";
			$sql .= " GROUP BY CONCAT(catform.code , '-', catform.intitule), sesssta.rowid";
			$sql .= " ORDER BY typesql";
			dol_syslog(get_class($this)." AGF_USE_REAL_HOURS::".__METHOD__, LOG_DEBUG);
		}

		$resql = $this->db->query($sql);

		if ($resql) {
			if ($this->db->num_rows($resql)) {
				while ($obj = $this->db->fetch_object($resql)) {
					$this->trainee_data_f4[$obj->intitule]['nb'][$obj->rowid] = 1; // Y a tjr des duplicatas
					$this->trainee_data_f4[$obj->intitule]['time'][$obj->rowid] = $obj->timeinsession;
				}
				foreach ($this->trainee_data_f4 as &$data_f4) {
					$data_f4['nb'] = count($data_f4['nb']);
					$data_f4['time'] = array_sum($data_f4['time']);
					/*if (!empty($data_f4['nb'])) {
						$data_f4['time'] = $data_f4['time'] / $data_f4['nb'];
					}*/
				}
			}
		}
		$this->db->free($resql);
		// Add time from FOAD
		$sql = "select count(DISTINCT sesssta.rowid) as cnt ,SUM(sesssta.hour_foad)/24 as timeinsession,CONCAT(catform.code , '-', catform.intitule) as intitule ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session as sess ";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_stagiaire AS sesssta ON sesssta.fk_session_agefodd=sess.rowid AND sesssta.status_in_session IN (3,4) ";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_stagiaire as sta ON sta.rowid=sesssta.fk_stagiaire ";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_calendrier as statime ON statime.fk_agefodd_session=sess.rowid ";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_formation_catalogue as formation ON formation.rowid=sess.fk_formation_catalogue ";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_formation_catalogue_type as catform ON catform.rowid=formation.fk_c_category ";
		$sql .= " WHERE statime.heured >= '" . $this->db->idate($filter['search_date_start']) . "' AND statime.heuref <= '" . $this->db->idate($filter['search_date_end']) . "'";
		$sql .= " AND sess.status IN (5,6)";
		$sql .= " AND COALESCE(sesssta.hour_foad, 0) <> 0";
		$sql .= " AND COALESCE(sess.fk_socpeople_presta, 0) = 0";
		$sql .= " AND sess.entity IN (".getEntity('agsession').")";
		$sql .= " GROUP BY CONCAT(catform.code , '-', catform.intitule)";

		dol_syslog(get_class($this) . " - FOAD ::" . __METHOD__, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql)) {
				while ( $obj = $this->db->fetch_object($resql) ) {
					if (array_key_exists($obj->intitule, $this->trainee_data_f4) && !empty($obj->timeinsession) ) {
						$this->trainee_data_f4[$obj->intitule]['time'] += $obj->timeinsession;
					} /*else {
						$this->trainee_data_f4[$obj->intitule]['nb'] = $obj->cnt;
						$this->trainee_data_f4[$obj->intitule]['time'] = $obj->timeinsession;
					}*/
				}
			}
		} else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::" . __METHOD__ . $this->error, LOG_ERR);
			return - 1;
		}
		$this->db->free($resql);
	}

	/**
	 * G: stagiaires dont la formation a été confiée à votre organisme par un autre organisme de formation
	 *  - nombre de stagiaires et apprentis
	 *  - nombre total d'heures de formation suivies par les stagiaires et apprentis
	 *
	 * @param array $filter output
	 * @return int <0 if KO, >0 if OK
	 */
	function fetch_trainee_g($filter = array())
	{
		global $langs, $conf;

		$key = 'Formations confiées par votre organisme à un autre organisme de formation';
		$sql = "SELECT count(DISTINCT sesssta.rowid) as cnt, ";
		if ($this->db->type == 'pgsql') {
			$sql .= " SUM(TIME_TO_SEC(TIMEDIFF('second',statime.heuref, statime.heured)))/(24*60*60) as timeinsession ";
		} else {
			$sql .= " SUM(TIME_TO_SEC(TIMEDIFF(statime.heuref, statime.heured)))/(24*60*60) as timeinsession ";
		}
		$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session as sess ";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_stagiaire AS sesssta ON sesssta.fk_session_agefodd=sess.rowid AND sesssta.status_in_session IN (3,4) ";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_stagiaire as sta ON sta.rowid=sesssta.fk_stagiaire ";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_calendrier as statime ON statime.fk_agefodd_session=sess.rowid ";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_formation_catalogue as formation ON formation.rowid=sess.fk_formation_catalogue AND formation.fk_c_category IS NOT NULL AND fk_c_category_bpf IS NOT NULL ";
		$sql .= " WHERE statime.heured >= '" . $this->db->idate($filter['search_date_start']) . "' AND statime.heuref <= '" . $this->db->idate($filter['search_date_end']) . "'";
		$sql .= " AND sess.status IN (5,6)";
		$sql .= " AND COALESCE(sess.fk_socpeople_presta, 0) > 0";
		$sql .= " AND sess.entity IN (".getEntity('agsession').")";

		dol_syslog(get_class($this) . "::" . __METHOD__. ' '.$key, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql)) {
				while ( $obj = $this->db->fetch_object($resql) ) {
					$this->trainee_data_g[$key]['nb'] = $obj->cnt;
					$this->trainee_data_g[$key]['time'] = $obj->timeinsession;
				}
			}
			if (!empty($conf->global->AGF_USE_REAL_HOURS)) {
				$sql = "SELECT count(DISTINCT assh.fk_stagiaire) as cnt , ";
				$sql .= "SUM(assh.heures)/24 as timeinsession";
				$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session_stagiaire_heures as assh";
				$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session as sess ON sess.rowid = assh.fk_session";
				$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_calendrier as statime ON statime.rowid=assh.fk_calendrier ";
				$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_formation_catalogue as formation ON formation.rowid=sess.fk_formation_catalogue AND formation.fk_c_category IS NOT NULL AND fk_c_category_bpf IS NOT NULL ";
				$sql .= " WHERE statime.heured >= '" . $this->db->idate($filter['search_date_start']) . "' AND statime.heuref <= '" . $this->db->idate($filter['search_date_end']) . "'";
				$sql .= " AND sess.status IN (5,6)";
				$sql .= " AND COALESCE(sess.fk_socpeople_presta, 0) > 0";
				$sql .= " AND sess.entity IN (".getEntity('agsession').")";

				dol_syslog(get_class($this) . " AGF_USE_REAL_HOURS::" . __METHOD__, LOG_DEBUG);
				$resql2 = $this->db->query($sql);
				if ($resql2) {
					$num=$this->db->num_rows($resql);
					if (empty($num)) {
						$this->trainee_data_g[$key]['nb'] = 0;
						$this->trainee_data_g[$key]['time'] = 0;
					}
					if ($this->db->num_rows($resql2)) {
						while ($obj = $this->db->fetch_object($resql2)) {
							$this->trainee_data_g[$key]['nb'] += $obj->cnt;
							$this->trainee_data_g[$key]['time'] += $obj->timeinsession;
						}
					}
				}
			}
		} else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::" . __METHOD__ . $this->error, LOG_ERR);
			return - 1;
		}
		$this->db->free($resql);

		// Add time from FOAD
		$sql = "SELECT count(DISTINCT sesssta.rowid) as cnt ,SUM(sesssta.hour_foad)/24 as timeinsession ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session as sess ";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_stagiaire AS sesssta ON sesssta.fk_session_agefodd=sess.rowid AND sesssta.status_in_session IN (3,4) ";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_stagiaire as sta ON sta.rowid=sesssta.fk_stagiaire ";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_calendrier as statime ON statime.fk_agefodd_session=sess.rowid ";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_formation_catalogue as formation ON formation.rowid=sess.fk_formation_catalogue AND formation.fk_c_category IS NOT NULL AND fk_c_category_bpf IS NOT NULL ";
		$sql .= " WHERE statime.heured >= '" . $this->db->idate($filter['search_date_start']) . "' AND statime.heuref <= '" . $this->db->idate($filter['search_date_end']) . "'";
		$sql .= " AND sess.status IN (5,6)";
		$sql .= " AND COALESCE(sess.fk_socpeople_presta, 0) > 0";
		$sql .= " AND COALESCE(sesssta.hour_foad, 0) <> 0";
		$sql .= " AND sess.entity IN (".getEntity('agsession').")";

		dol_syslog(get_class($this) . " - FOAD ::" . __METHOD__, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql)) {
				while ( $obj = $this->db->fetch_object($resql) ) {
					if (array_key_exists($key, $this->trainee_data_g)  && !empty($obj->timeinsession)) {
						$this->trainee_data_g[$key]['time'] += $obj->timeinsession;
					}/* else {
						$this->trainee_data_g[$key]['nb'] = $obj->cnt;
						$this->trainee_data_g[$key]['time'] = $obj->timeinsession;
					}*/
				}
			}
		} else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::" . __METHOD__ . $this->error, LOG_ERR);
			return - 1;
		}
		$this->db->free($resql);
	}

	/**
	 * Pour chaque catégorie de formateur, calcule le nombre de formateurs et le temps total de formation (en jours).
	 *   - $this->trainer_data[$obj->intitule]['nb']
	 *   - $this->trainer_data[$obj->intitule]['time']
	 *
	 * @param array $filter output
	 * @return int <0 if KO, >0 if OK
	 */
	function fetch_trainer($filter = array())
	{
		global $langs, $conf;

		// For Nb Trainer
		$sql = "select count(DISTINCT form.rowid) as cnt, fromtype.intitule, ";
		if ($this->db->type == 'pgsql') {
			$sql .= "SUM(TIME_TO_SEC(TIMEDIFF('second',formtime.heuref, formtime.heured)))/(24*60*60) as timeinsession";
		} else {
			$sql .= "SUM(TIME_TO_SEC(TIMEDIFF(formtime.heuref, formtime.heured)))/(24*60*60) as timeinsession";
		}
		$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session as sess";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_formateur AS sessform ON sessform.fk_session=sess.rowid AND sessform.trainer_status IN (3,4)";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_formateur_type as fromtype ON fromtype.rowid=sessform.fk_agefodd_formateur_type";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_formateur as form ON form.rowid=sessform.fk_agefodd_formateur";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_formateur_calendrier as formtime ON formtime.fk_agefodd_session_formateur=sessform.rowid";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_formation_catalogue as formation ON formation.rowid=sess.fk_formation_catalogue AND formation.fk_c_category IS NOT NULL AND fk_c_category_bpf IS NOT NULL ";
		$sql .= " WHERE formtime.heured >= '" . $this->db->idate($filter['search_date_start']) . "' AND formtime.heuref <= '" . $this->db->idate($filter['search_date_end']) . "'";
		$sql .= " AND sess.status IN (5,6)";
		$sql .= " AND sess.rowid IN (SELECT DISTINCT fk_session_agefodd FROM " . MAIN_DB_PREFIX . "agefodd_session_stagiaire)";
		$sql .= " AND formtime.status <> '-1'"; // ne pas compter les heures des créneaux annulés
		$sql .= " AND sess.entity IN (".getEntity('agsession').")";
		$sql .= " GROUP BY fromtype.intitule";

		dol_syslog(get_class($this) . "::" . __METHOD__, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql)) {
				while ( $obj = $this->db->fetch_object($resql) ) {
					$this->trainer_data[$obj->intitule]['nb'] = $obj->cnt;
					$this->trainer_data[$obj->intitule]['time'] = $obj->timeinsession;
				}
			}
		} else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::" . __METHOD__ . $this->error, LOG_ERR);
			return - 1;
		}
		$this->db->free($resql);
		return 1;
	}

	/**
	 * Calcule le nombre de stagiaires et le temps passé en formation (TODO détailler la PHPDoc)
	 *
	 * @param array $filter output
	 * @return int <0 if KO, >0 if OK
	 */
	function fetch_trainee($filter = array())
	{
		global $langs, $conf;
		$array_data = array(
				array(
						'label' => 'a-Salariés d’employeurs privés hors apprentis',
						'idtype' => '1,2,7,5,4'
				),
				array(
						'label' => 'b-Apprentis',
						'idtype' => '18'
				),
				array(
						'label' => 'c-Personnes en recherche d’emploi formées par votre organisme de formation',
						'idtype' => '17'
				),
				array(
						'label' => 'd-Particuliers à leurs propres frais formés par votre organisme de formation',
						'idtype' => '15'
				),
				array(
						'label' => 'e-Autres stagiaires',
						'idtype' => '6,8,9,10,11,12,13,14,16,0,20,3,19'
				)
		);

		foreach ($array_data as $key => $data) {
			if (empty($conf->global->AGF_USE_REAL_HOURS)) {
				$sql = "select sesssta.rowid, '1' as typesql,";
				if ($this->db->type == 'pgsql') {
					$sql .= "SUM(TIME_TO_SEC(TIMEDIFF('second',statime.heuref, statime.heured)))/(24*60*60) as timeinsession";
				} else {
					$sql .= "SUM(TIME_TO_SEC(TIMEDIFF(statime.heuref, statime.heured)))/(24*60*60) as timeinsession";
				}
				$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session as sess ";
				$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_stagiaire AS sesssta ON sesssta.fk_session_agefodd=sess.rowid AND sesssta.status_in_session IN (3,4) ";
				$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_stagiaire as sta ON sta.rowid=sesssta.fk_stagiaire ";
				$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_calendrier as statime ON statime.fk_agefodd_session=sess.rowid ";
				$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_formation_catalogue as formation ON formation.rowid=sess.fk_formation_catalogue AND formation.fk_c_category IS NOT NULL AND fk_c_category_bpf IS NOT NULL ";
				$sql .= " WHERE statime.heured >= '" . $this->db->idate($filter['search_date_start']) . "' AND statime.heuref <= '" . $this->db->idate($filter['search_date_end']) . "'";
				$sql .= " AND sess.status IN (5,6)";
				$sql .= " AND COALESCE(sess.fk_socpeople_presta, 0) = 0";
				if (!empty($data['idtype'])) {
					$sql .= " AND COALESCE(sesssta.fk_agefodd_stagiaire_type, 0) IN (" . $data['idtype'] . ") ";
				}
				$sql .= " AND sess.entity IN (" . getEntity('agsession') . ")";
				$sql .= 'GROUP BY sesssta.rowid';
			} else {
				$sql = "select sesssta.rowid , '2' as typesql,";
				$sql .= "SUM(assh.heures)/24 as timeinsession";
				$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session_stagiaire_heures as assh";
				$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session as sess ON sess.rowid = assh.fk_session";
				$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_stagiaire AS sesssta ON sesssta.fk_session_agefodd=sess.rowid AND sesssta.status_in_session IN (3,4) AND sesssta.fk_stagiaire=assh.fk_stagiaire";
				$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_calendrier as statime ON statime.rowid=assh.fk_calendrier ";
				$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_formation_catalogue as formation ON formation.rowid=sess.fk_formation_catalogue AND formation.fk_c_category IS NOT NULL AND fk_c_category_bpf IS NOT NULL ";
				$sql .= " WHERE statime.heured >= '" . $this->db->idate($filter['search_date_start']) . "' AND statime.heuref <= '" . $this->db->idate($filter['search_date_end']) . "'";
				$sql .= " AND sess.status IN (5,6)";
				$sql .= " AND sess.entity IN (".getEntity('agsession').")";
				$sql .= " AND COALESCE(sess.fk_socpeople_presta, 0) = 0";
				if (! empty($data['idtype'])) {
					$sql .= " AND COALESCE(sesssta.fk_agefodd_stagiaire_type, 0) IN (" . $data['idtype'] . ") ";
				}
				$sql .= 'GROUP BY sesssta.rowid';
				$sql .= ' ORDER BY typesql';
				dol_syslog(get_class($this) . "AGF_USE_REAL_HOURS::" . __METHOD__ . ' ' . $data['label'], LOG_DEBUG);
			}
			$total_cnt = 0;
			$total_timeinsession = 0;

			dol_syslog(get_class($this) . "::" . __METHOD__ . ' ' . $data['label'], LOG_DEBUG);


			$resql = $this->db->query($sql);
			if ($resql) {
				if ($this->db->num_rows($resql)) {
					while ($obj = $this->db->fetch_object($resql)) {
						$this->trainee_data[$data['label']]['nb'][$obj->rowid] = 1;
						$this->trainee_data[$data['label']]['time'][$obj->rowid] = $obj->timeinsession;
						$total_cnt ++;
						$total_timeinsession += $obj->timeinsession;
					}
					if ($data['idtype'] != '1,2,7,5,4') $this->trainee_data[$data['label']]['nb'] = count($this->trainee_data[$data['label']]['nb']);
					$this->trainee_data[$data['label']]['time'] = array_sum($this->trainee_data[$data['label']]['time']);
					/*if (!empty($this->trainee_data[$data['label']]['nb']) && !is_array($this->trainee_data[$data['label']]['nb'])) {
						$this->trainee_data[$data['label']]['time'] = $this->trainee_data[$data['label']]['time'] / $this->trainee_data[$data['label']]['nb'];
					} elseif (!empty($this->trainee_data[$data['label']]['nb']) && is_array($this->trainee_data[$data['label']]['nb'])) {
						$this->trainee_data[$data['label']]['time'] = $this->trainee_data[$data['label']]['time'] / count($this->trainee_data[$data['label']]['nb']);
					}*/
				}
			}else {
				$this->error = "Error " . $this->db->lasterror();
				dol_syslog(get_class($this) . "::" . __METHOD__ . $this->error, LOG_ERR);
				return - 1;
			}
			$this->db->free($resql);



			// Add time from FOAD
			$sql = "select  COUNT(DISTINCT sesssta.rowid) AS cnt,
                SUM(sesssta.hour_foad) / 24 AS timeinsession ";
			$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session as sess ";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_stagiaire as sesssta ON sesssta.fk_session_agefodd=sess.rowid AND sesssta.status_in_session IN (3,4) ";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_stagiaire as sta ON sta.rowid=sesssta.fk_stagiaire ";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_calendrier as statime ON statime.fk_agefodd_session=sess.rowid ";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_formation_catalogue as formation ON formation.rowid=sess.fk_formation_catalogue AND formation.fk_c_category IS NOT NULL AND fk_c_category_bpf IS NOT NULL ";
			$sql .= " WHERE statime.heured >= '" . $this->db->idate($filter['search_date_start']) . "' AND statime.heuref <= '" . $this->db->idate($filter['search_date_end']) . "'";
			$sql .= " AND sess.status IN (5,6)";
			$sql .= " AND sess.entity IN (".getEntity('agsession').")";
			if (! empty($data['idtype'])) {
				$sql .= " AND COALESCE(sesssta.fk_agefodd_stagiaire_type, 0) IN (" . $data['idtype'] . ") ";
			}
			$sql .= " AND COALESCE(sess.fk_socpeople_presta, 0) = 0";
			$sql .= " AND COALESCE(sesssta.hour_foad, 0) <> 0";

			dol_syslog(get_class($this) . " - FOAD ::" . __METHOD__ . ' ' . $data['label'], LOG_DEBUG);
			$resql = $this->db->query($sql);
			if ($resql) {
				if ($this->db->num_rows($resql)) {
					while ( $obj = $this->db->fetch_object($resql) ) {
						if (array_key_exists($data['label'], $this->trainee_data) && !empty($obj->timeinsession)) {
							$this->trainee_data[$data['label']]['time'] += $obj->timeinsession;
							$total_timeinsession += $obj->timeinsession;
						}
					}
				}
			} else {
				$this->error = "Error " . $this->db->lasterror();
				dol_syslog(get_class($this) . "::" . __METHOD__ . $this->error, LOG_ERR);
				return - 1;
			}
			$this->db->free($resql);

			if ($data['idtype'] == '1,2,7,5,4') {
				//              //Ajout des heures forcer
				// Ca sort des valeurs qui sont déjà sortis, je ne comprends pas l'utilité de cette requête.
				//              if ($this->db->type == 'pgsql') {
				//                  $sql = "SELECT SUM(TIME_TO_SEC(TIMEDIFF('second', statime.heuref, statime.heured))) / (24 * 60 * 60) AS timeinsession ";
				//              } else {
				//                  $sql = "SELECT SUM(TIME_TO_SEC(TIMEDIFF(statime.heuref, statime.heured))) / (24 * 60 * 60) AS timeinsession ";
				//              }
				//              $sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session AS sess ";
				//              $sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_calendrier AS statime ON statime.fk_agefodd_session = sess.rowid ";
				//              $sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_formation_catalogue as formation ON formation.rowid=sess.fk_formation_catalogue AND formation.fk_c_category IS NOT NULL AND fk_c_category_bpf IS NOT NULL ";
				//              $sql .= " WHERE statime.heured >= '" . $this->db->idate($filter['search_date_start']) . "' AND statime.heuref <= '" . $this->db->idate($filter['search_date_end']) . "'";
				//              $sql .= " AND sess.status IN (5,6) ";
				//                $sql .= " AND COALESCE(sess.fk_socpeople_presta, 0) = 0";
				//              $sql .= " AND sess.force_nb_stagiaire=1 ";
				//              $sql .= " AND sess.entity IN (".getEntity('agsession').")";
				//
				//              dol_syslog(get_class($this) . "::" . __METHOD__ . ' ' . $data['label'], LOG_DEBUG);
				//              $resql = $this->db->query($sql);
				//              if ($resql) {
				//                  if ($this->db->num_rows($resql)) {
				//                      if ($obj = $this->db->fetch_object($resql)) {
				//                          $this->trainee_data[$data['label']]['time'] += $obj->timeinsession;
				//                          $total_timeinsession += $obj->timeinsession;
				//                      }
				//                  }
				//              } else {
				//                  $this->error = "Error " . $this->db->lasterror();
				//                  dol_syslog(get_class($this) . "::" . __METHOD__ . " " . $data['label'] . " " . $this->error, LOG_ERR);
				//                  return - 1;
				//              }
				//              $this->db->free($resql);

				$sql = "SELECT sesssta.rowid ";
				$sql .= " FROM " . MAIN_DB_PREFIX . "agefodd_session AS sess ";
									$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_stagiaire AS sesssta ON sesssta.fk_session_agefodd=sess.rowid AND sesssta.status_in_session IN (3,4) ";

				$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "agefodd_formation_catalogue as formation ON formation.rowid=sess.fk_formation_catalogue AND formation.fk_c_category IS NOT NULL AND fk_c_category_bpf IS NOT NULL ";
				$sql .= " WHERE sess.rowid IN (SELECT fk_agefodd_session FROM " . MAIN_DB_PREFIX . "agefodd_session_calendrier AS statime ";
				$sql .= " 		        WHERE statime.heured >= '" . $this->db->idate($filter['search_date_start']) . "'";
				$sql .= " 		        AND statime.heuref <= '" . $this->db->idate($filter['search_date_end']) . "') ";
				$sql .= " AND sess.status IN (5,6) ";
				$sql .= " AND COALESCE(sess.fk_socpeople_presta, 0) = 0";
				$sql .= " AND sess.force_nb_stagiaire = 1";
				$sql .= " AND sess.entity IN (".getEntity('agsession').")";
				$sql .= " GROUP BY sesssta.rowid";

				dol_syslog(get_class($this) . "::" . __METHOD__ . ' ' . $data['label'], LOG_DEBUG);
				$resql = $this->db->query($sql);
				if ($resql) {
					if ($this->db->num_rows($resql)) {
						if ($obj = $this->db->fetch_object($resql)) {
							 $this->trainee_data[$data['label']]['nb'][$obj->rowid] = 1;
							$total_cnt ++;
						}
					}
				} else {
					$this->error = "Error " . $this->db->lasterror();
					dol_syslog(get_class($this) . "::" . __METHOD__ . " " . $data['label'] . " " . $this->error, LOG_ERR);
					return - 1;
				}
				$this->db->free($resql);
				if (empty($this->trainee_data[$data['label']]['nb'])) $this->trainee_data[$data['label']]['nb'] = [];
				$this->trainee_data[$data['label']]['nb'] = count($this->trainee_data[$data['label']]['nb']);
			}
			if (empty($this->trainee_data[$data['label']])) $this->trainee_data[$data['label']]['nb'] = 0;
			if (empty($data['idtype'])) {
				$this->trainee_data[$data['label']] = (!empty($obj->cnt)) ? ($obj->cnt - $total_cnt) : 0;
				$this->trainee_data[$data['label']]['time'] = (!empty($obj->timeinsession)) ? ($obj->timeinsession - $total_timeinsession) : 0;
			}
		}
	}

	/**
	 * Calcule la section C (C1 à C13): bilan financier hors taxes: origine des produits de l'organisme
	 * 19 requêtes (17 + 1 + 1) qui font toutes des sommes de lignes de factures clients
	 *
	 * @see ReportBPF::_getAmountFin() → 17 requêtes similaires paramétrées via $array_fin
	 * @see ReportBPF::_getAmountFinC11() → 1 requête
	 * @see ReportBPF::_getAmountFinC13() → 1 requête
	 *
	 * @param array $filter  Associative array: keys are names of search fields ("search_date_start",
	 *                       "search_date_end"); values are filter values (timestamps).
	 * @return int <0 if KO, >0 if OK
	 */
	function fetch_financial_c($filter = array())
	{
		global $langs, $conf;

		/*
		Détail des clés des 17 arrays de $array_fin (chacun des arrays permet de construire une requête pour calculer
		une des cases du BPF):

		- label            libellé (et clé de l'array $this->financial_data) de la section BPF concernée
		- idtypesta        liste d'ID de types de stagiaires (llx_agefodd_stagiaire_type) pour filtrage
		- confprod         nom d'une conf qui contient une liste d’ID de catégories de produits
		- confprodlabel    clé de trad du libellé de la conf confprod
		- confcust         nom d'une conf qui contient une liste d’ID de catégories de tiers
		- confcustlabel    clé de trad du libellé de la conf confcust
		- employer         bool: si oui, filtrera les lignes des factures dont le tiers est l'employeur lié à la session
		- checkOPCA        bool: si oui, filtrera les lignes des factures dont le tiers est l'OPCA de la session
		- checkPV          bool: si oui,
		- checkaltfin      bool: si oui, filtrera les lignes des factures dont le tiers est le même que celui du
						   stagiaire sur la session
		- datefac          bool: si oui, filtrera sur la date de facturation
		 */
		$array_fin = array(
			array(
				'idtypesta'     => 2,
				'confprod'      => 'AGF_CAT_BPF_PRODPEDA',
				'confprodlabel' => 'AgfReportBPFCategProdPeda',
				'label'         => 'C-1 Produits provenant des entreprises pour la formation de leurs salariés',
				'confcust'      => '',
				'employer'      => 0,
				'checkOPCA'     => 0,
				'checkPV'       => 0,
				'datefac'       => 1,
			),
			array(
				'idtypesta'     => 18,
				'confprod'      => 'AGF_CAT_BPF_PRODPEDA',
				'confprodlabel' => 'AgfReportBPFCategProdPeda',
				'label'         => 'C-a OPCA pour des formations dispensées des contrats d’apprentissage',
				'confcust'      => 'AGF_CAT_BPF_OPCA',
				'confcustlabel' => 'AgfReportBPFCategOPCA',
				'employer'      => 0,
				'checkOPCA'     => 1,
				'checkPV'       => 0,
				'datefac'       => 1,
			),
			array(
				'idtypesta'     => 1,
				'confprod'      => 'AGF_CAT_BPF_PRODPEDA',
				'confprodlabel' => 'AgfReportBPFCategProdPeda',
				'label'         => 'C-b OPCA pour des formations dispensées des contrats de professionnalisation',
				'confcust'      => 'AGF_CAT_BPF_OPCA',
				'confcustlabel' => 'AgfReportBPFCategOPCA',
				'employer'      => 0,
				'checkOPCA'     => 1,
				'checkPV'       => 0,
				'datefac'       => 1,
			),
			array(
				'idtypesta'     => 19,
				'confprod'      => 'AGF_CAT_BPF_PRODPEDA',
				'confprodlabel' => 'AgfReportBPFCategProdPeda',
				'label'         => 'C-c OPCA pour des formations dispensées de la promotion ou de la reconversion par alternance',
				'confcust'      => 'AGF_CAT_BPF_OPCA',
				'confcustlabel' => 'AgfReportBPFCategOPCA',
				'employer'      => 0,
				'checkOPCA'     => 1,
				'checkPV'       => 0,
				'datefac'       => 1,
			),
			array(
				'idtypesta'     => 7,
				'confprod'      => 'AGF_CAT_BPF_PRODPEDA',
				'confprodlabel' => 'AgfReportBPFCategProdPeda',
				'label'         => 'C-d OPCA pour des formations dispensées des congés individuels de formation et des projets de transition professionnelle',
				'confcust'      => 'AGF_CAT_BPF_OPCA',
				'confcustlabel' => 'AgfReportBPFCategOPCA',
				'employer'      => 0,
				'checkOPCA'     => 1,
				'checkPV'       => 0,
				'datefac'       => 1,
			),
			array(
				'idtypesta'     => 5,
				'confprod'      => 'AGF_CAT_BPF_PRODPEDA',
				'confprodlabel' => 'AgfReportBPFCategProdPeda',
				'label'         => 'C-e OPCA pour des formations dispensées du compte personnel de formation',
				'confcust'      => 'AGF_CAT_BPF_OPCA',
				'confcustlabel' => 'AgfReportBPFCategOPCA',
				'employer'      => 0,
				'checkOPCA'     => 1,
				'checkPV'       => 0,
				'datefac'       => 1,
			),
			array(
				'idtypesta'     => '17,3',
				'confprod'      => 'AGF_CAT_BPF_PRODPEDA',
				'confprodlabel' => 'AgfReportBPFCategProdPeda',
				'label'         => 'C-f OPCA pour des formations dispensées pour des dispositifs spécifiques pour les personnes en recherche d\'emploi',
				'confcust'      => 'AGF_CAT_BPF_OPCA',
				'confcustlabel' => 'AgfReportBPFCategOPCA',
				'employer'      => 0,
				'checkOPCA'     => 1,
				'checkPV'       => 0,
				'datefac'       => 1,
			),
			array(
				'idtypesta'     => 8,
				'confprod'      => 'AGF_CAT_BPF_PRODPEDA',
				'confprodlabel' => 'AgfReportBPFCategProdPeda',
				'label'         => 'C-g des fonds d assurance formation de non-salariés',
				'confcust'      => 'AGF_CAT_BPF_FAF',
				'confcustlabel' => 'AgfReportBPFCategFAF',
				'employer'      => 0,
				'checkOPCA'     => 1,
				'checkPV'       => 0,
				'datefac'       => 1,
			),
			array(
				'idtypesta'     => '20,4',
				'confprod'      => 'AGF_CAT_BPF_PRODPEDA',
				'confprodlabel' => 'AgfReportBPFCategProdPeda',
				'label'         => 'C-h OPCA pour des formations dispensées pour du plan de développement des compétences ou d’autres dispositifs',
				'confcust'      => 'AGF_CAT_BPF_OPCA',
				'confcustlabel' => 'AgfReportBPFCategOPCA',
				'employer'      => 0,
				'checkOPCA'     => 1,
				'checkPV'       => 0,
				'datefac'       => 1,
			),
			array(
				'idtypesta'     => 9,
				'confprod'      => 'AGF_CAT_BPF_PRODPEDA',
				'confprodlabel' => 'AgfReportBPFCategProdPeda',
				'label'         => 'C-3 Pouvoirs publics pour la formation de leurs agents (Etat, collectivités territoriales, établissements publics à caractère administratif)',
				'confcust'      => 'AGF_CAT_BPF_ADMINISTRATION',
				'confcustlabel' => 'AgfReportBPFCategAdmnistration',
				'employer'      => 0,
				'checkOPCA'     => 0,
				'checkPV'       => 0,
				'datefac'       => 1,
			),
			array(
				'idtypesta'     => 10,
				'confprod'      => 'AGF_CAT_BPF_PRODPEDA',
				'confprodlabel' => 'AgfReportBPFCategProdPeda',
				'label'         => 'C-4 Pouvoirs publics spécifiques Instances européennes',
				'confcust'      => '',
				'confcustlabel' => 'AgfReportBPFCategAdmnistration',
				'employer'      => 0,
				'checkOPCA'     => 0,
				'checkPV'       => 1,
				'datefac'       => 1,
			),
			array(
				'idtypesta'     => 11,
				'confprod'      => 'AGF_CAT_BPF_PRODPEDA',
				'confprodlabel' => 'AgfReportBPFCategProdPeda',
				'label'         => 'C-5 Pouvoirs publics spécifiques Etat',
				'confcust'      => '',
				'confcustlabel' => 'AgfReportBPFCategAdmnistration',
				'employer'      => 0,
				'checkOPCA'     => 0,
				'checkPV'       => 1,
				'datefac'       => 1,
			),
			array(
				'idtypesta'     => 12,
				'confprod'      => 'AGF_CAT_BPF_PRODPEDA',
				'confprodlabel' => 'AgfReportBPFCategProdPeda',
				'label'         => 'C-6 Pouvoirs publics spécifiques Conseils régionaux',
				'confcust'      => '',
				'confcustlabel' => 'AgfReportBPFCategAdmnistration',
				'employer'      => 0,
				'checkOPCA'     => 0,
				'checkPV'       => 1,
				'datefac'       => 1,
			),
			array(
				'idtypesta'     => 13,
				'confprod'      => 'AGF_CAT_BPF_PRODPEDA',
				'confprodlabel' => 'AgfReportBPFCategProdPeda',
				'label'         => 'C-7 Pouvoirs publics spécifiques Pôle emploi',
				'confcust'      => '',
				'confcustlabel' => 'AgfReportBPFCategAdmnistration',
				'employer'      => 0,
				'checkOPCA'     => 0,
				'checkPV'       => 1,
				'datefac'       => 1,
				'checkaltfin'   => 1
			),
			array(
				'idtypesta'     => 14,
				'confprod'      => 'AGF_CAT_BPF_PRODPEDA',
				'confprodlabel' => 'AgfReportBPFCategProdPeda',
				'label'         => 'C-8 Pouvoirs publics spécifiques Autres ressources publiques',
				'confcust'      => '',
				'confcustlabel' => 'AgfReportBPFCategAdmnistration',
				'employer'      => 0,
				'checkOPCA'     => 0,
				'checkPV'       => 1,
				'datefac'       => 1,
			),
			array(
				'idtypesta'     => 15,
				'confprod'      => 'AGF_CAT_BPF_PRODPEDA',
				'confprodlabel' => 'AgfReportBPFCategProdPeda',
				'label'         => 'C-9 Contrats conclus avec des personnes à titre individuel et à leurs frais',
				'confcust'      => 'AGF_CAT_BPF_PARTICULIER',
				'confcustlabel' => 'AgfReportBPFCategParticulier',
				'employer'      => 0,
				'checkOPCA'     => 0,
				'checkPV'       => 0,
				'datefac'       => 1,
			),
			array(
				'idtypesta'     => 16,
				'confprod'      => 'AGF_CAT_BPF_PRODPEDA',
				'confprodlabel' => 'AgfReportBPFCategProdPeda',
				'label'         => 'C-10 Contrats conclus avec d’autres organismes de formation y compris CFA',
				'confcust'      => '',
				'employer'      => 1,
				'checkOPCA'     => 0,
				'checkPV'       => 0,
				'datefac'       => 1,
			)
		);

		// Exécute toutes les requêtes (17) liées à $array_fin
		foreach ($array_fin as $key => $data) {
			$result = $this->_getAmountFin($data, $filter);
			if ($result < 0) {
				return -1;
			}
		}

		//special C - 1
		//compelete le montant avec les facture directement faite à l'emploeyr ou financeur alternatif du stagiaire
		$result = $this->_getAmountFinCHack($filter);
		if ($result < 0) {
			return -1;
		}

		// C - 11
		$result = $this->_getAmountFinC11($filter);
		if ($result < 0) {
			return -1;
		}

		// C - 13
		$result = $this->_getAmountFinC13($filter);
		if ($result < 0) {
			return -1;
		}

		//      dol_syslog(get_class($this) . "::" . __METHOD__ . ' DEBUG ALL C1 to 13 '."\n".implode(' UNION ',$sqldebugall), LOG_DEBUG);

		//      $sqlInvoicesLinesUnaccountedFor = $this->_getSQLQueryForInvoiceLinesNotInSectionC($filter);

		//      // ça, c'est la requête de contrôle qui sélectionne les factures
		//      $invoiceRef = 'factdddd.ref';
		//      if(floatval(DOL_VERSION) <= 9) $filedref = " factdddd.facnumber as ref";
		//      $sqldebugall_findinvoice = /* @lang SQL */
		//          "SELECT " . $filedref . ", factdddd.total"
		//          . " FROM " . MAIN_DB_PREFIX . "facture as factdddd"
		//          . " WHERE ("
		//          . "     factdddd.datef BETWEEN"
		//          . "          '" . $this->db->idate($filter['search_date_start']) . "'"
		//          . "          AND"
		//          . "          '" . $this->db->idate($filter['search_date_end'])."'"
		//          . "     )"
		//          . "     AND factdddd.rowid NOT IN ("
		//          . "          SELECT factinsssss.rowid"
		//          . "          FROM (".implode(' UNION ',$sqldebugall).") as factinsssss"
		//          . "     )";
		//
		//      dol_syslog(get_class($this) . "::" . __METHOD__ . ' DEBUG find invoice not in C1 to 13 '."\n".$sqldebugall_findinvoice, LOG_DEBUG);
	}

	/**
	 *
	 * Bloc financier "D": bilan financier hors taxes : charges de l'organisme, dont:
	 *  - salaires des formateurs,
	 *  - achats de prestations de formation et honoraires de formation
	 *
	 * @param array $filter output
	 * @return int <0 if KO, >0 if OK
	 */
	function fetch_financial_d($filter = array())
	{
		global $langs, $conf;
		$confNamesForWarning = array(
			'AGF_CAT_PRODUCT_CHARGES' => 'AgfCategOverheadCost',
			'AGF_CAT_BPF_PRESTA' =>      'AgfReportBPFCategPresta',
			'AGF_CAT_BPF_FEEPRESTA' =>   'AgfReportBPFCategFeePresta',
		);
		$this->_warnIfConfsMissing($confNamesForWarning);

		if (! empty($conf->global->AGF_CAT_PRODUCT_CHARGES)) {
			// Total des charges de l’organisme liées à l’activité de formation
			$sql = "SELECT SUM(facdet.total_ht) as amount ";
			$sql .= " FROM " . MAIN_DB_PREFIX . "facture_fourn as f  ";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "facture_fourn_det as facdet ON facdet.fk_facture_fourn=f.rowid  ";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "societe as so ON so.rowid = f.fk_soc";
			$sql .= " WHERE facdet.fk_product IN (SELECT catprod.fk_product FROM " . MAIN_DB_PREFIX . "categorie_product as catprod WHERE catprod.fk_categorie IN (" . $conf->global->AGF_CAT_PRODUCT_CHARGES . "))  ";
			$sql .= " AND f.rowid IN (SELECT sesselement.fk_element FROM " . MAIN_DB_PREFIX . "agefodd_session_element as sesselement INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session as sess ";
			$sql .= " ON sess.rowid=sesselement.fk_session_agefodd AND sesselement.element_type IN ('invoice_supplier_trainer','invoice_supplier_missions','invoice_supplier_room') AND sess.status IN (5,6) ";
			$sql .= " AND sess.entity IN (".getEntity('agsession').")";
			$sql .= " AND sess.dated BETWEEN '" . $this->db->idate($filter['search_date_start']) . "' AND '" . $this->db->idate($filter['search_date_end']) . "')";
			$sql .= " AND f.datef BETWEEN '" . $this->db->idate($filter['search_date_start']) . "' AND '" . $this->db->idate($filter['search_date_end']) . "'";

			dol_syslog(get_class($this) . "::" . __METHOD__, LOG_DEBUG);
			$resql = $this->db->query($sql);
			if ($resql) {
				if ($this->db->num_rows($resql)) {
					while ( $obj = $this->db->fetch_object($resql) ) {
						$this->financial_data_d['Total des charges de l’organisme liées à l’activité de formation'] = $obj->amount;
					}
				}
			} else {
				$this->error = "Error " . $this->db->lasterror();
				dol_syslog(get_class($this) . "::" . __METHOD__ . " Total des charges de l’organisme liées à l’activité de formation " . $this->error, LOG_ERR);
				return - 1;
			}
			$this->db->free($resql);
		}

		if (! empty($conf->global->AGF_CAT_BPF_FEEPRESTA) && ! empty($conf->global->AGF_CAT_BPF_PRESTA)) {
			// dont Achats de prestation de formation et honoraires de formation
			$sql = "SELECT SUM(facdet.total_ht) as amount ";
			$sql .= " FROM " . MAIN_DB_PREFIX . "facture_fourn as f  ";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "facture_fourn_det as facdet ON facdet.fk_facture_fourn=f.rowid  ";
			$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "societe as so ON so.rowid = f.fk_soc";
			$sql .= " WHERE facdet.fk_product IN (SELECT catprod.fk_product FROM " . MAIN_DB_PREFIX . "categorie_product as catprod WHERE catprod.fk_categorie IN (" . $conf->global->AGF_CAT_BPF_FEEPRESTA . "))  ";
			$sql .= " AND f.rowid IN (SELECT sesselement.fk_element FROM " . MAIN_DB_PREFIX . "agefodd_session_element as sesselement INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session as sess ";
			$sql .= " ON sess.rowid=sesselement.fk_session_agefodd AND sesselement.element_type IN ('invoice_supplier_trainer','invoice_supplier_missions','invoice_supplier_room') AND sess.status IN (5,6) ";
			$sql .= " AND sess.entity IN (".getEntity('agsession').")";
			$sql .= " AND sess.dated BETWEEN '" . $this->db->idate($filter['search_date_start']) . "' AND '" . $this->db->idate($filter['search_date_end']) . "')";
			$sql .= " AND f.datef BETWEEN '" . $this->db->idate($filter['search_date_start']) . "' AND '" . $this->db->idate($filter['search_date_end']) . "'";
			$sql .= " AND f.fk_soc IN (SELECT catfourn.fk_soc FROM " . MAIN_DB_PREFIX . "categorie_fournisseur as catfourn WHERE fk_categorie IN (" . $conf->global->AGF_CAT_BPF_PRESTA . "))";

			dol_syslog(get_class($this) . "::" . __METHOD__, LOG_DEBUG);
			$resql = $this->db->query($sql);
			if ($resql) {
				if ($this->db->num_rows($resql)) {
					while ( $obj = $this->db->fetch_object($resql) ) {
						$this->financial_data_d['dont Achats de prestation de formation et honoraires de formation'] = $obj->amount;
					}
				}
			} else {
				$this->error = "Error " . $this->db->lasterror();
				dol_syslog(get_class($this) . "::" . __METHOD__ . " Total des charges de l’organisme liées à l’activité de formation " . $this->error, LOG_ERR);
				return - 1;
			}
			$this->db->free($resql);
		}
	}

	/**
	 * Crée une arborescence de catégories Dolibarr pour le BPF, avec 3 catégories parentes (toutes trois appelées
	 * "BPF", mais de types 0 (product), 1 (supplier) et 2 (customer), chaque catégorie parente ayant des
	 * sous-catégories.
	 *
	 * Pour chaque catégorie, après l'avoir créé, ajoute son ID à la conf correspondante.
	 *
	 * Les confs ainsi créées (ou modifiées) sont des chaînes d'entiers séparés par des virgules:
	 * - BPF (0=produit)
	 *    - AGF_CAT_BPF_OPCA
	 *    - AGF_CAT_BPF_ADMINISTRATION
	 *    - AGF_CAT_BPF_FAF
	 *    - AGF_CAT_BPF_PARTICULIER
	 *    - AGF_CAT_BPF_FOREIGNCOMP
	 * - BPF (1=fournisseur)
	 *    - AGF_CAT_BPF_PRESTA
	 * - BPF (2=client)
	 *    - AGF_CAT_BPF_PRODPEDA
	 *    - AGF_CAT_BPF_TOOLPEDA
	 *    - AGF_CAT_PRODUCT_CHARGES
	 *    - AGF_CAT_BPF_FEEPRESTA
	 * @return number  1 ou -1
	 */
	public function createDefaultCategAffectConst()
	{
		global $conf;
		$error = 0;

		// type 0 = product (@see categorie.class.php)
		$sql = ' INSERT INTO ' . MAIN_DB_PREFIX . 'categorie (entity,fk_parent,label,type,description,fk_soc,visible,import_key) VALUES ('.$conf->entity.',0,\'BPF\',2,\'\',NULL,1,\'agefodd\')';

		dol_syslog(get_class($this) . "::" . __METHOD__, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (! $resql) {
			$error ++;
			$this->errors[] = "Error " . $this->db->lasterror();
		} else {
			$parent = $this->db->last_insert_id(MAIN_DB_PREFIX . "categorie");
		}

		if (! empty($parent)) {
			$sql = ' INSERT INTO ' . MAIN_DB_PREFIX . 'categorie (entity,fk_parent,label,type,description,fk_soc,visible,import_key) VALUES ('.$conf->entity.',' . $parent . ',\'BPF - OPCA\',2,\'\',NULL,1,\'agefodd\')';
			dol_syslog(get_class($this) . "::" . __METHOD__, LOG_DEBUG);
			$resql = $this->db->query($sql);
			if (! $resql) {
				$error ++;
				$this->errors[] = "Error " . $this->db->lasterror();
			} else {
				$idcateg = $this->db->last_insert_id(MAIN_DB_PREFIX . "categorie");
				$selected_categ = array();
				if (! empty($conf->global->AGF_CAT_BPF_OPCA)) {
					$selected_categ = explode(',', $conf->global->AGF_CAT_BPF_OPCA);
				}
				if (! in_array($idcateg, $selected_categ)) {
					$selected_categ[] = $idcateg;
				}

				$res = dolibarr_set_const($this->db, 'AGF_CAT_BPF_OPCA', implode(',', $selected_categ), 'chaine', 0, '', $conf->entity);

				if (! $res > 0) {
					$error ++;
				}
			}

			$sql = ' INSERT INTO ' . MAIN_DB_PREFIX . 'categorie (entity,fk_parent,label,type,description,fk_soc,visible,import_key) VALUES ('.$conf->entity.',' . $parent . ',\'BPF - Admnistration\',2,\'\',NULL,1,\'agefodd\')';
			dol_syslog(get_class($this) . "::" . __METHOD__, LOG_DEBUG);
			$resql = $this->db->query($sql);
			if (! $resql) {
				$error ++;
				$this->errors[] = "Error " . $this->db->lasterror();
			} else {
				$idcateg = $this->db->last_insert_id(MAIN_DB_PREFIX . "categorie");
				$selected_categ = array();
				if (! empty($conf->global->AGF_CAT_BPF_ADMINISTRATION)) {
					$selected_categ = explode(',', $conf->global->AGF_CAT_BPF_ADMINISTRATION);
				}
				if (! in_array($idcateg, $selected_categ)) {
					$selected_categ[] = $idcateg;
				}

				$res = dolibarr_set_const($this->db, 'AGF_CAT_BPF_ADMINISTRATION', implode(',', $selected_categ), 'chaine', 0, '', $conf->entity);

				if (! $res > 0) {
					$error ++;
				}
			}

			$sql = ' INSERT INTO ' . MAIN_DB_PREFIX . 'categorie (entity,fk_parent,label,type,description,fk_soc,visible,import_key) VALUES ('.$conf->entity.',' . $parent . ',\'BPF - FAF\',2,\'\',NULL,1,\'agefodd\')';
			dol_syslog(get_class($this) . "::" . __METHOD__, LOG_DEBUG);
			$resql = $this->db->query($sql);
			if (! $resql) {
				$error ++;
				$this->errors[] = "Error " . $this->db->lasterror();
			} else {
				$idcateg = $this->db->last_insert_id(MAIN_DB_PREFIX . "categorie");
				$selected_categ = array();
				if (! empty($conf->global->AGF_CAT_BPF_FAF)) {
					$selected_categ = explode(',', $conf->global->AGF_CAT_BPF_FAF);
				}
				if (! in_array($idcateg, $selected_categ)) {
					$selected_categ[] = $idcateg;
				}

				$res = dolibarr_set_const($this->db, 'AGF_CAT_BPF_FAF', implode(',', $selected_categ), 'chaine', 0, '', $conf->entity);

				if (! $res > 0) {
					$error ++;
				}
			}

			$sql = ' INSERT INTO ' . MAIN_DB_PREFIX . 'categorie (entity,fk_parent,label,type,description,fk_soc,visible,import_key) VALUES ('.$conf->entity.',' . $parent . ',\'BPF - Particulier\',2,\'\',NULL,1,\'agefodd\')';
			dol_syslog(get_class($this) . "::" . __METHOD__, LOG_DEBUG);
			$resql = $this->db->query($sql);
			if (! $resql) {
				$error ++;
				$this->errors[] = "Error " . $this->db->lasterror();
			} else {
				$idcateg = $this->db->last_insert_id(MAIN_DB_PREFIX . "categorie");
				$selected_categ = array();
				if (! empty($conf->global->AGF_CAT_BPF_PARTICULIER)) {
					$selected_categ = explode(',', $conf->global->AGF_CAT_BPF_PARTICULIER);
				}
				if (! in_array($idcateg, $selected_categ)) {
					$selected_categ[] = $idcateg;
				}

				$res = dolibarr_set_const($this->db, 'AGF_CAT_BPF_PARTICULIER', implode(',', $selected_categ), 'chaine', 0, '', $conf->entity);

				if (! $res > 0) {
					$error ++;
				}
			}

			$sql = ' INSERT INTO ' . MAIN_DB_PREFIX . 'categorie (entity,fk_parent,label,type,description,fk_soc,visible,import_key) VALUES ('.$conf->entity.',' . $parent . ',\'BPF - Entreprise etrangere\',2,\'\',NULL,1,\'agefodd\')';
			dol_syslog(get_class($this) . "::" . __METHOD__, LOG_DEBUG);
			$resql = $this->db->query($sql);
			if (! $resql) {
				$error ++;
				$this->errors[] = "Error " . $this->db->lasterror();
			} else {
				$idcateg = $this->db->last_insert_id(MAIN_DB_PREFIX . "categorie");
				$selected_categ = array();
				if (! empty($conf->global->AGF_CAT_BPF_FOREIGNCOMP)) {
					$selected_categ = explode(',', $conf->global->AGF_CAT_BPF_FOREIGNCOMP);
				}
				if (! in_array($idcateg, $selected_categ)) {
					$selected_categ[] = $idcateg;
				}

				$res = dolibarr_set_const($this->db, 'AGF_CAT_BPF_FOREIGNCOMP', implode(',', $selected_categ), 'chaine', 0, '', $conf->entity);

				if (! $res > 0) {
					$error ++;
				}
			}
		}

		// type 1 = supplier (@see categorie.class.php)
		$sql = ' INSERT INTO ' . MAIN_DB_PREFIX . 'categorie (entity,fk_parent,label,type,description,fk_soc,visible,import_key) VALUES ('.$conf->entity.',0,\'BPF\',1,\'\',NULL,1,\'agefodd\')';

		dol_syslog(get_class($this) . "::" . __METHOD__, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (! $resql) {
			$error ++;
			$this->errors[] = "Error " . $this->db->lasterror();
		} else {
			$parent = $this->db->last_insert_id(MAIN_DB_PREFIX . "categorie");
		}

		if (! empty($parent)) {
			$sql = ' INSERT INTO ' . MAIN_DB_PREFIX . 'categorie (entity,fk_parent,label,type,description,fk_soc,visible,import_key) VALUES ('.$conf->entity.',' . $parent . ',\'BPF - Prestataire\',1,\'\',NULL,1,\'agefodd\')';
			dol_syslog(get_class($this) . "::" . __METHOD__, LOG_DEBUG);
			$resql = $this->db->query($sql);
			if (! $resql) {
				$error ++;
				$this->errors[] = "Error " . $this->db->lasterror();
			} else {
				$idcateg = $this->db->last_insert_id(MAIN_DB_PREFIX . "categorie");
				$selected_categ = array();
				if (! empty($conf->global->AGF_CAT_BPF_PRESTA)) {
					$selected_categ = explode(',', $conf->global->AGF_CAT_BPF_PRESTA);
				}
				if (! in_array($idcateg, $selected_categ)) {
					$selected_categ[] = $idcateg;
				}

				$res = dolibarr_set_const($this->db, 'AGF_CAT_BPF_PRESTA', implode(',', $selected_categ), 'chaine', 0, '', $conf->entity);

				if (! $res > 0) {
					$error ++;
				}
			}
		}

		// type 2 = customer (@see categorie.class.php)
		$sql = ' INSERT INTO ' . MAIN_DB_PREFIX . 'categorie (entity,fk_parent,label,type,description,fk_soc,visible,import_key) VALUES ('.$conf->entity.',0,\'BPF\',0,\'\',NULL,1,\'agefodd\')';

		dol_syslog(get_class($this) . "::" . __METHOD__, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (! $resql) {
			$error ++;
			$this->errors[] = "Error " . $this->db->lasterror();
		} else {
			$parent = $this->db->last_insert_id(MAIN_DB_PREFIX . "categorie");
		}

		if (! empty($parent)) {
			$sql = ' INSERT INTO ' . MAIN_DB_PREFIX . 'categorie (entity,fk_parent,label,type,description,fk_soc,visible,import_key) VALUES ('.$conf->entity.',' . $parent . ',\'BPF - Produit Formation\',0,\'\',NULL,1,\'agefodd\')';
			dol_syslog(get_class($this) . "::" . __METHOD__, LOG_DEBUG);
			$resql = $this->db->query($sql);
			if (! $resql) {
				$error ++;
				$this->errors[] = "Error " . $this->db->lasterror();
			} else {
				$idcateg = $this->db->last_insert_id(MAIN_DB_PREFIX . "categorie");
				$selected_categ = array();
				if (! empty($conf->global->AGF_CAT_BPF_PRODPEDA)) {
					$selected_categ = explode(',', $conf->global->AGF_CAT_BPF_PRODPEDA);
				}
				if (! in_array($idcateg, $selected_categ)) {
					$selected_categ[] = $idcateg;
				}

				$res = dolibarr_set_const($this->db, 'AGF_CAT_BPF_PRODPEDA', implode(',', $selected_categ), 'chaine', 0, '', $conf->entity);

				if (! $res > 0) {
					$error ++;
				}
			}

			$sql = ' INSERT INTO ' . MAIN_DB_PREFIX . 'categorie (entity,fk_parent,label,type,description,fk_soc,visible,import_key) VALUES ('.$conf->entity.',' . $parent . ',\'BPF - Outils pédagogiques\',0,\'\',NULL,1,\'agefodd\')';
			dol_syslog(get_class($this) . "::" . __METHOD__, LOG_DEBUG);
			$resql = $this->db->query($sql);
			if (! $resql) {
				$error ++;
				$this->errors[] = "Error " . $this->db->lasterror();
			} else {
				$idcateg = $this->db->last_insert_id(MAIN_DB_PREFIX . "categorie");
				$selected_categ = array();
				if (! empty($conf->global->AGF_CAT_BPF_TOOLPEDA)) {
					$selected_categ = explode(',', $conf->global->AGF_CAT_BPF_TOOLPEDA);
				}
				if (! in_array($idcateg, $selected_categ)) {
					$selected_categ[] = $idcateg;
				}

				$res = dolibarr_set_const($this->db, 'AGF_CAT_BPF_TOOLPEDA', implode(',', $selected_categ), 'chaine', 0, '', $conf->entity);

				if (! $res > 0) {
					$error ++;
				}
			}

			$sql = ' INSERT INTO ' . MAIN_DB_PREFIX . 'categorie (entity,fk_parent,label,type,description,fk_soc,visible,import_key) VALUES ('.$conf->entity.',' . $parent . ',\'BPF - Frais Autre \',0,\'\',NULL,1,\'agefodd\')';
			dol_syslog(get_class($this) . "::" . __METHOD__, LOG_DEBUG);
			$resql = $this->db->query($sql);
			if (! $resql) {
				$error ++;
				$this->errors[] = "Error " . $this->db->lasterror();
			} else {
				$idcateg = $this->db->last_insert_id(MAIN_DB_PREFIX . "categorie");
				$selected_categ = array();
				if (! empty($conf->global->AGF_CAT_PRODUCT_CHARGES)) {
					$selected_categ = explode(',', $conf->global->AGF_CAT_PRODUCT_CHARGES);
				}
				if (! in_array($idcateg, $selected_categ)) {
					$selected_categ[] = $idcateg;
				}

				$res = dolibarr_set_const($this->db, 'AGF_CAT_PRODUCT_CHARGES', implode(',', $selected_categ), 'chaine', 0, '', $conf->entity);

				if (! $res > 0) {
					$error ++;
				}
			}

			$sql = ' INSERT INTO ' . MAIN_DB_PREFIX . 'categorie (entity,fk_parent,label,type,description,fk_soc,visible,import_key) VALUES ('.$conf->entity.',' . $parent . ',\'BPF - Frais/honoraire prestataires\',0,\'\',NULL,1,\'agefodd\')';
			dol_syslog(get_class($this) . "::" . __METHOD__, LOG_DEBUG);
			$resql = $this->db->query($sql);
			if (! $resql) {
				$error ++;
				$this->errors[] = "Error " . $this->db->lasterror();
			} else {
				$idcateg = $this->db->last_insert_id(MAIN_DB_PREFIX . "categorie");
				$selected_categ = array();
				if (! empty($conf->global->AGF_CAT_BPF_FEEPRESTA)) {
					$selected_categ = explode(',', $conf->global->AGF_CAT_BPF_FEEPRESTA);
				}
				if (! in_array($idcateg, $selected_categ)) {
					$selected_categ[] = $idcateg;
				}

				$res = dolibarr_set_const($this->db, 'AGF_CAT_BPF_FEEPRESTA', implode(',', $selected_categ), 'chaine', 0, '', $conf->entity);

				if (! $res > 0) {
					$error ++;
				}
			}
		}

		if (! empty($error)) {
			return - 1;
		}
	}

	/**
	 * Fonction helper: exécute une requête SELECT qui fait la somme ('amount') de lignes de facture, puis
	 * met cette somme dans $this->financial_data à la clé $financialDataKey.
	 *
	 * @param array $sqlParts  Clés: 'select', 'from', 'where'
	 * @param string $financialDataKey
	 * @return int  -1 = KO; 1 = OK
	 * @throws Exception
	 */
	private function _runFinancialQueryForSectionC($sqlParts, $financialDataKey)
	{
		dol_syslog(get_class($this) . "::" . __METHOD__ . " $financialDataKey", LOG_DEBUG);
		$this->SQL_DEBUG_C[$financialDataKey] = $sqlParts;
		$sqlParts = (object) $sqlParts;
		$sql = "$sqlParts->select $sqlParts->from $sqlParts->where";
		if (!isset($this->financial_data[$financialDataKey])) {
			$this->financial_data[$financialDataKey] = 0;
		}

		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql)) {
				if ($obj = $this->db->fetch_object($resql)) {
					$this->financial_data[$financialDataKey] += $obj->amount;
				}
			}
		} else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::" . __METHOD__ . " $financialDataKey $this->error", LOG_ERR);
			return - 1;
		}
		$this->db->free($resql);
		return 1;
	}

	/**
	 * Retourne une requête SQL de contrôle permettant de savoir quelles lignes de factures correspondant au filtre
	 * de date et au statut de facture ne sont pas prises en compte dans la section C (chiffre d'affaires) du BPF,
	 * ce qui permettra de faciliter la recherche d'éventuelles erreurs

	 * @param array $filter  Associative array: keys are names of search fields ("search_date_start",
	 *                       "search_date_end"); values are filter values (timestamps).
	 * @return string  Requête SQL de contrôle
	 */
	public function getSQLQueryForInvoiceLinesNotInSectionC($filter)
	{
		$invoiceLineTableAlias = 'facture_det_debug';
		$invoiceTableAlias = 'facture_debug';

		$allQueriesSectionC = [];
		foreach ($this->SQL_DEBUG_C as $sqlParts) {
			$sqlParts = (object) $sqlParts;
			$allQueriesSectionC[] = "SELECT DISTINCT fd.rowid $sqlParts->from $sqlParts->where";
		}

		$sql = /* @lang SQL */
			"SELECT "
			. "   $invoiceLineTableAlias.rowid    AS ${invoiceLineTableAlias}_rowid,"
			. "   $invoiceLineTableAlias.total_ht AS ${invoiceLineTableAlias}_total_ht,"
			. "   $invoiceTableAlias.rowid        AS ${invoiceTableAlias}_rowid,"
			. "   $invoiceTableAlias.ref          AS ${invoiceTableAlias}_ref,"
			. "   $invoiceTableAlias.total_ht     AS ${invoiceTableAlias}_total_ht"
			. " FROM " . MAIN_DB_PREFIX . "facturedet as $invoiceLineTableAlias"
			. " INNER JOIN " . MAIN_DB_PREFIX . "facture as $invoiceTableAlias"
			. "   ON $invoiceTableAlias.rowid = $invoiceLineTableAlias.fk_facture"
			// filtre sur les dates et les statuts de facture:
			. " WHERE ("
			.       $this->_getDateFilter($filter, "$invoiceTableAlias.datef")
			. " )"
			. " AND $invoiceTableAlias.fk_statut IN (1, 2)"

			// filtre complémentaire: filtre qui donne tout ce qui n'est pas dans les autres requêtes
			. " AND $invoiceLineTableAlias.rowid NOT IN ("
			. "     SELECT facture_det_debug_two.rowid"
			. "     FROM ("
			.           implode(' UNION ', $allQueriesSectionC)
			. "     ) as facture_det_debug_two"
			. " )"
			. " ORDER BY $invoiceTableAlias.rowid ASC, $invoiceLineTableAlias.rowid ASC"
		;
		return $sql;
	}

	/**
	 * C-13 Autres produits au titre de la formation professionnelle continue
	 *
	 * @param array $filter  Associative array: keys are names of search fields ("search_date_start",
	 *                       "search_date_end"); values are filter values (timestamps).
	 * @return number  1 ou -1
	 */
	private function _getAmountFinC13($filter)
	{
		global $conf, $langs;
		$financialDataKey = 'C-13 Autres produits au titre de la formation professionnelle continue';
		$sqlParts = ['select' => '', 'from' => '', 'where' => ''];

		// confs qui déclenchent un warning si vides
		$confNamesForWarning = array(
			'AGF_CAT_PRODUCT_CHARGES' => 'AgfCategOverheadCost',
			'AGF_CAT_BPF_FOREIGNCOMP' => 'AgfReportBPFCategForeignComp',
			'AGF_CAT_BPF_PRODPEDA'    => 'AgfReportBPFCategProdPeda',
		);
		$this->_warnIfConfsMissing($confNamesForWarning);

		if (! empty($conf->global->AGF_CAT_PRODUCT_CHARGES)) {
			$sqlParts['select'] = /* @lang SQL */
				" SELECT SUM(fd.total_ht) as amount ";
			$sqlParts['from'] = /* @lang SQL */
				" FROM " . MAIN_DB_PREFIX . "facturedet AS fd"
				. " INNER JOIN " . MAIN_DB_PREFIX . "facture AS f"
				. "   ON f.rowid = fd.fk_facture";
			$sqlParts['where'] = /* @lang SQL */
				" WHERE "
				. "   f.fk_statut IN (1, 2)"
				. "   AND f.datef BETWEEN '" . $this->db->idate($filter['search_date_start']) . "'"
				. "   AND '" . $this->db->idate($filter['search_date_end']) . "'"
				. "   AND fd.fk_product IN ("
				. "         SELECT cp.fk_product"
				. "         FROM " . MAIN_DB_PREFIX . "categorie_product AS cp"
				. "         WHERE cp.fk_categorie IN (" . $conf->global->AGF_CAT_PRODUCT_CHARGES . ")"
				. "   )"
				. "   AND f.rowid IN ("
				. "         SELECT DISTINCT factin.rowid"
				. "         FROM " . MAIN_DB_PREFIX . "agefodd_session_element AS se"
				. "         INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session AS sess"
				. "           ON sess.rowid = se.fk_session_agefodd"
				. "             AND se.element_type = 'invoice'"
				. "             AND sess.dated BETWEEN"
				. "                 '" . $this->db->idate($filter['search_date_start']) . "'"
				. "                 AND"
				. "                 '" . $this->db->idate($filter['search_date_end']) . "'"
				. "             AND sess.status IN (5, 6)"
				. "             AND sess.entity IN (".getEntity('agsession') . ")"
				. "         INNER JOIN " . MAIN_DB_PREFIX . "facture AS factin"
				. "           ON factin.rowid=se.fk_element"
				. "   )";
			// Note: les clauses:
			//       ! empty($conf->global->AGF_CAT_PRODUCT_CHARGES)
			//       et
			//       ! empty($conf->global->AGF_CAT_BPF_PRODPEDA) && ! empty($conf->global->AGF_CAT_BPF_FOREIGNCOMP)
			//       ne sont pas mutuellement exclusives!
			$res = $this->_runFinancialQueryForSectionC($sqlParts, $financialDataKey);
			if ($res < 0) return $res;
		}

		if (! empty($conf->global->AGF_CAT_BPF_PRODPEDA) && ! empty($conf->global->AGF_CAT_BPF_FOREIGNCOMP)) {
			$sqlParts = ['select' => '', 'from' => '', 'where' => ''];
			//          $sqlParts['select'] = /* @lang SQL */
			//              ' SELECT DISTINCT f.rowid ';
			$sqlParts['select'] = /* @lang SQL */
				" SELECT SUM(fd.total_ht) as amount ";
			$sqlParts['from'] = /* @lang SQL */
				"FROM " . MAIN_DB_PREFIX . "facturedet AS fd"
				. " INNER JOIN " . MAIN_DB_PREFIX . "facture AS f"
				. "   ON f.rowid = fd.fk_facture";
			$sqlParts['where'] = /* @lang SQL */
				" WHERE"
				. "   f.fk_statut IN (1 , 2)"
				. "   AND f.datef BETWEEN '" . $this->db->idate($filter['search_date_start']) . "'"
				. "   AND '" . $this->db->idate($filter['search_date_end']) . "'"
				. "   AND fd.fk_product IN ("
				. "       SELECT cp.fk_product"
				. "       FROM " . MAIN_DB_PREFIX . "categorie_product AS cp"
				. "       WHERE cp.fk_categorie IN (" . $conf->global->AGF_CAT_BPF_PRODPEDA . ")"
				. "   )"
				. "   AND f.fk_soc IN ("
				. "       SELECT cs.fk_soc"
				. "       FROM " . MAIN_DB_PREFIX . "categorie_societe AS cs"
				. "       WHERE cs.fk_categorie IN (" . $conf->global->AGF_CAT_BPF_FOREIGNCOMP . ")"
				. "   )"
				. "   AND f.rowid IN ("
				. "       SELECT DISTINCT factin.rowid"
				. "       FROM " . MAIN_DB_PREFIX . "agefodd_session_element AS se"
				. "       INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session AS sess"
				. "         ON sess.rowid = se.fk_session_agefodd"
				. "         AND se.element_type = 'invoice'"
				. "         AND sess.dated BETWEEN"
				. "             '" . $this->db->idate($filter['search_date_start']) . "'"
				. "             AND"
				. "             '" . $this->db->idate($filter['search_date_end']) . "'"
				. "         AND sess.status IN (5,6)"
				. "         AND sess.entity IN (".getEntity('agsession').")"
				. "       INNER JOIN " . MAIN_DB_PREFIX . "facture AS factin"
				. "         ON factin.rowid=se.fk_element"
				. "       INNER JOIN " . MAIN_DB_PREFIX . "agefodd_place as pl"
				. "         ON pl.rowid=sess.fk_session_place"
				. "         AND pl.fk_pays<>1"
				. "   )";

			$res = $this->_runFinancialQueryForSectionC($sqlParts, $financialDataKey);
			if ($res < 0) return $res;
		}
		if (empty($this->financial_data[$financialDataKey])) {
			unset($this->financial_data[$financialDataKey]);
		}
		return $res;
	}

	/**
	 * Récupreration des facture Bizzare (genre payeur est financeur alternatif mais pas employé sur stagiaire, multi financement entre client/emploeyr et OPCA
	 *
	 * @param $filter
	 * @return int
	 * @throws Exception
	 */
	public function _getAmountFinCHack($filter) {

		$financialDataKey = 'C-1 Produits provenant des entreprises pour la formation de leurs salariés';
		$sqlParts = ['select' => '', 'from' => '', 'where' => ''];

		$confprod = $this->getConf('AGF_CAT_BPF_PRODPEDA', 'confprod');
		if ($confprod === false) {
			$this->setWarning('AgfErroVarNotSetBPF', $langs->transnoentities('AgfReportBPFCategProdPeda'));
		}
		$confcust = $this->getConf('AGF_CAT_BPF_OPCA', 'confcust');
		if ($confcust === false) {
			$this->setWarning('AgfErroVarNotSetBPF', $langs->transnoentities('AgfReportBPFCategOPCA'));
		}

		$sqlParts['select'] = /* @lang SQL */
			" SELECT SUM(fd.total_ht) as amount";
		$sqlParts['from'] = /* @lang SQL */
			" FROM " . MAIN_DB_PREFIX . "facturedet AS fd "
			. "INNER JOIN " . MAIN_DB_PREFIX . "facture AS f"
			. "   ON f.rowid = fd.fk_facture ";

		// si l'une des clés (employer ou datefac) est définie, filtre sur la date de facturation
		if (!empty($data['employer']) || !empty($data['datefac'])) {
			$sqlParts['from'] .= /* @lang SQL */
				" AND " . $this->_getDateFilter($filter, 'f.datef');
		}

		// filtre sur le statut des factures (validées ou payées)
		$sqlParts['where'] = /* @lang SQL */
			" WHERE f.fk_statut IN (1 , 2)";

		// si défini, filtre sur catégorie(s) du produit de la ligne de facture
		if (!empty($confprod)) {
			$sqlParts['where'] .= /* @lang SQL */
				" AND fd.fk_product IN ("
				. "    SELECT cp.fk_product"
				. "    FROM " . MAIN_DB_PREFIX . "categorie_product AS cp"
				. "    WHERE cp.fk_categorie IN (" . $confprod . ")"
				. ")";
		}

		// si défini, filtre sur catégorie(s) du tiers de la facture
		if (!empty($confcust)) {
			$sqlParts['where'] .= /* @lang SQL */
				" AND f.fk_soc NOT IN ("
				. "    SELECT cs.fk_soc"
				. "    FROM " . MAIN_DB_PREFIX . "categorie_societe AS cs"
				. "    WHERE cs.fk_categorie IN (" . $confcust . ")"
				. ")";
		}

		// filtre: les factures doivent:
		//   • être liées à des sessions terminées ou en cours, dont les créneaux sont dans l'intervalle requis
		$sqlParts['where'] .= /* @lang SQL */
			" AND "
			. "( " // BLOC 1
			. "    ( " // BLOC 1.1
			. "    f.rowid IN "
			. "        (" // BLOC 1.1.1
			. "          SELECT DISTINCT factin.rowid"
			. "          FROM " . MAIN_DB_PREFIX . "agefodd_session_element AS se"
			. "          INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session AS sess"
			. "             ON sess.rowid = se.fk_session_agefodd"
			. "               AND se.element_type = 'invoice'"
			. "               AND sess.status IN (5,6)"
			. "               AND sess.entity IN (".getEntity('agsession').")"
			. "          INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_calendrier as statime"
			. "            ON statime.fk_agefodd_session=sess.rowid"
			. "              AND statime.heured >= '" . $this->db->idate($filter['search_date_start']) . "'"
			. "              AND statime.heuref <= '" . $this->db->idate($filter['search_date_end']) . "'";

		//   • si le booléen employeur est true, la facture doit être liée à une session liée à un tiers employeur
		if (! empty($data['employer'])) {
			$sqlParts['where'] .= /* @lang SQL */
				"            AND sess.fk_soc_employer IS NOT NULL ";
		}

		//   • la session doit avoir au moins un stagiaire ayant un des statuts spécifiés par $data['idtypesta']
		//     (les statuts sont dans llx_agefodd_stagiaire_type)
		$sqlParts['where'] .= /* @lang SQL */
			"            INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_stagiaire AS ss"
			. "            ON ss.fk_session_agefodd = sess.rowid"
			. "              AND ss.fk_agefodd_stagiaire_type IN (20,4)";

		//if (empty($data['checkOPCA']) && empty($data['employer'])) {
			//   Ni checkOPCA, ni employer: facture liée à la session et tiers du stagiaire = tiers de la facture
			$sqlParts['where'] .= /* @lang SQL */
				"        INNER JOIN " . MAIN_DB_PREFIX . "agefodd_stagiaire AS sta ON sta.rowid = ss.fk_stagiaire"
				. "      INNER JOIN " . MAIN_DB_PREFIX . "facture AS factin"
				. "        ON factin.rowid = se.fk_element";
			// si PAS checkPV, il faut que le stagiaire ait le même tiers que la facture
		//	if (empty($data['checkPV'])) {
				$sqlParts['where'] .= /* @lang SQL */
					"        AND factin.fk_soc = sta.fk_soc ";
		//	}

		$sqlParts['where'] .= /* @lang SQL */
			"          ) " // FIN BLOC 1.1.1
			. "    )";   // FIN BLOC 1.1


		$sqlParts['where'] .= /* @lang SQL */
			"  )"; // FIN BLOC 1

		// filtre: les factures doivent:
		//   • être liées à des sessions terminées ou en cours, dont les créneaux sont dans l'intervalle requis
		$sqlParts['where'] .= /* @lang SQL */
			" OR "
			. "( " // BLOC 1
			. "    ( " // BLOC 1.1
			. "    f.rowid IN "
			. "        (" // BLOC 1.1.1
			. "          SELECT DISTINCT factin.rowid"
			. "          FROM " . MAIN_DB_PREFIX . "agefodd_session_element AS se"
			. "          INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session AS sess"
			. "             ON sess.rowid = se.fk_session_agefodd"
			. "               AND se.element_type = 'invoice'"
			. "               AND sess.status IN (5,6)"
			. "               AND sess.entity IN (".getEntity('agsession').")"
			. "          INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_calendrier as statime"
			. "            ON statime.fk_agefodd_session=sess.rowid"
			. "              AND statime.heured >= '" . $this->db->idate($filter['search_date_start']) . "'"
			. "              AND statime.heuref <= '" . $this->db->idate($filter['search_date_end']) . "'";

		//   • si le booléen employeur est true, la facture doit être liée à une session liée à un tiers employeur
		if (! empty($data['employer'])) {
			$sqlParts['where'] .= /* @lang SQL */
				"            AND sess.fk_soc_employer IS NOT NULL ";
		}

		//   • la session doit avoir au moins un stagiaire ayant un des statuts spécifiés par $data['idtypesta']
		//     (les statuts sont dans llx_agefodd_stagiaire_type)
		$sqlParts['where'] .= /* @lang SQL */
			"            INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_stagiaire AS ss"
			. "            ON ss.fk_session_agefodd = sess.rowid"
			. "              AND ss.fk_agefodd_stagiaire_type IN (2)";

		//if (empty($data['checkOPCA']) && empty($data['employer'])) {
		//   Ni checkOPCA, ni employer: facture liée à la session et tiers du stagiaire = tiers de la facture
		$sqlParts['where'] .= /* @lang SQL */
			"        INNER JOIN " . MAIN_DB_PREFIX . "agefodd_stagiaire AS sta ON sta.rowid = ss.fk_stagiaire"
			. "      INNER JOIN " . MAIN_DB_PREFIX . "facture AS factin"
			. "        ON factin.rowid = se.fk_element";
		// si PAS checkPV, il faut que le stagiaire ait le même tiers que la facture
		//	if (empty($data['checkPV'])) {
		$sqlParts['where'] .= /* @lang SQL */
			"        AND factin.fk_soc = ss.fk_soc_link ";
		//	}

		$sqlParts['where'] .= /* @lang SQL */
			"          ) " // FIN BLOC 1.1.1
			. "    )";   // FIN BLOC 1.1


		$sqlParts['where'] .= /* @lang SQL */
			"  )"; // FIN BLOC 1

		return $this->_runFinancialQueryForSectionC($sqlParts, $financialDataKey);
	}

	/**
	 * TODO vérifier pourquoi la fonction s'appelle C11 mais la clé C12 (erreur ou nommage volontaire?)
	 * C11 = "Autres produits au titre de la formation professionnelle": provenant de non-salariés finançant
	 * eux-mêmes leur formation, des organismes (dont CFA) dont on est sous-traitant
	 * cf. https://culture-rh.com/bfp-bilan-pedagogique-financier/
	 * cf. https://hauts-de-france.dreets.gouv.fr/sites/hauts-de-france.dreets.gouv.fr/IMG/pdf/cartographie_et_analyse_de_l_offre_de_formation_professionnelle_dans_les_hauts_de_france.pdf
	 *
	 * @param array $filter  Associative array: keys are names of search fields ("search_date_start",
	 *                       "search_date_end"); values are filter values (timestamps).
	 * @return number  1 ou -1
	 */
	private function _getAmountFinC11($filter)
	{
		global $conf, $langs;
		$sqlParts = ['select' => '', 'from' => '', 'where' => ''];
		$financialDataKey = 'C-12 Produits résultant de la vente d’outils pédagogiques';

		if (empty($conf->global->AGF_CAT_BPF_TOOLPEDA)) {
			$this->warnings[] = $langs->transnoentities('AgfErroVarNotSetBPF', $langs->transnoentities("AgfReportBPFCategToolPeda"));
			dol_syslog(get_class($this) . ":: " . end($this->warnings), LOG_WARNING);
			// return - 1;
		}

		if (! empty($conf->global->AGF_CAT_BPF_TOOLPEDA)) {
			//          $sqldebug = /* @lang SQL */
			//              ' SELECT DISTINCT f.rowid ';
			$sqlParts['select'] = /* @lang SQL */
				" SELECT SUM(fd.total_ht) as amount ";
			$sqlParts['from'] = /* @lang SQL */
				" FROM " . MAIN_DB_PREFIX . "facturedet AS fd"
				. " INNER JOIN " . MAIN_DB_PREFIX . "facture AS f"
				. "   ON f.rowid = fd.fk_facture";
			$sqlParts['where'] = /* @lang SQL */
				" WHERE f.fk_statut IN (1 , 2)"
				. "   AND f.datef BETWEEN"
				. "              '" . $this->db->idate($filter['search_date_start']) . "'"
				. "              AND"
				. "              '" . $this->db->idate($filter['search_date_end']) . "'"
				. "   AND fd.fk_product IN ("
				. "       SELECT cp.fk_product"
				. "       FROM " . MAIN_DB_PREFIX . "categorie_product AS cp"
				. "       WHERE cp.fk_categorie IN (" . $conf->global->AGF_CAT_BPF_TOOLPEDA . ")"
				. "   )";

			return $this->_runFinancialQueryForSectionC($sqlParts, $financialDataKey);
		}

		return 1;
	}

	/**
	 * Utilisé pour la partie "C"
	 * @see ReportBPF::fetch_financial_c()
	 *
	 * Lance une requête de calcul du montant total HT des lignes de factures respectant les critères déterminés par
	 * `$filter` et `$data`, pour affecter ce total à `$this->financial_data[$data['label']]`.
	 *
	 * Alias de tables utilisés dans la requête:
	 * - fd:          llx_facturedet
	 * - f:           llx_facture
	 * - cp:          llx_categorie_product
	 * - cs:          llx_categorie_societe
	 * - se:          llx_agefodd_session_element
	 * - sess:        llx_agefodd_session
	 * - statime:     llx_agefodd_session_calendrier
	 * - ss:          llx_agefodd_session_stagiaire
	 * - sta:         llx_agefodd_stagiaire
	 * - factin:      llx_facture
	 * - opca:        llx_agefodd_opca
	 * - seopca:      llx_agefodd_session_element
	 * - statimeopca: llx_agefodd_session_calendrier
	 * - ssopca:      llx_agefodd_session_stagiaire
	 * - sessopca:    llx_agefodd_session
	 * - factinopca:  llx_facture
	 *
	 * @param array $data  Associative array: keys are:
	 *                     - 'label'         => string: (ex: 'C-6 Pouvoirs publics spécifiques Conseils régionaux'),
	 *                     - 'idtypesta'     => string|int liste d'ID de llx_agefodd_stagiaire_type
	 *                     - 'confprod'      => string (ex: 'AGF_CAT_BPF_PRODPEDA') = nom de conf contenant un ou
	 *                     plusieurs ID de catégorie(s) de produits: si définie, filtrera sur les
	 *                     lignes de facture dont le produit est dans la catégorie
	 *                     - 'confcust'      => string: CONF contenant un ou plusieurs ID de catégorie(s) de tiers
	 *                     - 'confprodlabel' => string: (ex: 'AgfReportBPFCategProdPeda') clé de traduction de confprod
	 *                     - 'confcustlabel' => string (ex: 'AgfReportBPFCategAdmnistration') clé de traduction
	 *                     - 'employer'      => bool,
	 *                     - 'checkOPCA'     => bool,
	 *                     - 'checkPV'       => bool,
	 *                     - 'datefac'       => bool,
	 * @param array $filter  Associative array: keys are names of search fields ("search_date_start",
	 *                       "search_date_end"); values are filter values (timestamps).
	 * @return int  1 = success, -1 = error
	 */
	private function _getAmountFin($data, $filter)
	{
		global $conf, $langs;
		$sqlParts = ['select' => '', 'from' => '', 'where' => ''];
		$financialDataKey = $data['label'];

		$confcust = $this->getConf($data, 'confcust');
		$confprod = $this->getConf($data, 'confprod');
		if ($confprod === false) {
			$this->setWarning('AgfErroVarNotSetBPF', $langs->transnoentities($data['confprodlabel']));
		}
		if ($confcust === false) {
			$this->setWarning('AgfErroVarNotSetBPF', $langs->transnoentities($data['confcustlabel']));
		}

		if (empty($confcust) && empty($confprod)) {
			return 1;
		}
		/* Exemple de $data:
		array(
			'idtypesta'     => 14,
			'confprod'      => 'AGF_CAT_BPF_PRODPEDA',
			'confprodlabel' => 'AgfReportBPFCategProdPeda',
			'label'         => 'C-8 Pouvoirs publics spécifiques Autres ressources publiques',
			'confcust'      => '',
			'confcustlabel' => 'AgfReportBPFCategAdmnistration',
			'employer'      => 0,
			'checkOPCA'     => 0,
			'checkPV'       => 1,
			'datefac'       => 1,
		),*/

		$sqlParts['select'] = /* @lang SQL */
			" SELECT SUM(fd.total_ht) as amount";
		$sqlParts['from'] = /* @lang SQL */
			" FROM " . MAIN_DB_PREFIX . "facturedet AS fd "
			. "INNER JOIN " . MAIN_DB_PREFIX . "facture AS f"
			. "   ON f.rowid = fd.fk_facture ";

		// si l'une des clés (employer ou datefac) est définie, filtre sur la date de facturation
		if (!empty($data['employer']) || !empty($data['datefac'])) {
			$sqlParts['from'] .= /* @lang SQL */
				" AND " . $this->_getDateFilter($filter, 'f.datef');
		}

		// filtre sur le statut des factures (validées ou payées)
		$sqlParts['where'] = /* @lang SQL */
			" WHERE f.fk_statut IN (1 , 2)";

		// si défini, filtre sur catégorie(s) du produit de la ligne de facture
		if (!empty($confprod)) {
			$sqlParts['where'] .= /* @lang SQL */
				" AND fd.fk_product IN ("
				. "    SELECT cp.fk_product"
				. "    FROM " . MAIN_DB_PREFIX . "categorie_product AS cp"
				. "    WHERE cp.fk_categorie IN (" . $confprod . ")"
				. ")";
		}

		// si défini, filtre sur catégorie(s) du tiers de la facture
		if (!empty($confcust)) {
			$sqlParts['where'] .= /* @lang SQL */
				" AND f.fk_soc IN ("
				. "    SELECT cs.fk_soc"
				. "    FROM " . MAIN_DB_PREFIX . "categorie_societe AS cs"
				. "    WHERE cs.fk_categorie IN (" . $confcust . ")"
				. ")";
		}

		// filtre: les factures doivent:
		//   • être liées à des sessions terminées ou en cours, dont les créneaux sont dans l'intervalle requis
		$sqlParts['where'] .= /* @lang SQL */
			" AND "
			. "( " // BLOC 1
			. "    ( " // BLOC 1.1
			. "    f.rowid IN "
			. "        (" // BLOC 1.1.1
			. "          SELECT DISTINCT factin.rowid"
			. "          FROM " . MAIN_DB_PREFIX . "agefodd_session_element AS se"
			. "          INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session AS sess"
			. "             ON sess.rowid = se.fk_session_agefodd"
			. "               AND se.element_type = 'invoice'"
			. "               AND sess.status IN (5,6)"
			. "               AND sess.entity IN (".getEntity('agsession').")"
			. "          INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_calendrier as statime"
			. "            ON statime.fk_agefodd_session=sess.rowid"
			. "              AND statime.heured >= '" . $this->db->idate($filter['search_date_start']) . "'"
			. "              AND statime.heuref <= '" . $this->db->idate($filter['search_date_end']) . "'";

		//   • si le booléen employeur est true, la facture doit être liée à une session liée à un tiers employeur
		if (! empty($data['employer'])) {
			$sqlParts['where'] .= /* @lang SQL */
				"            AND sess.fk_soc_employer IS NOT NULL ";
		}

		//   • la session doit avoir au moins un stagiaire ayant un des statuts spécifiés par $data['idtypesta']
		//     (les statuts sont dans llx_agefodd_stagiaire_type)
		$sqlParts['where'] .= /* @lang SQL */
			"            INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_stagiaire AS ss"
			. "            ON ss.fk_session_agefodd = sess.rowid"
			. "              AND ss.fk_agefodd_stagiaire_type IN (" . $data['idtypesta'] . ")";

		if (empty($data['checkOPCA']) && empty($data['employer'])) {
			//   Ni checkOPCA, ni employer: facture liée à la session et tiers du stagiaire = tiers de la facture
			$sqlParts['where'] .= /* @lang SQL */
				"        INNER JOIN " . MAIN_DB_PREFIX . "agefodd_stagiaire AS sta ON sta.rowid = ss.fk_stagiaire"
				. "      INNER JOIN " . MAIN_DB_PREFIX . "facture AS factin"
				. "        ON factin.rowid = se.fk_element";
			// si PAS checkPV, il faut que le stagiaire ait le même tiers que la facture
			if (empty($data['checkPV'])) {
				$sqlParts['where'] .= /* @lang SQL */
					"        AND factin.fk_soc = sta.fk_soc ";
			}
			// si checkaltfin, il faut que le stagiaire ait, sur la session, le même tiers que la facture
			elseif (!empty($data['checkaltfin'])) {
				$sqlParts['where'] .= /* @lang SQL */
					"        AND factin.fk_soc = ss.fk_soc_link ";
			}
		} elseif (!empty($data['checkOPCA'])) {
			//   checkOPCA: facture liée à la session et ayant pour tiers l'OPCA de la session
			$sqlParts['where'] .= /* @lang SQL */
				"        INNER JOIN " . MAIN_DB_PREFIX . "agefodd_opca AS opca"
				. "        ON opca.fk_session_trainee = ss.rowid"
				. "          AND opca.fk_session_agefodd=sess.rowid"
				. "      INNER JOIN " . MAIN_DB_PREFIX . "facture AS factin"
				. "        ON factin.rowid = se.fk_element"
				. "          AND factin.fk_soc = opca.fk_soc_OPCA";
		} elseif (!empty($data['employer'])) {
			//   employer: facture liée à la session et ayant pour tiers l'employeur lié à la session
			$sqlParts['where'] .= /* @lang SQL */
				"        INNER JOIN " . MAIN_DB_PREFIX . "facture AS factin"
				. "        ON factin.rowid = se.fk_element"
				. "          AND factin.fk_soc = sess.fk_soc_employer";
		}
		$sqlParts['where'] .= /* @lang SQL */
			"          ) " // FIN BLOC 1.1.1
			. "    )";   // FIN BLOC 1.1

		if (! empty($data['checkOPCA'])) {
			// checkOPCA
			$sqlParts['where'] .= /* @lang SQL */
				" OR "
				. " (" // BLOC 1.2
				. "   f.rowid IN"
				. "       (" // BLOC 1.2.1
				. "         SELECT DISTINCT factinopca.rowid"
				. "         FROM " . MAIN_DB_PREFIX . "agefodd_session_element AS seopca"
				. "         INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session AS sessopca"
				. "           ON sessopca.rowid = seopca.fk_session_agefodd AND seopca.element_type = 'invoice'"
				. "             AND sessopca.status IN (5,6)"
				. "         INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_calendrier as statimeopca"
				."            ON statimeopca.fk_agefodd_session=sessopca.rowid"
				."              AND statimeopca.heured >= '" . $this->db->idate($filter['search_date_start']) . "'"
				."              AND statimeopca.heuref <= '" . $this->db->idate($filter['search_date_end']) . "'";
			if (! empty($data['employer'])) {
				$sqlParts['where'] .= /* @lang SQL */
					"           AND sessopca.fk_soc_employer IS NOT NULL ";
			}
			$sqlParts['where'] .= /* @lang SQL */
				"     	    INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_stagiaire AS ssopca"
				. "           ON ssopca.fk_session_agefodd = sessopca.rowid "
				. "             AND ssopca.fk_agefodd_stagiaire_type IN (" . $data['idtypesta'] . ")"
				. "         INNER JOIN " . MAIN_DB_PREFIX . "facture AS factinopca"
				. "           ON factinopca.fk_soc = sessopca.fk_soc_OPCA"
				. "             AND factinopca.rowid=seopca.fk_element"
				. "       )" // FIN BLOC 1.2.1
				. " )";  // FIN BLOC 1.2
		}
		$sqlParts['where'] .= /* @lang SQL */
			"  )"; // FIN BLOC 1

		return $this->_runFinancialQueryForSectionC($sqlParts, $financialDataKey);
	}

	/**
	 * Fonction pour récupérer des filtres SQL communs (filtres sur les dates indiquées dans $filter avec les alias
	 * indiqués dans $fieldaliases)
	 *
	 * @param array $filter  Associative array: keys are names of search fields ("search_date_start",
	 *                       "search_date_end"); values are filter values (timestamps).
	 * @param string|string[] $fieldaliases  If string, adds a date filter (using $filter) for $fieldaliases. If array,
	 *                                       adds one date filter for each alias in $fieldaliases
	 *
	 * @return string  SQL filter for concatenation to a WHERE clause.
	 * TODO: l'utiliser plus? Note: en MariaDB, BETWEEN est inclusif
	 */
	protected function _getDateFilter($filter, $fieldaliases = array())
	{
		if (is_string($fieldaliases)) $fieldaliases = array($fieldaliases);
		$sqlConditions = array();
		$ds = '"' . $this->db->idate($filter['search_date_start']) . '"';
		$de = '"' . $this->db->idate($filter['search_date_end']) . '"';
		foreach ($fieldaliases as $fieldalias) {
			$sqlConditions[] = "$fieldalias BETWEEN $ds AND $de";
		}
		return implode(' AND ', $sqlConditions);
	}

	/**
	 * Retourne $conf->global->{$data[$key]}, sauf si:
	 *  1) $data[$key] est empty ⇒ null
	 *  2) la conf est empty ⇒ false
	 * @param $data
	 * @param $key
	 * @return false|mixed|null
	 */
	protected function getConf($data, $key)
	{
		global $conf;
		if (empty($data[$key])) return null;
		if (empty($conf->global->{$data[$key]})) return false;
		return $conf->global->{$data[$key]};
	}

	/**
	 * @param string $langsKey        translation key
	 * @param string ...$langsArgs    any arguments to pass to $langs->transnoentities()
	 * @throws Exception  if Incorrect Log Level
	 */
	protected function setWarning($langsKey, ...$langsArgs)
	{
		global $langs;
		$message = $langs->transnoentities($langsKey, ...$langsArgs);
		if (!in_array($message, $this->warnings)) {
			$this->warnings[] = $message;
			dol_syslog(get_class($this) . ":: " . end($this->warnings), LOG_ERR);
			reset($this->warnings);
		}
	}

	/**
	 * Issue warnings if confs (specified in $confNamesForWarning) are missing (unset or empty)
	 *
	 * @param array $confNamesForWarning  Array mapping conf names to translation keys
	 */
	private function _warnIfConfsMissing($confNamesForWarning)
	{
		global $langs, $conf;
		foreach ($confNamesForWarning as $confName => $confLabelKey) {
			if (empty($conf->global->{$confName})) {
				$this->setWarning('AgfErroVarNotSetBPF', $langs->transnoentities($confLabelKey));
			}
		}
	}
}
