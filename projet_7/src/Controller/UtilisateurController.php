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
     * @Route("/detail", name="utilisateur_detail", methods={"POST"})
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
}