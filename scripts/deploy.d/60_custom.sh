### Drupal 10: Directives pour la mise en place personnalisée du site Drupal.
###
### Variables:
### - DIR_DOCROOT
### - BIN_DRUSH
### - DB_STATUS

deploy_drupal_custom() {
	[ -n "${DB_STATUS}" ] || return 0
	[ -n "${BIN_DRUSH}" ] || return 0
	cd "${DIR_DOCROOT}"
	
	p_section "Drupal personnalisations..."
	${BIN_DRUSH} pm:install openig_config
#	${BIN_DRUSH} pm:install 
	
}
deploy_drupal_custom
