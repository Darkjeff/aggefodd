AGEFODD
=========


Licence
-------

GPLv3 or (at your option) any later version. 

See COPYING for more information.

INSTALL AGEFODD
----------------------

	http://wiki.dolibarr.org/index.php/FAQ_Repertoire_Custom_Module_Externe
	http://wiki.dolibarr.org/index.php/FAQ_ModuleCustomDirectory


INSTALL FROM GIT (into "custom" directory)
---------------

	$git clone git@git.framasoft.org:atm-consulting/dolibarr_agefodd.git agefodd


FORCE re-install Database elements
---------------

If you have an install error and need to replay sql table creation script, 
you can remove all AGF_LAST_VERION_INSTALL const in llx_const and then activate the module.


DOCUMENTATION
---------------

	https://wiki.atm-consulting.fr/index.php/Nos_modules_Dolibarr#Agefodd
	

DOLISTORE
---------------

	https://www.dolistore.com/fr/modules/146-Agefodd---Complet.html
	https://www.dolistore.com/en/modules/146-Agefodd---Full.html


DOLIBARR WIKI PAGE
---------------

	https://wiki.dolibarr.org/index.php/Module_Agefodd
	

LIVE DEMO
---------------

	http://dolibarr.atm-consulting.fr/dolibarr_agefodd/htdocs/

HIDDEN CONFIGURATION
---------------

- AGF_CRENEAU_FORCE_EMAIL_TO
- "AGEFODD_CONVENTION_DOUBLE_DESC_DESACTIVATE" => Disables the display of a double description in PDF Convention Sessions


Other Licences
--------------

Uses [Michel Fortin's PHP Markdown](http://michelf.ca/projets/php-markdown/) Licensed under BSD to display this README in the module's about page.


CONTACT
-----------------------
Web site Agefodd project : 
	https://git.framasoft.org/atm-consulting/dolibarr_agefodd
	
ATM Consulting <contact@atm-consulting.fr>
