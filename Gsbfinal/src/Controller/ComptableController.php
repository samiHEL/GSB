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
use Symfony\Component\Form\Extension\Core\Type\ResetType ;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


class ComptableController extends AbstractController
{
   
    public function index(Request $test)
    {
        
        $request = Request::createFromGlobals() ;
				
	$form = $this->createFormBuilder(  )
			->add( 'identifiant' , TextType::class )
			->add( 'motDePasse' , PasswordType::class )
			->add( 'valider' , SubmitType::class )
			->add( 'annuler' , ResetType::class )
			->getForm() ;
			
	$form->handleRequest( $request ) ;
		
	if ( $form->isSubmitted() && $form->isValid() ) 
        {
            $data = $form->getData() ;
            array( 'data' => $data ) ;
                        
            $pdo = new \PDO('mysql:host=localhost; dbname=gsbFrais', 'developpeur', 'azerty');
				
            $sql = $pdo->prepare("select * from Comptable where id = :identifiant and mdp = :mdp") ;
            $sql->bindParam(':identifiant', $data['identifiant']);
            $sql->bindParam(':mdp'        , $data['motDePasse']);
            $sql->execute() ;
            $result = $sql->fetch(\PDO::FETCH_ASSOC) ;				
				
            if (  $result['id'] == $data['identifiant'] && $result['mdp'] == $data['motDePasse'] ) 
            {
                                    
                $session = $test->getSession() ;
                $session->set('idC'      ,$result['id']) ;
                $session->set('prenomC'  ,$result['prenom']) ;
                $session->set('nomC'     ,$result['nom']) ;
 
                return $this->redirectToRoute( 'comptable/menu', array( 'data' => $data ) ) ;
            }
                                
        }
        
        $messageErreur = null ;
        if ( $form->getClickedButton() === $form->get('valider') ) 
        {
            $messageErreur = "identifiant ou mot de passe invalide" ;
        } 
        
        return $this->render( 'comptable/index.html.twig', 
                [ 
                    'formulaire' => $form->createView() ,
                    'messErreur' => $messageErreur ,
                ] ) ;
		
    }
    
