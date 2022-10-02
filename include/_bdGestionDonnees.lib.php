<?php

// FONCTIONs POUR L'ACCES A LA BASE DE DONNEES
// Ajouter en têtes 
// Voir : jeu de caractères à la connection

/** 
 * Se connecte au serveur de données                     
 * Se connecte au serveur de données à partir de valeurs
 * prédéfinies de connexion (hôte, compte utilisateur et mot de passe). 
 * Retourne l'identifiant de connexion si succès obtenu, le booléen false 
 * si problème de connexion.
 * @return resource identifiant de connexion
 */
function connecterServeurBD() 
{
    $PARAM_hote='localhost'; // le chemin vers le serveur
    $PARAM_port='3306';
    $PARAM_nom_bd='baselafleur1'; // le nom de votre base de données
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
    $jeuResultat->closeCursor();   // fermer le jeu de résultat
  
  return $fleur;
}


function ajouter($ref, $des, $prix, $image, $cat,&$tabErr)
{
  // Ouvrir une connexion au serveur mysql en s'identifiant
  $connexion = connecterServeurBD();
    
    // Vérifier que la référence saisie n'existe pas déja
    $requete="select * from produit";
    $requete=$requete." where pdt_ref = '".$ref."';"; 
   
    $jeuResultat=$connexion->query($requete); // on va chercher tous les membres de la table qu'on trie par ordre croissant
  
    //$jeuResultat->setFetchMode(PDO::FETCH_OBJ); // on dit qu'on veut que le résultat soit récupérable sous forme d'objet     
    
    $ligne = $jeuResultat->fetch();
    if($ligne)
    {
      $message="Echec de l'ajout : la référence existe déjà !";
      ajouterErreur($tabErr, $message);
    }
    else
    {
      // Créer la requête d'ajout 
       $requete="insert into produit"
       ."(pdt_ref,pdt_designation,pdt_prix,pdt_image, pdt_categorie) values ('"
       .$ref."','"
       .$des."',"
       .$prix.",'"
       .$image."','"
       .$cat."');";
   
        // Lancer la requête d'ajout 
        $ok=$connexion->query($requete); // on va chercher tous les membres de la table qu'on trie par ordre croissant
          
        // Si la requête a réussi
        if ($ok)
        {
          $message = "La fleur a été correctement ajoutée";
          ajouterErreur($tabErr, $message);
        }
        else
        {
          $message = "Attention, l'ajout de la fleur a échoué !!!";
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
    $jeuResultat->closeCursor();   // fermer le jeu de résultat
  
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
    $jeuResultat->closeCursor();   // fermer le jeu de résultat
      // Si la requête a réussi
      
    if ($i == 0)
    {
      $message = "Aucune fleur ne correspond à cette référence";
      ajouterErreur($tabErr, $message);
    }
  
  return $fleur;
}

// SECURITE : cryptage du mot de passe
function inscrire($nom, $mdp,&$tabErr)
{

   // Ouvrir une connexion au serveur mysql en s'identifiant
  $connexion = connecterServeurBD();
  
  // Si la connexion au SGBD à réussi
  if ($connexion) 
  {
    // Vérifier que le nom saisi n'existe pas déja
    $requete="select * from utilisateur";
    $requete=$requete." where nom = '".$nom."';"; 
   $jeuResultat=$connexion->query($requete); // on va chercher tous les membres de la table qu'on trie par ordre croissant
  
    $i = 0;
    $ligne = $jeuResultat->fetch();
    
    if($ligne)
    {
      $message="Echec de l'inscription : le nom existe déjà !";
      ajouterErreur($tabErr, $message);
 
    }
    else
    {
      // SECURITE Cryptage du mot de passe
      $mdp = md5($mdp);
      // Créer la requête d'ajout 
      // SECURITE : cryptage du mot de passe     
       $requete="insert into utilisateur"
       ."(nom,mdp,cat) values ('"
       .$nom."','"
       .$mdp."','client');";
       
         // Lancer la requête d'ajout 
        $ok=$connexion->query($requete); // on va chercher tous les membres de la table qu'on trie par ordre croissant
          
         
        // Si la requête a réussi
        if ($ok)
        {
          $message = "Inscription effectuée";
          ajouterErreur($tabErr, $message);
        }
        else
        {
          $message = "Attention, l'inscription a échoué !!!";
          ajouterErreur($tabErr, $message);
        } 

    }
    // fermer la connexion
    $jeuResultat->closeCursor();   // fermer le jeu de résultat
  }
  else
  {
    $message = "problème à la connexion <br />";
    ajouterErreur($tabErr, $message);
  }
}




function modifier($ref, $des, $prix, $image, $cat,&$tabErr)
{
  
  // Ouvrir une connexion au serveur mysql en s'identifiant
  $connexion = connecterServeurBD();
    
    // Vérifier que la référence saisie n'existe pas déja
    $requete="select * from produit";
    $requete=$requete." where pdt_ref = '".$ref."';";
              
   
    $jeuResultat=$connexion->query($requete); // on va chercher tous les membres de la table qu'on trie par ordre croissant
  
    //$jeuResultat->setFetchMode(PDO::FETCH_OBJ); // on dit qu'on veut que le résultat soit récupérable sous forme d'objet     
    
    $ligne = $jeuResultat->fetch();
    // Créer la requête de modification 
  
    $requete= "UPDATE `baselafleur1`.`produit` SET `pdt_designation` = '$des',
    `pdt_prix` = '$prix',
    `pdt_image` = '$image',
    `pdt_categorie` = '$cat' WHERE `produit`.`pdt_ref`='$ref';";
         
        // Lancer la requête d'ajout 
        $ok=$connexion->query($requete); // on va chercher tous les membres de la table qu'on trie par ordre croissant
          
        // Si la requête a réussi
        if ($ok)
        {
          $message = "La fleur a été correctement modifié";
          ajouterErreur($tabErr, $message);
        }
        else
        {
          $message = "Attention, la modification de la fleur a échoué !!!";
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
    
    // Lancer la requête supprimer
        $ok=$connexion->query($requete); // on va chercher tous les membres de la table qu'on trie par ordre croissant
          
        // Si la requête a réussi
        if ($ok)
        {
          $message = "La fleur a été correctement supprimée";
          ajouterErreur($tabErr, $message);
        }
        else
        {
          $message = "Attention, la suppression de la fleur a échoué !!!";
          ajouterErreur($tabErr, $message);
        }
       }
       else
       {
        $message="Echec de la suppression : la référence n'existe pas";
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
    $jeuResultat->closeCursor();   // fermer le jeu de résultat
      // Si la requête a réussi
  
  return $i;
}


// Fonction identifier non sécurisée (pas de requete préparée)

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
  
  // Initialisation de l'identification a échec
  $ligne = false;

   // Ouvrir une onnexion au serveur mysql en s'identifiant
  $connexion = connecterServeurBD();
  
    // Vérifier que nom et login existent
    $requete="select * from utilisateur where nom ='".$nom."' and mdp ='".$mdp."' ;";
echo $requete;
    $jeuResultat=$connexion->query($requete); // on va chercher tous les membres de la table qu'on trie par ordre croissant
  
    $ligne = $jeuResultat->fetch();

    if($ligne)
    {
        // identification réussie
    }
    else
    {
      $message = "Echec de l'identification !!!";
      ajouterErreur($tabErr, $message);
    }
   
  
  // renvoyer les informations d'identification si réussi
  return $ligne;
}

// Fonction identifier sécurisée 
// SECURITE : 
// A. requete préparée
// B. recherche de mot passe avec fonction de cryptage, car cryptage du mot de passe lors de l'inscription


function identifier($nom, $mdp,&$tabErr)
{
  
  // Initialisation de l'identification a échec
  $ligne = false;

   // Ouvrir une onnexion au serveur mysql en s'identifiant
  $connexion = connecterServeurBD();
  
    // Vérifier que nom et login existent
    $requete="select * from utilisateur where nom = ? and mdp = ? ;";
    
    // Préparation de la requete
    $jeuResultat = $connexion->prepare($requete);
    
    // Lancer la requete préparée avec les valeurs passées en paramètre
    //SECURITE : mot de passe securise, cond cryptage du mot de passe passé en parametre
    $jeuResultat->execute(array($nom, md5($mdp)));  
    
    // Extraire la ligne du jeu de résultats
    $ligne = $jeuResultat->fetch();

    if($ligne)
    {
        // identification réussie
    }
    else
    {
      $message = "Echec de l'identification !!!";
      ajouterErreur($tabErr, $message);
    }
   
  
  // renvoyer les informations d'identification si réussi
  return $ligne;
}





?>
