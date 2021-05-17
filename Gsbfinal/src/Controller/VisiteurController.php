<?php

namespace App\Controller;

use App\Controller ;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request ;
use Symfony\Component\HttpFoundation\Response ;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType ;
use Symfony\Component\Form\Extension\Core\Type\PasswordType ;
use Symfony\Component\Form\Extension\Core\Type\SubmitType ;
use Symfony\Component\Form\Extension\Core\Type\NumberType ;
use Symfony\Component\Form\Extension\Core\Type\IntegerType ;
use Symfony\Component\Form\Extension\Core\Type\ResetType ;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;


class VisiteurController extends AbstractController
{ 
        
    public function index(Request $test)
    {
        
		$request = Request::createFromGlobals() ;
                
				
		$form = $this->createFormBuilder(  )
			->add( 'identifiant' , TextType::class , [ 'label' => 'Identifiant ' ] )
			->add( 'motDePasse' , PasswordType::class , [ 'label' => 'Mot de passe ' ] )
			->add( 'valider' , SubmitType::class )
			->add( 'annuler' , ResetType::class )
			->getForm() ;
			
		$form->handleRequest( $request ) ;
		
		if ( $form->isSubmitted() && $form->isValid() ) {
			$data = $form->getData() ;
			
				array( 'data' => $data ) ;
				$pdo = new \PDO('mysql:host=localhost; dbname=gsbFrais', 'developpeur', 'azerty');
				
				$sql = $pdo->prepare("select * from Visiteur where login = :identifiant and mdp = :motDePasse") ;
				$sql->bindParam(':identifiant', $data['identifiant']);
                                $sql->bindParam(':motDePasse', $data['motDePasse']);
				$sql->execute() ;
				$b1 = $sql->fetch(\PDO::FETCH_ASSOC) ;
				
				if ( $b1['login'] == $data['identifiant'] && $b1['mdp'] == $data['motDePasse'] ) {
                                    
                                    ###
                                    $session = $test->getSession() ;
                                    $session->set('id',$b1['id']) ;
                                    $session->set('prenom',$b1['prenom']) ;
                                    $session->set('nom',$b1['nom']) ;
                                    ###
                                    
                                    $estCo = "Conection valide" ;
                                    
					return $this->redirectToRoute( 'visiteur/menu', 
                                                [ 'data' => $data ,
                                                    'connection' => $estCo ,
                                                    ] ) ;
					}
	
		}	
                
                $estCo = null ;
                if ( $form->getClickedButton() === $form->get('valider') ) {
                    $estCo = "identifiant ou mot de passe invalide" ;
                }                
                 
		return $this->render( 'visiteur/index.html.twig', 
                        [ 'formulaire' => $form->createView() ,
                            'connection' => $estCo ,
                            ] ) ;
		
    }
    
    /*------------------------------------------------------------------------------------------------*/
    
    public function consulter( Request $request )
    {
            
        #Session
        $session = $request->getSession() ;
        $idV = $session->get( 'id' ) ;
        $ficheFrais = $session->get( 'fiche' ) ;
        $prenom = $session->get( 'prenom' ) ;
        $nom = $session->get( 'nom' ) ;
        //$totalff = $session->get( 'totalff' ) ;
        $moisSaisi = $session->get( 'moissaisi' ) ;
        $anneeSaisi = $session->get( 'anneesaisi') ;
        
        #Date
        $today = getdate() ;
        $todayMonth = $today['mon'] ;
        $todayYear = $today['year'] ;
        if( strlen($todayMonth) != 2 ){
            $todayMonth = 0 . $todayMonth ;
        }
        $concatMoisAnnee = sprintf("%02d%04d",$todayMonth,$todayYear) ;
        
        $moisEtannee = $moisSaisi.$anneeSaisi ;
        $b1date = sprintf("%02d %04d",$moisSaisi,$anneeSaisi);
        
        
        //if ( $totalff == null ) { $totalff = 0 ; }
        
        $pdo = new \PDO('mysql:host=localhost; dbname=gsbFrais', 'developpeur', 'azerty');
                  
        #Requête selection des quantités pour la date choisi
        $sqlw = $pdo->prepare("select quantite from LigneFraisForfait where idVisiteur = :id and mois = :moisAnnee");
        $sqlw->bindParam(':id', $idV);
        //$sqlw->bindParam(':moisAnnee', $concatMoisAnnee);
        $sqlw->bindParam(':moisAnnee', $moisEtannee);
        $sqlw->execute();
        $res = $sqlw->fetchAll(\PDO::FETCH_ASSOC);
        
        #si pas de sésultat de sqlw insérer des quantité null / 0
        if ( $res == null or empty($res) == true ) 
        {
            $sql_insert_etp_null = $pdo->prepare("insert into LigneFraisForfait ( idVisiteur , mois , idFraisForfait , quantite ) values ( :idv , :mois , 'ETP' , 0 )");
            $sql_insert_etp_null->bindParam(':idv', $idV);
            $sql_insert_etp_null->bindParam(':mois', $concatMoisAnnee);
            $sql_insert_etp_null->execute();
            
            $sql_insert_km_null = $pdo->prepare("insert into LigneFraisForfait ( idVisiteur , mois , idFraisForfait , quantite ) values ( :idv , :mois , 'KM' , 0 )");
            $sql_insert_km_null->bindParam(':idv', $idV);
            $sql_insert_km_null->bindParam(':mois', $concatMoisAnnee);
            $sql_insert_km_null->execute();
            
            $sql_insert_nui_null = $pdo->prepare("insert into LigneFraisForfait ( idVisiteur , mois , idFraisForfait , quantite ) values ( :idv , :mois , 'NUI' , 0 )");
            $sql_insert_nui_null->bindParam(':idv', $idV);
            $sql_insert_nui_null->bindParam(':mois', $concatMoisAnnee);
            $sql_insert_nui_null->execute();
            
            $sql_insert_rep_null = $pdo->prepare("insert into LigneFraisForfait ( idVisiteur , mois , idFraisForfait , quantite ) values ( :idv , :mois , 'REP' , 0 )");
            $sql_insert_rep_null->bindParam(':idv', $idV);
            $sql_insert_rep_null->bindParam(':mois', $concatMoisAnnee);
            $sql_insert_rep_null->execute();
            
            #réexecuter sqlw
            $sqlw->execute();
            $res = $sqlw->fetchAll(\PDO::FETCH_ASSOC);
        }
        
        #total fhf
        $sqlfhf = $pdo->prepare("select sum(montant) as montant from LigneFraisHorsForfait where idVisiteur = :id and mois = :mois ");
        $sqlfhf->bindParam(':id', $idV);
        $sqlfhf->bindParam(':mois', $moisEtannee);
        $sqlfhf->execute();
        $resfhf = $sqlfhf->fetch(\PDO::FETCH_ASSOC);
        if ( $resfhf['montant'] == null )
        {
            $resfhf['montant'] = 0 ;
        }
        
        #nombre d'éléments hors forfait
        $sqly = $pdo->prepare("select count(*) as compteur from LigneFraisHorsForfait where idVisiteur = :id and mois = :mois");
        $sqly->bindParam(':id', $idV);
        $sqly->bindParam(':mois', $moisEtannee);
        $sqly->execute();
        $res2 = $sqly->fetch(\PDO::FETCH_ASSOC); 
                        
        #total par frais forfait
        $montETP = 110.00 * $res[0]['quantite'];
        $montKM  = 0.62 * $res[1]['quantite'];
        $montNUI = 80.00 * $res[2]['quantite'];
        $montREP = 25.00 * $res[3]['quantite'];
        $aze = [ 
            0 => $montETP ,
            1 => $montKM ,
            2 => $montNUI ,
            3 => $montREP ,
            ];    
        
        #total ff
        $totalff = $montETP + $montKM + $montNUI + $montREP ;
        if ( $totalff == null or $totalff == 0 ) { $totalff = 0 ; }
        
        #form déconnexion
        $form = $this->createFormBuilder(  )
                        ->add( 'SeDéconnecter' , SubmitType::class )
			            ->getForm() ;
        
                $form->handleRequest( $request ) ;
        
                 if ( $form->isSubmitted() && $form->isValid() ) {
                    $session->clear();
                    return $this->redirectToRoute( 'visiteur' , array( 'formulaire' => $form->createView() ) ) ;
                 }
        #fin form déconnexion
                 
        //return $this->render( 'visiteur/consulter.html.twig' , array( 'fiche' => $ficheFrais ) );
        return $this->render( 'visiteur/consulter.html.twig' , [ 
            'fiche' => $ficheFrais ,
            'totfhf' => $resfhf ,
            'idVisiteur' => $idV ,
            'prenomV' => $prenom ,
            'nomV' => $nom ,
            'totalff' => $totalff ,
            'quant' => $res ,
            'res2' => $res2 ,
            'aze' => $aze ,
            'formulaire' => $form->createView() ,
            'b1date' => $b1date ,
            ] );
    }
    
