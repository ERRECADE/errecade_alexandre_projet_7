<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Repository\ProduitRepository;

use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use App\Service\DoctrineObjectConstructor;

/**
 * @Route("/api/produits", name="api_produit_")
 */
class ProduitController extends AbstractController
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    
    /**
     * @Route("/", name="liste", methods={"GET"})
     *
     * @OA\Get(
     *     path="/api/produits/",
     *     tags={"Produit"},
     *     summary="Obtenir la liste des produits",
     *     @OA\Response(
     *         response="default",
     *         description="",
     *     )
     * )
     */
    public function getAllProduit(Request $request, TagAwareCacheInterface $tagAwareCacheInterface, ProduitRepository $produitRepository): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);
        $idCache = "getAllProduit-" . $page . "-" . $limit;
        
        $produitList = $tagAwareCacheInterface->get($idCache, function (ItemInterface $item) use ($produitRepository, $page, $limit) {
            $item->tag("produitcache");
            $item->expiresAfter(10);
            $list = $produitRepository->findAllProduit($page, $limit);
            $context = SerializationContext::create()->setGroups(['AllProduit']);
            $jsonProduitList = $this->serializer->serialize($list, 'json',$context);
            
            return new JsonResponse($jsonProduitList, Response::HTTP_OK, [], true);
        });
        
        return $produitList ?? new JsonResponse("Nous ne trouvons aucun produit.", Response::HTTP_NOT_FOUND);
    }

/**
 * @Route("/{id}", name="detail", methods={"GET"})
 *
 * @OA\Get(
 *     path="/api/produits/{id}",
 *     tags={"Produit"},
 *     summary="Obtenir les détails d'un produit"
 * )
 */
    public function getDetailProduit($id, Request $request, TagAwareCacheInterface $tagAwareCacheInterface, ProduitRepository $produitRepository)
    {    
        if ($id) {
            $idCache = "getDetailProduit-" . $id;
            
            $produit = $tagAwareCacheInterface->get($idCache, function (ItemInterface $item) use ($produitRepository, $id) {
                $item->tag("produitcacheUnique");
                $item->expiresAfter(10);
                $produit = $produitRepository->findOneBy(['id' => $id]);
                if ($produit) {
                    $jsonProduit = $this->serializer->serialize($produit, 'json');
                    return new JsonResponse($jsonProduit, Response::HTTP_OK, ['accept' => 'json'], true);
                }
                return null;
            });
            
            return $produit ?? new JsonResponse("Le produit n'est pas dans notre base de données.", Response::HTTP_NOT_FOUND);
        }
    }
}