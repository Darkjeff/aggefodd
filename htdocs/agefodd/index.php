<?php
/* Copyright (C) 2003		Rodolphe Quiedeville	<rodolphe@quiedeville.org>
 * Copyright (C) 2004-2008	Laurent Destailleur	<eldy@users.sourceforge.net>
 * Copyright (C) 2004		Eric Seigne		<eric.seigne@ryxeo.com>
 * Copyright (C) 2005-2009	Regis Houssin		<regis@dolibarr.fr>
 * Copyright (C) 2009-2010	Erick Bullier		<eb.dev@ebiconsulting.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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
	\file		$HeadURL: https://192.168.22.4/dolidev/trunk/agefodd/index.php $
	\brief		Tableau de bord du module de formation pro. (Agefodd).
	\Version	$Id: index.php 51 2010-03-28 17:06:42Z ebullier $
*/

require("./pre.inc.php");
require_once("./agefodd_index.class.php");
require_once("./agefodd_sessadm.class.php");
require_once("./lib/lib.php");
require_once("../lib/date.lib.php");

// Security check
if (!$user->rights->agefodd->lire) accessforbidden();


$db->begin();

llxHeader();

$sortorder=$_GET["sortorder"];
$sortfield=$_GET["sortfield"];
$page=$_GET["page"];

if (! $sortorder) $sortorder="DESC";
if (! $sortfield) $sortfield="c.date";


if ($page == -1) { $page = 0 ; }

$limit = $conf->liste_limit;
$offset = $limit * $page ;
$pageprev = $page - 1;
$pagenext = $page + 1;


print_barre_liste($langs->trans("AgfBilanGlobal"), $page, "index.php","&socid=$socid",$sortfield,$sortorder,'',$num);


print '<table width="auto">';

//colonne gauche
print '<tr><td width=auto>';
print '<table class="noborder" width="400px">';
print '<tr class="liste_titre"><td colspan=4>Statistiques</td></tr>';	

$agf = new Agefodd_index($db);

// Nbre de formation au catalogue actuellement
$resql = $agf->fetch_formation_nb();
	print '<tr class="liste"><td>formation au catalogue actuellement: </td><td align="right">';
	print '<a href="'.DOL_URL_ROOT.'/agefodd/f_liste.php?mainmenu=&leftmenu=agefodd">';
	print $agf->num.'</a>&nbsp;</td></tr>';


// nbre de stagiaires formés
$resql = $agf->fetch_student_nb();
	print '<tr class="liste"><td>stagiaires formés: </td><td align="right">'.$resql.'&nbsp;</td></tr>';	


// nbre de sessions realisées
$resql = $agf->fetch_session_nb();
	$nb_total_session = $agf->num;
	print '<tr class="liste"><td>sessions réalisées: </td><td align="right">'.$nb_total_session.'&nbsp;</td></tr>';	


// Nbre d'heure/session délivrées
$resql = $agf->fetch_heures_sessions_nb();
	print '<tr class="liste"><td>heures/sessions délivrées : </td><td align="right">'.$agf->total.'&nbsp;</td></tr>';	
	$total_heures = $agf->total;



// Nbre d'heures stagiaires délivrées
$resql = $agf->fetch_heures_stagiaires_nb();
	print '<tr class="liste"><td>heures/stagiaires réalisées : </td><td align="right">'.$agf->total.'&nbsp;</td></tr>';	

print '<table>';
print '&nbsp;';
print '<table class="noborder" width="400px">';

// Les 5 dernieres sessions
print '<tr class="liste_titre"><td colspan=4>5 dernières sessions réalisées</td></tr>';	
$resql = $agf->fetch_last_formations(5);
	$num = count($agf->line);
	for ($i=0; $i < $num; $i++)
	{
		print '<tr class="liste"><td>';
		print '<a href="'.DOL_URL_ROOT.'/agefodd/s_fiche.php?id='.$agf->line[$i]->id.'">';
		print img_object($langs->trans("AgfShowDetails"),"generic").' '.$agf->line[$i]->id.'</a></td>';
		print '<td colspan=2>'.dol_trunc($agf->line[$i]->intitule, 50).'</td><td align="right">';
		
		$today_mktime = mktime(0, 0, 0, date("m"), date("d"), date("y"));
		$endsession_mktime = (mysql2timestamp($agf->line[$i]->datef));
		$ilya = (num_between_day($endsession_mktime, $today_mktime) + 1);
		print "il y a ".$ilya." j.";
		print '</td></tr>';
	}

print '<table>';
print '&nbsp;';
print '<table class="noborder" width="400px">';

// top 5 des formations
print '<tr class="liste_titre"><td colspan=4>top 5 des formations délivrées (nb d\'occurence, % pondéré à la durée)</td></tr>';
$resql = $agf->fetch_top_formations(5);
	$num = count($agf->line);
	for ($i=0; $i < $num; $i++)
	{
		print '<tr class="liste"><td>';
		print '<a href="'.DOL_URL_ROOT.'/agefodd/f_fiche.php?id='.$agf->line[$i]->idforma.'">';
		print img_object($langs->trans("AgfShowDetails"),"service").' '.$agf->line[$i]->idforma.'</a></td>';
		//print '<td colspan=2>'.dol_trunc($agf->line[$i]->intitule, 50).'</td><td align="right">'.$agf->line[$i]->num.' '.sprintf("(%02.1f%%)", (($agf->line[$i]->num *100)/$nb_total_session)).'</td></tr>';
		// On calcul le % en focntion du nombre d'heure de cette session sur le nombre d'heure total
		print '<td colspan=2>'.dol_trunc($agf->line[$i]->intitule, 50).'</td><td align="right">'.$agf->line[$i]->num.' '.sprintf("(%02.1f%%)", (($agf->line[$i]->num * $agf->line[$i]->duree * 100)/$total_heures) ).'</td></tr>';
	}
