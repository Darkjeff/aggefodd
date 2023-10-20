<?php
/*
 * Copyright (C) 2009-2010	Erick Bullier		<eb.dev@ebiconsulting.fr>
 * Copyright (C) 2012-2016 Florian Henry <florian.henry@open-concept.pro>
 * Copyright (C) 2014-2015 	Philippe Grand 	<philippe.grand@atoo-net.com>
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
 * \file agefodd/core/modules/agefodd/pdf/pdf_attestation.modules.php
 * \ingroup agefodd
 * \brief PDF for certificate (attestation)
 */
dol_include_once('/agefodd/core/modules/agefodd/modules_agefodd.php');
dol_include_once('/agefodd/class/agsession.class.php');
dol_include_once('/agefodd/class/agefodd_formation_catalogue.class.php');
dol_include_once('/agefodd/class/agefodd_session_stagiaire.class.php');
dol_include_once('/agefodd/class/agefodd_session_catalogue.class.php');
dol_include_once('/agefodd/class/agefodd_place.class.php');
dol_include_once('/agefodd/class/agefodd_session_formateur.class.php');
require_once (DOL_DOCUMENT_ROOT . '/core/lib/company.lib.php');
require_once (DOL_DOCUMENT_ROOT . '/core/lib/pdf.lib.php');
dol_include_once('/agefodd/lib/agefodd.lib.php');
require_once (DOL_DOCUMENT_ROOT . '/contact/class/contact.class.php');
class pdf_attestationendtraining_trainee extends ModelePDFAgefodd {
	var $emetteur; // Objet societe qui emet

	// Definition des couleurs utilisées de façon globales dans le document (charte)
	protected $colorfooter;
	protected $colortext;
	protected $colorhead;
	protected $colorheaderBg;
	protected $colorheaderText;
	protected $colorLine;

	/**
	 * @var int $stampOverlapTolerance Tolérance au chevauchement sur le tampon
	 *                                 (on autorise à dépasser de quelques millimètres
	 *                                 car il y a souvent un peu d'espace entre le
	 *                                 "Fait à…" et le nom du responsable)
	 */
	public $stampOverlapTolerance = 15;

	// La taille du tampon sera réduite si besoin, en préservant le ratio d'aspect
	/** @var int $stampMaxWidth Largeur maxi du tampon / signature */
	public $stampMaxWidth = 60;
	/** @var int $stampMaxHeight Hauteur maxi du tampon / signature */
	public $stampMaxHeight = 35;
	/** @var int $stampRightOffset Espacement entre la marge droite et le tampon */
	public $stampRightOffset = 10;
	/** @var int $stampBottomOffset Espacement entre la marge basse et le tampon */
	public $stampBottomOffset = 0;

	// La taille des logos sera réduite si besoin, en préservant le ratio d'aspect
	/** @var int $logoMaxWidth Largeur maxi des logos*/
	public $logoMaxWidth = 40;
	/** @var int $logoMaxHeight Hauteur maxi des logos */
	public $logoMaxHeight = 30;
	public $footerHeight;
	/**
	 * \brief Constructor
	 * \param db Database handler
	 */
	function __construct($db) {
		global $conf, $langs, $mysoc;

		$this->db = $db;
		$this->name = "attestationendtrainng_trainee";
		$this->description = $langs->trans('AgfPDFAttestation');

		// Dimension page pour format A4 en paysage
		$this->type = 'pdf';
		$formatarray = pdf_getFormat();
		$this->page_largeur = $formatarray['width'];
		$this->page_hauteur = $formatarray['height'];
		$this->format = array (
				$this->page_largeur,
				$this->page_hauteur
		);
		$this->marge_gauche = 15;
		$this->marge_droite = 15;
		$this->marge_haute = 10;
		$this->marge_basse = 10;
		$this->unit = 'mm';
		$this->oriantation = 'P';
		$this->espaceH_dispo = $this->page_largeur - ($this->marge_gauche + $this->marge_droite);
		$this->milieu = $this->espaceH_dispo / 2;

		$this->colorfooter = agf_hex2rgb($conf->global->AGF_FOOT_COLOR);
		$this->colortext = agf_hex2rgb($conf->global->AGF_TEXT_COLOR);
		$this->colorhead = agf_hex2rgb($conf->global->AGF_HEAD_COLOR);
		$this->colorheaderBg = agf_hex2rgb($conf->global->AGF_HEADER_COLOR_BG);
		$this->colorheaderText = agf_hex2rgb($conf->global->AGF_HEADER_COLOR_TEXT);
		$this->colorLine = agf_hex2rgb($conf->global->AGF_COLOR_LINE);
		$this->footerHeight = $this->marge_basse + 8;

		// Get source company
		$this->emetteur = $mysoc;
		if (! $this->emetteur->country_code)
			$this->emetteur->country_code = substr($langs->defaultlang, - 2); // By default, if was not defined
	}

