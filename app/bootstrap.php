<?php

/**
 * Fichier de démarrage et configuration de l'application
 */
define('ENV_DEV', 'dev');
define('ENV_R7', 'recette');
define('ENV_PROD', 'production');

// voir pour déclarer ces variables directement dans la conf vhost
define('ENVIRONMENT', ENV_DEV);

if (ENVIRONMENT != ENV_PROD) {
    // ini_set('error_reporting', 'E_ALL & ~E_NOTICE');
    ini_set('display_errors', 1);
}
set_time_limit(180);
date_default_timezone_set('Europe/Paris');
setlocale(LC_TIME, 'fr_FR.utf8', 'fr_FR', 'fra', 'french');
 
# Config de l'application
$config['application']['title'] = 'BASE';
$config['application']['logo'] = '';

# Autoloader => Permet de charger dynamiquement les classes
spl_autoload_register( function ($class_name) {
	# app/models
    if (substr($class_name, 0, 10) == 'App_Model_') {
        $path_to_class = ROOT . DS . 'app' . DS . 'models' . DS . str_replace('_', DS, substr($class_name, 10)) . '.php';
    } 
	# app/helpers
    elseif (substr($class_name, 0, 11) == 'App_Helper_') {
        $path_to_class = ROOT . DS . 'app' . DS . 'helpers' . DS . str_replace('_', DS, substr($class_name, 11)) . '.php';
    }
	# library
    else {
        $path_to_class = ROOT . DS . 'library' . DS . str_replace('_', DS,$class_name) . '.php';
    }    
    if (file_exists($path_to_class)) {
        require_once $path_to_class;
    }
});

# Chargement des fichiers de config
# -- initialisation de la database
require ROOT . DS . 'app' . DS . 'config' . DS . 'db.php';
# -- views
require ROOT . DS . 'app' . DS . 'config' . DS . 'views.php';
# -- users
// require ROOT . DS . 'app' . DS . 'config' . DS . 'users.php';

# Instauration de la config
App_Helper_Config::setConfig($config);


# Session
// session_cache_expire(3600); // session expirant au bout d'une heure
// session_start();
# Redirection vers la page de login si l'utilisateur ne s'est pas connecté OU si page de login OU script cron
// if (
    // (!isset($no_redirect) || $no_redirect === false) &&
    // App_Helper_User_Session::isLoggedIn() === false 
    // && strpos($_SERVER['REQUEST_URI'], 'login') === false 
    // && strpos($_SERVER['REQUEST_URI'], 'logout') === false
    // && strpos($_SERVER['REQUEST_URI'], '/cron/') === false
    // && strpos($_SERVER['REQUEST_URI'], 'api') === false
// ) {
    // if (filter_has_var(INPUT_GET, 'ajax')) {
        // echo 'loggedout';
    // } else {
        // header('Location: index.php?view=user/login');
    // }
    // exit;
// }