print "</table>";
print '&nbsp;';


//colonne droite
print '</td><td width="auto" valign="top">';

// tableau de bord travail
print '<table class="noborder" width="500px" align="left">';
print '<tr class="liste_titre"><td colspan=3> Tableau de bord de travail</td>';
print '<td width="50px" align="right">Nombre</td></tr>';

// sessions en cours
print '<tr class="liste"><td width="10px">'.img_object($langs->trans("AgfShowDetails"),"generic").'</td>';
$resql = $agf->fetch_session(0);
	print '<td colspan="2" >sessions en cours</td><td align="right">';	
	print '<a href="'.DOL_URL_ROOT.'/agefodd/s_liste.php?mainmenu=&leftmenu=agefodd">'.$agf->total.'</a>&nbsp;</td></tr>' ;	

$agf1 = new Agefodd_sessadm($db);

// tâches en retard
print '<tr class="liste"><td width="10px">&nbsp;</td><td bgcolor="red">'.img_object($langs->trans("AgfShowDetails"),"task").'</td>';
$resql = $agf->fetch_tache_en_retard(0);
	print '<td>'.$langs->trans("AgfAlertLevel0").'</td><td align="right">';
	print '<a href="'.DOL_URL_ROOT.'/agefodd/s_adm_liste.php?filtre=0">'.$agf->total.'</a>&nbsp;</td></tr>' ;	

// Taches urgentes (3 jours avant limite)
print '<tr class="liste"><td width="10px">&nbsp;</td><td bgcolor="orange">'.img_object($langs->trans("AgfShowDetails"),"task").'</td>';

$agf1->fetch_session_per_dateLimit('asc', 's.datea', '10', '0', 3, 1);
$nbre = count($agf1->line);
	print '<td >'.$langs->trans("AgfAlertLevel1").'</td><td align="right">';
	print '<a href="'.DOL_URL_ROOT.'/agefodd/s_adm_liste.php?filtre=1">'.$nbre.'</a>&nbsp;</td></tr>' ;	
// $resql = $agf->fetch_tache_en_retard(3);
// if ($resql)
// {	
// 	print '<td >'.$langs->trans("AgfAlertLevel1").'</td><td align="right">';
// 	print '<a href="'.DOL_URL_ROOT.'/agefodd/s_adm_liste.php?filtre=3">'.$agf->total.'</a>&nbsp;</td></tr>' ;	
// 	$db->free($resql);
// }
// else dol_print_error($db);

// Taches à planifier (8 jours avant limite)
print '<tr class="liste"><td width="10px">&nbsp;</td><td bgcolor="#ffe27d">'.img_object($langs->trans("AgfShowDetails"),"task").'</td>';
$agf1->fetch_session_per_dateLimit('asc', 's.datea', '10', '0', 8, 3);
$nbre = count($agf1->line);
	print '<td >'.$langs->trans("AgfAlertLevel2").'</td><td align="right">';
	print '<a href="'.DOL_URL_ROOT.'/agefodd/s_adm_liste.php?filtre=2">'.$nbre.'</a>&nbsp;</td></tr>' ;	
// $resql = $agf->fetch_tache_en_retard(5);
// if ($resql)
// {	
// 	print '<td >'.$langs->trans("AgfAlertLevel2").'</td><td align="right">';
// 	print '<a href="'.DOL_URL_ROOT.'/agefodd/s_adm_liste.php?filtre=5">'.$agf->total.'</a>&nbsp;</td></tr>' ;	
// 	$db->free($resql);
// }
// else dol_print_error($db);


// tâches en cours
print '<tr class="liste"><td width="10px">&nbsp;</td><td width="10px">'.img_object($langs->trans("AgfShowDetails"),"task").'</td>';
$resql = $agf->fetch_tache_en_cours();
	print '<td>'.$langs->trans("AgfAlertLevel3").'</td><td align="right">';
	print '<a href="'.DOL_URL_ROOT.'/agefodd/s_adm_liste.php?filtre=3">'.$agf->total.'</a>&nbsp;</td></tr>' ;	

// sessions à archiver
print '<tr class="liste"><td width="10px" valign="top">'.img_object($langs->trans("AgfShowDetails"),"generic").'</td>';
$num = $agf->fetch_session_to_archive();
if ($num > 0)
{	
	print '<td colspan="2" >sessions prêtes à être archivées</td><td align="right">';	
	print '<a href="'.DOL_URL_ROOT.'/agefodd/s_liste.php?arch=2"">'.$num.'</a>&nbsp;</td></tr>' ;	
}
else
{
	print '<td colspan="2" >sessions prêtes à être archivées</td><td align="right">';	
	print '0&nbsp;</td></tr>';
	
}


// sessions archivées
print '<tr class="liste"><td width="10px">'.img_object($langs->trans("AgfShowDetails"),"generic").'</td>';
$resql = $agf->fetch_session(1);
if ($resql)
{	
	print '<td colspan="2" >sessions archivées</td><td align="right">';	
	print '<a href="'.DOL_URL_ROOT.'/agefodd/s_liste.php?arch=1&mainmenu=&leftmenu=agefodd">'.$agf->total.'</a>&nbsp;</td></tr>' ;	
	//$db->free($resql);
}
else
{
	print '<td colspan="3">&nbsp;</td></tr>';
	//dol_print_error($db);
}

// fin colonne droite
print '</td></tr></table>';
$db->close();

llxFooter('$Date: 2010-03-28 19:06:42 +0200 (dim. 28 mars 2010) $ - $Revision: 51 $');
?>
