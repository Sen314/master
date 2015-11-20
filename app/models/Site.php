<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Site
 *
 * @author vava
 */
class App_Model_Site extends App_Model_Db_Abstract {
    //Le nom et champs de la table Site
    protected $base_table ='T_SITE';
    protected $fields = array(
        'id'=> 'sit_id','name'=> 'sit_name', 'tag'=>'sit_tag');
    
    //Retourne le ou les activités du site
    public function getActivity()
    {
        $query='Select act_id as id, act_name as name, act_parentId as parent, act_tag as tag
                From T_ACTIVITY, TJ_ACTIVITYSITE
                Where act_id=act_activity
                and act_site='.$this->id.'
                and act_actif=\'True\'
                order by act_name';
        
        $statement = Db::getInstance()->query($query);
        return $statement->fetchAll(PDO::FETCH_CLASS, 'App_Model_Activity');
    }
    //Retourn tous les sites
    static function getSites()
    {
        $query='Select sit_id as id, sit_name as name, sit_tag as tag
                From T_SITE';
        
        $statement = Db::getInstance()->query($query);
        return $statement->fetchAll(PDO::FETCH_CLASS, 'App_Model_Site');
    }
    //Retourne l'activité en fonction du tag de l'activite
    static function getSiteByTag($tag)
    {
          $query = 'Select sit_id as id,sit_name as name, sit_tag as tag
                    FROM T_SITE
                    where sit_tag =\''.$tag.'\'';
          
          $statement = Db::getInstance()->query($query);
          return $statement->fetchObject('App_Model_Site');
    }
}