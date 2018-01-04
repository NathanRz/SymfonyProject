<?php

// src/OC/PlatformBundle/Controller/AdvertController.php

namespace OC\PlatformBundle\Controller;
use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Entity\Image;
use OC\PlatformBundle\Entity\Comment;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AdvertController extends Controller
{
  public function indexAction($page)
  {
    if ($page < 1) {
      throw new NotFoundHttpException('Page "'.$page.'" inexistante.');
    }

    $nbPerPages = 3;

    $em = $this->getDoctrine()->getManager();
    
    $listAdverts = $em->getRepository('OCPlatformBundle:Advert')->getAdverts($page,$nbPerPages);

    $nbPages = ceil(count($listAdverts) / $nbPerPages);

    if($page > $nbPages){
      throw $this->createNotFoundException("La page " . $page);
    }

   	return $this->render('OCPlatformBundle:Advert:index.html.twig', array(
      'listAdverts' => $listAdverts,
      'nbPages' => $nbPages,
      'page' => $page
    ));
  }

  public function viewAction($id)
  {
    $em = $this->getDoctrine()->getManager();
    
    $advert = $em->getRepository('OCPlatformBundle:Advert')->getAdvertWithComments($id);

    if(null === $advert){
      throw new NotFoundHttpException("L'annonce d'id " .$id. " n'existe pas.");
    }

    return $this->render('OCPlatformBundle:Advert:view.html.twig', array(
      'advert' => $advert,
      'listComments' => $advert->getComments()
    ));
  }

  public function addAction(Request $request)
  {

    $advert = new Advert();
    $advert->setDate(new \Datetime());

    $formBuilder = $this->get('form.factory')->createBuilder(FormType::class, $advert);

    $formBuilder
      ->add('date',     DateType::class)
      ->add('title',    TextType::class)
      ->add('content',  TextareaType::class)
      ->add('author',   TextType::class)
      ->add('published',CheckboxType::class, array('required' => false))
      ->add('save',     SubmitType::class);


    $form = $formBuilder->getForm();

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
    $formBuilder = $this->get('form.factory')->createBuilder(FormType::class, $advert);

    $formBuilder
      ->add('date',     DateType::class)
      ->add('title',    TextType::class)
      ->add('content',  TextareaType::class)
      ->add('author',   TextType::class)
      ->add('published',CheckboxType::class, array('required' => false))
      ->add('save',     SubmitType::class);


    $form = $formBuilder->getForm();

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

    if(empty($advert->getCategories())){
      $listCategories = $em->getRepository('OCPlatformBundle:Category')->findAll();

      foreach ($listCategories as $cat) {
        $advert->addCategory($cat);
      }
      $em->flush();
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
    // On fixe en dur une liste ici, bien entendu par la suite
    // on la récupérera depuis la BDD !
    $listAdverts = array(
      array('id' => 2, 'title' => 'Recherche développeur Symfony'),
      array('id' => 5, 'title' => 'Mission de webmaster'),
      array('id' => 9, 'title' => 'Offre de stage webdesigner')
    );

    return $this->render('OCPlatformBundle:Advert:menu.html.twig', array(
      // Tout l'intérêt est ici : le contrôleur passe
      // les variables nécessaires au template !
      'listAdverts' => $listAdverts
    ));
  }
}