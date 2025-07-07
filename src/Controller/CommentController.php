<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Comments;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

final class CommentController extends AbstractController
{
    #[Route('/comment/admin/delete/{id}', name: 'app_comment')]
    public function delete(Request $request, Comments $comment, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('delete'.$comment->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Неверный токен безопасности');
            return $this->redirectToRoute('post_detail', ['id' => $comment->getPost()->getId()]);
        }

        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'У вас нет прав для удаления комментариев');
            return $this->redirectToRoute('post_detail', ['id' => $comment->getPost()->getId()]);
        }

        $comment->setIsDeleted(true);
        $entityManager->flush();

        $this->addFlash('success', 'Комментарий успешно удален');
        return $this->redirectToRoute('post_detail', ['id' => $comment->getPost()->getId()]);
    }
}
