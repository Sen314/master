<?php

/* 
 * Database config
 */

Db::getInstance(ENVIRONMENT, array(    
    // serveur
    'server' => 'localhost',    
    // type de connexion à la bdd
    'database_type' => 'mysql',    
    // nom de la bdd
    'database_name' => 'eteamstic',    
    // login
    'username' => 'root',    
    // mot de passe
    'password' => '',
));