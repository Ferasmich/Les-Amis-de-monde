<?php

namespace App\Controller;

use App\Repository\ActionRepository;
use App\Repository\NewzRepository;
use App\Repository\ArticleRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\AboutUsRepository;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(NewzRepository $newzRepository, ArticleRepository $articleRepository, AboutUsRepository $aboutUsRepository, ActionRepository $actionRepository): Response
    {
        $newzs = $newzRepository->findAll();
        $articles = $articleRepository->findAll();
        $actions = $actionRepository->findAll();
        $aboutUs = $aboutUsRepository->findBy(['type' => 'section']);;
        $aboutUsImage = $aboutUsRepository->findBy(['type' => 'accueil-image']);;
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'newzs' => $newzs,
            'articles' => $articles,
            'actions' => $actions,
            'aboutUs' => $aboutUs,
            'aboutUsImage' => $aboutUsImage,

        ]);
    }
}
