<?php
/** 
 * Script de contrôle et d'affichage du cas d'utilisation "Ajouter"
 * @package default
 * @todo  RAS
 */
 
$repInclude = './include/';
$repVues = './vues/';

require($repInclude . "_init.inc.php");
  
$uneDes=lireDonneePost("des", "");

if (count($_POST)==0)
{
  $etape = 1;
}
else
{
  $etape = 2;
  $lafleur=rechercher($uneDes);
  if (count($lafleur) == 0)
  {
    $avertissement = 1;
    $messageAvert = "Aucune fleur n'a été trouvée";    
  }
}

// Construction de la page Rechercher
// pour l'affichage (appel des vues)
include($repVues."entete.php") ;
include($repVues."menu.php") ;
include($repVues ."erreur.php");
if ($etape==1)
{
  include($repVues."vRechercher.php"); ;
}
else
{
  include($repVues."vFleurs.php") ;
}
include($repVues."pied.php") ;
?>
  

