### Drupal 10: Installation de l'instance Drupal (selon état de la DB).
###
### Variables:
### - DIR_HOSTING
### - DIR_DOCROOT
### - (DRUPAL_LANG)
### - (DRUPAL_NAME)
### - (DRUPAL_MAIL)
### - (DRUPAL_USER)
### - (DRUPAL_MAIL)
### - (DRUPAL_PASS)
###
### Provides:
### - BIN_DRUSH
### - DB_STATUS

[ -n "${BIN_DRUSH}" ] || BIN_DRUSH="${DIR_HOSTING}/bin/drush"
[ -x "${BIN_DRUSH}" ] || BIN_DRUSH="$(which drush 2>/dev/null || true)"
[ -z "${BIN_DRUSH}" ] || BIN_DRUSH="${BIN_DRUSH} -y"

DB_STATUS=

###
### Afficher l'état de l'instance Drupal
###
deploy_drupal_status() {
	cd "${DIR_DOCROOT}"
	
	## fin du script d'init si la commande drush n'est pas disponible
	[ -z "${BIN_DRUSH}" ] && p_warning "Commande Drush non disponible (aucune configuration Drupal effectuée) !" && return 0
	
	p_section "Nettoyage complet des caches de l'instance Drupal..."
	${BIN_DRUSH} cache:rebuild 2>/dev/null || true
	
	## afficher l'état de l'instance Drupal
	p_section "Drupal core status :"
	${BIN_DRUSH} core:status || true
	DB_STATUS="$(${BIN_DRUSH} core:status --fields=db-status || true)"
	p_section "Drupal db status : '${DB_STATUS}'"
	
	return 0
}
deploy_drupal_status

###
### Installation de l'instance (équivalent du wizard web)
###
deploy_drupal_install() {
	[ -n "${BIN_DRUSH}" ] || return 0
	[ -z "${DB_STATUS}" ] || return 0
	cd "${DIR_DOCROOT}"
	
	p_section "Drupal initial installation..."
	chmod u+w "${DIR_SETTINGS}" "${DIR_SETTINGS}/settings".* 2>/dev/null || true
	
	${BIN_DRUSH} site:install 'standard' \
	 --locale="${DRUPAL_LANG:-fr}" \
	 --site-name="${DRUPAL_NAME:-Drupal 10 website}" \
	 --site-mail="${DRUPAL_MAIL:-website@example.org}" \
	 --account-name="${DRUPAL_USER:-admin}" \
	 --account-mail="${DRUPAL_MAIL:-webmaster@example.org}" \
	 --account-pass="${DRUPAL_PASS:-admin}" \
	 install_configure_form.enable_update_status_emails=NULL
	
	## note: le fichier settings.php a pu être modifier par le script d'installation de Drupal...
	chmod u+w "${DIR_SETTINGS}" "${DIR_SETTINGS}/settings".* 2>/dev/null || true
	printf "<?php\n" > "${DIR_SETTINGS}/settings.php"
	printf "\n### Paramètres par défaut fournis par le projet Drupal\n"     >> "${DIR_SETTINGS}/settings.php"
	printf "include_once __DIR__.'/default.settings.php';\n"                >> "${DIR_SETTINGS}/settings.php"
	printf "\n### Paramètres spécifiques du projet (fichier versionné)\n"   >> "${DIR_SETTINGS}/settings.php"
	printf "require_once __DIR__.'/settings.project.php';\n"                >> "${DIR_SETTINGS}/settings.php"
	printf "\n### Paramètres locaux de l'instance (non versionné)\n"        >> "${DIR_SETTINGS}/settings.php"
	printf "include_once __DIR__.'/settings.local.php';\n"                  >> "${DIR_SETTINGS}/settings.php"
	chmod u+w "${DIR_SETTINGS}" "${DIR_SETTINGS}/settings".* 2>/dev/null || true
	
	## nouvelle recherche de l'état de la db (devrait être opérationnelle)
	deploy_drupal_status
	return 0
}
deploy_drupal_install

###
### Update de l'instance
###
deploy_drupal_update() {
	[ -n "${BIN_DRUSH}" ] || return 0
	[ -n "${DB_STATUS}" ] || return 0
	cd "${DIR_DOCROOT}"
	
	p_section "Drupal apply updates (DB)..."
	${BIN_DRUSH} updatedb:status
	${BIN_DRUSH} updatedb
	
	## Todo autres imports ? (traduction)
	return 0
}
deploy_drupal_update
