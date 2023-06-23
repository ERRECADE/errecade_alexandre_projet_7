<?php
namespace App\Controller;

use DateTime;
use App\Entity\Client;
use App\Entity\Utilisateur;
use App\Repository\ClientRepository;

use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @Route("/api/utilisateur", name="api_utilisateur_")
 */
class UtilisateurController extends AbstractController
{
    private $security;
    private $clientRepository;
    private $serializer;
    private $utilisateurRepository;
    private $validator;


    public function __construct(Security $security, ClientRepository $clientRepository,SerializerInterface $serializer,UtilisateurRepository $utilisateurRepository, ValidatorInterface $validator)
    {
        $this->security = $security;
        $this->clientRepository = $clientRepository;
        $this->serializer = $serializer;
        $this->utilisateurRepository = $utilisateurRepository;
        $this->validator = $validator;
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

        $errors = $this->validator->validate($utilisateur);

        if ($errors->count() > 0) {
            return new JsonResponse($this->serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

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

   public function updateUtilisateur(Request $request): JsonResponse
   { 
    $id = $request->query->get('id');
    $client = $this->security->getUser();
    $utilisateur = $this->utilisateurRepository->findOneByUtilisateur($id, $client);

    if (!$utilisateur) {
        return new JsonResponse("Utilisateur non trouvé.", Response::HTTP_NOT_FOUND);
    }

    $updatedUtilisateur = $this->serializer->deserialize(
        $request->getContent(),
        Utilisateur::class,
        'json',
        [AbstractNormalizer::OBJECT_TO_POPULATE => $utilisateur]
    );

    $updatedUtilisateur->setClient($client);
    $updatedUtilisateur->setUpdatedAt(new DateTime);

    $this->utilisateurRepository->add($updatedUtilisateur, true);

    return new JsonResponse("L'utilisateur a bien été mis à jour.", Response::HTTP_OK);

  }
}