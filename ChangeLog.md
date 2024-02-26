
# Change Log
All notable changes to this project will be documented in this file.
## Version 7.14 - Released on *29/05/2023*
- FIX : DA024423 : FIX Rapport BPF C1 ne doit pas tenir comptes des catégories clientes BPF - *01/02/2024* - 7.14.33
- FIX : DA024295 : FIX check if product id is superior to 0 - *10/01/2024* - 7.14.32
- FIX : DA024298 : FIX Compat PHP 8 for V7.14 - *04/01/2024* - 7.14.31
- FIX : DA024183 : onglet evenement/agenda d'une session utilisait le mauvais parametrge de dol_buildpath - *15/12/2023* - 7.14.30
- FIX : DA024173 : select liste formateur lors de l'édition d'un formateur virait le formateur sélectionné du select - *23/11/2023* - 7.14.29 
- FIX : DA024012 : rapport détaillé par client, fichier rendu scrollable horizontalement - *14/11/2023* - 7.14.28
- FIX : liste receuils: champs dernieres session + session réalisées filtrables - *13/11/2023* - 7.14.25 (backport)
- FIX : DA023972 dans le cas du formateur lors de l'envoi d'email de signature il n'y avait pas d'adresse mail - *08/11/2023* - 7.14.27
- FIX : warnings à la création de calendrier depuis une session - *31/10/2023* - 7.14.26
- FIX : compat php8 pour génération PDF Conseils pratiques - *31/10/2023* - 7.14.26
- FIX : filtre select_thirdparty oublié lors de la correction dans la 7.14.24 (dans session > edit subro) - *31/10/2023* - 7.14.26
- FIX : DA023972 - token signature - *10/10/2023* - 7.14.25
- FIX : modification des filtres passés à select_thirdparty_list pour la version 18.0 dolibarr  - *12/10/2023)* - 7.14.24  
- FIX : missing user message when no trainee or no compatible status in massgeneration - *12/09/2023* - 7.14.23
- FIX : fix fatal multicompany BIS - *07/06/2023* - 7.14.22
- FIX : trainer mode affichage lieu - *31/08/2023* - 7.14.21  
- FIX : if no multicompany => FATAL - *06/09/2023* - 7.14.20
- FIX : array_sum() expects parameter 1 to be array, null given - *25/08/2023* - 7.14.19
- FIX : recherche dans un extrafield de type varchar dans la liste des participants - *21/08/2023* - 7.14.18
- FIX : tms timestamp fields for mysql - *10/08/2023* - 7.14.17
- FIX : trainee merge extrafields - *04/08/2023* - 7.14.16
- FIX : modification date de génération pdf  - *08/08/2023* - 7.14.15  
- FIX : Const AGF_USE_PREV_CONVENTION_BY_SOC in bad entity - *03/08/2023* - 7.14.14
- FIX : Wrong entity - *17/07/2023* - 7.14.13
- FIX : Online sign securekey error - *10/07/2023* - 7.14.12
- FIX : Error: Bug into hook getNomUrl of module class ActionsAgefodd. Method must not return a string but an int - *03/07/2023* - 7.14.11
- FIX : ajout des contexts et redirections pour la prise en compte du nombre de session affecté aux differents elements (propal, facture , facfourn, cmd , cmdfourn , tiers)   - *04/07/2023* - 7.14.10  
- FIX : Retour fusion de stagiaire (historique de société) - *28/06/2023* - 7.14.9
- FIX : gestion de l'ent agenda sur envoi par mail document lié certificat de réalisation - *28/06/2023* - 7.14.8  
- FIX : actionComm ajout des fichiers envoyés en fin de message   - *27/06/2023* - 7.14.7
- FIX : Compatibility mass action attachment  - *20/06/2023* - 7.14.6
- FIX : Ajout evenement agenda sur envoi mail réalisation  - *22/06/2023* - 7.14.5
- FIX : MODIFICATION icone disquette  - *14/06/2023* - 7.14.5

- FIX : MODIFICATION icone disquette  - *14/06/2023* - 7.14.4  
- FIX : trads - *14/06/2023* - 7.14.3
- FIX : Fix Dolibarr V18 Compatibility - function htmlPrintOnlinePaymentFooter becomes htmlPrintOnlineFooter - *08/06/2023* - 7.14.2
- FIX : DA023459 Pb de quote postgre  - *01/06/2023* - 7.14.1
- NEW : Bouton fusionner participant  - *29/05/2023* - 7.14.0

## Version 7.13 - Released on *17/05/2023*

- FIX : Fix sql injection in FormAgefodd::select_formateur - *12/09/2023* - 7.13.7
- FIX : do not delete used contributors - *01/08/2023* - 7.13.6
- FIX : use Eldy's conf to call CMailFile - *01/08/2023* - 7.13.5
- FIX : sql error in module init - *04/06/2023* - 7.13.4
- FIX : Display program pdf creation date in the PDF - *26/06/2023* - 7.13.3
- FIX : Display program pdf creation date - *14/06/2023* - 7.13.2
- FIX : Stats visibility without rights - *07/06/2023* - 7.13.1
- NEW : Type de modèle d'email pour les signatures en ligne  - *16/05/2023* - 7.13.0

## Version 7.12 - Released on *21/04/2023*
- FIX : SOCIETE pdf affichage absent si session réalisée sinon vide  - *25/04/2023* - 7.12.1  
- NEW : Les signatures en ligne sont possibles pour les formateurs *30/03/2023* - 7.12.0

## Version 7.11 - Released on *24/02/2023*

- FIX : DA023766 USER_SIGNATURE éclatée à l'envoi de mail en masse - *07/09/2023* - 7.11.8
- FIX : Warning Invalid argument onglet session dans commande fournisseur - *14/04/2023* - 7.11.7
- FIX : delete on null  *29/03/2023* - 7.11.6
- FIX : Mysql update fail  *09/03/2023* - 7.11.5
- FIX : Ajout des extrafields sur les mails des sessions de formations dans les documents par stagiaire - *09/03/2023* - 7.11.4
- NEW : Ajout des extrafields sur les mails des sessions de formations - *03/03/2023* - 7.11.3
- NEW : Configuration AGF_GET_REF_SESSION_INSTEAD_OF_LABEL_WHEN_CREATE_PROPOSAL_FROM_SESSION rendu visible + troncature manuelle par défaut si conf cachée MAIN_DISABLE_TRUNC activée pour éviter bug base de données - *24/02/2023* - 7.11.1
- NEW : Ajout conf AGF_GET_REF_SESSION_INSTEAD_OF_LABEL_WHEN_CREATE_PROPOSAL_FROM_SESSION permettant lors de la création d'une propal depuis une session grâce au bouton "Créer la proposition commerciale
complète avec ajout d'une ligne avec le produit lié à la formation" de renseigner dans le champ référence de la propal la référence de la session au lieu de son libellé - *23/02/2023* - 7.11.0

## Version 7.10 - Released on *30/01/2023*
- FIX : DA023433 dans la liste des trainee, si on vient depuis une formation, on perd le filtre de la formation quand on change la limite d'éléments par page - *07/06/2023* - 7.10.18
- FIX : Ajout de la variable de substitution lieu pour les modèles de mail - *21/04/2023* - 7.10.17
- FIX : crenau en creneau - *17/03/2023* - 7.10.16
- FIX : Misplace complete head - *08/03/2023* - 7.10.15
- FIX : Missing trigger stagiaire deletion - *07/03/2023* - 7.10.14
- FIX : Ajout des pièces jointes dans les évènements agendas - *23/02/2023* - 7.10.13 
- FIX : Empêche l'ajout automatique d'une PJ sur les template d'email n'ayant pas l'option - *23/02/2023* - 7.10.12 
- FIX : redirection sur la session au moment de rajouter un stagiaire - *17/02/2023* - 7.10.11
- FIX : check if conf is enabled to show send sign email button - *17/02/2023* - 7.10.10
- FIX : Ajout du fk_soc dans le backurl de la création de site - *17/02/2023* - 7.10.9
- FIX : Affichage de la liste des stagiaires ayant reçu un mail *13/02/2023* - 7.10.8
- FIX : include main dolistore *31/01/2023* - 7.10.7
- FIX : Compat V17 *31/01/2023* - 7.10.6
- FIX : Changement de participants e nstagiaire à l'exeption de l'onglet participant *08/02/2023* - 7.10.5
- FIX : Creation evenement agenda lors de l'envoi d'email du cron du questionnaire à froid - *06/02/2023* - 7.10.4  
- NEW : Ajout des extrafields sur les mails des sessions de formations - *20/02/2023* - 7.10.4
- FIX : Triple saut de page, date format, double signature - *03/02/2023* - 7.10.3  
- FIX : Ajout des traduction pour les modèle envoi en masse  - *02/02/2023* - 7.10.2  
- FIX : Prise en charge du template d'email pour l'envoi en masse des documents par participants 'convocations' *31/01/2023* - 7.10.1
- NEW : Ajout d'un statut d'envoi du questionnaire de session - *27/01/2023* - 7.10.0
  Si besoin voici un requête à adapter pour passer les sessions à envoyer pour les 6 derniers mois  
  ```UPDATE llx_agefodd_session SET send_survey_status = 1 WHERE send_survey_status = 2 AND datef > DATE_SUB(now(), INTERVAL 6 MONTH)```
   
- NEW : Ajout d'une page public pour la signatures des créneaux - *06/01/2023* - 7.9.0
- FIX : Ajout condition pour Cron completionMailTrainee *13/01/2023* - 7.8.1
- NEW : Suppression des signatures au moment de la suppression du créneau - *09/12/2022* - 7.8.0

## Version 7.7 - Released on *02/12/2022*

- NEW : Signature des formateurs depuis le portail client - *30/11/2022* - 7.7.0
- NEW : API pour signature d'un créneau par un stagiaire ou formateur - *17/11/2022* - 7.6.0

