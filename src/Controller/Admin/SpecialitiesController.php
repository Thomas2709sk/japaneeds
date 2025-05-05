<?php

namespace App\Controller\Admin;

use App\Entity\Specialities;
use App\Form\AddSpecialityFormType;
use App\Repository\SpecialitiesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/specialities', name: 'app_admin_specialities_')]
class SpecialitiesController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(SpecialitiesRepository $specialitiesRepository): Response
    {   
        $specialities = $specialitiesRepository->findBy([], ['name' => 'ASC']);

        return $this->render('admin/specialities/index.html.twig', [
            'specialities' => $specialities
        ]);
    }

    #[Route('/ajouter', name: 'add')]
    public function addCategories(Request $request, SluggerInterface $slugger, EntityManagerInterface $em): Response
    {   
        // On initialise une ville
        $speciality = new Specialities();

        // On initialise le formulaire
        $specialityForm = $this->createForm(AddSpecialityFormType::class, $speciality);

        // On traite le formulaire
        $specialityForm->handleRequest($request);

        // On vérifie si le formulaire est envoyé et valide
        if($specialityForm->isSubmitted() && $specialityForm->isValid()){
            // On crée le slug
            $slug = strtolower($slugger->slug($speciality->getName()));

            // On attribue le slug à notre mot clef
            $speciality->setSlug($slug);

            // On enregistre la ville dans la BDD
            $em->persist($speciality);
            $em->flush();

            $this->addFlash('success', 'La spécialité a été ajouté');
            return $this->redirectToRoute('app_admin_specialities_index');
        }

        // On affiche la vue
        return $this->render('admin/specialities/add.html.twig', [
            'specialityForm' => $specialityForm->createView(),
        ]);
    }
}
