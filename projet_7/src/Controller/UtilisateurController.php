<?php
namespace App\Controller;

use App\Entity\Utilisateur;
use App\Entity\Client;
use App\Repository\ClientRepository;
use App\Repository\UtilisateurRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use DateTime;


/**
 * @Route("/api/utilisateur", name="api_utilisateur_")
 */
class UtilisateurController extends AbstractController
{
    private $security;
    private $clientRepository;
    private $serializer;
    private $utilisateurRepository;

    public function __construct(Security $security, ClientRepository $clientRepository,SerializerInterface $serializer,UtilisateurRepository $utilisateurRepository)
    {
        $this->security = $security;
        $this->clientRepository = $clientRepository;
        $this->serializer = $serializer;
        $this->utilisateurRepository = $utilisateurRepository;
    }

    /**
     * @Route("/global", name="global", methods={"GET"})
     * Récupération d'un seul produits
     */
    public function getUtilisateurFromClient()
    {
        $client = $this->security->getUser();

        if ($client) {
            $utilisateurs = $this->utilisateurRepository->findAllByClient($client);
            $jsonUtilisateurs = $this->serializer->serialize($utilisateurs, 'json',['groups' => 'getClient']);
            return new JsonResponse($jsonUtilisateurs, Response::HTTP_OK, ['accept' => 'json'], true);
        }

        return new JsonResponse("Le client n'existe pas.", Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route("/detail", name="detail", methods={"POST"})
     * Récupération d'un seul utilisateur
     */
    public function getUtilisateur(Request $request)
    {
        $client = $this->security->getUser();
        $data = json_decode($request->getContent(), true);
        $id = $data['id'];

        if ($client) {
            $utilisateur = $this->utilisateurRepository->findOneByUtilisateur($id, $client);
            if ($utilisateur) {
                $jsonUtilisateur = $this->serializer->serialize($utilisateur, 'json', ['groups' => 'getUtilisateurDetail']);
                return new JsonResponse($jsonUtilisateur, Response::HTTP_OK, ['accept' => 'json'], true);
            } else {
                return new JsonResponse("Utilisateur non trouvé.", Response::HTTP_NOT_FOUND);
            }
        }
    }

    /**
     * @Route("/delets", name="delets", methods={"DELETE"})
     * Suprimez un utilisateur
     */
    public function deleteUtilisateur(Request $request)
    {
        $client = $this->security->getUser();
        $data = json_decode($request->getContent(), true);
        $id = $data['id'];

        if ($id) {
            $utilisateur = $this->utilisateurRepository->findOneByUtilisateur($id, $client);
            if ($utilisateur) {
                $this->utilisateurRepository->remove($utilisateur,true);
                return new JsonResponse("Utilisateur suprimé.", Response::HTTP_OK);
            } else {
                return new JsonResponse("Utilisateur non trouvé.", Response::HTTP_NOT_FOUND);
            }
        }
    }

    /**
     * @Route("/create", name="create", methods={"POST"})
     * Crée un utilisateur
     */
    public function createUtilisateur(Request $request): JsonResponse 
    {

        $utilisateur = $this->serializer->deserialize($request->getContent(), Utilisateur::class, 'json');
        $content = $request->toArray();
        $emailClient = $this->security->getUser()->getUserIdentifier();

        $utilisateur->setClient($this->clientRepository->findOneBy(["email" => $emailClient]));
        $utilisateur->setActif(1);
        $utilisateur->setIsDeleted(0);
        $utilisateur->setCreatedAt(new DateTime);

        $this->utilisateurRepository->add($utilisateur,true);

        return new JsonResponse("l'utilisateur a bien était ajouter", Response::HTTP_CREATED);
   }

    /**
     * @Route("/update", name="update", methods={"PUT"})
     * Modifier un utilisateur
    */

   public function updateUtilisateur(Request $request, Utilisateur $utilisateur)
   {
    error_log('la 1');
       $utilisateur = $this->serializer->deserialize($request->getContent(), 
                Utilisateur::class, 
               'json', 
               [AbstractNormalizer::OBJECT_TO_POPULATE => $utilisateur]);
               error_log('la 2');
        $content = $request->toArray();
        $emailClient = $this->security->getUser()->getUserIdentifier();
        error_log('la 3');
        $utilisateur->setClient($this->clientRepository ->findOneBy(["email" => $emailClient]));
        $utilisateur->setUpdatedAt(new DateTime);
        error_log('la 4');
        $this->utilisateurRepository->add($utilisateur,true); 
        error_log('la 5');
       return new JsonResponse("l'utilisateur a bien étais mis a jour", JsonResponse::HTTP_NO_CONTENT);
  }
}