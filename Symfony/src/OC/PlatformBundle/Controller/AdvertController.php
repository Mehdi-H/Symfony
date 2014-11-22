<?php
/**
 * @Author: Mehdi
 * @Date:   2014-11-15 16:33:06
 * @Last Modified by:   Mehdi
 * @Last Modified time: 2014-11-22 22:37:07
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
	public function addAction(Request $request)
	{
		$advert = new Advert();
		$form = $this->createForm(new AdvertType(), $advert);

		if ($form->handleRequest($request)->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$em->persist($advert);
			$em->flush();

			$request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');

			return $this->redirect($this->generateUrl('oc_platform_view', array('id' => $advert->getId())));
		}

		// À ce stade :
		// - Soit la requête est de type GET, donc le visiteur vient d'arriver sur la page et veut voir le formulaire
		// - Soit la requête est de type POST, mais le formulaire n'est pas valide, donc on l'affiche de nouveau
		return $this->render('OCPlatformBundle:Advert:add.html.twig', array(
		  'form' => $form->createView(),
		));
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

		// On récupère l'annonce $id
		$advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

		if (null === $advert) {
			throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
		}

		$form = $this->createForm(new AdvertEditType(), $advert);

		if ($form->handleRequest($request)->isValid()) {
			// Inutile de persister ici, Doctrine connait déjà notre annonce
			$em->flush();

			$request->getSession()->getFlashBag()->add('notice', 'Annonce bien modifiée.');

			return $this->redirect($this->generateUrl('oc_platform_view', array('id' => $advert->getId())));
		}

		return $this->render('OCPlatformBundle:Advert:edit.html.twig', array(
		  'form'   => $form->createView(),
		  'advert' => $advert // Je passe également l'annonce à la vue si jamais elle veut l'afficher
		));
	}

	public function deleteAction($id, Request $request)
	{
		$em = $this->getDoctrine()->getManager();

    	// On récupère l'annonce $id
		$advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

		if (null === $advert) {
			throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
		}

	    // On crée un formulaire vide, qui ne contiendra que le champ CSRF
	    // Cela permet de protéger la suppression d'annonce contre cette faille
		$form = $this->createFormBuilder()->getForm();

		if ($form->handleRequest($request)->isValid()) {
			$em->remove($advert);
			$em->flush();

			$request->getSession()->getFlashBag()->add('info', "L'annonce a bien été supprimée.");

			return $this->redirect($this->generateUrl('oc_platform_home'));
		}

    	// Si la requête est en GET, on affiche une page de confirmation avant de supprimer
		return $this->render('OCPlatformBundle:Advert:delete.html.twig', array(
			'advert' => $advert,
			'form'   => $form->createView()
			));
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