### Drupal 10: Fin de l'initialisation de l'instance Drupal.
###
### Variables:
### - DIR_HOSTING
### - DIR_DOCROOT
### - BIN_DRUSH
### - DB_STATUS
### - DRUPAL_USER
### - DRUPAL_PASS

init_d10_ready() {
	[ -n "${BIN_DRUSH}" ] || return 0
	cd "${DIR_DOCROOT}"
	
	p_section "Drupal cache reinitialisation..."
	${BIN_DRUSH} cache:rebuild
	
	p_section "Drupal instance informations..."
	${BIN_DRUSH} core:status
}
init_d10_ready

###
### Mise en place du compte administrateur
###
init_d10_account() {
	[ -n "${DRUPAL_USER}" ] || return 0
	[ -n "${DB_STATUS}"   ] || return 0
	[ -n "${BIN_DRUSH}"   ] || return 0
	
	p_section "Drupal administrator account..."
	cd "${DIR_DOCROOT}"
	
	${BIN_DRUSH} user:information "${DRUPAL_USER}" >/dev/null 2>&1 \
	 || ${BIN_DRUSH} user:create "${DRUPAL_USER}"
	
	[ -z "${DRUPAL_PASS}" ] || ${BIN_DRUSH} user:password "${DRUPAL_USER}" "${DRUPAL_PASS}"
	
	${BIN_DRUSH} user:role:add administrator "${DRUPAL_USER}"
	${BIN_DRUSH} user:unblock                "${DRUPAL_USER}"
}
init_d10_account
