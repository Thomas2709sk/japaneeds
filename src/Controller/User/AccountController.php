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
        // get User
        $user = $this->getUser();


        if (!$user instanceof Users) {
            throw new \LogicException('The user is not of type Users.');
        }

        // Create form
        $accountForm = $this->createForm(EditAccountFormType::class, $user);

        $accountForm->handleRequest($request);

        // if form is valid
        if ($accountForm->isSubmitted() && $accountForm->isValid()) {
            // upload picture for user profile picture
            $featuredImage = $accountForm->get('picture')->getData();

            //  if picture is not null
            if ($featuredImage !== null) {

                if (!$featuredImage instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
                    throw new \LogicException('Uploaded file is not an instance of UploadedFile.');
                }

                $image = $pictureService->square($featuredImage, 'users', 40);
                $user->setPicture($image);
            }

            // Get user role
            $role = $accountForm->get('role')->getData();

            if ($role === 'guide') {
                // if User choose guide in the account form
                $guide = $em->getRepository(Guides::class)->findOneBy(['user' => $user]);
                if (!$guide) {
                    // Create new object guide
                    $guide = new Guides();
                    // User get a guide ID
                    $guide->setUser($user);
                    $em->persist($guide);
                }
            } elseif ($role === 'voyageur') {
                // If User is a guide and chosse 'voyageur'
                $guide = $em->getRepository(Guides::class)->findOneBy(['user' => $user]);
                // Remove guide ID 
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
