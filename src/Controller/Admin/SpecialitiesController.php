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
        // get all the specialities from the table
        $specialities = $specialitiesRepository->findBy([], ['name' => 'ASC']);

        return $this->render('admin/specialities/index.html.twig', [
            'specialities' => $specialities
        ]);
    }

    #[Route('/ajouter', name: 'add')]
    public function addCategories(Request $request, SluggerInterface $slugger, EntityManagerInterface $em): Response
    {   
        // create new object specialities
        $speciality = new Specialities();

        // Create form
        $specialityForm = $this->createForm(AddSpecialityFormType::class, $speciality);

        // handle form
        $specialityForm->handleRequest($request);

        // if form is valid
        if($specialityForm->isSubmitted() && $specialityForm->isValid()){
            // create slug
            $slug = strtolower($slugger->slug($speciality->getName()));

            $speciality->setSlug($slug);

            $em->persist($speciality);
            $em->flush();

            $this->addFlash('success', 'La spécialité a été ajouté');
            return $this->redirectToRoute('app_admin_specialities_index');
        }

        return $this->render('admin/specialities/add.html.twig', [
            'specialityForm' => $specialityForm->createView(),
        ]);
    }
}
