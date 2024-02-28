<?php

### Paramètres par défaut fournis par le projet Drupal
include_once __DIR__.'/default.settings.php';

### Paramètres spécifiques du projet (fichier versionné)
require_once __DIR__.'/settings.project.php';

### Paramètres locaux de l'instance (non versionné)
include_once __DIR__.'/settings.local.php';
