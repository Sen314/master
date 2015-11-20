<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Values
 *
 * @author vava
 */
class App_Model_Value extends App_Model_Db_Abstract{
   //Le nom et champs de la table Values
    protected $base_table ='TJ_ITEMVALUES';
    protected $fields = array(
        'site'=> 'val_site','item'=> 'val_item','date'=>'val_date', 'lastUpdate'=>"val_lastUpdate", 'value' =>'val_value');
    
    //Retourne le site de la valeur
    public function getSite()
    {
        $query='Select sit_id as id, sit_name as name
                From T_SITE
                Where sit_id='.$this->site;
        
        $statement = Db::getInstance()->query($query);
        return $statement->fetchObject('App_Model_Site');
    }
    
    //Retourne l'item de la valeur
    public function getItem()
    {
       $query = 'Select ite_id as id, ite_tag as tag, ite_name as name, ite_formula as formula, ite_objective as objective,
                    ite_operator as operator, ite_type as type, ite_graph as graphique,ite_visibility as visibility, ite_activity as activity
                    From T_ITEM
                    Where ite_id =' . $this->item;

        $statement = Db::getInstance()->query($query);
        return $statement->fetchObject( 'App_Model_Item');
    }
    //Retourne la valeur en fonction de l'id d'un site et d'un item
     static function getValueByIdItem($site,$item,$dateDebut, $dateFin = null)
    {
         if ($dateFin==null)
         {
             $dateFin=$dateDebut;
         }
        $query='Select val_site as site, val_item as item, CONVERT (VARCHAR,val_date,105) as date, val_lastUpdate as lastUpdate, val_value as value
                from TJ_ITEMVALUES
                where val_site='.$site.'
                and val_item='.$item.'
                and val_date between \''.$dateDebut.'\' and \''.$dateFin.'\'';
        
        $statement = Db::getInstance()->query($query);
        return $statement->fetchAll(PDO::FETCH_CLASS, 'App_Model_Value');
    }
    //Retourne la valeur en fonction de l'id d'un site et du nom de l'item
    static function getValueByNameItem($site,$nameItem,$dateDebut, $dateFin = null)
    {
         if ($dateFin==null)
         {
             $dateFin=$dateDebut;
         }
        $query ='Select val_site as site, val_item as item, CONVERT (VARCHAR,val_date,105) as date, val_lastUpdate as lastUpdate, val_value as value
                    from TJ_ITEMVALUES,T_ITEM
                    where  val_item=ite_id
                    and val_site='.$site.'
                    and ite_name=\''.str_replace('\'','\'\'',$nameItem).'\'
                    and val_date between \''.$dateDebut.'\' and \''.$dateFin.'\'';
        
        $statement = Db::getInstance()->query($query);
        return $statement->fetchAll(PDO::FETCH_CLASS, 'App_Model_Value');
    }
    //Retourne la somme des valeurs en fonction de l'id de l'activite et du tag de l'item et du site
    static function getSumValueByTagItemSite($site,$activity,$tagItem,$dateDebut, $dateFin = null)
    {
         if ($dateFin==null)
         {
             $dateFin=$dateDebut;
         }
        $query ='Select SUM(val_value) as value
                    from TJ_ITEMVALUES,T_ITEM
                    where  val_item=ite_id
                    and val_site='.$site.'
                    and ite_activity='.$activity.'
                    and ite_tag=\''.str_replace('\'','\'\'',$tagItem).'\'
                    and val_date between \''.$dateDebut.'\' and \''.$dateFin.'\'';
        
        $statement = Db::getInstance()->query($query);
        return $statement->fetchColumn();
    }
    //Retourne la somme des valeurs en fonction de l'id de l'activite et du tag de l'item
     static function getSumValueByTagItem($activity,$tagItem,$dateDebut, $dateFin = null)
    {
         if ($dateFin==null)
         {
             $dateFin=$dateDebut;
         }
        $query ='Select SUM(val_value) as value
                    from TJ_ITEMVALUES,T_ITEM
                    where  val_item=ite_id
                    and ite_activity='.$activity.'
                    and ite_tag=\''.str_replace('\'','\'\'',$tagItem).'\'
                    and val_date between \''.$dateDebut.'\' and \''.$dateFin.'\'';
        
        $statement = Db::getInstance()->query($query);
        return $statement->fetchColumn();
    }
    //Retourne la somme des valeurs en fonction de l'id de l'activite , l'item
    static function getSumValueByItem($activity,$item,$dateDebut, $dateFin = null)
    {
         if ($dateFin==null)
         {
             $dateFin=$dateDebut;
         }
        $query ='Select SUM(val_value) as value
                            from TJ_ITEMVALUES,T_ITEM
                            where  val_item=ite_id
                            and ite_activity='.$activity.'
                            and ite_id='.$item.'
                    and val_date between \''.$dateDebut.'\' and \''.$dateFin.'\'';
        
        $statement = Db::getInstance()->query($query);
        return $statement->fetchColumn();
    }
    //Insert la value dans la table TJ_ITEMVALUES en fonction de l'id item, site et de la date
    static function setValueByItem($site,$item,$date,$value)
    {
        $today = date("Ymd H:i:s"); 
        $reqVerif='Select val_site as site, val_item as item, val_date as date, val_lastUpdate as lastUpdate, val_value as value
                    From TJ_ITEMVALUES
                    where val_site='.$site.'
                    and val_item='.$item.'
                    and val_date=\''.$date.'\'';
        $verif = Db::getInstance()->query($reqVerif);
        $verif=$verif->fetchObject('App_Model_Value');
        
        if(empty($verif))
        {            
            $query ='INSERT INTO TJ_ITEMVALUES (val_site,val_item,val_date,val_lastUpdate,val_value)
                VALUES ('.$site.','.$item.',\''.$date.'\',\''.$today.'\','.$value.')';
        }
        else
        {
            $query ='UPDATE TJ_ITEMVALUES
                    SET val_lastUpdate =\''.$today.'\', val_value ='.$value.'
                    WHERE val_site='.$site.'
                    and val_item='.$item.'
                    and val_date=\''.$date.'\'';
        }
        Db::getInstance()->query($query);
    }
    //Insert la value dans la table TJ_ITEMVALUES en fonction du tag de l'item, du tag site du tag de l'activity et de la date
    static function setValueByTag($site,$activity,$item,$date,$value)
    {
        if($value>0)
        {
            $today = date("Ymd H:i:s"); 
            $reqVerif='Select val_site as site, val_item as item, val_date as date, val_lastUpdate as lastUpdate, val_value as value
                        From TJ_ITEMVALUES V, T_ITEM I, T_SITE S, T_ACTIVITY A
                        Where I.ite_id=V.val_item
                        and S.sit_id=V.val_site
                        and A.act_id=I.ite_activity
                        and act_tag=\''.$activity.'\'
                        and sit_tag=\''.$site.'\'
                        and ite_tag=\''.$item.'\'
                        and val_date=\''.$date.'\'';

            $verif = Db::getInstance()->query($reqVerif);
            $verif=$verif->fetchObject('App_Model_Value');

            if(empty($verif))
            {            
                $query ='INSERT INTO TJ_ITEMVALUES (val_site,val_item,val_date,val_lastUpdate,val_value) 
                        VALUES ((select sit_id from T_SITE WHERE sit_tag=\''.$site.'\'),(Select ite_id 
                                    from T_ITEM I,T_ACTIVITY A 
                                    WHERE I.ite_activity=A.act_id
                                     and act_tag=\''.$activity.'\' and ite_tag=\''.$item.'\'),
                                     \''.$date.'\',
                                     \''.$today.'\','.$value.')';
            }
            else
            {
                $query ='UPDATE TJ_ITEMVALUES SET val_lastUpdate =\''.$today.'\', val_value ='.$value.'
                            WHERE val_site=(Select sit_id
                                            from T_SITE 
                                            where sit_tag=\''.$site.'\')
                             and val_item=(Select ite_id 
                                            from T_ITEM I,T_ACTIVITY A 
                                            WHERE I.ite_activity=A.act_id
                                            and act_tag=\''.$activity.'\'
                                            and ite_tag=\''.$item.'\')
                             and val_date=\''.$date.'\'';
            }
            Db::getInstance()->query($query);
        }
    }
    //Retourne la date du dernier update de l'activité du site
    static function getLastUpdateByActivitySite($activity,$site,$dateDebut, $dateFin = null)
    {
        if ($dateFin==null)
        {
            $dateFin=$dateDebut;
        }
        $query="Select CONVERT(VARCHAR(24),MAX(val_lastUpdate),103)+' '+CONVERT(VARCHAR(24),MAX(val_lastUpdate),108) as lastUpdate
                    from TJ_ITEMVALUES,T_ITEM
                    where val_item in ( Select ite_id from T_ITEM where ite_activity=".$activity.")
                    and val_site=".$site."
                    and val_date between '".$dateDebut."' and '".$dateFin."'";
        $statement = Db::getInstance()->query($query);
        return $statement->fetchColumn();
    }
    //Retourne la date du dernier update de l'activité generale
    static function getLastUpdateByActivity($activity,$dateDebut, $dateFin = null)
    {
        if ($dateFin==null)
        {
            $dateFin=$dateDebut;
        }
        $query="Select CONVERT(VARCHAR(24),MAX(val_lastUpdate),103)+' '+CONVERT(VARCHAR(24),MAX(val_lastUpdate),108) as lastUpdate
                    from TJ_ITEMVALUES,T_ITEM
                    where val_item in ( Select ite_id from T_ITEM where ite_activity=".$activity.")
                    and val_date between '".$dateDebut."' and '".$dateFin."'";
        $statement = Db::getInstance()->query($query);
        return $statement->fetchColumn();
    }
}