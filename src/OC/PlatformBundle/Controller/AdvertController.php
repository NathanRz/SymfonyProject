<?php

// src/OC/PlatformBundle/Controller/AdvertController.php

namespace OC\PlatformBundle\Controller;
use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Entity\Image;
use OC\PlatformBundle\Entity\Comment;
use OC\PlatformBundle\Entity\Category;

use OC\PlatformBundle\Form\AdvertType;
use OC\PlatformBundle\Form\CommentType;
use OC\PlatformBundle\Form\CategoryType;
use OC\PlatformBundle\Form\CategoryAddType;

use Symfony\Component\Form\Extension\Core\Type\FormType;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class AdvertController extends Controller
{
  public function indexAction($page, Request $request)
  {

    if ($page < 1) {
      throw new NotFoundHttpException('Page "'.$page.'" inexistante.');
    }
    $categoryFilter = new Category();
    $form = $this->get('form.factory')->create(CategoryType::class, $categoryFilter);

    $em = $this->getDoctrine()->getManager();

    $nbPerPages = 6;

    if ($request->isMethod('POST')) {
    
      $form->handleRequest($request);

      if($form->isValid()){
        $listAdverts = $em->getRepository('OCPlatformBundle:Advert')->getAdvertWithCategorie($categoryFilter->getName()->getName());

        $nbPages = ceil(count($listAdverts) / $nbPerPages);
      }

      return $this->render('OCPlatformBundle:Advert:index.html.twig', array(
        'listAdverts' => $listAdverts,
        'nbPages' => $nbPages,
        'page' => $page,
        'form' => $form->createView(),
      ));
    }
    
    $listAdverts = $em->getRepository('OCPlatformBundle:Advert')->getAdverts($page,$nbPerPages);

    $nbPages = ceil(count($listAdverts) / $nbPerPages);

    if($page > $nbPages && !count($listAdverts) == 0){
      throw $this->createNotFoundException("La page " . $page);
    }

   	return $this->render('OCPlatformBundle:Advert:index.html.twig', array(
      'listAdverts' => $listAdverts,
      'nbPages' => $nbPages,
      'page' => $page,
      'form' => $form->createView(),
    ));
  }

  public function viewAction($id, Request $request)
  {
    $em = $this->getDoctrine()->getManager();
    
    $advert = $em->getRepository('OCPlatformBundle:Advert')->getAdvertWithComments($id);
    
    $comment = new Comment();
    $comment->setDate(new \Datetime);
    $comment->setAdvert($advert);
    if($this->getUser() !== null ){
      $comment->setAuthor($this->getUser()->getUsername());  
    }

    $form = $this->get('form.factory')->create(CommentType::class, $comment);

    if ($request->isMethod('POST')) {
    
      $form->handleRequest($request);

      if($form->isValid()){
        $em = $this->getDoctrine()->getManager();
        $em->persist($comment);
        $em->flush();
      }
      $request->getSession()->getFlashBag()->add('notice', 'Votre commentaire a été ajouté.');

      return $this->redirectToRoute('oc_platform_view', array('id' => $advert->getId()));
    }

    if(null === $advert){
      throw new NotFoundHttpException("L'annonce d'id " .$id. " n'existe pas.");
    }
   
    return $this->render('OCPlatformBundle:Advert:view.html.twig', array(
      'advert' => $advert,
      'listComments' => $advert->getComments(),
      'form' => $form->createView(),
    ));
  }
  /**
  * @Security("has_role('ROLE_AUTHOR')")
  */
  public function addAction(Request $request)
  {

    $user = $this->getUser();
    $advert = new Advert();
    $advert->setAuthor($this->getUser()->getUsername());
    $advert->setDate(new \Datetime());

    $form = $this->get('form.factory')->create(AdvertType::class, $advert);

    if ($request->isMethod('POST')) {
    
      $form->handleRequest($request);

      if($form->isValid()){
        $em = $this->getDoctrine()->getManager();
        $em->persist($advert);
        $em->flush();
      }
      $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');

      return $this->redirectToRoute('oc_platform_view', array('id' => $advert->getId()));
    }

    return $this->render('OCPlatformBundle:Advert:add.html.twig', array('form' => $form->createView()));
  }

  public function editAction($id, Request $request)
  {
    $em = $this->getDoctrine()->getManager();

    $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);
    $form = $this->get('form.factory')->create(AdvertType::class, $advert);

    if ($request->isMethod('POST')) {
    
      $form->handleRequest($request);

      if($form->isValid()){
        $em = $this->getDoctrine()->getManager();
        $em->persist($advert);
        $em->flush();
      }
      $request->getSession()->getFlashBag()->add('notice', 'Annonce bien modifiée.');

      return $this->redirectToRoute('oc_platform_view', array('id' => $advert->getId()));
    }

    return $this->render('OCPlatformBundle:Advert:edit.html.twig', array(
      'advert' => $advert,
      'form' => $form->createView()
    ));
  }

  public function deleteAction($id)
  {
    
    $em = $this->getDoctrine()->getManager();

    $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

    foreach ($advert->getCategories() as $cat) {
      $advert->removeCategory($cat);
    }

    $em->flush();

    return $this->render('OCPlatformBundle:Advert:delete.html.twig');
  }

  public function menuAction($limit){
    $em = $this->getDoctrine()->getManager();

    $listAdverts = $em->getRepository('OCPlatformBundle:Advert')->getLastXAdverts(3);

    return $this->render('OCPlatformBundle:Advert:menu.html.twig', array(
      // Tout l'intérêt est ici : le contrôleur passe
      // les variables nécessaires au template !
      'listAdverts' => $listAdverts
    ));
  }

  /**
  * @Security("has_role('ROLE_ADMIN')")
  */
  public function adminAction(Request $request){
    $em = $this->getDoctrine()->getManager();
    $category = new Category();
    $categories = $em->getRepository('OCPlatformBundle:Category')->findAll();
    $form = $this->get('form.factory')->create(CategoryAddType::class, $category);

    if ($request->isMethod('POST')) {
    
      $form->handleRequest($request);

      if($form->isValid()){
        $em = $this->getDoctrine()->getManager();
        $em->persist($category);
        $em->flush();
      }
      $request->getSession()->getFlashBag()->add('notice', 'Catégorie bien ajoutée.');

      return $this->redirectToRoute('oc_platform_admin');
    }

    return $this->render('OCPlatformBundle:Advert:admin.html.twig', array(
      'categories' => $categories,
      'form' => $form->createView()
    ));
  }
}