OPenIG - Instance Drupal 10 du site web <https://www.openig.org>
================================================================================


Déploiement d'une instance avec Docker (développement)
--------------------------------------------------------------------------------

Voir la documentation du projet de dockerisation :
<https://git.teicee.fr/openig/openig-d10-docker>


Déploiement d'une instance hébergée (prod/preprod)
--------------------------------------------------------------------------------

### Prérequis

Accès SSH sur un serveur LAPP avec :

-	PHP    `8.3` + Composer
-	NodeJS `20`  + Yarn
-	Solr   `9.4`

Un serveur SQL (local ou distant) avec :

-	PostgreSQL `15`
-	une base de données avec identifiants


### Installation

Mettre en place le projet depuis son dépôt Git dans le dossier de l'hébergement :
```bash
### Soit par SSH
git clone "git@git.teicee.fr:openig/openig-d10-website.git" .
### ou
git clone "git@github.com:openig/CMS-Drupal.git" .

### Soit par HTTP
git clone "https://git.teicee.fr/openig/openig-d10-website.git" .
### ou
git clone "https://github.com/openig/CMS-Drupal.git" .
```

Définir la configuration spécifique `web/sites/default/settings.local.php`, par exemple :
```php
### Par exemple les accès à la base de données PosgreSQL :
$databases['default']['default']['host']      = 'localhost';
$databases['default']['default']['port']      = '4321';
$databases['default']['default']['database']  = 'drupal';
$databases['default']['default']['username']  = 'drupal';
$databases['default']['default']['password']  = '****************';
```

Définir les variables spécifiques de déploiement dans `configs/deploy.local`, par exemple :
```bash
### Par exemple la définition de l'utilisateur web/php
USR_WORKER="www-data"
GRP_WORKER="www-data"

### Par exemple la définition de la configuration du mailer
SMTP_HOST="smtp.openig.org"
SMTP_PORT=587
SMTP_USER="drupal"
SMTP_PASS="********"

### Par exemple Définition du moteur d'indexation
SOLR_HOST="localhost"
SOLR_NAME="recherche_solr"
SOLR_CORE="drupal_core"

### Par exemple la définition du compte administrateur
DRUPAL_USER="admin"
DRUPAL_PASS="********"
DRUPAL_MAIL="webmestre@openig.org"
```

Puis lancer les commandes d'initialisation de l'instance avec le script fourni :
```bash
./scripts/deploy.sh
```

### Mise à jour

Mettre à jour le projet depuis son dépôts Git :
```bash
cd ~/drupal
git pull
```

Relancer les commandes d'initialisation de l'instance avec le script fourni :
```bash
./scripts/deploy.sh
```


Configuration du moteur d'indexation Solr
--------------------------------------------------------------------------------

### Génération des fichiers de configuration pour Solr

**Note:** Le module Drupal permet d'exporter la configuration pour Solr.

Afficher la liste des serveurs de recherche configurés dans Drupal :
```bash
drush search-api:server-list
```

Exporter les fichiers de configuration de "recherche_solr" pour Solr 9.4 :
```bash
drush search-api-solr:get-server-config "recherche_solr" "solr_config.zip" 9.4
```

Ranger les configurations exportées dans un sous-dossier de "configs" :
```bash
mv "./web/solr_config.zip" "./configs/solr_config.zip"
rm "./configs/solr/"* || true
unzip "configs/solr_config.zip" -d "./configs/solr/"
```

### Création d'un core Solr et mise à jour de sa configuration

**Note:** Ces actions sont réalisées automatiquent avec la dockerisation.

Création d'un nouveau core Solr nommé "drupal_core" avec la configuration fournie :
```bash
sudo -u solr /solr/bin/solr create_core -c "drupal_core" -n "drupal_core" -d "./configs/solr/"
```

Mise à jour de la configuration fournie pour un core Solr nommé "drupal_core" existant :
```bash
rm "/var/solr/data/drupal_core/conf/"*
cp "./configs/solr/"* "/var/solr/data/drupal_core/conf/"
```

Demande de rechargement au serveur Solr pour le core nommé "drupal_core" :
```bash
curl -k "http://localhost:8983/solr/admin/cores?action=RELOAD&core=drupal_core"
```

### Commandes Drush utiles pour gérer l'indexation des contenus

Affichage des serveurs d'indexation et des indexes de recherches configurés :
```bash
drush search-api:server-list
drush search-api:list
drush search-api:status
```

Réglages pour l'indexation en utilisant Solr :
```bash
drush search-api-solr:install-missing-fieldtypes
drush search-api-solr:reinstall-fieldtypes
```

Réindexation des contenus pour un index donné :
```bash
drush search-api:reset-tracker "${SOLR_INDEX}"
drush search-api:index
drush search-api-solr:finalize-index
drush search-api:status
```

