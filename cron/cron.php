<?php
dol_include_once('/agefodd/class/agsession.class.php');
dol_include_once('/agefodd/class/agefodd_session_stagiaire.class.php');
dol_include_once('/agefodd/class/agefodd_stagiaire.class.php');
dol_include_once('/agefodd/lib/agefodd.lib.php');


class cron_agefodd
{

	private $db;

	/**
	 * Constructor
	 *
	 */
	public function __construct()
	{
		global $db, $const;
		$this->db = $db;
		$this->const = $const;

	}


    /**  Change le statut du calendrié formateur et du calendrier de session lié si la date est dans le passé et que le statut est confirmé
     * @param int $fk_newStatus
     * @param int $daysOffset
     * @param int $daysRange
     * @return int
     */
    public function autoStatusAgefoddFormateurCalendar($fk_newStatus = Agefoddsessionformateurcalendrier::STATUS_FINISH, $daysOffset = 0, $daysRange = 1)
    {
        global $db, $user;

        $errors = 0;
        $updated = 0;

        dol_include_once('agefodd/class/agefodd_session_formateur_calendrier.class.php');
        // GET SESSION AT DAY-1
        $sql = "SELECT rowid, fk_agefodd_session_formateur";
        $sql.= " FROM " . MAIN_DB_PREFIX . "agefodd_session_formateur_calendrier sfc ";
        $sql.= " WHERE sfc.heuref >=  CURDATE() - INTERVAL ".(intval($daysOffset) + intval($daysRange))." DAY ";

        $sql.= " AND sfc.heuref < CURDATE() ";
        if(!empty($daysOffset)){
            $sql.= " - INTERVAL ".(intval($daysOffset))." DAY ";
        }

        $sql.= " AND   sfc.status =  ". Agefoddsessionformateurcalendrier::STATUS_CONFIRMED;

        $resql = $this->db->query($sql);
        if (!empty($resql) && $this->db->num_rows($resql) > 0) {
            while ($obj = $this->db->fetch_object($resql)) {

                $sessionCal = new  Agefoddsessionformateurcalendrier($db);
                if($sessionCal->fetch($obj->rowid)>0)
                {
                    $sessionCal->status = $fk_newStatus;
                    if($sessionCal->update($user) > 0)
                    {
                        $updated++;

                        // Now update trainer and trainee
                        $TCalendrier = _getCalendrierFromCalendrierFormateur($sessionCal, true, true);
                        if (!empty($TCalendrier))
                        {
                            $agf_calendrier = $TCalendrier[0];
                            $agf_calendrier->date_session = $sessionCal->date_session;
                            $agf_calendrier->heured = $sessionCal->heured;
                            $agf_calendrier->heuref = $sessionCal->heuref;
                            $agf_calendrier->status = $sessionCal->status;
                            //$agf_calendrier->calendrier_type = $code_c_session_calendrier_type;
                            $r=$agf_calendrier->update($user);
                        }

                    }
                    else
                    {
                        $errors++;
                    }
                }
            }
        }

        $this->output = 'errors: '.$errors.' | Updated: '.$updated;

        return $errors;
    }




    /**  Change le statut du calendrié de session si la date est dans le passé et que le statut est confirmé
     * @param int $fk_newStatus
     * @param int $daysOffset
     * @param int $daysRange
     * @return int
     */
	public function autoStatusAgefoddCalendar($fk_newStatus = Agefodd_sesscalendar::STATUS_FINISH, $daysOffset = 0, $daysRange = 1)
	{
		global $db, $user;

		$errors = 0;
		$updated = 0;

		dol_include_once('agefodd/class/agefodd_session_calendrier.class.php');
		// GET SESSION AT DAY-1
		$sql = "SELECT rowid, fk_agefodd_session ";
		$sql.= " FROM " . MAIN_DB_PREFIX . "agefodd_session_calendrier sc ";
		$sql.= " WHERE sc.heuref >=  CURDATE() - INTERVAL ".(intval($daysOffset) + intval($daysRange))." DAY ";

		$sql.= " AND sc.heuref < CURDATE() ";
		if(!empty($daysOffset)){
			$sql.= " - INTERVAL ".(intval($daysOffset))." DAY ";
		}

		$sql.= " AND   sc.status =  ".Agefodd_sesscalendar::STATUS_CONFIRMED;

		$resql = $this->db->query($sql);
		if (!empty($resql) && $this->db->num_rows($resql) > 0) {
			while ($obj = $this->db->fetch_object($resql)) {

				$sessionCal = new Agefodd_sesscalendar($db);
				if($sessionCal->fetch($obj->rowid)>0)
				{
					$sessionCal->status = $fk_newStatus;
					if($sessionCal->update($user) > 0)
					{
						$updated++;

						/*
						// Now update trainer and trainee
                        $TCalendrier = _getCalendrierFormateurFromCalendrier($sessionCal);
                        if (!empty($TCalendrier))
                        {
                            $agf_calendrier = $TCalendrier[0];
                            $agf_calendrier->date_session = $sessionCal->date_session;
                            $agf_calendrier->heured = $sessionCal->heured;
                            $agf_calendrier->heuref = $sessionCal->heuref;
                            $agf_calendrier->status = $sessionCal->status;
                            //$agf_calendrier->calendrier_type = $code_c_session_calendrier_type;
                            $r=$agf_calendrier->update($user);
                        }*/

					}
					else
					{
						$errors++;
					}
				}
			}
		}

		$this->output = 'errors: '.$errors.' | Updated: '.$updated;

		return $errors;
	}