    /*------------------------------------------------------------------------------------------------*/
    
    public function renseigner( Request $test )
    {
        $session = $test->getSession() ;
        $idV = $session->get( 'id' ) ;
        $prenom = $session->get( 'prenom' ) ;
        $nom = $session->get( 'nom' ) ;
        
        $today = getdate() ;
        $todayMonth = $today['mon'] ;
        $todayYear = $today['year'] ;
        
        $auj = date('Y-m-d') ;
        if( strlen($todayMonth) != 2 ){
            $todayMonth = 0 . $todayMonth ;
        }
        $aaa = sprintf("%02d%04d",$todayMonth,$todayYear) ;
        $todaymy = $todayMonth."-".$todayYear ;
        
        //
        $pdo = new \PDO('mysql:host=localhost; dbname=gsbFrais', 'developpeur', 'azerty');
        $sql = $pdo->prepare("select * from FicheFrais where idVisiteur = :id and mois = :mois"); 
        $sql->bindParam(':id', $idV);
        $sql->bindParam(':mois', $aaa);
        $sql->execute();
        $count = $sql->rowCount() ;
        if ( $count == 0 ) { 
            $sql2 = $pdo->prepare("insert into FicheFrais ( idVisiteur , mois ) values ( $idV , $aaa ) where idVisiteur = :id and mois = :mois");
            $sql2->bindParam(':id', $idV);
            $sql2->bindParam(':mois', $aaa);
            $sql2->execute();
            
            $sql3 = $pdo->prepare("select * from LigneFraisForfait where idVisiteur = :id and mois = :mois"); 
            $sql3->bindParam(':id', $idV);
            $sql3->bindParam(':mois', $aaa);
            $sql3->execute();
            $count2 = $sql3->rowCount() ;
            if ( $count2 == 0 ) {
                $sql4 = $pdo->prepare("insert into LigneFraisForfait(idVisiteur , mois , idFraisForfait , quantite) values ($idV , $aaa , 'ETP' , 0 ");
                $sql4->bindParam(':id', $idV);
                $sql4->bindParam(':mois', $aaa);
                $sql4->execute();
                $sql4 = $pdo->prepare("insert into LigneFraisForfait(idVisiteur , mois , idFraisForfait , quantite) values ($idV , $aaa , 'KM' , 0 ");
                $sql4->bindParam(':id', $idV);
                $sql4->bindParam(':mois', $aaa);
                $sql4->execute();                
                $sql4 = $pdo->prepare("insert into LigneFraisForfait(idVisiteur , mois , idFraisForfait , quantite) values ($idV , $aaa , 'NUI' , 0 ");
                $sql4->bindParam(':id', $idV);
                $sql4->bindParam(':mois', $aaa);
                $sql4->execute();                
                $sql4 = $pdo->prepare("insert into LigneFraisForfait(idVisiteur , mois , idFraisForfait , quantite) values ($idV , $aaa , 'REP' , 0 ");
                $sql4->bindParam(':id', $idV);
                $sql4->bindParam(':mois', $aaa);
                $sql4->execute();                
            }
        }
        //
        
        $request = Request::createFromGlobals() ;
        
        $form = $this->createFormBuilder(  )
                        ->add( 'SeDéconnecter' , SubmitType::class )
			->getForm() ;
        
        $form->handleRequest( $request ) ;
        
        if ( $form->isSubmitted() && $form->isValid() ) {
            return $this->redirectToRoute( 'visiteur' , array( 'formulaire' => $form->createView() ) ) ;
        }
        
        return $this->render('visiteur/renseigner.html.twig', [
            'controller_name' => 'VisiteurController',
            'formulaire' => $form->createView() ,
            'idVisiteur' => $idV ,
            'prenomV' => $prenom ,
            'nomV' => $nom ,
            'todaymy' => $todaymy ,
        ]);
    
        /*      $request = Request::createFromGlobals() ;                   
                
		$form = $this->createFormBuilder(  )
			->add( 'ETP' , TextType::class , ['data' => 0] )
                        ->add( 'KM' , TextType::class , ['data' => 0] )
                        ->add( 'NUI' , TextType::class , ['data' => 0] )
                        ->add( 'REP' , TextType::class , ['data' => 0] )
			->add( 'suivant' , SubmitType::class )
			->add( 'modifier' , ResetType::class )
                        ->add( 'valider' , SubmitType::class )
			->getForm() ;
			
                $form2 = $this->createFormBuilder(  )
                        ->add( 'dateEngagement' , TextType::class , ['data' => $auj] )
                        ->add( 'libelle' , TextType::class )
                        ->add( 'montant' , TextType::class )
                        ->add( 'suivant' , SubmitType::class )
			->add( 'modifier' , ResetType::class )
                        ->add( 'valider' , SubmitType::class )
			->getForm() ;
                
		$form->handleRequest( $request ) ;
                $form2->handleRequest( $request ) ;
 
		if ( $form->isSubmitted() && $form->isValid() ) {
                #if ( $form->getClickedButton() === $form->get('suivant') ) {
			$data = $form->getData() ;
                        array( 'data' => $data ) ;
                        
                        $montETP = 110.00*$data['ETP'];
                        $montKM = 0.62*$data['KM'];
                        $montNUI = 80.00*$data['NUI'];
                        $montREP = 25.00*$data['REP'];
                        $montTotal = $montETP + $montKM + $montNUI + $montREP ;
                        $totalF = [ '1' => " nombre d'étapes : ".$data['ETP'] ,
                                    '2' => " nombre de kilometres : ".$data['KM'] ,
                                    '3' => " nombre de nuits : ".$data['NUI'] ,
                                    '4' => " nombre de repas : ".$data['REP'] ,
                                ];
                        
				$pdo = new \PDO('mysql:host=localhost; dbname=gsbFrais', 'developpeur', 'azerty');
                                
                                $sqlb = $pdo->prepare("select * from LigneFraisForfait where idVisiteur = :id and mois = :mois and idFraisForfait = 'ETP'") ;
                                $sqlb->bindParam(':id', $idV);
                                $sqlb->bindParam(':mois', $aaa);
                                $sqlb->execute() ;
				$check1 = $sqlb->fetch(\PDO::FETCH_ASSOC) ;
                                $count1 = $sqlb->rowCount() ;
                                if ( $count1 == 0 ) {
                                    $sql = $pdo->prepare("insert into LigneFraisForfait ( idVisiteur , mois , idFraisForfait , quantite ) values ( :id , :mois , 'ETP' , :quantite )") ;
                                    $sql->bindParam(':id', $idV);
                                    $sql->bindParam(':mois', $aaa);
                                    $sql->bindParam(':quantite', $data['ETP']);
                                }
                                else {
                                    $sql = $pdo->prepare("update LigneFraisForfait set quantite = :quantite where idVisiteur = :id and mois = :mois and idFraisForfait = 'ETP'") ;
                                    $add = $check1['quantite'] + $data['ETP'] ;
                                    $sql->bindParam(':quantite', $add);
                                    $sql->bindParam(':id', $idV);
                                    $sql->bindParam(':mois', $aaa);
                                }
                                
                                $sqlc = $pdo->prepare("select * from LigneFraisForfait where idVisiteur = :id and mois = :mois and idFraisForfait = 'KM'") ;
                                $sqlc->bindParam(':id', $idV);
                                $sqlc->bindParam(':mois', $aaa);
                                $sqlc->execute() ;
				$check2 = $sqlc->fetch(\PDO::FETCH_ASSOC) ;
                                $count2 = $sqlc->rowCount() ;
                                if ( $count2 == 0 ) {
                                    $sql2 = $pdo->prepare("insert into LigneFraisForfait ( idVisiteur , mois , idFraisForfait , quantite ) values ( :id , :mois , 'KM' , :quantite )") ;
                                    $sql2->bindParam(':id', $idV);
                                    $sql2->bindParam(':mois', $aaa);
                                    $sql2->bindParam(':quantite', $data['KM']);
                                }
                                else {
                                    $sql2 = $pdo->prepare("update LigneFraisForfait set quantite = :quantite where idVisiteur = :id and mois = :mois and idFraisForfait = 'KM'") ;
                                    $add2 = $check2['quantite'] + $data['KM'] ;
                                    $sql2->bindParam(':quantite', $add2);
                                    $sql2->bindParam(':id', $idV);
                                    $sql2->bindParam(':mois', $aaa);
                                }
                                
                                $sqld = $pdo->prepare("select * from LigneFraisForfait where idVisiteur = :id and mois = :mois and idFraisForfait = 'NUI'") ;
                                $sqld->bindParam(':id', $idV);
                                $sqld->bindParam(':mois', $aaa);
                                $sqld->execute() ;
				$check3 = $sqld->fetch(\PDO::FETCH_ASSOC) ;
                                $count3 = $sqld->rowCount() ;
                                if ( $count3 == 0 ) {
                                    $sql3 = $pdo->prepare("insert into LigneFraisForfait ( idVisiteur , mois , idFraisForfait , quantite ) values ( :id , :mois , 'NUI' , :quantite )") ;
                                    $sql3->bindParam(':id', $idV);
                                    $sql3->bindParam(':mois', $aaa);
                                    $sql3->bindParam(':quantite', $data['NUI']);
                                }
                                else {
                                    $sql3 = $pdo->prepare("update LigneFraisForfait set quantite = :quantite where idVisiteur = :id and mois = :mois and idFraisForfait = 'NUI'") ;
                                    $add3 = $check3['quantite'] + $data['NUI'] ;
                                    $sql3->bindParam(':quantite', $add3);
                                    $sql3->bindParam(':id', $idV);
                                    $sql3->bindParam(':mois', $aaa);
                                }
                                
                                $sqle = $pdo->prepare("select * from LigneFraisForfait where idVisiteur = :id and mois = :mois and idFraisForfait = 'REP'") ;
                                $sqle->bindParam(':id', $idV);
                                $sqle->bindParam(':mois', $aaa);
                                $sqle->execute() ;
				$check4 = $sqle->fetch(\PDO::FETCH_ASSOC) ;
                                $count4 = $sqle->rowCount() ;
                                if ( $count4 == 0 ) {
                                    $sql4 = $pdo->prepare("insert into LigneFraisForfait ( idVisiteur , mois , idFraisForfait , quantite ) values ( :id , :mois , 'REP' , :quantite )") ;
                                    $sql4->bindParam(':id', $idV);
                                    $sql4->bindParam(':mois', $aaa);
                                    $sql4->bindParam(':quantite', $data['REP']);
                                }
                                else {
                                    $sql4 = $pdo->prepare("update LigneFraisForfait set quantite = :quantite where idVisiteur = :id and mois = :mois and idFraisForfait = 'REP'") ;
                                    $add4 = $check4['quantite'] + $data['REP'] ;
                                    $sql4->bindParam(':quantite', $add4);
                                    $sql4->bindParam(':id', $idV);
                                    $sql4->bindParam(':mois', $aaa);
                                }
                                
                        if ( $form->getClickedButton() === $form->get('valider') ) {              
				$sql->execute() ;
                                $sql2->execute() ;
                                $sql3->execute() ;
                                $sql4->execute() ;
                        }   
                        
                        return $this->render( 'visiteur/renseigner.html.twig', [ 
                                 'formulaire' => $form->createView() ,
                                 'formulaire2' => $form2->createView() ,
                                 'controller_name' => 'VisiteurController',
                                 'idVisiteur' => $idV ,
                                 'data' => $data ,
                                 'prenomV' => $prenom ,
                                 'nomV' => $nom ,
                                 'total' => $montTotal ,
                                 'totalF' => $totalF ,
                                 'todaymy' => $todaymy ,

                        ]);  
                }
                
                                    
               if ( $form2->isSubmitted() && $form2->isValid() ) {   
			$data = $form->getData() ;
                        array( 'data' => $data ) ;
                        
                        $pdo = new \PDO('mysql:host=localhost; dbname=gsbFrais', 'developpeur', 'azerty');
				
				$sql = $pdo->prepare("insert into LigneFraisHorsForfait ( idVisiteur , mois , libelle , date , montant ) values ( :identifiant , :moisAnnee , :libelle , :date , :montant )") ;
				$sql->bindParam(':identifiant', $idV);
                                $sql->bindParam(':moisAnnee', $aaa);
                                $sql->bindParam(':libelle', $data['libelle']);
                                $sql->bindParam(':date', $data['dateEngagement']);
                                $sql->bindParam(':montant', $data['montant']);       
				$sql->execute() ;                             
                                
                                return $this->render( 'visiteur/renseigner.html.twig', [ 
                                 'formulaire' => $form->createView() ,
                                 'formulaire2' => $form2->createView() ,
                                 'controller_name' => 'VisiteurController',
                                 'idVisiteur' => $idV ,
                                 'data' => $data ,
                                 'prenomV' => $prenom ,
                                 'nomV' => $nom ,
                                 'total' => $montTotal , 
                                 'totalF' => $totalF ,
                                 'todaymy' => $todaymy ,   
                        ]);
                }
    

                $totalF = [ '1' => null ,
                            '2' => null ,
                            '3' => null ,
                            '4' => null ,
                                ];
                
                return $this->render( 'visiteur/renseigner.html.twig', [
                        'formulaire2' => $form2->createView() ,
                        'formulaire' => $form->createView() ,
                        'idVisiteur' => $idV ,
                        'prenomV' => $prenom ,
                        'nomV' => $nom ,
                        'total' => $montTotal ,
                        'totalF' => $totalF ,
                        'todaymy' => $todaymy ,
                        ]); 
   
        */
    }
                
