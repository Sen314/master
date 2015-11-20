<?php

/**
 * Classe de config
 *
 * @author Maxime Cazé <maximecaze@coriolis.fr>
 */
class App_Helper_Config 
{
    protected static $config;
    
    /**
     * Instaure la config
     * @param array $config
     */
    public static function setConfig($config)
    {
        self::$config = $config;
    }
    
    /**
     * Retourne la config dans sa globalité
     * @return array
     */
    public static function getConfig()
    {
        return self::$config;
    }
    
    /**
     * Retourne une config par son chemin dans le tableau de conf (tableau de clés)
     * @param array $paths
     * @return mixed
     */
    public static function getConfigField($paths = array())
    {
        $config = self::getConfig();
        foreach ($paths as $path) {
            if (isset($config[$path]) === false) return false;
            $config = $config[$path];
        }
        return $config;
    }
}
