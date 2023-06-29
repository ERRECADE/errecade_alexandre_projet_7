<?php

namespace App\Controller;

use DateTime;
use App\Entity\Client;
use App\Entity\Utilisateur;
use JMS\Serializer\Serializer;

use App\Repository\ClientRepository;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Construction\ObjectConstructorInterface;
use App\Repository\UtilisateurRepository;
use JMS\Serializer\DeserializationContext;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

/**
 * @Route("/api/utilisateurs", name="api_utilisateurs_")
 */
class UtilisateurController extends AbstractController
{
    private $security;
    private $clientRepository;
    private $serializer;
    private $validator;


    public function __construct(Security $security, ClientRepository $clientRepository, SerializerInterface $serializer, ValidatorInterface $validator)
    {
        $this->security = $security;
        $this->clientRepository = $clientRepository;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    /**
     * @Route("/", name="global", methods={"GET"})
     *
     * @OA\Get(
     *     path="/api/utilisateurs",
     *     tags={"Utilisateur"},
     *     summary="Récupérer tous les utilisateurs du client connecté",
     *     @OA\Response(
     *         response=200,
     *         description="Liste des utilisateurs",
     *         @Model(type=Utilisateur::class, groups={"getClient"})
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Le client n'existe pas, il ne possède donc aucun utilisateur"
     *     )
     * )
     */
    public function getUtilisateurFromClient(Request $request, TagAwareCacheInterface $tagAwareCacheInterface, UtilisateurRepository $utilisateurRepository)
    {
        $client = $this->security->getUser();
        $emailClient = $this->security->getUser()->getUserIdentifier();
        $idClient = $this->clientRepository->findOneBy(["email" => $emailClient]);
        if ($client) {
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 3);
            $cacheKey = 'getUtilisateurFromClient-' . $idClient->getId() . '_' . $page . '_' . $limit;

            $utilisateurList = $tagAwareCacheInterface->get($cacheKey, function (ItemInterface $item) use ($utilisateurRepository, $client, $page, $limit) {
                $item->expiresAfter(1);
                $item->tag('utilisateursCacheGlobal');

                $utilisateurs = $utilisateurRepository->findAllByClient($client, $page, $limit);
                $context = SerializationContext::create()->setGroups(['getClient']);

                $jsonUtilisateurs = $this->serializer->serialize($utilisateurs, 'json', $context);
                return new JsonResponse($jsonUtilisateurs, Response::HTTP_OK, ['accept' => 'json'], true);
            });

            return $utilisateurList ?? new JsonResponse("Le client n'existe pas, il ne possède donc aucun utilisateur", Response::HTTP_NOT_FOUND);

        }

        return new JsonResponse("Le client n'existe pas, il ne possède donc aucun utilisateur", Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route("/{id}", name="detail_utilisateur", methods={"GET"})
     *
     * @OA\GET(
     *     path="/api/utilisateurs/{id}",
     *     tags={"Utilisateur"},
     *     summary="Récupérer les détails d'un utilisateur",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de l'utilisateur",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails de l'utilisateur",
     *         @Model(type=Utilisateur::class, groups={"getUtilisateurDetail"})
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Utilisateur non trouvé"
     *     )
     * )
     */
    public function getUtilisateurDetail($id, Request $request, TagAwareCacheInterface $tagAwareCacheInterface, UtilisateurRepository $utilisateurRepository)
    {
        $client = $this->security->getUser();

        if ($client) {
            $cacheKey = 'getUtilisateur-' . $id;

            $utilisateurList = $tagAwareCacheInterface->get($cacheKey, function (ItemInterface $item) use ($utilisateurRepository, $id, $client) {
                $item->expiresAfter(1);
                $item->tag('utilisateursCacheDetail');

                $utilisateur = $utilisateurRepository->findOneByUtilisateur($id, $client);
                if ($utilisateur) {
                    $context = SerializationContext::create()->setGroups(['getUtilisateurDetail']);
                    $jsonUtilisateur = $this->serializer->serialize($utilisateur, 'json', $context);
                    return new JsonResponse($jsonUtilisateur, Response::HTTP_OK, ['accept' => 'json'], true);
                }

                return null;
            });

            return $utilisateurList ?? new JsonResponse("Utilisateur non trouvé.", Response::HTTP_NOT_FOUND);

        }
    }

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"})
     *
     * @OA\Delete(
     *     path="/api/utilisateurs/{id}",
     *     tags={"Utilisateur"},
     *     summary="Supprimer un utilisateur",
     * )
     */
    public function deleteUtilisateur($id, Request $request, UtilisateurRepository $utilisateurRepository)
    {
        $client = $this->security->getUser();
        if ($id) {
            $utilisateur = $utilisateurRepository->findOneByUtilisateur($id, $client);
            if ($utilisateur) {
                $utilisateurRepository->remove($utilisateur, true);
                return new JsonResponse(null, Response::HTTP_NO_CONTENT);
            } else {
                return new JsonResponse("Utilisateur non trouvé.", Response::HTTP_NOT_FOUND);
            }
        }
    }

    /**
     * @Route("/", name="create", methods={"POST"})
     * @OA\Post(
     *     path="/api/utilisateurs",
     *     tags={"Utilisateur"},
     *     summary="Créer un nouvel utilisateur",
     *     @OA\Response(
     *         response=201,
     *         description="Utilisateur créé"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Mauvaise requête",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/ConstraintViolation")
     *         )
     *     )
     * )
     */
    public function createUtilisateur(Request $request, UtilisateurRepository $utilisateurRepository): JsonResponse
    {
        $utilisateur = $this->serializer->deserialize($request->getContent(), Utilisateur::class, 'json');
        $emailClient = $this->security->getUser()->getUserIdentifier();
        $errors = $this->validator->validate($utilisateur);
        if ($errors->count() > 0) {
            return new JsonResponse($this->serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
        $utilisateur->setClient($this->clientRepository->findOneBy(["email" => $emailClient]));
        $utilisateur->setActif(1);
        $utilisateur->setIsDeleted(0);
        $utilisateur->setCreatedAt(new DateTime());
        $utilisateurRepository->add($utilisateur, true);

        $context = SerializationContext::create()->setGroups(['getUtilisateurDetail']);
        $jsonUtilisateur = $this->serializer->serialize($utilisateur, 'json', $context);
        return new JsonResponse($jsonUtilisateur, Response::HTTP_CREATED, ['accept' => 'json'], true);
    }

    /**
     * @Route("/{id}", name="update", methods={"PUT"})
     *
     * @OA\Put(
     *     path="/api/utilisateurs/{id}",
     *     tags={"Utilisateur"},
     *     summary="Mettre à jour un utilisateur"
     * )
     */
    public function updateUtilisateur($id, Request $request, UtilisateurRepository $utilisateurRepository): JsonResponse
    {
        $client = $this->security->getUser();
        $utilisateur = $utilisateurRepository->findOneByUtilisateur($id, $client);

        if (!$utilisateur) {
            return new JsonResponse("Utilisateur non trouvé.", Response::HTTP_NOT_FOUND);
        }

        $jsonContent = $request->getContent();
        $updatedUtilisateur = $this->serializer->deserialize($jsonContent, Utilisateur::class, 'json');

        if ($updatedUtilisateur instanceof Utilisateur) {
            $utilisateur->setUpdatedAt(new DateTime());
            $utilisateur->setName($updatedUtilisateur->getName());
            $utilisateur->setPrenom($updatedUtilisateur->getPrenom());

            $utilisateurRepository->add($utilisateur, true);

            $context = SerializationContext::create()->setGroups(['getUtilisateurDetail']);
            $jsonUtilisateur = $this->serializer->serialize($utilisateur, 'json', $context);

            return new JsonResponse($jsonUtilisateur, Response::HTTP_OK, ['Content-Type' => 'application/json'], true);
        } else {
            return new JsonResponse("Erreur lors de la désérialisation de l'utilisateur.", Response::HTTP_BAD_REQUEST);
        }
    }
}