	/**
	 * \brief Fonction generant le document sur le disque
	 * \param agf Objet document a generer (ou id si ancienne methode)
	 * outputlangs Lang object for output language
	 * file Name of file to generate
	 * \return int 1=ok, 0=ko
	 */
	function write_file($agf, $outputlangs, $file, $session_trainee_id) {
		global $user, $langs, $conf, $mysoc, $hookmanager;

		/*
		Pourquoi tous les styles ne sont pas utilisés ? c'est du copier-coller depuis
		pdf_attestation_trainee.modules.php.

		Ces styles prédéfinis ici ne sont pas du CSS même si le concept s'en inspire.

		Ce sont des styles de paragraphes utilisés par la méthode
		printHTML(), ils permettent de compléter lorsque le moteur de rendu
		HTML de TCPDF ne prend pas en charge certaines fonctionnalités (comme
		les marges gauche / droite ou le padding).

		Les styles commençant par "page-" sont des styles de pages qui permettent
		d'activer automatiquement des fonctionnalités (logos, tampons…) à l'ajout
		d'une nouvelle page en utilisant $this->newPage().
		*/
		$this->styles = array(
			'title' => array('font-size' => 20, 'color' => $this->colorhead, 'text-align' => 'C'),
			'default' => array(
				'font-family'    => pdf_getPDFFont($langs),
				'font-size'      => 12,
				'color'          => $this->colortext,
				'text-align'     => '',
				'margin-left'    => 0,
				'margin-top'     => 0,
				'margin-bottom'  => 0,
				'margin-right'   => 0,
				'padding-left'   => 5,
				'padding-top'    => 5,
				'padding-bottom' => 5,
				'padding-right'  => 5,
			),
			'center' => array('text-align' => 'C'),
			'trainingModuleTitle' => array('text-align' => 'C', 'font-size' => 20),
			'bulletList' => array(
				'margin-left'    => 20,
				'margin-top'     => 0,
				'margin-right'   => 20,
				'margin-bottom'  => 0,
				'padding-left'   => 0,
				'padding-top'    => 0,
				'padding-right'  => 0,
				'padding-bottom' => 0,
			),
			'page-default' => array(
				'use-header'        => true,
				'use-footer'        => true,
				'use-company-logo'  => true,
				'use-company-stamp' => true,
				'use-client-logo'   => false,
				'use-frame'         => false,
				'use-tpl'           => true,
				'orientation'       => 'P',
			),
		);

		if (! is_object($outputlangs))
			$outputlangs = $langs;

		if (! is_object($agf)) {
			$id = $agf;
			$agf = new Agsession($this->db);
			$ret = $agf->fetch($id);
		}

		// Definition of $dir and $file
		$dir = $conf->agefodd->dir_output;
		$file = $dir . '/' . $file;

		if (! file_exists($dir)) {
			if (dol_mkdir($dir) < 0) {
				$this->error = $langs->trans("ErrorCanNotCreateDir", $dir);
				return 0;
			}
		}

		if (file_exists($dir)) {
			$this->pdf = $pdf = pdf_getInstance($this->format, $this->unit, $this->orientation);

			$pdf->Open();
			$pagenb = 0;

			if (class_exists('TCPDF')) {
				$pdf->setPrintHeader(false);
				$pdf->setPrintFooter(false);
			}

			$pdf->SetTitle($outputlangs->convToOutputCharset($agf->ref));
			$pdf->SetSubject($outputlangs->transnoentities("Invoice"));
			$pdf->SetCreator("Dolibarr " . DOL_VERSION . ' (Agefodd module)');
			$pdf->SetAuthor($outputlangs->convToOutputCharset($user->fullname));
			$pdf->SetKeyWords($outputlangs->convToOutputCharset($agf->ref) . " " . $outputlangs->transnoentities("Document"));
			if ($conf->global->MAIN_DISABLE_PDF_COMPRESSION)
				$pdf->SetCompression(false);

			$pdf->SetMargins($this->marge_gauche, $this->marge_haute, $this->marge_droite); // Left, Top, Right
			$pdf->SetAutoPageBreak(1, 0);

			// Set path to the background PDF File
			if (empty($conf->global->MAIN_DISABLE_FPDI) && ! empty($conf->global->AGF_ADD_PDF_BACKGROUND_P))
			{
				$pagecount = $pdf->setSourceFile($conf->agefodd->dir_output . '/background/' . $conf->global->AGF_ADD_PDF_BACKGROUND_P);
				$tplidx = $pdf->importPage(1);
			}

			/** SWITCH OBJECT FORMATION - SESSION_CATALOGUE ---------------------------------  */
			$agf_sessioncal = new SessionCatalogue($this->db); // formation clone
			$ret = $agf_sessioncal->fetchSessionCatalogue($id); // par default ça fetch le clone

			$agf_session = new Agsession($this->db);
			$retSession = $agf_session->fetch($id);

			if (empty($ret)) // pas de clone
			{

				if ($retSession > 0 ){

					$agf_op = new Formation($this->db);
					$agf_op->fetch($agf_session->fk_formation_catalogue);
					$agf_op->fetch_objpeda_per_formation($agf->fk_formation_catalogue);

				}else{
					$agf_op = new Formation($this->db); // prevent error on foreach
					setEventMessage('errorloadSession','errors');
				}


			}else{
				$agf_op = new SessionCatalogue($this->db);
				$agf_op->fetch($ret);
				$agf_op->fetch_objpeda_per_session_catalogue($ret);
			}
			/** ---------------------------------  */

			// Recuperation des informations du lieu de la session
			$agf_place = new Agefodd_place($this->db);
			$result = $agf_place->fetch($agf->placeid);

			// Recuperation des informations des formateurs
			$agf_session_trainer = new Agefodd_session_formateur($this->db);
			$agf_session_trainer->fetch_formateur_per_session($id);

			$agf_session_trainee = new Agefodd_session_stagiaire($this->db);
			$agf_session_trainee->fetch($session_trainee_id);

			$agf_trainee = new Agefodd_stagiaire($this->db);
			$agf_trainee->fetch($agf_session_trainee->fk_stagiaire);

			$trainer_arr=array();
			foreach($agf_session_trainer->lines as $trainer) {
				$trainer_arr[] = $trainer->firstname ." ". $trainer->lastname;
			}
			$trainer_str = implode("<br>", $trainer_arr);

			/**
			 * styles CSS définis dans le template destinés au rendu HTML par TCPDF
			 * @var string $css
			 */
			$defaultColor = 'rgb(' . implode(',', $this->colortext) . ')';
			$titleColor = 'rgb(' . implode(',', $this->colorhead) . ')';
			include __DIR__ . '/htmltpl/pdf_attestation_trainee.tpl.php';

			// New page
			$this->newPage($agf_session, $outputlangs, $tplidx);

			$pagenb ++;

			// calcul de la taille du bloc qui ira tout en bas
			$pdf->startTransaction();
			$y = $pdf->GetY();

			$bottomBlockHTML = $this->applyTplSubst('<div style="font-size: 9pt">'
				. '<table style="width: 150mm;">'
				. '<tr>'
				// Lieu : …………
				. '  <td><u>{AgfLieu} :</u></td>'
				. "  <td>$agf_place->ref_interne, $agf_place->adresse, $agf_place->cp, $agf_place->ville</td>"
				. '</tr><tr>'
				. '<td colspan="2"><br><br>'
				// Fait pour servir et valoir ce que de droit
				. '{AgfPDFAttestation10} <br><br>'
				. '</td>'
				. '</tr><tr>'
				. '<td>'
				// <ma ville>, le jj/mm/yyyy
				. "$mysoc->town, {AgfPDFFichePres8} " . dol_print_date($agf->datef)
				. '</td>'
				// Le(s) formateur(s) : Untel, Unetelle, etc.
				. '<td><br><br>{AgfTrainerPDF} : <br>' . $trainer_str . '</td>'
				. '</tr>'
				. '</table>'
				. '</div>'
				, $outputlangs);
			$this->printHTML($css . $bottomBlockHTML, 'default', true);
			$sizeOfBottomBlock = $pdf->GetY() - $y;
			unset($y);
			$pdf->rollbackTransaction(true);

			// ATTESTATION DE FIN DE FORMATION
			$pdf->SetY($pdf->GetY() + 3);
			$pdf->SetDrawColor($this->colorhead[0], $this->colorhead[1], $this->colorhead[2]);
			$pdf->Line($this->marge_gauche, $pdf->GetY(), $this->page_largeur - $this->marge_droite, $pdf->GetY(), '');
			$this->printHTML($css . $this->applyTplSubst('<h1 class="title centered">{AgfPDFAttestationEnd1}</h1>', $outputlangs), 'default');
			$pdf->Line($this->marge_gauche, $pdf->GetY(), $this->page_largeur - $this->marge_droite, $pdf->GetY(), '');

			// certifie que ………

			$contact_static = new Contact($this->db);
			$contact_static->civility_id = $agf_trainee->civilite;

			$civility = ucfirst(strtolower($contact_static->getCivilityLabel())) . ' ';

			// <Mon organisme de forma> certifie que, conformément aux dispositions etc.
			//               Untel
			// a effectivement suivi avec assiduité le module […] intitulé
			//               « nom du module »
			$this->printHTML(
				$css
				. $this->applyTplSubst(
					'<p>'
					. $this->emetteur->name . '{AgfPDFAttestationEnd2}'
					. '</p>'
					. '<p class="centered">'
					.  '<span style="font-size: 13pt">'. $civility . ' ' . $agf_trainee->prenom . ' ' . $agf_trainee->nom . '</span>'
					. '</p>'
					. '<p class="centered">'
					. '{AgfPDFAttestation3}'
					. '</p>'
					. '<p class="centered big" style="font-size: 13pt">'.'« '.$agf->intitule_custo.' »'.'</p>'
					, $outputlangs),
				'default',
				true
			);

			if (! empty($conf->global->AGF_USE_REAL_HOURS)) {
				dol_include_once('/agefodd/class/agefodd_session_stagiaire_heures.class.php');
				$agfssh = new Agefoddsessionstagiaireheures($this->db);
				$duree_session=$agfssh->heures_stagiaire($agf->id, $agf_session_trainee->fk_stagiaire);
			} else {
				$duree_session=$agf->duree_session;
			}
			// Cette formation s'est déroulée […]
			$this->printHTML($this->applyTplSubst(
				'<p>{AgfPDFAttestation4} '.$agf->libSessionDate().' {AgfPDFAttestation5} '.$duree_session.'{AgfPDFAttestation6}'
				.'</p>', $outputlangs), 'default', true
			);
			if (count($agf_op->lines)) {
				// Évaluation
				$this->printHTML($this->applyTplSubst('<p style="text-decoration: underline">{AgfPDFAttestationEndEval}</p>', $outputlangs), 'default', true);
			}

			// fonction closure pour afficher une ligne du tableau des objectifs
			/**
			 * @param string $objectifs
			 * @param string $acquis
			 * @param string $nonacquis
			 * @param string $encours
			 * @param string $nonevalue
			 * @param bool $isHeaderRow
			 * @return float new Y pos
			 */
			$tableRow = function ($objectifs, $acquis, $nonacquis, $encours, $nonevalue, $isHeaderRow = false) use ($pdf, $outputlangs) {
				static $t = null;
				$colWidth = [20, 20, 20, 20];
				// la première colonne prend toute la place restante
				$firstColWidth = $this->page_largeur - $this->marge_gauche - $this->marge_droite - array_sum($colWidth);
				array_splice($colWidth, 0, 0, [$firstColWidth]);
				if ($t === null) $t = ['top' => $pdf->GetY()];
				$default_font_size = pdf_getPDFFontSize($outputlangs);

				$pdf->SetDrawColor(128, 128, 128);
				if ($isHeaderRow) $pdf->SetFont('', 'B', $default_font_size - 1);
				else $pdf->SetFont('', '', $default_font_size - 1);

//				$style = $this->styles['default'];
				$pdf->setCellPaddings(0, 0, 0, 0);
				$monoCellHeight = $pdf->getCellHeight($pdf->getFontSize());
				$h = 1;
				$tdNum = 0;

				/**
				 * appelle MultiCell pour afficher la cellule à droite de la précédente
				 * récupère la hauteur (en lignes) retournée par MultiCell
				 * compare la hauteur obtenue avec la hauteur max des précédentes pour garder la plus haute valeur
				 *
				 * Note: ça ne fonctionne bien QUE
				 */
				$printTd = function ($content, $align = 'L') use (&$h, &$tdNum, $pdf, $monoCellHeight, $outputlangs, $colWidth, $isHeaderRow) {
					$h = max($h, $pdf->MultiCell(
						// largeur, hauteur
						$colWidth[$tdNum++],
						// le 0.2 permet un espacement plus naturel si la ligne de tableau ne fait qu'une ligne de texte
						max($monoCellHeight + 1, $h * $monoCellHeight + 0.21),
						// texte de la cellule
						$this->applyTplSubst($content, $outputlangs),
						$isHeaderRow ? 1 : 'LRB',
						$align,
						false,
						0
					));
				};
				$pdf->SetXY($this->marge_gauche, $pdf->GetY());
				$printTd($objectifs, 'L');
				$printTd($acquis, 'C');
				$printTd($nonacquis, 'C');
				$printTd($encours, 'C');
				$printTd($nonevalue, 'C');
				$h = max($h * $monoCellHeight, $monoCellHeight + 1);

				$pdf->SetY($pdf->GetY() + $h);

				return $pdf->GetY();
			};

			// ligne d'en-tête
			$tableRow(
				'{AgfObjectifs}',
				'{AgfAcquis}',
				'{AgfNonAcquis}',
				'{AgfEncours}',
				'{AgfNotEvaluated}',
				true
			);

			/* TEST de la fonction avec texte de longueur aléatoire
			$tableRow(str_repeat('test ', rand(5, 160)), 'acquis', 'nonacquis', 'encorus', 'noteval', false);
			$tableRow(str_repeat('test ', rand(5, 160)), 'acquis', 'nonacquis', 'encorus', 'noteval', false);
			*/

			$pdf->SetAutoPageBreak(false);

			$newY = $pdf->GetY();
			// Bloc objectifs pedagogiques
			if (count($agf_op->lines) > 0) {
				$pdf->SetFont(pdf_getPDFFont($outputlangs), 'I', 14);
				$hauteur = 0;
				for($y = 0; $y < count($agf_op->lines); $y ++) {
					$pdf->startTransaction();
					$newY = $tableRow(
						$agf_op->lines[$y]->priorite . '. ' . $agf_op->lines[$y]->intitule,
						empty($conf->global->AGF_ATTESTION_PDF_DEFAULT_NOTAQUIS) ? 'X' : '',
						empty($conf->global->AGF_ATTESTION_PDF_DEFAULT_NOTAQUIS) ? '' : 'X',
						'',
						'',
						false
					);
					if ($newY < $this->page_hauteur - $this->footerHeight - $sizeOfBottomBlock) {
						$pdf->commitTransaction();
						continue;
					}

					// si on doit sauter une page, on répète l'en-tête de tableau
					$pdf->rollbackTransaction(true);
					$this->newPage($agf_session, $outputlangs, $tplidx);
//					$pdf->SetY($pdf->GetY() + 5);
					$this->printHTML(' ', 'default');  // hack pour reset couleurs / polices / padding
					//                                                    pas le temps de faire mieux là maintenant

					// TODO: comprendre pourquoi le padding dans TCPDF a changé ici par rapport à avant le saut de page
					$tableRow(
						'{AgfObjectifs}',
						'{AgfAcquis}',
						'{AgfNonAcquis}',
						'{AgfEncours}',
						'{AgfNotEvaluated}',
						true
					);
					$tableRow(
						$agf_op->lines[$y]->priorite . '. ' . $agf_op->lines[$y]->intitule,
						empty($conf->global->AGF_ATTESTION_PDF_DEFAULT_NOTAQUIS) ? 'X' : '',
						empty($conf->global->AGF_ATTESTION_PDF_DEFAULT_NOTAQUIS) ? '' : 'X',
						'',
						'',
						false
					);

				}
			}
			$this->setAutoPageBreak(true);

			$this->printHTML($css . $bottomBlockHTML, 'default', true);

			$pdf->Close();
			$pdf->Output($file, 'F');
			if (! empty($conf->global->MAIN_UMASK))
				@chmod($file, octdec($conf->global->MAIN_UMASK));


			// Add pdfgeneration hook
			if (! is_object($hookmanager))
			{
				include_once DOL_DOCUMENT_ROOT.'/core/class/hookmanager.class.php';
				$hookmanager=new HookManager($this->db);
			}
			$hookmanager->initHooks(array('pdfgeneration'));
			$parameters=array('file'=>$file,'object'=>$agf,'outputlangs'=>$outputlangs);
			global $action;
			$reshook=$hookmanager->executeHooks('afterPDFCreation',$parameters,$this,$action);    // Note that $action and $object may have been modified by some hooks


			return 1; // Pas d'erreur
		} else {
			$this->error = $langs->trans("ErrorConstantNotDefined", "AGF_OUTPUTDIR");
			return 0;
		}
		$this->error = $langs->trans("ErrorUnknown");
		return 0; // Erreur par defaut
	}

