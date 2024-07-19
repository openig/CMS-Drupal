### Drupal 10: Installation / mise à jour des dépendances PHP.
###
### Variables:
### - DIR_HOSTING
### - DIR_DOCROOT
### - CMD_COMPOSER
### - SOLR_NAME

###
### Création initiale du projet Drupal avec Composer
###
init_d10_composer_create() {
	cd "${DIR_HOSTING}"
	[ -f "./composer.json" ] && return 0
	[ -n "${CMD_COMPOSER}" ] || return 0
	
	p_section "Création initiale du projet Drupal avec Composer..."
	${CMD_COMPOSER} create-project "drupal/recommended-project" /tmp/drupal
	tar cf - --one-file-system -C "/tmp/drupal" . | tar xf -
	
	p_section "Configuration de composer pour le projet..."
	${CMD_COMPOSER} config "bin-dir" "bin"
#	${CMD_COMPOSER} config extra.allow-plugins."composer/installers" true
#	${CMD_COMPOSER} config extra.allow-plugins."drupal/core-composer-scaffold" true
#	${CMD_COMPOSER} config extra.allow-plugins."phpstan/extension-installer" true
#	${CMD_COMPOSER} config extra.allow-plugins."dealerdirect/phpcodesniffer-composer-installer" true
#	${CMD_COMPOSER} config extra.allow-plugins."cweagans/composer-patches" true
	
	p_section "Configuration des paquets à installer par Composer..."
	[ -d "./vendor/bin" ] && rm -rf "./vendor/bin"
	${CMD_COMPOSER} remove  "drupal/core-project-message"
	${CMD_COMPOSER} require "drush/drush"
	[ -z "${SOLR_NAME}" ] || ${CMD_COMPOSER} require "drupal/search_api_solr"
	
	return 0
}
init_d10_composer_create

###
### Installation des dépendances du projet avec Composer
###
init_d10_composer_install() {
	cd "${DIR_HOSTING}"
	[ -f "./composer.json" ] || return 0
	[ -n "${CMD_COMPOSER}" ] || return 0
	
	## ouverture des droits en écriture pour installations & mises à jour
	[ -d "./vendor"       ] && chmod -R u+w "./vendor"       2>/dev/null || true
	[ -d "./bin"          ] && chmod -R u+w "./bin"          2>/dev/null || true
	[ -d "${DIR_DOCROOT}" ] && chmod -R u+w "${DIR_DOCROOT}" 2>/dev/null || true
	
	## installation des vendors du projet Drupal
	p_section "Installation des dépendances avec composer..."
	${CMD_COMPOSER} install --optimize-autoloader
	
	return 0
}
init_d10_composer_install
