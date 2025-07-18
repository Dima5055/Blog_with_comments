<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\PostRepository;

final class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    public function index(PostRepository $postRepository): Response
    {
        $posts = $postRepository->findAllWithLikes();
        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
            'posts' => $posts,
        ]);
    }

    #[Route('/about', name: 'pages_about')]
    public function pages_about(): Response
    {
        return $this->render('pages/about.html.twig');
    }

    #[Route('/rules', name: 'pages_rules')]
    public function pages_rules(): Response
    {
        return $this->render('pages/rules.html.twig');
    }
}
