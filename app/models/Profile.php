<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Profile
 *
 * @author vava
 */
class App_Model_Profile extends App_Model_Db_Abstract{
    //Le nom et champs de la table PROFILE
    protected $base_table ='T_PROFILE';
    protected $fields = array(
        'id'=> 'pro_id','name'=> 'pro_name');
}
