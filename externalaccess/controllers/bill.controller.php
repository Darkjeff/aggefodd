<?php


class BillController extends Controller
{

	public function __construct()
	{
		global $conf, $user, $db;
		parent::__construct();
		$this->db = $db;
	}



	/**
	 * check current access to controller
	 *
	 * @param void
	 * @return  bool
	 */
	public function checkAccess()
	{
		global $conf, $user;

		$this->userCanCreate = !empty($user->rights->fournisseur->facture->creer);

		if (!empty($user->socid)) {
			$this->thirdparty = new Societe($this->db);
			$res = $this->thirdparty->fetch($user->socid);
			if ($res>0) {
				$this->accessRight = $this->userCanCreate && empty($conf->global->AGF_TRAINERS_CAN_CREATE_SUPPLIERINVOICES_FOR_A_SESSION) && !empty($user->rights->externalaccess->view_supplier_invoices);
			}
		}

		return parent::checkAccess();
	}

	/**
	 * action method is called before html output
	 * can be used to manage security and change context
	 *
	 * @param void
	 * @return void
	 */
	public function action()
	{
		global $user, $conf, $langs;

		$langs->load('agfexternalaccess@agefodd');
		$langs->load('orders');

		$context = Context::getInstance();
		if (!$context->controllerInstance->checkAccess()) { return; }

		$context->title = $langs->trans('AgfMakeASupplierBillHour');
		$context->desc = $langs->trans('AgfMakeASupplierBillDesc');
		$context->menu_active[] = 'agfbill';

		$hookRes = $this->hookDoAction();

		if (empty($hookRes)) {
			if ($context->action == "createSupplierInvoice") {
				if ($this->userCanCreate ) {
					$ref_supplier = GETPOST('ref_supplier', 'alphanohtml');

					if (!empty($conf->global->AGF_SERVICE_FOR_HOURS_IN_TRAINERSINVOICES) && $conf->global->AGF_SERVICE_FOR_HOURS_IN_TRAINERSINVOICES != -1) {
						$lineHours = new stdClass();
						$lineHours->qty = GETPOST('hoursQty', 'int');
						$lineHours->puht = GETPOST('hoursUnitPrice', 'int');
						$lineHours->tva_tx = GETPOST('hoursVAT', 'int');
						$lineHours->notes = GETPOST('hoursNote', 'alphanohtml');
						if (empty($lineHours->tva_tx)) $lineHours->tva_tx = 0;
						$lineHours->fk_product = $conf->global->AGF_SERVICE_FOR_HOURS_IN_TRAINERSINVOICES;
						$lineHours->toInsert = (!empty($lineHours->qty) && !empty($lineHours->puht) && !empty($lineHours->fk_product));
					}

					if (!empty($conf->global->AGF_SERVICE_FOR_MISC_IN_TRAINERSINVOICES) && $conf->global->AGF_SERVICE_FOR_MISC_IN_TRAINERSINVOICES != -1) {
						$lineMisc = new stdClass();
						$lineMisc->qty = GETPOST('miscQty', 'int');
						$lineMisc->puht = GETPOST('miscUnitPrice', 'int');
						$lineMisc->tva_tx = GETPOST('miscVAT', 'int');
						$lineMisc->notes = GETPOST('miscNote', 'alphanohtml');
						if (empty($lineMisc->tva_tx)) $lineMisc->tva_tx = 0;
						$lineMisc->fk_product = $conf->global->AGF_SERVICE_FOR_MISC_IN_TRAINERSINVOICES;
						$lineMisc->toInsert = (!empty($lineMisc->qty) && !empty($lineMisc->puht) && !empty($lineMisc->fk_product));
					}

					// test poids du fichier
					if (!empty($_FILES) && !empty($conf->global->MAIN_UPLOAD_DOC)) {
						if (is_array($_FILES['userfile']['tmp_name'])) $userfiles = $_FILES['userfile']['tmp_name'];
						else $userfiles = array($_FILES['userfile']['tmp_name']);

						$error = 0;

						foreach ($userfiles as $key => $userfile) {
							if (empty($_FILES['userfile']['tmp_name'][$key])) {
								if ($_FILES['userfile']['error'][$key] == 1 || $_FILES['userfile']['error'][$key] == 2) {
									$error++;
									$context->setError($langs->trans('ErrorFileSizeTooLarge'));
								}
							}
						}
					}

					if (!$error && (!empty($lineHours->toInsert) || !empty($lineMisc->toInsert))) {
						require_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.facture.class.php';
						$facFourn = new FactureFournisseur($this->db);
						$facFourn->socid = $this->thirdparty->id;
						$facFourn->type = FactureFournisseur::TYPE_STANDARD;
						$facFourn->date = dol_now();
						$facFourn->ref_supplier = $ref_supplier;
						$res = $facFourn->create($user);
						if ($res > 0) {
							$context->setEventMessages($langs->transnoentities('agfSupplierInvoiceCreatedAndAddedToSuppliarInvoicesTab', $facFourn->ref_supplier));
							// ajout des lignes saisies
							if (!empty($lineHours->toInsert)) {
								$res = $facFourn->addline($lineHours->notes, $lineHours->puht, $lineHours->tva_tx, 0, 0, $lineHours->qty, $lineHours->fk_product);
								if ($res > 0) $context->setEventMessages($langs->transnoentities('agfSupplierInvoiceHoursLineAdded'));
							}
							if (!empty($lineMisc->toInsert)) {
								$res = $facFourn->addline($lineMisc->notes, $lineMisc->puht, $lineMisc->tva_tx, 0, 0, $lineMisc->qty, $lineMisc->fk_product);
								if ($res > 0) $context->setEventMessages($langs->transnoentities('agfSupplierInvoiceMiscLineAdded'));
							}


							// liaison du fichier joint à la facture s'il y en a un
							if (!empty($_FILES) && !empty($conf->global->MAIN_UPLOAD_DOC)) {
								$upload_dir = $conf->fournisseur->facture->dir_output . "/" . get_exdir($facFourn->id, 2, 0, 0, $facFourn, 'invoice_supplier').$facFourn->ref;


								if (!$error) {
									$result = dol_add_file_process($upload_dir, 0, 1, 'userfile', GETPOST('savingdocmask', 'alpha'));

									if ($result < 0) {
										$error++;
									} else $context->setEventMessages($langs->transnoentities('AgfUploadSuccess'));
								}
							}
						}
					} elseif (!$error) {
						$context->setError($langs->trans("AgfNoLineToCreate"));
					}
				} else {
					$context->setError($langs->trans("AgfCreateInvoiceRightPb"));
				}

				//Reste sur cette page après création de la facture fournisseur
				$url = $context->getRootUrl($context->controller);
				header('Location: ' . $url);
				exit;
			}
		}
	}


