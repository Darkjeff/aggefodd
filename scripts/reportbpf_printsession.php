<?php

$res = @include "../../main.inc.php"; // For root directory
if (! $res)
	$res = @include "../../../main.inc.php"; // For "custom" directory
if (! $res)
	die("Include of main fails");


dol_include_once('/core/lib/functions.lib.php');
include_once '../class/report_bpf.class.php';


/* Ce script a pour objectif de retourner la liste des ids des sessions prises en compte par le rapport BPF sur une période donnée */

global $db;


if (empty($user->admin)) {
	restrictedArea($user, 'agefoddadminarea');
}


$search_date_start = GETPOST('search_date_start');
if (empty($search_date_start)) {
	$search_date_start = date('Y-m-d');
}

$search_date_end= GETPOST('search_date_end');
if (empty($search_date_end)) {
	$search_date_end = date('Y-m-d');
}

?>
	<link rel="stylesheet" type="text/css" href="<?php echo DOL_MAIN_URL_ROOT . "/theme/eldy/style.css.php" ?>">
	<style>
		.sqlquery {
			width: 100%;
			height: 400px;
		}
		.error {
			background: pink;
			padding: 1em 1em;
			margin: 0.4em 0.4em;
		}
		tr.fac th {
			text-align: left;
		}
		tr.facdet td {
			text-align: left;
			padding-left: 2em;
		}
		tr.toptitle th {
			text-align: left;
		}
		div.invoice-lines-not-in-section-c {
			max-width: 800px;
		}
		summary > h3 {
			display: inline-block;
		}
	</style>
	<?php
	print '<form action="'.$_SERVER['PHP_SELF'].'" method="get" >';
	print '<fieldset>';
	print '<legend>'.$langs->trans('Dates').' BPF</legend>';

	print $langs->trans('StartDate').' : <input type="date" name="search_date_start" value="'.$search_date_start.'" >';
	print $langs->trans('EndDate').' : <input type="date" name="search_date_end" value="'.$search_date_end.'" >';
	print '<hr/>';
	print '<button type="submit" >'.$langs->trans('Search').'</button>';
	print '</fieldset>';
	print '</form>';

	if (!empty($search_date_start) && !empty($search_date_end)) {
		// /!\ A mettre à jour si des changements dans le tableau d'origine fonction fetch_financial_c() : dernière mise à jour 2021-04-22
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

		$filter['search_date_start'] = $search_date_start;
		$filter['search_date_end'] = $search_date_end;

		$TSessions = array();
		$TSessionsFinal = array();
		$TSessionsExclude = array();

		//on affiche toutes les sessions prises en compte par catégorie
		foreach ($array_fin as $key => $data) {
			$result = _getSessionFin($data, $filter);
			if (is_array($result)) $TSessions[$data['label']] = $result;
		}

		$result = _getSessionFinC11($filter);
		if (is_array($result)) $TSessions['C-11'] = $result;

		$result = _getSessionFinC13($filter);
		if (is_array($result)) $TSessions['C-13 Autres produits au titre de la formation professionnelle continue'] = $result;

		foreach ($TSessions as $key => $TSessionsIds) {
			print '<br>';
			print $key . ': ';
			foreach ($TSessionsIds as $value) {
				if (!in_array($value, $TSessionsFinal)) $TSessionsFinal[] = $value;
				print $value;
				print ' ,';
			}
			print '<br>';
		}

		//on affiche toutes les sessions utilisées par le rapport BPF toutes catégories confondues
		if (!empty($TSessionsFinal)) {
			print '<br>';
			print 'Toutes les sessions prises en compte : ';
			foreach ($TSessionsFinal as $value) print $value . ',';
			print '<br>';
		}

		//on affiche toutes les sessions exclues par le rapport BPF
		$result = _getSessionFinExclude($TSessionsFinal, $filter);
		if (is_array($result)) $TSessionsExclude = $result;

		if (!empty($TSessionsExclude)) {
			print '<br>' . 'Toutes les sessions exclues : ';
			foreach ($TSessionsExclude as $value) print $value . ',';
			print '<br>';
		}

		// on affiche la requête pour avoir les lignes de facture exclues de la section C du rapport BPF
		$reportBPF = new ReportBPF($db, $langs);
		$reportBPF->fetch_financial_c($filter);
		$sql = $reportBPF->getSQLQueryForInvoiceLinesNotInSectionC($filter);
		$sql = preg_replace('/(\b|[)(\'])(   )/', "$1\n$2", $sql); // formatage pour la lisibilité

		echo "<div><hr/>";
		echo "<details>",
		"<summary><h3>Lignes de factures exclues de la section C du rapport (cliquer pour voir la requête):</h3></summary>",
		"<textarea class='sqlquery'>$sql</textarea>",
		"</details>";
		echo _getInvoiceLinesNotInSectionC($sql);



		echo "</div>";
	} else {
		print 'Le script a besoin des paramètres suivants : search_date_start=YYYY-MM-dd & search_date_end=YYYY-MM-dd' ;
	}


	function _getSessionFin($data = array(), $filter)
	{
		global $conf, $langs, $db;

		if (! empty($data['confprod']) && !empty($conf->global->{$data['confprod']}) || (! empty($data['confcust']) && !empty($conf->global->{$data['confcust']}))) {
			$sqldebug = ' SELECT DISTINCT f.rowid ';
			$sql = " SELECT DISTINCT (agfs.rowid) as sessrowid ";
			$sqlrest =  " FROM
			    " . MAIN_DB_PREFIX . "facturedet AS fd
			        INNER JOIN
			    " . MAIN_DB_PREFIX . "facture AS f ON f.rowid = fd.fk_facture ";
			if (!empty($data['employer'])) {
				$sqlrest .= " AND (f.datef BETWEEN '" . $db->idate($filter['search_date_start']) . "' AND '" . $db->idate($filter['search_date_end'])."')";
			}
			if (!empty($data['datefac'])) {
				$sqlrest .= " AND (f.datef BETWEEN '" . $db->idate($filter['search_date_start']) . "' AND '" . $db->idate($filter['search_date_end'])."')";
			}
			$sqlrest .= " INNER JOIN ".MAIN_DB_PREFIX."agefodd_session_element agfse ON agfse.fk_element = f.rowid AND agfse.element_type = 'invoice' ";
			$sqlrest .= " INNER JOIN ".MAIN_DB_PREFIX."agefodd_session agfs ON agfs.rowid = agfse.fk_session_agefodd ";
			$sqlrest .= " WHERE
			    f.fk_statut IN (1 , 2) ";
			if (! empty($data['confprod']) && !empty($conf->global->{$data['confprod']})) {
				$sqlrest .= " AND fd.fk_product IN (SELECT
			            cp.fk_product
			        FROM
			            " . MAIN_DB_PREFIX . "categorie_product AS cp
			        WHERE
			            cp.fk_categorie IN (" . $conf->global->{$data['confprod']} . "))";
			}

			if (! empty($data['confcust']) && !empty($conf->global->{$data['confcust']})) {
				$sqlrest .= " AND f.fk_soc IN (SELECT
			            cs.fk_soc
			        FROM
			            " . MAIN_DB_PREFIX . "categorie_societe AS cs
			        WHERE
			            cs.fk_categorie IN (" . $conf->global->{$data['confcust']} . "))";
			}

			$sqlrest .= " AND ( (f.rowid IN (SELECT DISTINCT
			            factin.rowid
			        FROM
			            " . MAIN_DB_PREFIX . "agefodd_session_element AS se
			                INNER JOIN
			            " . MAIN_DB_PREFIX . "agefodd_session AS sess ON sess.rowid = se.fk_session_agefodd
			                AND se.element_type = 'invoice'
							AND sess.status IN (5,6)
							INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_calendrier as statime ON statime.fk_agefodd_session=sess.rowid
							AND statime.heured >= '" . $db->idate($filter['search_date_start']) . "' AND statime.heuref <= '" . $db->idate($filter['search_date_end']) . "'";

			if (! empty($data['employer'])) {
				$sqlrest .= " AND sess.fk_soc_employer IS NOT NULL ";
			}
			$sqlrest .= " INNER JOIN
			            " . MAIN_DB_PREFIX . "agefodd_session_stagiaire AS ss ON ss.fk_session_agefodd = sess.rowid
			                AND ss.fk_agefodd_stagiaire_type IN (" . $data['idtypesta'] . ")";
			if (empty($data['checkOPCA']) && empty($data['employer'])) {
				$sqlrest .= " INNER JOIN
			            " . MAIN_DB_PREFIX . "agefodd_stagiaire AS sta ON sta.rowid = ss.fk_stagiaire";
				$sqlrest .= " INNER JOIN
			            " . MAIN_DB_PREFIX . "facture AS factin ON ";
				if (empty($data['checkPV'])) {
					$sqlrest .= " factin.fk_soc = sta.fk_soc AND ";
				}
				if (array_key_exists('checkaltfin', $data) && !empty($data['checkaltfin'])) {
					$sqlrest .= " factin.fk_soc = ss.fk_soc_link AND ";
				}
				$sqlrest .= " factin.rowid=se.fk_element))";
			} elseif (!empty($data['checkOPCA'])) {
				$sqlrest .= " INNER JOIN
			            " . MAIN_DB_PREFIX . "agefodd_opca AS opca ON opca.fk_session_trainee = ss.rowid AND opca.fk_session_agefodd=sess.rowid
			                INNER JOIN
			            " . MAIN_DB_PREFIX . "facture AS factin ON factin.fk_soc = opca.fk_soc_OPCA AND factin.rowid=se.fk_element))";
			} elseif (!empty($data['employer'])) {
				$sqlrest .= " INNER JOIN
			            " . MAIN_DB_PREFIX . "facture AS factin ON factin.fk_soc = sess.fk_soc_employer AND factin.rowid=se.fk_element))";
			}
			if (! empty($data['checkOPCA'])) {
				$sqlrest .= " OR (f.rowid IN (SELECT DISTINCT
			            factinopca.rowid
			        FROM
			            " . MAIN_DB_PREFIX . "agefodd_session_element AS seopca
			                INNER JOIN
			            " . MAIN_DB_PREFIX . "agefodd_session AS sessopca ON sessopca.rowid = seopca.fk_session_agefodd
			                AND seopca.element_type = 'invoice'
							AND sessopca.status IN (5,6)
							INNER JOIN " . MAIN_DB_PREFIX . "agefodd_session_calendrier as statimeopca ON statimeopca.fk_agefodd_session=sessopca.rowid
							AND statimeopca.heured >= '" . $db->idate($filter['search_date_start']) . "' AND statimeopca.heuref <= '" . $db->idate($filter['search_date_end']) . "'";
				if (! empty($data['employer'])) {
					$sqlrest .= "  AND sessopca.fk_soc_employer IS NOT NULL ";
				}
				$sqlrest .= " 	INNER JOIN
			            " . MAIN_DB_PREFIX . "agefodd_session_stagiaire AS ssopca ON ssopca.fk_session_agefodd = sessopca.rowid
			                AND ssopca.fk_agefodd_stagiaire_type IN (" . $data['idtypesta'] . ")
 							INNER JOIN
			            " . MAIN_DB_PREFIX . "facture AS factinopca ON factinopca.fk_soc = sessopca.fk_soc_OPCA AND factinopca.rowid=seopca.fk_element))";
			}
			$sqlrest .= ")";

			$sql = $sql.$sqlrest;

			//      print_r($sql);

			$TSessions = array();
			$TSessionsExclude = array();

			$resql = $db->query($sql);
			if ($resql) {
				if ($db->num_rows($resql)) {
					while ($obj = $db->fetch_object($resql)) {
						if (!in_array($obj->sessrowid, $TSessions)) $TSessions[] = $obj->sessrowid;
					}

					return $TSessions;
				}
			} else {
				return - 1;
			}
			$db->free($resql);
		}

		return 1;
	}

	function _getSessionFinC11($filter)
	{
		global $conf, $langs, $db;

		$TSessions = array();

		if (! empty($conf->global->AGF_CAT_BPF_TOOLPEDA)) {
			$sql = " SELECT DISTINCT (agfs.rowid) as sessrowid ";
			$sqlrest = "
			FROM
			    " . MAIN_DB_PREFIX . "facturedet AS fd
			        INNER JOIN
			    " . MAIN_DB_PREFIX . "facture AS f ON f.rowid = fd.fk_facture ";
			$sqlrest .= " INNER JOIN ".MAIN_DB_PREFIX."agefodd_session_element agfse ON agfse.fk_element = f.rowid AND agfse.element_type = 'invoice' ";
			$sqlrest .= " INNER JOIN ".MAIN_DB_PREFIX."agefodd_session agfs ON agfs.rowid = agfse.fk_session_agefodd ";
			$sqlrest .= "
			WHERE
			    f.fk_statut IN (1 , 2)
				AND f.datef BETWEEN '" . $db->idate($filter['search_date_start']) . "' AND '" . $db->idate($filter['search_date_end']) . "'
		 			AND fd.fk_product IN (SELECT
			            cp.fk_product
			        FROM
			            " . MAIN_DB_PREFIX . "categorie_product AS cp
			        WHERE
			            cp.fk_categorie IN (" . $conf->global->AGF_CAT_BPF_TOOLPEDA . "))";

			$sql = $sql.$sqlrest;

			$resql = $db->query($sql);
			if ($resql) {
				if ($db->num_rows($resql)) {
					while ($obj = $db->fetch_object($resql)) {
						$TSessions = $obj->amount;
					}
					return $TSessions;
				}
			} else {
				return - 1;
			}
			$db->free($resql);
		}

		return 1;
	}

	function _getSessionFinC13($filter)
	{
		global $conf, $langs, $db;


		$TSessions = array();

		if (! empty($conf->global->AGF_CAT_PRODUCT_CHARGES)) {
			$sqldebug = ' SELECT DISTINCT f.rowid ';
			$sql = " SELECT DISTINCT (agfs.rowid) as sessrowid ";
			$sqlrest = "
				FROM
				    " . MAIN_DB_PREFIX . "facturedet AS fd
				        INNER JOIN
				    " . MAIN_DB_PREFIX . "facture AS f ON f.rowid = fd.fk_facture ";
			$sqlrest .= " INNER JOIN ".MAIN_DB_PREFIX."agefodd_session_element agfse ON agfse.fk_element = f.rowid AND agfse.element_type = 'invoice' ";
			$sqlrest .= " INNER JOIN ".MAIN_DB_PREFIX."agefodd_session agfs ON agfs.rowid = agfse.fk_session_agefodd ";
			$sqlrest .= "
				WHERE
				    f.fk_statut IN (1 , 2)
					AND f.datef BETWEEN '" . $db->idate($filter['search_date_start']) . "' AND '" . $db->idate($filter['search_date_end']) . "'
			 			AND fd.fk_product IN (SELECT
				            cp.fk_product
				        FROM
				            " . MAIN_DB_PREFIX . "categorie_product AS cp
				        WHERE
				            cp.fk_categorie IN (" . $conf->global->AGF_CAT_PRODUCT_CHARGES . "))
					AND f.rowid IN (SELECT DISTINCT
				            factin.rowid
				        FROM
				            " . MAIN_DB_PREFIX . "agefodd_session_element AS se
				                INNER JOIN
				            " . MAIN_DB_PREFIX . "agefodd_session AS sess ON sess.rowid = se.fk_session_agefodd
				                AND se.element_type = 'invoice'
				                AND sess.dated BETWEEN '" . $db->idate($filter['search_date_start']) . "' AND '" . $db->idate($filter['search_date_end']) . "'
								AND sess.status IN (5,6)
								INNER JOIN
							" . MAIN_DB_PREFIX . "facture AS factin ON factin.rowid=se.fk_element)";

			$sql = $sql.$sqlrest;

			$sqldebugarray[]='('.$sqldebug.$sqlrest.')';

			$resql = $db->query($sql);
			if ($resql) {
				if ($db->num_rows($resql)) {
					while ($obj = $db->fetch_object($resql)) {
						$TSessions[] = $obj->sessrowid;
					}
					return $TSessions;
				}
			} else {
				return - 1;
			}
			$db->free($resql);
		}

		if (! empty($conf->global->AGF_CAT_BPF_PRODPEDA) && ! empty($conf->global->AGF_CAT_BPF_FOREIGNCOMP)) {
			$sqldebug = ' SELECT DISTINCT f.rowid ';
			$sql = " SELECT DISTINCT (agfs.rowid) as sessrowid ";
			$sqlrest = "
			FROM
			    " . MAIN_DB_PREFIX . "facturedet AS fd
			        INNER JOIN
			    " . MAIN_DB_PREFIX . "facture AS f ON f.rowid = fd.fk_facture ";
			$sqlrest .= " INNER JOIN ".MAIN_DB_PREFIX."agefodd_session_element agfse ON agfse.fk_element = f.rowid AND agfse.element_type = 'invoice' ";
			$sqlrest .= " INNER JOIN ".MAIN_DB_PREFIX."agefodd_session agfs ON agfs.rowid = agfse.fk_session_agefodd ";
			$sqlrest .= "
			WHERE
			    f.fk_statut IN (1 , 2)
				AND f.datef BETWEEN '" . $db->idate($filter['search_date_start']) . "' AND '" . $db->idate($filter['search_date_end']) . "'
		 			AND fd.fk_product IN (SELECT
			            cp.fk_product
			        FROM
			            " . MAIN_DB_PREFIX . "categorie_product AS cp
			        WHERE
			            cp.fk_categorie IN (" . $conf->global->AGF_CAT_BPF_PRODPEDA . "))
				AND f.fk_soc IN (SELECT
			            cs.fk_soc
			        FROM
			            " . MAIN_DB_PREFIX . "categorie_societe AS cs
			        WHERE
			            cs.fk_categorie IN (" . $conf->global->AGF_CAT_BPF_FOREIGNCOMP . "))
				AND f.rowid IN (SELECT DISTINCT
			            factin.rowid
			        FROM
			            " . MAIN_DB_PREFIX . "agefodd_session_element AS se
			                INNER JOIN
			            " . MAIN_DB_PREFIX . "agefodd_session AS sess ON sess.rowid = se.fk_session_agefodd
			                AND se.element_type = 'invoice'
			                AND sess.dated BETWEEN '" . $db->idate($filter['search_date_start']) . "' AND '" . $db->idate($filter['search_date_end']) . "'
							AND sess.status IN (5,6)
							INNER JOIN
						" . MAIN_DB_PREFIX . "facture AS factin ON factin.rowid=se.fk_element
							INNER JOIN
						" . MAIN_DB_PREFIX . "agefodd_place as pl ON pl.rowid=sess.fk_session_place
							AND pl.fk_pays<>1)";

			$sql = $sql.$sqlrest;

			$resql = $db->query($sql);
			if ($resql) {
				if ($db->num_rows($resql)) {
					while ($obj = $db->fetch_object($resql)) {
						$TSessions[] = $obj->sessrowid;
					}

					return $TSessions;
				}
			} else {
				return - 1;
			}
			$db->free($resql);
		}

		return 1;
	}

	function _getSessionFinExclude($TSessions, $filter)
	{
		global $db;

		$TSessionsExclude = array();

		$sql = "SELECT DISTINCT sess.rowid as sessrowid";
		$sql.= " FROM ".MAIN_DB_PREFIX."agefodd_session as sess";
		$sql.= " INNER JOIN ".MAIN_DB_PREFIX."agefodd_session_calendrier  AS statime ON statime.fk_agefodd_session = sess.rowid AND statime.heured >= '".$db->idate($filter['search_date_start'])."' AND statime.heuref <= '".$db->idate($filter['search_date_end'])."'";

		$resql = $db->query($sql);

		if ($resql) {
			if ($db->num_rows($resql) >= 1) {
				while ($obj = $db->fetch_object($resql)) {
					if (!in_array($obj->sessrowid, $TSessions)) {
						$TSessionsExclude[] = $obj->sessrowid;
					}
				}
				return $TSessionsExclude;
			}
		} else {
			return -1;
		}

		return 0;
	}


	/*
	Retours wysiwyg.

	SORT BY facid asc

	pour chaque transition d'ID, créer un <thead> avec les infos facture (date, montant).

	*/


	function _getInvoiceLinesNotInSectionC($sql)
	{
		global $db;
		$resql = $db->query($sql);
		if (!$resql) {
			$error = $db->lasterror();
			return "<div class='error'>ERROR: {$error}</div>";
		}
		$ret =
		'<table class="liste">'
		. '  <thead>'
		. '    <tr class="toptitle">'
		. '      <th>  Facture'
		. '      </th>'
		. '      <th>  Montant facture'
		. '      </th>'
		. '      <th>  Montant ligne'
		. '      </th>'
		. '    </tr>'
		. '  </thead>'
		;

		$n = 0;
		$lastfac = 0;
		while ($obj = $db->fetch_object($resql)) {
			$curfac = intval($obj->facture_debug_rowid);
			if ($curfac !== $lastfac) {
				$lastfac = $curfac;
				// nouvelle ligne de titre avec les infos de la facture
				$linkurl = DOL_MAIN_URL_ROOT . "/compta/facture/card.php?facid={$obj->facture_debug_rowid}";
				if ($n > 0) $ret .= "\n</tbody>";
				$total_fac_ht = price($obj->facture_debug_total_ht);
				$ret .=
				"\n<thead>"
				. "  <tr class='fac'>"
				. "    <th class='facref'><a href='{$linkurl}'>{$obj->facture_debug_ref}</a></th>"
				. "    <th class='factotalht'>{$total_fac_ht}</th>"
				. "    <th></th>"
				. "  </tr>"
				. "</thead>"
				. "<tbody>"
				;
			}
			$total_line_ht = price($obj->facture_det_debug_total_ht);
			$ret .=
			"<tr class='facdet'>"
			. "<td>ligne {$obj->facture_det_debug_rowid}</td>"
			. "<td></td>"
			. "<td>{$total_line_ht}</td>"
			. "</tr>"
			;
			$n++;
		}
		$ret .= "\n</table>";
		return "<div class='invoice-lines-not-in-section-c'>{$ret}</div>";
	}