	public function sendAgendaToTrainee($fk_mailModel = 0, $days = 1, $basedOnSession = false)
	{
		global $conf, $langs, $user;
		require_once (DOL_DOCUMENT_ROOT .'/core/class/CMailFile.class.php');
		$message = '';

		$days = intval($days);

		if(empty($fk_mailModel) && !empty($conf->global->AGF_SENDAGENDATOTRAINEE_DEFAULT_MAILMODEL)) {
			$fk_mailModel = $conf->global->AGF_SENDAGENDATOTRAINEE_DEFAULT_MAILMODEL;
		}

		$mailTpl = agf_getMailTemplate($fk_mailModel);
		if($mailTpl < 1){
			return $langs->trans('TemplateNotExist');
		}


		if(empty($basedOnSession))
		{
			/* # Status
			 *  0 prévi
			 *  1 Confirmée
			 */
			// GET SESSION AT DAY-1
			$sql = "SELECT rowid fk_session_calendrier, fk_agefodd_session  ";
			$sql.= " FROM " . MAIN_DB_PREFIX . "agefodd_session_calendrier sc ";
			$sql.= " WHERE sc.heured >=  CURDATE() + INTERVAL ".$days." DAY AND sc.heured < CURDATE() + INTERVAL ".($days+1)." DAY ";
			$sql.= " AND   sc.status = 1 ";
		}
		else{
			/* # Status
			 *  1 Envisagée
			 *  2 Confirmée
			 *  6 En cours
			 *  5 Réalisée
			 *  3 Non réalisée
			 *  4 Archivée
			 */
			// GET SESSION AT DAY-1
			$sql = "SELECT rowid fk_agefodd_session ";
			$sql.= " FROM " . MAIN_DB_PREFIX . "agefodd_session s ";
			$sql.= " WHERE s.dated >=  CURDATE() + INTERVAL ".$days." DAY AND s.dated < CURDATE() + INTERVAL ".($days+1)." DAY ";
			$sql.= " AND   s.status = 2 ";
		}






		$resql = $this->db->query($sql);



		$sended = 0;
		$errors = 0;
		$invalidEmailAdress = 0;
		$disabledMContact = 0;


		if (!empty($resql) && $this->db->num_rows($resql) > 0)
		{
			while ($obj = $this->db->fetch_object($resql))
			{
				$agsession = new Agsession($this->db);
				if($agsession->fetch($obj->fk_agefodd_session))
				{
					$agsession->fetch_optionals();

					// GET TRAINEES
					$sql = "SELECT rowid ";
					$sql.= " FROM " . MAIN_DB_PREFIX . "agefodd_session_stagiaire ss ";
					$sql.= " WHERE  ss.fk_session_agefodd = ".$agsession->id . ' AND status_in_session IN (2) ' ;

					$resqlStag = $this->db->query($sql);

					if (!empty($resqlStag) && $this->db->num_rows($resqlStag) > 0) {
						while ($objStag = $this->db->fetch_object($resqlStag)){
							$agsessionTrainee = new Agefodd_session_stagiaire($this->db);
							if($agsessionTrainee->fetch($objStag->rowid) > 0)
							{
								$agsessionTrainee->fetch_optionals();

								$stagiaire = new Agefodd_stagiaire($this->db);
								if($stagiaire->fetch($agsessionTrainee->fk_stagiaire) > 0)
								{
									if(!empty($stagiaire->disable_auto_mail)){
										$disabledMContact ++;
										continue;
									}
									else{
										// PREPARE EMAIL
                                        $from = getExternalAccessSendEmailFrom($user->email);
                                        $replyto = $user->email;
                                        $errors_to = $conf->global->MAIN_MAIL_ERRORS_TO;

										//$arrayoffamiliestoexclude=array('system', 'mycompany', 'object', 'objectamount', 'date', 'user', ...);
										if (! isset($arrayoffamiliestoexclude)) $arrayoffamiliestoexclude=null;

										// Make substitution in email content
										$substitutionarray = getCommonSubstitutionArray($langs, 0, $arrayoffamiliestoexclude, $agsession);

										complete_substitutions_array($substitutionarray, $langs, $agsession);



										$thisSubstitutionarray = $substitutionarray;

										$thisSubstitutionarray['__agfsendall_nom__'] = $stagiaire->nom;
										$thisSubstitutionarray['__agfsendall_prenom__'] = $stagiaire->prenom;
										$thisSubstitutionarray['__agfsendall_civilite__'] = $stagiaire->civilite;
										$thisSubstitutionarray['__agfsendall_socname__'] = $stagiaire->socname;
										$thisSubstitutionarray['__agfsendall_email__'] = $stagiaire->email;

										// Tableau des substitutions
										if (! empty($agsession->intitule_custo)) {
											$thisSubstitutionarray['__FORMINTITULE__'] = $agsession->intitule_custo;
										} else {
											$thisSubstitutionarray['__FORMINTITULE__'] = $agsession->formintitule;
										}

										$date_conv = $agsession->libSessionDate('daytext');
										$thisSubstitutionarray['__FORMDATESESSION__'] = $date_conv;


										$sendTopic =make_substitutions($mailTpl->topic, $thisSubstitutionarray);
										$sendContent =make_substitutions($mailTpl->content, $thisSubstitutionarray);

										$to = $stagiaire->mail;

										if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
											// is not a valid email address
											$invalidEmailAdress ++;
											continue;
										}


										if(!empty($conf->global->AGF_CRON_FORCE_EMAIL_TO) && filter_var($conf->global->AGF_CRON_FORCE_EMAIL_TO, FILTER_VALIDATE_EMAIL)){
											$to = $conf->global->AGF_CRON_FORCE_EMAIL_TO;
										}

                                        if (!empty($conf->global->MAIN_MAIL_ADD_INLINE_IMAGES_IF_DATA)) {
                                            $upload_dir_tmp = DOL_DATA_ROOT.'/mail/img';
                                            $cMailFile = new CMailFile($sendTopic, $to, $from, $sendContent, array(), array(), array(), $addr_cc, "",  0, 1, $errors_to, '', '', '', getExternalAccessSendEmailContext(), $replyto, $upload_dir_tmp);
                                        }
                                        else
                                        {
                                            $cMailFile = new CMailFile($sendTopic, $to, $from, $sendContent, array(), array(), array(), $addr_cc, "",  0, 1, $errors_to, '', '', '', getExternalAccessSendEmailContext(), $replyto);
                                        }

										if($cMailFile->sendfile()){
											$sended++;
										}
										else{

											$message.=  $cMailFile->error .' : '.$to;


											$errors++;
										}

									}
								}
								else{
									$message.='error fetch stagiaire';
								}
							}
							else{
								$message.='error fetch agsessionTrainee';
							}
						}
					}
					else{
						// nothing to send
						if (empty($resql)) dol_print_error($this->db);
						$message.='No email to send ';
					}

				}
				else{
					$message.='error fetch agefodd';
				}
			}

			$message.= ' | ' . $langs->trans('Sended').' : '.$sended;
			$message.= ' | ' . $langs->trans('Errors').' : '.$errors;
			$message.= ' | disabled contact : '.$disabledMContact;
			$message.= ' | Invalid email adress : '.$invalidEmailAdress;

		}
		else{
			if (empty($resql)) dol_print_error($this->db);
			$message.=  $langs->trans('AgfNoSessionToSend');
		}

