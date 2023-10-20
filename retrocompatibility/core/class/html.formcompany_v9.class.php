<?php
/* Copyright (C) 2008-2012	Laurent Destailleur	<eldy@users.sourceforge.net>
 * Copyright (C) 2008-2012	Regis Houssin		<regis.houssin@inodbox.com>
 * Copyright (C) 2014		Juanjo Menent		<jmenent@2byte.es>
 * Copyright (C) 2017		Rui Strecht			<rui.strecht@aliartalentos.com>
 * Copyright (C) 2020       Open-Dsi         	<support@open-dsi.fr>
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
 *	\file       htdocs/core/class/html.formcompany.class.php
 *  \ingroup    core
 *	\brief      File of class to build HTML component for third parties management
 */


/**
 *	Class to build HTML component for third parties management
 *	Only common components are here.
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';


/**
 * Class of forms component to manage companies
 */
class FormCompanyFallBackV9 extends FormCompany
{

	/**
	 * Methode présente seulement à partir de la v10
	 * Return a HTML select for thirdparty type
	 *
	 * @param int $selected selected value
	 * @param string $htmlname HTML select name
	 * @param string $htmlidname HTML select id
	 * @param string $typeinput HTML output
	 * @param string $morecss More css
	 * @return string HTML string
	 */
	public function selectProspectCustomerType($selected, $htmlname = 'client', $htmlidname = 'customerprospect', $typeinput = 'form', $morecss = '')
	{

		global $conf, $langs;

		$out = '<select class="flat '.$morecss.'" name="'.$htmlname.'" id="'.$htmlidname.'">';
		if ($typeinput == 'form') {
			if ($selected == '' || $selected == '-1') $out .= '<option value="-1">&nbsp;</option>';
			if (empty($conf->global->SOCIETE_DISABLE_PROSPECTS)) {
				$out .= '<option value="2"'.($selected == 2 ? ' selected' : '').'>'.$langs->trans('Prospect').'</option>';
			}
			if (empty($conf->global->SOCIETE_DISABLE_PROSPECTS) && empty($conf->global->SOCIETE_DISABLE_CUSTOMERS) && empty($conf->global->SOCIETE_DISABLE_PROSPECTSCUSTOMERS)) {
				$out .= '<option value="3"'.($selected == 3 ? ' selected' : '').'>'.$langs->trans('ProspectCustomer').'</option>';
			}
			if (empty($conf->global->SOCIETE_DISABLE_CUSTOMERS)) {
				$out .= '<option value="1"'.($selected == 1 ? ' selected' : '').'>'.$langs->trans('Customer').'</option>';
			}
			$out .= '<option value="0"'.((string) $selected == '0' ? ' selected' : '').'>'.$langs->trans('NorProspectNorCustomer').'</option>';
		} elseif ($typeinput == 'list') {
			$out .= '<option value="-1"'.(($selected == '' || $selected == '-1') ? ' selected' : '').'>&nbsp;</option>';
			if (empty($conf->global->SOCIETE_DISABLE_PROSPECTS)) {
				$out .= '<option value="2,3"'.($selected == '2,3' ? ' selected' : '').'>'.$langs->trans('Prospect').'</option>';
			}
			if (empty($conf->global->SOCIETE_DISABLE_CUSTOMERS)) {
				$out .= '<option value="1,3"'.($selected == '1,3' ? ' selected' : '').'>'.$langs->trans('Customer').'</option>';
			}
			$out .= '<option value="4"'.($selected == '4' ? ' selected' : '').'>'.$langs->trans('Supplier').'</option>';
			$out .= '<option value="0"'.($selected == '0' ? ' selected' : '').'>'.$langs->trans('Other').'</option>';
		} elseif ($typeinput == 'admin') {
			if (empty($conf->global->SOCIETE_DISABLE_PROSPECTS) && empty($conf->global->SOCIETE_DISABLE_CUSTOMERS) && empty($conf->global->SOCIETE_DISABLE_PROSPECTSCUSTOMERS)) {
				$out .= '<option value="3"'.($selected == 3 ? ' selected' : '').'>'.$langs->trans('ProspectCustomer').'</option>';
			}
			if (empty($conf->global->SOCIETE_DISABLE_CUSTOMERS)) {
				$out .= '<option value="1"'.($selected == 1 ? ' selected' : '').'>'.$langs->trans('Customer').'</option>';
			}
		}
		$out .= '</select>';
		$out .= ajax_combobox($htmlidname);

		return $out;
	}
}
