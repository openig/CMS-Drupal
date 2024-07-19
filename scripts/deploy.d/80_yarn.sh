### Drupal 10: Gestion des dépendances frontend (NPM) du projet web (construction du thème).
###
### Variables:
### - DIR_DOCROOT
### - CMD_NODEYARN
### - DRUPAL_THEME
### - RUN_YARN
###
### Provides:
### - DIR_THEME

### Emplacement du thème Drupal
[ -z "${DRUPAL_THEME}" ] || DIR_THEME="${DIR_DOCROOT}themes/custom/${DRUPAL_THEME}"

init_d10_theme_install() {
	[ -n "${DRUPAL_THEME}" ] || return 0
	[ -n "${CMD_NODEYARN}" ] || return 0
	[ -f "${DIR_THEME}/package.json" ] || return 0
	cd "${DIR_THEME}"
	
	## ouverture des droits en écriture pour installations & mises à jour
	chmod -R u+w "${DIR_THEME}" 2>/dev/null || true
	
	## définition des commandes yarn utilisées
#	p_section "Scripts yarn personnalisés (dep-install, dep-check) et adaptations (build, dev, watch, dev-server)"
#	jq --indent 4 '
#		.scripts."dep-install" = "yarn install --non-interactive --frozen-lockfile --check-files" |
#		.scripts."dep-check"   = "yarn check --non-interactive --verify-tree --integrity" |
#		.scripts."build"       = "gulp" |
#		.scripts."watch"       = "gulp watch"
#	' ./package.json > ./package.json.tmp && mv ./package.json.tmp ./package.json
	
	## installation, vérification et construction
	p_section "Installation des dépendances frontend (paquets npm)"
	${CMD_NODEYARN} dep-install || ${CMD_NODEYARN} install --frozen-lockfile --check-files || ${CMD_NODEYARN} install --check-files
	
	p_section "Vérification des versions du yarn.lock (par rapport au packages.json)"
	${CMD_NODEYARN} dep-check   || ${CMD_NODEYARN} check --verify-tree --integrity
}
init_d10_theme_install

### Définition de la commande à lancer par le conteneur (sauf si déjà forcée)
### pour lancer la compilation du theme et ses assets avec les commandes yarn (selon RUN_YARN)
###
### NOTE: les actions restant en écoute sont spécifiées dans la variable ARG_ENTRYPOINT
###       afin de devenir la commande finale du conteneur (modes dev server|watch),
###       tandis que celles rendant la main sont exécutées directement (mode prod)
init_d10_theme_build() {
	[ -n "${DRUPAL_THEME}" ] || return 0
	[ -n "${CMD_NODEYARN}" ] || return 0
	[ -f "${DIR_THEME}/package.json" ] || return 0
	
	## ouverture des droits en écriture pour compilations des assets
	chmod -R u+w "${DIR_THEME}" 2>/dev/null || true
	
	ARG_NODEYARN="$(echo "${CMD_NODEYARN}" | tr " " "\t")	--cwd	${DIR_THEME}"
	case "${RUN_YARN:-none}" in
		d* | s* | h*  ) ## serveur pour compilations à chaud
			[ -n "${ARG_ENTRYPOINT}" ] && p_warning "Aucune (re)compilation des assets : un autre service doit être lancé" && return 0
#			ARG_ENTRYPOINT="${ARG_NODEYARN}	dev-server"
#			ARG_ENTRYPOINT="${ARG_NODEYARN}	hot"
			ARG_ENTRYPOINT="${ARG_NODEYARN}	${RUN_YARN}"
		;;
		w*            ) ## (re)compilations auto en mode dev
			[ -n "${ARG_ENTRYPOINT}" ] && p_warning "Aucune (re)compilation des assets : un autre service doit être lancé" && return 0
#			ARG_ENTRYPOINT="${ARG_NODEYARN}	watch"
			ARG_ENTRYPOINT="${ARG_NODEYARN}	${RUN_YARN}"
		;;
		p* | b*       ) ## compilation statique en mode prod
#			${CMD_NODEYARN} --cwd "${DIR_THEME}" build
#			${CMD_NODEYARN} --cwd "${DIR_THEME}" production
			${CMD_NODEYARN} --cwd "${DIR_THEME}" ${RUN_YARN}
		;;
		n*            ) ## fonctionnalité non activée
			p_warning "Aucune (re)compilation des assets : aucun mode n'est configuré"
		;;
		*             ) ## configuration invalide
			p_warning "Aucune (re)compilation des assets : mode configuré non supporté '${RUN_YARN}'"
		;;
	esac
}
init_d10_theme_build