		$this->output = $message;

		return $errors;
	}

	/**
	 * Exécuté tous les mois pour l'envoi de mails aux participants ayant terminés une formation dans les "$nbmonth" mois
	 *
	 * @param int $userID de l'emetteur
	 * @param int $nbmonth nombre de mois
	 * @param int $mailModelId id du model de mail
	 * @return int
	 */
	public function completionMailTrainee($userID, $nbmonth, $mailModelId)
	{
		global $langs, $user;

		$dateAgo = strtotime('-' . $nbmonth . ' months');

		$countMailsToSend = 0;
		$countMailsSent = 0;
		$countMailsSkip = 0;
		$countMailsError = 0;

		//Récupère la liste des stagiaires qui ont participé à une session de formation $nbmonth mois avant aujourd'hui
		$sql = /** @lang MySQL */ 'select s.rowid, ss.fk_session_agefodd from ' . MAIN_DB_PREFIX . 'agefodd_stagiaire as s '
			. ' inner join ' . MAIN_DB_PREFIX . 'agefodd_session_stagiaire as ss on s.rowid = ss.fk_stagiaire '
			. ' inner join ' . MAIN_DB_PREFIX . 'agefodd_session as ages on ages.rowid = ss.fk_session_agefodd '
			. ' where ages.datef <= \'' .$this->db->idate($dateAgo) . '\' '
			. ' AND ages.send_survey_status IN (1)'
			. ' ORDER BY ss.fk_session_agefodd ASC '
		;

		$resTrainee = $this->db->query($sql);
		if ($resTrainee === false) {
			$this->output = $this->db->lasterror();
			return 1;
		}

		//Envois le modèle de mail $mailModelId modifiable à ces stagiaires avec $userID en émetteur

		//Pour chaque stagiaire concerné
		while ($objTrainee = $this->db->fetch_object($resTrainee)) {
			$countMailsToSend++;
			$trainee = new Agefodd_stagiaire($this->db);
			$agsession = new Agsession($this->db);
			if (($trainee->fetch($objTrainee->rowid) > 0) && ($agsession->fetch($objTrainee->fk_session_agefodd) > 0)) {

				if($agsession->send_survey_status ==  Agsession::SURVEY_STATUS_DO_NOT_SEND){
					// Normalement il n'y a pas besoin de faire ça, mais bon, qui me garantit que le statut ne va pas passer à ne pas envoyer pendant le traitement.
					$countMailsSkip++;
					continue;
				}

				$res = $this->sendMail($trainee, $mailModelId, $userID, $agsession);
				if ($res > 0) {
					$countMailsSent++;
					if($agsession->send_survey_status != Agsession::SURVEY_STATUS_SENT){
						// La logique voudrait que je ne mette le statut à jour qu'une fois tous les mails d'une session envoyés,
						// mais actuellement aucun suivi d'envoi au cas par cas n'existe. Je ne peux donc pas connaître les stagiaires qui ont déjà reçu un questionnaire.
						// Par conséquent, il est plus sécurisé de mettre à jour le statut directement à la première occurrence pour éviter en cas de bug d'envoyer en boucle aux premiers de la liste
						// à chaque lancement du cron
						$agsession->setSendSurveyStatus(Agsession::SURVEY_STATUS_SENT);
					}
				}
				else {
					$countMailsError++;
					$this->output .= '<br/><a href="' . dol_buildpath('/agefodd/trainee/card.php', 2) . '?id=' . $trainee->id . '">' . $trainee->ref . '</a> ' . $langs->trans('AgfCantSendMail');
				}
			}
		}

		$this->output =
			'Nb mail To send : ' . $countMailsToSend .'<br/>'
			. 'Nb mail sent : ' . $countMailsSent .'<br/>'
			. 'Nb mail skip : ' . $countMailsSkip .'<br/>'
			. 'Nb mail error : ' . $countMailsError .'<br/>'
			. $this->output;

		if($countMailsError>0){
			$this->error = 'Nb mail error : ' . $countMailsError;
			return 1;
		}

		return 0;
	}

	/**
	 * @param Agefodd_stagiaire $trainee
	 * @param int $fk_model
	 * @param int $userID
	 * @param Agsession $agsession
	 * @return int
	 */
	public function sendMail(Agefodd_stagiaire &$trainee, $fk_model, $userID, Agsession $agsession)
	{
		global $langs, $conf;
		require_once (DOL_DOCUMENT_ROOT .'/core/class/CMailFile.class.php');
		dol_include_once('agefodd/lib/agf_externalaccess.lib.php');

		$langs->load('mails');
		$message = '';
		$sended = 0;
		$errors = 0;
		$invalidEmailAdress = 0;
		$disabledMContact = 0;

		// PREPARE EMAIL
		$user = new User($this->db);
		$resUser = $user->fetch($userID);
		if ($resUser > 0) {
			$from = getExternalAccessSendEmailFrom($user->email);
		}

		$replyto = $user->email;
		$errors_to = !empty($conf->global->MAIN_MAIL_ERRORS_TO) ? $conf->global->MAIN_MAIL_ERRORS_TO : '';

		if(empty($fk_model) && !empty($conf->global->AGF_SENDAGENDATOTRAINEE_DEFAULT_MAILMODEL)) {
			$fk_model = $conf->global->AGF_SENDAGENDATOTRAINEE_DEFAULT_MAILMODEL;
		}

		$mailTpl = agf_getMailTemplate($fk_model);
		if ($mailTpl < 1) {
			$this->output = $langs->trans('TemplateNotExist');
			return -1;
		}

		if (! isset($arrayoffamiliestoexclude)) $arrayoffamiliestoexclude=null;

		// Make substitution in email content
		$substitutionarray = getCommonSubstitutionArray($langs, 0, $arrayoffamiliestoexclude, $agsession);

		complete_substitutions_array($substitutionarray, $langs, $agsession);

		$thisSubstitutionarray = $substitutionarray;

		$thisSubstitutionarray['agfsendall_nom'] = $trainee->nom;
		$thisSubstitutionarray['agfsendall_prenom'] = $trainee->prenom;
		$thisSubstitutionarray['agfsendall_civilite'] = $trainee->civilite;
		$thisSubstitutionarray['agfsendall_socname'] = $trainee->socname;
		$thisSubstitutionarray['agfsendall_email'] = $trainee->email;

		// Add ICS link replacement to mails
		$downloadIcsLink = dol_buildpath('public/agenda/agendaexport.php', 2).'?format=ical&type=event';
		$thisSubstitutionarray['AGENDAICS'] = $downloadIcsLink.'&amp;agftraineeid='.$trainee->id;
		$thisSubstitutionarray['AGENDAICS'].= '&exportkey='.md5($conf->global->MAIN_AGENDA_XCAL_EXPORTKEY.'agftraineeid'.$trainee->id);

		// Tableau des substitutions
		if (!empty($agsession->intitule_custo)) {
			$thisSubstitutionarray['__FORMINTITULE__'] = $agsession->intitule_custo;
		} else {
			$thisSubstitutionarray['__FORMINTITULE__'] = $agsession->formintitule;
		}

		$date_conv = $agsession->libSessionDate('daytext');
		$thisSubstitutionarray['__FORMDATESESSION__'] = $date_conv;

		//Send mail
		$sendTopic =make_substitutions($mailTpl->topic, $thisSubstitutionarray);
		$sendContent =make_substitutions($mailTpl->content, $thisSubstitutionarray);

		$to = $trainee->mail;
		if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
			// is not a valid email address
			$invalidEmailAdress ++;
			//continue;
		}

        if (!empty($conf->global->MAIN_MAIL_ADD_INLINE_IMAGES_IF_DATA)) {
            $upload_dir_tmp = DOL_DATA_ROOT.'/mail/img';
            $cMailFile = new CMailFile($sendTopic, $to, $from, $sendContent, array(), array(), array(), "", "",  0, 1, $errors_to, '', '', '', getExternalAccessSendEmailContext(), $replyto, $upload_dir_tmp);
        }
        else {
            $cMailFile = new CMailFile($sendTopic, $to, $from, $sendContent, array(), array(), array(), "", "",  0, 1, $errors_to, '', '', '', getExternalAccessSendEmailContext(), $replyto);
        }

		if($cMailFile->sendfile()){
			$sended++;

			$actionmsg = $langs->trans('MailSentBy').' '.$from.' '.$langs->trans('To').' '.$to.".\n";
			if($sendContent) {
				$actionmsg .= $langs->trans('MailTopic').': '.$sendTopic."\n";
				$actionmsg .= $langs->trans('TextUsedInTheMessageBody').":\n";
				$actionmsg .= $sendContent;
			}

			//Creation de l'évènement agenda
			include_once DOL_DOCUMENT_ROOT . '/comm/action/class/actioncomm.class.php';
			$actioncomm = new ActionComm($this->db);
			$actioncomm->type_code = 'AC_EMAIL';
			$actioncomm->label = $langs->trans('CronTaskCompletionMailTrainee').' ('.$agsession->id.')';
			$actioncomm->note = $actionmsg;
			$actioncomm->datep = dol_now();
			$actioncomm->datef = dol_now();
			$actioncomm->durationp = 0;
			$actioncomm->punctual = 1;
			$actioncomm->percentage = -1; // Not applicable
			if(intval(DOL_VERSION) < 13) $actioncomm->contactid = $agsession->sendtoid;
			else $actioncomm->contact_id = $agsession->sendtoid;
			$actioncomm->socid = $agsession->socid;
			$actioncomm->author = $user;
			$actioncomm->userdone = $user; // User doing action
			$actioncomm->fk_element = $agsession->id;
			$actioncomm->elementtype = $agsession->element;
			$actioncomm->userownerid = $user->id;

			$ret = method_exists($actioncomm, 'create') ? $actioncomm->create($user) : $actioncomm->add($user);
		}
		else{
			$message.=  $cMailFile->error .' : '.$to;
			$errors++;
		}

		if (empty($errors)) {
			return 1;
		} else {
			return -1;
		}

	}

}