    /*------------------------------------------------------------------------------------------------*/
    
    public function renseignerff( Request $test )
    {
        
        #Session
        $session = $test->getSession() ;
        $idV = $session->get( 'id' ) ;
        $prenom = $session->get( 'prenom' ) ;
        $nom = $session->get( 'nom' ) ;
        
        #Date
        $today = getdate() ;
        $todayMonth = $today['mon'] ;
        $todayYear = $today['year'] ;
        $todayDay = $today['mday'] ;
        
        $todaymyj = $todayMonth."-".$todayYear."-".$todayDay ;
        $auj = date('Y-m-d') ;
        if( strlen($todayMonth) != 2 ){
            $todayMonth = 0 . $todayMonth ;
        }
        $aaa = sprintf("%02d%04d",$todayMonth,$todayYear) ;
        $todaymy = $todayMonth."-".$todayYear ;
            
        $montTotal = 0 ;
        
        
        $request = Request::createFromGlobals() ;                   
                
		$form = $this->createFormBuilder(  )
			->add( 'ETP' , TextType::class , ['data' => 0 , 'label' => 'Forfait étape '] )
                        ->add( 'KM' , TextType::class , ['data' => 0 , 'label' => 'Frais kilométrique '] )
                        ->add( 'NUI' , TextType::class , ['data' => 0 , 'label' => 'Nuitée hotel '] )
                        ->add( 'REP' , TextType::class , ['data' => 0 , 'label' => 'Repas restaurant '] )
			//->add( 'suivant' , SubmitType::class )
			->add( 'annuler' , SubmitType::class )
                        ->add( 'valider' , SubmitType::class )
			->getForm() ;
                
		$form->handleRequest( $request ) ;
 
		//if ( $form->isSubmitted() && $form->isValid() ) {
                #if ( $form->getClickedButton() === $form->get('suivant') ) {
			$data = $form->getData() ;
                        array( 'data' => $data ) ;
                        
                     /* $montETP = 110.00*$data['ETP'];
                        $montKM = 0.62*$data['KM'];
                        $montNUI = 80.00*$data['NUI'];
                        $montREP = 25.00*$data['REP'];
                        $montTotal = $montETP + $montKM + $montNUI + $montREP ;*/
                        /*$totalF = [ '1' => " nombre d'étapes : ".$data['ETP'] ,
                                    '2' => " nombre de kilometres : ".$data['KM'] ,
                                    '3' => " nombre de nuits : ".$data['NUI'] ,
                                    '4' => " nombre de repas : ".$data['REP'] ,
                                ];*/
                        
			$pdo = new \PDO('mysql:host=localhost; dbname=gsbFrais', 'developpeur', 'azerty');
                                
                        $sqlw = $pdo->prepare("select quantite from LigneFraisForfait where idVisiteur = :id and mois = :mois");
                        $sqlw->bindParam(':id', $idV);
                        $sqlw->bindParam(':mois', $aaa);
                        $sqlw->execute();
                        $res = $sqlw->fetchAll(\PDO::FETCH_ASSOC);
                        //var_dump($res);
                        
                        if ( $res == null or empty($res) == true ) 
                        {
                            $sql_ins_ff = $pdo->prepare("insert into FicheFrais ( idVisiteur , mois , nbJustificatifs , montantValide , dateModif , idEtat ) values ( :idv , :mois , null , 0 , :dateModif , 'CR' ) ");
                            $sql_ins_ff->bindParam(':idv', $idV);
                            $sql_ins_ff->bindParam(':mois', $aaa);
                            $sql_ins_ff->bindParam(':dateModif', $auj);
                            $sql_ins_ff->execute();
                                    
                            $sql_insert_etp_null = $pdo->prepare("insert into LigneFraisForfait ( idVisiteur , mois , idFraisForfait , quantite ) values ( :idv , :mois , 'ETP' , 0 )");
                            $sql_insert_etp_null->bindParam(':idv', $idV);
                            $sql_insert_etp_null->bindParam(':mois', $aaa);
                            $sql_insert_etp_null->execute();
            
                            $sql_insert_km_null = $pdo->prepare("insert into LigneFraisForfait ( idVisiteur , mois , idFraisForfait , quantite ) values ( :idv , :mois , 'KM' , 0 )");
                            $sql_insert_km_null->bindParam(':idv', $idV);
                            $sql_insert_km_null->bindParam(':mois', $aaa);
                            $sql_insert_km_null->execute();
            
                            $sql_insert_nui_null = $pdo->prepare("insert into LigneFraisForfait ( idVisiteur , mois , idFraisForfait , quantite ) values ( :idv , :mois , 'NUI' , 0 )");
                            $sql_insert_nui_null->bindParam(':idv', $idV);
                            $sql_insert_nui_null->bindParam(':mois', $aaa);
                            $sql_insert_nui_null->execute();
            
                            $sql_insert_rep_null = $pdo->prepare("insert into LigneFraisForfait ( idVisiteur , mois , idFraisForfait , quantite ) values ( :idv , :mois , 'REP' , 0 )");
                            $sql_insert_rep_null->bindParam(':idv', $idV);
                            $sql_insert_rep_null->bindParam(':mois', $aaa);
                            $sql_insert_rep_null->execute();
            
                            //réexecuter sqlw
                            $sqlw->execute();
                            $res = $sqlw->fetchAll(\PDO::FETCH_ASSOC);
                        }                        
                        
                        $totalF = [ '1' => " nombre d'étapes : ".$res[0]['quantite'] ,
                                    '2' => " nombre de kilometres : ".$res[1]['quantite'] ,
                                    '3' => " nombre de nuits : ".$res[2]['quantite'] ,
                                    '4' => " nombre de repas : ".$res[3]['quantite'] , ];
                        $montETP = 110.00*$res[0]['quantite'];
                        $montKM = 0.62*$res[1]['quantite'];
                        $montNUI = 80.00*$res[2]['quantite'];
                        $montREP = 25.00*$res[3]['quantite'];
                        $montTotal = $montETP + $montKM + $montNUI + $montREP ;
                      /*$quant = ['1' => $res[0]['quantite'],
                                  '2' => $res[1]['quantite'],
                                  '3' => $res[2]['quantite'],
                                  '4' => $res[3]['quantite'], ];*/
                        #
                        $session->set('totalff',$montTotal) ;                       
                        #
                                
                                $sqlb = $pdo->prepare("select * from LigneFraisForfait where idVisiteur = :id and mois = :mois and idFraisForfait = 'ETP'") ;
                                $sqlb->bindParam(':id', $idV);
                                $sqlb->bindParam(':mois', $aaa);
                                $sqlb->execute() ;
				$check1 = $sqlb->fetch(\PDO::FETCH_ASSOC) ;
                                $count1 = $sqlb->rowCount() ;
                                if ( $count1 == 0 ) {
                                    $sql = $pdo->prepare("insert into LigneFraisForfait ( idVisiteur , mois , idFraisForfait , quantite ) values ( :id , :mois , 'ETP' , :quantite )") ;
                                    $sql->bindParam(':id', $idV);
                                    $sql->bindParam(':mois', $aaa);
                                    $sql->bindParam(':quantite', $data['ETP']);
                                }
                                else {
                                    $sql = $pdo->prepare("update LigneFraisForfait set quantite = :quantite where idVisiteur = :id and mois = :mois and idFraisForfait = 'ETP'") ;
                                    $add = $check1['quantite'] + $data['ETP'] ;
                                    $sql->bindParam(':quantite', $add);
                                    $sql->bindParam(':id', $idV);
                                    $sql->bindParam(':mois', $aaa);
                                }
                                
                                $sqlc = $pdo->prepare("select * from LigneFraisForfait where idVisiteur = :id and mois = :mois and idFraisForfait = 'KM'") ;
                                $sqlc->bindParam(':id', $idV);
                                $sqlc->bindParam(':mois', $aaa);
                                $sqlc->execute() ;
				$check2 = $sqlc->fetch(\PDO::FETCH_ASSOC) ;
                                $count2 = $sqlc->rowCount() ;
                                if ( $count2 == 0 ) {
                                    $sql2 = $pdo->prepare("insert into LigneFraisForfait ( idVisiteur , mois , idFraisForfait , quantite ) values ( :id , :mois , 'KM' , :quantite )") ;
                                    $sql2->bindParam(':id', $idV);
                                    $sql2->bindParam(':mois', $aaa);
                                    $sql2->bindParam(':quantite', $data['KM']);
                                }
                                else {
                                    $sql2 = $pdo->prepare("update LigneFraisForfait set quantite = :quantite where idVisiteur = :id and mois = :mois and idFraisForfait = 'KM'") ;
                                    $add2 = $check2['quantite'] + $data['KM'] ;
                                    $sql2->bindParam(':quantite', $add2);
                                    $sql2->bindParam(':id', $idV);
                                    $sql2->bindParam(':mois', $aaa);
                                }
                                
                                $sqld = $pdo->prepare("select * from LigneFraisForfait where idVisiteur = :id and mois = :mois and idFraisForfait = 'NUI'") ;
                                $sqld->bindParam(':id', $idV);
                                $sqld->bindParam(':mois', $aaa);
                                $sqld->execute() ;
				$check3 = $sqld->fetch(\PDO::FETCH_ASSOC) ;
                                $count3 = $sqld->rowCount() ;
                                if ( $count3 == 0 ) {
                                    $sql3 = $pdo->prepare("insert into LigneFraisForfait ( idVisiteur , mois , idFraisForfait , quantite ) values ( :id , :mois , 'NUI' , :quantite )") ;
                                    $sql3->bindParam(':id', $idV);
                                    $sql3->bindParam(':mois', $aaa);
                                    $sql3->bindParam(':quantite', $data['NUI']);
                                }
                                else {
                                    $sql3 = $pdo->prepare("update LigneFraisForfait set quantite = :quantite where idVisiteur = :id and mois = :mois and idFraisForfait = 'NUI'") ;
                                    $add3 = $check3['quantite'] + $data['NUI'] ;
                                    $sql3->bindParam(':quantite', $add3);
                                    $sql3->bindParam(':id', $idV);
                                    $sql3->bindParam(':mois', $aaa);
                                }
                                
                                $sqle = $pdo->prepare("select * from LigneFraisForfait where idVisiteur = :id and mois = :mois and idFraisForfait = 'REP'") ;
                                $sqle->bindParam(':id', $idV);
                                $sqle->bindParam(':mois', $aaa);
                                $sqle->execute() ;
				$check4 = $sqle->fetch(\PDO::FETCH_ASSOC) ;
                                $count4 = $sqle->rowCount() ;
                                if ( $count4 == 0 ) {
                                    $sql4 = $pdo->prepare("insert into LigneFraisForfait ( idVisiteur , mois , idFraisForfait , quantite ) values ( :id , :mois , 'REP' , :quantite )") ;
                                    $sql4->bindParam(':id', $idV);
                                    $sql4->bindParam(':mois', $aaa);
                                    $sql4->bindParam(':quantite', $data['REP']);
                                }
                                else {
                                    $sql4 = $pdo->prepare("update LigneFraisForfait set quantite = :quantite where idVisiteur = :id and mois = :mois and idFraisForfait = 'REP'") ;
                                    $add4 = $check4['quantite'] + $data['REP'] ;
                                    $sql4->bindParam(':quantite', $add4);
                                    $sql4->bindParam(':id', $idV);
                                    $sql4->bindParam(':mois', $aaa);
                                }
                        
                        if ( $form->getClickedButton() === $form->get('annuler') ) {
                            $totalF = [ 
                            '1' => " nombre d'étapes : 0" ,
                            '2' => " nombre de kilometres : 0" ,
                            '3' => " nombre de nuits : 0" ,
                            '4' => " nombre de repas : 0" ,
                                      ];
                                    return $this->redirectToRoute( 'visiteur/renseigner/ff',  [
                                         'formulaire' => $form->createView() ,
                                         'idVisiteur' => $idV ,
                                         'prenomV' => $prenom ,
                                         'nomV' => $nom ,
                                         'total' => $montTotal ,
                                         'totalF' => $totalF ,
                                         'todaymy' => $todaymy ,
                                    ]); 
                        }           
                                
                        if ( $form->getClickedButton() === $form->get('valider') ) {              
				$sql->execute() ;
                                $sql2->execute() ;
                                $sql3->execute() ;
                                $sql4->execute() ;
                                //update dernière date de modif de la fiche
                                $sqlddm = $pdo->prepare("update FicheFrais set dateModif = :dateM where idVisiteur = :id and mois = :mois");
                                $sqlddm->bindParam(':dateM', $auj);
                                $sqlddm->bindParam(':id', $idV);
                                $sqlddm->bindParam(':mois', $aaa); 
                                $sqlddm->execute();
                                //
                                   return $this->redirectToRoute( 'visiteur/renseigner/ff',  [
                                         'formulaire' => $form->createView() ,
                                         'idVisiteur' => $idV ,
                                         'prenomV' => $prenom ,
                                         'nomV' => $nom ,
                                         'total' => $montTotal ,
                                         'totalF' => $totalF ,
                                         'todaymy' => $todaymy ,
                                    ]);                             
                                //
                                
                        }   
                        
                        return $this->render( 'visiteur/renseignerff.html.twig', [ 
                                 'formulaire' => $form->createView() ,
                                 'controller_name' => 'VisiteurController',
                                 'idVisiteur' => $idV ,
                                 'data' => $data ,
                                 'prenomV' => $prenom ,
                                 'nomV' => $nom ,
                                 'total' => $montTotal ,
                                 'totalF' => $totalF ,
                                 'todaymy' => $todaymy ,

                        ]);  
                //}
 
                $totalF = [ '1' => " nombre d'étapes : 0" ,
                            '2' => " nombre de kilometres : 0" ,
                            '3' => " nombre de nuits : 0" ,
                            '4' => " nombre de repas : 0" ,
                                ];
                
                return $this->render( 'visiteur/renseignerff.html.twig', [
                        'formulaire' => $form->createView() ,
                        'idVisiteur' => $idV ,
                        'prenomV' => $prenom ,
                        'nomV' => $nom ,
                        'total' => $montTotal ,
                        'totalF' => $totalF ,
                        'todaymy' => $todaymy ,
                        ]); 
    }
    
