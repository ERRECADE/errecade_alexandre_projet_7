<?php
namespace App\Controller;

use App\Entity\Produit;
use App\Repository\ProduitRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/api/produit", name="api_produit_")
 */
class ProduitController extends AbstractController
{
    private $serializer;
    private $produitRepository;
    public function __construct(SerializerInterface $serializer, ProduitRepository $produitRepository)
    {
        $this->serializer = $serializer;
        $this->produitRepository = $produitRepository;
    }
    /**
     * @Route("/liste", name="liste", methods={"GET"})
     * Récupération liste produits
     */
    public function getAllProduit(ProduitRepository $produitRepository): JsonResponse
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
        
            $jsonProduitList = $this->serializer->serialize($serializedProduitList, 'json');
            return new JsonResponse($jsonProduitList, Response::HTTP_OK, [], true);
        }
        return new JsonResponse("Nous ne trouvons aucun produit.", Response::HTTP_NOT_FOUND);

    }

    /**
     * @Route("/detail", name="detail", methods={"POST"})
     * Récupération d'un seul produits
     */
    public function getDetailProduit(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $id = $data['id'];
        
        if ($id) {
            $produit =  $this->produitRepository->findOneBy(['id' => $id]);
            if ($produit) {
                $jsonProduit = $this->serializer->serialize($produit, 'json');
                return new JsonResponse($jsonProduit, Response::HTTP_OK, ['accept' => 'json'], true);
            } else {
                return new JsonResponse("Le produit n'est pas dans notre base de données.", Response::HTTP_NOT_FOUND);
            }
        }
    }
}