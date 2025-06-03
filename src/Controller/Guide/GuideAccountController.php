<?php

namespace App\Controller\Guide;

use App\Controller\Guide\ReservationsController;
use App\Repository\ReservationsRepository;
use App\Entity\Users;
use App\Entity\Guides;
use App\Form\EditGuideFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/guide/account', name: 'app_guide_account_')]
class GuideAccountController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(Request $request, EntityManagerInterface $em, ReservationsRepository $reservationsRepository): Response
    {
        // get User
        $user = $this->getUser();


        if (!$user instanceof Users) {
            throw new \LogicException('The user is not of type Users.');
        }

        // get the guide ID associate with the user ID
        $guide = $em->getRepository(Guides::class)->findOneBy(['user' => $user]);

        // if User don't have a guide ID
        if (!$guide) {
            //  Create new guide object
            $guide = new Guides();
            $guide->setUser($user);
        }

        // Create form
        $accountForm = $this->createForm(EditGuideFormType::class, $guide);

        // handle form request
        $accountForm->handleRequest($request);

        // if form is valid
        if ($accountForm->isSubmitted() && $accountForm->isValid()) {

            $em->persist($guide);
            $em->flush();


            $this->addFlash('success', 'Vos informations de guide ont été mises à jour.');


            return $this->redirectToRoute('app_guide_account_index');
        }

        if ($guide) {
            // get the reservation of the guide
            $reservations = $reservationsRepository->findBy(['guide' => $guide]);
        } else {
            $reservations = [];
        }


        // Retourner la vue avec le formulaire
        return $this->render('guide/guide_account/index.html.twig', [
            'guideForm' => $accountForm->createView(),
            'reservations' => $reservations,
        ]);
    }

}
