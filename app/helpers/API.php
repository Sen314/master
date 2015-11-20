<?php

class App_Helper_API
{
/*
* Fonction getItemsByActivityTag sert à récuperer les items en fonction du tag de l'activité qui n'ont pas de formule
* @param $tag String tag de l'activité
* @return $items Tableau d'items (APP_MODEL_ITEM)
*/
  public function getItemsByActivityTag($tag)
  {
        $activity=App_Model_Activity::getActivityByTag($tag);
        $items=$activity->getItemWhithoutForm();
        return $items;
  }
  /*
* Fonction getItemsByActivityTag sert à récuperer les items en fonction du tag de l'activité qui ont une formule
* @param $tag String tag de l'activité
* @return $items Tableau d'items (APP_MODEL_ITEM)
*/
  public function getItemsFormByActivityTag($tag)
  {
        $activity=App_Model_Activity::getActivityByTag($tag);
        $items=$activity->getItemForm();
        return $items;
  }
    /*
* Fonction getActivityTag sert à récuperer les tag des activité du site
* @param $tag String tag du site
* @return $items Tableau d'items (APP_MODEL_ITEM)
*/
  public function getActivityTag($tag)
  {
        $site=  App_Model_Site::getSiteByTag($tag);
        $activities=$site->getActivity();
        return $activities;
  }
  /*
* Fonction setValue insert une valeur en fonction du tag du site, de l'item de l'activite
* @param $tagSite String tag du site
* @param $tagActivity String tag de l'activity
   * @param $tagItem String tag de l'item
   * @param $date Date Date de la valeur
   * @param $value Int/Double valeur à ajouter
*/
  public function setValue($tagSite,$tagActivity,$tagItem,$date,$value)
  {
      App_Model_Value::setValueByTag($tagSite,$tagActivity,$tagItem,$date,$value);
  }
}
