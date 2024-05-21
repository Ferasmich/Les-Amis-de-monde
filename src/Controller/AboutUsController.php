<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\AboutUsRepository;
use App\Form\AboutType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;


class AboutUsController extends AbstractController
{
    #[Route('/about_us', name: 'about_us')]
    public function index(AboutUsRepository $aboutUsRepository): Response
    {
        $aboutUs = $aboutUsRepository->findBy(['type' => 'section']);;
        $aboutUsImage = $aboutUsRepository->findBy(['type' => 'about-us-image']);;



        return $this->render('about-us/index.html.twig', [
            'controller_name' => 'AboutUsController',
            'aboutUs' => $aboutUs,
            'aboutUsImage' => $aboutUsImage,

        ]);
    }
    #[Route('/about_us/tab', name: 'tab_about_us')]
    public function aboutUsTab(AboutUsRepository $aboutUsRepository): Response
    {
        $aboutUs = $aboutUsRepository->findAll();;



        return $this->render('about-us/tabBord.html.twig', [
            'controller_name' => 'AboutUsController',
            'aboutUs' => $aboutUs,

        ]);
    }


    #DELETE

    #[Route("/delete/aboutus/{id}", name: "delete_aboutus")]
    public function delete(EntityManagerInterface $em, AboutUsRepository $aboutUsRepository, int $id)
    {
        $action = $aboutUsRepository->FindOneBy(["id" => $id]);
        $em->remove($action);
        $em->flush();
        return $this->redirectToRoute("app_user_index");
    }


    #UPDATE
    #[Route("/aboutus/{id}", name: "update_aboutUs")]
    public function update(int $id, AboutUsRepository $aboutUsRepository, Request $request, EntityManagerInterface $em)
    {
        $aboutUs = $aboutUsRepository->findOneBy(["id" => $id]);

        $form = $this->createForm(AboutType::class, $aboutUs, ["label" => "modifier"]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($aboutUs);
            $em->flush();
            return $this->redirectToRoute("app_user_index");
        }

        return $this->render("about-us/update.html.twig", [
            "aboutUs" => $aboutUs,
            "form" => $form,
        ]);
    }
}
