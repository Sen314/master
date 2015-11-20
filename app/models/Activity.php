<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Activity
 *
 * @author vava
 */
class App_Model_Activity extends App_Model_Db_Abstract {
    //put your code here
    //Le nom et champs de la table Activity
    protected $base_table ='T_ACTIVITY';
    protected $fields = array(
        'id'=> 'act_id','name'=> 'act_name', 'parent'=>'act_parentId', 'tag'=>'act_tag');
    
    //Retourne le parent de l'activité
    public function getParent()
    {
        if (isset($this->parent)) 
        {
            $query = 'SELECT ' . $this->formatFieldsToSelect()
            . ' FROM ' . $this->base_table
            . ' WHERE ' . $this->fields['id'] . ' = ' . $this->parent;
            
            $statement = Db::getInstance()->query($query);
            return $statement->fetchObject(get_called_class());
        }
        return false;
    }
    //Retourne les fils de l'activity en fonction du site
    public function getChildrenBySite($site)
    {
            $query = 'SELECT ' . $this->formatFieldsToSelect()
            . ' FROM ' . $this->base_table.',TJ_ACTIVITYSITE'
            . ' WHERE act_activity=act_id'
            . ' and act_parentId = ' . $this->id.
            ' and act_site = '.$site.''
            . 'and act_actif=\'True\''
            . 'order by act_name';
            
            $statement = Db::getInstance()->query($query);
            return $statement->fetchAll(PDO::FETCH_CLASS, 'App_Model_Activity');
    }
    //Retourne les fils de l'activity 
    public function getChildren()
    {
            $query = 'SELECT ' . $this->formatFieldsToSelect()
            . ' FROM ' . $this->base_table
            . ' WHERE act_parentId = ' . $this->id
               .'order by act_name' ;
            
            $statement = Db::getInstance()->query($query);
            return $statement->fetchAll(PDO::FETCH_CLASS, 'App_Model_Activity');
    }
    //Retourne le ou les sites où l'activité est présente
    public function getSite()
    {
        $query='Select sit_id as id, sit_name as name 
                from T_SITE, TJ_ACTIVITYSITE
                where sit_id=act_site
                and act_activity='.$this->id;

        $statement = Db::getInstance()->query($query);
        return $statement->fetchAll(PDO::FETCH_CLASS, 'App_Model_Site');
    }
    
    //Retourne le ou les items appartennant à l'activity
    public function getItem()
    {
        $query = 'Select ite_id as id, ite_tag as tag, ite_name as name, ite_formula as formula, ite_objective as objective,
                    ite_operator as operator, ite_type as type, ite_graph as graphique, ite_visibility as visibility, ite_activity as activity
                    From T_ITEM
                    Where ite_activity =' . $this->id.
                    'order by ite_name,ite_formula';

            $statement = Db::getInstance()->query($query);
            return $statement->fetchAll(PDO::FETCH_CLASS, 'App_Model_Item');
    }
    //Retourne le ou les items appartennant à l'activity qui n'ont pas de formule
    public function getItemWhithoutForm()
    {
        $query = 'Select ite_id as id, ite_tag as tag, ite_name as name, ite_formula as formula, ite_objective as objective,
                    ite_operator as operator, ite_type as type, ite_graph as graphique, ite_visibility as visibility, ite_activity as activity
                    From T_ITEM
                    Where ite_activity =' . $this->id.
                    'and ite_formula is null
                    order by ite_name,ite_formula';

            $statement = Db::getInstance()->query($query);
            return $statement->fetchAll(PDO::FETCH_CLASS, 'App_Model_Item');
    }
    //Retourne le ou les items appartennant à l'activity qui ont une formule
    public function getItemForm()
    {
        $query = 'Select ite_id as id, ite_tag as tag, ite_name as name, ite_formula as formula, ite_objective as objective,
                    ite_operator as operator, ite_type as type, ite_graph as graphique, ite_visibility as visibility, ite_activity as activity
                    From T_ITEM
                    Where ite_activity =' . $this->id.
                    'and ite_formula is not null
                    order by ite_name,ite_formula';

            $statement = Db::getInstance()->query($query);
            return $statement->fetchAll(PDO::FETCH_CLASS, 'App_Model_Item');
    }
    //Retourne les activités par nom
    static function getActivities()
    {
        $query = 'SELECT act_id as id, act_name as name,act_parentId as parent, act_tag as tag
             FROM T_ACTIVITY
             where act_actif=\'True\'
             order by name';
        
        $statement = Db::getInstance()->query($query);
        return $statement->fetchAll(PDO::FETCH_CLASS, get_called_class());
    }
    //Retourne l'activité en fonction du tag de l'activite
    static function getActivityByTag($tag)
    {
          $query = 'Select act_id as id,act_name as name, act_parentId as parent, act_tag as tag
              FROM T_ACTIVITY
              where act_tag =\''.$tag.'\'';
          
          $statement = Db::getInstance()->query($query);
          return $statement->fetchObject('App_Model_Activity');
    }
}
