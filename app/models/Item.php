<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Item
 *
 * @author vava
 */
class App_Model_Item extends App_Model_Db_Abstract {
    //Le nom et champs de la table Site
    protected $base_table ='T_ITEM';
    protected $fields = array(
        'id'=> 'ite_id','tag'=>'ite_tag','name'=> 'ite_name' ,'formula'=>'ite_formula',
        'objective'=>'ite_objective', 'operator'=>'ite_operator','type'=>'ite_type', 
        'graphique'=>'ite_graph','visibility'=>'ite_visibility','activity'=>'ite_activity');
    
     //Retourne le ou les activités de l'item
    public function getActivity()
    {
        $query='Select act_id as id, act_name as name, act_parentID as parent
                    from T_ACTIVITY
                    where act_id='.$this->activity;
        
        $statement = Db::getInstance()->query($query);
        return $statement->fetchObject('App_Model_Activity');
    }
}
