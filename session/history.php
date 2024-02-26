<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (! $res)
	die("Include of main fails");

require_once '../class/agsession.class.php';
require_once '../lib/agefodd.lib.php';


$id = GETPOST('id', 'none');
$with_calendar = GETPOST('with_calendar','alpha');
if (empty($with_calendar)) {
	$with_calendar = 'nocalendar';
}
llxHeader('', $langs->trans("Events/Agenda"));

if (! empty($id)) {
	$agf = new Agsession($db);
	$agf->fetch($id);

	$result = $agf->fetch_societe_per_session($id);

	// Display consult
	$head = session_prepare_head($agf);

	dol_fiche_head($head, 'agenda', $langs->trans("AgfSessionDetail"), 0, 'generic');

	dol_agefodd_banner_tab($agf, 'id');
	print '<div class="underbanner clearboth"></div>';

	// List of actions on element
	//include_once (DOL_DOCUMENT_ROOT . '/core/class/html.formactions.class.php');
	$filters ='';
	$sortfield='';
	$sortorder='';


	print '<div id="inclusion"></div>';
	_printAjaxActionCommList($id);



}
/**
 *  load la liste des evenements agenda en ajax
 * (la requête est adaptée  pour récupérer les elements associés à la session courante via les hooks disponibles sur la liste).
 *
 * @param $id id de la session
 */
function _printAjaxActionCommList($id) {

	global $hookmanager;
	$TParamURL = $_REQUEST;

	?>

	<script type="text/javascript" language="javascript">
		$(document).ready(function() {

			$.ajax({
				// Appel ajax à la liste des événements agenda (avec le paramètre url origin_page=agefodd_history)
				url:"<?php print dol_buildpath('/comm/action/list.php', 1).'?origin_page=agefodd_history&search_status=&'.http_build_query($TParamURL); ?>"
			}).done(function(data) {

				// On ne récupère que le bloc formulaire
				var form_contacts = $(data).find('div.fiche #searchFormList');


				// On remplace les liens de la pagination pour rester sur la card session événements en cas de changement de page
				form_contacts.find('table.table-fiche-title a').each(function() {
					$(this).attr('href', $(this).attr('href').replace("<?php print dol_buildpath('/comm/action/list.php', 1); ?>", "<?php print dol_buildpath('/agefodd/session/history.php', 1); ?>"));
					if(!($(this).hasClass('btnTitlePlus'))) { // Il ne faut pas ajouter de id= sur le bouton "+" car sinon en 15 et en develop ça fait apparaître une card de l'action comm qui a le même id que la session
						$(this).attr('href', $(this).attr('href') + '&id=' + <?php print $id; $hookmanager->executeHooks('addMoreURLParams', $parameters, $object, $action); ?>);
					}
				});

				// On remplace les liens de tri pour rester sur la card session événements en cas de tri sur une colonne
				form_contacts.find('table.liste tr.liste_titre a').each(function() {
					$(this).attr('href', $(this).attr('href').replace("<?php print dol_buildpath('/comm/action/list.php', 1); ?>", "<?php print dol_buildpath('/agefodd/session/history.php', 1); ?>"));
					$(this).attr('href', $(this).attr('href') + '&id=' + <?php print $id; $hookmanager->executeHooks('addMoreURLParams', $parameters, $object, $action); ?>);
				});

				// Formulaire (pour que lors d'un recherche on reste bien sur la card session événements)
				form_contacts.attr('action', form_contacts.attr('action').replace("<?php print dol_buildpath('/comm/action/list.php', 1); ?>", "<?php print dol_buildpath('/agefodd/session/history.php', 1); ?>"));
				form_contacts.attr('action', form_contacts.attr('action') + '?id=' + <?php print $id; ?>);

				// on retire les liens vers les vues semaines ... jours car pas de sens dans ce contexte
				form_contacts.find('li.paginationbeforearrows').remove();

				// on recupere le bouton  + dans l'entete de la liste
				let act = form_contacts.find('li.paginationafterarrows > a.btnTitlePlus')

				// on ajoute les paramètres url necessaires pour que l'événement soit automatiquement lié à la session lors de la création depuis la card session événements
				act.attr('href', act.attr('href') + '&fk_element=' + <?php print $id; ?> + '&elementtype=agefodd_agsession&origin_page=agefodd_history');

				// on change ulr de retour  pour revenir à la liste filtrée sur les event de la session active (contenu du backtopage)
				act.attr('href', act.attr('href').replace("<?php print urlencode(dol_buildpath('/comm/action/list.php?', 1)); ?>", "<?php print urlencode(dol_buildpath('/agefodd/session/history.php', 1).'?id='.$id); ?>"));


				// On affiche la liste des contacts
				$("#inclusion").append(form_contacts);

				<?php

				$reshook = $hookmanager->executeHooks('printMoreAfterAjax', $parameters, $object, $action);

				?>

			});

		});
	</script>

	<?php

}

llxFooter();

