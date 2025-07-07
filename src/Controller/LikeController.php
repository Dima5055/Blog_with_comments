<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Post;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Like;
final class LikeController extends AbstractController
{
    #[Route('post/{id}/like', name: 'app_like')]
    #[IsGranted('ROLE_USER')]
    public function like(Post $post, EntityManagerInterface $entityManager): RedirectResponse
    {
        $user = $this->getUser();

        // Проверяем существование лайка
        $existingLike = null;
        foreach ($post->getLikes() as $like) {
            if ($like->getUser() === $user) {
                $existingLike = $like;
                break;
            }
        }

        if ($existingLike) {
            $entityManager->remove($existingLike);
        } else {
            $like = new Like();
            $like->setPost($post);
            $like->setUser($user);
            $like->setCreatedAt(new \DateTime());
            $entityManager->persist($like);
        }

        $entityManager->flush();
        return $this->redirectToRoute('app_main');
    }
}
