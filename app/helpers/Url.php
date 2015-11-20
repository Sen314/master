<?php

/**
 * Classe aide pour les URLs
 *
 * @author Maxime Cazé <maximecaze@coriolis.fr>
 */
class App_Helper_Url 
{    
    /**
     * Retourne l'url de base de l'application
     * @return string
     */
    public static function getBaseUrl()
    {
        if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
            return 'http://' . $_SERVER['HTTP_HOST'] . '/butagaz';
        } else {
            return 'http://' . $_SERVER['HTTP_HOST'] . '/butagaz/' . (ENVIRONMENT == ENV_PROD ? 'production' : 'recette');
        }
    }
    
    /**
     * Construction d'une url
     * @param string $view
     * @param array $params
     * @return string
     */
    public static function getUrl($view, $params = array())
    {
        $url = 'index.php?view=' . $view;
        foreach ($params as $param_key => $param_value) {
            if (is_array($param_value)) {
                foreach ($param_value as $p_value) {
                    $url .= '&' . $param_key . '[]=' . $p_value;                    
                }
            } else {
                $url .= '&' . $param_key . '=' . $param_value;
            }
        }
        return $url;
    }
}
