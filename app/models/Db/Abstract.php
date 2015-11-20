<?php

/**
 * Classe abstraite relative utilitaire pour une classe associée à une table sql
 *
 * @author Maxime Cazé <maximecaze@coriolis.fr>
 */
abstract class App_Model_Db_Abstract 
{
    const SQL_INSERT_MODE_CODE = 'insert';
    const SQL_UPDATE_MODE_CODE = 'update';
    const SQL_DELETE_MODE_CODE = 'delete';
    const SQL_SELECT_MODE_CODE = 'select';
    
    protected $base_table;
    
    /**
     * Champs des différentes tables qu'on veut voir apparaitre dans les requetes
     * Possibilité d'en ajouter avec la méthode setFields($fields)
     * Possibilité d'indiquer le nom de remplacement d'un champ (alias) en indiquant la clé
     * ex: array('id' => 'code') équivant à SELECT code AS id
     * @var array 
     */
    protected $fields = array();
    
    /**
     * Définit les champs à ne pas mettre entre quotes
     * @var array
     */
    protected $unquoted_fields = array();
    
    public function __construct() { }
    
    /**
     * Retourne l'instance de bdd sur laquelle on doit taper selon le mode (select, insert, update, delete)
     * Les actions de type DELETE, INSERT ou UPDATE seront faites sur la base de prod
     * Les actions de type SELECT seront faites sur la base répliquée
     * @param type $mode
     * @return type
     */
    protected function _getDbInstance($mode = self::SQL_SELECT_MODE_CODE)
    {
        if ($mode == self::SQL_SELECT_MODE_CODE) {
            return Db::getInstance(Db::DB_REPLICATION_CODE);
        } elseif ($mode == self::SQL_INSERT_MODE_CODE || $mode == self::SQL_UPDATE_MODE_CODE || $mode == self::SQL_DELETE_MODE_CODE) {
            return Db::getInstance(Db::DB_PRODUCTION_CODE);
        }
    }
    
    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }
    
    /**
     * Instaure les champs souhaites
     * @return App_Model_Easyphone_Abstract
     */
    public function setFields($fields)
    {
        $this->fields = array_merge($this->fields, $fields);
        return $this;
    }
    
    /**
     * Retourne la selection de champs au format string
     * @return string
     */
    protected function formatFieldsToSelect()
    {
        $str_fields = '';
        if (!empty($this->fields)) {
            $n_field = 0;
            foreach ($this->fields as $field_name => $field) {
                $str_fields .= ($n_field++ > 0 ? ', ' : '') . $field;
                if (is_string($field_name)) $str_fields .= ' AS ' . $field_name;
            }
        } else {
            $str_fields = '*';
        }
        return $str_fields;
    }
    
    /**
     * Retourne un element par son identifiant
     * @param int $id
     * @return App_Model_Easyphone_Abstract
     */
    public function load($id)
    {
        $query = 'SELECT ' . $this->formatFieldsToSelect()
            . ' FROM ' . $this->base_table
            . ' WHERE ' . $this->fields['id'] . ' = ' . $id;
        
        $statement = Db::getInstance()->query($query);
        if ($statement !== false)
            return $statement->fetchObject(get_called_class());
        else
            return false;
    }
    
    /**
     * Retourne toutes les lignes d'une table
     * @return array
     */
    public function getAll()
    {
        $query = 'SELECT ' . $this->formatFieldsToSelect()
            . ' FROM ' . $this->base_table;
        
        $statement = Db::getInstance()->query($query);
        return $statement->fetchAll(PDO::FETCH_CLASS, get_called_class());
    }
    
    /**
     * Retourne des éléments par leur id
     * @param array $id
     * @return array
     */
    public function loadByIds($ids)
    {
        $query = 'SELECT ' . $this->formatFieldsToSelect()
            . ' FROM ' . $this->base_table
            . ' WHERE ' . $this->fields['id'] . ' IN (' . implode(',', $ids) . ')';
        
        $statement = Db::getInstance()->query($query);
        return $statement->fetchAll(PDO::FETCH_CLASS, get_called_class());
    }
    
    /**
     * Insertion en BDD
     * @param array $datas
     * @return boolean
     */
    public function insert($datas) 
    {
        $fields = $values = array();
        
        foreach ($datas as $key => $value) {
            if ($key != 'id' && !is_null($value) && $value != '') {
                $fields[] = $key;
                if (!in_array($key, $this->unquoted_fields)) {
                    $values[] = '\'' . trim(str_replace("'", "''", $value)) . '\'';
                } else {
                    $values[] = trim($value);
                }
            }
        }
        
        $query = 'INSERT INTO ' . $this->base_table . ' (' . implode(',', $fields) . ') VALUES (' . implode(',', $values) . ')';
        return Db::getInstance()->exec($query);
    }
    
    /**
     * Update en BDD
     * @param array $datas
     * @return boolean
     */
    public function update($datas, $where) 
    {
        $query = 'UPDATE ' . $this->base_table . ' SET ';    
        $n_value = 0;
        foreach ($datas as $key => $value) {
            if ($key != 'id' && !is_null($value) && $value != '') {
                $query .= ($n_value++ > 0 ? ', ' : null) . $key . ' = ';
                if (!in_array($key, $this->unquoted_fields)) {
                    $query .= '\'' . trim(str_replace("'", "''", $value)) . '\'';
                } else {
                    $query .= trim($value);
                }
            }
        } 
        
        $query .= ' WHERE ';  
        $n_where = 0;
        foreach ($where as $key => $value) {
            $query .= ($n_where++ > 0 ? 'AND ' : null) . $key;
            if (is_array($value)) {
                if (!in_array($key, $this->unquoted_fields)) {
                    $query .= ' IN (\'' . implode('\',\'', $value) . '\')';
                } else {
                    $query .= ' IN (' . implode(',', $value) . ')';
                }
            } else {
                $query .= ' = ' . trim($value);
            }
        }        
        return Db::getInstance()->exec($query);
    }
    
    /**
     * Update en BDD
     * @param array $datas
     * @return boolean
     */
    public function delete($where) 
    {
        $query = 'DELETE FROM ' . $this->base_table;        
        $query .= ' WHERE ';  
        $n_where = 0;
        foreach ($where as $key => $value) {
            $query .= ($n_where++ > 0 ? 'AND ' : null) . $key;
            if (is_array($value)) {
                if (!in_array($key, $this->unquoted_fields)) {
                    $query .= ' IN (\'' . implode('\',\'', $value) . '\')';
                } else {
                    $query .= ' IN (' . implode(',', $value) . ')';
                }
            } else {
                $query .= ' = ' . trim($value);
            }
        }        
        return Db::getInstance()->exec($query);
    }
    
    /**
     * Retourne le dernier id inséré
     * @return string
     */
    public function getLastInsertId()
    {
        $query = 'SELECT @@IDENTITY AS last_insert_id';        
        $statement = Db::getInstance()->query($query);
        return $statement->fetchColumn();
    }
}