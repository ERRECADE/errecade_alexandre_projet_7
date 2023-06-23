<?php
namespace App\Controller;

use App\Entity\Produit;
use App\Repository\ProduitRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/api/produit", name="api_produit_")
 */
class ProduitController extends AbstractController
{
    /**
     * @Route("/liste", name="liste", methods={"GET"})
     * Récupération liste produits
     */
    public function getAllProduit(ProduitRepository $produitRepository, SerializerInterface $serializer): JsonResponse
    {
        $produitList = $produitRepository->findAll();
        if($produitList){
            $serializedProduitList = [];
            foreach ($produitList as $produit) {
                $serializedProduit = [
                    'id' => $produit->getId(),
                    'name' => $produit->getName(),
                ];
                $serializedProduitList[] = $serializedProduit;
            }
        
            $jsonProduitList = $serializer->serialize($serializedProduitList, 'json');
            return new JsonResponse($jsonProduitList, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);

    }

    /**
     * @Route("/detail/{id}", name="detail", methods={"GET"})
     * Récupération d'un seul produits
     */
    public function getDetailProduit(Produit $produit, SerializerInterface $serializer): JsonResponse 
    {
        $jsonProduit = $serializer->serialize($produit, 'json');
        return new JsonResponse($jsonProduit, Response::HTTP_OK, ['accept' => 'json'], true);
    }
}