    /*------------------------------------------------------------------------------------------------*/
    
    public function renseignerfhf( Request $test )
    {
        
        #Session
        $session = $test->getSession() ;
        $idV = $session->get( 'id' ) ;
        $prenom = $session->get( 'prenom' ) ;
        $nom = $session->get( 'nom' ) ;
        
        #Date
        $today = getdate() ;
        $todayMonth = $today['mon'] ;
        $todayYear = $today['year'] ;
        $todayDay = $today['mday'] ;
    
        $auj = date('Y-m-d') ;
        if( strlen($todayMonth) != 2 ){
            $todayMonth = 0 . $todayMonth ;
        }
        $aaa = sprintf("%02d%04d",$todayMonth,$todayYear) ;  
        $todaymy = $todayMonth."-".$todayYear ;
        
        $messErrorDate = "";
        $messErrorMontant = "";
        $messErrorDate2 = "";
        
        $request = Request::createFromGlobals() ;                   
                
		$form = $this->createFormBuilder(  )
                        ->add( 'dateEngagement' , TextType::class , [ 'label' => 'Date d\'engagement ' , 'data' => $auj /*, 'help' => 'yyyy-mm-jj'*/] )
                        ->add( 'libelle' , TextType::class , ['label' => 'Libelle '])
                        ->add( 'montant' , TextType::class , ['label' => 'Montant '])
                        //->add( 'montant' , NumberType::class )
			->add( 'annuler' , SubmitType::class , ['label' => 'Annuler'] )
                        ->add( 'valider' , SubmitType::class , ['label' => 'Valider'])
			->getForm() ;
                
                
		$form->handleRequest( $request ) ;
                
                #instant unless afficher après un button submit
                //if ( $form->isSubmitted() && $form->isValid() ) {   
			$data = $form->getData() ;
                        array( 'data' => $data ) ;
                        
                        $pdo = new \PDO('mysql:host=localhost; dbname=gsbFrais', 'developpeur', 'azerty');
				
				$sql = $pdo->prepare("insert into LigneFraisHorsForfait ( idVisiteur , mois , libelle , date , montant ) values ( :identifiant , :moisAnnee , :libelle , :date , :montant )") ;
				$sql->bindParam(':identifiant', $idV);
                                $sql->bindParam(':moisAnnee', $aaa);
                                $sql->bindParam(':libelle', $data['libelle']);
                                $sql->bindParam(':date', $data['dateEngagement']);
                                $sql->bindParam(':montant', $data['montant']);
                                
                                $sql2 = $pdo->prepare("select * from LigneFraisHorsForfait where idVisiteur = :id and mois = :mois") ;
                                $sql2->bindParam(':id', $idV);
                                $sql2->bindParam(':mois', $aaa);
                                $sql2->execute() ;
				$tab = $sql2->fetchAll(\PDO::FETCH_ASSOC) ;
                                
                                //$sql3 = "select * from LigneFraisHorsForfait where idVisiteur = :id";
                                
                                $res = $pdo->query("select count(*) from LigneFraisHorsForfait where idVisiteur = '$idV' and mois = '$aaa'");
                                $nbLigneRes = $res->fetchColumn();
                                $nbLigneRes = $nbLigneRes - 1 ;
                                
                                if ( $form->getClickedButton() === $form->get('annuler') ) {              
                                    return $this->redirectToRoute( 'visiteur/renseigner/fhf', [
                                         'formulaire' => $form->createView() ,
                                         'idVisiteur' => $idV ,
                                         'prenomV' => $prenom ,
                                         'nomV' => $nom ,
                                         'todaymy' => $todaymy ,
                                   ]);
                                }   
                                
                                /////////////
                                if ( $form->getClickedButton() === $form->get('valider') ) {

                                    //si ce n'est pas une date valide -> erreur
                                    
                                    //$dateSelec = date_create($data['dateEngagement']); 
                                    $d = \DateTime::createFromFormat('Y-m-d', $data['dateEngagement']);
                                    if ( $d != true )
                                    { 
                                        $messErrorDate = "La date d'engagement doit être valide"; 
                                        
                                        return $this->render( 'visiteur/renseignerfhf.html.twig', [ 
                                            'formulaire' => $form->createView() ,  
                                            'controller_name' => 'VisiteurController',
                                            'idVisiteur' => $idV ,
                                            'data' => $data ,
                                            'prenomV' => $prenom ,
                                            'nomV' => $nom ,
                                            'todaymy' => $todaymy ,
                                            'tab' => $tab ,
                                            'nbLigne' => $nbLigneRes , 
                                            'errorDate1' => $messErrorDate ,
                                            'errorDate2' => $messErrorDate2 ,
                                            'errorMontant' => $messErrorMontant ,                                    
                                        ]); 
                                        
                                    }
                                    
                                    //si c'est plus d'un an -> erreur
                                    /*
                                    $dateUnAnAvant = $todayYear - 1 . $todayMonth . $todayDay ;
                                    $dateUnAnAvant_c = date_create($dateUnAnAvant);  
                                    //$auj2 = date_sub($auj, date_interval_create_from_date_string('1 year'));
                                    //$auj2 = date_sub($auj2, date_interval_create_from_date_string('1 day'));
                                    $intervalDate = date_diff($auj, $dateUnAnAvant_c);
                                    var_dump($intervalDate);
                                    if ( $auj )
                                    {}                                                                        
                                    */
                                    
                                    //check si le montant est numérique
                                    if ( is_numeric($data['montant']) != true )
                                    { 
                                        $messErrorMontant = "Erreur le montant n'est pas numérique";
                                        
                                        return $this->render( 'visiteur/renseignerfhf.html.twig', [ 
                                            'formulaire' => $form->createView() ,  
                                            'controller_name' => 'VisiteurController',
                                            'idVisiteur' => $idV ,
                                            'data' => $data ,
                                            'prenomV' => $prenom ,
                                            'nomV' => $nom ,
                                            'todaymy' => $todaymy ,
                                            'tab' => $tab ,
                                            'nbLigne' => $nbLigneRes , 
                                            'errorDate1' => $messErrorDate ,
                                            'errorDate2' => $messErrorDate2 ,
                                            'errorMontant' => $messErrorMontant ,                                    
                                        ]);                                        
                                        
                                    }
                                    
                                    //
                                    //update dernière date de modif de la fiche
                                    $sqlddm = $pdo->prepare("update FicheFrais set dateModif = :dateM where idVisiteur = :id and mois = :mois");
                                    $sqlddm->bindParam(':dateM', $auj);
                                    $sqlddm->bindParam(':id', $idV);
                                    $sqlddm->bindParam(':mois', $aaa); 
                                    $sqlddm->execute();                                    
                                    //
                                    
                                    $sql->execute() ;
                                    return $this->redirectToRoute( 'visiteur/renseigner/fhf', [
                                         'formulaire' => $form->createView() ,
                                         'idVisiteur' => $idV ,
                                         'prenomV' => $prenom ,
                                         'nomV' => $nom ,
                                         'todaymy' => $todaymy , 
                                         'errorDate1' => $messErrorDate ,
                                         'errorDate2' => $messErrorDate2 ,
                                         'errorMontant' => $messErrorMontant ,
                                        ]);
                                }     
                                /////////////
                                
                                return $this->render( 'visiteur/renseignerfhf.html.twig', [ 
                                 'formulaire' => $form->createView() ,  
                                 'controller_name' => 'VisiteurController',
                                 'idVisiteur' => $idV ,
                                 'data' => $data ,
                                 'prenomV' => $prenom ,
                                 'nomV' => $nom ,
                                 'todaymy' => $todaymy ,
                                 'tab' => $tab ,
                                 'nbLigne' => $nbLigneRes , 
                                 'errorDate1' => $messErrorDate ,
                                 'errorDate2' => $messErrorDate2 ,
                                 'errorMontant' => $messErrorMontant ,                                    
                                ]);
              //  }
    
                /*if ( $form2->isSubmitted() && $form2->isValid() ) { 
                    if ( $form2->getClickedButton() === $form2->get('Supprimer') ) { 
                        return $this->redirectToRoute( 'visiteur/renseigner/fhf/confirmation', []);
                    }
                }*/
                
                /*
                $data = 
                [
                 [  'dateEngagement' => null ,
                    'libelle' => null ,
                    'montant' => null ,  ] ,
                 [  'dateEngagement' => null ,
                    'libelle' => null ,
                    'montant' => null ,  ] ,
                 [  'dateEngagement' => null ,
                    'libelle' => null ,
                    'montant' => null ,  ] ,  
                ] ;
                
                $tab = [
                    [
                    'id' => 0 ,    
                    'montant' => null ,
                    'date' => null ,
                    'libelle' => null ,
                    ] ,
                    [
                    'id' => 0 ,    
                    'montant' => null ,
                    'date' => null ,
                    'libelle' => null ,
                    ]
                    ] ;
                $nbLigneRes = 0 ;
                
                return $this->render( 'visiteur/renseignerfhf.html.twig', [
                        'formulaire' => $form->createView() ,
                        //'formulaire2' => $form2->createView() ,
                        'idVisiteur' => $idV ,
                        'prenomV' => $prenom ,
                        'nomV' => $nom ,
                        'todaymy' => $todaymy ,
                        'data' => $data ,
                        'tab' => $tab ,
                        'nbLigne' => $nbLigneRes ,
                        ]); 
                 
                 */
    }
 
