<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Post;
use App\Form\PostForm;
use App\Entity\Comments;
use App\Form\CommentsForm;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\FileUploader;

final class PostController extends AbstractController
{
    #[Route('/post/create', name: 'app_post_create')]
    public function create(Request $request, EntityManagerInterface $entityManager, FileUploader $fileUploader): Response
    {
        $post = new Post();
        $form = $this->createForm(PostForm::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $fileName = $fileUploader->upload($imageFile);
                $post->setImage($fileName);
            }
            $post->setPublicationDate(new \DateTime());
            $entityManager->persist($post);
            $entityManager->flush();
        }

        return $this->render('pages/post_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/post/{id}', name: 'post_detail')]
    public function show(Post $post,  Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $text = $request->request->get('text');
            $parentId = $request->request->get('parent');

            if ($text && $this->getUser()) {
                $comment = new Comments();
                $comment->setText($text);
                $comment->setAuthor($this->getUser());
                $comment->setPost($post);

                if ($parentId) {
                    $parent = $entityManager->getRepository(Comments::class)->find($parentId);
                    $comment->setParent($parent);
                }

                $entityManager->persist($comment);
                $entityManager->flush();

                $this->addFlash('success', 'Комментарий добавлен!');
                return $this->redirectToRoute('post_detail', ['id' => $post->getId()]);
            }
        }
        $replyTo = $request->query->get('reply_to');

        return $this->render('pages/detail_post.html.twig', [
            'post' => $post,
            'replyTo' => $replyTo
        ]);
    }

}
