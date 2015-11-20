<?php

/**
 * Objet de connexion à la base de données
 * Basé sur le framework medoo
 * @see http://medoo.in/
 * @author Maxime Cazé
 * @copyright (c) 2014, Coriolis Service
 */
class Db
{	
    private static $_instance = array();
    
    /**
     * Infos relatives à la base de données
     */
	protected $database_type = '';
	protected $server = '';
	protected $username = '';
	protected $password = '';
	protected $charset = 'utf8';
	protected $database_name = '';
    
    /**
     * Objet de connexion PDO
     * @var PDO 
     */
    public $pdo;
    
    /**
     * Compteur de requêtes
     * @var int
     */
    protected $count_queries = 0;
    
    /**
     * Dernière requête utilisée
     * @var string 
     */
    protected $last_query;
    
	public function __construct($options)
	{
		try {
            foreach ($options as $option => $value)
            {
                $this->$option = $value;
            }
            
			$type = strtolower($this->database_type);

			switch ($type)
			{
                // dblib / mysql
				case 'dblib':
                case 'mysql':
					$this->pdo = new PDO(
						$type . ':host=' . $this->server . ';dbname=' . $this->database_name,
						$this->username,
						$this->password
					);
					$this->pdo->exec('SET NAMES \'' . $this->charset . '\'');
					break;
                // driver odbc : driver de connexion à MSSQL avec support unicode
				case 'odbc':                    
					$this->pdo = new PDO(
						$type . ':Driver={SQL Server Native Client 11.0};Server=' . $this->server . ';Database=' . $this->database_name,
						$this->username,
						$this->password
					);                    
					$this->pdo->exec('SET NAMES \'' . $this->charset . '\'');
					break;
                // driver windows sqlsrv non présent dans medoo
                // pour développement local avec wamp
				case 'sqlsrv':
					$this->pdo = new PDO(
						$type . ':Server=' . $this->server . ';Database=' . $this->database_name,
						$this->username,
						$this->password
					);
					$this->pdo->exec('SET NAMES \'' . $this->charset . '\'');
					break;
			}
		}
		catch (PDOException $e) {
			throw new Exception($e->getMessage());
		}
    }
    
    /**
     * Crée et retourne une instance de connexion à la base de données
     * @return Db
     */
    public static function getInstance ($name = ENVIRONMENT, $options = array()) 
        {
        if (array_key_exists($name, self::$_instance) === false) {
            self::$_instance[$name] = new self($options);
        }
        return self::$_instance[$name];
    }
	
    /**
     * Execute une requete SQL et retourne un set de resultats PDOStatement
     * @param string $query
     * @return PDOStatement
     */
	public function query($query)
	{
//        echo '<pre>';
//        print_r($query);
//        echo '</pre>';
		$this->last_query = $query;
        $this->count_queries++;		
		return $this->pdo->query($query);
	}

    /**
     * Execute une requete SQL et retourne le nombre de lignes affectees
     * @param string $query
     * @return int
     */
	public function exec($query)
	{
		$this->last_query = $query;
        $this->count_queries++;
		return $this->pdo->exec($query);
	}

    /**
     * Retourne la derniere requete executee
     * @return string
     */
	public function getLastQuery()
	{
		return $this->last_query;
	}

    /**
     * Retourne le nombre de requêtes
     * @return int
     */
	public function getCountQueries()
	{
		return $this->count_queries;
	}
}
