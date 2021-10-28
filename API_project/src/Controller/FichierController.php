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

        $visible = $fichierRepository->findByOne('visible');

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
                ->setVisible($request->get());


        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($fichier);
        $entityManager->flush();
        
        // dd($fichier);
        return $this->json([['Fichier cree avec success'], $fichier], 201);
    }

    /**
     * @Route("/api/fichiers/show", name="fichier_index", methods={"GET"})
     */
    public function index(FichierRepository $fichierRepository): Response
    {

        return $this->json($fichierRepository->findAll(), 200);
    }

    /**
     * @Route("/api/fichiers/show/{id}", name="fichier_show", methods={"GET"})
     */
    public function show($id, Request $request): Response
    {
        $fichier = $this->getDoctrine()->getRepository(Fichier::class)->find($id);
        if (!$fichier) {
            throw $this->createNotFoundException(
                'Aucun fichier trouvé pour cet id : '.$id
            );
        }
        return $this->json($fichier, 200);
    }

    /**
     * @Route("/api/fichiers/edit/{id}", name="fichier_edit", methods={"POST","PUT"})
     */
    public function edit(?Fichier $fichier, Request $request, UtilisateurRepository $utilisateurRepo, $id): Response
    {
            $fichier = $this->getDoctrine()->getRepository(Fichier::class)->find($id);
            if (!$fichier) {
                throw $this->createNotFoundException(
                    'Aucun fichier trouvé pour cet id : '.$id
                );
            }else{
            $id = $request->get('id');

            $utilisateur = $utilisateurRepo->findById(['id'=>$id]);
    
            /** @var UploadedFile $file */
            $file = $request->files->get('lien');
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $newFile = Urlizer::urlize($originalFilename).'-'.uniqid().'.'.$file->guessExtension();
            $destination = $this->getParameter('kernel.project_dir').'/public/files';
    
            $dest = '/files/';
            $file->move($destination, $newFile);
            // $fichier = new Fichier();
            $fichier->setNom($request->get('nom'))
                    ->setDescription($request->get('description'))
                    ->setLien($dest.$newFile);
                    // ->setUtilisateur($utilisateur[0])
                    // ->setVisible($request->get());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($fichier);
            $entityManager->flush();
            }
           
        return $this->json([['Fichier modifie avec success'], $fichier]);
    }

    /**
     * @Route("/api/fichiers/del/{id}", name="fichier_delete", methods={"GET", "DELETE"})
     */
    public function delete($id): Response
    {
        $fichier = $this->getDoctrine()->getRepository(Fichier::class)->find($id);
        if (!$fichier) {
            throw $this->createNotFoundException(
                'Aucun fichier trouvé pour cet id : '.$id
            );
        }
        // if ($this->isCsrfTokenValid('delete'.$fichier->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($fichier);
            $entityManager->flush();
        // }

        return $this->json(['Fichier supprime avec success'], Response::HTTP_SEE_OTHER);    }
}