	/**
	 * \brief Show header of page
	 * \param pdf Object PDF
	 * \param object Object invoice
	 * \param showaddress 0=no, 1=yes
	 * \param outputlangs Object lang for output
	 */
	function _pagehead(&$pdf, $object, $showaddress = 1, $outputlangs) {
		global $conf, $langs;

		$outputlangs->load("main");

		$default_font_size = pdf_getPDFFontSize($outputlangs);

		pdf_pagehead($pdf, $outputlangs, $pdf->page_hauteur);

		$pdf->SetTextColor($this->colorhead[0], $this->colorhead[1], $this->colorhead[2]);

		$posy = $this->marge_haute;
		$posx = $this->page_largeur - $this->marge_droite - 55;

		// Logo
		$logo = $conf->mycompany->dir_output . '/logos/' . $this->emetteur->logo;
		if ($this->emetteur->logo) {
			if (is_readable($logo)) {
				$height = pdf_getHeightForLogo($logo);
				$width_logo = pdf_getWidthForLogo($logo);
				if ($width_logo > 0) {
					$posx = $this->page_largeur - $this->marge_droite - $width_logo;
				} else {
					$posx = $this->page_largeur - $this->marge_droite - 55;
				}
				$pdf->Image($logo, $posx, $posy, 0, $height);
			} else {
				$pdf->SetTextColor(200, 0, 0);
				$pdf->SetFont('', 'B', $default_font_size - 2);
				$pdf->MultiCell(100, 3, $outputlangs->transnoentities("ErrorLogoFileNotFound", $logo), 0, 'L');
				$pdf->MultiCell(100, 3, $outputlangs->transnoentities("ErrorGoToGlobalSetup"), 0, 'L');
			}
		} else {
			$text = $this->emetteur->name;
			$pdf->MultiCell(100, 4, $outputlangs->convToOutputCharset($text), 0, 'L');
		}
		// Other Logo
		if ($conf->multicompany->enabled && !empty($conf->global->AGF_MULTICOMPANY_MULTILOGO)) {
			$sql = 'SELECT value FROM ' . MAIN_DB_PREFIX . 'const WHERE name =\'MAIN_INFO_SOCIETE_LOGO\' AND entity=1';
			$resql = $this->db->query($sql);
			if (! $resql) {
				setEventMessage($this->db->lasterror, 'errors');
			} else {
				$obj = $this->db->fetch_object($resql);
				$image_name = $obj->value;
			}
			if (! empty($image_name)) {
				$otherlogo = DOL_DATA_ROOT . '/mycompany/logos/' . $image_name;
				if (is_readable($otherlogo) && $otherlogo!=$logo) {
					$logo_height = pdf_getHeightForLogo($otherlogo, true);
					$pdf->Image($otherlogo, $this->marge_gauche + 80, $posy, 0, $logo_height);
				}
			}
		}
		if ($showaddress) {
			// Sender properties
			// Show sender
			$posy = $this->marge_haute;
			$posx = $this->marge_gauche;

			$hautcadre = 30;
			$pdf->SetXY($posx, $posy);
			$pdf->SetFillColor(255, 255, 255);
			$pdf->MultiCell(70, $hautcadre, "", 0, 'R', 1);

			// Show sender name
			$pdf->SetXY($posx, $posy);
			$pdf->SetFont('', 'B', $default_font_size);
			$pdf->MultiCell(80, 4, $outputlangs->convToOutputCharset($this->emetteur->name), 0, 'L');
			$posy = $pdf->GetY();

			// Show sender information
			$pdf->SetXY($posx, $posy);
			$pdf->SetFont('', '', $default_font_size - 1);
			$pdf->MultiCell(70, 4, $outputlangs->convToOutputCharset($this->emetteur->address), 0, 'L');
			$posy = $pdf->GetY();
			$pdf->SetXY($posx, $posy);
			$pdf->SetFont('', '', $default_font_size - 1);
			$pdf->MultiCell(70, 4, $outputlangs->convToOutputCharset($this->emetteur->zip . ' ' . $this->emetteur->town), 0, 'L');
			$posy = $pdf->GetY();
			$pdf->SetXY($posx, $posy);
			$pdf->SetFont('', '', $default_font_size - 1);
			$pdf->MultiCell(70, 4, $outputlangs->convToOutputCharset($this->emetteur->phone), 0, 'L');
			$posy = $pdf->GetY();
			$pdf->SetXY($posx, $posy);
			$pdf->SetFont('', '', $default_font_size - 1);
			$pdf->MultiCell(70, 4, $outputlangs->convToOutputCharset($this->emetteur->email), 0, 'L');
			$posy = $pdf->GetY();

			printRefIntForma($this->db, $outputlangs, $object, $default_font_size - 1, $pdf, $posx, $posy, 'L');
		}
	}

