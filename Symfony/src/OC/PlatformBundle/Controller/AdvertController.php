<?php
/**
 * @Author: Mehdi
 * @Date:   2014-11-15 16:33:06
 * @Last Modified by:   Mehdi
 * @Last Modified time: 2014-11-21 23:39:17
 */

//src/OC/PlatformBundle/Controller/AdvertController.php

//=====NAMESPACE=====
namespace OC\PlatformBundle\Controller; //ns des controllers du bundle

//=====USE=====
use Symfony\Bundle\FrameworkBundle\Controller\Controller; //hérite du contrôleur de base de S2
use Symfony\Component\HttpFoundation\Response; //utilis° de l'objet Response avec use
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Entity\Image;
use OC\PlatformBundle\Entity\Application;
use OC\PlatformBundle\Entity\AdvertSkill;
use OC\PlatformBundle\Form\AdvertType;
use OC\PlatformBundle\Form\AdvertEditType;

//=====CLASSE=====
class AdvertController extends Controller{ 

//=====FONCTIONS ROUTES=====
	
	//Route OCPlatformBundle:Advert:viewSlug 
	//avec le path /platform/{year}/{slug}.{format}
	//=> méthode viewSlugAction($slug, $year, $format)	
	public function viewSlugAction($slug, $year, $format){
		return new Response(
			"On affichera l'annonce de notre projet par le slug	
			'".$slug."', créée en ".$year." et au format ".$format."
			"
		);
	}

	/**
	 * vérifie le nb de pages et affiche le template index
	 * @param  [int] $page [le nombre de page à lister]
	 * @return [NotFoundHttpException]
	 * @return [render]       [template]
	 */
	public function indexAction($page){ //méthode appellée par le noyau

		if($page < 1){
			//on balance un 404 pnf
			throw $this->createNotFoundException('La page "'.$page.'" n\'existe pas. ');
		}

		$nbPerPage = 3;

		//Traitement -> récupération de la liste des annonces 
		//qu'on passera au template pour l'afficher
		
		$listAdverts = $this
			->getDoctrine()
			->getManager()
			->getRepository('OCPlatformBundle:Advert')
			->getAdverts($page, $nbPerPage)
		;

		// On calcule le nombre total de pages grâce au count($listAdverts) qui retourne le nombre total d'annonces
    	$nbPages = ceil(count($listAdverts)/$nbPerPage);

    	if($page > $nbPages) {
    		throw $this->createNotFoundException('La page "'.$page.'" n\'existe pas. ');
    	}

		//Affichage du template avec infos nécessaires à la vue
		return $this->render('OCPlatformBundle:Advert:index.html.twig', array(
			'listAdverts' => $listAdverts,
			'nbPages'     => $nbPages,
			'page'        => $page
			)
		);	
	}

	/**
	 * affiche l'annonce correspondant à l'id $id
	 * @param  [int] $id [clé id de l'annonce]
	 * @return [render]     [template de vue]
	 */
	public function viewAction($id)
	{
		$em = $this->getDoctrine()->getManager();

	    // On récupère l'annonce $id
	    $advert = $em
	      ->getRepository('OCPlatformBundle:Advert')
	      ->find($id)
	    ;

	    //Vérifier que l'annonce récupérée existe
	    if( null === $advert ) {
	    	throw $this->createNotFoundException("L'annonce d'id ".$id." n'existe pas.");
	    }

	    $listAdvertSkills = $em->getRepository('OCPlatformBundle:AdvertSkill')->findByAdvert($advert);


		//on récupère la liste des candidatures pour cette annonce
		$listApplications = $em
			->getRepository('OCPlatformBundle:Application')
			->findBy(array('advert' => $advert))
		
;
		return $this
		->render(
			'OCPlatformBundle:Advert:view.html.twig', 
			array(
				'advert'           => $advert,
				'listApplications' => $listApplications,
				'listAdvertSkills' => $listAdvertSkills
			)
		);
		
	}

	/**
	 * ajout d'une annonce
	 * @param Request $request [methode http]
	 */
	public function addAction(Request $request){

		//Création de l'entité Advert
		$advert = new Advert();
		
		// //on crée le formBuilder avec le service form factory de symfony2
		// $formBuilder = $this->get('form.factory')->createBuilder('form', $advert);

		// //on paramètre notre formulaire
		// $formBuilder
		// 	->add('date',      'date')
		// 	->add('title',     'text')
		// 	->add('content',   'textarea')
		// 	->add('author',    'text')
		// 	->add('published', 'checkbox', array('required' => false))
		// 	->add('save',      'submit')
		// ;

		// //on peut créer le formulaire
		// $form = $formBuilder->getForm();
		
		// On peut créer directement notre formulaire réutilisable depuis le dossier Form
		$form = $this->get('form.factory')->create(new AdvertType, $advert);

		//On vérifie que la requete est de type POST
		//On fait le lien requete <-> formulaire
		$form->handleRequest($request);

		//on check que les valeurs entrées dans le formulaire sont correctes
		if ($form->isValid() ) {
			$em = $this->getDoctrine()->getManager();
			$em->persist($advert);
			$em->flush();
		

		$request
			->getSession()
			->getFlashBag()
			->add('notice', 'L\'annonce a bien bien enregistrée.');

		return $this->redirect($this->generateUrl('oc_platform_view', array(
			'id' => $advert->getId() 
			)
		));

		}

		//ici, le formulaire n'est pas valide
		//soit parce que l'user s'est rendu ici à la main, en GET
		//soit c'est du POST mais avec des données non validées

		return $this->render('OCPlatformBundle:Advert:add.html.twig', array(
			'form' => $form->createView(),
			)
		);
	}

