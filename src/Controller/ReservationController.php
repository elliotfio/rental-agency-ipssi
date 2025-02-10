<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Vehicle;
use App\Form\ReservationType;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/reservation')]
final class ReservationController extends AbstractController
{
    #[Route(name: 'reservation_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')] 
    public function index(ReservationRepository $reservationRepository): Response
    {
        $reservations = $reservationRepository->findBy(['customer' => $this->getUser()]);

        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    #[Route('/new/{vehicleId}', name: 'reservation_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    #[Route('/new/{vehicleId}', name: 'reservation_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(int $vehicleId, Request $request, EntityManagerInterface $entityManager): Response
    {
        $vehicle = $entityManager->getRepository(Vehicle::class)->find($vehicleId);
        if (!$vehicle) {
            throw $this->createNotFoundException("Le véhicule demandé n'existe pas.");
        }
    
        $reservation = new Reservation();
        $reservation->setCustomer($this->getUser());
        $reservation->setVehicle($vehicle);
    
        $totalPrice = 0;
    
        if ($request->isMethod('POST')) {
            $startDate = new \DateTime($request->request->get('startDate'));
            $endDate = new \DateTime($request->request->get('endDate'));
    
            if ($endDate < $startDate) {
                $this->addFlash('danger', 'La date de fin doit être après la date de début.');
                return $this->redirectToRoute('reservation_new', ['vehicleId' => $vehicleId]);
            }
    
            $reservation->setStartDate($startDate);
            $reservation->setEndDate($endDate);
    
            $days = max(1, $startDate->diff($endDate)->days);
            $totalPrice = $days * $vehicle->getDailyPrice();
            $reservation->setTotalPrice($totalPrice);
    
            $entityManager->persist($reservation);
            $entityManager->flush();
    
            $this->addFlash('success', 'Réservation confirmée avec succès.');
            return $this->redirectToRoute('reservation_index');
        }
    
        return $this->render('reservation/new.html.twig', [
            'reservation' => $reservation,
            'vehicle' => $vehicle,
            'totalPrice' => $totalPrice
        ]);
    }

    #[Route('/{id}/validate', name: 'reservation_validate', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')] 
    public function validate(Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        $reservation->getVehicle()->setAvailable(false);
        $entityManager->flush();

        $this->addFlash('success', 'Réservation validée avec succès.');

        return $this->redirectToRoute('reservation_index');
    }

    #[Route('/{id}', name: 'reservation_show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')] 
    public function show(Reservation $reservation): Response
    {
        if ($this->getUser() !== $reservation->getCustomer()) {
            throw $this->createAccessDeniedException("Accès interdit.");
        }

        return $this->render('reservation/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/{id}/edit', name: 'reservation_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser() !== $reservation->getCustomer()) {
            throw $this->createAccessDeniedException("Accès interdit.");
        }
    
        $form = $this->createForm(ReservationType::class, $reservation, ['include_vehicle' => false]);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $days = max(1, $reservation->getStartDate()->diff($reservation->getEndDate())->days);
            $reservation->setTotalPrice($days * $reservation->getVehicle()->getDailyPrice());
    
            $entityManager->flush();
    
            $this->addFlash('success', 'Réservation mise à jour avec succès.');
    
            return $this->redirectToRoute('reservation_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('reservation/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/cancel', name: 'reservation_cancel', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function cancel(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser() !== $reservation->getCustomer()) {
            throw $this->createAccessDeniedException("Accès interdit.");
        }

        if ($this->isCsrfTokenValid('cancel' . $reservation->getId(), $request->get('_token'))) {
            $reservation->getVehicle()->setAvailable(true);
            $entityManager->remove($reservation);
            $entityManager->flush();

            $this->addFlash('danger', 'Réservation annulée.');
        }

        return $this->redirectToRoute('reservation_index', [], Response::HTTP_SEE_OTHER);
    }
}