<?php

namespace App\Controller;

use App\Entity\Fichier;
use App\Form\FichierType;
use Gedmo\Sluggable\Util\Urlizer;
use App\Repository\FichierRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



class FichierController extends AbstractController
{
    /**
     * @Route("/api/fichiers/new", name="fichier_new", methods={"POST"})
     */
    public function new(Request $request, UtilisateurRepository $utilisateurRepo, FichierRepository $fichierRepository, EntityManagerInterface $entityManager): Response
    {
        $token = $request->headers->get('Authorization');
      
        $utilisateur = $utilisateurRepo->findByToken(['Authorization'=>$token]);
        if(empty($utilisateur)){
            $etat = 400;
            $message = 'Vous n\'êtes pas connecte!';
            return $this->json(['status'=>$etat, 'message'=>$message]);
        }
        /** @var UploadedFile $file */
        $file = $request->files->get('lien');
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $newFile = Urlizer::urlize($originalFilename).'-'.uniqid().'.'.$file->guessExtension();
        $destination = $this->getParameter('kernel.project_dir').'/public/files';
       
        $dest = '/files/';
        $file->move($destination, $newFile);
        $fichier = new Fichier();
        $fichier->setNom($request->get('nom'))
                ->setDescription($request->get('description'))
                ->setLien($dest.$newFile)
                ->setUtilisateur($utilisateur[0])
                ->setVisible(Fichier::VISIBLE_ARRAY['ACTIF']);
    
        $status = 201;
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($fichier);
        $entityManager->flush();
       
        return $this->json(['status' => $status,'message' =>'Fichier cree avec success', 'details du fichier'=>$fichier]);
    }

    /**
     * @Route("/api/fichiers/show", name="fichier_show_visible", methods={"GET"})
     */
    public function show_visible(Request $request, UtilisateurRepository $utilisateurRepo, FichierRepository $fichiersRepo): Response
    {
        $token = $request->headers->get('Authorization');
        $status = 200;

        $utilisateur = $utilisateurRepo->findOneBy(['token'=>$token]);
        
        if(empty($utilisateur)){
            $etat = 400;
            $message = 'Vous n\'êtes pas connecte!';
            return $this->json(['status'=>$etat, 'message'=>$message]);
        }
        $fichier = $fichiersRepo->selectFichiersVisible();
        $status = 200;
        for($i = 0; $i<=16; $i++){
            $fichiers[$i] = array('fichier_id'=>$fichier[$i]->getId(),
                                'fichierName'=>$fichier[$i]->getNom(),
                                'fichierDescription'=>$fichier[$i]->getDescription(),
                                'fichierLink'=>$fichier[$i]->getLien(),
                                'fichier_dateCreated'=>$fichier[$i]->getCreatedAt(),
                                'fichier_dateUpdated'=>$fichier[$i]->getUpdatedAt(),
                                'user'=>['userId'=>$fichier[$i]->getUtilisateur()->getId(),
                                        'userEmail'=>$fichier[$i]->getUtilisateur()->getEmail(),
                                        'userFirstName'=>$fichier[$i]->getUtilisateur()->getPrenom(),
                                        'fichierName'=>$fichier[$i]->getUtilisateur()->getNom(),
                                        'userPhone'=>$fichier[$i]->getUtilisateur()->getTelephone()
                                ]);
        }
            
        return $this->json(['status' => $status,'message' =>$fichiers]);
    }

    /**
     * @Route("/api/fichiers/utilisateur/{id}", name="fichier_view_visible", methods={"GET"})
     */
    public function view_visible(Request $request, UtilisateurRepository $utilisateurRepo, FichierRepository $fichiersRepo, $id): Response
    {
        $token = $request->headers->get('Authorization');
      
        $utilisateur = $utilisateurRepo->findOneBy(['token'=>$token]);
        $fichier = $fichiersRepo->FichiersUtilisateur($id);
        $status = 200;
        if(empty($token) || empty($utilisateur)){
            $etat = 400;
            $message = 'Vous n\'êtes pas connecte!';
            return $this->json(['status'=>$etat, 'message'=>$message]);

        }elseif (!$fichier) {
            $status = 404;
            $message =  'Aucun fichier trouvé pour cet id : '.$id;
            
            return $this->json(['status'=>$status,'message'=> $message]);
        }
        
        for($i = 0; $i< sizeof($fichier)-1; $i++){
                $fichiers[$i] = array('fichier_id'=>$fichier[$i]->getId(),
                'fichierName'=>$fichier[$i]->getNom(),
                'fichierDescription'=>$fichier[$i]->getDescription(),
                'fichierLink'=>$fichier[$i]->getLien(),
                'fichier_dateCreated'=>$fichier[$i]->getCreatedAt(),
                'fichier_dateUpdated'=>$fichier[$i]->getUpdatedAt(),
                'user'=>['userId'=>$fichier[$i]->getUtilisateur()->getId(),
                        'userEmail'=>$fichier[$i]->getUtilisateur()->getEmail(),
                        'userFirstName'=>$fichier[$i]->getUtilisateur()->getPrenom(),
                        'fichierName'=>$fichier[$i]->getUtilisateur()->getNom(),
                        'userPhone'=>$fichier[$i]->getUtilisateur()->getTelephone()
                ]);
        }
        return $this->json(['status' => $status,'message' =>$fichiers]);
    }

