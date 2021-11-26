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
        $user_id = $request->get('user_id');
       
        $utilisateur = $utilisateurRepo->findById(['user_id'=>$user_id]);

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
    public function show_visible(FichierRepository $fichiersRepo): Response
    {
        $fichier = $fichiersRepo->selectFichiersVisible();
        
        $status = 200;

        return $this->json(['status' => $status,'message' =>$fichier]);
    }

    /**
     * @Route("/api/fichiers/utilisateur/{id}", name="fichier_view_visible", methods={"GET"})
     */
    public function view_visible(FichierRepository $fichiersRepo, $id): Response
    {
        $fichier = $fichiersRepo->FichiersUtilisateur($id);
        $status = 200;
        if (!$fichier) {
            $status = 404;
            $message =  'Aucun fichier trouvé pour cet id : '.$id;
            
            return $this->json(['status'=>$status,'message'=> $message]);
        }
        return $this->json(['status' => $status,'message' =>$fichier]);
    }

    /**
     * @Route("/api/fichiers/edit/{id}", name="fichier_edit", methods={"POST","PUT"})
     */
    public function edit($id, FichierRepository $fichiersRepo, Request $request, UtilisateurRepository $utilisateurRepo): Response
    {
        $user_id = $request->get('user_id');
        
        $utilisateur = $utilisateurRepo->findById(['user_id'=>$user_id]);
        $fichier = $fichiersRepo->FichierUpdate($id);
      
        if (!$fichier) {
            $status = 404;
            $message =  'Aucun fichier trouvé pour cet id : '.$id;
            
            return $this->json(['status'=>$status,'message'=> $message]);

        }elseif 
        ($fichier[0]->getUtilisateur()->getId() != $user_id) {
            $status = 401;
            $message ='Le fichier ' .$id. ' ne vous appartient pas ! '.$user_id. 'Verifiez!!!';

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
    public function delete($id, EntityManagerInterface $entityManager, Request $request): Response
    {
        $user_id = $request->get('user_id');
        $fichier = $this->getDoctrine()->getRepository(Fichier::class)->find($id);
        $status = 204;
        if (!$fichier) {
            $status = 404;
            $message =  'Aucun fichier trouvé pour cet id : '.$id;
            
            return $this->json(['status'=>$status,'message'=> $message]);

       }elseif 
        ($fichier->getUtilisateur()->getId() != $user_id) {
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