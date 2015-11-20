<?php

/**
 * Classe utilitaire de session utilisateur
 *
 * @author Maxime Cazé <maximecaze@coriolis.fr>
 */
class App_Helper_User_Session 
{    
    const USER_SESSION_KEY = '';
    
    /**
     * Connecte un utilisateur avec ses identifiants
     * @param string $login
     * @param string $password
     * @return boolean
     */
    public static function login($login, $password)
    {
        $objUser = new App_Model_User();
        $user = $objUser->authenticate($login, $password);
        if ($user !== false) {
			//$user->activite = 'orange';
            $_SESSION[self::_getUserSessionKey()] = $user;
            return true;
        }
        return false;
    }
    
    /**
     * Déconnexion de l'utilisateur
     */
    public static function logout()
    {
        unset($_SESSION[self::_getUserSessionKey()]);
    }
    
    /**
     * Test si un user est loggé
     * @return boolean
     */
    public static function isLoggedIn()
    {   
        if (empty($_SESSION[self::_getUserSessionKey()])) {
            return false;
        }
        return true;
    }
    
    /**
     * Get current user session
     * @return App_Model_Kelio_User
     */
    public static function getUserSession()
    {
        return $_SESSION[self::_getUserSessionKey()];
    }
    
    /**
     * Get user session key
     * @return string
     */
    protected static function _getUserSessionKey()
    {
        return md5(self::USER_SESSION_KEY);
    }
}
