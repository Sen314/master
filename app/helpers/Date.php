<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Date
 *
 * @author vava
 */
class App_Helper_Date {
    //Retourne le numero de la semaine
    static function getWeek($date)
    {
        $good_format=strtotime ($date);
        return date('W',$good_format);
    }
    
    //Retourne le 1er jour de la semaine en cours
    static function getFirstDayOfWeek()
    {
        return date('d-m-Y', mktime(0, 0, 0, date('m'), date('d')-date('N')+1, date('Y')));
    }
    //Retourne le dernier jour de la semaine en cours
    static function getLastDayOfWeek()
    {
        return date('d-m-Y', mktime(0, 0, 0, date('m'), date('d')-date('N')+7, date('Y')));
    }
    //Retourne le 1er jour du mois passé en parametre
    static function getFirstDayOfMonth($mois)
    {
        return date('d-m-Y', mktime(0, 0, 0, $mois, 1, date('Y')));
    }
    //Retourne le dernier jour mois passé en parametre
    static function getLastDayOfMonth($mois)
    {
        $moisEnCours = mktime( 0, 0, 0, $mois, 1, date('Y') ); 
        $nbJoursMois=date("t",$moisEnCours);
        return date('d-m-Y', mktime(0,0,0,$mois,$nbJoursMois,date('Y')));
    }
}
