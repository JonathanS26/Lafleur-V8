<?php

// FONCTIONs POUR L'ACCES A LA BASE DE DONNEES
// Ajouter en t�tes 
// Voir : jeu de caract�res � la connection

/** 
 * Se connecte au serveur de donn�es                     
 * Se connecte au serveur de donn�es � partir de valeurs
 * pr�d�finies de connexion (h�te, compte utilisateur et mot de passe). 
 * Retourne l'identifiant de connexion si succ�s obtenu, le bool�en false 
 * si probl�me de connexion.
 * @return resource identifiant de connexion
 */
function connecterServeurBD() 
{
    $PARAM_hote='localhost'; // le chemin vers le serveur
    $PARAM_port='3306';
    $PARAM_nom_bd='baselafleur1'; // le nom de votre base de donn�es
    $PARAM_utilisateur='root'; // nom d'utilisateur pour se connecter
    $PARAM_mot_passe='root'; // mot de passe de l'utilisateur pour se connecter

    $connect = new PDO('mysql:host='.$PARAM_hote.';port='.$PARAM_port.';dbname='.$PARAM_nom_bd, $PARAM_utilisateur, $PARAM_mot_passe);
 
    return $connect;
}

function lister()
{
    $connexion = connecterServeurBD();
   
    $requete="select * from produit";
    
    $jeuResultat=$connexion->query($requete); // on va chercher tous les membres de la table qu'on trie par ordre croissant
  
    $i = 0;
    $ligne = $jeuResultat->fetch();
    while($ligne)
    {
        $fleur[$i]['image']=$ligne['pdt_image'];
        $fleur[$i]['ref']=$ligne['pdt_ref'];
        $fleur[$i]['designation']=$ligne['pdt_designation'];
        $fleur[$i]['prix']=$ligne['pdt_prix'];
        $ligne=$jeuResultat->fetch();
        $i = $i + 1;
    }
    $jeuResultat->closeCursor();   // fermer le jeu de r�sultat
  
  return $fleur;
}


function ajouter($ref, $des, $prix, $image, $cat,&$tabErr)
{
  // Ouvrir une connexion au serveur mysql en s'identifiant
  $connexion = connecterServeurBD();
    
    // V�rifier que la r�f�rence saisie n'existe pas d�ja
    $requete="select * from produit";
    $requete=$requete." where pdt_ref = '".$ref."';"; 
   
    $jeuResultat=$connexion->query($requete); // on va chercher tous les membres de la table qu'on trie par ordre croissant
  
    //$jeuResultat->setFetchMode(PDO::FETCH_OBJ); // on dit qu'on veut que le r�sultat soit r�cup�rable sous forme d'objet     
    
    $ligne = $jeuResultat->fetch();
    if($ligne)
    {
      $message="Echec de l'ajout : la r�f�rence existe d�j� !";
      ajouterErreur($tabErr, $message);
    }
    else
    {
      // Cr�er la requ�te d'ajout 
       $requete="insert into produit"
       ."(pdt_ref,pdt_designation,pdt_prix,pdt_image, pdt_categorie) values ('"
       .$ref."','"
       .$des."',"
       .$prix.",'"
       .$image."','"
       .$cat."');";
   
        // Lancer la requ�te d'ajout 
        $ok=$connexion->query($requete); // on va chercher tous les membres de la table qu'on trie par ordre croissant
          
        // Si la requ�te a r�ussi
        if ($ok)
        {
          $message = "La fleur a �t� correctement ajout�e";
          ajouterErreur($tabErr, $message);
        }
        else
        {
          $message = "Attention, l'ajout de la fleur a �chou� !!!";
          ajouterErreur($tabErr, $message);
        } 
  
    }
}

function rechercher($des)
{
    $connexion = connecterServeurBD();
    
    $fleur = array();
   
    $requete="select * from produit";
      $requete=$requete." where pdt_designation='".$des."';";
    
    $jeuResultat=$connexion->query($requete); // on va chercher tous les membres de la table qu'on trie par ordre croissant
  
    $i = 0;
    $ligne = $jeuResultat->fetch();
    while($ligne)
    {
        $fleur[$i]['image']=$ligne['pdt_image'];
        $fleur[$i]['ref']=$ligne['pdt_ref'];
        $fleur[$i]['designation']=$ligne['pdt_designation'];
        $fleur[$i]['prix']=$ligne['pdt_prix'];
        $ligne=$jeuResultat->fetch();
        $i = $i + 1;
    }
    $jeuResultat->closeCursor();   // fermer le jeu de r�sultat
  
  return $fleur;
}



