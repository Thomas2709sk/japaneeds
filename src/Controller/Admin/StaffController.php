<?php

namespace App\Controller\Admin;

use App\Entity\Users;
use App\Form\AddStaffFormType;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/staff', name: 'app_admin_staff_')]
final class StaffController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(UsersRepository $usersRepository): Response
    {
        $users = $usersRepository->findBy([], ['pseudo' => 'ASC']);


        return $this->render('admin/staff/index.html.twig', [
            'users' => $users
        ]);
    }

    #[Route('/create', name: 'create')]
    public function createStaff(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager): Response
    {
        $user = new Users();
        $form = $this->createForm(AddStaffFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // $user->setRoles(['ROLE_STAFF']);
            $user->setRoles($form->get('roles')->getData());

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            $user->setIsVerified(true);
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Employé inscrit');
            return $this->redirectToRoute('app_admin_staff_index');
        }

        return $this->render('admin/staff/create.html.twig', [
            'staffForm' => $form,
        ]);
    }
}
