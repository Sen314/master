<?php

/**
 * Cache config
 * @need phpfastcache
 * @see http://www.phpfastcache.com
 */

require ROOT . DS . 'library' . DS . 'phpfastcache' . DS . 'phpfastcache.php';
phpFastCache::setup('storage', 'files'); // config: stockage du cache par fichier
phpFastCache::setup('path', ROOT); // config: chemin de la racine de l'application
phpFastCache::setup('securityKey', 'cache'); // config: dossier où sont stockés les fichiers de cache