function rechercherRef($ref, &$tabErr)
{
    $connexion = connecterServeurBD();
    
    $fleur = array();
   
    $requete="select * from produit";
      $requete=$requete." where pdt_ref='".$ref."';";
    
    $jeuResultat=$connexion->query($requete); // on va chercher tous les membres de la table qu'on trie par ordre croissant
  
    $i = 0;
    $ligne = $jeuResultat->fetch();
    if ($ligne)
    {
        $fleur[$i]['image']=$ligne['pdt_image'];
        $fleur[$i]['ref']=$ligne['pdt_ref'];
        $fleur[$i]['designation']=$ligne['pdt_designation'];
        $fleur[$i]['prix']=$ligne['pdt_prix'];
        $i = $i + 1;
    }
    $jeuResultat->closeCursor();   // fermer le jeu de r�sultat
      // Si la requ�te a r�ussi
      
    if ($i == 0)
    {
      $message = "Aucune fleur ne correspond � cette r�f�rence";
      ajouterErreur($tabErr, $message);
    }
  
  return $fleur;
}

// SECURITE : cryptage du mot de passe
function inscrire($nom, $mdp,&$tabErr)
{

   // Ouvrir une connexion au serveur mysql en s'identifiant
  $connexion = connecterServeurBD();
  
  // Si la connexion au SGBD � r�ussi
  if ($connexion) 
  {
    // V�rifier que le nom saisi n'existe pas d�ja
    $requete="select * from utilisateur";
    $requete=$requete." where nom = '".$nom."';"; 
   $jeuResultat=$connexion->query($requete); // on va chercher tous les membres de la table qu'on trie par ordre croissant
  
    $i = 0;
    $ligne = $jeuResultat->fetch();
    
    if($ligne)
    {
      $message="Echec de l'inscription : le nom existe d�j� !";
      ajouterErreur($tabErr, $message);
 
    }
    else
    {
      // SECURITE Cryptage du mot de passe
      $mdp = md5($mdp);
      // Cr�er la requ�te d'ajout 
      // SECURITE : cryptage du mot de passe     
       $requete="insert into utilisateur"
       ."(nom,mdp,cat) values ('"
       .$nom."','"
       .$mdp."','client');";
       
         // Lancer la requ�te d'ajout 
        $ok=$connexion->query($requete); // on va chercher tous les membres de la table qu'on trie par ordre croissant
          
         
        // Si la requ�te a r�ussi
        if ($ok)
        {
          $message = "Inscription effectu�e";
          ajouterErreur($tabErr, $message);
        }
        else
        {
          $message = "Attention, l'inscription a �chou� !!!";
          ajouterErreur($tabErr, $message);
        } 

    }
    // fermer la connexion
    $jeuResultat->closeCursor();   // fermer le jeu de r�sultat
  }
  else
  {
    $message = "probl�me � la connexion <br />";
    ajouterErreur($tabErr, $message);
  }
}




function modifier($ref, $des, $prix, $image, $cat,&$tabErr)
{
  
  // Ouvrir une connexion au serveur mysql en s'identifiant
  $connexion = connecterServeurBD();
    
    // V�rifier que la r�f�rence saisie n'existe pas d�ja
    $requete="select * from produit";
    $requete=$requete." where pdt_ref = '".$ref."';";
              
   
    $jeuResultat=$connexion->query($requete); // on va chercher tous les membres de la table qu'on trie par ordre croissant
  
    //$jeuResultat->setFetchMode(PDO::FETCH_OBJ); // on dit qu'on veut que le r�sultat soit r�cup�rable sous forme d'objet     
    
    $ligne = $jeuResultat->fetch();
    // Cr�er la requ�te de modification 
  
    $requete= "UPDATE `baselafleur1`.`produit` SET `pdt_designation` = '$des',
    `pdt_prix` = '$prix',
    `pdt_image` = '$image',
    `pdt_categorie` = '$cat' WHERE `produit`.`pdt_ref`='$ref';";
         
        // Lancer la requ�te d'ajout 
        $ok=$connexion->query($requete); // on va chercher tous les membres de la table qu'on trie par ordre croissant
          
        // Si la requ�te a r�ussi
        if ($ok)
        {
          $message = "La fleur a �t� correctement modifi�";
          ajouterErreur($tabErr, $message);
        }
        else
        {
          $message = "Attention, la modification de la fleur a �chou� !!!";
          ajouterErreur($tabErr, $message);
        } 
    }
  


