<?php
/*
 * Statistique d"une activité
 */
//Choisir les items à visualiser
$visibility=true;
/**********************DECLARATION DES VARIABLES GLOBALES****************************/
//Declaration de l'activite
$activity = new App_Model_Activity();
$activity= $activity->load($_GET['act']);
//Profil de l'utilisateur
$profil = new App_Model_Profile();
$profil=$profil->load(App_Helper_User_Session::getUserSession()->profil); 
//Recuperation du ou des sites suivant le site de l'utilisateur
$sitesActivite=  $activity->getSite();
$sites=array();
foreach($sitesActivite as $site)
{
    $sites[]=$site;
}
/*
if(App_Helper_User_Session::getUserSession()->site==null)
{
    $site=new App_Model_Site();
    $site=$site->load(App_Helper_User_Session::getUserSession()->site);
    $sites=array($site);
}
else
{
    $sitesActivite=  $activity->getSite();
    $sites=array();
    foreach($sitesActivite as $site)
    {
        $sites[]=$site;
    }
}*/
//Gestion des différentes dates du tableau
$dateDuJour=date('d-m-Y');
//Specifique a EDF,Orange car les données sont intégré a j-1
if($activity->name == 'EDF')
{
    $dateDuJour = date("d-m-Y", mktime(1, 1, 1, date("m"), date("d") - 1, date("Y")));
}
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
/***************************************************************************************/
//Déclaration des differents onglets
echo '<div class="row">
	<div class="col-xs-12">
		<div class="box">
			<div class="box-header">
				<h3 class="box-title">'.$activity->name.'</h3>
			</div><!-- /.box-header -->
			<div class="box-body table-responsive">
			<div class="col-md-12">
				  <!-- Custom Tabs -->
				  <div class="nav-tabs-custom">
					<ul class="nav nav-pills">';
                     $nSite = 0;
                     foreach($sites as $site)
                        {
                            echo '<li class="active"><a href="#'.$site->name.'" data-toggle="tab" id="'.$site->name.'-tab">'.$site->name.'</a></li>';
                        }
                        if($profil->name=="Admin" or $profil->name=="Directeur" or $profil->name=="Technicien")
                        {    
                            echo '<li class="active"><a href="#Global" data-toggle="tab" id="Global-tab">Global</a></li>';
                        }
					echo '</ul>
					<div class="tab-content">';
$nSite = 0;
//Tableau comportant les tableaux de stats des différents sites pour l'onglet global
$tableauGlobal=array();
/*********************Pour chaque site remplissage du tableau de stat ****************/
 foreach ($sites as $site)
{
     
    //Date de la derniere mise à jour des valeurs, pour outes les valeurs allant du 1er jour du mois M-1 à la journée d'aujoud'hui
    $lastUpdate=  App_Model_Value::getLastUpdateByActivitySite($activity->id,$site->id,$premierJourMoisPrec,$dateDuJour);
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
                $pattern='{\{\{[a-zA-Z0-9\_]*\}\}}';
                preg_match_all($pattern, $formuleBase, $matches);
                $evaluer=true; //Boolean pour savoir si on doit evaluer la formule
                foreach($matches as $tableau) //Boucle car matches est un tableau de de tableau de données
                {
                    foreach($tableau as $variableDeb)
                    {
                        //Retire les caracteres speciaux de la variable
                        $variableModif = str_replace("{{", "",$variableDeb);
                        $variableModif = str_replace("}}", "",$variableModif);
                        //On récupere la donnée avec la variable
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
   //Ecriture du tableau
echo '<div class="tab-pane active in" id="'.$site->name.'"> 
        <a href="index.php?view=saisie&act='.$activity->id.'&sit='.$site->id.'">
           <button style="float: right" class="btn btn-info btn-sm" title="" data-toggle="tooltip" data-original-title="Editer">
               <i class="fa fa-gear">
               </i>
           </button>
       </a>
        <table id="tabStat" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th></th>';                            
                    foreach($tableauDate as $cleColonne=>$colonne)
                    {
                        echo "<th style='text-align:center'>".$cleColonne."</th>";
                    }
                echo '</tr>
            </thead>
            <tbody>';
                foreach($tableauItems as $item)
                {
                    if($item->visibility == $visibility) //Afiche seulement les items ayant la propriete visibility
                    {
                        $formule=null;
                        if($item->formula!=null)
                        {
                            $formule = str_replace("{{", "",$item->formula);
                            $formule = str_replace("}}", "",$formule);
                        }
                        echo '<tr><td data-toggle="tooltip" data-container="body" data-placement="top" title="'.$formule.'">'.$item->name.'</td>';
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
                                                echo "<td style='text-align:center' class='bg-green'>".$tableauStat[$item->name][$colonne]."</td>";                                            
                                            }
                                            else
                                            {
                                                echo "<td style='text-align:center' class='bg-red-active'>".$tableauStat[$item->name][$colonne]."</td>";
                                            }                                            
                                        }
                                        else
                                        {
                                            echo "<td style='text-align:center' >".$tableauStat[$item->name][$colonne]."</td>";
                                        }
                                    }
                                    else
                                    {
                                        echo "<td style='text-align:center' >".$tableauStat[$item->name][$colonne]."</td>";
                                    }
                           }
                           else
                           {
                                echo "<td style='text-align:center' >".$tableauStat[$item->name][$colonne]."</td>";
                           }
                        }
                    }
                    echo "</tr>";
                }
            echo '</tbody>
        </table>
        <div class="box-body chart-responsive">
            <div class="chart" id="chart-'.$site->name.'" style="height: 300px;"> 
        </div>
         <div>
            <i> Dernière mise à jour: ';
                echo $lastUpdate;
        echo '</i>
        </div>
        </div><!-- /.box-body -->
    </div><!-- /.tab-pane -->';