    /*------------------------------------------------------------------------------------------------*/
    
    public function confirmation( $idff , Request $test )
    {
        $session = $test->getSession() ;
        $idV = $session->get( 'id' ) ;
        $prenom = $session->get( 'prenom' ) ;
        $nom = $session->get( 'nom' ) ;
        
       /* $request = Request::createFromGlobals() ;
        
        $form = $this->createFormBuilder(  )
			->add( 'annuler' , SubmitType::class )
                        ->add( 'confirmer' , SubmitType::class )
			->getForm() ;
        
        if ( $form->isSubmitted() && $form->isValid() ) { 
                $pdo = new \PDO('mysql:host=localhost; dbname=gsbFrais', 'developpeur', 'azerty');
                $req = $pdo->prepare("delete from LigneFraisHorsForfait where idVisiteur = 'a17'") ;
                $req->bindParam(':id', $idV);
                if ( $form->getClickedButton() === $form->get('confirmer') ) {
                    $req->execute();
                    return $this->redirectToRoute( 'visiteur/renseigner/fhf', []);
                } 
                    return $this->redirectToRoute( 'visiteur/renseigner/fhf', []);
        }*/
        
        $pdo = new \PDO('mysql:host=localhost; dbname=gsbFrais', 'developpeur', 'azerty');
        $req = $pdo->prepare("delete from LigneFraisHorsForfait where id = :id") ;
        $req->bindParam(':id', $idff);
        $req->execute() ;
        
        /*return $this->render( 'visiteur/confirmation.html.twig', [
                        'idVisiteur' => $idV ,
                        'prenomV' => $prenom ,
                        'nomV' => $nom ,
                        'formulaire' => $form->createView() ,
        ]);*/
        
        /*return $this->render( 'visiteur/confirmation.html.twig', [
                    'idff' => $idff ,
                    'prenomV' => $prenom ,
                    'nomV' => $nom ,
                    'idVisiteur' => $idV ,
        ] ) ;*/
        return $this->redirectToRoute('visiteur/renseigner/fhf' , [
                    'idff' => $idff ,
                    'prenomV' => $prenom ,
                    'nomV' => $nom ,
                    'idVisiteur' => $idV ,
        ]);
    }
    
