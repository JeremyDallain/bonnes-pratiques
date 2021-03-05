<?php

namespace App\Controller;

use DateTime;
use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\UserRepository;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ArticleController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index(ArticleRepository $articleRepository)
    {
        
        // $articles = $articleRepository->findAll();
        $articles = $articleRepository->findByDateBefore(new DateTime());

        return $this->render('article/index.html.twig', [
            'articles' => $articles
        ]);
    }

    
    /**
     * @Route("/article", name="article")
     */
    public function article()
    {
        
        $articles = $this->getUser()->getArticles();

        if (!$articles) {
            throw $this->createNotFoundException("les articles demandés n'existe pas!");
        }

        // dd($articles);
        
        return $this->render('article/article.html.twig', [
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

        $this->denyAccessUnlessGranted('CAN_EDIT', $article, "Vous n'êtes pas le proprietaire de cet article, vous ne pouvez pas l'éditer");
        
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
        
        $user = $this->getUser();
                
        if($form->isSubmitted() && $form->isValid()) {
            $article->setSlug(strtolower($slugger->slug($article->getTitle())))
                ->setCreatedAt(new DateTime())
                ->setUser($user);
            
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

        $this->denyAccessUnlessGranted('CAN_DELETE', $article, "Vous n'êtes pas le proprietaire de cet article, vous ne pouvez pas le supprimer");

        $em->remove($article);
        $em->flush();
        
        return $this->redirectToRoute('article');
    }


    /**
     * @Route("/admin", name="admin")
     * @IsGranted("ROLE_ADMIN", message="Vous n'avez pas le droit d'être ici !")
     */
    public function admin()
    {
        return $this->render('home/admin.html.twig');
    }
}