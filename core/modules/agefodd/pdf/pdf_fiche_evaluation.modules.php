<?php
/* Copyright (C) 2009-2010	Erick Bullier		<eb.dev@ebiconsulting.fr>
 * Copyright (C) 2012       Florian Henry   <florian.henry@open-concept.pro>
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
* along with this program; if not, write to the Free Software
* Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
*/


/**
 \file		agefodd/core/modules/agefodd/pdf/pdf_fiche_evaluation.modules.php
 \brief		Page permettant la création de la fiche d'évaluation propre à une formation au format pdf
*/

dol_include_once('/agefodd/core/modules/agefodd/agefodd_modules.php');
dol_include_once('/agefodd/class/agsession.class.php');
dol_include_once('/agefodd/class/agefodd_formation_catalogue.class.php');
dol_include_once('/agefodd/class/agefodd_session_formateur.class.php');
dol_include_once('/agefodd/class/agefodd_contact.class.php');
dol_include_once('/core/lib/company.lib.php');
dol_include_once('/core/lib/pdf.lib.php');
dol_include_once('/agefodd/lib/agefodd.lib.php');

class pdf_fiche_evaluation extends ModelePDFAgefodd
{
	var $emetteur;	// Objet societe qui emet

	// Definition des couleurs utilisées de façon globales dans le document (charte)
	protected $colorfooter;
	protected $colortext;
	protected $colorhead;

	/**
	 *	\brief		Constructor
	 *	\param		db		Database handler
	 */
	function pdf_fiche_evaluation($db)
	{
		global $conf,$langs,$mysoc;


		$this->db = $db;
		$this->name = "fiche_evaluation";
		$this->description = $langs->trans('AgfModPDFFicheEval');

		// Dimension page pour format A4 en portrait
		$this->type = 'pdf';
		$formatarray=pdf_getFormat();
		$this->page_largeur = $formatarray['width'];
		$this->page_hauteur = $formatarray['height'];
		$this->format = array($this->page_largeur,$this->page_hauteur);
		$this->marge_gauche=15;
		$this->marge_droite=15;
		$this->marge_haute=10;
		$this->marge_basse=10;
		$this->unit='mm';
		$this->oriantation='P';
		$this->espaceH_dispo = $this->page_largeur - ($this->marge_gauche + $this->marge_droite);
		$this->milieu = $this->espaceH_dispo / 2;
		$this->espaceV_dispo = $this->page_hauteur - ($this->marge_haute + $this->marge_basse);

		$this->colorfooter = agf_hex2rgb($conf->global->AGF_FOOT_COLOR);
		$this->colortext = agf_hex2rgb($conf->global->AGF_TEXT_COLOR);
		$this->colorhead = agf_hex2rgb($conf->global->AGF_HEAD_COLOR);

		// Get source company
		$this->emetteur=$mysoc;
		if (! $this->emetteur->country_code) $this->emetteur->country_code=substr($langs->defaultlang,-2);    // By default, if was not defined
	}