?>
<!-- Javascript des graphiques-->
<script type="text/javascript">
      $(function ()
      {
        "use strict";
        //Bar chart
        var line<?php echo $site->name ?> = new Morris.Bar({
            element: 'chart-<?php echo $site->name ?>',
            data: [
                  <?php foreach ($tableauDate as $keyDate=>$date)
                        {
                            if($date!= "Objectif")
                            {?>
                                {y: ' <?php echo $keyDate ?>',
                                <?php foreach($tableauItems as $item)
                                {
                                    if($item->graphique==True)
                                    {?>
                                        '<?php echo $item->name ?>' : <?php (!is_null($tableauStat[$item->name][$date])) ? print $tableauStat[$item->name][$date] : print 0 ?> ,
                                    <?php
                                    }
                                }?>
                                },
                            <?php
                            }
                        } ?>
                ],
            xkey: 'y',
            ykeys: [
                    <?php foreach ($tableauItems as $item) 
                        {
                            if($item->graphique==True)
                            { ?>
                            '<?php echo $item->name ?>',
                            <?php }
                        }?>
                    ],
            labels: [
                    <?php foreach ($tableauItems as $item) 
                         {
                            if($item->graphique==True)
                            { ?>
                            '<?php echo $item->name ?>',
                            <?php }
                        }?>
                    ]
          });     
     });
    
