<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin', name: 'admin_')]
#[IsGranted('ROLE_ADMIN')] 
class AdminController extends AbstractController
{
    #[Route('/', name: 'dashboard')]
    #[Route('/', name: 'dashboard')]
    public function dashboard(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll(); 
    
        return $this->render('admin/index.html.twig', [
            'users' => $users, 
        ]);
    }

    #[Route('/users', name: 'users')]
    public function manageUsers(UserRepository $userRepository): Response
    {
        return $this->render('admin/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/user/{id}/promote', name: 'promote')]
    public function promoteUser(User $user, EntityManagerInterface $entityManager): Response
    {
        $user->setRoles(['ROLE_ADMIN']);
        $entityManager->flush();

        $this->addFlash('success', 'L\'utilisateur a été promu administrateur.');
        return $this->redirectToRoute('admin_users');
    }

    #[Route('/user/{id}/delete', name: 'delete', methods: ['POST'])]
    public function deleteUser(User $user, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('danger', 'L\'utilisateur a été supprimé.');
        return $this->redirectToRoute('admin_users');
    }
}