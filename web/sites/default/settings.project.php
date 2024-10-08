<?php
/**
 * Configuration de l'instance Drupal : paramètres fixes ou par défaut du projet
 *   => communs et versionnés (quelque soit l'instance déployée) : NE PAS METTRE DE SECRET !
 *   => pour tout paramètrage propre au serveur, voir 'settings.local.php' (non versionné)
 */

###
### URL DOMAIN
###
$settings['trusted_host_patterns'] = [
	'^openig\.org$',
	'^.+\.openig\.org$',
	'^.+\.teicee\.fr$',
];

if (isset($_SERVER['SERVER_NAME'])) {
	$settings['tic_is_dev'] = preg_match('/^(www\.)?openig\.org$/', $_SERVER['SERVER_NAME']) ? false : true;
}

###
### DATABASE
###
$databases = array(
	'default' => array(
		'default' => array(
			'driver'    => 'pgsql',
#			'namespace' => 'Drupal\\pgsql\\Driver\\Database\\pgsql',
#			'autoload'  => 'core/modules/pgsql/src/Driver/Database/pgsql/',
			'host'      => "127.0.0.1",
			'port'      => "5432",
			'database'  => "drupal10",
			'username'  => "drupal10",
			'password'  => '',  // à définir dans settings.local.php !
			'prefix'    => '',
		)
	),
	'migrate' => array(
		'default' => array(
			'driver'    => 'pgsql',
#			'namespace' => 'Drupal\\pgsql\\Driver\\Database\\pgsql',
#			'autoload'  => 'core/modules/pgsql/src/Driver/Database/pgsql/',
			'host'      => "127.0.0.1",
			'port'      => "5432",
			'database'  => "drupal8",
			'username'  => "drupal8",
			'password'  => '',  // à définir dans settings.local.php !
			'prefix'    => '',
		)
	),
);

###
### FILE SYSTEM
###
#$settings['file_temp_path']              = $app_root . '/../temp';
$settings['file_private_path']           = $app_root . '/../private';
$settings['config_sync_directory']       = $app_root . '/../configs/sync';
$settings['hash_salt'] = file_get_contents($app_root . '/../configs/salt.txt');

###
### PROXY WEB
###
if (($v = getenv('HTTP_PROXY'))  !== false) $settings['http_client_config']['proxy']['http']  = $v;
if (($v = getenv('HTTPS_PROXY')) !== false) $settings['http_client_config']['proxy']['https'] = $v;
if (($v = getenv('NO_PROXY'))    !== false) $settings['http_client_config']['proxy']['no']    = explode(",", $v);

###
### DEBUG ERRORS
###
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors',         false);
ini_set('display_startup_errors', false);
$config['system.logging']['error_level'] = 'hide'; // hide|some|all|verbose