## Version 7.5 - Released on *18/11/2022*
- FIX : ajout du td manquant sur colonne facturé sur la page de signature_page   - *25/11/2022* - 7.5.2
- FIX : Ajout du bouton retour sur la fiche de signature   - *25/11/2022* - 7.5.1  
- NEW : Signature des stagiaires depuis le portail client - *17/11/2022* - 7.5.0
- NEW : Ajout des signatures dans les feuilles d'émargement - *16/11/2022* - 7.4.0  
- NEW : Signature d'un créneau par un stagiaire ou un formateur via un canvas avec enregistrement de l'image - *14/11/2022* - 7.3.0
- NEW : Page de signature des formateurs/participants d'une session - *26/10/2022* - 7.2.0

## Version 7.1 - Released on *21/10/2022*

- FIX : Nom des contraintes sql trop longues - 17/05/2023 - 7.1.2
- FIX : Ajout de tokens manquant et paramètre manquant dans la fonction showOutputField pour affichage extrafields dans les listes - 10/11/2022 - 7.1.1
- Table des liens png pour les signatures (ajout conf "Gestion des signatures par créneau des stagiaires et formateurs") - 21/10/2022 - 7.1.0
- Fusion des onglets participants et formateurs - 21/10/2022 - 7.0.0

## Version 6.34 - Released on *20/10/2022*

- FIX : DA024012 : rapport détaillé par client, fichier rendu scrollable horizontalement (backport de 7.14.28) - *14/11/2023* - 6.34.29
- FIX : DA023880 generation convention pagebreak disposition financière refacto fonction generative  - *09/10/2023* - 6.34.28
- FIX : Modification section G du BPF - *01/08/2023* - 6.34.27
- FIX : Modification de  F-2 pour ne selectionner que les prestataires - *31/07/2023* - 6.34.26  
- FIX : suppression de la ligne c13 dans le pdf - *27/07/2023* - 6.34.25  
- FIX : suppression dans c10 de catégorie des particuliers - *27/07/2023* - 6.34.24  
- FIX : C-1 toutes catégories à l'exception de AGF_CAT_BPF_FAF - *27/07/2023)* - 6.34.23  
   - modification de la recupération des cat_bpf pour supporter le multi via get_conf dans _getAmountFin. 
   - mise en place dans constcust pour C-1 toutes catégories à l'exception de AGF_CAT_BPF_FAF
- FIX : DA023702 - Exclusion des sessions pour le compte de dans f1 f3 & f4  - *27/07/2023* - 6.34.22
- FIX : Indicateur F - 1 : a     - *27/07/2023* - 6.34.21
  - Les participants de type  :Financement par l'employeur, Financement par l''employeur (contrat pro.) opco contrat pro, Congés individuel de formation (CIF),Compte personnel de formation (CPF)  , Autre dispositifs (plan de formation, périodes de professionnalisation,...)
  - Suppression de  la prise en compte des stagiaires de type CPF dans cette mention. Compte personnel de formation (CPF) ⇒ il faut comptabiliser ce champ dans la case F-1 : e
- FIX : ajout de l'entité dans la generation des lien de téléchargement pour external access - *25/07/2023* - 6.34.20  
- FIX : Ajout du filtre stagiaire present ou partiellement present sur document lié Certificat de réalisation - *05/07/2023* - 6.34.19
- FIX : déselection du client de session quand on passe la session en inter - *23/05/2023* - 6.34.18
- FIX : Ajout d'une conf pour selectionner uniquement l'entité courante lors de la génération du rapport bpf - *10/05/2023* - 6.34.17  
- FIX : ajout socpeople information sur export participants - *19/04/2023* - 6.34.16  
- FIX : FIX convention generation conf handling - *17/04/2023* - 6.34.15
- FIX : Fix add commercial mobile phone - *04/04/2023* - 6.34.14
- FIX : Fix sql BPF DA023034 - *10/03/2023* - 6.34.13
- FIX : correction les documents financiers trop longs ne s'affichent pas dans la convention + les lignes sont dans le désordre... - *03/03/2023* - 6.34.12
- FIX : corrections diverses sur les tâches adminitratives - *23/02/2023* - 6.34.11
- FIX : Affichage du titre mentor seulement si au moins 1 mentor est selectionné dans l'administration du module  - *09/02/2023* - 6.34.10  
- FIX : passage de la société en paramètre lors de l'action du bouton saveAndStay - *27/01/2023* - 6.34.9  
- FIX : token on delete img signature in admin  - *06/01/2023* - 6.34.8  
- FIX : Viewing form to add session linked file must check same right than action - *23/12/2022* - 6.34.7
- FIX : Cronjob fetch all undefined *29/11/2022* - 6.34.6
- FIX : AJOUT MESSAGE PERSONNALISÉ SUR SESSION EN INTRA - *25/11/2022* - 6.34.5  
- FIX : test Array preventing error - *23/11/2022)* - 6.34.4  
- FIX : Ajout parma dans sessionstats.class.php - *23/11/2022)* - 6.34.3  
- Recup du FIX 7.1 de kev : Ajout de tokens manquant et paramètre manquant dans la fonction showOutputField pour affichage extrafields dans les listes - 10/11/2022 - 6.34.2
- FIX : AFGEQUIPMENT MISSING  - *21/10/2022* - 6.34.1  
- NEW : Les extrafields des formations sont maintenant répercutés sur le formulaire de création d'une session
	+ retrait du système ajax pour remplissage auto des champs durée, nature action, nb places et produit (maintenant fait via rechargement de page) - *13/10/2022* - 6.34.0

## Version 6.33 - Released on *08/09/2022
- FIX : fetch all undefined *18/10/2022* - 6.33.6
- FIX : Changement des prefixes Module formation  par Module formation - participant  - *12/10/2022* - 6.33.5  
- FIX : Ajout d'un modèle de mail certificat de réalisation Participant et préselection de celui-ci dans envoi de document par mail ( réalisation ) - *12/10/2022* - 6.33.4  
- FIX : Ajout des tokens manquant dans l'action de suppression des documents dans l'onglet Documents liés - *13/10/2022* - 6.33.4
- FIX : Ajout du lien de la fiche pedagogique originale ou copiée sur la ligne convention de formation dans la liste par tiers des documents liés - *11/10/2022* - 6.33.3  
- FIX : changement de traduction de la civilité dans fiche_pedago - *10/10/2022* - 6.33.2  
- FIX : Ajout Équipement necessaire sur la fiche pédago - *10/10/2022* - 6.33.1   
- NEW : Ajout d'un modèle de certificat de réalisation utilisable dans l'onglet Documents liés - *30/08/2022* 6.33
- NEW : Cron exécuté tous les mois pour l'envoi de mails aux participants ayant terminés une formation dans les "X" mois - *25/08/2022* 6.32

## Version 6.31 - Released on *08/08/2022*
- FIX : DA023101 portail formateur multientité - *16/03/2023* - 6.31.7
- FIX : DA022760 BPF ne filtrer que sur les dates  - *13/01/2023* - 6.31.4
- FIX : Ajout du parametre useLocalBrowser dans doleditor sur la page training/card.php - *10/01/2023* - 6.31.5  
- FIX : Blocage de l'intégration de facture n'existant pas - *23/11/2022* - 6.31.4
- FIX : le clonage de session en erreur n'affichait pas l'erreur - *05/10/2022* - 6.31.3
- FIX : Recalcul du cout de mission sur la card session car la champs n'est pas toujours mis à jour comme on voudrait - *26/09/2022* - 6.31.2
- FIX : Fix test sur le fk_soc d'une session inter - *19/08/2022* - 6.31.1
- NEW : Ajout du hook pour usernavhistory - *02/08/2022* - 6.31.0

## Version 6.30 - Released on *13/07/2022*

- FIX : Undefined fetch_all() *05/10/2022* - 6.30.15
- FIX : Gestion des champs Clients et contacts client sur session intra/inter - *19/08/2022* - 6.30.14
- FIX : pdf_certificate_completion_trainee: ratio d'aspect du logo + couleurs - *12/08/2022* - 6.30.13
- FIX : Correction du fix précédent concernant pdf_certificate_completion_trainee *12/08/2022* - 6.30.12
- FIX : action de formation définie par défaut lors de la création d'une nouvelle formation *09/08/2022* - 6.30.11
- FIX : Remplacement de l'entête de pdf_certificate_completion_trainee *08/08/2022* - 6.30.10
- FIX : Correctif DA022127 - annexe de la convention de formation : ne tenait pas compte de l'existence d'un PDF du programme pour la session et prenait systématiquement celui de la formation catalogue - *03/08/2022* - 6.30.9
- FIX : Ajout d'une règle pour le champ type de la formation(intra/inter), dans session permettant d'obliger l'ajout d'un client ou non - *04/08/2022* - 6.30.8
- FIX : Editor name - *03/08/2022* - 6.30.7
- FIX : Ajout de la civilité dans les modèles de documents des participants - *27/07/2022* - 6.30.6
- FIX : Gestion de l'export pour prendre en compte le changement de societé d'un participant d'une session - *27/07/2022* - 6.30.5
- FIX : Correctif ticket DA022127: mauvaise durée de session sur les pdf générés - *26/07/2022* - 6.30.4
- FIX : change getEntity parameters on archive_documents_session *15/07/2022* - 6.30.3
- FIX : Dolistore zip  - *13/07/2022* - 6.30.2
- FIX : Compatibiilty V16 - Remove doUpgrade2 hook unised and renamed in V16 - rename famlily and update tokens with retrocompatibility - *13/07/2022* - 6.30.1
- NEW : Added a new page for archiving session documents - *11/07/2022* - 6.30.0