    /*------------------------------------------------------------------------------------------------*/
    
    public function menu( Request $test )
    {
 
        $session = $test->getSession() ;
        $idV = $session->get( 'id' ) ;
        $prenom = $session->get( 'prenom' ) ;
        $nom = $session->get( 'nom' ) ;

        
        $request = Request::createFromGlobals() ;
        
        $form = $this->createFormBuilder(  )
                        ->add( 'SeDéconnecter' , SubmitType::class )
			->getForm() ;
        
        $form->handleRequest( $request ) ;
        
        //cloture des fiches de frais 
        $pdo = new \PDO('mysql:host=localhost; dbname=gsbFrais', 'developpeur', 'azerty');
        $req = $pdo->prepare("select * from FicheFrais where idEtat = 'CR' ") ;
        $req->execute() ;
	    $tab = $req->fetchAll(\PDO::FETCH_ASSOC) ;       
        //print_r($tab);
        //var_dump($tab);
        
        $today = getdate() ;
        $todayMonth = $today['mon'] ;
        $todayYear = $today['year'] ;
        //$todayDay = $today['mday'] ;
        
        if( strlen($todayMonth) != 2 ){
            $todayMonth = 0 . $todayMonth ;
        }
        
        $todayYM00 = $todayYear."-".$todayMonth."-00" ;
        
        foreach ($tab as $value) 
        {
            $leMois[0] = str_split($value['mois'], 2);
            //var_dump($leMois[0]);
            
            if( intval($todayMonth) > intval($leMois[0]) )
            {
                $req2 = $pdo->prepare( " update FicheFrais set idEtat = 'CL' where idEtat = 'CR' and dateModif < '$todayYM00' " );
                $req2->execute();
            }    
            
        }
        //fin cloture
        
        if ( $form->isSubmitted() && $form->isValid() ) {
            return $this->redirectToRoute( 'visiteur' , array( 'formulaire' => $form->createView() ) ) ;
        }
        
        return $this->render('visiteur/menu.html.twig', [
            'controller_name' => 'VisiteurController',
            'formulaire' => $form->createView() ,
            'idVisiteur' => $idV ,
            'prenomV' => $prenom ,
            'nomV' => $nom ,
        ]);
    }
    
