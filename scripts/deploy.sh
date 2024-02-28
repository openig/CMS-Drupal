#!/bin/sh
### Drupal 10: Lancement des scripts de déploiement pour l'instance Drupal.
###
### Variables:
### - CMD_COMPOSER
### - CMD_NODEYARN
### - DIR_HOSTING
### - DIR_DOCROOT
### - DIR_STARTER
### - USR_WORKER
### - GRP_WORKER
###
### - SMTP_HOST
### - SMTP_PORT
### - SMTP_USER
### - SMTP_PASS
### - SOLR_HOST
### - SOLR_NAME
### - SOLR_CORE
###
### - DRUPAL_NAME
### - DRUPAL_LANG
### - DRUPAL_THEME
### - DRUPAL_USER
### - DRUPAL_PASS
### - DRUPAL_MAIL

set -e

cd "$(dirname "$0")"

DIR_INCD="$(pwd)/deploy.d"

cd ".."

################################################################################ VARS

### Définition des chemins des dossiers
DIR_HOSTING="$(pwd)"
DIR_PUBHTML="web/"
DIR_DOCROOT=
DIR_STARTER=

### Définition de l'utilisateur web/php
#USR_WORKER="$(id -u)"
#GRP_WORKER="$(id -g)"

### Définition des commandes utilisées
CMD_COMPOSER="$(which composer 2>/dev/null || true)"
CMD_NODEYARN="$(which yarn     2>/dev/null || true)"

### Définition de la configuration du mailer
SMTP_HOST=
SMTP_PORT=
SMTP_USER=
SMTP_PASS=

### Définition du moteur d'indexation
SOLR_HOST="localhost"
SOLR_NAME="recherche_solr"
SOLR_CORE="drupal_core"

### Définition des paramètres de l'instance
DRUPAL_NAME=
DRUPAL_LANG=
DRUPAL_THEME="openig"

### Définition du compte administrateur
DRUPAL_USER="admin"
DRUPAL_PASS=
DRUPAL_MAIL=

################################################################################ UTIL

p_title() {
	printf "\n\033[1;36m###\033[0;00m\n\033[1;36m### %s\033[0;00m\n\033[1;36m###\033[0;00m\n" "${1}"
}
p_section() {
	printf "\n\033[1;34m=== %s\033[0;00m\n" "${1}"
}
p_warning() {
	printf "\033[1;41m\033[1;37m %s \033[0;00m\n" "${1}"
}
p_success() {
	printf "\033[1;42m\033[1;37m %s \033[0;00m\n" "${1}"
}

################################################################################ CONF

#ENV_FILE="${DIR_INCD}/.env.local"
ENV_FILE="${DIR_HOSTING}/configs/deploy.local"

p_section "Chargement des variables de '${ENV_FILE}'"
[ -f "${ENV_FILE}" ] && . "${ENV_FILE}"

[ -n "${DIR_DOCROOT}" ] || DIR_DOCROOT="${DIR_HOSTING}/${DIR_PUBHTML:-}"

#printenv
#exit 1

################################################################################ MAIN

p_title "Lancement des scripts du dossier './deploy.d'"
for INC_SCRIPT in "${DIR_INCD}/${1:-}"*.sh
	do . "${INC_SCRIPT}"
done
p_success "Fin du déploiement de l'instance Drupal"
