<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ArticleController extends AbstractController
{
    /**
     * @Route("/article", name="article")
     */
    public function index(ArticleRepository $articleRepository)
    {

        $articles = $articleRepository->findAll();

        return $this->render('article/index.html.twig', [
            'articles' => $articles
        ]);
    }

    /**
     * @Route("/article/show/{id}", name="article_show")
     */
    public function show($id, ArticleRepository $articleRepository)
    {
        
        $article = $articleRepository->find($id);

        if (!$article) {
            throw $this->createNotFoundException("l'article demandé n'existe pas!");
        }

        return $this->render('article/show.html.twig', [
            'article' => $article
        ]);
    }

    /**
     * @Route("/article/edit/{id}", name="article_edit")
     */
    public function edit($id, Request $request, SluggerInterface $slugger, EntityManagerInterface $em, ArticleRepository $articleRepository)
    {
        $article = $articleRepository->find($id);

        if (!$article) {
            throw $this->createNotFoundException("l'article demandé n'existe pas!");
        }

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $article->setSlug(strtolower($slugger->slug($article->getTitle())));

            $em->persist($article);            
            $em->flush();

            return $this->redirectToRoute('article');
        }

        $formView = $form->createView();

        return $this->render('article/edit.html.twig', [
            'formView' => $formView,
            'article' => $article
        ]);
    }

    /**
     * @Route("/article/create", name="article_create")
     */
    public function create(Request $request, SluggerInterface $slugger, EntityManagerInterface $em)
    {
        $article = new Article;

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $article->setSlug(strtolower($slugger->slug($article->getTitle())));
            $article->setCreatedAt(new DateTime());

            $em->persist($article);            
            $em->flush();

            return $this->redirectToRoute('article');
        }

        $formView = $form->createView();

        return $this->render('article/create.html.twig', [
            'formView' => $formView
        ]);
    }

    /**
     * @Route("/article/delete/{id}", name="article_delete")
     */
    public function delete($id, EntityManagerInterface $em, ArticleRepository $articleRepository)
    {
        $article = $articleRepository->find($id);

        if (!$article) {
            throw $this->createNotFoundException("l'article demandé n'existe pas!");
        }

        $em->remove($article);
        $em->flush();

        return $this->redirectToRoute('article');
    }
}