	/**
	 *
	 * @param void
	 * @return void
	 */
	public function display()
	{
		global $conf, $user;
		$context = Context::getInstance();
		if (!$context->controllerInstance->checkAccess()) {  return $this->display404(); }

		$this->loadTemplate('header');

		$hookRes = $this->hookPrintPageView();
		if (empty($hookRes)) {
			print $this->getPageViewSessionCardExternalAccess_supplierinvoice();
		}
		$this->loadTemplate('footer');
	}


	function getPageViewSessionCardExternalAccess_supplierinvoice()
	{
		global $langs, $db, $hookmanager, $conf, $user;

		$langs->load('agfexternalaccess@agefodd');
		$langs->load('bills');

		$context = Context::getInstance();
		if (!validateFormateur($context)) return '';

		// et si on vérifiait que le user à le droit de créer des factures fourn ?
		$hoursConfigured = !empty($conf->global->AGF_SERVICE_FOR_HOURS_IN_TRAINERSINVOICES) && $conf->global->AGF_SERVICE_FOR_HOURS_IN_TRAINERSINVOICES != -1;
		$miscConfigured = !empty($conf->global->AGF_SERVICE_FOR_MISC_IN_TRAINERSINVOICES) && $conf->global->AGF_SERVICE_FOR_MISC_IN_TRAINERSINVOICES != -1;

		$displayForm = true;
		$out = '<section id="section-ticket"><div class="container">';
		// droit de créer des factures fourn
		if (!$this->userCanCreate ) {
			$displayForm = false;
			$out .= '<div class="alert alert-secondary" role="alert">'.$langs->trans('AgfCreateInvoiceRightPb').'</div>';
		}

		// le tiers attaché est-il fournisseur
		if (empty($this->thirdparty->fournisseur)) {
			$displayForm = false;
			$out .= '<div class="alert alert-secondary" role="alert">'.$langs->trans('AgfThirdpartyNotSupplierPb').'</div>';
		}

		// droit d'upload
		if (empty($user->rights->agefodd->external_trainer_upload)) {
			$displayForm = false;
			$out.='<div class="alert alert-secondary" role="alert">'.$langs->trans('AgfDownloadRightPb').'</div>';
		}

		// vérification des configurations
		if (!$hoursConfigured && !$miscConfigured) {
			$displayForm = false;
			$out.='<div class="alert alert-secondary" role="alert">'.$langs->trans('AgfTrainerInvoicesConfiguration').'</div>';
		}


		if ($displayForm && ($hoursConfigured || $miscConfigured)) {
			$out.= '<!-- getPageViewSessionCardExternalAccess_files -->
			<div class="container px-0">
				<div class="panel panel-default">
					<div class="panel-body">
						<div class="row clearfix">
							<div class="col-md-12">
								<h5>'.$langs->trans('AgfCreateInvoice').'</h5><br />';



			// show form
			$url = $context->getRootUrl('agfbill').'&action=createSupplierInvoice';

			$out .= '<form name="formusertrainer" id="formusertrainer" action="'.$url.'" enctype="multipart/form-data" method="POST">';
			$out .= '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
			//      $out .= '<input type="hidden" name="action" value="createTrainerInvoice">';
			$out .= '<input type="hidden" name="socid" value="'.$this->thirdparty->id.'">';

			$out .= '<div>';
			$out .= $langs->transnoentities('Reference').' '.strtolower($langs->transnoentities('Bill')) .' : ';
			$out .= '<input type="text" name="ref_supplier" required>';
			$out .= '</div><br>';

			$out .= '<table width="100%" class="nobordernopadding border">';
			$out .= '<tr>';
			$out .= '<th>'.$langs->transnoentities('Product').'</th>';
			$out .= '<th>'.$langs->transnoentities('Qty').'</th>';
			$out .= '<th>'.$langs->transnoentities('UnitPrice').'</th>';
			$out .= '<th>'.$langs->transnoentities('VAT').'</th>';
			$out .= '<th>'.$langs->transnoentities('TotalHT').'</th>';
			$out .= '<th>'.$langs->transnoentities('AgfBillNotes').'</th>';
			$out .= '</tr>';

			if ($hoursConfigured) {
				$out .= '<tr>';

				$out .= '<td class="valignmiddle nowrap">';
				$out .= $langs->transnoentities('AgfHoursToBill');
				$out .= "</td>";

				$out .= '<td class="valignmiddle nowrap">';
				$out .= '<input type="number" style="width: 7em" name="hoursQty" id="hoursQty" step="0.1" min="0">';
				$out .= "</td>";

				$out .= '<td class="valignmiddle nowrap">';
				$out .= '<input type="number" style="width: 7em" name="hoursUnitPrice" id="hoursUnitPrice" step="0.1" min="0">';
				$out .= "</td>";

				$out .= '<td class="valignmiddle nowrap">';
				$out .= '<input type="number" style="width: 7em" name="hoursVAT" id="hoursVAT" step="0.1" min="0">';
				$out .= "</td>";

				$out .= '<td class="valignmiddle nowrap">';
				$out .= '<input type="number" style="width: 7em" name="hoursTotalHT" id="hoursTotalHT" step="0.1"  min="0" disabled>';
				$out .= "</td>";

				$out .= '<td class="valignmiddle nowrap">';
				$out .= '<input type="text" size="50" name="hoursNote" id="hoursNote">';
				$out .= "</td>";
				$out .= "</tr>";
			}

			if ($miscConfigured) {
				$out .= '<tr>';

				$out .= '<td class="valignmiddle nowrap">';
				$out .= $langs->transnoentities('AgfMiscelanous');
				$out .= "</td>";

				$out .= '<td class="valignmiddle nowrap">';
				$out .= '<input type="number" style="width: 7em" name="miscQty" id="miscQty" step="0.1" min="0">';
				$out .= "</td>";

				$out .= '<td class="valignmiddle nowrap">';
				$out .= '<input type="number" style="width: 7em" name="miscUnitPrice" id="miscUnitPrice" step="0.1" min="0">';
				$out .= "</td>";

				$out .= '<td class="valignmiddle nowrap">';
				$out .= '<input type="number" style="width: 7em" name="miscVAT" id="miscVAT" step="0.1"  min="0">';
				$out .= "</td>";

				$out .= '<td class="valignmiddle nowrap">';
				$out .= '<input type="number" style="width: 7em" name="miscTotalHT" id="miscTotalHT" step="0.1" disabled  min="0">';
				$out .= "</td>";

				$out .= '<td class="valignmiddle nowrap">';
				$out .= '<input type="text" size="50" name="miscNote" id="miscNote">';
				$out .= "</td>";
				$out .= "</tr>";
			}

			$out .= "</table>";

			$out .= '<div>';
			$max=$conf->global->MAIN_UPLOAD_DOC;		// En Kb
			$maxphp=@ini_get('upload_max_filesize');	// En inconnu
			if (preg_match('/k$/i', $maxphp)) $maxphp=$maxphp*1;
			if (preg_match('/m$/i', $maxphp)) $maxphp=$maxphp*1024;
			if (preg_match('/g$/i', $maxphp)) $maxphp=$maxphp*1024*1024;
			if (preg_match('/t$/i', $maxphp)) $maxphp=$maxphp*1024*1024*1024;
			// Now $max and $maxphp are in Kb
			$maxmin = $max;
			if ($maxphp > 0) $maxmin=min($max, $maxphp);

			if ($maxmin > 0) {
				// MAX_FILE_SIZE doit précéder le champ input de type file
				$out .= '<input type="hidden" name="max_file_size" value="'.($maxmin*1024).'">';
			}

			$out .= '<h6>'.$langs->transnoentities('AgfJoinAttacchement').'</h6>';
			$out .= '<input class="flat minwidth400" type="file"';
			$out .= ((! empty($conf->global->MAIN_DISABLE_MULTIPLE_FILEUPLOAD) || $conf->browser->layout != 'classic')?' name="userfile"':' name="userfile[]" multiple');
			$out .= (empty($conf->global->MAIN_UPLOAD_DOC) ? ' disabled':'');
			$out .= '>';
			$out .= ' ';

			$out .= '</div><br>';

			$out .= '<input type="submit" class="button" name="sendit" value="'.$langs->trans("AgfUpload").'"';
			$out .= (empty($conf->global->MAIN_UPLOAD_DOC) ? ' disabled':'');
			$out .= '>';

			$out .= '</form>';

			$out .= '
			<script>
				$(document).ready(function (){
				    $("#hoursQty").on("change", function() {
				        if ($("#hoursUnitPrice").val() != "") $("#hoursTotalHT").val($(this).val() * $("#hoursUnitPrice").val());
				    });
				    $("#hoursUnitPrice").on("change", function() {
				        if ($("#hoursQty").val() != "") $("#hoursTotalHT").val($(this).val() * $("#hoursQty").val());
				    });

				    $("#miscQty").on("change", function() {
				        if ($("#miscUnitPrice").val() != "") $("#miscTotalHT").val($(this).val() * $("#miscUnitPrice").val());
				    });
				    $("#miscUnitPrice").on("change", function() {
				        if ($("#miscQty").val() != "") $("#miscTotalHT").val($(this).val() * $("#miscQty").val());
				    });

				    $("#formusertrainer").on("submit", function (e) {
				        /*e.preventDefault();
				        console.log($("#hoursUnitPrice").val(), $("#hoursQty").val(), $("#miscQty").val(), $("#miscUnitPrice").val())*/
				    });
				});
			</script>';


			$out.=			    '</div>
						</div>
					</div>
				</div>
			</div>';
		}


		$out.= '</div></section>';

		return $out;
	}
}
