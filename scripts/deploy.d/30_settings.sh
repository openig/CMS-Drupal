### Drupal 10: Fichiers de configuration de l'instance pour Docker.
###
### Variables:
### - DIR_HOSTING
### - DIR_DOCROOT
### - (DIR_STARTER)
### - (USR_WORKER)
### - (GRP_WORKER)
### - (DIR_DRUPAL_CONF)
### - (DIR_DRUPAL_PRIV)
### - (DIR_DRUPAL_TEMP)
###
### Provides:
### - DIR_SETTINGS

DIR_SETTINGS="${DIR_DOCROOT}sites/default"

[ -n "${DIR_DRUPAL_CONF}" ] || DIR_DRUPAL_CONF="${DIR_HOSTING}/configs"
[ -n "${DIR_DRUPAL_PRIV}" ] || DIR_DRUPAL_PRIV="${DIR_HOSTING}/private"
[ -n "${DIR_DRUPAL_TEMP}" ] || DIR_DRUPAL_TEMP="${DIR_HOSTING}/temp"

#[ -d "${DIR_DRUPAL_CONF}" ] || DIR_DRUPAL_CONF=""          ## non facultatif (utilisé pour sync, solr, salt)
[ -d "${DIR_DRUPAL_PRIV}" ] || DIR_DRUPAL_PRIV=""
[ -d "${DIR_DRUPAL_TEMP}" ] || DIR_DRUPAL_TEMP=""

###
### Mise en place de l'arborescence pour le projet Drupal
###
init_d10_folders() {
	TPL_HTACCESS="${DIR_STARTER:-.}/htaccess.protected" ; [ -f "${TPL_HTACCESS}" ] || TPL_HTACCESS=""
	
	## création des dossiers configurés pour Drupal
	p_section "Mise en place de l'arborescence pour le projet Drupal..."
	if [ -n "${DIR_DRUPAL_CONF}" ]; then
		[ -d "${DIR_DRUPAL_CONF}" ] || mkdir -p "${DIR_DRUPAL_CONF}"
		[ -z "${TPL_HTACCESS}" ] || cp "${TPL_HTACCESS}" "${DIR_DRUPAL_CONF}/.htaccess"
	fi
	if [ -n "${DIR_DRUPAL_PRIV}" ]; then
		[ -d "${DIR_DRUPAL_PRIV}" ] || mkdir -p "${DIR_DRUPAL_PRIV}"
		[ -z "${TPL_HTACCESS}" ] || cp "${TPL_HTACCESS}" "${DIR_DRUPAL_PRIV}/.htaccess"
	fi
	if [ -n "${DIR_DRUPAL_TEMP}" ]; then
		[ -d "${DIR_DRUPAL_TEMP}" ] || mkdir -p "${DIR_DRUPAL_TEMP}"
		[ -z "${TPL_HTACCESS}" ] || cp "${TPL_HTACCESS}" "${DIR_DRUPAL_TEMP}/.htaccess"
	fi
	[ -d "${DIR_SETTINGS}/files" ] || mkdir -p "${DIR_SETTINGS}/files"
	
	## génération du sel aléatoire dans un fichier
	if [ ! -f "${DIR_DRUPAL_CONF}/salt.txt" ]; then
		p_section "Génération du fichier contenant le sel cryptographique de l'instance..."
		head -c75 /dev/urandom | base64 -w0 > "${DIR_DRUPAL_CONF}/salt.txt"
	fi
}
init_d10_folders

###
### Mise en place des fichiers de settings de Drupal (includes)
###
init_d10_settings() {
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
}
init_d10_settings

init_d10_fixperms() {
	cd "${DIR_HOSTING}"
	
	## autorisations en écriture par l'accès web
	if [ -n "${DIR_DRUPAL_CONF}" -a -d "${DIR_DRUPAL_CONF}/sync" ]; then
		chown "${USR_WORKER:-www-data}" -R "${DIR_DRUPAL_CONF}/sync" || p_warning "chown failed!"  # non-critical
		chgrp "${GRP_WORKER:-www-data}" -R "${DIR_DRUPAL_CONF}/sync" || p_warning "chgrp failed!"  # non-critical
		chmod u+rwX,g+rwX               -R "${DIR_DRUPAL_CONF}/sync" || p_warning "chmod failed!"  # non-critical
	fi
	if [ -n "${DIR_DRUPAL_PRIV}" -a -d "${DIR_DRUPAL_PRIV}" ]; then
		chown "${USR_WORKER:-www-data}" -R "${DIR_DRUPAL_PRIV}" || p_warning "chown failed!"  # non-critical
		chgrp "${GRP_WORKER:-www-data}" -R "${DIR_DRUPAL_PRIV}" || p_warning "chgrp failed!"  # non-critical
		chmod u+rwX,g+rwX               -R "${DIR_DRUPAL_PRIV}" || p_warning "chmod failed!"  # non-critical
	fi
	if [ -n "${DIR_DRUPAL_TEMP}" -a -d "${DIR_DRUPAL_TEMP}" ]; then
		chown "${USR_WORKER:-www-data}" -R "${DIR_DRUPAL_TEMP}" || p_warning "chown failed!"  # non-critical
		chgrp "${GRP_WORKER:-www-data}" -R "${DIR_DRUPAL_TEMP}" || p_warning "chgrp failed!"  # non-critical
		chmod u+rwX,g+rwX               -R "${DIR_DRUPAL_TEMP}" || p_warning "chmod failed!"  # non-critical
	fi
	if [ -n "${DIR_SETTINGS}/files" -a -d "${DIR_SETTINGS}/files" ]; then
		chown "${USR_WORKER:-www-data}" -R "${DIR_SETTINGS}/files" || p_warning "chown failed!"  # non-critical
		chgrp "${GRP_WORKER:-www-data}" -R "${DIR_SETTINGS}/files" || p_warning "chgrp failed!"  # non-critical
		chmod u+rwX,g+rwX               -R "${DIR_SETTINGS}/files" || p_warning "chmod failed!"  # non-critical
	fi
	
	## remise des protections en écriture
	chown "${USR_WORKER:-www-data}" "${DIR_SETTINGS}/"settings*.php || p_warning "chown failed!"  # non-critical
	chgrp "${GRP_WORKER:-www-data}" "${DIR_SETTINGS}/"settings*.php || p_warning "chgrp failed!"  # non-critical
	chmod 0440                      "${DIR_SETTINGS}/"settings*.php || p_warning "chmod failed!"  # non-critical
	chmod a-w                       "${DIR_SETTINGS}/"              || p_warning "chmod failed!"  # non-critical
}
init_d10_fixperms
