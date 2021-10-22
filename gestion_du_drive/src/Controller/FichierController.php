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
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class FichierController extends AbstractController
{
    /**
     * @Route("/api/fichier/new", name="fichier_new", methods={"GET","POST"})
     */
    public function new(Request $request, UtilisateurRepository $utilisateurRepo, EntityManagerInterface $entityManager): Response
    {
        $id = $request->get('id');

        $utilisateur = $utilisateurRepo->findById($id);
       
        /** @var UploadedFile $file */
        $file = $request->files->get('lien');
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $newFile = Urlizer::urlize($originalFilename).'-'.uniqid().'.'.$file->guessExtension();
        $destination = $this->getParameter('kernel.project_dir').'/public/files';

        $dest = '/public/files/';
        dd($file->move($destination, $newFile));
        $fichier = new Fichier();
        $fichier->setNom($request->get('nom'))
                ->setDescription($request->get('description'))
                ->setLien($dest.$newFile)
                ->setUtilisateur($utilisateur[0]);


        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($fichier);
        $entityManager->flush();

        // dd($fichier);
        return $this->json([['Fichier cree avec success'], $fichier], 201);
    }

    /**
     * @Route("/api/fichier/show", name="fichier_index", methods={"GET"})
     */
    public function index(FichierRepository $fichierRepository): Response
    {
        return $this->json($fichierRepository->findAll(), 200);
    }

    /**
     * @Route("/api/fichier/show/{id}", name="fichier_show", methods={"GET"})
     */
    public function show($id): Response
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
     * @Route("/api/fichier/edit/{id}", name="fichier_edit", methods={"GET","PUT"})
     */
    public function edit($id, Request $request, Fichier $fichier, EntityManagerInterface $entityManager): Response
    {
        $fichier = $this->getDoctrine()->getRepository(Fichier::class)->find($id);
        if (!$fichier) {
            throw $this->createNotFoundException(
                'Aucun fichier trouvé pour cet id : '.$id
            );
        }
        $form = $this->createForm(FichierType::class, $fichier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('fichier_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('fichier/edit.html.twig', [
            'fichier' => $fichier,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/api/fichier/del/{id}", name="fichier_delete", methods={"DELETE"})
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
