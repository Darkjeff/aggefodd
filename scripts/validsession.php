<?php
/* Copyright (C) 2013 Florian Henry  <florian.henry@open-concept.pro>
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
* along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * \file /agefodd/scripts/createtaskadmin.php
 * \brief Generate script
 */
if (! defined ( 'NOTOKENRENEWAL' ))
	define ( 'NOTOKENRENEWAL', '1' ); // Disables token renewal
if (! defined ( 'NOREQUIREMENU' ))
	define ( 'NOREQUIREMENU', '1' );
if (! defined ( 'NOREQUIREHTML' ))
	define ( 'NOREQUIREHTML', '1' );
if (! defined ( 'NOREQUIREAJAX' ))
	define ( 'NOREQUIREAJAX', '1' );
if (! defined ( 'NOLOGIN' ))
	define ( 'NOLOGIN', '1' );

$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die ( "Include of main fails" );


dol_include_once ( '/user/class/user.class.php' );
dol_include_once ( '/comm/propal/class/propal.class.php' );
dol_include_once ( '/core/modules/propale/modules_propale.php' );


$userlogin = GETPOST ( 'login' );
$idpropal = GETPOST ( 'idpropal' );

$user = new User ( $db );
$result = $user->fetch ( '', $userlogin );

$propal = new Propal ( $db );
$propal->fetch ( $idpropal );
if (! empty ( $propal->id )) {

	$result = $propal->cloture ( $user, 2,'From Extranet RH' );

	if ($result < 0) {
		print - 1;
	} else {
		print 1;
	}
}

			