    public function valider(Request $test)
    {
		
        $request = Request::createFromGlobals() ;  
        $session = $test->getSession() ;
		
	$builder = $this->createFormBuilder(  )
            ->add('visiteur', ChoiceType::class, [
                  'choices' => [
                  'a131'    => 'a131',
                  'a17'     => 'a17',
                  'e5'      => 'e5',
                  'f4' => 'f4',
                  'f39'=> 'f39',   
                      ] ])     
            ->add('mois', ChoiceType::class, [
                  'choices'  => [
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
                      ] ])
            ->add('annee', ChoiceType::class, [
                  'choices'  => [
                  '2017' => '2017', 
                  '2018' => '2018',
                  '2019' => '2019',
                  '2020' => '2020',
                      ] ])      
                ->add( 'valider' , SubmitType::class )
		->add( 'annuler' , ResetType::class )
		->getForm() ;    
                    
                 $builder->handleRequest( $request ) ;
             
                if ( $builder->isSubmitted() && $builder->isValid() ) {
						
						$data = $builder->getData() ;
                        $aaa = sprintf("%02d%04d",$data['mois'],$data['annee']) ;
                        $pdo = new \PDO('mysql:host=localhost; dbname=gsbFrais', 'developpeur', 'azerty');
                        
                        $sql = $pdo->prepare("select * from FicheFrais where mois = :mois and idVisiteur = :idVisiteur") ;
                        $sql->bindParam(':mois', $aaa);
                        $sql->bindParam(':idVisiteur', $data['visiteur']);
                        $sql->execute() ;
                        $b1 = $sql->fetch(\PDO::FETCH_ASSOC) ; 
                        
                        
                        $sql1 = $pdo->prepare("select * from LigneFraisForfait where mois = :mois and idVisiteur = :idVisiteur") ;
                        $sql1->bindParam(':mois', $aaa);
                        $sql1->bindParam(':idVisiteur', $data['visiteur']);
                        $sql1->execute() ;
                        $b2 = $sql1->fetch(\PDO::FETCH_ASSOC) ; 
                        
                        $sql2 = $pdo->prepare("select * from LigneFraisHorsForfait where mois = :mois and idVisiteur = :idVisiteur") ;
                        $sql2->bindParam(':mois', $aaa);
                        $sql2->bindParam(':idVisiteur', $data['visiteur']);
                        $sql2->execute() ;
                        $b3 = $sql2->fetch(\PDO::FETCH_ASSOC) ; 
                        
                        $session->set('moisA',$aaa);
                        $session->set('fiche',$b1) ;
                        $session->set('fiche2',$b2) ;
                        $session->set('fiche3',$b3) ;
                        $session->set('id1',$b1['idVisiteur']);
                        $session->set('mois1',$b1['mois']);
                        
                        if ( $b1['mois'] == $aaa && $b1['idVisiteur'] == $data['visiteur'] ) {
                        #return $this->redirectToRoute( 'visiteur/consulter', array( 'data' => $data ) ) ;
                            return $this->redirectToRoute( 'comptable/valider1' , [
                                 'date' => $data ,
                                 'controller_name' => 'VisiteurController',      
                        ]); 
                        }
                        
                }
                if ( $builder->isSubmitted() && $builder->isValid()) {
					echo'--Pas de fiche de frais pour ce visiteur ce mois';
				}
        return $this->render('comptable/valider.html.twig', [
            'formulaire' => $builder->createView() 
        ]);
    }
    
    
    public function suivre(Request $test)
    {
	$request = Request::createFromGlobals() ;  
        $session = $test->getSession() ;
        
	$builder = $this->createFormBuilder(  )
                  
                  ->add('visiteur', ChoiceType::class, [
                  'choices'  => [
                  'a131' => 'a131',
                  'a17' => 'a17',
                  'e5' => 'e5',
                  'f4' => 'f4',
                  'f39'=> 'f39',   
                      ] ])     
                  ->add('mois', ChoiceType::class, [
                  'choices'  => [
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
                      ] ])
                  ->add('annee', ChoiceType::class, [
                  'choices'  => [
                  '2017' => '2017', 
                  '2018' => '2018',
                  '2019' => '2019',
                  '2020' => '2020',
                      ] ])      
                ->add( 'valider' , SubmitType::class )
		->add( 'annuler' , ResetType::class )
		->getForm() ;    
                    
                 $builder->handleRequest( $request ) ;
               
                if ( $builder->isSubmitted() && $builder->isValid() ) {
						
			$data = $builder->getData() ;
                        $aaa = sprintf("%02d%04d",$data['mois'],$data['annee']) ;
                        $pdo = new \PDO('mysql:host=localhost; dbname=gsbFrais', 'developpeur', 'azerty');
                        
                        $sql = $pdo->prepare("select * from FicheFrais where mois = :mois and idVisiteur = :idVisiteur") ;
                        $sql->bindParam(':mois', $aaa);
                        $sql->bindParam(':idVisiteur', $data['visiteur']);
                        $sql->execute() ;
                        $b1 = $sql->fetch(\PDO::FETCH_ASSOC) ; 
                        
                        
                        $sql1 = $pdo->prepare("select * from LigneFraisForfait where mois = :mois and idVisiteur = :idVisiteur") ;
                        $sql1->bindParam(':mois', $aaa);
                        $sql1->bindParam(':idVisiteur', $data['visiteur']);
                        $sql1->execute() ;
                        $b2 = $sql1->fetch(\PDO::FETCH_ASSOC) ; 
                        
                        $sql2 = $pdo->prepare("select * from LigneFraisHorsForfait where mois = :mois and idVisiteur = :idVisiteur") ;
                        $sql2->bindParam(':mois', $aaa);
                        $sql2->bindParam(':idVisiteur', $data['visiteur']);
                        $sql2->execute() ;
                        $b3 = $sql2->fetch(\PDO::FETCH_ASSOC) ; 
                        
                        $session->set('moisA',$aaa);
                        $session->set('fiche',$b1) ;
                        $session->set('fiche2',$b2) ;
                        $session->set('fiche3',$b3) ;
                        $session->set('id1',$b1['idVisiteur']);
                        $session->set('mois1',$b1['mois']);
                        
                        if ( $b1['mois'] == $aaa && $b1['idVisiteur'] == $data['visiteur'] ) {
                        
                         return $this->redirectToRoute( 'comptable/suivre1' , [
                                 'date' => $data ,
                                 'controller_name' => 'VisiteurController',      
                        ]); 
                        }
                        
                }
                if ( $builder->isSubmitted() && $builder->isValid()) {
					echo'--Pas de fiche de frais pour ce visiteur ce mois';
				}
        return $this->render('comptable/valider.html.twig', [
            'formulaire' => $builder->createView() 
        ]);
    }
		
    
    public function menu( Request $test )
    {
        
        //Session
        $session = $test->getSession() ;
        $idC = $session->get( 'idC' ) ;
        $prenom = $session->get( 'prenomC' ) ;
        $nom = $session->get( 'nomC' ) ;
        //fin session
        
        if( $session->get('idC') == null )
        {
            return $this->redirectToRoute( 'comptable' ) ;
        }
        
        //form de déconnexion ( bouton retour à se connecter )
        $request = Request::createFromGlobals() ;       
        $form = $this->createFormBuilder(  )
                        ->add( 'SeDéconnecter' , SubmitType::class )
			->getForm() ;
        
        $form->handleRequest( $request ) ;  
        if ( $form->isSubmitted() && $form->isValid() ) 
        {
            //vider la session
                $session->set('idC'      ,null) ;
                $session->set('prenomC'  ,null) ;
                $session->set('nomC'     ,null) ;
            return $this->redirectToRoute( 'comptable' , array( 'formulaire' => $form->createView() ) ) ;
        }
        //fin form de déconnexion
        
        return $this->render('comptable/menu.html.twig', 
            [
                'controller_name' => 'ComptableController',
                'formulaire' => $form->createView() ,
                'idComptable'     => $idC ,
                'nomComptable'    => $nom ,
                'prenomComptable' => $prenom ,
            ]);
    }
    
