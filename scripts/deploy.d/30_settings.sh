### Drupal 10: Fichiers de configuration de l'instance pour Docker.
###
### Variables:
### - DIR_HOSTING
### - DIR_DOCROOT
### - (DIR_STARTER)
### - (USR_WORKER)
### - (GRP_WORKER)
###
### Provides:
### - DIR_SETTINGS

DIR_SETTINGS="${DIR_DOCROOT}/sites/default"

###
### Mise en place de l'arborescence pour le projet Drupal
###
deploy_folders() {
	p_section "Mise en place de l'arborescence pour le projet Drupal..."
	
	## création des dossiers configurés pour Drupal
	[ -d "${DIR_HOSTING}/temp"         ] || mkdir -p "${DIR_HOSTING}/temp"
	[ -d "${DIR_HOSTING}/configs/sync" ] || mkdir -p "${DIR_HOSTING}/configs/sync"
	[ -d "${DIR_HOSTING}/private"      ] || mkdir -p "${DIR_HOSTING}/private"
	[ -d "${DIR_SETTINGS}/files"       ] || mkdir -p "${DIR_SETTINGS}/files"
	
	## ajout des .htaccess pour protéger les dossiers non-public
	if [ -f "${DIR_STARTER:-.}/htaccess.protected" ]; then
		[ -f "${DIR_HOSTING}/temp/.htaccess"    ] || cp "${DIR_STARTER:-.}/htaccess.protected" "${DIR_HOSTING}/temp/.htaccess"
		[ -f "${DIR_HOSTING}/configs/.htaccess" ] || cp "${DIR_STARTER:-.}/htaccess.protected" "${DIR_HOSTING}/configs/.htaccess"
		[ -f "${DIR_HOSTING}/private/.htaccess" ] || cp "${DIR_STARTER:-.}/htaccess.protected" "${DIR_HOSTING}/private/.htaccess"
	fi
	
	## génération du sel aléatoire dans un fichier
	[ -f "${DIR_HOSTING}/configs/salt.txt" ] || head -c74 /dev/urandom | base64 -w0 > "${DIR_HOSTING}/configs/salt.txt"
}
deploy_folders

###
### Mise en place des fichiers de settings de Drupal (includes)
###
deploy_settings() {
	p_section "Mise en place des fichiers de configuration de Drupal..."
	chmod u+w "${DIR_SETTINGS}" "${DIR_SETTINGS}/settings".* 2>/dev/null || true
	
#	if [ ! -e "${DIR_SETTINGS}/settings.php" ]; then
		printf "<?php\n" > "${DIR_SETTINGS}/settings.php"
		printf "\n### Paramètres par défaut fournis par le projet Drupal\n"     >> "${DIR_SETTINGS}/settings.php"
		printf "include_once __DIR__.'/default.settings.php';\n"                >> "${DIR_SETTINGS}/settings.php"
		printf "\n### Paramètres spécifiques du projet (fichier versionné)\n"   >> "${DIR_SETTINGS}/settings.php"
		printf "require_once __DIR__.'/settings.project.php';\n"                >> "${DIR_SETTINGS}/settings.php"
		printf "\n### Paramètres locaux de l'instance (non versionné)\n"        >> "${DIR_SETTINGS}/settings.php"
		printf "include_once __DIR__.'/settings.local.php';\n"                  >> "${DIR_SETTINGS}/settings.php"
#	fi
	
	if [ -f "${DIR_STARTER:-.}/settings.project.php" -a ! -e "${DIR_SETTINGS}/settings.project.php" ]; then
		cp "${DIR_STARTER:-.}/settings.project.php" "${DIR_SETTINGS}/settings.project.php"
	fi
	if [ -f "${DIR_STARTER:-.}/settings.local.php" ]; then
		cp "${DIR_STARTER:-.}/settings.local.php" "${DIR_SETTINGS}/settings.local.php"
	fi
	
	return 0
}
deploy_settings

deploy_fixperms() {
	cd "${DIR_HOSTING}"
	
	## remise des protections en écriture
	chown "${USR_WORKER:-www-data}" "${DIR_SETTINGS}/"settings*.php || p_warning "chown failed!"  # non-critical
	chgrp "${GRP_WORKER:-www-data}" "${DIR_SETTINGS}/"settings*.php || p_warning "chgrp failed!"  # non-critical
	chmod 0440                      "${DIR_SETTINGS}/"settings*.php || p_warning "chown failed!"  # non-critical
	chmod a-w                       "${DIR_SETTINGS}/"              || p_warning "chown failed!"  # non-critical
	
	## autorisations en écriture par l'accès web
#	"${DIR_HOSTING}/temp"
#	"${DIR_HOSTING}/private"
#	"${DIR_SETTINGS}/files"
	
#	"${DIR_HOSTING}/configs/sync"
	
	return 0
}
deploy_fixperms
