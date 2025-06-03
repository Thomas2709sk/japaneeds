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
        // get all the cities from the table
        $cities = $citiesRepository->findBy([], ['name' => 'ASC']);

        return $this->render('admin/cities/index.html.twig', [
            'cities' => $cities
        ]);
    }


    #[Route('/ajouter', name: 'add')]
    public function addCities(Request $request, SluggerInterface $slugger, EntityManagerInterface $em): Response
    {
        // create new object cities
        $city = new Cities();

        // create form
        $cityForm = $this->createForm(AddCityFormType::class, $city);

        // handle form
        $cityForm->handleRequest($request);

        // if form is valid
        if ($cityForm->isSubmitted() && $cityForm->isValid()) {
            // create slug
            $slug = strtolower($slugger->slug($city->getName()));


            $city->setSlug($slug);

            // save the city on the DB
            $em->persist($city);
            $em->flush();

            $this->addFlash('success', 'La ville a été ajouté');
            return $this->redirectToRoute('app_admin_cities_index');
        }

        return $this->render('admin/cities/add.html.twig', [
            'cityForm' => $cityForm->createView(),
        ]);
    }
}
