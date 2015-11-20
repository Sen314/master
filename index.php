<?php

/**
 * Controlleur principal d'accès aux vues
 */
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__FILE__));
require_once ROOT . DS . 'app' . DS . 'bootstrap.php';

// vue courante : si pas de param view, on prend la vue par défaut
$current_view = filter_has_var(INPUT_GET, 'view') === true ? filter_input(INPUT_GET, 'view') : 'default';

// si vue non déclarée, on affiche une page non existante
$current_view = isset($config['views'][$current_view]) ? $current_view : '404';

// lien vers le script php correspondant à la vue à afficher
$view_path = ROOT . DS . 'views' . DS . $config['views'][$current_view]['folder'] . DS . $config['views'][$current_view]['file'] . '.php';

// si le script php en question n'existe pas, on affiche une page non existante
if (file_exists($view_path) === false) {
    $current_view = '404';
    $view_path = ROOT . DS . 'views' . DS . $config['views'][$current_view]['folder'] . DS . $config['views'][$current_view]['file'] . '.php';
}

// header
if (file_exists(ROOT . DS . 'layout' . DS . 'templates' . DS . $config['views'][$current_view]['layout'] . DS . 'header.php')) {
    require_once ROOT . DS . 'layout' . DS . 'templates' . DS . $config['views'][$current_view]['layout'] . DS . 'header.php';
}

// content
if (file_exists($view_path)) {
    require_once $view_path;
}
// footer
if (file_exists(ROOT . DS . 'layout' . DS . 'templates' . DS . $config['views'][$current_view]['layout'] . DS . 'footer.php')) {
    require_once ROOT . DS . 'layout' . DS . 'templates' . DS . $config['views'][$current_view]['layout'] . DS . 'footer.php';
}