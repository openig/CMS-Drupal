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
init_d10_conf_cron() {
	cd "${DIR_DOCROOT}"
	
	p_section "Drupal CRON disabled..."
	${BIN_DRUSH} cset 'automated_cron.settings' 'interval' 0
}

###
### Reparamétrage du moteur d'indexation
###
init_d10_conf_search() {
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
init_d10_conf_mailer() {
	[ -n "${SMTP_HOST}" ] || return 0
	cd "${DIR_DOCROOT}"
	
	p_section "Drupal SMTP initialisation..."
	MAIL_SYSTEM="$(${BIN_DRUSH} pm:list --type=module --status=enabled --package=Mail --filter=mailer --field=name 2>/dev/null | head -n1 || true)"
	[ -n "${MAIL_SYSTEM}" ] || MAIL_SYSTEM="$(${BIN_DRUSH} cget --format=string  mailsystem.settings      defaults.sender    2>/dev/null || true)"
#	[ -n "${MAIL_SYSTEM}" ] || MAIL_SYSTEM="$(${BIN_DRUSH} cget --format=string  system.mail              interface.default  2>/dev/null || true)"
	case "${MAIL_SYSTEM}" in
		'symfony_mailer'*  )  ## Symfony Mailer
			MAIL_TRANSPORT="$(${BIN_DRUSH} cget --format=string symfony_mailer.settings default_transport)"
			case "${MAIL_TRANSPORT}" in
				'smtp'*    )  ## SMTP
					MAIL_CONF="{ verify_peer: false, local_domain: '', restart_threshold: null, restart_threshold_sleep: null, ping_threshold: null }"
					MAIL_CONF="{ user: '${SMTP_USER}', pass: '${SMTP_PASS}', host: '${SMTP_HOST}', port: ${SMTP_PORT:-null}, query: ${MAIL_CONF} }"
					${BIN_DRUSH} cset --input-format=yaml "symfony_mailer.mailer_transport.${MAIL_TRANSPORT}" configuration "${MAIL_CONF}"
					p_success "Drupal mailsystem using 'SymfonyMailer+smtp': configuration done"
				;;
				'sendmail'*)  ## Sendmail (cf $settings['mailer_sendmail_commands'])
					MAIL_CONF="{ query: { command: '/usr/sbin/sendmail' } }"
					${BIN_DRUSH} cset --input-format=yaml "symfony_mailer.mailer_transport.${MAIL_TRANSPORT}" configuration "${MAIL_CONF}"
					p_success "Drupal mailsystem using 'SymfonyMailer+sendmail': configuration done"
				;;
				'nati'*    )  ## PHP (native|natif)
					# send all e-mails using the built-in mail functionality of PHP
					# not configured here: please refer to the PHP documentation
					p_success "Drupal mailsystem using 'SymfonyMailer+native': nothing to configure here"
				;;
				* )
					p_warning "Drupal mailsystem unknown SymfonyMailer transport: nothing configured here"
				;;
			esac
		;;
		'swiftmailer'*     )  ## Swift Mailer
			MAIL_TRANSPORT="$(${BIN_DRUSH} cget --format=string swiftmailer.transport transport)"
			case "${MAIL_TRANSPORT}" in
				'smtp'*    )  ## SMTP
					${BIN_DRUSH} cset --input-format=yaml swiftmailer.transport smtp_host     "'${SMTP_HOST}'"
					${BIN_DRUSH} cset --input-format=yaml swiftmailer.transport smtp_port     "'${SMTP_PORT}'"
					${BIN_DRUSH} cset --input-format=yaml swiftmailer.transport smtp_username "'${SMTP_USER}'"
					${BIN_DRUSH} cset --input-format=yaml swiftmailer.transport smtp_password "'${SMTP_PASS}'"
					${BIN_DRUSH} cset --input-format=yaml swiftmailer.transport smtp_encryption "'0'" ## 0|ssl|tls
					p_success "Drupal mailsystem using 'SwiftMailer+smtp': configuration done"
				;;
				'sendmail'*)  ## Sendmail
					${BIN_DRUSH} cset --input-format=string swiftmailer.transport sendmail_path '/usr/sbin/sendmail'
					${BIN_DRUSH} cset --input-format=string swiftmailer.transport sendmail_mode 'bs'  ## bs|t
					p_success "Drupal mailsystem using 'SwiftMailer+sendmail': configuration done"
				;;
				'nati'*    )  ## PHP
					# send all e-mails using the built-in mail functionality of PHP
					# not configured here: please refer to the PHP documentation
					p_success "Drupal mailsystem using 'SwiftMailer+native': nothing to configure here"
				;;
				'spool'*   )  ## Spool
					# saves the message to a spool file
					# another process can then read from the spool and take care of sending the emails
					p_success "Drupal mailsystem using 'SwiftMailer+spool': nothing to configure here"
				;;
				* )
					p_warning "Drupal mailsystem unknown SwiftMailer transport: nothing configured here"
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
		'sendmail'*)
			p_warning "Drupal mailsystem using 'sendmail': nothing configured here"
		;;
		* )
			p_warning "Drupal mailsystem UNKNOWN! (nothing configured here)"
		;;
	esac
	return 0
}

################################################################################ MAIN

init_d10_configure() {
	## fin du script d'init si la commande drush n'est pas disponible
	[ -z "${BIN_DRUSH}" ] && p_warning "Commande Drush non disponible (aucune configuration Drupal effectuée) !" && return 0
	## fin du script d'init si la base de données n'est pas initialisée
	[ -z "${DB_STATUS}" ] && p_warning "Base de données non disponible (aucune configuration Drupal effectuée) !" && return 0
	
	init_d10_conf_cron
	init_d10_conf_search
	init_d10_conf_mailer
	
	return 0
}
init_d10_configure
