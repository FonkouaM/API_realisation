<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

       $userData = $userRepo->findOneBy(['email'=>$email]);
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
        $utilisateur = $utilisateurRepo->findAll();
        $status = 200;

        for($i = 0; $i<=50; $i++){
            $utilisateurs[$i] = ([          
                        'user_id'=>$utilisateur[$i]->getId(),
                        'user_email'=>$utilisateur[$i]->getEmail(),
                        'user_name'=>$utilisateur[$i]->getNom(),
                        'user_firstname'=>$utilisateur[$i]->getPrenom(),
                        'user_phone'=>$utilisateur[$i]->getTelephone(),
                        'user_dateCreated'=>$utilisateur[$i]->getCreatedAt(),
                        'user_dateUpdated'=>$utilisateur[$i]->getUpdatedAt(),
            ]);
        }
       
        return $this->json(['status' => $status, 'utilisateur' =>$utilisateurs]);
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
        $utilisateurs =[
                    'user_id'=>$utilisateur->getId(),
                    'user_email'=>$utilisateur->getEmail(),
                    'user_name'=>$utilisateur->getNom(),
                    'user_firstname'=>$utilisateur->getPrenom(),
                    'user_phone'=>$utilisateur->getTelephone(),
                    'user_dateCreated'=>$utilisateur->getCreatedAt(),
                    'user_dateUpdated'=>$utilisateur->getUpdatedAt(),
        ];
        return $this->json(['status' => $status, 'utilisateur' =>$utilisateurs]);
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
     * @Route("/api/generate_token", name="generate_token", methods="POST")
     */
    public function generateToken() //: JsonResponse
    {
        
    }

    /**
     * @Route("/api/login", name="connexion_login", methods={"POST"})
     */
    public function login(Request $request, UtilisateurRepository $userRepo, UserPasswordHasherInterface $hasherPassword, EntityManagerInterface $em): JsonResponse
    {
        $user = \json_decode($request->getContent(), true);
       
        $client = HttpClient::create();
        
        $response = $client->request('POST', 'http://localhost:8000/api/generate_token', [
            'headers' => [
                
                'Content-Type' => 'application/json'
            ],
            'json' => [
                'email' => $user['email'],
                'password' => $user['password'], 
            ],
        ]);
        $statusCode = $response->getStatusCode();

        if($statusCode === 401){

           $status = 401;
           $message = 'Mauvaise identification, verifiez vos donnees!';
           return $this->json(['status'=>$status, 'message'=>$message]);
        }else{
            $token = $response->toArray();
           
            $status = 201;
            $email = $user['email'];
            $utilisateur = $userRepo->findOneBy(['email'=>$email]);
            $utilisateur->setToken('Bearer '.$token["token"])
                        ->setStartDate(new \DateTime())
                        ->setEndDate(new \DateTime('+1day'));
               
            $em = $this->getDoctrine()->getManager();
            $em->persist($utilisateur);
            $em->flush();
            return $this->json(['status'=>$status, 'token'=>'Bearer '.$token["token"],
                    // 'Refresh_token'=>$
                    'user_email'=>$utilisateur->getEmail(),
                    'user_name'=>$utilisateur->getNom(),
                    'user_firstname'=>$utilisateur->getPrenom(),
                    'user_phone'=>$utilisateur->getTelephone()
            ]);        
        }
    }

    /**
     * @Route("/api/logout", name="deconnexion_logout")
     */
    public function logout(Request $request, UtilisateurRepository $userRepo, EntityManagerInterface $em): JsonResponse
    {
        $token = $request->headers->get('Authorization');
        $status = 200;

        $utilisateur = $userRepo->findOneBy(['token'=>$token]);
        if(empty($utilisateur)){
            $etat = 400;
            $message = 'Vous n\'avez jamais ete connecte!';
            return $this->json(['status'=>$etat, 'message'=>$message]);
        }
        $utilisateur->setToken(null)
                    ->setStartDate(null)
                    ->setEndDate(null);
                    
        $em = $this->getDoctrine()->getManager();
        $em->persist($utilisateur);
        $em->flush();

        return $this->json(['status'=>$status, 'message'=>'Deconnexion valide']);
    }
}
