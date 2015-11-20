<?php
/*
 * Envoi des mails automatique
 */

//Gestion des différentes dates du tableau
$dateDuJour=date('d-m-Y');
$numSemaine=  App_Helper_Date::getWeek($dateDuJour);
$moisPrecedent=date('m')-1;
$moisEnCours=date('m');
$tableauDate= array('Objectif'=>'Objectif','M-1'=>$moisPrecedent,'M'=>$moisEnCours,'S '.$numSemaine=>$numSemaine,$dateDuJour=>$dateDuJour);
$premierJourSemaine=App_Helper_Date::getFirstDayOfWeek($numSemaine);
$dernierJourSemaine=App_Helper_Date::getLastDayOfWeek($numSemaine);
$premierJourMois=App_Helper_Date::getFirstDayOfMonth($moisEnCours);
$dernierJourDuMois=App_Helper_Date::getLastDayOfMonth($moisEnCours);
$premierJourMoisPrec=App_Helper_Date::getFirstDayOfMonth($moisPrecedent);
$dernierJourDuMoisPrec=App_Helper_Date::getLastDayOfMonth($moisPrecedent);
/**************************************************************************/
$users=App_Model_User::GetUsers();
foreach ($users as $user)
{
    if($user->mail!=null)
    {
        echo "<br />".$user->name."<br />";
        //Récupération des activités pour lesquelles le user est inscrit
        $activities=$user->GetActivityMail();
        //Initialisation de la variable comportant les tableaux de stats
        $htmlTableau="<html>";
        //On boucle sur toutes les activités auxquelles le user c'est inscrit
        foreach($activities as $activity)
        {
            //Declaration du tableau d'item
            // Et de tableau comportant les item avec et sans formule
            $tableauItems= $activity->getItem();
            $itemFormule=array();
            $itemSansFormule=array();
            foreach($tableauItems as $item)
            {
                if($item->formula != null)
                {
                    $itemFormule[]=$item;
                }
                else
                {
                    $itemSansFormule[]=$item;
                }
            }
            //Création du tableau de stats
            $tableauStat=array();
            // construction du header du tableau
            foreach ($tableauDate as $cle => $valeur) 
            {
                $tableauStat[null][$valeur] = $cle; //Position null pour avoir la 1ere case vide
            }
            //Remplissage des champs item et objectifs du tableau
            foreach($tableauItems as $item)
            {
                $tableauStat[$item->name]=array();
                foreach($tableauDate as $cleColonne => $colonne)
                {
                    if ($cleColonne == 'Objectif') 
                    {
                        $tableauStat[$item->name][$colonne]=$item->objective;
                        if($item->type=="percentage")
                        {
                            $tableauStat[$item->name][$colonne]=$tableauStat[$item->name][$colonne]."%";
                        }
                    } 
                    else 
                    {            
                        $tableauStat[$item->name][$colonne]=null;
                    }
                }
            }
            //Récuperation du site du user sinon tous les sites de l'activites
            if($user->site!=null)
            {
                $site=new App_Model_Site();
                $site=$site->load($user->site);
                $sites=array($site);
            }
            else
            {
                $sitesActivite=$activity->getSite();
                $sites=array();
                foreach($sitesActivite as $site)
                {
                    $sites[]=$site;
                }
            }
            //Pour chaque site de l'activité du user
            foreach ($sites as $site)
            {
                 //Remise à null des valeurs du tableaux
                foreach($tableauItems as $item)
                {
                        foreach($tableauDate as $colonne)
                        {
                           if($colonne!="Objectif")
                           {       
                               $tableauStat[$item->name][$colonne]=null;
                           }
                        }
                }
                //Remplissage du tableau avec les items sans formules
                foreach ($itemSansFormule as $item)
                {
                    foreach($tableauDate as $cleColonne => $colonne)
                    {
                        if($cleColonne!="Objectif")
                        {
                            //Different cas possible de plages de dates
                            if($cleColonne=="M") //Mois en cours
                            {
                                $dateDebut=$premierJourMois;
                                $dateFin=$dernierJourDuMois;
                            }
                            elseif($cleColonne=="M-1")  //Mois precedent
                            {
                                $dateDebut=$premierJourMoisPrec;
                                $dateFin=$dernierJourDuMoisPrec;
                            }
                            elseif ($cleColonne=="S ".$numSemaine) //Pour la semaine
                            {
                                $dateDebut=$premierJourSemaine;
                                $dateFin=$dernierJourSemaine;
                            }
                            elseif ($cleColonne==$dateDuJour)//Pour la date du jour
                            {
                                $dateDebut=$dateDuJour;
                                $dateFin=$dateDuJour;
                            }

                             //Recuperation de la valeur
                            $value = new App_Model_Value();
                            $values=App_Model_Value::getValueByIdItem($site->id,$item->id,$dateDebut, $dateFin);
                            if($values!=null)
                            {
                                foreach($values as $value)
                                {
                                    $tableauStat[$item->name][$colonne]+=$value->value;
                                }                
                            }
                            else
                            {
                                $tableauStat[$item->name][$colonne]=null;
                            }      
                        }
                    }
                }
                //Remplissage du tableau avec les items ayant une formule
                foreach($itemFormule as $item)
                {
                    foreach($tableauDate as $cleColonne => $colonne)
                    {
                        if($cleColonne!="Objectif")
                        {
                            //Different cas possible de plages de dates
                            if($cleColonne=="M") //Mois en cours
                            {
                                $dateDebut=$premierJourMois;
                                $dateFin=$dernierJourDuMois;
                            }
                            elseif($cleColonne=="M-1")  //Mois precedent
                            {
                                $dateDebut=$premierJourMoisPrec;
                                $dateFin=$dernierJourDuMoisPrec;
                            }
                            elseif ($cleColonne=="S ".$numSemaine) //Pour la semaine
                            {
                                $dateDebut=$premierJourSemaine;
                                $dateFin=$dernierJourSemaine;
                            }
                            elseif($cleColonne==$dateDuJour) //Pour la date du jour
                            {
                                $dateDebut=$dateDuJour;
                                $dateFin=$dateDuJour;
                            }
                            $result=null;
                            $formuleBase=$item->formula;
                            $formuleFinale=$formuleBase;
                            //pregmatch Recherche des variables dans la formule
                            $pattern='{\{\{[a-zA-Z0-9]*\}\}}';
                            preg_match_all($pattern, $formuleBase, $matches);
                            $evaluer=true; //Boolean pour savoir si on doit evaluer la formule
                            foreach($matches as $tableau) //Boucle car matches est un tableau de de tableau de données
                            {
                                foreach($tableau as $variableDeb)
                                {
                                    //Retire les caracteres speciaux de la variable
                                    $variableModif = str_replace("{{", "",$variableDeb);
                                    $variableModif = str_replace("}}", "",$variableModif);
                                    //On effectue la requete avec la variable
                                    $donnee=App_Model_Value::getSumValueByTagItemSite($site->id,$activity->id,$variableModif,$dateDebut, $dateFin);

                                    if(is_null($donnee)) //Une des valeur est null, on n'evalue pas la formule
                                    {
                                        $evaluer=false;
                                    }
                                    $formuleFinale=str_replace($variableDeb,$donnee,$formuleFinale);
                                }               
                            }
                            if($evaluer==true)
                            {
                                eval("\$result = $formuleFinale;");
                                if($result!=null)
                                {
                                    $tableauStat[$item->name][$colonne]=round($result,2);
                                }
                            }
                        }
                    }
                }
                //Création du code html du tableau à envoyé par mail
                $cssTableau="text-align:center;width:100px;";
                $cssCritere="text-align:left;width:100px;";
                $cssObjTrue="text-align:center;width:100px;background-color:#00A65A;color: #FFFFFF;";
                $cssObjFalse="text-align:center;width:100px;background-color:#D33724;color: #FFFFFF;";
                $cssCadre='border="0";cellpadding="0";cellspacing="0";height="100%";width="100%;font-family: "Source Sans Pro","Helvetica Neue","Helvetica","Arial","sans-serif";' ;
                //Debut du tableau en html
                $htmlTableau.= "<table id='tabStat' style='".$cssCadre."'>
                                <thead>
                                    <tr>
                                        <th style='".$cssTableau."'>".$activity->name.'-'.$site->name."</th>";                            
                                        foreach($tableauDate as $cleColonne=>$colonne)
                                        {
                                            $htmlTableau.= "<th style='".$cssTableau."'>".$cleColonne."</th>";
                                        }
                                    $htmlTableau.= '</tr>
                                </thead>
                <tbody>';
                foreach($tableauItems as $item)
                {
                    if($item->visibility == true) //Afiche seulement les items ayant la propriete visibility
                    {
                        $htmlTableau.= "<tr><td style='".$cssCritere."'>".$item->name."</td>";
                        foreach($tableauDate as $colonne)
                        {
                           if($colonne!="Objectif")
                           {       
                                    $valeur=$tableauStat[$item->name][$colonne];
                                    $operateur=$item->operator;
                                    $objectif=$item->objective;
                                    $resultat=false;
                                    if(!is_null($valeur)) //Si il y'a une valeur
                                    {
                                        if(!is_null($objectif)) //Si il ya un objectif
                                        {
                                            eval("\$result = $valeur$operateur$objectif ;");
                                            if($result)
                                            {
                                                $htmlTableau.= "<td style='".$cssObjTrue."'>".$tableauStat[$item->name][$colonne]."</td>";                                            
                                            }
                                            else
                                            {
                                                $htmlTableau.= "<td style='".$cssObjFalse."'>".$tableauStat[$item->name][$colonne]."</td>";
                                            }                                            
                                        }
                                        else
                                        {
                                            $htmlTableau.= "<td style='".$cssTableau."' >".$tableauStat[$item->name][$colonne]."</td>";
                                        }
                                    }
                                    else
                                    {
                                        $htmlTableau.= "<td style='".$cssTableau."' >".$tableauStat[$item->name][$colonne]."</td>";
                                    }
                           }
                           else
                           {
                                $htmlTableau.= "<td style='".$cssTableau."' >".$tableauStat[$item->name][$colonne]."</td>";
                           }
                        }
                    }
                    $htmlTableau.= "</tr>";
                }
            $htmlTableau.= '</tbody>'
            . '</table><br /><br /><br /><br />';
            }
        }
        $htmlTableau.="</html>";
        echo $htmlTableau;
        //Envoi du mail comportant les differents tableaux
        $sujet = 'Stat du: '.$dateDuJour;
        $message = $htmlTableau;
        $destinataire = $user->mail;//,alexandreroger@coriolis.fr,maximecaze@coriolis.fr"; 
        $headers = "From: \"Tableau de bord\"\n";
        //$headers .= "Reply-To: maximecaze@coriolis.fr, alexandreroger@coriolis.fr\n";
        $headers .= "Content-Type: text/html; charset=\"iso-8859-1\"";
        if(mail($destinataire,$sujet,$message,$headers))
        {
                echo "L'email a bien été envoyé.";
        }
        else
        {
                echo "Une erreur c'est produite lors de l'envois de l'email.";
        }
    }  
}
