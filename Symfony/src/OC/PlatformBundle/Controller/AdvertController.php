<?php
/**
 * @Author: Mehdi
 * @Date:   2014-11-15 16:33:06
 * @Last Modified by:   Mehdi
 * @Last Modified time: 2014-11-18 20:49:54
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
			throw new NotFoundHttpException('Page "'.$page.'" inexistante. ');
		}

		//Traitement -> récupération de la liste des annonces 
		//qu'on passera au template pour l'afficher
		
		$listAdverts = array(
	      array(
	        'title'   => 'Recherche développpeur Symfony2',
	        'id'      => 1,
	        'author'  => 'Alexandre',
	        'content' => 'Nous recherchons un développeur Symfony2 débutant sur Lyon. Blabla…',
	        'date'    => new \Datetime()),
	      array(
	        'title'   => 'Mission de webmaster',
	        'id'      => 2,
	        'author'  => 'Hugo',
	        'content' => 'Nous recherchons un webmaster capable de maintenir notre site internet. Blabla…',
	        'date'    => new \Datetime()),
	      array(
	        'title'   => 'Offre de stage webdesigner',
	        'id'      => 3,
	        'author'  => 'Mathieu',
	        'content' => 'Nous proposons un poste pour webdesigner. Blabla…',
	        'date'    => new \Datetime())
	    );
		//Affichage du template
		return $this->render('OCPlatformBundle:Advert:index.html.twig', array(
		  'listAdverts' => $listAdverts
		));	}

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

	    $repository = $this
		    ->getDoctrine()
		    ->getManager()
		    ->getRepository('OCPlatformBundle:Advert')
		  ;
		
		//soit $advert est null si $id n'est pas répertorié
		if (null === $advert){
			throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
		}

		//on récupère la liste des candidatures pour cette annonce
		$listApplications = $em
			->getRepository('OCPlatformBundle:Application')
			->findBy(array('advert' => $advert))
		;

		//on récupère la liste des AdvertSkill
		$listAdvertSkills = $em
			->getRepository('OCPlatformBundle:AdvertSkill')
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
		//Paramétrage de l'entité
		$advert->setTitle('Recherche développeur Symfony2.');
		$advert->setAuthor('Alexandre');
		$advert->setContent('Nous recherchons un développeur Symfony2 sur la région de Lyon, blabla...');			
		//la date et la publication ne sont pas définis ici mais dans le constructeur, automatiquement
		
		//Création de l'entité Image associée à l'Advert
		$image = new Image();
		$image->setUrl('http://media.meltycampus.fr/article-1370046-ratio265_610/etudiants-job-de-reve-top-5.jpg');
		$image->setAlt('Dream Job');

		//Il faut lier l'image à l'annonce
		$advert->setImage($image);

		//Création d"une première candidature
		$application1 = new Application();
		$application1->setAuthor('Laure');
		$application1->setContent('J\'utilise Symfony2 depuis 3 ans.');

		//Création d"une deuxième candidature d'exemple
		$application2 = new Application();
		$application2->setAuthor('John');
		$application2->setContent('Embauchez-moi, je suis très motivé.');

		//Il faut lier ces candidatures à l'annonce
		$application1->setAdvert($advert);
		$application2->setAdvert($advert);

		//Ensuite on récupère l'EntityManager
		$doctrine = $this->getDoctrine();
		$em = $doctrine->getManager();

		//On récupère l'ensemble des compétences qui existent en base
		$listSkills = $em->getRepository('OCPlatformBundle:Skill')->findAll();

		foreach ($listSkills as $skill) {
			
			//On crée une nouvelle entité de type
			//"relation entre 1 annonce et 1 compétence"
			$advertSkill = new AdvertSkill();

			//On lie cette entité à l'annonce
			$advertSkill->setAdvert($advert);

			//On la lie à la compétence courante dans cette boucle
			$advertSkill->setSkill($skill);

			//On pose un niveau requis
			$advertSkill->setLevel('Chevalier Jedi du web au minimum');

			//On n'oublie pas de persisté l'entité, 
			//propriétaire dans ses 2 relations 1To1
			$em->persist($advertSkill);
		}

		//Puis on persiste l'entité et on flush ce qui a été persisté avant
		$em->persist($advert); //cette entité est maintenant gérée par Doctrine

		// si on n'avait pas défini le cascade={"persist"},
	    // on devrait persister à la main l'entité $image
	    // $em->persist($image);
	    
	    //pour cette relation avec les candidatures, 
	    //pas de cascade lorsqu'on persiste Advert, car la relation est
    	// définie dans l'entité Application et non Advert. 
    	// On doit donc tout persister à la main ici.
    	$em->persist($application1);
    	$em->persist($application2);
    
		$em->flush(); //cet advert est enregistrée en base de donnée,
					  // et Doctrine2 lui a attribué un id récupérable par getId()

		if($request->isMethod('POST')){

			// $antispam = $this->container->get('oc_platform.antispam'); //récupération du service antispam
			// $text = '...';
			// if($antispam->isSpam($text)){
			// 	throw new \Exception('Votre message a été détecté comme spam !');
			// }

			$request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.'); //message flash: tout est OK

			//Redirection vers la page de visu de cette annonce
			return $this->redirect(
						$this->generateUrl('oc_platform_view', array('id' => $advert->getId())
							)
						);
		}

		//Test if spam fonctionnel
		// $antispam = $this->container->get('oc_platform.antispam'); //récupération du service antispam
		// $text = '...';
		// if($antispam->isSpam($text)){
		// 	throw new \Exception('Votre message a été détecté comme spam !');
		// }
		//si not POST, on affiche le formulaire
		// return $this->render('OCPlatformBundle:Advert:add.html.twig');
		//Redirection vers la page de visu de cette annonce
		return $this->redirect(
					$this->generateUrl('oc_platform_view', array('id' => $advert->getId())
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

		//on récupère toutes les catégories avec findAll
		$listCategories = $em->getRepository('OCPlatformBundle:Category')->findAll();

		//on boucle sur les catégories pour les lier à cette annonce
		foreach ($listCategories as $category) {
			$advert->addCategory($category);
		}

		//Persistance:
		// Pour persister le changement dans la relation, 
		// il faut persister l'entité propriétaire
   		// Ici, Advert est le propriétaire, donc 
   		// inutile de la persister car on l'a récupérée depuis Doctrine
   		
   		$em->flush(); //enregistrement

		return $this->render('OCPlatformBundle:Advert:view.html.twig', array(
		  'advert' => $advert
		));
	}

	public function deleteAction($id){ //méthode appellée par le noyau
		
		$em = $this->getDoctrine()->getManager();

		$advert = $em->getRepository('OCPlatformBundle:Advert')->find('$id');

		if (null === $advert) {
			throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
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
		$listAdverts = array(
			array('id' => 2, 'title' => 'Recherche développeur Symfony2'),			
			array('id' => 5, 'title' => 'Mission de webmaster'),
			array('id' => 9, 'title' => 'Ofre de stage webdesigner')
		);

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