	/**
	 * \brief Show footer of page
	 * \param pdf PDF factory
	 * \param object Object invoice
	 * \param outputlang Object lang for output
	 * \remarks Need this->emetteur object
	 */
	function _pagefoot(&$pdf, $object, $outputlangs) {
		$pdf->SetAutoPageBreak(false);
		$pdf->SetTextColor($this->colorfooter [0], $this->colorfooter [1], $this->colorfooter [2]);
		$pdf->SetDrawColor($this->colorfooter [0], $this->colorfooter [1], $this->colorfooter [2]);
		$ret = pdf_agfpagefoot($pdf,$outputlangs,'',$this->emetteur,$this->marge_basse,$this->marge_gauche,$this->page_hauteur,$object,1,$hidefreetext);
		$pdf->SetAutoPageBreak(true);
		return $ret;
	}

	/**
	 * Ajoute le footer sur la page courante puis crée une nouvelle page et ajoute son header.
	 * @param Agsession $agf         Passé au footer mais pas utilisé
	 * @param Translate $outputlangs Les traductions
	 * @param int $tplidx            Template de page (optionnel) présent dans $pdf->tpls
	 * @param array|string $style  Style de page ou nom de style de page défini sur $this->styles
	 *                             permet d'ajouter en auto sur la page le header / footer / logo…
	 * @return void
	 */
	function newPage(&$agf, $outputlangs, $tplidx = null, $style = null) {
		/** @var Conf $conf */
		global $conf;
		$pdf = $this->pdf;

		// les logos (le mien et celui du client) doivent tenir dans 40×30mm
		$logoMaxWidth = $this->logoMaxWidth;
		$logoMaxHeight = $this->logoMaxHeight;

		// le tampon doit tenir dans 60×40mm
		$stampMaxWidth = $this->stampMaxWidth;
		$stampMaxHeight = $this->stampMaxHeight;
		$stampRightOffset = $this->stampRightOffset;
		$stampBottomOffset = $this->stampBottomOffset;
		$finalY = $this->marge_haute;
		if (!is_array($style)) {
			$style = (isset($this->styles[$style])) ? $this->styles[$style] : array();
		}
		// on complète le style avec le style par défaut
		$style += $this->styles['page-default'];

		if ($pdf->GetPage()) $this->_pagefoot($pdf, $agf, $outputlangs);
		$pdf->AddPage($style['orientation']);
		// on sauvegarde le style de chaque page
		$this->usedPageStyles[$pdf->GetPage()] = $style;
		$this->frameOffset[$pdf->GetPage()] = 0;
		if (! empty($tplidx) && !empty($style['use-tpl'])) {
			$pdf->useTemplate($tplidx);
		} elseif ($style['use-frame']) {
			// Cadre double autour de la page

			// encadré extérieur (épais)
			$pdf->SetLineWidth(1);
			$pdf->SetDrawColorArray($this->colorLine);
			$left = $this->marge_gauche;
			$top = $this->marge_haute;
			$right = $this->page_largeur - $this->marge_droite;
			$bottom = $this->page_hauteur - $this->marge_basse;
			$pdf->Rect($left, $top, $right - $left, $bottom - $top, 'D', array('all' => true));

			// encadré intérieur (moins épais)
			$pdf->SetLineWidth(0.3);
			$curFrameOffset = 1.2; // c'était en dur, je l'ai laissé
			$left += $curFrameOffset;
			$top += $curFrameOffset;
			$right -= $curFrameOffset;
			$bottom -= $curFrameOffset;
			$pdf->Rect($left, $top, $right - $left, $bottom - $top, 'D', array('all' => true));

			// sauvegarde de l'offset pour la page
			$this->frameOffset[$pdf->GetPage()] = $curFrameOffset + 1;
		}
		$this->setAutoPageBreak(false);
		$this->setMargins();
//
//		// Logo de l'organisme de formation en haut à gauche
//		if (!empty($style['use-company-logo'])) {
//			$logo = $conf->mycompany->dir_output . '/logos/' . $this->emetteur->logo;
//			// Logo en haut à gauche
//			if ($this->emetteur->logo) {
//				if (is_readable($logo)) {
//					$pdf->Image(
//						$logo,
//						0, // géré par le paramètre palign = 'L' (left)
//						$this->marge_haute + $this->getFrameOffset(),
//						$logoMaxWidth,
//						$logoMaxHeight,
//						'',
//						'',
//						'N',
//						false,
//						300,
//						'L',
//						false,
//						false,
//						0,
//						true,
//						false,
//						false,
//						false,
//						array()
//					);
//				}
//			}
//			$finalY = max($finalY, $pdf->GetY() + $logoMaxHeight);
//		}
//
//		// Logo client (optionnel) en haut à droite
//		if (!empty($style['use-client-logo']) && !empty($conf->global->AGF_USE_LOGO_CLIENT)) {
//			$staticsoc = new Societe($this->db);
//			$staticsoc->fetch($agf->socid);
//			$dir = $conf->societe->multidir_output [$staticsoc->entity] . '/' . $staticsoc->id . '/logos/';
//			if (! empty($staticsoc->logo)) {
//				$logo_client = $dir . $staticsoc->logo;
//				if (is_readable($logo_client)){
//					$pdf->Image(
//						$logo_client,
//						0, // géré par le paramètre palign = 'L' (left)
//						$this->marge_haute + $this->getFrameOffset(),
//						$logoMaxWidth,
//						$logoMaxHeight,
//						'',
//						'',
//						'N',
//						false,
//						300,
//						'R',
//						false,
//						false,
//						0,
//						true,
//						false,
//						false,
//						false,
//						array()
//					);
//				}
//			}
//			$finalY = max($finalY, $pdf->GetY() + $logoMaxHeight);
//		}

		// Tampon de l'organisme de formation en bas à droite
		if (!empty($style['use-company-stamp']) && !empty($conf->global->AGF_INFO_TAMPON)) {
			$dir = $conf->agefodd->dir_output . '/images/';
			$stampImg = $dir . $conf->global->AGF_INFO_TAMPON;
			if (is_readable($stampImg)) {
				$stampHeight = min($stampMaxHeight, pdf_getHeightForLogo($stampImg));
				$pdf->Image(
					$stampImg,
					$this->page_largeur - ($this->marge_gauche + $this->marge_droite + $this->getFrameOffset() * 2 + $stampMaxWidth + $stampRightOffset),
					$this->page_hauteur - ($this->footerHeight + $this->getFrameOffset() + $stampMaxHeight + $stampBottomOffset),
					$stampMaxWidth,
					$stampMaxHeight,
					'',
					'',
					'',
					false,
					300,
					'',
					false,
					false,
					0,
					true,
					false,
					false,
					false,
					array()
				);
			}
		}

		// en-tête de page
		if (!empty($style['use-header'])) {
			$this->_pagehead($pdf, $agf, 1, $outputlangs);
			$finalY = $pdf->GetY();
		}

		// pied de page
		if (!empty($style['use-footer'])) {
			$this->_pagefoot($pdf, $agf, $outputlangs);
		}

		// setAutoPageBreak est déjà appelé par le header et le footer mais si aucun des deux
		// n'est utilisé, on doit quand même remettre autopagebreak
		if (empty($style['use-header']) && empty($style['use-footer'])) {
			$this->setAutoPageBreak(true);
		}

		// repositionnement curseur en haut à gauche pour le contenu
		$pdf->SetXY($this->marge_gauche, $finalY);
	}

