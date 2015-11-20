<?php

/* 
  * Client pour l'API
 */

try
{
  //Creation du client SOAP
  $clientSOAP = new SoapClient( null,
    array (
      'uri' => 'http://statdev/TdB/index.php?view=api/server',
      'location' => 'http://statdev/TdB/index.php?view=api/server',
      'trace' => 1,
      'exceptions' => 0
  ));
  
  $tagSite="amiens";
  $tagActivite="canRep";
   //On appelle une fonction grace a la fonction __call(Fonction, tableau de données)
  /******************Activité du site **************************/
  echo "<br /> Activité du site<br />";
  $activities = $clientSOAP->__call('getActivityTag', array('tag'=>$tagSite));
  foreach ($activities as $activity)
  {
      echo $activity->name."------".$activity->tag."<br />";
  }
  /******************Item sans formule **************************/
  echo "<br />Item sans formule -> à remplir <br />";
  $items = $clientSOAP->__call('getItemsByActivityTag', array('tag'=>$tagActivite));
  foreach ($items as $item)
  {
      echo $item->name."------".$item->tag."<br />";
  }
  /******************Item sans formule **************************/
  echo "<br /> Item sans formule -> vérifier les formules<br />";
  $items = $clientSOAP->__call('getItemsFormByActivityTag', array($tagActivite));
  foreach ($items as $item)
  {
      echo $item->name."------".$item->tag."------".$item->formula."<br />";
  }
  /*******************Insertion d'un donnée****************************/
  //Le tableau de données contient  array(tagSite,tagActivity,tagItem, date,value)
  //$clientSOAP->__call('setValue',array('$site','$tagActivite','AT','25-10-2015',99.9));
  
  
}
catch(SoapFault $f)
{
  echo $f->getMessage();
}