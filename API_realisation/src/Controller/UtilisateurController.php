<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UtilisateurController extends AbstractController
{
    /**
     * @Route("/utilisateurs/add", name="utilisateur_new", methods={"POST"})
     */
    public function new(Request $request, SerializerInterface $serializer, EntityManagerInterface $em)
    {
        $jsonReceive = $request->getContent();
        
        $utilisateur = $serializer->deserialize($jsonReceive, Utilisateur::class, 'json');

        
        $em = $this->getDoctrine()->getManager();
        $em->persist($utilisateur);
        $em->flush();

        // dd($utilisateur);
       return $this->json(['Utilisateur cree avec success'], 201);
    }

     /**
     * @Route("/api/utilisateurs/list", name="utilisateur_index", methods={"GET"})
     */
    public function index(UtilisateurRepository $utilisateurRepo): Response
    {
       return $this->json($utilisateurRepo->findAll(), 200);
    }
    
     /**
     * @Route("/api/utilisateurs/list/{id}", name="utilisateur_show", methods={"GET"})
     */
    public function show($id): Response
    {
        $utilisateur = $this->getDoctrine()->getRepository(Utilisateur::class)->find($id);
        if (!$utilisateur) {
            throw $this->createNotFoundException(
                'Aucun utilisateur trouvé pour cet id : '.$id
            );
        }

       return $this->json($utilisateur, 200);
    }

    /**
     * @Route("/api/utilisateurs/{id}", name="utilisateur_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Utilisateur $utilisateur): Response
    {
        // if ($this->isCsrfTokenValid('delete'.$utilisateur->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($utilisateur);
            $em->flush();
        // }

        return $this->json(['Utilisateur supprime avec success'], Response::HTTP_SEE_OTHER);
    }
}