    public function valider1(Request $request)
    {
        $session = $request->getSession() ;
        
        
        $ficheFrais =  $session->get( 'fiche' ) ; 
        $idvv= $session->get( 'id1' ) ;
        $mois=$session->get('mois1');
        $ficheFrais2 =  $session->get( 'fiche2' ) ; 
        $ficheFrais3 =  $session->get( 'fiche3' ) ; 
        
        $form = $this->createFormBuilder(  )
            ->add( 'Valider la fiche' , SubmitType::class )
			->getForm() ;
        
             $form->handleRequest( $request ) ;
             
             
		$pdo = new \PDO('mysql:host=localhost; dbname=gsbFrais', 'developpeur', 'azerty');
		$sql = $pdo->prepare("select * from FicheFrais where mois = :mois and idVisiteur = :idVisiteur") ;
						$sql->bindParam(':mois', $aaa);
						$sql->bindParam(':idVisiteur', $data['visiteur']);
						$sql->execute() ;
						$b1 = $sql->fetch(\PDO::FETCH_ASSOC) ; 
            
            
        if ( $form->isSubmitted()&& $b1['idEtat']!='Va') {
              $pdo = new \PDO('mysql:host=localhost; dbname=gsbFrais', 'developpeur', 'azerty');
                        
						$sql6 = $pdo->prepare(" UPDATE FicheFrais SET idEtat = 'Va' WHERE idVisiteur = :identifiant and mois= :moisAnnee;");
						$sql6->bindParam(':identifiant',$idvv);
                        $sql6->bindParam(':moisAnnee',$mois);
                        $sql6->execute() ;  
                 }
       
        if ( empty($ficheFrais2)){
            $pdo = new \PDO('mysql:host=localhost; dbname=gsbFrais', 'developpeur', 'azerty');
                        
                        $sql3 = $pdo->prepare("insert into LigneFraisForfait ( idVisiteur , mois, idFraisForfait ) values ( :identifiant, :moisAnnee,'REP' )") ;
                        $sql3->bindParam(':identifiant',$idvv);
                        $sql3->bindParam(':moisAnnee',$mois);
                        $sql3->execute() ;
                          
                        
                        $sql4 = $pdo->prepare("select * from LigneFraisForfait where mois = :mois and idVisiteur = :idVisiteur") ;
                        $sql4->bindParam(':mois', $idvv);
                        $sql4->bindParam(':idVisiteur', $mois);
                        $sql4->execute() ;
                        $b2 = $sql4->fetch(\PDO::FETCH_ASSOC) ; 
                        $ficheFrais2 = $b2 ;
        }
        
        if ( empty($ficheFrais3)){
            $pdo = new \PDO('mysql:host=localhost; dbname=gsbFrais', 'developpeur', 'azerty');
                        
                        $sql1 = $pdo->prepare("insert into LigneFraisHorsForfait ( idVisiteur , mois ) values ( :identifiant , :moisAnnee )") ;
                        $sql1->bindParam(':identifiant',$idvv);
                        $sql1->bindParam(':moisAnnee',$mois);
                        $sql1->execute() ;
                        
                        $sql5 = $pdo->prepare("select * from LigneFraisForfait where mois = :mois and idVisiteur = :idVisiteur") ;
                        $sql5->bindParam(':mois', $idvv);
                        $sql5->bindParam(':idVisiteur', $mois);
                        $sql5->execute() ;
                        $b3 = $sql5->fetch(\PDO::FETCH_ASSOC) ;   
                        $ficheFrais3 = $b3 ;           
        }
        #$session->clear();
        if ($ficheFrais != NULL && $ficheFrais2 != NULL && $ficheFrais3 != NULL  ){
        #return $this->render( 'visiteur/consulter.html.twig' , array( 'fiche' => $ficheFrais ) );
        return $this->render('comptable/valider1.html.twig', [ 'fiche' => $ficheFrais ,'fiche2'=> $ficheFrais2 ,'fiche3'=> $ficheFrais3, 'formulaire' => $form->createView() ,
            
            ] ); 
    }
    return $this->redirectToRoute( 'comptable/valider' , [
                                 'fiche' => $ficheFrais ,'fiche2'=> $ficheFrais2 ,'fiche3'=> $ficheFrais3,
                                 'controller_name' => 'VisiteurController',      
                        ]); 
        }
}