</script>
<?php
$tableauGlobal[]=$tableauStat;
}
/********************* Onglet global *********************/
if($profil->name=="Admin" or $profil->name=="Directeur" or $profil->name=="Technicien")
{
    //Date de la derniere mise à jour des valeurs, pour toutes les valeurs allant du 1er jour du mois M-1 à la journée d'aujoud'hui pour l'activité
    $lastUpdate=  App_Model_Value::getLastUpdateByActivity($activity->id,$premierJourMoisPrec,$dateDuJour);   
    if(count($tableauGlobal)>0) //Si il ya plusieurs sites
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
                    $value=App_Model_Value::getSumValueByItem($activity->id,$item->id,$dateDebut, $dateFin);
                    if($value!=null)
                    {
                        $tableauStat[$item->name][$colonne]=$value;
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
                    $pattern='{\{\{[a-zA-Z0-9\_]*\}\}}';
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
                            $donnee=App_Model_Value::getSumValueByTagItem($activity->id,$variableModif,$dateDebut, $dateFin);

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
           //Ecriture du tableau
        echo '<div class="tab-pane active in" id="Global">
                <table id="tabStat" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th></th>';                            
                            foreach($tableauDate as $cleColonne=>$colonne)
                            {
                                echo "<th style='text-align:center'>".$cleColonne."</th>";
                            }
                        echo '</tr>
                    </thead>
                    <tbody>';
                        foreach($tableauItems as $item)
                        {
                            if($item->visibility==$visibility) //Affiche seulement les items ayant la propriete visibility
                            {
                                $formule=null;
                                if($item->formula!=null)
                                {
                                    $formule = str_replace("{{", "",$item->formula);
                                    $formule = str_replace("}}", "",$formule);
                                }
                                echo '<tr><td data-toggle="tooltip" data-container="body" data-placement="top" title="'.$formule.'">'.$item->name.'</td>';
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
                                                        echo "<td style='text-align:center' class='bg-green'>".$tableauStat[$item->name][$colonne]."</td>";                                            
                                                    }
                                                    else
                                                    {
                                                        echo "<td style='text-align:center' class='bg-red-active'>".$tableauStat[$item->name][$colonne]."</td>";
                                                    }                                            
                                                }
                                                else
                                                {
                                                    echo "<td style='text-align:center' >".$tableauStat[$item->name][$colonne]."</td>";
                                                }
                                            }
                                            else
                                            {
                                                echo "<td style='text-align:center' >".$tableauStat[$item->name][$colonne]."</td>";
                                            }
                                   }
                                   else
                                   {
                                        echo "<td style='text-align:center' >".$tableauStat[$item->name][$colonne]."</td>";
                                   }
                                }
                            }
                            echo "</tr>";
                        }
                    echo '</tbody>
                </table>
                <div class="box-body chart-responsive">
                    <div class="chart" id="chart-Global" style="height: 300px;"> 
                    </div>
                </div><!-- /.box-body -->
                <div>
                <i> Dernière mise à jour: ';
                    echo $lastUpdate;
                echo '</i>
                </div>
            </div><!-- /.tab-pane -->';
    ?>
<!-- Javascript des graphiques-->
<script type="text/javascript">
      $(function ()
      {
        "use strict";
        //Bar chart
        var lineGlobal = new Morris.Bar({
            element: 'chart-Global',
            data: [
                  <?php foreach ($tableauDate as $keyDate=>$date)
                        {
                            if($date!= "Objectif")
                            {?>
                                {y: ' <?php echo $keyDate ?>',
                                <?php foreach($tableauItems as $item)
                                {
                                    if($item->graphique==True)
                                    {?>
                                        '<?php echo $item->name ?>' : <?php (!is_null($tableauStat[$item->name][$date])) ? print $tableauStat[$item->name][$date] : print 0 ?> ,
                                    <?php
                                    }
                                }?>
                                },
                            <?php
                            }
                        } ?>
                ],
            xkey: 'y',
            ykeys: [
                    <?php foreach ($tableauItems as $item) 
                        {
                            if($item->graphique==True)
                            { ?>
                            '<?php echo $item->name ?>',
                            <?php }
                        }?>
                    ],
            labels: [
                    <?php foreach ($tableauItems as $item) 
                         {
                            if($item->graphique==True)
                            { ?>
                            '<?php echo $item->name ?>',
                            <?php }
                        }?>
                    ]
          });     
     });
    
</script>
<?php
    }
}
?>
					</div><!-- /.tab-content -->
				  </div><!-- nav-tabs-custom -->
				</div><!-- /.col -->
			</div><!-- /.box-body -->
		</div><!-- /.box -->
	</div>
</div> 

<script type="text/javascript">

     //Fonction qui s'execute a la fin du chargement de la page, permet d'activer les tableaux dans chaques tab
    window.onload = function ()
    { 
        <?php 
        foreach ($sites as $site)
        {?>
            $("#<?php echo $site->name ?>-tab").parent().removeClass('active');//desactive the profile tab
            $('#<?php echo $site->name ?>').removeClass('active in');//hide home content
        <?php
        } ?>
         //Pour l'onglet global
        $("#Global-tab").parent().removeClass('active');//desactive the profile tab
        $('#Global').removeClass('active in');//hide home content       
        //Reactive le premier onglet
        $("#<?php echo $sites[0]->name ?>-tab").parent().addClass('active');//active the profile tab
        $('#<?php echo $sites[0]->name ?>').addClass('active in');//show home content   
    };
</script>     