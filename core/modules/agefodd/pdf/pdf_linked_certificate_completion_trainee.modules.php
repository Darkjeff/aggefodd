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
 * \file agefodd/core/modules/agefodd/pdf/pdf_certificate_completion_trainee.modules.php
 * \ingroup agefodd
 * \brief PDF for certificate
 */
dol_include_once('/agefodd/core/modules/agefodd/modules_agefodd.php');
dol_include_once('/agefodd/class/agsession.class.php');
dol_include_once('/agefodd/class/agefodd_formation_catalogue.class.php');
dol_include_once('/agefodd/class/agefodd_session_stagiaire.class.php');
dol_include_once('/agefodd/class/agefodd_session_stagiaire_heures.class.php');
dol_include_once('/agefodd/class/agefodd_place.class.php');
require_once (DOL_DOCUMENT_ROOT . '/core/lib/company.lib.php');
require_once (DOL_DOCUMENT_ROOT . '/core/lib/pdf.lib.php');
dol_include_once('/agefodd/lib/agefodd.lib.php');
require_once (DOL_DOCUMENT_ROOT . '/contact/class/contact.class.php');

class pdf_linked_certificate_completion_trainee extends ModelePDFAgefodd {
	var $emetteur; // Objet societe qui emet

	// Definition des couleurs utilisées de façon globales dans le document (charte)
	protected $colorfooter;
	protected $colortext;
	protected $colorhead;
	protected $colorheaderBg;
	protected $colorheaderText;
	protected $colorLine;
	public $TStagiairesSession = array();

	/** @var TCPDF $pdf */
	public $pdf;

	/** @var Translate $outputlangs */
	public $outputlangs;

	public $dpi = 150; // 150 points par pouce

    /**
     * @param DoliDB $db
     */
	function __construct($db) {
		global $conf, $langs, $mysoc;

		$this->db = $db;
        $this->name = 'linked_certificate_completion_trainee';
		$this->description = $langs->trans('AgfPDFAttestation');

		// Dimension page pour format A4 en paysage
		$this->type = 'pdf';
		$formatarray = pdf_getFormat();
		$this->page_largeur = $formatarray ['width'];
		$this->page_hauteur = $formatarray ['height'];
		$this->format = array (
			$this->page_largeur,
			$this->page_hauteur
		);
		$this->marge_gauche = 15;
		$this->marge_droite = 15;
		$this->marge_haute = 10;
		$this->marge_basse = 10;
		$this->unit = 'mm';
		$this->oriantation = 'l';
		$this->espaceH_dispo = $this->page_largeur - ($this->marge_gauche + $this->marge_droite);
		$this->milieu = $this->espaceH_dispo / 2;

		$this->colorfooter = agf_hex2rgb($conf->global->AGF_FOOT_COLOR);
		$this->colortext = agf_hex2rgb($conf->global->AGF_TEXT_COLOR);
		$this->colorhead = agf_hex2rgb($conf->global->AGF_HEAD_COLOR);
		$this->colorheaderBg = agf_hex2rgb($conf->global->AGF_HEADER_COLOR_BG);
		$this->colorheaderText = agf_hex2rgb($conf->global->AGF_HEADER_COLOR_TEXT);
		$this->colorLine = agf_hex2rgb($conf->global->AGF_COLOR_LINE);

		// Get source company
		$this->emetteur = $mysoc;
		if (! $this->emetteur->country_code)
			$this->emetteur->country_code = substr($langs->defaultlang, - 2); // By default, if was not defined
	}

