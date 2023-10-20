<?php
/*
 * Copyright (C) 2009-2010	Erick Bullier		<eb.dev@ebiconsulting.fr>
 * Copyright (C) 2012-2016 Florian Henry <florian.henry@open-concept.pro>
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
dol_include_once('/agefodd/class/agefodd_stagiaire.class.php');
require_once (DOL_DOCUMENT_ROOT . '/core/lib/company.lib.php');
require_once (DOL_DOCUMENT_ROOT . '/core/lib/pdf.lib.php');
dol_include_once('/agefodd/lib/agefodd.lib.php');
require_once (DOL_DOCUMENT_ROOT . '/contact/class/contact.class.php');
class pdf_attestation_trainee extends ModelePDFAgefodd {
	var $emetteur; // Objet societe qui emet

	// Definition des couleurs utilisées de façon globales dans le document (charte)
	protected $colorfooter;
	protected $colortext;
	protected $colorhead;
	protected $colorheaderBg;
	protected $colorheaderText;
	protected $colorLine;

	public $orientation = 'L';

	public $defaultFontSize = 12;
	/**
	 * @var array $styles  Tableau associatif contenant des tableaux (indexés par nom), ces tableaux sont associatifs
	 *                     aussi et définissent des styles de paragraphe (marges…) ou de page (logos, footer, etc.)
	 */
	public $styles = array();

	/** @var int[] $frameOffset  Pour chaque page: espace laissé libre pour l'encadré de page */
	public $frameOffset = array();

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
	/**
	 * @var array $usedPageStyles  Tableau qui associe à un no. de page son style pour
	 *                             connaître les caractéristiques de chaque page.
	 */
	public $usedPageStyles = array();
	/**
	 * @var TCPDF $pdf
	 */
	public $pdf;

	/**
	 * \brief		Constructor
	 * \param		db		Database handler
	 */
	function __construct($db) {
		global $conf, $langs, $mysoc;

		$this->db = $db;
		$this->name = $langs->trans('AgfPDFAttestationTrainee');
		$this->description = $langs->trans('AgfPDFAttestationTrainee');

		// Dimension page pour format A4 en paysage
		$this->type = 'pdf';
		$formatarray = pdf_getFormat();
		$this->page_largeur = $formatarray ['height'];
		$this->page_hauteur = $formatarray ['width'];
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

		/*
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
				'font-size'      => $this->defaultFontSize,
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
				'use-company-logo'  => false,
				'use-company-stamp' => false,
				'use-client-logo'   => false,
				'use-frame'         => false,
				'use-tpl'           => true,
				'orientation'       => $this->orientation,
			),
		);

		// Get source company
		$this->emetteur = $mysoc;
		if (! $this->emetteur->country_code)
			$this->emetteur->country_code = substr($langs->defaultlang, - 2); // By default, if was not defined
	}

	/**
	 * @param Agsession|int $agf   Si $agf est un entier, il représente l'ID d'une session (qui sera chargée sur $agf)
	 * @param Translate $outputlangs  Objet langs pour la génération (permet de générer le doc dans une langue
	 *                                différente de la langue de l'interface Dolibarr)
	 * @param string $file         Nom du fichier (avec extension mais sans chemin d'accès)
	 * @param int $session_trainee_id  ID du stagiaire
	 * @return int  1=OK, 0=KO
	 * @throws Exception
	 */
	function write_file($agf, $outputlangs, $file, $session_trainee_id) {
		global $user, $langs, $conf, $mysoc;

		if (! is_object($outputlangs))
			$outputlangs = $langs;

		if (! is_object($agf)) {
			$id = $agf;
			$agf = new Agsession($this->db);
			$ret = $agf->fetch($id);
		}

		/**
		 * Printe une série insécable de paragraphes HTML. Si la série ne tient pas entièrement sur la page courante,
		 * on rollback et on retourne false. Sinon on commit.
		 *
		 * Nombre variable d'arguments. Un argument peut être:
		 * - un array décrivant un style (s'appliquera aux paragraphes suivants)
		 * - un nom de style présent dans $this->styles (s'appliquera aux paragraphes suivants)
		 * - une instruction 'RESET:Y' pour revenir sur la même ligne que le paragraphe précédent
		 * - une chaîne HTML à afficher en tant que paragraphe
		 *
		 * @param string|array $args  Séquence: soit un style, soit un 'RESET:Y', soit un paragraphe HTML
		 * @return bool  True si le bloc entier tient sur la page
		 */
		$funcPrintNonBreakBlock = function (...$args) use (&$css, $outputlangs) {
			$this->pdf->startTransaction();
			$style = 'default';
			$lastY = $this->pdf->GetY();
			foreach ($args as $block) {
				if (is_array($block) || strlen($block) < 40 && isset($this->styles[$block])) {
					$style = $block;
				} elseif ($block === 'RESET:Y') {
					$this->pdf->SetY($lastY);
				} else {
					$lastY = $this->pdf->GetY();
					$html = $css /*. "$lastY  "*/ . $this->applyTplSubst($block, $outputlangs);
					if (! $this->printHTML($html, $style, false)) {
						$this->pdf->rollbackTransaction(true);
						return false;
					}
				}
			}
			$this->pdf->commitTransaction();
			return true;
		};

		/**
		 * Chaque bloc est insécable, mais on autorise des sauts de page entre deux blocs
		 *
		 * @param ...$args
		 * @return bool
		 */
		$funcPrintBlocks = function (...$args) use (&$css, &$funcNewPage, $outputlangs) {
			$style = 'default';
			$pageStyle = 'page-default';
			$allowBreakInside = false;
			$lastY = $this->pdf->GetY();
			foreach ($args as $block) {
				if (is_array($block) || strlen($block) < 40 && isset($this->styles[$block])) {
					// les styles de pages commencent par "page-"
					if (strpos($block, 'page-') === 0) $pageStyle = $block;
					// les autres styles n'ont pas de préfixe
					else $style = $block;
				} elseif ($block === 'RESET:Y') {
					$this->pdf->SetY($lastY);
				} elseif ($block === 'BREAK:ALLOW') {
					$allowBreakInside = true;
				} elseif ($block === 'BREAK:DISALLOW') {
					$allowBreakInside = false;
				} elseif ($block === 'BREAK:FORCE') {
					$funcNewPage($pageStyle);
				} elseif (empty($block)) {
					continue;
				} else {
					$this->pdf->startTransaction();
					$lastY = $this->pdf->GetY();
					$html = $css /*. "$lastY  "*/ . $this->applyTplSubst($block, $outputlangs);
					if (! $this->printHTML($html, $style, $allowBreakInside)) {
						$this->pdf->rollbackTransaction(true);
						return false;
					}
				}
			}
			$this->pdf->commitTransaction();
			return true;
		};

		/**
		 * Partial de la méthode newPage pour éviter de répéter les arguments invariables
		 *
		 * Ajoute le footer sur la page courante puis crée une nouvelle page et ajoute son header.
		 * @param array|string $style  Style de page ou nom de style de page défini sur $this->styles
		 *                             permet d'ajouter en auto sur la page le header / footer / logo…
		 * @return void
		 */
		$funcNewPage = function ($style = null) use ($agf, $outputlangs, &$tplidx) {
			$this->newPage($agf, $outputlangs, $tplidx, $style);
		};

		$agf_session_trainee = new Agefodd_session_stagiaire($this->db);
		$agf_session_trainee->fetch($session_trainee_id);

		$agf_trainee = new Agefodd_stagiaire($this->db);
		$agf_trainee->fetch($agf_session_trainee->fk_stagiaire);

		// Definition of $dir and $file
		$dir = $conf->agefodd->dir_output;
		$file = $dir . '/' . $file;

		if (! file_exists($dir)) {
			if (dol_mkdir($dir) < 0) {
				$this->error = $langs->trans("ErrorCanNotCreateDir", $dir);
				return 0;
			}
		}

		if (! file_exists($dir)) {
			$this->error = $langs->trans("ErrorConstantNotDefined", "AGF_OUTPUTDIR");
			return 0;
		}

		$this->pdf = $pdf = pdf_getInstance($this->format, $this->unit, $this->orientation);

		$pdf->Open();

		if (class_exists('TCPDF')) {
			$pdf->setPrintHeader(false);
			$pdf->setPrintFooter(false);
		}

		$pdf->SetTitle($outputlangs->convToOutputCharset($agf->ref));
		$pdf->SetSubject($outputlangs->transnoentities("AgfPDFAttestationTrainee"));
		$pdf->SetCreator("Dolibarr " . DOL_VERSION . ' (Agefodd module)');
		$pdf->SetAuthor($outputlangs->convToOutputCharset($user->fullname));
		$pdf->SetKeyWords($outputlangs->convToOutputCharset($agf->ref) . " " . $outputlangs->transnoentities("Document"));
		if ($conf->global->MAIN_DISABLE_PDF_COMPRESSION)
			$pdf->SetCompression(false);


		// Set path to the background PDF File
		if (empty($conf->global->MAIN_DISABLE_FPDI) && ! empty($conf->global->AGF_ADD_PDF_BACKGROUND_L))
		{
			$pagecount = $pdf->setSourceFile($conf->agefodd->dir_output . '/background/' . $conf->global->AGF_ADD_PDF_BACKGROUND_L);
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


		// New page
		$funcNewPage(array(
			'use-header'        => true,
			'use-footer'        => true,
			'use-company-logo'  => true,
			'use-company-stamp' => true,
			'use-client-logo'   => true,
			'use-frame'         => true,
			'use-tpl'           => true,
		));

		$defaultFont = pdf_getPDFFont($outputlangs);

		// Récupération des variables: nom prénom
		$contact_static = new Contact($this->db);
		$contact_static->civility_id = $agf_trainee->civilite;
		$traineeCivility = ucfirst(strtolower($contact_static->getCivilityLabel()));
		$traineeName = $agf_trainee->prenom . ' ' . $agf_trainee->nom;

		// Récupération des variables: duree_session
		if (! empty($conf->global->AGF_USE_REAL_HOURS)) {
			dol_include_once('/agefodd/class/agefodd_session_stagiaire_heures.class.php');
			$agfssh = new Agefoddsessionstagiaireheures($this->db);
			$duree_session=$agfssh->heures_stagiaire($agf->id, $agf_session_trainee->fk_stagiaire);
		} else {
			$duree_session=$agf->duree_session;
		}

		$session_dates = $agf->libSessionDate();


		// Récupération des variables: Liste des objectifs de la formation (HTML)
		$goalsList = '';
		if (count($agf_op->lines)) {
			$goalsList .= '<ol>' . "\n";
			foreach ($agf_op->lines as $line) {
				$goalsList .= '    <li><i>' . $line->intitule . '</i></li>' . "\n";
			}
			$goalsList .= '</ol>' . "\n";
		}


		/**
		 * styles CSS définis dans le template destinés au rendu HTML par TCPDF
		 * @var string $css
		 */
		$defaultColor = 'rgb(' . implode(',', $this->colortext) . ')';
		$titleColor = 'rgb(' . implode(',', $this->colorhead) . ')';
		include __DIR__ . '/htmltpl/pdf_attestation_trainee.tpl.php';


		$titres = /** @lang HTML */
			// 'Attestation de formation'
			'<p class="centered title">{AgfPDFAttestation1}</p>'
			// 'Ce document atteste que XXXX a effectivement suivi […] le module intitulé:
			. '<p class="centered ">'
			. '             {AgfPDFAttestation2} '.$traineeCivility.' <span class="big">'.$traineeName.'</span>'
			. '             <br/> {AgfPDFAttestation3}'
			. '</p>'
			// « blablabla »
			. '<p class="centered intitule-forma">« ' . $agf->intitule_custo . ' »</p>'
			// 'Cette formation s'est déroulée du XX au XX (pour un total de X h effectives)'
			. '<p class="centered ">'
			. '             {AgfPDFAttestation4} '.$session_dates
			.'              {AgfPDFAttestation5} '.$duree_session.' {AgfPDFAttestation6}'
			. '</p>';

		// Modèle 1: on essaye de tout faire tenir sur la première page, y compris les objectifs pédagogiques
		$res = $funcPrintNonBreakBlock(
			// Titres:
			//     "Attestation de formation",
			//     "Ce document atteste que … a effectivement suivi … le module de formation intitulé …",
			//     "Cette formation s'est déroulée du… au … pour un total de …h effectives"
			$titres,

			// Objectifs pédagogiques:
			//   'À l'issue de cette formation, le participant est arrivé aux objectifs suivants:'
			'bulletList', // style spécial (car tcpdf ne décale pas les puces quand il y a du padding -> chevauchement)
			($goalsList ? '<p class="centered">{AgfPDFAttestation7}</p>' . $goalsList : ''),

			// 'Avec les félicitations du pôle formation de … '
			'default',
			'<p class="centered">{AgfPDFAttestation8} ' . $mysoc->name . '</p>',

			// 'Fait à … le … '
			'<p>{AgfPDFConv20} ' . $mysoc->town . ', {AgfPDFFichePres8}, ' . date("d/m/Y") . '</p>',

			// Prénom Nom du représentant de l'organisme de formation (sur la même ligne que le précédent)
			'RESET:Y', // s'affiche sur la même ligne que "Fait à … le …"
			'<p class="align-right">' . $conf->global->AGF_ORGANISME_REPRESENTANT . '</p>'
		);

		if ($res) {
		} else {
			// Les objectifs pédagogiques ne tiennent pas sur la page 1 -> Modèle alternatif
			//      = objectifs pédagogiques sur la/les pages suivantes (texte légèrement différent)
			$funcPrintNonBreakBlock(
				// Titres:
				//     "Attestation de formation",
				//     "Ce document atteste que … a effectivement suivi … le module de formation intitulé …",
				//     "Cette formation s'est déroulée du… au … pour un total de …h effectives"
				$titres,

				// 'À l'issue de cette formation, le participant est arrivé aux objectifs détaillés ci-après.'
				$goalsList ? '<p class="centered">{AgfPDFAttestationGoalsTitleShort}</p>' : '',

				// 'Avec les félicitations du pôle formation de … '
				'default',
				'<p class="centered">{AgfPDFAttestation8} ' . $mysoc->name . '</p>',

				// 'Fait à … le … '
				'<p>{AgfPDFConv20} ' . $mysoc->town . ', {AgfPDFFichePres8}, ' . date("d/m/Y") . '</p>',

				// Prénom Nom du représentant de l'organisme de formation (sur la même ligne que le précédent)
				'RESET:Y',
				'<p class="align-right">' . $conf->global->AGF_ORGANISME_REPRESENTANT . '</p>'
			);

			// saut de page puis objectifs pédagogiques
			$AgfPDFAttestationGoalsTitleFull = $outputlangs->transnoentities(
				'AgfPDFAttestationGoalsTitleFull',
				$agf->intitule_custo,
				$traineeCivility . ' ' . $traineeName
			);
			if ($goalsList) {
				$funcPrintBlocks(
				// 'À l'issue du module de formation intitulé %s, le participant %s est arrivé aux objectifs suivants :'
					'BREAK:FORCE', // saut de page obligatoire
					'<p>' . $AgfPDFAttestationGoalsTitleFull . '</p>',

					// objectifs (on autorise les sauts de page à l'intérieur du paragraphe)
					'BREAK:ALLOW',
					'bulletList', // style spécial liste à puce car TCPDF ne décale pas les puces quand il y a du padding
					$goalsList
				);
			}
		}

		// Mise en place du copyright
		$pdf->SetFont(pdf_getPDFFont($outputlangs), '', 8);
		$this->str = $outputlangs->transnoentities('copyright ' . date("Y") . ' - ' . $mysoc->name);
		$this->width = $pdf->GetStringWidth($this->str);
		// alignement du bord droit du container avec le haut de la page
		$baseline_ecart = $this->page_hauteur - $this->marge_haute - $this->marge_basse - $this->width;
		$baseline_angle = (M_PI / 2); // angle droit
		$baseline_x = $this->page_largeur - $this->marge_gauche - 12;
		$baseline_y = $baseline_ecart + 30;
		$baseline_width = $this->width;

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
	}

	/**
	 * En-tête de page (ne pas changer la signature de la méthode car définie sur classe parente)
	 * @param TCPDF $pdf
	 * @param Object $object
	 * @param bool $showaddress
	 * @param Translate $outputlangs
	 * @return void
	 */
	function _pagehead(&$pdf, $object, $showaddress, $outputlangs) {
		global $conf, $langs;

		$outputlangs->load("main");

		$this->setAutoPageBreak(false);
		pdf_pagehead($pdf, $outputlangs, $this->page_hauteur);
		$this->setAutoPageBreak(true);
	}

	/**
	 * Pied de page (ne pas changer la signature de la méthode car définie sur classe parente)
	 * @param TCPDF $pdf
	 * @param Object $object  Not used
	 * @param Translate $outputlangs
	 * @return int
	 */
	function _pagefoot($pdf, $object, $outputlangs) {
		global $conf, $langs, $mysoc;
		$this->setAutoPageBreak(false);

		if (!empty($conf->global->AGF_HIDE_DOC_FOOTER)) return $this->marge_basse;

		$pdf->SetDrawColor($this->colorfooter[0], $this->colorfooter[1], $this->colorfooter[2]);
		$pdf->SetTextColor($this->colorfooter[0], $this->colorfooter[1], $this->colorfooter[2]);
		$this->str = $mysoc->name;
		$this->str .= ' ' . $outputlangs->transnoentities('AgfPDFFoot12') . ' ';
		if (! empty($conf->global->AGF_ORGANISME_PREF)) {
			$this->str .= ' ' . $outputlangs->transnoentities('AgfPDFFoot10') . ' ' . $conf->global->AGF_ORGANISME_PREF;
		}
		if (! empty($conf->global->AGF_ORGANISME_NUM)) {
			$this->str .= ' ' . $outputlangs->transnoentities('AgfPDFFoot11',$conf->global->AGF_ORGANISME_NUM);
		}

		$pdf->SetXY($this->marge_gauche + 1, $this->page_hauteur - $this->marge_basse);
		$pdf->SetFont(pdf_getPDFFont($outputlangs), 'I', 8);
		$pdf->SetTextColor($this->colorfooter [0], $this->colorfooter [1], $this->colorfooter [2]);
		$pdf->Cell(0, 6, $outputlangs->convToOutputCharset($this->str), 0, 0, 'C', 0);

		$this->setAutoPageBreak(true);
		// resetcolor
		$pdf->SetTextColor($this->colorheaderText[0], $this->colorheaderText[1], $this->colorheaderText[2]);
		return $this->marge_basse;
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

		// Logo de l'organisme de formation en haut à gauche
		if (!empty($style['use-company-logo'])) {
			$logo = $conf->mycompany->dir_output . '/logos/' . $this->emetteur->logo;
			// Logo en haut à gauche
			if ($this->emetteur->logo) {
				if (is_readable($logo)) {
					$pdf->Image(
						$logo,
						0, // géré par le paramètre palign = 'L' (left)
						$this->marge_haute + $this->getFrameOffset(),
						$logoMaxWidth,
						$logoMaxHeight,
						'',
						'',
						'N',
						false,
						300,
						'L',
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
			$finalY = max($finalY, $pdf->GetY());
		}

		// Logo client (optionnel) en haut à droite
		if (!empty($style['use-client-logo']) && !empty($conf->global->AGF_USE_LOGO_CLIENT)) {
			$staticsoc = new Societe($this->db);
			$staticsoc->fetch($agf->socid);
			$dir = $conf->societe->multidir_output [$staticsoc->entity] . '/' . $staticsoc->id . '/logos/';
			if (! empty($staticsoc->logo)) {
				$logo_client = $dir . $staticsoc->logo;
				if (is_readable($logo_client)){
					$pdf->Image(
						$logo_client,
						0, // géré par le paramètre palign = 'L' (left)
						$this->marge_haute + $this->getFrameOffset(),
						$logoMaxWidth,
						$logoMaxHeight,
						'',
						'',
						'N',
						false,
						300,
						'R',
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
			$finalY = max($finalY, $pdf->GetY());
		}

		// Tampon de l'organisme de formation en bas à droite
		if (!empty($style['use-company-stamp']) && !empty($conf->global->AGF_INFO_TAMPON)) {
			$dir = $conf->agefodd->dir_output . '/images/';
			$stampImg = $dir . $conf->global->AGF_INFO_TAMPON;
			if (is_readable($stampImg)) {
				$stampHeight = min($stampMaxHeight, pdf_getHeightForLogo($stampImg));
				$pdf->Image(
					$stampImg,
					$this->page_largeur - ($this->marge_gauche + $this->marge_droite + $this->getFrameOffset() * 2 + $stampMaxWidth + $stampRightOffset),
					$this->page_hauteur - ($this->marge_basse + $this->getFrameOffset() + $stampMaxHeight + $stampBottomOffset),
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
