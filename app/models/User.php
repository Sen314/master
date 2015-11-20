<?php

/**
 * Classe utilisateur de l'application
 *
 * @author Maxime Cazé <maximecaze@coriolis.fr>
 */
class App_Model_User 
{
    public $id;
    public $name;
    public $login;
    public $mail;
    public $mdp;
    public $profil;
    public $site;
    
    
    /**
     * Authentification d'un utilisateur
     * @param string $login
     * @param string $password
     * @return boolean|\App_Model_User
     */
    public function authenticate($login, $password)
    {
        $utilisateurs = Db::getInstance()->query("Select use_id as id ,use_name as name,use_mail as mail,use_login as login,use_pwd as mdp,
                                                            use_profile as profil,use_site as site
													from T_USER ");
		$users=$utilisateurs->fetchAll();
        foreach ($users as $key => $user) {
            if ($user['login'] == $login && $user['mdp'] == md5($password)) {
                $this->setData($user);
                return $this;
            }
        }
        return false;
    }
        
    /**
     * Instaure les données $data dans les propriétés de l'objet
     * @param array $data
     * @return \App_Model_User
     */
    public function setData($data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
        return $this;
    }
	/*Retourne un objet du type profil*/
	    public function GetProfil()
    {
        $query = "Select pro_id as id,pro_name as name FROM T_PROFILE WHERE pro_id=".$this->profil.";";
        $statement = Db::getInstance()->query($query);
        return $statement->fetchObject('App_Model_Profile');
    }    
    /* Retourne un objet site */
	public function GetSite()
    {
        $query = "Select sit_id as id,sit_name as name FROM T_SITE WHERE sit_id=".$this->site.";";
        $statement = Db::getInstance()->query($query);
        return $statement->fetchObject('App_Model_Site');
    }
    
    /* Retourne les activités du user */
	public function GetActivity()
    {
        $query = "Select act_id as id, act_name as name,  act_parentId as parent,act_tag as tag
                    from T_ACTIVITY A, TJ_AFFECT AF
                    where A.act_id=AF.aff_activity
                    and aff_user=".$this->id.'
                    and act_actif=\'True\'
                    order by act_name';
        
        $statement = Db::getInstance()->query($query);
        return $statement->fetchAll(PDO::FETCH_CLASS, 'App_Model_Activity');
    }
    
    /* Retourne vrai si le user est affecte a cette activite */
	public function AffectActivity($activity)
    {
        $query = "Select act_id as id, act_name as name,  act_parentId as parent,act_tag as tag
                    from T_ACTIVITY A, TJ_AFFECT AF
                    where A.act_id=AF.aff_activity
                    and aff_user=".$this->id."
                    and aff_activity=".$activity;
        
        $statement = Db::getInstance()->query($query);
        if( $statement->fetch() != null)
        {
           $res=true;
        }
        else
        {
            $res=false;
        }
        return $res;
    }
    /* Retourne les users de l'application*/
	static function GetUsers()
    {
        $query = "Select use_id as id ,use_name as name,use_mail as mail,use_login as login,use_pwd as mdp,
                    use_profile as profil,use_site as site
                    from T_USER";

        $statement = Db::getInstance()->query($query);
        return $statement->fetchAll(PDO::FETCH_CLASS, 'App_Model_User');
    }
    /* Retourne les activités du user pour la diffusion de mail*/
	public function GetActivityMail()
    {
        $query = "Select act_id as id, act_name as name, act_tag as tag, act_parentId as parentID 
                    from TJ_DIFFUSION, T_ACTIVITY
                    where dif_activity=act_id
                    and dif_user=".$this->id;

        $statement = Db::getInstance()->query($query);
        return $statement->fetchAll(PDO::FETCH_CLASS, 'App_Model_Activity');
    }
}