    /**
     * @param Agsession $agf
     * @param Translate $outputlangs
     * @param string $file
     * @param int $socid
     * @return int
     */
	function write_file($agf, $outputlangs, $file, $socid) {
		global $user, $langs, $conf, $mysoc;

		if (! is_object($outputlangs)) $this->outputlangs = $outputlangs = $langs;
		else $this->outputlangs = $outputlangs;

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
			$pdf = $this->pdf = pdf_getInstance($this->format, $this->unit, $this->orientation);

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
			$this->_resetColorsAndStyle();

			// Set path to the background PDF File
			if (empty($conf->global->MAIN_DISABLE_FPDI) && ! empty($conf->global->AGF_ADD_PDF_BACKGROUND_P))
			{
				$pagecount = $pdf->setSourceFile($conf->agefodd->dir_output . '/background/' . $conf->global->AGF_ADD_PDF_BACKGROUND_P);
				$tplidx = $pdf->importPage(1);
			}

			// Récuperation des objectifs pédagogiques de la formation
			$agf_op = new Formation($this->db);
			$result2 = $agf_op->fetch_objpeda_per_formation($agf->fk_formation_catalogue);

			// Récupération de la durée de la formation
			$agf_duree = new Formation($this->db);
			$result = $agf_duree->fetch($agf->fk_formation_catalogue);

			// Récupération des stagiaires participant à la formation
			$stagiaires = new Agefodd_session_stagiaire($this->db);
			$result = $stagiaires->fetch_stagiaire_per_session($id);

			// Récupération des heures de session auquelles les participants ont participé
			$heures = new Agefoddsessionstagiaireheures($this->db);
			$result = $heures->fetch_all_by_session($agf->id, $socid);

			// Récupération des informations du lieu de la session
			$agf_place = new Agefodd_place($this->db);
			$result = $agf_place->fetch($agf->placeid);

			// Récupération des informations des formateur
			$agf_session_trainer = new Agefodd_session_formateur($this->db);
			$agf_session_trainer->fetch_formateur_per_session($id);

		if (is_array($stagiaires->lines) && !empty($stagiaires->lines) ) {
			foreach ($stagiaires->lines as $trainee) {
				// le stagiaire fait partie de la société courante et son statut dans la session est présent ou partiellement présent
				if ($trainee->socid == $socid && ($trainee->status_in_session ==  Agefodd_session_stagiaire::STATUS_IN_SESSION_TOTALLY_PRESENT || $trainee->status_in_session == Agefodd_session_stagiaire::STATUS_IN_SESSION_PARTIALLY_PRESENT)) {
					// New page
					$pdf->AddPage();
					if (!empty($tplidx)) $pdf->useTemplate($tplidx);

					$pagenb++;
					$this->_pagehead($pdf, $agf, 1, $outputlangs);
					$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 9);
					$pdf->MultiCell(0, 3, '', 0, 'J'); // Set interline to 3

					// On met en place le cadre
					$pdf->SetDrawColor($this->colorLine [0], $this->colorLine [1], $this->colorLine [2]);

					$newY = $this->marge_haute + 40;
					$pdf->SetXY($this->marge_gauche + 1, $newY);
					$pdf->SetFont(pdf_getPDFFont($outputlangs), 'B', 20);
					$this->setTextColor('head');
					$pdf->Cell(0, 0, $outputlangs->transnoentities('AgfCompletionCertificate'), 0, 0, 'C', 0);
					$this->setTextColor('text');

					$newY = $pdf->GetY() + 20;
					$pdf->SetXY($this->marge_gauche, $newY);
					$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 12);

					//Je soussigné
					$pdf->SetFont(pdf_getPDFFont($outputlangs), 'B', 12);
					$this->str1 = $outputlangs->transnoentities('AgfSign') . ' ' . $conf->global->AGF_ORGANISME_REPRESENTANT;
					$pdf->MultiCell($this->page_largeur - $this->marge_gauche - $this->marge_droite, 4, $this->str1, 0, 'L', 0);

					//représentant légal du dispensateur de l’action concourant au développement des compétences
					$pdf->SetXY($this->marge_gauche, $newY + 10);
					$this->str2 = $outputlangs->transnoentities('AgfRepresentAction') . ' ' . $this->emetteur->name;
					$pdf->MultiCell($this->page_largeur - $this->marge_gauche - $this->marge_droite, 4, $this->str2, 0, 'L', 0);

					//atteste que
					$pdf->SetXY($this->marge_gauche, $newY + 25);
					$this->str3 = $outputlangs->transnoentities('AgfAttesting');
					$pdf->MultiCell($this->page_largeur - $this->marge_gauche - $this->marge_droite, 4, $this->str3, 0, 'L', 0);


					//nom et prénom du bénéficiaire
					$pdf->SetXY($this->marge_gauche, $newY + 35);
					$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 12);

					$civilite = $trainee->civilitel;
					$nom = $trainee->nom;
					$prenom = $trainee->prenom;
					$socname = $trainee->socname;

					$this->str4 = $civilite . ' ' . $nom . ' ' . $prenom;
					$pdf->MultiCell($this->page_largeur - $this->marge_gauche - $this->marge_droite, 4, $this->str4, 0, 'L', 0);

					//Raison sociale
					$this->str5 = $outputlangs->transnoentities('AgfCustomer') . ' ' . $socname;
					$pdf->MultiCell($this->page_largeur - $this->marge_gauche - $this->marge_droite, 4, $this->str5, 0, 'L', 0);

					//Intitulé de la formation
					$this->str6 = $outputlangs->transnoentities('AgfFollowAction') . ' ' . $outputlangs->transnoentities('« ' . $agf->intitule_custo . ' ».');
					$pdf->MultiCell($this->page_largeur - $this->marge_gauche - $this->marge_droite, 4, $this->str6, 0, 'L', 0);


					/*
					 * CHECKBOXS
					 */

					$pdf->SetXY($this->marge_gauche, $newY + 55);
					$pdf->SetFont(pdf_getPDFFont($outputlangs), 'I', 11);
					$this->str6 = $outputlangs->transnoentities('NatureAction');
					$pdf->MultiCell($this->page_largeur - $this->marge_gauche - $this->marge_droite, 4, $this->str6, 0, 'L', 0);

					$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 12);
					$sql = 'SELECT code, label FROM ' . MAIN_DB_PREFIX . 'c_formation_nature_action WHERE active = 1';
					$resql = $this->db->query($sql);

