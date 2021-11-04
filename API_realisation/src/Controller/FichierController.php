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
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



class FichierController extends AbstractController
{
    /**
     * @Route("/api/fichiers/new", name="fichier_new", methods={"POST"})
     */
    public function new(Request $request, UtilisateurRepository $utilisateurRepo, FichierRepository $fichierRepository, EntityManagerInterface $entityManager): Response
    {
        $id = $request->get('id');

        $utilisateur = $utilisateurRepo->findById(['id'=>$id]);

    //    dd($utilisateur);
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
      

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($fichier);
        $entityManager->flush();
        
        // dd($fichier);
        return $this->json([['Fichier cree avec success'], $fichier], 201);
    }

    /**
     * @Route("/api/fichiers/show", name="fichier_show_visible", methods={"GET"})
     */
    public function show_visible(FichierRepository $fichiersRepo): Response
    {
        $fichier = $fichiersRepo->selectFichiersVisible();
        
      

        return $this->json($fichier, 200);
    }

    /**
     * @Route("/api/fichiers/utilisateur/{id}", name="fichier_view_visible", methods={"GET"})
     */
    public function view_visible(FichierRepository $fichiersRepo, $id): Response
    {
        $fichier = $fichiersRepo->FichiersUtilisateur($id);

        if (!$fichier) {
            throw $this->createNotFoundException(
                'Aucun fichier trouvé pour cet id : '.$id
            );
        }
        dd($fichier);
        return $this->json($fichier, 200);
    }

    /**
     * @Route("/api/fichiers/edit/{id}", name="fichier_edit", methods={"GET","POST","PUT"})
     */
    public function edit( $id, FichierRepository $fichiersRepo, Request $request, UtilisateurRepository $utilisateurRepo): Response
    {
        $fichier = $fichiersRepo->FichierUpdate($id);
        
        if (!$fichier) {
            throw $this->createNotFoundException(
                'Aucun fichier trouvé pour cet id : '.$id
            );
        }else{
        // $id = $request->get('id');

        // $utilisateur = $utilisateurRepo->findById(['id'=>$id]);

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
                // ->setUtilisateur($utilisateur[0])
                // ->setVisible($request->get($visible));
               
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($fichier[0]);
        $entityManager->flush();
        }
        // dd($fichier);
        return $this->json([['Fichier modifie avec success'], $fichier]);
    }

    /**
     * @Route("/api/fichiers/del/{id}", name="fichier_delete", methods={"GET", "DELETE"})
     */
    public function delete($id, EntityManagerInterface $entityManager): Response
    {
        $fichier = $this->getDoctrine()->getRepository(Fichier::class)->find($id);

        //dd($fichier);
        if (!$fichier) {
            throw $this->createNotFoundException(
                'Aucun fichier trouvé pour cet id : '.$id
            );
        }else{
            $fichier->setVisible(Fichier::VISIBLE_ARRAY['INACTIF']);
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($fichier);
        $entityManager->flush();;

        // dd($fichier);

        return $this->json(['Fichier '.$id. ' supprime avec success'], Response::HTTP_SEE_OTHER); 
    }

    
      // /**
    //  * @Route("/api/fichiers", name="fichier_index", methods={"GET"})
    //  */
    // public function index(FichierRepository $fichierRepository): Response
    // {

    //     return $this->json($fichierRepository->findAll(), 200);
    // }

    // /**
    //  * @Route("/api/fichiers/{id}", name="fichier_show", methods={"GET"})
    //  */
    // public function show($id, Request $request): Response
    // {
    //     $fichier = $this->getDoctrine()->getRepository(Fichier::class)->find($id);
    //     if (!$fichier) {
    //         throw $this->createNotFoundException(
    //             'Aucun fichier trouvé pour cet id : '.$id
    //         );
    //     }
    //     return $this->json($fichier, 200);
    // }
}