## Version 6.29 - Released on *17/06/2022*
- FIX : CHERRY-PICK  https://gitlab.atm-consulting.fr/dolistore/store-agefodd/-/merge_requests/943 - *12/05/2023* - 6.29.5  
- FIX : fix clone on training card - *16/09/2022* - 6.29.4
- FIX : add missed call addExtrafield on init function module - *11/07/2022* - 6.29.3  
- FIX : add_referent_users.php does not comply with DoliStore ✌good practices✌ regarding inclusion of main.inc.php - *29/06/2022* - 6.29.2
- FIX : fix jquery bug on file upload site and trainee - *24/06/2022)* - 6.29.1 
- NEW: Écran intermédiaire lors de l'action de masse "mail aux participants" - *25/05/2022* - 6.29.0

## Version 6.28 - Released on *19/05/2022*

- FIX : Compatibility token *19/07/2022* - 6.28.4
- FIX : Compatibility v16  replace MAIN_DB_PREFIX tabname *14/06/2022* - 6.28.3
- FIX : Add menu link to the check integrity page - *10/06/2022* - 6.28.2
- FIX : Fix module hook conflicts for global search - *01/06/2022* - 6.28.1
- NEW : Ajout de barre de progression sur la liste & card des sessions de formation *19/05/2022* 6.28.0
- NEW : Ajout d'un onglet dans recueil de formation affichants les participants des sessions liées *11/05/2022* - 6.27.0
- NEW : Ajout de la class TechATM pour l'affichage de la page "A propos" *11/05/2022* 6.26.0

