<?php  
/********************************************************************************************
*********************************************************************************************
*******GESTION DE L'AUTHENTIFICATION EN  PHP *****************************
*******AUTEUR : SERGE GUERINET       *****************************
*******V.1 : Cr�� le 14/06/2011      ****************************
*********************************************************************************************
**  Teste l'identit� d'un individu aupr�s d'une base de donn�es (en clair ou en MD5) 	**
**  Teste l'identit� d'une personne aupr�s d'un annuaire LDAP 	                        **
*********************************************************************************************
**                                                                                Version Objet							**
*********************************************************************************************
*********************************************************************************************
** REMARQUE 
 pour utiliser la biblioth�que ldap : 
   d�commenter extension=php_ldap.dll dans le fichier php.ini pr�sent dans le dossier apache
   ou activer ce module dans l'inteface du WAMP/LAMP/MAMP/XAMP
   r�demarrer les services
*/ 
/*------------------------------------------- D�claration de la classe -------------------------------------------------------------------------------*/
class cAuthentification {
/*--------------------------------------------Propri�t�s de la classe  -------------------------------------------------------------------------------*/
// pour la base de donn�es
var $connexion ; 
var $dsn ="" ;
var $mode="" ;  //mode  d'authentification : LDAP ou Base de donn�es
var $nivoSecu ;  //niveau  de s�curit� pour le mot de passe dans la base de donn�es (text ou md5)
var $table ;  //table qui contient les donn�es d'identification
var $champId ; //champs pour tester les valeurs fournies
var $champPasse ; 
// pour l'annuaire LDAP
var $nomServeur;
var $nomDomaine ;
// gestion des erreurs
var $erreur = "";
/*------------------------------------------- Acc�s aux propri�t�s -----------------------------------------------------------------------------------*/
function getConnexion() {return $this->connexion;}
function getErreur() { return $this->erreur;}
/* ------------------------------------------   Fonctionnement avec une base------------------------------------------------------------------------- */
	function connecte($pNomDSN, $pUtil, $pPasse) {
		//tente d'�tablir une connexion � une base de donn�es 
		try {
			$this->dsn = $pNomDSN;
			$this->mode = "bdd";
			$this->connexion = odbc_connect( $pNomDSN , $pUtil, $pPasse ) ;				
			}
		catch(Exception $e) {$this->erreur="Echec Base";header("Location:testId.php");}
	}
	function definitChamps( $pTable, $pId, $pPasse, $pSecu) {
		//affecte les valeurs � la table et aux champs qui contiennent les identifiants
		$this->table = $pTable;
		$this->champId = $pId;
		$this->champPasse = $pPasse;
		$this->nivoSecu = $pSecu;
	}
/* 	------------------------------------------   Fonctionnement avec un annuaire -------------- --------------------------------------------------------- */
	function definitAnnuaire( $pServeur, $pDomaine) {
		$nomServeur = $pServeur ;
		$nomDomaine = $pDomaine ;
		$mode = "ldap" ;
	}
/* ------------------------------------------   V�rification de l'identit� ------------------------------------------------------------------- */
	function verifId( $pId, $pPasse) {
		$retour = false;
		if ($this->erreur=="") { //s'il n'y a pas eu d'erreur de connexion
			if ($this->mode=="bdd" ) {
				$requete = "select count(*) as nbRep from $this->table where $this->champId='$pId' and $this->champPasse=";
				if ($this->nivoSecu=="md5") {$requete .= "md5('$pPasse')";} else {$requete .= "'$pPasse'";}
				$rs = odbc_do($this->connexion,$requete) or die($this->erreur="Echec Requete");
				$reponse = odbc_fetch_array($rs); 			
				if ($reponse["nbRep"] == 1 ) // Retourne vrai s'il y a un r�sultat correspondant
					{$retour = true;}
				else
					{$this->erreur = "Identifiants incorrects";}
				odbc_close($this->connexion  );
			}
			else //on est sur un annuaire
			{	//se connecte � la machine
				$serveur = ldap_connect($this->nomServeur) or die ($this->erreur = "Echec Annuaire");
				//si succ�s
				if ($serveur<>"") {				
					//teste la connexion avec les donn�es de l'utilisateur 
					$utilisateur = "$pId@$this->nomDomaine";					
					$connexion = ldap_bind($serveur, $utilisateur, $pPasse) or die($this->erreur = "Identifiants incorrects" );
					$retour=true ; //si la connexion a r�ussi
				}
				ldap_close($serveur);
			}
		}
		return $retour;
	}
	
}