	/**
	 * édition d'une annonce
	 * @param  [int]  $id      [clé id de l'annonce]
	 * @param  Request $request 
	 * @return [vue d'édition ou vue de l'annonce]
	 */
	public function editAction($id, Request $request)
	{
		$em = $this->getDoctrine()->getManager();

		//on récupère l'annonce d'id $id
		$advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

		if( null === $advert){ //si l'id $id ne correspond à aucun id d'annonce
			throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
		}

		// //on récupère toutes les catégories avec findAll
		// $listCategories = $em->getRepository('OCPlatformBundle:Category')->findAll();

		// //on boucle sur les catégories pour les lier à cette annonce
		// foreach ($listCategories as $category) {
		// 	$advert->addCategory($category);
		// }

		// //Persistance:
		// // Pour persister le changement dans la relation, 
		// // il faut persister l'entité propriétaire
  //  		// Ici, Advert est le propriétaire, donc 
  //  		// inutile de la persister car on l'a récupérée depuis Doctrine
  //  		// 
   		$listAdvertSkills = $em
			->getRepository('OCPlatformBundle:AdvertSkill')
			->findBy(array('advert' => $advert))
		;
   		
  //  		$em->flush(); //enregistrement
   		
   		$form = $this->get('form.factory')->create(new AdvertEditType, $advert);

   		$form->handleRequest($request);

		//on check que les valeurs entrées dans le formulaire sont correctes
		if ($form->isValid() ) {
			$em = $this->getDoctrine()->getManager();
			$em->persist($advert);
			$em->flush();
		

		$request
			->getSession()
			->getFlashBag()
			->add('notice', 'L\'annonce a bien bien enregistrée.');

		return $this->redirect($this->generateUrl('oc_platform_view', array(
			'id' => $advert->getId() 
			)
		));

		}

		return $this->render('OCPlatformBundle:Advert:edit.html.twig', array(
			'advert'           => $advert,
			'listAdvertSkills' => $listAdvertSkills,
			'form' => $form->createView()
		));
	}

	public function deleteAction($id){ //méthode appellée par le noyau
		
		$em = $this->getDoctrine()->getManager();

		$advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

		if (null === $advert) {
			throw $this->createNotFoundException("L'annonce d'id ".$id." n'existe pas.");
		}

		foreach ($listCategories as $category) {
			$advert->removeCategory($category);
		}

		$em->flush();

		return $this->render('OCPlatformBundle:Advert:delete.html.twig');
	}	

	/**
	 * Récupération des annonces et passage au template pour l'affichage
	 * @return [template] [advert listing]
	 */
	public function menuAction($limit){

		//On créée une liste pour tester, mais à terme on passera par une BDD
		$listAdverts = $this->getDoctrine()
			->getManager()
			->getRepository('OCPlatformBundle:Advert')
			->getAdverts(1,3)
		;

		//le contrôleur passe les variables nécessaires au template
		return $this->render(
			'OCPlatformBundle:Advert:menu.html.twig', 
			array('listAdverts' => $listAdverts)
		);
	}

	public function editImageAction($advertId){
		$em = $this->getDoctrine()->getManager();

		$advert = $em->getRepository('OCPlatformBundle:Advert')->find($advertId);

		$advert->getImage()->setImage('test.png');

		// On n'a pas besoin de persister l'annonce ni l'image.
		// ces entités sont automatiquement persistées car
		// on les a récupérées depuis Doctrine lui-même
		
		$em->flush();

		return new Response("L'image a bien été modifiée.");
	}

	public function listAction(){

		$listAdverts = $this->getDoctrine()->getManager()
			->getRepository('OCPlatformBundle:Advert')
			->getAdvertWithApplications()
		;

		foreach ($listAdverts as $advert) {
			$advert->getApplications();
		}
	}
}


//Route OCPlatformBundle:Advert:view avec path /platform/advert/{id}
	//=> besoin d'une méthode viewAction($id)
	// public function viewAction($id, Request $request){
		// $tag = $request->query->get('tag');
		// return new Response(
		// 	"Affichage de l'annonce d'id : ".$id.", avec le tag : ".$tag." ."
		// );
		
		// $tag = $request->query->get('tag');
		// return $this
		// 			->get('templating')
		// 			->renderResponse(
  //     					'OCPlatformBundle:Advert:view.html.twig',
  //     					array('id'  => $id, 'tag' => $tag)
  //   			);
    			
    	// $url = $this->get('router')->generate('oc_platform_add');
    	// return $this->redirect($url);
    	
    	// $session = $request->getSession();
    	// $userId = $session->get('user_id');
    	// $session->set('user_id', 91);
    	// return new Response('<body>TEst</body>');
	// }