	/**
	 *	\brief      	Fonction generant le document sur le disque
	 *	\param	    	agf		Objet document a generer (ou id si ancienne methode)
	 *			outputlangs	Lang object for output language
	 *			file		Name of file to generate
	 *	\return	    	int     	1=ok, 0=ko
	 */
	function write_file($agf, $outputlangs, $file, $socid, $courrier)
	{
		global $user,$langs,$conf,$mysoc;

		if (! is_object($outputlangs)) $outputlangs=$langs;

		if (! is_object($agf))
		{
			$id = $agf;
			$agf = new Agsession($this->db);
			$ret = $agf->fetch($id);
		}

		// Definition of $dir and $file
		$dir = $conf->agefodd->dir_output;
		$file = $dir.'/'.$file;

		if (! file_exists($dir))
		{
			if (create_exdir($dir) < 0)
			{
				$this->error=$langs->trans("ErrorCanNotCreateDir",$dir);
				return 0;
			}
		}

		if (file_exists($dir))
		{
			$pdf=pdf_getInstance($this->format,$this->unit,$this->orientation);

			if (class_exists('TCPDF'))
			{
				$pdf->setPrintHeader(false);
				$pdf->setPrintFooter(false);
			}

			$pdf->Open();
			$pagenb=0;

			$pdf->SetTitle($outputlangs->convToOutputCharset($agf->ref));
			$pdf->SetSubject($outputlangs->transnoentities("Invoice"));
			$pdf->SetCreator("Dolibarr ".DOL_VERSION.' (Agefodd module)');
			$pdf->SetAuthor($outputlangs->convToOutputCharset($user->fullname));
			$pdf->SetKeyWords($outputlangs->convToOutputCharset($agf->ref)." ".$outputlangs->transnoentities("Document"));
			if ($conf->global->MAIN_DISABLE_PDF_COMPRESSION) $pdf->SetCompression(false);

			$pdf->SetMargins($this->marge_gauche, $this->marge_haute, $this->marge_droite);   // Left, Top, Right
			$pdf->SetAutoPageBreak(1,0);

			// On recupere les infos societe
			$agf_soc = new Societe($this->db);
			$result = $agf_soc->fetch($socid);

			if ($result)
			{
				// New page
				$pdf->AddPage();
				$pagenb++;
				$this->_pagehead($pdf, $agf, 1, $outputlangs);
				$pdf->SetFont(pdf_getPDFFont($outputlangs),'', 9);
				$pdf->MultiCell(0, 3, '', 0, 'J');		// Set interline to 3
				$pdf->SetTextColor($this->colortext[0], $this->colortext[1], $this->colortext[2]);

				$posY = $this->marge_haute;
				$posX = $this->marge_gauche;

				// Logo en haut à gauche
				$logo=$conf->mycompany->dir_output.'/logos/'.$this->emetteur->logo;
				if ($this->emetteur->logo)
				{
					if (is_readable($logo))
					{
						$heightLogo=pdf_getHeightForLogo($logo);
						include_once(DOL_DOCUMENT_ROOT.'/core/lib/images.lib.php');
						$tmp=dol_getImageSize($logo);
						if ($tmp['width'])
						{
							$widthLogo = $tmp['width'];
						}
						$pdf->Image($logo, $this->page_largeur - $this->marge_gauche - $this->marge_droite - 50, $this->marge_haute, 0, $heightLogo, '', '', '', true, 300, '', false, false, 0, false, false, true);	// width=0 (auto)
					}
					else
					{
						$pdf->SetTextColor(200,0,0);
						$pdf->SetFont(pdf_getPDFFont($outputlangs),'B', 8);
						$pdf->MultiCell(100, 3, $outputlangs->transnoentities("ErrorLogoFileNotFound",$logo), 0, 'R');
						$pdf->MultiCell(100, 3, $outputlangs->transnoentities("ErrorGoToGlobalSetup"), 0, 'R');
					}
				}
				else
				{
					$text=$this->emetteur->name;
					$pdf->SetTextColor($this->colorhead[0], $this->colorhead[1], $this->colorhead[2]);
					$pdf->SetFont(pdf_getPDFFont($outputlangs),'B', 11);
					$pdf->MultiCell(150, 3, $outputlangs->convToOutputCharset($text), 0, 'R');
				}

				//$posX += $this->page_largeur - $this->marge_droite - 65;

				$pdf->SetFont(pdf_getPDFFont($outputlangs),'',11);
				$pdf->SetTextColor($this->colorhead[0], $this->colorhead[1], $this->colorhead[2]);
				$pdf->SetXY($posX, $posY -1);
				$pdf->Cell(0, 5, $mysoc->name,0,0,'L');

				$pdf->SetFont(pdf_getPDFFont($outputlangs),'',7);
				$pdf->SetXY($posX, $posY +3);
				$this->str = $mysoc->address."\n";
				$this->str.= $mysoc->zip.' '.$mysoc->town;
				$this->str.= ' - '.$mysoc->country."\n";
				if ($mysoc->phone) {
					$this->str.= $outputlangs->transnoentities('AgfPDFHead1').' '.$mysoc->phone."\n";
				}
				if ($mysoc->fax) {
					$this->str.= $outputlangs->transnoentities('AgfPDFHead2').' '.$mysoc->fax."\n";
				}
				if ($mysoc->email) {
					$this->str.= $outputlangs->transnoentities('AgfPDFHead3').' '.$mysoc->email."\n";
				}
				if ($mysoc->url) {
					$this->str.= $outputlangs->transnoentities('AgfPDFHead4').' '.$mysoc->url."\n";
				}

				$pdf->MultiCell(100,3, $outputlangs->convToOutputCharset($this->str), 0, 'L');

				$posY = $pdf->GetY() + 10;

				$pdf->SetDrawColor($this->colorhead[0], $this->colorhead[1], $this->colorhead[2]);
				$pdf->Line ($this->marge_gauche + 0.5, $posY, $this->page_largeur - $this->marge_droite, $posY);

				// Mise en page de la baseline
				$pdf->SetFont(pdf_getPDFFont($outputlangs),'',18);
				$this->str = $outputlangs->transnoentities($mysoc->url);
				$this->width = $pdf->GetStringWidth($this->str);

				// alignement du bord droit du container avec le haut de la page
				$baseline_ecart = $this->page_hauteur - $this->marge_haute - $this->marge_basse - $this->width;
				$baseline_angle = (M_PI/2); //angle droit
				$baseline_x = 8;
				$baseline_y = $this->espaceV_dispo - $baseline_ecart + 30;
				$baseline_width = $this->width;
				$pdf->SetXY($baseline_x, $baseline_y);

				/*
				 * Corps de page
				*/

				$posX = $this->marge_gauche;
				$posY = $posY + 5;


				/***** Titre *****/
				$pdf->SetFont(pdf_getPDFFont($outputlangs),'',15);
				$pdf->SetTextColor($this->colorhead[0], $this->colorhead[1], $this->colorhead[2]);
				$pdf->SetXY($posX, $posY);
				$this->str = $outputlangs->transnoentities('AgfFicheEval');
				$pdf->Cell(0, 5, $outputlangs->convToOutputCharset($this->str),0,0,'C');
				$posY+= 10;

				$pdf->SetFont(pdf_getPDFFont($outputlangs),'',12);
				$pdf->SetTextColor($this->colortext[0], $this->colortext[1], $this->colortext[2]);
				$this->str = $agf->formintitule;
				$hauteur = dol_nboflines_bis($this->str,50)*4;
				// cadre
				$pdf->SetFillColor(255);
				$pdf->Rect($posX, $posY-1, $this->espaceH_dispo, $hauteur+3);
				// texte
				$pdf->SetXY( $posX, $posY);
				$pdf->MultiCell(0,5, $outputlangs->convToOutputCharset($this->str), 0, 'C');
				$posY+= $hauteur + 3;


				/***** Date et formateur *****/

				$pdf->SetFont(pdf_getPDFFont($outputlangs),'I',9);

				$pdf->SetXY($posX, $posY);
				$this->str = $outputlangs->transnoentities('AgfPDFFicheEval1')." ";
				if ($agf->dated == $agf->datef) $this->str .= dol_print_date($agf->dated);
				else $this->str .= ' '.dol_print_date($agf->dated).' au '.dol_print_date($agf->datef);
				$pdf->Cell(0, 5, $outputlangs->convToOutputCharset($this->str),0,0,'C');
				$posY+= 4;

				$formateurs = new Agefodd_session_formateur($this->db);
				$nbform = $formateurs->fetch_formateur_per_session($agf->id);
				$form_str = "";
				for ($i=0; $i < $nbform; $i++)
				{
					// Infos formateurs
					$forma_str .= strtoupper($formateurs->lines[$i]->name).' '.ucfirst($formateurs->lines[$i]->firstname);
					if ($i < ($nbform - 1)) $forma_str .= ', ';
				}

				$pdf->SetXY($posX, $posY);
				($nbform > 1) ? $this->str = $outputlangs->transnoentities('AgfPDFFicheEval2')." " : $this->str = $outputlangs->transnoentities('AgfPDFFicheEval3')." ";
				$this->str .= $forma_str;
				$pdf->MultiCell(0, 5, $outputlangs->convToOutputCharset($this->str),0,'C',0);
				$posY = $pdf->GetY() + 5;

				/***** Trainee Information *************/

				$pdf->SetXY($posX, $posY);
				$this->str=$outputlangs->transnoentities('AgfPDFFicheEvalNameTrainee').' : .....................';
				$pdf->MultiCell(0, 5, $outputlangs->convToOutputCharset($this->str),0,'L',0);
				$this->str=$outputlangs->transnoentities('AgfPDFFicheEvalCompany').' : .....................';
				$pdf->MultiCell(0, 5, $outputlangs->convToOutputCharset($this->str),0,'L',0);
				$this->str=$outputlangs->transnoentities('AgfPDFFicheEvalEmailTrainee').' : .....................';
				$pdf->MultiCell(0, 5, $outputlangs->convToOutputCharset($this->str),0,'L',0);
				$posY = $pdf->GetY() + 5;

				/***** Objectifs pedagogique de la formation *****/

				// Récuperation
				$agf_op = new Agefodd($this->db,"",$id);
				$result2 = $agf_op->fetch_objpeda_per_formation($agf->formid);

				$width = 160;
				$pdf->SetFont(pdf_getPDFFont($outputlangs),'',10);
				$pdf->SetXY($posX, $posY);
				$this->str = $outputlangs->transnoentities('AgfPDFFicheEval4');
				$pdf->MultiCell($width, 5, $outputlangs->convToOutputCharset($this->str),0,'L',0);
				$posY = $pdf->GetY() + 1;


				$pdf->SetFont(pdf_getPDFFont($outputlangs),'',10);

				for ( $y = 0; $y < count($agf_op->line); $y++)
				{
					// Intitulé
					$posY = $pdf->GetY();
					$pdf->SetXY($posX, $posY);
					$pdf->MultiCell($width, 0, $outputlangs->transnoentities($agf_op->line[$y]->intitule), 1,'L',0);
					$posY_after = $pdf->GetY();
					$hauteur=($posY_after-$posY);

					// Oui
					$pdf->SetXY ($posX + $width, $posY);
					$this->str = $outputlangs->transnoentities('AgfPDFFicheEvalYes');
					$pdf->MultiCell(10, $hauteur, $outputlangs->convToOutputCharset($this->str),0,'C',0);
					$pdf->Rect($posX + $width, $posY, 10, $hauteur);

					// Non
					$pdf->SetXY ($posX + $width + 10, $posY);
					$this->str = $outputlangs->transnoentities('AgfPDFFicheEvalNo');
					$pdf->MultiCell(10, $hauteur, $outputlangs->convToOutputCharset($this->str),0,'C',0);
					$pdf->Rect($posX + $width + 10, $posY, 10, $hauteur);

				}
				$posY = $pdf->GetY() + 5;


				/***** présentation echelle de notation *****/

				$pdf->SetFont(pdf_getPDFFont($outputlangs),'B',10);
				$pdf->SetXY($posX, $posY);
				$this->str = $outputlangs->transnoentities('AgfPDFFicheEval5');
				$pdf->MultiCell(0, 4, $outputlangs->transnoentities($this->str), 0,'C',0);
				$posY = $pdf->GetY() + 1;


				$col_larg = $this->espaceH_dispo / 5;

				// ligne 1
				$pdf->SetFont(pdf_getPDFFont($outputlangs),'B',10);
				$pdf->SetXY($posX, $posY);
				$pdf->Cell($col_larg, 5, $outputlangs->convToOutputCharset("1"),1,0,'C');
				$pdf->SetXY($posX + (1 * $col_larg), $posY);
				$pdf->Cell($col_larg, 5, $outputlangs->convToOutputCharset("2"),1,0,'C');
				$pdf->SetXY($posX + (2 * $col_larg), $posY);
				$pdf->Cell($col_larg, 5, $outputlangs->convToOutputCharset("3"),1,0,'C');
				$pdf->SetXY($posX + (3 * $col_larg), $posY);
				$pdf->Cell($col_larg, 5, $outputlangs->convToOutputCharset("4"),1,0,'C');
				$pdf->SetXY($posX + (4 * $col_larg), $posY);
				$pdf->Cell($col_larg, 5, $outputlangs->convToOutputCharset("5"),1,0,'C');
				$posY+= 5;

				// ligne 2
				$pdf->SetFont(pdf_getPDFFont($outputlangs),'',9);
				$pdf->SetXY($posX, $posY);
				$pdf->Cell($col_larg, 5, $outputlangs->transnoentities('AgfPDFFicheEval6'),1,0,'C');
				$pdf->Cell($col_larg, 5, $outputlangs->transnoentities('AgfPDFFicheEval7'),1,0,'C');
				$pdf->Cell($col_larg, 5, $outputlangs->transnoentities('AgfPDFFicheEval8'),1,0,'C');
				$pdf->Cell($col_larg, 5, $outputlangs->transnoentities('AgfPDFFicheEval9'),1,0,'C');
				$pdf->Cell($col_larg, 5, $outputlangs->transnoentities('AgfPDFFicheEval10'),1,0,'C');
				$posY+= 5 + 10;


				/***** lignes d'évaluations *****/

				$pdf->SetFont(pdf_getPDFFont($outputlangs),'',10);
				$hauteur_ligne = 6;

				$pdf->SetXY($posX, $posY);
				$pdf->Cell(170, $hauteur_ligne, $outputlangs->transnoentities('AgfPDFFicheEval11'),1,0,'L');
				$pdf->Cell(10, $hauteur_ligne, $outputlangs->convToOutputCharset(""),1,0,'C');
				$posY+= $hauteur_ligne;

				$pdf->SetXY($posX, $posY);
				$pdf->Cell(170, $hauteur_ligne, $outputlangs->transnoentities('AgfPDFFicheEval12'),1,0,'L');
				$pdf->Cell(10, $hauteur_ligne, $outputlangs->convToOutputCharset(""),1,0,'C');
				$posY+= $hauteur_ligne;

				$pdf->SetXY($posX, $posY);
				$pdf->Cell(170, $hauteur_ligne, $outputlangs->transnoentities('AgfPDFFicheEval13'),1,0,'L');
				$pdf->Cell(10, $hauteur_ligne, $outputlangs->convToOutputCharset(""),1,0,'C');
				$posY+= $hauteur_ligne;

				$pdf->SetXY($posX, $posY);
				$pdf->Cell(170, $hauteur_ligne, $outputlangs->transnoentities('AgfPDFFicheEval14'),1,0,'L');
				$pdf->Cell(10, $hauteur_ligne, $outputlangs->convToOutputCharset(""),1,0,'C');
				$posY+= $hauteur_ligne;

				$pdf->SetXY($posX, $posY);
				$pdf->Cell(170, $hauteur_ligne, $outputlangs->transnoentities('AgfPDFFicheEval15'),1,0,'L');
				$pdf->Cell(10, $hauteur_ligne, $outputlangs->convToOutputCharset(""),1,0,'C');
				$posY+= $hauteur_ligne;

				$pdf->SetXY($posX, $posY);
				$pdf->Cell(170, $hauteur_ligne, $outputlangs->transnoentities('AgfPDFFicheEval16'),1,0,'L');
				$pdf->Cell(10, $hauteur_ligne, $outputlangs->convToOutputCharset(""),1,0,'C');
				$posY+= $hauteur_ligne;

				$pdf->SetXY($posX, $posY);
				$pdf->Cell(170, $hauteur_ligne, $outputlangs->transnoentities('AgfPDFFicheEval17'),1,0,'L');
				$pdf->Cell(10, $hauteur_ligne, $outputlangs->convToOutputCharset(""),1,0,'C');
				$posY+= $hauteur_ligne;

				$pdf->SetXY($posX, $posY);
				$pdf->Cell(170, $hauteur_ligne, $outputlangs->transnoentities('AgfPDFFicheEval18'),1,0,'L');
				$pdf->Cell(10, $hauteur_ligne, $outputlangs->convToOutputCharset(""),1,0,'C');
				$posY+= $hauteur_ligne;

				$pdf->SetXY($posX, $posY);
				$pdf->Cell(170, $hauteur_ligne, $outputlangs->transnoentities('AgfPDFFicheEval19'),1,0,'L');
				$pdf->Cell(10, $hauteur_ligne, $outputlangs->convToOutputCharset(""),1,0,'C');
				$posY+= $hauteur_ligne;

				$pdf->SetXY($posX, $posY);
				$pdf->Cell(170, $hauteur_ligne, $outputlangs->transnoentities('AgfPDFFicheEval20'),1,0,'L');
				$pdf->Cell(10, $hauteur_ligne, $outputlangs->convToOutputCharset(""),1,0,'C');
				$posY+= $hauteur_ligne;

				$pdf->SetXY($posX, $posY);
				$pdf->Cell(170, $hauteur_ligne, $outputlangs->transnoentities('AgfPDFFicheEval21'),1,0,'L');
				$pdf->Cell(10, $hauteur_ligne, $outputlangs->convToOutputCharset(""),1,0,'C');
				$posY+= 5 + $hauteur_ligne;

				$pdf->SetFont(pdf_getPDFFont($outputlangs),'I',11);
				$pdf->SetXY($posX, $posY);
				$pdf->Cell(0, $hauteur_ligne, $outputlangs->transnoentities('AgfPDFFicheEval22'),0,0,'C');
				$posY+= $hauteur_ligne;


				/***** bloc commentaire *****/

				$pdf->SetFont(pdf_getPDFFont($outputlangs),'B',10);
				$pdf->SetXY($posX, $posY);
				$pdf->Cell(0, 5, $outputlangs->transnoentities('AgfPDFFicheEval23'),0,0,'L');

				$hauteur = $this->page_hauteur - 20 - $posY - 5;

				$pdf->Rect($posX, $posY, $this->espaceH_dispo, $hauteur);

				// Pied de page
				$this->_pagefoot($pdf,$agf,$outputlangs);
				$pdf->AliasNbPages();

			}
			$pdf->Close();
			$pdf->Output($file,'F');
			if (! empty($conf->global->MAIN_UMASK))
				@chmod($file, octdec($conf->global->MAIN_UMASK));

			return 1;   // Pas d'erreur
		}
		else
		{
			$this->error=$langs->trans("ErrorConstantNotDefined","AGF_OUTPUTDIR");
			return 0;
		}
		$this->error=$langs->trans("ErrorUnknown");
		return 0;   // Erreur par defaut
	}