    /**
     * @Route("/api/fichiers/edit/{id}", name="fichier_edit", methods={"POST","PUT"})
     */
    public function edit($id, FichierRepository $fichiersRepo, Request $request, UtilisateurRepository $utilisateurRepo): Response
    {
        $token = $request->headers->get('Authorization');
        
        $utilisateur = $utilisateurRepo->findByToken(['Authorization'=>$token]);
        $fichier = $fichiersRepo->FichierUpdate($id);
       if(empty($token) || empty($utilisateur)){
            $etat = 400;
            $message = 'Vous n\'êtes pas connecte!';
            return $this->json(['status'=>$etat, 'message'=>$message]);
            
        }elseif(!$fichier) {
            $status = 404;
            $message =  'Aucun fichier trouvé pour cet id : '.$id;
            
            return $this->json(['status'=>$status,'message'=> $message]);

        }elseif 
        ($fichier[0]->getUtilisateur()->getToken() != $token) {
            $status = 401;
            $message ='Le fichier ' .$id. ' ne vous appartient pas ! ';

            return $this->json(['status' => $status,'message'=> $message]);
            
        }else{
        $status = 204;  
        /** @var UploadedFile $file */
        $file = $request->files->get('lien');
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $newFile = Urlizer::urlize($originalFilename).'-'.uniqid().'.'.$file->guessExtension();
        $destination = $this->getParameter('kernel.project_dir').'/public/files';

        $dest = '/files/';
        $file->move($destination, $newFile);
        // $fichier = new Fichier();
        $fichier[0]->setNom($request->get('nom'))
                   ->setDescription($request->get('description'))
                   ->setLien($dest.$newFile);
          
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($fichier[0]);
        $entityManager->flush();
        }
       
        return $this->json(['status' => $status,'message' =>'Fichier modifie avec success', 'details du fichier modifie'=>$fichier]);
    }

    /**
     * @Route("/api/fichiers/del/{id}", name="fichier_delete", methods={"POST", "DELETE"})
     */
    public function delete($id, EntityManagerInterface $entityManager, Request $request, UtilisateurRepository $utilisateurRepo): Response
    {
        $token = $request->headers->get('Authorization');
        $fichier = $this->getDoctrine()->getRepository(Fichier::class)->find($id);
        $utilisateur = $utilisateurRepo->findByToken(['Authorization'=>$token]);
        $status = 204;
        if(empty($token) || empty($utilisateur)){
            $etat = 400;
            $message = 'Vous n\'êtes pas connecte!';
            return $this->json(['status'=>$etat, 'message'=>$message]);
            
        }elseif(!$fichier) {
            $status = 404;
            $message =  'Aucun fichier trouvé pour cet id : '.$id;
            
            return $this->json(['status'=>$status,'message'=> $message]);

       }elseif 
        ($fichier->getUtilisateur()->getToken() != $token) {
            $status = 401;
            $message ='Le fichier ' .$id. ' ne vous appartient pas!';
           
            return $this->json(['status'=>$status,'message'=> $message]);

        }elseif($fichier->getVisible() === 'inactif'){
            $status = 404;
            $message =   'Le fichier ' .$id. ' n\'existe pas!';
            
            return $this->json(['status'=>$status,'message'=> $message]);
        }else{
            $fichier->setVisible(Fichier::VISIBLE_ARRAY['INACTIF']);
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($fichier);
        $entityManager->flush();;


        return $this->json(['status' => $status,'message' =>'Fichier '.$id. ' supprime avec success'], Response::HTTP_SEE_OTHER); 
    }
   
}