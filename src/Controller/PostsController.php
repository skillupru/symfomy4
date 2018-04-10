<?php

namespace App\Controller;

use App\Entity\Posts;
use App\Form\AddPostType;
use App\Helpers\FileHelper;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PostsController extends Controller
{
    /**
     * @Route("/posts", name="get_posts")
     */
    public function getPosts(Request $request)
    {
        $search = $request->get('search');
        $em = $this->getDoctrine()->getManager();
        $posts = $em->getRepository('App:Posts')->getFeed($search);

        return $this->render('posts/list.html.twig', [
            'posts' => $posts,
        ]);
    }

    /**
     * @Route("/posts/add", name="add_post")
     */
    public function addPost(Request $request)
    {
        $post = new Posts();
        $form = $this->createForm(AddPostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $filename = $post->getFile()->getClientOriginalName();
            $extension = FileHelper::getExtension($filename);

            // Валидация расширения файла
            if (!in_array($extension, Posts::FILE_EXTENSION)) {
                $form->get('file')->addError(new FormError("Расширение $extension не допустимо для загрузки"));
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();
            $this->addFlash('success', 'Пост успешно добавлен');
        }

        return $this->render('posts/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