	/**
	 *   	\brief      Show header of page
	 *      \param      pdf             	Object PDF
	 *      \param      object          	Object invoice
	 *      \param      showaddress     	0=no, 1=yes
	 *      \param      outputlangs		Object lang for output
	 */
	function _pagehead(&$pdf, $object, $showaddress=1, $outputlangs)
	{
		global $conf,$langs;

		$outputlangs->load("main");

		pdf_pagehead($pdf,$outputlangs,$pdf->page_hauteur);
	}


	/**
	 *   	\brief		Show footer of page
	 *   	\param		pdf     		PDF factory
	 * 	\param		object			Object invoice
	 *      \param		outputlang		Object lang for output
	 * 	\remarks	Need this->emetteur object
	 */
	function _pagefoot(&$pdf,$object,$outputlangs)
	{
		global $conf,$langs,$mysoc;

		$pdf->SetDrawColor($this->colorfooter[0], $this->colorfooter[1], $this->colorfooter[2]);
		$pdf->Line ($this->marge_gauche, $this->page_hauteur - 20, $this->page_largeur - $this->marge_droite, $this->page_hauteur - 20);

		$this->str = $mysoc->name;

		$pdf->SetFont(pdf_getPDFFont($outputlangs),'',9);
		$pdf->SetTextColor($this->colorfooter[0], $this->colorfooter[1], $this->colorfooter[2]);
		$pdf->SetXY( $this->marge_gauche, $this->page_hauteur - 20);
		$pdf->Cell(0, 5, $outputlangs->convToOutputCharset($this->str),0,0,'C');

		$this->str = $mysoc->address." ";
		$this->str.= $mysoc->zip.' '.$mysoc->town;
		$this->str.= ' - '.$mysoc->country;
		$this->str.= ' '.$outputlangs->transnoentities('AgfPDFFoot1').' '.$mysoc->phone;
		$this->str.= ' '.$outputlangs->transnoentities('AgfPDFFoot2').' '.$mysoc->email."\n";

		$statut = getFormeJuridiqueLabel($mysoc->forme_juridique_code);
		$this->str.= $statut;
		if (!empty($mysoc->capital)) {
			$this->str.=' '.$outputlangs->transnoentities('AgfPDFFoot3').' '.$mysoc->capital.' '.$langs->trans("Currency".$conf->currency);
		}
		if (!empty($mysoc->idprof2)) {
			$this->str.= ' '.$outputlangs->transnoentities('AgfPDFFoot4').' '.$mysoc->idprof2;
		}
		if (!empty($mysoc->idprof4)) {
			$this->str.= ' '.$outputlangs->transnoentities('AgfPDFFoot5').' '.$mysoc->idprof4;
		}
		if (!empty($mysoc->idprof3)) {
			$this->str.= ' '.$outputlangs->transnoentities('AgfPDFFoot6').' '.$mysoc->idprof3;
		}
		$this->str.="\n";
		if (!empty($conf->global->AGF_ORGANISME_NUM)) {
			$this->str.= ' '.$outputlangs->transnoentities('AgfPDFFoot7').' '.$conf->global->AGF_ORGANISME_NUM;
		}
		if (!empty($conf->global->AGF_ORGANISME_PREF)) {
			$this->str.= ' '.$outputlangs->transnoentities('AgfPDFFoot8').' '.$conf->global->AGF_ORGANISME_PREF;
		}
		if (!empty($mysoc->tva_intra)) {
			$this->str.=' '.$outputlangs->transnoentities('AgfPDFFoot9').' '.$mysoc->tva_intra;
		}

		$pdf->SetFont(pdf_getPDFFont($outputlangs),'I',7);
		$pdf->SetXY( $this->marge_gauche, $this->page_hauteur - 16);
		$pdf->MultiCell(0, 3, $outputlangs->convToOutputCharset($this->str),0,'C');

	}

