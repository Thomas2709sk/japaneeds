<?php

namespace App\Controller\Admin;


use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/admin/users', name: 'app_admin_users_')]
class UsersController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(UsersRepository $usersRepository): Response
    {
        $users = $usersRepository->findBy([], ['id' => 'ASC']);

        return $this->render('admin/users/index.html.twig', [
            'users' => $users
        ]);
    }

    #[Route('/remove/{id}', name: 'remove')]
    public function removeUser(int $id, UsersRepository $usersRepository, EntityManagerInterface $em): Response
    {
        // Vérifie si l'utilisateur connecté a le rôle ROLE_ADMIN
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Récupérer l'utilisateur à supprimer
        $user = $usersRepository->find($id);

        // Vérifier si l'utilisateur existe
        if (!$user) {
            $this->addFlash('error', 'Utilisateur introuvable.');
            return $this->redirectToRoute('app_admin_users_index');
        }

        // Supprimer l'utilisateur
        $em->remove($user);
        $em->flush();

        $this->addFlash('success', 'L\'utilisateur a été supprimer avec succès.');

        return $this->redirectToRoute('app_admin_users_index');
    }
}