    /*------------------------------------------------------------------------------------------------*/
    
    public function saisirMois( Request $test )
    {
                
                #Session
                $session = $test->getSession() ;
                $idV = $session->get( 'id' ) ;
                $prenom = $session->get( 'prenom' ) ;
                $nom = $session->get( 'nom' ) ;
                
                #Message d'erreur
                $messErreur = '' ;
                
                #Date
                $today = getdate() ;
                $todayYear = $today['year'] ;
                $todayMonth = $today['mon'] ;
                if( strlen($todayMonth) != 2 ){
                     $todayMonth = 0 . $todayMonth ;
                }
                
                #Calcul des années pour la liste déroulante
                $todayYear2 = $todayYear - 1 ;
                $todayYear3 = $todayYear - 2 ;
                
                #Début formulaire
		$request = Request::createFromGlobals() ;
                
                $builder = $this->createFormBuilder(  )
                  ->add('mois', ChoiceType::class, [
                    'choices'  => [
                    $todayMonth => $todayMonth,
                    '01' => '01',    
                    '02' => '02',
                    '03' => '03',
                    '04' => '04',
                    '05' => '05',
                    '06' => '06',
                    '07' => '07',
                    '08' => '08',
                    '09' => '09',
                    '10' => '10',
                    '11' => '11', 
                    '12' => '12',    
                      ] 
                      , 'label' => 'Mois '
                      ,
                      ])
                  ->add('annee', ChoiceType::class, [
                    'choices'  => [
                    $todayYear => $todayYear,
                    $todayYear2 => $todayYear2,
                    $todayYear3 => $todayYear3,
                      ] 
                      , 'label' => 'Année '                      
                      ,
                      ])      
                  ->add( 'valider' , SubmitType::class )
		          ->add( 'annuler' , ResetType::class )
		          ->getForm() ;    
                    
                $builder->handleRequest( $request ) ;
                
                #Traitement formulaire
                if ( $builder->isSubmitted() && $builder->isValid() ) {
			$data = $builder->getData() ;
                        
                        ###
                        #$bbb = $session->get( 'id' ) ;
                        ###
                        
                        $messErreur = " Aucune fiche pour cette date ";
                        $moisAnnee = sprintf("%02d%04d",$data['mois'],$data['annee']) ;
                        
                        $pdo = new \PDO('mysql:host=localhost; dbname=gsbFrais', 'developpeur', 'azerty');
                        
                        #$sql = $pdo->prepare("select * from FicheFrais where mois = :mois and idVisiteur = :idVisiteur") ;
                        $sql = $pdo->prepare( 
                           'select e.id, e.libelle, f.mois, f.dateModif, l.quantite,
                            LigneFraisHorsForfait.montant, LigneFraisHorsForfait.libelle, LigneFraisHorsForfait.date
                            from FicheFrais as f inner join Etat as e
                            on f.idEtat = e.id  
                            inner join LigneFraisForfait as l 
                            on f.idVisiteur = l.idVisiteur
                            inner join LigneFraisHorsForfait 
                            on f.idVisiteur = LigneFraisHorsForfait.idVisiteur
                            where f.mois = :mois and f.idVisiteur = :idVisiteur and l.mois = :mois ;');
                            //where f.mois = :mois and f.idVisiteur = :idVisiteur and LigneFraisHorsForfait.mois = :mois ;');
                        
                        $sql->bindParam(':mois', $moisAnnee);
                        $sql->bindParam(':idVisiteur', $idV);
                        $sql->execute() ;
                        $b1 = $sql->fetch(\PDO::FETCH_ASSOC) ;                           
                        
                        #Set 
                        $session->set('fiche',$b1) ;
                        $session->set('moissaisi' ,$data['mois']) ;
                        $session->set('anneesaisi' ,$data['annee']) ;
                        
                        if ( $b1['mois'] == $moisAnnee ) {
                        #return $this->redirectToRoute( 'visiteur/consulter', array( 'data' => $data ) ) ;
                         return $this->redirectToRoute( 'visiteur/consulter' , [
                                 'date' => $data ,
                                 'controller_name' => 'VisiteurController',
                                 'idVisiteur' => $moisAnnee ,
                                 'prenomV' => $prenom ,
                                 'nomV' => $nom ,
                        ]); 
                        }

                }
                
                $form = $this->createFormBuilder(  )
                        ->add( 'SeDéconnecter' , SubmitType::class, [ 
                            'label' => 'Se déconnecter',
                            'attr' => ['class' => 'button'],
                            ])
			            ->getForm() ;
        
                $form->handleRequest( $request ) ;
        
                 if ( $form->isSubmitted() && $form->isValid() ) {
                     if ( $form->getClickedButton() === $form->get('SeDéconnecter') ) {
                        $session->clear();
                        return $this->redirectToRoute( 'visiteur' , array( 'formulaire2' => $form->createView() ) ) ;
                     }
                 }
                                
		#return $this->render( 'visiteur/saisirMois.html.twig', array( 'formulaire' => $builder->createView() ) ) ; 
                return $this->render( 'visiteur/saisirMois.html.twig', [ 
                                 'formulaire' => $builder->createView() ,
                                 'formulaire2' => $form->createView() ,   
                                 'idVisiteur' => $idV ,
                                 'prenomV' => $prenom ,
                                 'nomV' => $nom ,
                                 'mess' => $messErreur ,   
                    ]) ;
    }
    
    /*------------------------------------------------------------------------------------------------*/
}
