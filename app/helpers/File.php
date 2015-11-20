<?php

/**
 * Classe utilitaire fichier/dossier
 *
 * @author Maxime Cazé <maximecaze@coriolis.fr>
 */
class App_Helper_File 
{
    /**
     * Fonction de copie de répertoire et de son contenu
     * 
     * @param string $source
     * @param string $dest
     * @param boolean $del_after_copy : supprime après la copie (fonction "couper" plutôt que "copier")
     */
    public static function copyDir($source, $dest, $del_after_copy = false)
    {
        // si $source est un dossier
        if (is_dir($source)) {
            // on ouvre le dossier $source
            if ($dh = opendir($source)) {
                // parse les fichiers et les dossiers contenus dans le dossier $source
                while (($file = readdir($dh)) !== false) {
                    // si le dossier destinataire $dest n'existe pas, on le crée
                    if (!is_dir($dest)) mkdir ($dest, 0777);

                    // si le fichier pointé est un dossier, utilisation de la récurvisité pour copier le dossier
                    if (is_dir($source . $file) && $file != '..'  && $file != '.') {
                        App_Helper_File::copyDir($source . $file . DS, $dest . $file . DS);    
                    }
                    // sinon c'est un fichier simple, on le copie
                    elseif ($file != '..'  && $file != '.') {
                        copy($source . $file, $dest . $file);
                        if ($del_after_copy === true) {
                            unlink($source.$file);
                        }
                    }

                }
                closedir($dh);
            }
        } 
    }
    
    /**
     * Nettoie un répertoire (récursivement)
     * @param string $source
     */
    public static function cleanDir($source)
    {
        // si $source est un dossier
        if (is_dir($source)) {
            // on ouvre le dossier $source
            if ($dh = opendir($source)) {
                // parse les fichiers et les dossiers contenus dans le dossier $source
                while (($file = readdir($dh)) !== false) {
                    if ($file != '..' && $file != '.') {
                        if (is_dir($source . $file)) {
                            self::cleanDir($source . $file);                            
                            unlink($source . $file);
                        } elseif (is_file($source . $file)) {
                            unlink($source . $file);
                        }
                    }
                }
                closedir($dh);
            }
        } 
    }
    
    /**
     * @param int $bytes Number of bytes (eg. 25907)
     * @param int $precision [optional] Number of digits after the decimal point (eg. 1)
     * @return string Value converted with unit (eg. 25.3KB)
     */
    public static function formatBytes($bytes, $precision = 2, $dec_sep = ',', $th_sep = '') 
    {
        $unit = array('O', 'Ko', 'Mo', 'Go');
        $exp = floor(log($bytes, 1024)) | 0;
        return number_format(round($bytes / (pow(1024, $exp)), $precision), $precision, $dec_sep, $th_sep) . ' ' . $unit[$exp];
    }
}