	/**
	 * Exemple:
	 *   $funcApplyTplSubst('{FirstName}: ' . $prenomStagiaire . '<br/>{Surname}: ' . $nomStagiaire);
	 * Retournera (si les clés de trad FirstName et Surname existent):
	 *   Prénom: John<br/>Nom: Doe
	 *
	 * @param string $tpl  Template HTML avec substitution des clés de traduction entre accolades
	 * @param Translate $langs
	 * @return string
	 */
	function applyTplSubst($tpl, $langs) {
		return preg_replace_callback(
			'/{([^}]+)}/',
			function ($m) use ($langs) {
				return (isset($langs->tab_translate[$m[1]])) ? $langs->transnoentities($m[1]) : $m[0];
			},
			$tpl
		);
	}

	/**
	 * Si le curseur Y dépasse de la page (ou même s'il mord sur la marge basse)
	 *   -> retourne false pour permettre un rollback mais ne fait pas le rollback lui-même (charge au contexte
	 *      appelant de le faire si nécessaire, ça permet de démarrer la transaction )
	 * Sinon, retourne true.
	 * @param string $html
	 * @param array|string  $style  Soit un style nommé (présent dans $this->styles), soit un style custom: array
	 *                              d'attributs de style gérés.
	 * @return bool  True si réussit à tout faire tenir (ou si $allowBreakInside est true), False sinon
	 */
	function printHTML($html, $style, $allowBreakInside=false) {
		$pdf = $this->pdf;
		if (!is_array($style)) {
			$style = (isset($this->styles[$style])) ? $this->styles[$style] : array();
		}
		// on complète le style avec le style par défaut
		$style += $this->styles['default'];
		// police du style
		$pdf->SetFont($style['font-family'], '', $style['font-size']);
		// couleur du style
		$pdf->SetTextColor(...$style['color']);

		$curentCellPaddinds = array_values($pdf->getCellPaddings());
		// set cell padding with column content definition
		$pdf->setCellPaddings(
			$style['padding-left'],
			$style['padding-top'],
			$style['padding-right'],
			$style['padding-bottom']
		);

		$cellWidth = $this->page_largeur;
		$cellWidth -= $this->marge_gauche + $style['margin-left'];
		$cellWidth -= $this->marge_droite + $style['margin-right'];

		$this->setAutoPageBreak($allowBreakInside);
		$pdf->writeHTMLCell(
			$cellWidth,  // largeur de la "cellule" printée
			0,       // hauteur minimum de la "cellule" printée
			$this->marge_gauche + $style['margin-left'], // coin haut-gauche X de la "cellule"
			$pdf->GetY() + $style['margin-top'],         // coin haut-gauche Y de la "cellule"
			$html,  // HTML
			0,      // border
			1,      // ln
			false,  // fill
			true,  // reseth
			$style['text-align'], // alignement: pris sur le style
			true    // autopadding
		);

		// restore cell padding
		$pdf->setCellPaddings(...$curentCellPaddinds);

		// on avance le curseur Y en fonction du margin-bottom défini par le style et on reset le curseur X
		$pdf->SetXY($this->marge_gauche, $pdf->GetY() + $style['margin-bottom']);

		$this->setAutoPageBreak(true);
		// si on mord sur la marge ou qu'on dépasse de la page, on retourne false (ce qui permettra un rollback)
		if ($pdf->GetY() >= $pdf->getPageHeight() - $this->getBreakMargin()) {
			return false;
		}
		return true;
	}