	/**
	 *   	\brief		Formatage d'une liste à puce hierarchisée
	 *   	\param		pdf     		PDF factory
	 *      \param		outputlang		Object lang for output
	 */
	function liste_a_puce($text)
	{
		// - 1er niveau: remplacement de '# ' en debut de ligne par une puce de niv 1 (petit rond noir)
		// - 2éme niveau: remplacement de '## ' en début de ligne par une puce de niv 2 (tiret)
		// - 3éme niveau: remplacement de '### ' en début de ligne par une puce de niv 3 (>)
		// Pour annuler le formatage (début de ligne sur la mage gauche : '!#'
		$str = "";
		$line = explode("\n", $text);
		foreach ($line as $row)
		{
			if (preg_match('/^\!# /', $row)) $str.= preg_replace('/^\!# /', '', $row)."\n";
			elseif (preg_match('/^# /', $row)) $str.= chr(149).' '.preg_replace('/^#/', '', $row)."\n";
			elseif (preg_match('/^## /', $row)) $str.= '   '.'-'.preg_replace('/^##/', '', $row)."\n";
			elseif (preg_match('/^### /', $row)) $str.= '   '.'  '.chr(155).' '.preg_replace('/^###/', '', $row)."\n";
			else $str.= '   '.$row."\n";
		}
		return $str;
	}
}

# llxFooter('$Date: 2010-03-30 20:58:28 +0200 (mar. 30 mars 2010) $ - $Revision: 54 $');
?>