					if ($resql) {
						while ($obj = $this->db->fetch_object($resql)) {
							if ($agf->fk_nature_action_code == $obj->code)
								$this->str6 = '[x] ';
							else
								$this->str6 = '[  ] ';

							if ($obj->code == 'AGF_NAT_ACT_AF')
								$this->str6 .= $outputlangs->transnoentities('AgfFormActionStar');
							else
								$this->str6 .= $obj->label;
							$pdf->MultiCell($this->page_largeur - $this->marge_gauche - $this->marge_droite, 4, $this->str6, 0, 'L', 0);
						}
					}
					//dates
					$pdf->SetXY($this->marge_gauche, $newY + 85);
					$this->str6 = $outputlangs->transnoentities('AgfDateDelta') . ' ' . $agf->libSessionDate() . ' ';

					//nombre d’heures réalisées: Si la conf est activée on prend les valeurs saisies manuellement, si non on prend la durée totale de la session
					if (!empty($conf->global->AGF_USE_REAL_HOURS)) {
						foreach ($stagiaires->lines as $trainee) {
							if ($trainee->stagerowid == $socid) {
								$totalHeures = $heures->heures_stagiaire($agf->id, $trainee->id);
							}
						}

						$this->str6 .= $outputlangs->transnoentities('AgfSessionDuration') . ' ' . $totalHeures . $outputlangs->transnoentities('AgfEffectHours');
						$pdf->MultiCell($this->page_largeur - $this->marge_gauche - $this->marge_droite, 4, $this->str6, 0, 'L', 0);
					} else {
						$this->str6 .= $outputlangs->transnoentities('AgfSessionDuration') . ' ' . $agf->duree_session . $outputlangs->transnoentities('AgfEffectHours');
						$pdf->MultiCell($this->page_largeur - $this->marge_gauche - $this->marge_droite, 4, $this->str6, 0, 'L', 0);
					}
					$this->str8 = $outputlangs->transnoentities('AffEffectHoursDesc');
					$pdf->SetFont(pdf_getPDFFont($outputlangs), 'I', 10);
					$pdf->MultiCell($this->page_largeur - $this->marge_gauche - $this->marge_droite, 4, $this->str8, 0, 'L', 0);


