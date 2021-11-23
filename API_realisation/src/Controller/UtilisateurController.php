<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
// use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpClient\ScopingHttpClient;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class UtilisateurController extends AbstractController
{
    
    /**
     * @Route("/api/utilisateurs/add", name="utilisateur_new", methods={"POST"})
     */
    public function new(Request $request, UtilisateurRepository $userRepo, SerializerInterface $serializer, UserPasswordHasherInterface $hasherPassword, EntityManagerInterface $em)
    {
        $requestData = \json_decode($request->getContent(), true);
        
        $email = $requestData['email'];

       $userData = $userRepo->findOneBy(['Email'=>$email]);
       $status = 201; 

       if(empty($userData))
        {
            $jsonReceive = $request->getContent();
           
            $utilisateur = $serializer->deserialize($jsonReceive, Utilisateur::class, 'json');
         
            $hash = $hasherPassword->hashPassword($utilisateur, $utilisateur->getPassword());
            $utilisateur->setPassword($hash);
           
            $em = $this->getDoctrine()->getManager();
            $em->persist($utilisateur);
            $em->flush();
        }else{
            $status = 400;
            $message='Cet email existe deja';

            return $this->json(['status'=>$status, 'message'=>$message]);
        }
       
        return $this->json(['status' => $status, 'message' => 'Utilisateur cree avec success']);
    }
    
    /**
     * @Route("/api/utilisateurs/list", name="utilisateur_index", methods={"GET"})
     */
    public function index(UtilisateurRepository $utilisateurRepo): Response
    {
        $utilisateurs = $utilisateurRepo->findAll();
        $status = 200;
       return $this->json(['status' => $status, 'message' => $utilisateurs]);
    }
    
    /**
     * @Route("/api/utilisateurs/list/{id}", name="utilisateur_show", methods={"GET"})
     */
    public function show($id): Response
    {
        $utilisateur = $this->getDoctrine()->getRepository(Utilisateur::class)->find($id);
        if (!$utilisateur) {
            // throw $this->createNotFoundException(
            return $this->json(['status'=>404, 'message'=>'Aucun utilisateur trouvé pour cet id : '.$id]);
            // );
        }
        $status = 200;
       return $this->json(['status' => $status, 'message' => $utilisateur]);
    }

    /**
     * @Route("/api/utilisateurs/{id}", name="utilisateur_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Utilisateur $utilisateur): Response
    {
        $status = 204;
        // if ($this->isCsrfTokenValid('delete'.$utilisateur->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($utilisateur);
            $em->flush();
        // }

        return $this->json(['status' => $status, 'message' =>'Utilisateur supprime avec success'], Response::HTTP_SEE_OTHER);
    }
    
    /**
     * @Route("/api/login", name="connexion_login", methods={"POST"})
     */
    public function login(Request $request, UtilisateurRepository $userRepo): JsonResponse
    {
       
        $user = \json_decode($request->getContent(), true);
        $email = $user['email'];
        $utilisateur[0] = $userRepo->findOneBy(['Email'=>$email]);
        
        if($utilisateur[0]->getEmail() === $user['email'] || $utilisateur[0]->getPassword() === $user['password']){
            $status = 400;
            $message= 'Mauvaise requete, verifiez vos donnees!';

            return $this->json(['Status'=> $status, 'message'=>$message]);
        }else{
            dd($token);
            $client = HttpClient::create();
            $status = 201;
            $response = $client->request('POST', 'http://localhost:8000/api/generate_token', [
                'headers' => [
                    
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    'email' => $user['email'],
                    'password' => $user['password'], 
                ],
            ]);
            $token = $response->toArray();
            
        }
       
        // $em = $this->getDoctrine()->getManager();
        // $em->persist($token);
        // $em->flush();
        return $this->json(['Status'=>$status, 'Token'=>$token["token"],
         'User'=>$utilisateur[0]->getEmail()
                                // ->getNom()
                                // ->getPrenom()
                                // ->getTelephone()
    ]);

    }

    /**
     * @Route("/api/generate_token", name="generate_token", methods="POST")
     */
    public function generateToken() //: JsonResponse
    {
        
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
