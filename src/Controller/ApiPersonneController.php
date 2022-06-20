<?php

namespace App\Controller;

use App\Entity\Adresse;
use App\Entity\Personne;
use App\Repository\PersonneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;

class ApiPersonneController extends AbstractController
{
    #[Route('/api/personne', name: 'app_api_personne', methods: ['GET'])]
    public function index(PersonneRepository $rep, SerializerInterface $serializer)
    {
        $personnes = $rep->findAll();

        // normalisation et encodage et sérialisation
        // $normalized = $normalizer->normalize($personnes, null, [
        //     'groups' => 'personne:read'
        // ]);
        // $json = json_encode($normalized);
        // $json = $serializer->serialize($personnes, 'json', ['groups' => 'personne:read']);

        // version plus simple mais avec autant d'étape interne
        return $this->json($personnes, 200, [], ['groups' => 'personne:read']);
    }

    #[Route('/api/personne', name: 'app_api_personne_add', methods: ['POST'])]
    public function add(EntityManagerInterface $em, SerializerInterface $serializer, Request $request)
    {
        $data = $request->getContent();
        try {
            $personne = $serializer->deserialize($data, Personne::class, 'json');
            $em->persist($personne);
            $em->flush();
            return $this->json($personne, 201, [], ['groups' => 'personne:read']);
        } catch (NotEncodableValueException $e) {
            return $this->json([
                'status' => 400,
                'message' => $e->getMessage()
            ]);
        }
    }

    #[Route('/api/personne/{id}', name: 'app_api_personne_delete', methods: ['DELETE'])]
    public function delete(int $id, PersonneRepository $rep, EntityManagerInterface $em)
    {
        $personne = $rep->find($id);
        if ($personne) {
            $em->remove($personne);
            $em->flush();
            return $this->json([
                'status' => 204,
                'message' => "L'identifiant $id a bien été supprimé!"
            ]);
        } else {
            return $this->json([
                'status' => 404,
                'message' => "Auncune donnée trouvée pour l'identifiant $id"
            ]);
        }
    }

    #[Route('/api/personne/{id}', name: 'app_api_personne_put', methods: ['PUT'])]
    public function edit(int $id, EntityManagerInterface $em, SerializerInterface $serializer, Request $request)
    {
        $data = $request->getContent();
        try {
            $personne = $serializer->deserialize($data, Personne::class, 'json');
            if ($id != $personne->getId()) {
                return $this->json([
                    'status' => 400,
                    'message' => "Incohérence : l'identifiant $id ne correspond pas à l'identifiant reçu dans le body"
                ]);
            }
            $p = $em->getRepository(Personne::class)->find($id);
            if (!$p) {
                return $this->json([
                    'status' => 404,
                    'message' => "Aucune correspondance avec l'identifiant $id"
                ]);
            }
            $p = $personne;
            $em->flush();
            return $this->json($p, 202, [], ['groups' => 'personne:read']);
        } catch (NotEncodableValueException $e) {
            return $this->json([
                'status' => 400,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * show Personne By id
     * @param int $id
     * @param EntityManagerInterface $em
     * @return json $this
     */
    #[Route('/api/personne/{id}', name: 'app_api_personne_show', methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $em)
    {
        $personne = $em->getRepository(Personne::class)->find($id);
        if ($personne) {
            return $this->json($personne, 200, [], ['groups' => 'personne:read']);
        }
        return $this->json([
            'status' => 404,
            'message' => "Aucune correspondance avec l'identifiant $id"
        ]);
    }

    /**
     * Show all adresses of a personne
     * @param int $id
     * @param EntityManagerInterface $em
     * @return json $this
     */
    #[Route('/api/personne/{id}/adresse', name: 'app_api_personne_show', methods: ['GET'])]
    public function showAdresses(int $id, EntityManagerInterface $em)
    {
        $personne = $em->getRepository(Personne::class)->find($id);
        if ($personne) {
            return $this->json($personne->getAdresses(), 200, [], ['groups' => 'personne:read']);
        }
        return $this->json([
            'status' => 404,
            'message' => "Aucune correspondance avec l'identifiant $id"
        ]);
    }

    /**
     * Show Only One Adresse of a personne By id
     * @param int $id
     * @param EntityManagerInterface $em
     * @return json $this
     */
    #[Route('/api/personne/{idPersonne}/adresse/{idAdresse}', name: 'app_api_personne_show_details', methods: ['GET'])]
    public function showOneAdresse(int $idPersonne, int $idAdresse, EntityManagerInterface $em)
    {
        $personne = $em->getRepository(Personne::class)->find($idPersonne);
        if (!$personne) {
            return $this->json([
                'status' => 404,
                'message' => "Personne inexistante avec l'$idPersonne!"
            ]);
        }
        $adresse = $em->getRepository(Adresse::class)->find($idAdresse);
        if (!$adresse) {
            return $this->json([
                'status' => 404,
                'message' => "Adresse inexistante avec l'$idAdresse!"
            ]);
        }
        $ids = array_map(fn ($adr) => $adr->getId(), $personne->getAdresses()->toArray());
        if (in_array($idAdresse, $ids)) {
            return $this->json($adresse, 200, [], ['groups' => 'adresse:read']);
        }
        return $this->json([
            'status' => 404,
            'message' => "L'adresse avec l'id $idAdresse n'appartient pas à la personne avec l'id $idPersonne !"
        ]);
    }
}