## Version 6.25 - Released on *31/03/2022*
- FIX : Multicompany shares required a database migration after all + some warnings fixed + add script to share user group- *13/06/2022* - 6.25.16
- FIX : Fix share conf between entities - *10/06/2022* - 6.25.15
- FIX : Fix don't calculate traineestatus with realhours in the futur - *07/06/2022* - 6.25.14
- FIX : Fix trainee status to avoid + fix costBySoc - *16/05/2022* - 6.25.13
- FIX : Change sql ON clause in fetch_societe_per_session to get sessionTrainee soc instead of current soc - *13/05/2022* - 6.25.12
- FIX : Multi Module Hook compatibility - *06/05/2022)* - 6.25.11
- FIX : Mise à jour de traductions & Manuel d'utilisation BPF *26/04/2022* - 6.25.10
- FIX : Selection des champs de la liste des sessions n'était pas prise en compte *15/04/2022* - 6.25.9
- FIX : Ajout de boxes manquantes dans les widgets en page d'acceuil *06/04/2022* - 6.25.8
- FIX : Possibilité de créer des horaires de session 24/24 et non plus de 5h à 23 comme avant (pour les clients qu'on des session nocturnes) - *05/04/2022* - 6.25.7
- FIX : Session search list fail for boolean extrafields *31/03/2022* - 6.25.6
- FIX : Mise à jour du fichier agefodd.lang *29/03/2022* - 6.25.5
- FIX : pdf_conseils, la ligne (Repas, hébergement...) prend un retour à la ligne *29/03/2022* - 6.25.4
- FIX : Affichage des champs manquant ou dupliqué dans une fiche recueil en edit et création *28/03/2022* 6.25.3
- FIX : Redirection sur le formulaire de création de participant, bouton "Enregistrer et rester" *27/03/2022* 6.25.2
- FIX : Sql update file *31/03/2022* - 6.25.1
- NEW : Ajout d'un nouveau modèle de mail pour les Certificat de réalisation *23/03/2022* - 6.25.0

## Version 6.24 - Released on *11/03/2022*

- FIX : Compatibilité avec le module attachments, il est maintenant possible lors d'un envoi de mail depuis l'onglet "Documents liés", de sélectionner tous les PDF générés sur cet onglet - *04/11/2022* - 6.24.12
- FIX : DA021667 la valeur 0 est vidée et la valeur théorique est de nouveau affiché, si on met 0 on veut garder la valeur 0 - *06/09/2022* - 6.24.11
- FIX : Soucis de parenthèse dans la requete SQL - *20/07/2022* - 6.24.10
- FIX : DA022091 : Conflit de conf "Utiliser le temps de présence réel des stagiaires" && "Les statuts d'inscription des participants sont calculés automatiquement" - *22/06/2022* - 6.24.9
  Lorsque l’on souhaite passer le candidat du statut « prospect » à « confirmé » ou même « accord verbal », le statut bascule systématiquement en mode non présent.
- FIX : Modèle PDF "Attestation de fin de formation": pagination si les objectifs pédagogiques ne tiennent pas sur une page - *17/06/2022* - 6.24.8
- FIX : Si un stagiaire d'une entreprise a un OPCO, les autres stagiaires de la
  même entreprise seront listés sous cet OPCO dans la liste des documents même
  s'ils n'ont pas d'OPCO - *03/06/2022* - 6.24.7
- FIX : Pagination du PDF d'attestation de formation : objectifs
  pédagogiques sur page suivante si trop longs - *06/05/2022* - 6.24.6
- FIX : Ajout de deux confs permettant de gérer la création d'une facture depuis une session et affichage de l'onglet planning par participants *27/04/2022* - 6.24.5
- FIX : selection du status Non présent si pas d'heure - *08/04/2022)* - 6.24.4  
- FIX : add form  param to selectPropectCustomerType - *07/04/2022)* - 6.24.3  
- FIX : redirection pour "enregistrer et rester" lors de la création+inscription d'un stagiaire dans une session - *06/04/2022* - 6.24.2
- FIX : recuperation des propales liees a un tiers pour liaison a la session courante (en V14.0 le champs total n'existe plus) - *22/03/2022* - 6.24.1
- NEW : Ajout de la prise en compte du module attachments dans les formulaires de mail d'agefodd - *09/03/2022* - 6.24.0
    - Ajoute la possibilité de joindre les fichiers joints venant de la session, des formateurs de la session, des stagiaires, du lieu de session et formation du recueil   
- NEW : Ajout de la possibilité de téléverser un pdf de "Réglement intérieur" pour les lieux - *07/03/2022* - 6.23.0
    - l'onglet "règlement intérieur" des lieux est caché mais peut être affiché grâce à la conf AGF_DISPLAY_REG_INT_TAB
    - modification de la configuration de fusion des pdf Convocation et conseils pratique pour y ajouter le règlement intérieur du lieux de la session
    - ajout du règlement intérieur en fichier joint à l'envoi de mail
    - ajout dans "document liés" d'une ligne de document permettant de télécharger, prévisualiser et envoyer le pdf par mail
- NEW : Fait marcher les BPF avec la notion de multicompany - *01/03/2022* - 6.22.0

## Version 6.21 - Released on *07/12/2022*

- FIX : fix mauvais chemin de génération des fichiers de rapports - *16/03/2022* - 6.21.2
- FIX : ne pas afficher le nombre total de participants sur les factures de session - *22/02/2022* - 6.21.1
- NEW : Mandatory file on administrative tasks -  - *07/012/2022* - 6.21.0

## Version 6.20 - Released on *19/01/2022*

- FIX : déplacement du test d'erreur sur les exécutions de script 
  quand le rôle update n'était pas présent pour une table, le retour était systématiquement en erreur - *20/01/2022* - 6.20.2
- FIX  : test Isset fk_parent_level when editing element - *20/01/2022* - 6.20.1
- FIX : Missing sql file to create llx_c_formation_nature_action on update - *25/01/2022* - 6.20.1
- NEW : Add good icons for Module - *18/01/2022* - 6.20.0
- FIX : Backport fix 6.19 : data integrity and remove cascading delete for foreign key llx_agefodd_formation_catalogue_ibfk_1 - *12/01/2020* - 6.19.0
  Retrait de la suppression en cascade en base pour la table llx_agefodd_session sur suppression d'un item de llx_agefodd_formation_catalogue
  Il n'est plus possible de supprimer un recueil de formation si des sessions existent pour ce recueil.

## Version 6.18 - Released on *26/11/2021*
- FIX : FIX filter on product - *15/06/2023* - 6.18.22
- FIX : Compat v14 - *16/02/2023* - 6.18.21
- FIX : SQL query reassigned by mistake instead of concatenation (`=` instead of `.=`) - *07/07/2022* - 6.18.20
- FIX : various problems with BPF queries (section F) - *08/06/2022* - 6.18.19
- FIX : clone session fails to clone trainees despite relevant checkbox being checked *22/02/2022* - 6.18.18
- FIX : missing translation *22/02/2022* - 6.18.17
- FIX : filter by trainee status only for after training documents *15/02/2022* - 6.18.16
- FIX : Regression suite refonte formulaire, retour du bouton "nouveau participant" *04/02/2022* - 6.18.15
- FIX : fix call to Agefodd_session_stagiaire::fetch_stagiaire_per_session - *01/02/2022* - 6.18.14
- FIX : Missing redirection to session/subscribers.php after add new trainee - *25/01/2020* - 6.18.13
- FIX : add filter on the presence or the partial presence of a trainee during mass generation   - *25/01/2020* - 6.18.12
- FIX : Missing convention var for signature - *19/01/2020* - 6.18.11
- FIX : save nature action fields from the catalog for the save_confirm action on a session - *18/01/2020* - 6.18.10
- FIX : prevent overlapping of place field with date field if there are no obj peda - *18/01/2020* - 6.18.9
- FIX : the footer of the convention pdf page which was grayed out grayed out all the following pages V2 - *18/01/2020* - 6.18.8
- FIX : the footer of the convention pdf page which was grayed out grayed out all the following pages - *12/01/2020* - 6.18.7
- FIX : accesibility handicap bool to int on postgres - *20/12/2021* - 6.18.6
  ERROR:  column "accessibility_handicap" is of type boolean but expression is of type integer at character 2009
- FIX : Compatibility with external access 1.31 - *20/12/2021* - 6.18.5
- FIX : Compatibility V15 : ActionComm::getActions($db, ...) is not static anymore - *17/12/2021* - 6.18.4 
- FIX : PostgreSQL compatibility, remove display width from create table llx_c_formation_nature_action - *14/12/2021* - 6.18.3
- FIX : From 4.4 to 6.18 littles fixes.. - *06/12/2021* - 6.18.2 
- FIX : handle non-secable blocks on PDF for training agreement (convention de formation) *2021-11-23* - 6.18.1
- FIX : Installation system for sql files *19/11/2021* - 6.18.0  
  Voir procedure d'ajout de table dans le fichier  [README.md](sql/README.md)

## Version 6.17 - Released on *29/10/2021*
- FIX : the current month's filter was reapplied when navigating to another page and when exporting listincsv - *12/01/2022* - 6.17.5
- FIX : duree and duree_session used with GETPOST int or GETPOST intcomma if empty - *05/01/2022* - 6.17.4
- FIX : Installation error missing tables *19/11/2021* - 6.17.3
- FIX : traduction export cumul hours participant *27/10/2021* - 6.17.2
- FIX : optimize multicompany handling *27/10/2021* - 6.17.1 (OpenDSI)
- NEW : PDF generation of trainings from catalog now handle trainings overloaded using
        sessionCatalogue *08/10/2021* - 6.17.0
- NEW : Creation of sessionCatalogue object, clone of formation catalogue +
        Add ability to modify informations of the training on sessions - *29/09/2021* - 6.17.0
- NEW add participant status for a session
  - sum of hours spent for a session by a participant *22/10/2021* - 6.17.0

# Version 6.16 - Released on *01/10/2021*
- FIX : remove duplicate display on edit_subrogation - *09/02/2022* - 6.16.2
- FIX: remove some legacy code – *23/10/2021* - 6.16.1
- NEW uniformize order lines with propal lines in convention PDF - *20/09/2021* - 6.16.0 (OpenDSI)
- NEW : Better layout of elements on session card & tab on lib - *20/09/2021* - 6.15.0  
   and remove useless buttons session card  :  
  - Modify subcribers and subrogation
  - Modify calendar
  - Modify Trainer

- NEW : UI modification & edit trainee in view replace img save button by dolibar friendly save button - *20/09/2021* - 6.14.0

# Version 6.12 - Released on *17/09/2021*
- FIX : error if fk_pays of agefodd place is null, we should not be blocked *20/05/2022* - 6.12.13
- FIX : Need to set a value to $user->rights->agefodd_agsession->read to work with new test $user->hasRight($modulecodetouseforpermissioncheck, 'read') on /comm/action/card.php - *31/03/2022* - 6.12.12
- FIX : API return id trainer calendar period after add and same for calendar period - *21/02/2022* - 6.12.11
- FIX : Refonte graphique des listes de l'onglet planning par participant d'une session;  - *16-11-2021* - 6.12.10 
        Affichage feuilles d'émargement pdf, taille des cases de signature;
        Affichage erroné du statut d'une facture sur l'onglet document par participant d'une session de formation
- FIX : On trainee in session deletion, delete the hours for this trainee in the session *27-10-2021* - 6.12.9
- FIX : Add legends to agenda for trainers *21-10-2021* - 6.12.8
- FIX : Change the notes of the line hour et the notes of the misc line *22-10-2021* - 6.12.7
- FIX : sql entities management *22/10/2021* 6.12.6
- FIX  : unavailability at the right place for trainer *15/10/2021* 6.12.5
- FIX : If a trainee has a status excused or canceled, inputs are darkened on exertal acess -> time slot edition - *12/10/2021* - 6.12.4
- FIX : Add missing fields in API update period method - *27/09/2021* - 6.12.3
- FIX : Modification du placement du "Lieu" dans les pdf_attestationendtraining pour que celui ci soit lisible peu importe le cas - *2021-09-23* - 6.12.2
- FIX : export commercial on session *17/09/2021* 6.12.1  
  ajout du nom et du prénom du commercial lié à la session dans l'export std dolibarr des sessions 
- NEW : Add comment on trainee session - *16/09/2021* - 6.12.0
- NEW : New liste agenda ajax history - 6.11.0  
  refonte visuelle de la liste agenda. un appel ajax load desormais la liste standard dolibarr avec les filtres correspondant à la session
- NEW : Triggers on session element object - *16/09/2021* - 6.11.0
- NEW : change format date liste session - 6.10.0  
  changement du format date dans les listes des sessions 
- NEW : change acces to real in same day + trad button update to save - 6.10.0  
  la possibilité de modifier un creneau lorsque l'on est à j-0 et changement de traduction du bouton modifier vers enregistrer 
- NEW : double validation on  card time slot - 6.10.0  
  les formulaires de l'onglet creneau ne sont plus dissossiés, le click sur le bouton enregistrer sauvegarde l'ensemble de la page.

# Version 6.9 - Released on *12/08/2021*

- FIX : Add missing fields in API update period method - *27/09/2021* - 6.9.8
- FIX : filter month and year doesn't work - *17/09/2021* - 6.9.7
- FIX : certificat trad - *03/09/2021* - 6.9.6
- FIX : empty value of the sales representative selector is now -1 instead of ''; all lists/reports with filter on sales
        representative required fixing - *08/09/2021* - 6.9.5
- FIX : default value for $this->fk_user_author in create function of agefodd_session_calendrier.class.php - *15/09/2021* - 6.9.4
- FIX : generate_all in Session - *18/08/2021* - 6.9.3
- FIX : transnoentities in PDF - *17/08/2021* - 6.9.2
- FIX : translation key conflict - *16/08/2021* - 6.9.1
- NEW : add fk_user_author, tms and datec to API agefodd (calendar) get and post | fix global filter on ref session, id session and trainee list - *10/08/2021* - 6.9.0

# Version 6.8 - Released on *06/08/2021*

- FIX : mass generation of certificates of completion - 11/08/2021 - 6.8.3
- FIX : gives the same behavior for users as for contacts (external access suppliar invoice) - *11/08/2021* - 6.8.2
- FIX : adapt size of the tampon signature - *06/08/2021* - 6.8.1
- NEW : Create new PDF model: certification de réalisation (officiel France) - *03/08/2021* - 6.8.0
- NEW : separate supplier invoices from sessions - *08/27/2021* - 6.7.0
- NEW : add "note" field on products/services lignes on a bill (external access) - *27/07/2021* - 6.7.0
- FIX : behaviour changes of js pluggin for traineelist conf between dol9.0 and dol13.0 - *27/07/2021* - 6.6.1
- FIX : broken tabs and table overflow in EA traineelist - *15/07/2021* - 6.6.1
- NEW : traineelist table in EA is a dataTable - *15/07/2021* - 6.6.1


## Version 6.6

- NEW : list of session trainee in external access + conf to select fields and order of columns - *2021-06-30* - 6.6.0
- NEW : Nouveau champs "commercial en charge" sur une session *02/07/2021* - 6.5.1
- NEW : NEW trigger on session trainee update - OpenDsi - *23/06/2021* - 6.5.0
- FIX : Email format on document trainee before sending by mail - OpenDsi - *23/06/2021* - 6.4.4
- NEW : add missing hooks *2021-06-21* - 6.4.3
- FIX : it is now possible to filter the session list by a single date (start / end) *2021-06-21* - 6.4.2
- FIX : compat v13/14 NOCSRFCHECK & NOTOKENRENEWAL sur interface.php *2021-06-18* - 6.4.1
- NEW : add nature action field  and autofill. *2021-06-18* - 6.4.0
- FIX : Missing hours and type column are diplayed. *2021-06-18* - 6.3.2
- FIX : API for session list returned error if there were no sessions. It now returns an empty set. *2021-06-16* - 6.3.1
- NEW : Duration on training is now optional if AGF_OPTIONNAL_TRAINING_DURATION is activated *2021-06-09* - 6.3
- NEW : TK2003-0577 - Qualiopi Accessibilité handicap ajout d'un champ sur formation case à cochée *2021-06-4* - 6.1

- NEW : ajout 3 champs (liste déroulante d'utilisateurs) dans la config (admin)  *2021-06-01* - target -> 6.0
  - Référent Administratif
  - Référent Pédagogique
  - Référent Handicap

- FIX : Requete trop longue + totaux faux
- FIX : FIX Doublon sur liste - *2021-06-01* - 4.9.23
- FIX : Sending mail to trainer is broken for in-house trainers - *2021-05-20* - 4.8.27
- FIX : Calendar color for ongoing session (orange) - *2021-05-20* - 5.1.12

---------------------------------

## Version 5.2 - Préremplissage d'une convention par société
- FIX : VAT number make difference for printing when multicompany is used if the entity VAT Number is present or not - *10-02-2022* - 5.2.17
- FIX : warning on agenda index.php  *2022-02-10* - 5.1.16
- FIX : Backport fix 6.18.7 : data integrity and remove cascading delete for foreign key llx_agefodd_formation_catalogue_ibfk_1 - *12/01/2020* - 5.2.15 
  Retrait de la suppression en cascade en base pour la table llx_agefodd_session sur suppression d'un item de llx_agefodd_formation_catalogue
  Il n'est plus possible de supprimer un recueil de formation si des sessions existent pour ce recueil.
- FIX : Sql error when we search on session/list_soc.php - *30/11/2021* - 5.2.14
- FIX : fix getpost for intitule_custo of sessions *2021-11-10* - 5.2.13
- FIX : Fonction "remove" agsession : suppression des liens de cette session dans la table "agefodd_session_element" - *2021-10-27* - 5.2.12
- FIX : input type for external access doesn't work on each browser, then we use input type text with defined pattern - *2021-10-11* - 5.2.11
- FIX : Dolibarr retrocompatibilty V9 - *2021-09-29* - 5.2.10
- FIX : Empty info on contact training tab  - *2021-09-29* - 5.2.9
- FIX : Modification du placement du "Lieu" dans les pdf_attestationendtraining pour que celui ci soit lisible peu importe le cas - *2021-09-23* - 5.2.8
- FIX : Les marge n'apparaissent pas sur certaines pages lorsque la description d'un ou des produits est trop longue - *2021-07-06* - 5.2.7
- FIX : Add conf 'AGF_CERTIF_ALERT_DATE_NB_MONTHS' to change certificate alert date *25/06/2021* 5.2.6
- FIX : Modify sql query syntax on CheckDataIntegrity screen to make it work with Postgresql *23/06/2021* 5.2.5
- FIX : Modify sql query syntax to match with Postgresql *23/06/2021* 5.2.4
- FIX : ADD unavailability on agenda *03/06/2021* 5.2.3
- FIX : AgefoddTitleAndCOdeInt splited on 2 lines for preventing overlaping lines on crabe invoice pdf *01/06/2021* 5.2.2
- FIX : TCPDF_PARSER_ERROR when concatenating PDF files with Agefodd - *2021-05-07* - 4.8.26
- FIX : Current timeslot deduction in edit mode (#DA020229) - *2021-04-19* - 4.9.15
- NEW : Conf pour utiliser le préremplissage d'une convention par société - *2021-05-17* - 5.2.0

---------------------------------

## Version 5.1 - Ajout Compatibilité V13 de Dolibarr

- FIX : Cost display on session card and on thirdparty view with "trainee" filter - *2021-09-27* - 5.1.16
- FIX : Calendar color for ongoing session (orange) - *2021-05-20* - 5.1.12
- FIX : Cost per trainee calcul soc list *23/06/2021* - 5.1.15
- FIX : SQL error if we add calendar entry from date to date and we don't set debut hour or end hour *2021-04-20* 5.1.6
- FIX : parent level not set when we create new administrative task *2021-04-19* - 5.1.5

- FIX : Session status quick edit *19/04/2021* - 5.1.5

- FIX : Compatibility V13 : Box compatibility *06/04/2021* - 5.1.2
- FIX : conf AGF_ALWAYS_USE_DEFAULT_CONVENTION pour que la dernière convention d'un tiers ne soit pas systématiquement chargée lors de la création d'une nouvelle *2021-04-01* - 5.1.3
- FIX : MySQL version compatibility : tms have no default value for
  - class agefodd_formation_catalogue
  - class agefodd_training_admlevel
- FIX : Compatibility V13 : Replace contactid to contact_id *17/03/2021* - 5.0.8
- FIX : Compatibility V13 : Replace SIGNATURE to USER_SIGNATURE *17/03/2021* - 5.0.7
- FIX : Compatibility V13 : Add token parameter to action add/delete/update *17/03/2021* - 5.0.6

## Version 5.0

- BACKPORT : DA022138 - https://gitlab.atm-consulting.fr/dolistore/store-agefodd/-/merge_requests/536 - *07/07/2022* - 5.0.24
- FIX : show_trainer_mission default modele now autogenerate if tagged as default    - *23/06/2022* - 5.0.23  
- FIX : add join clause in BPF with real hours ON F1-d - *20/05/2022* - 5.0.22
- FIX : Postgresql compatibility - *07/09/2021* - 5.0.21
- FIX : Sorting the "certificat (carte de credit)" drop-down list alphabetically in the linked documents of a session *25/06/2021* 5.0.20
- FIX : Add conf 'AGF_CERTIF_ALERT_DATE_NB_MONTHS' to change certificate alert date *25/06/2021* 5.0.19
- FIX : SQL error if we add calendar entry from date to date and we don't set debut hour or end hour *2021-04-20* 5.0.10
- FIX : parent level not set when we create new administrative task *2021-04-19* - 5.0.9
- FIX : conf AGF_ALWAYS_USE_DEFAULT_CONVENTION pour que la dernière convention d'un tiers ne soit pas systématiquement chargée lors de la création d'une nouvelle *2021-04-01* - 5.0.8
- FIX : same tiers historized even if no soc change *26/06/2021* - 5.0.5
  historize function from agefodd_stagiaire_soc_history.php
         test on soc change

- WARNING : The changes may create regressions for external modules with
  classes that extend `pdf_fiche_presence.modules.php`
- FIX tickets #11916, #11888, #11861 and #12049 : *24/12/2021*
  PDF templates for attendance sheets had wrong page break rules leading to orphans/widows
  and, in some cases, successions of pages containing one single cell
  overlapping the page break threshold


## Version 4.12
- FIX : fix bpf help screen *2023-03-06* - 4.12.25
- FIX : training extrafields import handle *2021-12-03* - 4.12.24
- FIX : green color hidden conf *2021-05-07* - 4.9.12
- FIX : BPF doublon *2021-05-04* - 4.9.11
- FIX : Avoid call to fetch_all method with limit set to 1 000 000 in case of MAIN_DISABLE_FULL_SCANLIST activated [2020-04-08]
- FIX : conf AGF_ALWAYS_USE_DEFAULT_CONVENTION pour que la dernière convention d'un tiers ne soit pas systématiquement chargée lors de la création d'une nouvelle [2021-04-01]
- FIX : Multicell html in PDF [2020-12-18]
- FIX : Dol V13 compatibility [2020-12-09]
- FIX : Fix d'un bug d’affichage de bordure sur le bloc société dans la génération PDF de certaines feuilles d’émargement [2021-03-04]
- FIX : parent level not set when we create new administrative task [2021-04-19]

___
## OLDER

***** ChangeLog for 4.11 *****
FIX : Les totaux du rapport commercial ne sont pas en bas des bonnes colonnes *20/09/2022* - 4.11.23
FIX : La fonction convertBackOfficeMediasLinksToPublicLinks n'existe pas avant Dolibarr 10.0 - *24/06/2022* - 4.11.22
FIX : La fonction de l'objet form s'appelle selectDate seulement à partir de la 9.0 - *05/05/2022* - 4.11.21
FIX : Quand on ajoute un nouveau participant et qu'on clique sur "Enregistrer et rester" les champs doivent être vidés - *03/05/2022* - 4.11.20
FIX : Seules les sessions pour lesquelles toutes les tâches administratives sont terminées doivent être vertes - *03/05/2022* - 4.11.19

***** ChangeLog for 4.9 *****
- FIX : Requete trop longue + totaux faux
- FIX : FIX Doublon sur liste - *2021-06-01* - 4.9.23

***** ChangeLog for 4.8.26 and above *****
- FIX : session list no longer filterable unless filtered by sales representative - 2021-08-10* - 4.8.28
- FIX : Sending mail to trainer is broken for in-house trainers - *2021-05-20* - 4.8.27
- FIX : TCPDF_PARSER_ERROR when concatenating PDF files with Agefodd - *2021-05-07* - 4.8.26
- FIX : Current timeslot deduction in edit mode (#DA020229) - *2021-04-19* - 4.9.15

***** ChangeLog for 4.8.26 compared to 4.8.25 *****
FIX : pagination fonctionnelle sur liste des sessions

***** ChangeLog for 4.8.25 compared to 4.8.24 *****
FIX : green color hidden conf

***** ChangeLog for 4.9.18 compared to 4.9.17 *****
FIX : BPF doublon *2021-05-04* - 4.9.18

***** ChangeLog for 4.11.10 compared to 4.11.9 *****
FIX : parent level not set when we create new administrative task

***** ChangeLog for 4.9.12 compared to 4.9.11 *****
FIX : Text formating for sites access instructions without wysiwyg
FIX : HTML 5 form validation for module external access view

***** ChangeLog for 4.8.24 compared to 4.8.23 *****
FIX : ignore administrative tasks from archived sessions in the agefodd home page counters

***** ChangeLog for 4.8.23 compared to 4.8.22 *****
FIX : parent level not set when we create new administrative task

***** ChangeLog for 4.4.11 compared to 4.4.12 *****
FIX : create invoice from session -> keep product type value for invoice line


***** ChangeLog for 3.0.18 compared to 3.0.17 *****
NEW : Can add image on location

***** ChangeLog for 3.0.17 compared to 3.0.16 *****
NEW : Display trainer type on trainer list
NEW : Option to Group session per day on session calendar
NEW : Add filter on session calendar on session status
NEW : Add option to warning or block if trainer is already book on another session

***** ChangeLog for 3.0.16 compared to 3.0.15 *****
NEW : Allow convention without financial document linked in module setup

***** ChangeLog for 3.0.15 compared to 3.0.14 *****
FIX : Various fix
NEW : new column for document models

***** ChangeLog for 3.0.14 compared to 3.0.13 *****
FIX : Global review for PgSQL

***** ChangeLog for 3.0.13 compared to 3.0.12 *****
NEW : Extrafields on trainee

***** ChangeLog for 3.0.12 compared to 3.0.11 *****
NEW : Joined Files on Trainne card
NEW : Compatible on Dolibarr 6.x
NEW : if cost management is disabled, no cost automatic update on session finacial document linked

***** ChangeLog for 3.0.11 compared to 3.0.10 *****
NEW : Add new right to acces module admin even if user is not dolibarr administrator

***** ChangeLog for 3.0.10 compared to 3.0.9 *****
NEW : Add chapter into convention

***** ChangeLog for 3.0.9 compared to 3.0.8 *****
NEW : Add admin option to include program into convention
NEW : Add admin option to include signature image into convocation
NEW : Add email tempates management with dictionnary

***** ChangeLog for 3.0.6 compared to 3.0.7 *****
NEW : Franch BPF 2017 available 
FIX : compatiblity for 5.0

***** ChangeLog for 3.0.5 compared to 3.0.6 *****
NEW : Better trainee category for french BPF 

***** ChangeLog for 3.0.4 compared to 3.0.5 *****
NEW : Certificate card format PDF
NEW : Add field on training that craate an QR code on credit card 

***** ChangeLog for 3.0.3 compared to 3.0.4 *****
NEW : Create event on trainer document send
NEW : Can send End training attestation into send doc  
FIX : Product cost management

***** ChangeLog for 3.0.2 compared to 3.0.3 *****
NEW : Add field on session for sous traitance
FIX : Allow to remove thirdparty and contact on session (if combox use)


***** ChangeLog for 3.0.1 compared to 3.0.2 *****
NEW : Add trainer attachement file tab
NEW : Add place attachement file tab
FIX : PDF, always use session duration instead of training programm duration
FIX : PDF, Always check Aquired on session objectifs on attestation end training 
NEW : PDF,  add chevalet documents


***** ChangeLog for 3.0.0 compared to 3.0.1 *****
FIX : Do not output chapter if empty into training program
NEW : Import/Export training program


***** ChangeLog for 2.1.16 compared to 3.0.0 *****
New : Add option to do not auto generate propal ref
Fix/New : Manage convention signataire in a better way for inter entreprise
New : Calendar tab to mamange separatly session calendar
New : is session salemans is empty, it will be affected automaticly to first customer saleman
New : Add pages numbers on all PDF documents
Fix : French translation
Fix : Add training program to proposal work not correctly
Fix : Issue #16
Fix : Issue #15
Fix : Issue #14
Fix : Issue #18
Fix : Issue #23
Fix : Issue #12
New : Can upload files on training (optionnaly to replace training program)
Fix : Invoice created from Document linked was not associated to Session
New : Add BPF report
New : Add {breakpage} tag into traiing program to mange manually break page on long program training
New : Add place control use in the same dates only for controled location
New : Only for Dolibarr 4.0.x version minimum
New : Better calendar session deletion management

***** ChangeLog for 2.1.15 compared to 2.1.14 *****
Fix : Fix training colors
Fix : Fix bug on new time session
Fix : Revert to php 5.4 compatiblity
Fix : Trainee status in session must not be auto updated if option is OFF on propal closure or re-opening
New : Export session with extrafields catalogue and session
New : Add place control use in the same dates 
New : Easier trainer calendar deletion (checkbox)

***** ChangeLog for 2.1.14 compared to 2.1.13 *****
-Fix : Fix logo on PDF
-Fix : Add option to output only product line on convention
-New : Add status DONE for training session
-New : Add colors on training
-New : Add modules in training catalogues
-New : Add file import of trainee into session
-New : Add trainer diploma management (dictionnay)
-New : Add option to link trainer  to training catalogue (to filter trainer list on trainer session affectation)
-New : New session calendar generation feature
-New : In session list display cost information (enabled by right management)
-New : Add End training attestation empty
-New : Can merge training programme to proposal PDF

***** ChangeLog for 2.1.13 compared to 2.1.12 *****
-Fix : Fix session agenda bug

***** ChangeLog for 2.1.12 compared to 2.1.11 *****
-New : Clone Training
-Fix : OPCA managemnt on intra
-New : Add trainee name into Document linked tabs (limit to 7 by company)
-New : Add more information into linked manually document by thridparty on Docuement linked tab
-New : can link manually document by thridparty and mother company on Docuement linked tab
-New : Add option to add avg cost on propal/order/invoice (also on update lines)
-New : Can link invoice/propal/session from session tab in propal/order/invoice screen
-New : PRESENCE SHEET : add trainer signature block by session calendar
-fix : decimal value in training catalogue duration allowed
-New : Add two field in training catalgue to be compliant with French DIRRECT 2015 reform (and output in fiche pedago PDF)
-New : Add recipient (supplier contact) in session (as well supplier contract and invoice)
-New : comptability for Dolibarr 3.7 only

***** ChangeLog for 2.1.11 compared to 2.1.10 *****
-Fix : Priority in objectif training can be new updated 
-Fix : Break pages on training program PDF
-Fix : Link Logistique go on location management
-New : Only for Dolibarr 3.6.x
-New : Trainer Mission letter

***** ChangeLog for 2.1.10 compared to 2.1.9 *****
-Fix : French spelling (thank to  Joël Pastré)
-Fix : Multicompany trainee creation can be block if trainee exist for another entity
-New : Add filter on site list
-Fix : Fix create trainne and add to session without linked to extra thirdparty
-Fix : Multi entity trainee (trainne is not sharable)
-Fix : Certificate Box show wrong result
-Fix : Edit administrative task into trainning show select parent task with multiples values
-New : New form to add trainee (from contact from simple creation are now mix in the same screen)
-New : Certificate A4 PDF with relevant data
-Fix : Remove certificate Credit card PDF  because not usefull
-New : Add trainer type on session. Usefull for french admnistrative document (Tools,Export)
-New : Can have different OPCA for one session/thirdparty in inter-company session


***** ChangeLog for 2.1.9 compared to 2.1.8 *****
-Fix : Attestation by trainnee with training objectives wrong output
-New : Better trainer list and session trainer list
-New : Attestation PDF is printed only if trainne is Cofrim/present or Part Present
-New : Add button to mass update trainee status in session (Edit trainee new button available)
-New : Convention per trainee (can create more than one convention per session/custom and affect them to selected trainnee)
-New : Clone trainer on session clone
-Fix : [bugs_agefodd #1433] Retreive document attached into session
-Fix : PDF Attestation by trainee Objectif pédagogique strange output
-Fix : bugs_agefodd #1453 - Erreur lors de l'attachement d'un OPCA sur un participant
-Fix : On Training program generated from session time was not correct.
-Fix : PDF output, global review on logo and other things
-New : New field on create trainee
-New : Better management of certification date according configuration
-New : Add location filter on agenda 
-Fix : Cost management create supplier invoice with product with no buying price wasn't ok

***** ChangeLog for 2.1.8 compared to 2.1.7 *****
-New : New permissions on Training catalogue, location (basic (old) permissions apply now sessions) all defaulted to yes (to avoid right problem)
-New : New permission on session to limit session display in list regarding user customer sales affectation
-Fix : Statistics screen account manager do not work
-Fix : Remove PHP warning on training note screen
-New : Can manage more than on proposal/order/invoice per session/thirdparty.
-Fix : bugs_agefodd #1143 - Apostrophe into trainee lastname or firstname 
-New : Cannot generate "auto-compelte" proposal from document link screen if no product linked before
-New : On Propal and order "auto" génération contacts are filled with session informations
-Fix : No-limit contact list if combobox is not use
-Fix : session : Send document better warining message and enabled WYSIWYG into mail message
-Fix : bugs_agefodd #1146 - small errors in fr_FR/agefodd.lang 
-Fix : On proposal or Order auto generation fill line with only on date if strat date and end date are the same 
-Fix/New : Propal/order/invoice session tab allow order and display Propal/Oerder/Inoice other tabs
-Change : Change status session list
-New : New left session menu
-Fix : Export Session didn't work if prefeix table were not llx_
-New : New tab "Trainer session list" into trainer screen
-New : If trainer calendar is use then control on existing booked date is done on update or add trainer calendar in session
-New : Warning message if session date are not correct regarding session calendar date (and trainer calendar date if use)
-New : color into Session agenda show status of session
-New : Session-> Document link => Cannot create invoice from proposal if proposal is not signed
-New : Session list can be filtered by period (1,2,3 in month and year=2013 will diplsay Jnuary,febuary,march 2013 session)
-New : Auto calculation of selling price of session (regarding proposal/order/invoicelinked to the session)
-New : Session cost mangement (supplier invoice creation from session) and auto caculation of session cost
-New : New field date for confirm reservation date on session
-New : If session is set to "Not done" status all status of trainer and trainee wil be set to cancelled
-New : if use trainer calendar date are dipslay in all card where trainer list is displayed
-New : Update certificate information from trainnee->certificate card
-New : Only for Dolibarr 3.5


For Dev : 
Column archive from llx_agefodd_session have been dropped now it's llx_agefodd_session.status=4

***** ChangeLog for 2.1.7 compared to 2.1.6 *****
For All:
	-Fix : Update session bug if no product set
	-Fix : Agenda session got Week and go to list file main.inc.php is missing
	-Fix : Trainer calendar in session decimal cost didn't save
	-Fix : Add trainee to session bug ( dol_time_plus_duree undefined function)
	-Fix : Problem with no use of trainee type into session
	-Fix : Various problem on Adress into letter PDF
	-New : Extend Training label to 100 caracters
For Dev:
	-New: Add column ref_ext into agefodd_session.

***** ChangeLog for 2.1.6 compared to 2.1.4 *****
For All:
	- New : Add session type into session list 
	- Fix : bugs_agefodd #1050 - Error when update trainee without using type
	- Fix : Update certificate with numbering rule always increment it
	- Fix : Field label Training internal ref vs ref
	- Fix : Use of Agefodd contact select list do not work.
	- New : Add trainer time management (optional in admnistration)
	- New : Add product link to session (copied from training is defined)
	- New : Add session status (and default status in administration)
	- New : Add menu session in draft status
	- New : Add button to generate convention directly from convention edit screen
	- New : When generate a document the screen auto scroll to targeted customer
	- New : Less click to add trainer
	- New : Add trainee name into proposal,order, invoice lines (optionnal in admnistration)
	- Fix : Specific agenda session and trainer session do not work well
	- New : When create trainee into session intra-entreprise the customer new trainne is already selected
	- Fix : Remove trainee from session decrement number of trainee into the session
	- Fix : PDF attestatation : missing space before hour
	- Fix : Session time selection to 23h (21h before)
	- New : If customer is specified on session header, if will be apply on calendar event 
	- New : Tab Training session on Thirdparty screen
	- Fix : Hide manage agefodd contact according admin setup
	- New : Session list by administrative task
	- New : PDF : Add function trainnee into convention and timesheet
	- New : Cerficate Box on module home page review (new dedicated list)

***** ChangeLog for 2.1.4 compared to 2.1.3 *****
For All:
	- Fix : When generating convention Main company information missing
	- Fix : Bad PDF Conseil pratique display when using WYSIWYG on training
	- Fix : Send Doc Welcome letter, convocation, courrier acceuil, conseil pratique did not work
	- New : Add filter on training list
	- New : New Session Export (can be use for BPF france)
	- Fix : Create invoice process block with 
	- Fix : Fix SQL upgrade bug (between 2.1.0 and 2.1.3 or 2.1.4) (no impact for orhter upgrade)
	- Upgrade : Dutch language file upgrade


***** ChangeLog for 2.1.3 compared to 2.1.2 *****
For Dev:
    - Change column name nb_min_target to nb_subscribe_min
For All    
    - Fix : pagging training list 
    - Fix : bugs_agefodd #1038 - Inscription participant
    - Fix : bugs_agefodd #1042 - Convention de formation
    - Fix : Objectif Pédagogique not display in trainning card and in Fiche pedago PDF.
    - New : Add training category manage by dictionnary.
    - New : Extrafield on training and session
    - New : Add tab list session on proposal
    
    
***** ChangeLog for 2.1.2 compared to 2.1.1 *****
For All :
    - Session : When create session failed field are refill with old value
    - Fix bug missing column creation

***** ChangeLog for 2.1.1 compared to 2.1.0 *****
For All :
    - Fix statistics pages (training list box can be empty)
    - Session Send docs : In send certificate add certificate A4 and certicificate card attachement files if certificate is managed (admin)
    - Fix : Doc timesheet presence (fiche de présence (format paysage)  

***** ChangeLog for 2.1.0 compared to 2.0.29 *****
For All :
    - Compatibility with Dolibarr 3.4 only
    - Fix bugs_agefodd #906 - Courrier accompagnant l'envoi du dossier de cloture   
    - tasks_agefodd #932 : Add calculation of the cost of a session
    - New Screen : Mass Archive sessions per year 
    - Import/Export Trainee 
    - Import/Export Certificate
    - Certificate : Certificate indicator (pass or failed)
    - Certificate : Certificate type dictionnary
    - Certificate : Certificate numbering rules
    - Dashboard : Fix dashboard bugs
    - Admin/Training/Session : Add administrative task per training (base on admin administrative task)
    - Session : Administrative task : Fix Admin administrative task (when all are deleted you can create new ones)
    - Admin : Switch to turn off OPCA (funding) feature
    - Session : Add session attached files tab (to attach all kind of document)
    - Session : Add product link to training
    - Session : Add proposal link to session
    - Session : Document linked screen : On create proposal auto create and link the full proposal
    - Session : Document linked screen : On create order auto create from proposal linked or from scratch
    - Session : Document linked screen : On create Invoice create and auto link to session/thridparty from order or proposal
    - Admin : Remove number of lines per list from agefodd admin because this option can be set into general Dolibarr administration and do conflict
    - Session : Calendar : Better input method in sessions from "templated date set" in admnistration screen
    - Session : Option to clone with trainee
    - Session : Subscribers have now a status (auto calculated or manually set) according administration options
    - Session list : Display ratio Prospect/confirm/cancelled in session list
    - Session list : Display ration in green if trainee confirm is equal or greater than minimum subscribers session information or red if not 
    - Session : Add proposal/order/invoice amount link to session
    - Agenda : Add custom agenda into Agefodd module to filter session by Salesman, thirdparty, contact, trainer
    - Agenda : Add custom agenda dedicated to trainer (if trainer logged in see only his own training session) managable by right management
For Dev:
    - Rewrite $line to $lines in all classes
    - Implement $lines object in all classes for fetch_all (or similar) methods
    - No more use of dol_htmloutput_mesg, uses setEventMessage instead
    - reformat (auto-indent) all module code
    - Remove dol_include_once when possible replace by require_once
    - Comment in code should be in english
    - Try to remove all PgSQL warning regarding date update or insert (date must be quoted)
    - Create class Agefodd_session_stagiaire and move all method concerning this class from Agsession class to Agefodd_session_stagiaire 


***** ChangeLog for 1.1 compared to 1.0 version *****
For developers:
- Fix: full Dolibarr 3.x compatibility
- Fix: complete restructuring of files

***** ChangeLog for 1.0-beta1 compared to non-existent version *****
For users:
- New: Agefodd Module Packaging.

***** ChangeLog for 2.0.0 compared to 1.0-beta1 *****
For all :
- Fix: full Dolibarr 3.2 compatibility
- Fix: complete restructuring of files

***** ChangeLog for 2.0.1 compared to 1.0-beta1 *****
For all :
- Fix: Bug on list Trainee and Contact 
- Fix: Add filter on Session and Trainee view

***** ChangeLog for 2.0.2 compared to 2.0.1 *****
For all :
- On Session edit page add button to save and close "edit mode" and another to save and stay in "edit" mode
- Add fields Site acess and various notes on place
- Add fields Required document and equipements on Trainning Catalogue
- Add PDF documents "conseils pratique"


***** ChangeLog for 2.0.3 compared to 2.0.2 *****
For all :
- Fix : When cancel trainner creation return on list is better than resturn on blank screen
- Fix : Suivi of place object (creator and creation date logged correctly)
- Fix : When cancel contact creation return on list is better than resturn on blank screen
- Fix : List of Dolibarr contact in contact Agefodd creation (display in gray (not selectable) contact already exists in agefodd)
- Fix : Suivie of trainneee code
- Fix : Add pseudo English translation
- Fix : Correct training update on session works

***** ChangeLog for 2.0.4 compared to 2.0.3 *****
For all :
- Fix : Can normally be use without "custom" directory

***** ChangeLog for 2.0.5 compared to 2.0.4 *****
For all :
- Fix : Add error massage to session update or new without location
- Fix : convention generation for number of trainne (Chapter 1)
- Fix : Set better PDF generation for documents.

***** ChangeLog for 2.0.6 compared to 2.0.5 *****
For all :
- Fix : convention PDF document city
- Fix : convention PDF page number
- Fix : convention generation predefined text
- Fix : fiche presence with more than 10 trainee

***** ChangeLog for 2.0.7 compared to 2.0.6 *****
For all :
- Fix : Document liée : generating invoice according and link to order
- Fix : Document liée : Better display (in one line) for "bon de commande"
- Fix : Pagging on session list (archive and active)
- Fix : Pagging on training list (archive and active)
- Fix : Pagging on site list (archive and active)
- Fix : Pagging on trainer list (archive and active)
- Fix : Pagging on training list (archive and active)
- Fix : Session edit detail display type of founding for trainee (if option enabled)
- Fix : Session time management (time by quarter rather than select date std control)

***** ChangeLog for 2.0.8 compared to 2.0.7 *****
For all :
- Fix : migration : location are correct after migration

***** ChangeLog for 2.0.9 compared to 2.0.8 *****
For all :
- Fix : Site, remove debug display userid
- Add : Session  :creation tool tips to explain from where come contact.
- Fix : Site, Edit and change location name
- Fix : Cenvetion PDF, Avoid create last page if "fiche pedagogique" not exist
- Fix : Site, Customer and supplier choice are possible
- Change : When creating site, it's no more possible to input adress. It's done just after on edit screen
- Change : Site : On edit screen, there is a button to import customer adress

***** ChangeLog for 2.0.10 compared to 2.0.9 *****
For all :
- Fix: Trainee - create : customer choice is now combobox
- Add: You can set in module conf if session contact come from agefodd contact or doibarr contact (if dolibarr contact, agefodd contact will be created auto)
- Change: Manage subscribe trainee on different tab in session screen 
For dev :
- Fix: Create agefodd contact return now new agefodd contact id.
- Fix: Session create : better error management 

***** ChangeLog for 2.0.11 compared to 2.0.10 *****
For dev :
- Fix: comment agefodd.lib.php according PSR 
For all :
- Fix : Session level graph now calulated on session information
- Fix : spelling correction
- Fix : Session : creation : better display of question mark (help picto)
- Fix : Session : document : convention on texte Texte "l'organisme" get good enterprise legal form 
- Fix : Session : document : convention text 5 correctly saved

***** ChangeLog for 2.0.12 compared to 2.0.11 *****
For all :
- Fix : Correct fiche pédagogique document (avoid bug of long programme and strange display)
- Fix : Remove programme document (useless because programme is include into fiche pédagogique)
- Fix : PDF Conseil pratique, add foot page
- Fix : PDF Convention : better Layout.
- Fix : PDF Convention : import all page of fiche pedago
- Fix : Allow into trainning programme double quote.
- Add : Generate fiche pedagogique from Trainning
- Add : PDF Réglement intérieur
- Add : Manage internal rule for location
- Add : Add technical spec into module folder
- Add : Agenda Dolibarr management
- Remove : BPF document because do not exists yet
- Fix : On update convention art. 5 is no more replace with art. 4
- Merge Branch from jf-Ferry : searate tab for trainner and merge subrogation and trainee
- Merge Branch from jf-Ferry : Type of session (intra-inter entreprise), session nb place, session color
For dev :
- Fix: Use __construct() for all class

***** ChangeLog for 2.0.13 compared to 2.0.12 *****
For all :
- Change: PDF : merge Reglement interieur et conseil pratrique
- Add : PDF : Add convocation model 
- Add : type attribut on training sessions
- Add : fields on training session 
- Add : allow to choice a color for the session
- Fix : On archive session with option "Affiche dans la liste des contacts (création de session) les contacts Dolibarr (plutot que les correspondant Agefodd)"
to yes, the contact client do not change anymore
- Fix : On desativation and reactivation of the module, only good upgrade script are launch.
- Add : Add field goal/but in training card and in PDF fiche Pedago
- Add : separate Tab for trainer
- Add : Send documents by mail (link with agenda)
- Add : better upgrade process (do not active session on upgrade version (must be from 2.0.12 to work)) 
For dev : 
 - always pass in paramters of method create, update, and so on, the $user object rather tahn $user->id
  - move all class to class directory
  
***** ChangeLog for 2.0.14 compared to 2.0.13 *****
For All :
   - Fix bug on updating session calendar
   - Fix upgrade process
For dev :
	- Delete AGF_LAST_VERION_INSTALL in modAgefodd before create a new one 
	
***** ChangeLog for 2.0.15 compared to 2.0.14 *****
For All :
   - Merge EnvoisDoc from jf-ferry
   - Manage error on trainee civility 
   - Correct Convention PDF Modele, now attach the correct fiche pedago
   - Review paging of Fiche pedago
   - Manage in convention the french TVA applicable or not (according conf->society setting)
   - Some chapter of convention are'nt no more retreive from older one, because some data can change from on session to another 
For Dev : 
   - Correct wrong call to agsession constructor in convention PDF
   - Drop llx_agefodd_place_ibfk_2 if extists because it is not use and create bug
   
***** ChangeLog for 2.0.16 compared to 2.0.15 *****
For All :
   - Merge EnvoieDoc from jf-ferry :
      - Add session list tab in trainning and site screen
      - Change some description into document screen
      - improvement of envoie doc screen and better mail layout
   - Correct the litteral number of page for the convention PDF
   - correct some syntax in PDF
   - add parameters in admin to change number of elements display in all list screen
   - Correct town trainning format in agefodd configuration 
   - Change type of but, prerequis, public, method for extand to text (more than 255 caracters)
   - Correct mistake into fiche presence
   - Add color picker for PDF model in configuration
For Dev : 
   - Rename file update_1.0-2.0 to avoid this file to be run. Use this file only for migration from Agefodd for Dolibarr 2.9 to this version
   - Remove "Plateau technique" from PDF 
   
***** ChangeLog for 2.0.17 compared to 2.0.16 *****
For All :
	- Fix Bug on Universal mask (Bug #541)
	- Uniformize pagefoot for PDF
	- Display subrogation info in all case
	- Merge from jf-ferry : multicompagny module compatibility
For Dev : 
	- Add entity column to prepare multicompany module functionnality
	- change way to get society info in PDF use $mysoc global is better than $conf->global->MAIN_SOC....
	
***** ChangeLog for 2.0.18 compared to 2.0.17 *****
For All :
	- Can create trainer from Dolibarr user list
	- Add ref interne on Training catalogue
	- Add Multicompany for trainer,catalogue, contact (correspondant)
	- Location : Fix bug on creation of internal Rule
	- Clone session
For Dev : 
	- Add Multicompany for catalogue (fetch method)
	- remove llx_agefodd_reg_interieur foreign key to allow Internal Rule creation. Manage foreign key by code
	
***** ChangeLog for 2.0.19 compared to 2.0.18 *****
For All :
	- Add option from jf-ferry to add picture of customer on doc
	- Add missing english translation
	- Fix behaviour on force update number of trainee per session
	- Fix bug #582 on fiche pedage (duration is not display) 
	- Review adn improve of trainee creation page
	- Functionnal documentation updated
	- Option to link invoice without order
	
***** ChangeLog for 2.0.20 compared to 2.0.19 *****
For All :
	- review page title (tag <TITLE>)
	- Fix Bug #589 on PDF Asttestation (duration  and objectif are not display)
	- Task #591 complete (PDF foot page render only fill information)
	- Add translation for index pages and module titles
	- Change behaviour of function "auto calcul nb trainne" in session card
	- Better calculation of nb trainee trained in first stat pages
	- Better comptatibility module for multicompny
	- Fix : Sort of list place
	- Better error management in place update screen
	- Display all compagny link to a session in Document screen (Customer session, OPCA trainee, OPCA session)
		- you can now generate Convention for OPCA or Customer as you wish
	- Add translation key for all PDF and convention
	- Add tab Session in Order and Invoice screen  
	- Fix bug on trainee creation (always create contact of customer) 
	- Task #512 complete (title of convention is trainee company is individual)
For Dev:
	- Rename index on tables to be unique in all dolibarr database 
	- Save correct convention creation date in formation catalogue and objectif peda
	- Set correct creation date in create stagaire_type sql script
	- Fix some query for PostgreSQL compatibility
	- Review creation table SQL script to be complient with Dolibarr and PostgreSQL query	
	
	
***** ChangeLog for 2.0.21 compared to 2.0.20 *****
For All :
	- Add option to activate MAIN_USE_COMPANY_NAME_OF_CONTACT in admin (see help for detail)
	- Add statistique blok (thank to jf-ferry)
	- Fix bug #613 - erreur lors du "clonage de session"
	- Fix bug #614 - bug affichage sur convention
	- Fix bug #612 - Contact sur courrier accompagnant l'envoi du dossier de clôture


***** ChangeLog for 2.0.22 compared to 2.0.21 *****
For All :
	- Better installation/upgrade process (run only structure update required)
	- Change database structure to upgrade performance and reliability
	- Add postgresql compatibility
	- Index (first figures page): Fix lots of bugs on figures
	- Session : On create Session contact is refresh automaticly on customer selection(according to settings (show Dolibarr contact in creation of session=>Yes,Thirdsparty settings combox-box=>Yes))
	- Session : display trainnee funding only if option activated
	- Session : Add option to display contact OPCA adress rather than OPCA adress
	- Session : Fix bug on add trainer : if trainne is a user, it's now possible to select it (bug occured if combo-box for trainer is not active) 
	- Session : Fix Bug on create session : Customer contact is correctly save
	- Session : Fix Bug on update calendar : new date correctly saved
	- Index (first figures page): Add english missings translations
	- Session Calendar/admin : Task #450 - Crée une journée type et permettre de la choisir sur la gestion du calendrier de la session
	- PDF attestation : Task #623 - Attestation de formation
	- PDF convetion : task #615 - Première page convention de formation
	- PDF customization : tasks #650 - 2 more color selector for PDF customization (thanks to sgiovagnoli for his contribution)
	- PDF timecard (fiche presence) : tasks #649 - PDF Timecard (fiche presence) behaviours
For Dev :
	- Review function comment for all class
	- Review $this->line[$i]->... in some class to avoid "create object from empty property" if display PHP error message is ON  
	- Move multiselect directory from inc/multiselect to includes/multiselect
	- Add about pages 
	- Enabled required other Dolibarr modules on Agefodd activation 
	
***** ChangeLog for 2.0.23 compared to 2.0.22 *****
For All :
	- Add Timecard in landscape format
	- Fix bug 677,678,679,680,682
	- Task tasks_agefodd #688 done - 	Add litte session header in Session->document tabs
	- Add Convocation send document behaviours
	- Fix translation in Send documents screen 
	- tasks_agefodd #687 - Send convocation 
For Dev :
	- Update licence version from GPL v2 to GPL v3
	
***** ChangeLog for 2.0.24 compared to 2.0.23 *****
For All :
	- Fix logo size and localization on PDF
	- Add Dutch language file (Thanks to S. van Tuinen)
	- Fix Bug 716 (Now in Session->Send docs : only trainee from contact email will be available in list box)
	- Fix Bug on "courrier" PDF
	- Add Send Doc "Conseil pratique"
	- Fix bug in Send Documents screen (document not attached, event not triggred...)
	- Add ComboBox select in New trainer card
	- Add behaviours "Certification" (task #616)
	
***** ChangeLog for 2.0.25 compared to 2.0.24 *****
For All :
	- Fix bugs_agefodd #721
	- Fix bugs_agefodd #723
	- Fix bugs_agefodd #724
	
***** ChangeLog for 2.0.26 compared to 2.0.25 *****
For All :
	- Change PDF Fiche presence: remove double country displayed in header
	- Fix bugs_agefodd #660
	- Fix bugs_agefodd #684
	- Session : Color picker reflect immediatly the color changes
	- WYSIWYG in trainning card (activated by option in configuration)
	- Fix oversize problem for Timecard by trainee (Fiche de présence, Fiche de présence vide, Fiche de présence (format paysage), Fiche de présence par stagaire)
For Dev : 
	- Change colorpicker lib js to avoid js bug (can be seen with firebug)
	
***** ChangeLog for 2.0.27 compared to 2.0.26 *****
For All :
	- PDF : Fix Fiche pedago problem (footer on two pages...)
	- PDF : Fix Customer image into certification (attestation) and timecard (feuille présence)
	- Admin page with on/off button
	- Add more Dutch translation and English also.
For dev : 
	-Change admin page from agefodd.php to admin_agefodd.php
	
***** ChangeLog for 2.0.28 compared to 2.0.27 *****
For All :
	- Fix bugs_agefodd #760
	- Fix bugs_agefodd #761
	- PDF : Fiche eval : Litle update
	- Session/Send Documents : Fix history event view problem with dolibarr 3.3
	- PDF : Convention : On order summary the product description are display.
	- Review English and Dutch translation
	- Remove PHP warining from agefodd_facture and agefodd_session_calendrier
	
***** ChangeLog for 2.0.29 compared to 2.0.28 *****
For All :
	- Fix french translation (spelling)
	- Fix Bug #829 - Can't clone a session (with pgsql)
	- Fix bug PDF : If use customer logo and the logo do not exists PDF do not print at all
	- Fix bug bugs_agefodd #868 - bugs envoi mail attestation
	
***** ChangeLog for 2.0.30 compared to 2.0.29 *****
For All :
	- Fix bugs_agefodd #897 - PDF Conseil Pratique do not result proper data with 3.3
	- Fix bugs_agefodd #896 - PDF header ugly if no logo on company
	- Fix bugs_agefodd #898 - "Suivie admnistratif" this screen simply do not provide relevent information 
	
