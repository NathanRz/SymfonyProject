<?php

// src/OC/PlatformBundle/Controller/AdvertController.php

namespace OC\PlatformBundle\Controller;
use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Entity\Image;
use OC\PlatformBundle\Entity\Comment;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AdvertController extends Controller
{
  public function indexAction($page)
  {
    if ($page < 1) {
      throw new NotFoundHttpException('Page "'.$page.'" inexistante.');
    }

    $em = $this->getDoctrine()->getManager();
    
    $listAdverts = $em->getRepository('OCPlatformBundle:Advert')->findAll();

   	return $this->render('OCPlatformBundle:Advert:index.html.twig', array('listAdverts' => $listAdverts));
  }

  public function viewAction($id)
  {
    $em = $this->getDoctrine()->getManager();
    
    $advert = $em->getRepository('OCPlatformBundle:Advert')->getAdvertWithComments($id);

    $advert->setContent($advert->getContent() . " !Test edition");

    $em->flush();

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
    $advert->setTitle('Recherche développeur Symfony');
    $advert->setAuthor('Alexandre');
    $advert->setContent("Nous recherchons un développeur Symfony débutant sur Lyon.");

    $image = new Image();
    $image->setUrl('http://sdz-upload.s3.amazonaws.com/prod/upload/job-de-reve.jpg');
    $image->setAlt('Job de rêve');

    $comment1 = new Comment();
    $comment1->setContent("Ca m'interesse !");
    $comment1->setAuthor("Philipe");
    $comment1->setDate(new \DateTime());
    $comment1->setAdvert($advert);

    $comment2 = new Comment();
    $comment2->setContent("Très intéressant");
    $comment2->setAuthor("Dupont");
    $comment2->setDate(new \DateTime());
    $comment2->setAdvert($advert);

    $advert->setImage($image);

    $em = $this->getDoctrine()->getManager();

    $em->persist($advert);
    $em->persist($comment1);
    $em->persist($comment2);
    $em->flush();

    // La gestion d'un formulaire est particulière, mais l'idée est la suivante :

    // Si la requête est en POST, c'est que le visiteur a soumis le formulaire
    if ($request->isMethod('POST')) {
      // Ici, on s'occupera de la création et de la gestion du formulaire

      $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');

      // Puis on redirige vers la page de visualisation de cettte annonce
      return $this->redirectToRoute('oc_platform_view', array('id' => 5));
    }

    // Si on n'est pas en POST, alors on affiche le formulaire
    return $this->render('OCPlatformBundle:Advert:add.html.twig');
  }

  public function editAction($id, Request $request)
  {
    $em = $this->getDoctrine()->getManager();

    $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

    $listCategories = $em->getRepository('OCPlatformBundle:Category')->findAll();

    foreach ($listCategories as $cat) {
      $advert->addCategory($cat);
    }

    $em->flush();

    return $this->render('OCPlatformBundle:Advert:edit.html.twig', array(
      'advert' => $advert
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