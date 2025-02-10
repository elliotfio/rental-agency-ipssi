<?php

namespace App\Controller;

use App\Entity\Vehicle;
use App\Entity\Comment;
use App\Form\VehicleType;
use App\Form\CommentType;
use App\Form\VehicleFilterType;
use App\Repository\VehicleRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/vehicle')]
class VehicleController extends AbstractController
{
    #[Route('/', name: 'vehicle_index', methods: ['GET'])]
    public function index(VehicleRepository $vehicleRepository, Request $request): Response
    {
        $form = $this->createForm(VehicleFilterType::class);
        $form->handleRequest($request);

        $criteria = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if (!empty($data['brand'])) {
                $criteria['brand'] = $data['brand'];
            }

            if (!empty($data['maxPrice'])) {
                $criteria['dailyPrice'] = $data['maxPrice'];
            }

            if ($data['availability'] !== null) {
                $criteria['available'] = $data['availability'];
            }
        }

        $vehicles = $vehicleRepository->findByCriteria($criteria);

        return $this->render('vehicle/index.html.twig', [
            'vehicles' => $vehicles,
            'filterForm' => $form->createView(),
        ]);
    }

    #[Route('/new', name: 'vehicle_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $vehicle = new Vehicle();
        $form = $this->createForm(VehicleType::class, $vehicle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($vehicle->getDailyPrice() < 100 || $vehicle->getDailyPrice() > 500) {
                $this->addFlash('danger', 'Le prix doit être entre 100 et 500 €.');
                return $this->redirectToRoute('vehicle_new');
            }

            $entityManager->persist($vehicle);
            $entityManager->flush();

            return $this->redirectToRoute('vehicle_index');
        }

        return $this->render('vehicle/new.html.twig', [
            'vehicle' => $vehicle,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'vehicle_show', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function show(
        VehicleRepository $vehicleRepository,
        Request $request,
        EntityManagerInterface $entityManager,
        ReservationRepository $reservationRepository,
        int $id
    ): Response {
        $vehicle = $vehicleRepository->find($id);

        if (!$vehicle) {
            throw $this->createNotFoundException('Véhicule introuvable.');
        }

        $hasRented = $reservationRepository->findOneBy([
            'customer' => $this->getUser(),
            'vehicle' => $vehicle
        ]);

        $form = $this->createForm(CommentType::class, new Comment());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $hasRented) {
            $comment = $form->getData();
            $comment->setVehicle($vehicle);
            $comment->setCustomer($this->getUser());
            $comment->setCreatedAt(new \DateTime());

            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash('success', 'Commentaire ajouté avec succès.');
            return $this->redirectToRoute('vehicle_show', ['id' => $vehicle->getId()]);
        }

        return $this->render('vehicle/show.html.twig', [
            'vehicle' => $vehicle,
            'commentForm' => $form->createView(),
            'canComment' => $hasRented !== null,
            'similarVehicles' => $vehicleRepository->findSimilarVehicles($vehicle)
        ]);
    }

    #[Route('/{id}/edit', name: 'vehicle_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, VehicleRepository $vehicleRepository, EntityManagerInterface $entityManager, int $id): Response
    {
        $vehicle = $vehicleRepository->find($id);

        if (!$vehicle) {
            throw $this->createNotFoundException('Véhicule introuvable.');
        }

        $form = $this->createForm(VehicleType::class, $vehicle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($vehicle->getDailyPrice() < 100 || $vehicle->getDailyPrice() > 500) {
                $this->addFlash('danger', 'Le prix doit être entre 100 et 500 €.');
                return $this->redirectToRoute('vehicle_edit', ['id' => $id]);
            }

            $entityManager->flush();

            return $this->redirectToRoute('vehicle_index');
        }

        return $this->render('vehicle/edit.html.twig', [
            'vehicle' => $vehicle,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'vehicle_delete', methods: ['POST'])]
    public function delete(Request $request, VehicleRepository $vehicleRepository, EntityManagerInterface $entityManager, int $id): Response
    {
        $vehicle = $vehicleRepository->find($id);

        if (!$vehicle) {
            throw $this->createNotFoundException('Véhicule introuvable.');
        }

        if (!$vehicle->getComments()->isEmpty()) {
            $this->addFlash('danger', 'Impossible de supprimer ce véhicule car il a des commentaires.');
            return $this->redirectToRoute('vehicle_index');
        }

        if ($this->isCsrfTokenValid('delete' . $vehicle->getId(), $request->request->get('_token'))) {
            $entityManager->remove($vehicle);
            $entityManager->flush();
        }

        return $this->redirectToRoute('vehicle_index');
    }

    #[Route('/{id}/favorite', name: 'vehicle_add_favorite', methods: ['POST'])]
    public function addFavorite(Vehicle $vehicle, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException("Vous devez être connecté pour ajouter un favori.");
        }
    
        $user->addFavorite($vehicle);
        $entityManager->persist($user); 
        $entityManager->flush();
    
        return $this->redirectToRoute('vehicle_show', ['id' => $vehicle->getId()]);
    }
    
    #[Route('/{id}/favorite/remove', name: 'vehicle_remove_favorite', methods: ['POST'])]
    public function removeFavorite(Vehicle $vehicle, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException("Vous devez être connecté pour retirer un favori.");
        }
    
        $user->removeFavorite($vehicle);
        $entityManager->persist($user);
        $entityManager->flush();
    
        return $this->redirectToRoute('vehicle_show', ['id' => $vehicle->getId()]);
    }
}