function supprimer($ref, &$tabErr)
{
    $connexion = connecterServeurBD();
    
    $fleur = array();
    $requete="select * from produit";
      $requete=$requete." where pdt_ref='".$ref."';";
      $jeuResultat=$connexion->query($requete);
     $ligne = $jeuResultat->fetch();
     if ($ligne)
     { 
          
    $requete="delete from produit";
    $requete=$requete." where pdt_ref='".$ref."';";
    
    // Lancer la requ�te supprimer
        $ok=$connexion->query($requete); // on va chercher tous les membres de la table qu'on trie par ordre croissant
          
        // Si la requ�te a r�ussi
        if ($ok)
        {
          $message = "La fleur a �t� correctement supprim�e";
          ajouterErreur($tabErr, $message);
        }
        else
        {
          $message = "Attention, la suppression de la fleur a �chou� !!!";
          ajouterErreur($tabErr, $message);
        }
       }
       else
       {
        $message="Echec de la suppression : la r�f�rence n'existe pas";
        ajouterErreur($tabErr, $message);
       } 

}


function rechercherUtilisateur($log, $psw, &$tabErr)
{
    $connexion = connecterServeurBD();
      
    $requete="select * from utilisateur";
      $requete=$requete." where nom='".$log."' and mdp ='".$psw."';";
    $jeuResultat=$connexion->query($requete); // on va chercher tous les membres de la table qu'on trie par ordre croissant
  
    $i = 0;
    $ligne = $jeuResultat->fetch();
    if ($ligne)
    {
        $i = $i + 1;
    }
    $jeuResultat->closeCursor();   // fermer le jeu de r�sultat
      // Si la requ�te a r�ussi
  
  return $i;
}


// Fonction identifier non s�curis�e (pas de requete pr�par�e)

// voivi une injection SQL possible
// Entrer :
// login : abc' OR 1=1;--
// mdp : (laisser vide)

// voivi une autre injection SQL possible
// Entrer :
// login :  X' OR '1=1
// mdp :    X' OR '1=1
 
function ZZZidentifier($nom, $mdp,&$tabErr)
{
  
  // Initialisation de l'identification a �chec
  $ligne = false;

   // Ouvrir une onnexion au serveur mysql en s'identifiant
  $connexion = connecterServeurBD();
  
    // V�rifier que nom et login existent
    $requete="select * from utilisateur where nom ='".$nom."' and mdp ='".$mdp."' ;";
echo $requete;
    $jeuResultat=$connexion->query($requete); // on va chercher tous les membres de la table qu'on trie par ordre croissant
  
    $ligne = $jeuResultat->fetch();

    if($ligne)
    {
        // identification r�ussie
    }
    else
    {
      $message = "Echec de l'identification !!!";
      ajouterErreur($tabErr, $message);
    }
   
  
  // renvoyer les informations d'identification si r�ussi
  return $ligne;
}

// Fonction identifier s�curis�e 
// SECURITE : 
// A. requete pr�par�e
// B. recherche de mot passe avec fonction de cryptage, car cryptage du mot de passe lors de l'inscription


function identifier($nom, $mdp,&$tabErr)
{
  
  // Initialisation de l'identification a �chec
  $ligne = false;

   // Ouvrir une onnexion au serveur mysql en s'identifiant
  $connexion = connecterServeurBD();
  
    // V�rifier que nom et login existent
    $requete="select * from utilisateur where nom = ? and mdp = ? ;";
    
    // Pr�paration de la requete
    $jeuResultat = $connexion->prepare($requete);
    
    // Lancer la requete pr�par�e avec les valeurs pass�es en param�tre
    //SECURITE : mot de passe securise, cond cryptage du mot de passe pass� en parametre
    $jeuResultat->execute(array($nom, md5($mdp)));  
    
    // Extraire la ligne du jeu de r�sultats
    $ligne = $jeuResultat->fetch();

    if($ligne)
    {
        // identification r�ussie
    }
    else
    {
      $message = "Echec de l'identification !!!";
      ajouterErreur($tabErr, $message);
    }
   
  
  // renvoyer les informations d'identification si r�ussi
  return $ligne;
}





?>