					//Paragraphe d'avant la signature
					$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 12);
					$pdf->SetXY($this->marge_gauche, $newY + 100);
					$this->str6 = $outputlangs->transnoentities('AgfCertificateCommitment');
					$pdf->MultiCell($this->page_largeur - $this->marge_gauche - $this->marge_droite, 4, $this->str6, 0, 'J', 0);
					$this->str6 = $outputlangs->transnoentities('AgfCertificateCommitment1');
					$pdf->MultiCell($this->page_largeur - $this->marge_gauche - $this->marge_droite, 4, $this->str6, 0, '', 0);


					//Lieu
					$newY = $pdf->GetY() + 15;
					$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 12);
					$this->str = $outputlangs->transnoentities('Fait à :') . ' ' . $mysoc->town;
					$pdf->SetXY($this->marge_gauche + 1, $newY);
					$pdf->Cell(0, 0, $outputlangs->convToOutputCharset($this->str), 0, 0, 'L', 0);
					$pdf->SetXY(50, $newY);

					$newY = $pdf->GetY() + 10;
					$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 12);
					$pdf->SetXY($this->marge_gauche + 1, $newY);
					$this->str = $outputlangs->transnoentities('Le :') . ' ' . dol_print_date($agf->datef);
					$pdf->MultiCell(80, 3, $outputlangs->convToOutputCharset($this->str), 0, 'L', 0);


					// Cadre de signature
					$newY = $pdf->GetY();
					$posX = $this->page_largeur / 2;
					$pdf->SetXY($posX, $newY);
					$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 12);

					//contenu de la cellule
					$this->str = '<div>' . $outputlangs->transnoentities('AgfSigning') . '</div>';
					$this->str .= '<strong>' . $conf->global->AGF_ORGANISME_REPRESENTANT . ', ' . '</strong>';
					$this->str .= '<strong>' . $outputlangs->transnoentities('AgfDir') . '</strong>';
					$pdf->MultiCell('', 50, $this->str, '1', 'C', '', '', '100', '210', '', '1', true);

					// Incrustation image tampon
					$pdf->SetXY($this->marge_gauche, $newY);
					$tampon_exitst = 1;
					if ($conf->global->AGF_INFO_TAMPON) {
						$dir = $conf->agefodd->dir_output . '/images/';
						$img_tampon = $dir . $conf->global->AGF_INFO_TAMPON;
						if (file_exists($img_tampon)) {
							$newY = $pdf->GetY();

							$img_tampon = $dir . $conf->global->AGF_INFO_TAMPON;
							require_once DOL_DOCUMENT_ROOT . '/core/lib/images.lib.php';
							$TRealSizeLogo = dol_getImageSize($img_tampon);
							$TSize = self::getGoodImageDimensionsForThirdpartyLogo($TRealSizeLogo['width'], $TRealSizeLogo['height']);

							if (is_readable($img_tampon)) {
								$pdf->Image($img_tampon, $this->page_largeur / 2 + 10, '', $TSize['width'], $TSize['height'], '', '', '', false, 300, ''); // width=0 (auto)
//						$pdf->Image($img_tampon, $this->page_largeur - $this->marge_gauche - $this->marge_droite - 55, $newY, 50);
//var_dump($this->page_largeur);exit;
							}
							$tampon_exitst = 22;
						}
					}


					//Bas de page
					$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 10);
					$pdf->Line($this->marge_gauche, $newY + 40, $this->page_largeur - ($this->marge_droite + 100), $newY + 40);
					$pdf->SetXY($this->marge_gauche, $newY + 45);
					$this->str = $outputlangs->transnoentities('AgfFooterOne');
					$pdf->MultiCell(0, 3, $outputlangs->convToOutputCharset($this->str), 0, '', 0);

					$this->str = $outputlangs->transnoentities('AgfFooterTwo');
					$pdf->MultiCell(0, 3, $outputlangs->convToOutputCharset($this->str), 0, '', 0);


					// Mise en place du copyright
					$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 8);
					$this->str = $outputlangs->transnoentities('copyright ' . date('Y') . ' - ' . $mysoc->name);
					$this->width = $pdf->GetStringWidth($this->str);
					// alignement du bord droit du container avec le haut de la page
					$baseline_ecart = $this->page_hauteur - $this->marge_haute - $this->marge_basse - $this->width;
					$baseline_angle = (M_PI / 2); // angle droit
					$baseline_x = $this->page_largeur - $this->marge_gauche - 12;
					$baseline_y = $baseline_ecart + 30;
					$baseline_width = $this->width;
				}
			}
		}

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
     * @param pdf_linked_certificate_completion_trainee $pdf
     * @param Agsession $object
     * @param int $showaddress
     * @param Translate $outputlangs
     * @return void
     */
	public function _pagehead(&$pdf, $object, $showaddress = 1, $outputlangs) {
		global $conf, $langs;

		$outputlangs->load("main");

		$default_font_size = pdf_getPDFFontSize($outputlangs);

		pdf_pagehead($pdf, $outputlangs, $pdf->page_hauteur);

		$posy=$this->marge_haute;
		$posx=$this->marge_gauche;

		$logo = $conf->mycompany->dir_output.'/logos/'.$this->emetteur->logo;

		$this->showLogo($posx, $posy, $logo);

		if ($showaddress)
		{
			// Sender properties
			// Show sender
			$posy=$this->marge_haute;
			$posx=$this->marge_gauche;

			$hautcadre=30;
			$pdf->SetXY($posx,$posy);
			$pdf->SetFillColor(255,255,255);
			$pdf->MultiCell(70, $hautcadre, "", 0, 'R', 1);

			// Show sender name
			$pdf->SetXY($posx,$posy);
			$pdf->SetFont('','B', $default_font_size);
			$pdf->MultiCell(0, 4, $outputlangs->convToOutputCharset($this->emetteur->name), 0, 'R');
			$posy=$pdf->GetY();

			 //Show sender information
			$pdf->SetXY($posx,$posy);
			$pdf->SetFont('','', $default_font_size - 1);
			$pdf->MultiCell(0, 4, $outputlangs->convToOutputCharset($this->emetteur->address), 0, 'R');
			$posy=$pdf->GetY();
			$pdf->SetXY($posx,$posy);
			$pdf->SetFont('','', $default_font_size - 1);
			$pdf->MultiCell(0, 4, $outputlangs->convToOutputCharset($this->emetteur->zip.' '.$this->emetteur->town), 0, 'R');
			$posy=$pdf->GetY();
			$pdf->SetXY($posx,$posy);
			$pdf->SetFont('','', $default_font_size - 1);
			$pdf->MultiCell(0, 4, $outputlangs->convToOutputCharset($this->emetteur->phone), 0, 'R');
			$posy=$pdf->GetY();
			$pdf->SetXY($posx,$posy);
			$pdf->SetFont('','', $default_font_size - 1);
			$pdf->MultiCell(0, 4, $outputlangs->convToOutputCharset($this->emetteur->email), 0, 'R');
			$posy=$pdf->GetY();

			printRefIntForma($this->db, $outputlangs, $object, $default_font_size - 1, $pdf, $posx, $posy, 'R');
		}
	}

    /**
     * @param string $style
     * @return void
     */
	public function setTextColor($style) {
		$prop = 'color' . $style;
		$color = isset($this->{$prop}) ? $this->{$prop} : $this->colortext;
		$this->pdf->SetTextColor($color[0], $color[1], $color[2]);
	}

    /**
     * Reset text color, draw color, line style and font.
     *
     * @return void
     */
	public function _resetColorsAndStyle()
	{
		$this->pdf->SetFont(pdf_getPDFFont($this->outputlangs));
		$this->setTextColor('text');
		$this->pdf->SetDrawColor($this->colorLine[0], $this->colorLine[1], $this->colorLine[2]);
		$this->pdf->SetLineStyle(array(
			'width' => 0.05,
		));
	}

    /**
     * @param float $posx
     * @param float $posy
     * @param string $logo
     * @param float $maxwidth
     * @param float $maxheight
     * @return void
     */
	public function showLogo($posx, $posy, $logo, $maxwidth = 90, $maxheight = 40)
	{
		if (is_readable($logo))
		{
			dol_include_once('/core/lib/images.lib.php');
			$TSizes = dol_getImageSize($logo);
			$realwidth = $TSizes['width'];
			$realheight = $TSizes['height'];

			$maxwidth = min($maxwidth, $realwidth / ($this->dpi / 25.4));
			$maxheight = min($maxheight, $realheight / ($this->dpi / 25.4));

			if ($realwidth > $realheight) {
				$this->pdf->Image($logo, $posx, $posy, $maxwidth, 0, '', '', '', true);
			} else {
				$this->pdf->Image($logo, $posx, $posy, 0, $maxheight, '', '', '', true);
			}
		}
	}

	/**
	 * Recursive function used to get right height and width for thirdparty logo which allow it not to exceed pdf size (remove 10% until getting the right size)
	 * @param	int	$w	logo weight
	 * @param	int	$h	logo height
	 * @return	array	array result
	 */
	public static function getGoodImageDimensionsForThirdpartyLogo($w, $h) {

		if($w <= 80 && $h <= 30) return array('width'=>round($w), 'height'=>round($h));
		else {
			return self::getGoodImageDimensionsForThirdpartyLogo($w * 0.9, $h * 0.9);
		}

	}
}
