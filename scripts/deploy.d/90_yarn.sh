### Drupal 10: Gestion des dépendances frontend (NPM) du projet web.
###
### Variables:
### - DIR_DOCROOT
### - CMD_NODEYARN
### - DRUPAL_THEME

deploy_theme_yarn() {
	[ -n "${DRUPAL_THEME}" ] || return 0
	[ -n "${CMD_NODEYARN}" ] || return 0
	
	## emplacement du thème Drupal
	local DIR_THEME="${DIR_DOCROOT}/themes/custom/${DRUPAL_THEME}"
	[ -f "${DIR_THEME}/package.json" ] || return 0
	cd "${DIR_THEME}"
	
	## ouverture des droits en écriture pour installations & mises à jour
	chmod -R u+w "${DIR_THEME}" 2>/dev/null || true
	
	## définition des commandes yarn utilisées
	p_section "Scripts yarn personnalisés (dep-install, dep-check) et adaptations (build, dev, watch, dev-server)"
	jq --indent 2 '
		.scripts."dep-install" = "yarn install --non-interactive --frozen-lockfile --check-files" |
		.scripts."dep-check"   = "yarn check --non-interactive --verify-tree --integrity" |
		.scripts."build"       = "gulp" |
		.scripts."watch"       = "gulp watch"
	' ./package.json > ./package.json.tmp && mv ./package.json.tmp ./package.json
	
	## installation, vérification et construction
	p_section "Installation des dépendances frontend (paquets npm)"
	${CMD_NODEYARN} dep-install || ${CMD_NODEYARN} install
	
	p_section "Vérification des versions du yarn.lock (par rapport au packages.json)"
	${CMD_NODEYARN} dep-check
	
	p_section "Modifications du thème (SCSS, JS...)"
	${CMD_NODEYARN} build || p_warning "Impossible de construire le thème !"
}
deploy_theme_yarn
