### Drupal 10: Configuration avec Drush pour Docker.
###
### Variables:
### - DIR_HOSTING
### - DIR_DOCROOT
### - BIN_DRUSH
### - DB_STATUS
### - (SOLR_HOST)
### - (SOLR_NAME)
### - (SOLR_CORE)
### - (SMTP_HOST)
### - (SMTP_PORT)
### - (SMTP_USER)
### - (SMTP_PASS)

###
### Désactivation de l'exécution automatique du cron
###
deploy_drupal_cron() {
	cd "${DIR_DOCROOT}"
	
	p_section "Drupal CRON disabled..."
	${BIN_DRUSH} cset 'automated_cron.settings' 'interval' 0
}

###
### Reparamétrage du moteur d'indexation
###
deploy_drupal_search() {
	cd "${DIR_DOCROOT}"
	
	if [ -n "${SOLR_HOST}" -o -n "${SOLR_NAME}" ]; then
		p_section "Drupal Search installation..."
		${BIN_DRUSH} pm:enable search_api_solr
#		${BIN_DRUSH} pm:enable search_api_solr_admin search_api_solr_devel search_api_solr_defaults search_api_solr_autocomplete
	fi
	
	if [ -n "${SOLR_NAME}" ]; then
		p_section "Drupal Search configuration..."
		if ${BIN_DRUSH} search-api:server-list --format yaml 2>&1 | grep -q "^${SOLR_NAME}:\$"
		then
			${BIN_DRUSH} cset "search_api.server.${SOLR_NAME}" 'backend_config.connector_config.scheme'       "${SOLR_HTTP:-http}"
			${BIN_DRUSH} cset "search_api.server.${SOLR_NAME}" 'backend_config.connector_config.host'         "${SOLR_HOST:-localhost}"
			${BIN_DRUSH} cset "search_api.server.${SOLR_NAME}" 'backend_config.connector_config.port'         "${SOLR_PORT:-8983}"
			${BIN_DRUSH} cset "search_api.server.${SOLR_NAME}" 'backend_config.connector_config.path'         "${SOLR_PATH:-/}"
			${BIN_DRUSH} cset "search_api.server.${SOLR_NAME}" 'backend_config.connector_config.core'         "${SOLR_CORE:-drupal_core}"
			${BIN_DRUSH} cset "search_api.server.${SOLR_NAME}" 'backend_config.connector_config.solr_version' "${SOLR_VERSION:-}"
			${BIN_DRUSH} search-api:server-list
		else
			p_warning "Serveur Solr '${SOLR_NAME}' non configuré dans Drupal !"
		fi
	fi
}

###
### Reparamétrage du module SMTP
###
deploy_drupal_mailer() {
	[ -n "${SMTP_HOST}" ] || return 0
	cd "${DIR_DOCROOT}"
	
	p_section "Drupal SMTP initialisation..."
#	MAIL_SYSTEM="$(${BIN_DRUSH} cget --format=string mailsystem.settings defaults.sender)"
	MAIL_SYSTEM="$(${BIN_DRUSH} cget --format=string system.mail interface.default)"
	case "${MAIL_SYSTEM}" in
		'swiftmailer*'     )  ## Swift Mailer
			MAIL_TRANSPORT="$(${BIN_DRUSH} cget --format=string swiftmailer.transport transport)"
			case "${MAIL_TRANSPORT}" in
				'smtp'     )  ## SMTP
					${BIN_DRUSH} cset --format=yaml swiftmailer.transport smtp_host     --value="'${SMTP_HOST}'"
					${BIN_DRUSH} cset --format=yaml swiftmailer.transport smtp_port     --value="'${SMTP_PORT}'"
					${BIN_DRUSH} cset --format=yaml swiftmailer.transport smtp_username --value="'${SMTP_USER}'"
					${BIN_DRUSH} cset --format=yaml swiftmailer.transport smtp_password --value="'${SMTP_PASS}'"
					${BIN_DRUSH} cset --format=yaml swiftmailer.transport smtp_encryption --value="'0'" ## 0|ssl|tls
					p_success "Drupal mailsystem using 'swiftmailer+smtp': configuration done"
				;;
				'sendmail' )  ## Sendmail
					${BIN_DRUSH} cset --format=string swiftmailer.transport sendmail_path --value='/usr/sbin/sendmail'
					${BIN_DRUSH} cset --format=string swiftmailer.transport sendmail_mode --value='t'   ## bs|t
					p_success "Drupal mailsystem using 'swiftmailer+sendmail': configuration done"
				;;
				'native'   )  ## PHP
					# send all e-mails using the built-in mail functionality of PHP
					# not configured here: please refer to the PHP documentation
					p_success "Drupal mailsystem using 'swiftmailer+native': nothing to configure here"
				;;
				'spool'    )  ## Spool
					# saves the message to a spool file
					# another process can then read from the spool and take care of sending the emails
					p_success "Drupal mailsystem using 'swiftmailer+spool': nothing to configure here"
				;;
				* )
					p_warning "Drupal mailsystem unknown swiftmailer: nothing configured here"
				;;
			esac
		;;
		'php_mail'         )  ## Serveur de courriel PHP par défaut
			p_warning "Drupal mailsystem using 'php_mail': nothing configured here"
					# send all e-mails using the built-in mail functionality of PHP
					# not configured here: please refer to the PHP documentation
		;;
		'webform_php_mail' )  ## Webform PHP mailer
			p_warning "Drupal mailsystem using 'webform': nothing configured here"
		;;
		* )
			p_warning "Drupal mailsystem unknown: nothing configured here"
		;;
	esac
	return 0
}

################################################################################ MAIN

deploy_drupal_configure() {
	## fin du script d'init si la commande drush n'est pas disponible
	[ -z "${BIN_DRUSH}" ] && p_warning "Commande Drush non disponible (aucune configuration Drupal effectuée) !" && return 0
	## fin du script d'init si la base de données n'est pas initialisée
	[ -z "${DB_STATUS}" ] && p_warning "Base de données non disponible (aucune configuration Drupal effectuée) !" && return 0
	
	deploy_drupal_cron
	deploy_drupal_search
	deploy_drupal_mailer
	
	return 0
}
deploy_drupal_configure