	/**
	 * @param bool $auto
	 * @return void
	 */
	function setAutoPageBreak($auto = true) {
		$this->pdf->SetAutoPageBreak($auto, $this->getBreakMargin());
	}

	function setMargins($pageNum = null) {
		if ($pageNum === null) $pageNum = $this->pdf->GetPage();
		$frameOffset = $this->getFrameOffset($pageNum);
		$this->pdf->SetMargins(
			$this->marge_gauche + $frameOffset,
			$this->marge_haute + $frameOffset,
			$this->marge_droite + $frameOffset
		);
	}

	/**
	 * @param int $pageNum
	 * @return int
	 */
	function getFrameOffset($pageNum = null) {
		if ($pageNum === null) $pageNum = $this->pdf->GetPage();
		return $this->frameOffset[$pageNum];
	}

	/**
	 * Retourne la somme des hauteurs à réserver en bas de page:
	 *  = marge basse
	 *   + éventuel espace pour l'encadré
	 *   + éventuel espace pour le tampon / signature
	 * @param int $pageNum  Par défaut, retourne pour la page courante, mais on peut demander pour
	 *                      une autre page existante.
	 * @return int
	 */
	function getBreakMargin($pageNum = null) {
		if ($pageNum === null) $pageNum = $this->pdf->GetPage();
		$style = $this->usedPageStyles[$pageNum];
		$breakMargin = $this->marge_basse;
		if (!empty($style['use-frame'])) $breakMargin += $this->getFrameOffset($pageNum);
		if (!empty($style['use-company-stamp'])) $breakMargin += $this->stampBottomOffset + $this->stampMaxHeight - $this->stampOverlapTolerance;
		return $breakMargin;
	}
}
