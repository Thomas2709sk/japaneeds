<?php

namespace App\Controller\Admin;

use App\Entity\Cities;
use App\Form\AddCityFormType;
use App\Repository\CitiesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/cities', name: 'app_admin_cities_')]
class CitiesController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(CitiesRepository $citiesRepository): Response
    {
        $cities = $citiesRepository->findBy([], ['name' => 'ASC']);

        return $this->render('admin/cities/index.html.twig', [
            'cities' => $cities
        ]);
    }


    #[Route('/ajouter', name: 'add')]
    public function addCities(Request $request, SluggerInterface $slugger, EntityManagerInterface $em): Response
    {
        // On initialise une ville
        $city = new Cities();

        // On initialise le formulaire
        $cityForm = $this->createForm(AddCityFormType::class, $city);

        // On traite le formulaire
        $cityForm->handleRequest($request);

        // On vérifie si le formulaire est envoyé et valide
        if ($cityForm->isSubmitted() && $cityForm->isValid()) {
            // On crée le slug
            $slug = strtolower($slugger->slug($city->getName()));

            // On attribue le slug à notre mot clef
            $city->setSlug($slug);

            // On enregistre la ville dans la BDD
            $em->persist($city);
            $em->flush();

            $this->addFlash('success', 'La ville a été ajouté');
            return $this->redirectToRoute('app_admin_cities_index');
        }

        // On affiche la vue
        return $this->render('admin/cities/add.html.twig', [
            'cityForm' => $cityForm->createView(),
        ]);
    }
}
