<?php

namespace App\Controller\User;

use App\Entity\Users;
use App\Entity\Guides;
use App\Entity\Reservations;
use App\Entity\Reviews;
use App\Repository\UsersRepository;
use App\Repository\ReservationsRepository;
use App\Form\EditAccountFormType;
use App\Service\PictureService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user/account', name: 'app_user_account_')]
class AccountController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(Request $request, EntityManagerInterface $em, PictureService $pictureService): Response
    {
        // Récupérer l'utilisateur actuellement connecté
        $user = $this->getUser();

        // Vérifiez si l'utilisateur est bien une instance de Users
        if (!$user instanceof Users) {
            throw new \LogicException('The user is not of type Users.');
        }

        $accountForm = $this->createForm(EditAccountFormType::class, $user);

        $accountForm->handleRequest($request);

        // On vérifie si le formulaire est valide
        if ($accountForm->isSubmitted() && $accountForm->isValid()) {
            // Gérer l'upload de l'image si un fichier a été téléchargé
            $featuredImage = $accountForm->get('picture')->getData();

            if ($featuredImage !== null) {
                // Assurez-vous que $featuredImage est une instance de UploadedFile
                if (!$featuredImage instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
                    throw new \LogicException('Uploaded file is not an instance of UploadedFile.');
                }

                $image = $pictureService->square($featuredImage, 'users', 40);
                $user->setPicture($image);
            }

            // Gérer le rôle de l'utilisateur
            $role = $accountForm->get('role')->getData();

            if ($role === 'guide') {
                // Ajouter l'utilisateur à la table Guides s'il choisit 'guide'
                $guide = $em->getRepository(Guides::class)->findOneBy(['user' => $user]);
                if (!$guide) {
                    $guide = new Guides();
                    $guide->setUser($user);
                    $em->persist($guide);
                }
            } elseif ($role === 'voyageur') {
                // Supprimer l'utilisateur de la table Guides s'il choisit 'voyageur'
                $guide = $em->getRepository(Guides::class)->findOneBy(['user' => $user]);
                if ($guide) {
                    $em->remove($guide);
                }
            }


            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Votre profil a été mis à jour.');

            return $this->redirectToRoute('app_user_account_index');
        }


        return $this->render('user/account/index.html.twig', [
            'accountForm' => $accountForm->createView(),
        ]);
    }
}
