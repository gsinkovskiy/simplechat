<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use App\Service\PostsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    /**
     * @Route("/", name="post")
     */
    public function index(EntityManagerInterface $entityManager, PostsService $postsService, Request $request)
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setPostedAt(new \DateTime());
            $entityManager->persist($post);
            $entityManager->flush();

            if ($request->isXmlHttpRequest()){
                return $this->postTable($postsService);
            }

            return $this->redirectToRoute('post');
        }

        return $this->render('post/index.html.twig', [
            'form' => $form->createView(),
            'posts'=>$postsService->getPosts(),
        ]);
    }

    public function postTable(PostsService $postsService, PostType $form)
    {
        $postsService->checkPostCount();
        return $this->render('post/postTable.html.twig', [
            'form' => $form,
            'posts' => $postsService->getPosts(),
        ]);
    }

}

//Вариант запроса, удаляющего лишние записи (проверка на то, что записей больше 10, происходит в PHP)
//SET @oldest = (SELECT COUNT(*)-10 FROM post);
//PREPARE STMT FROM 'SELECT * FROM post ORDER BY posted_at LIMIT ?';
//EXECUTE STMT USING @oldest;

