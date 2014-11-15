<?php
/**
 * @Author: Mehdi
 * @Date:   2014-11-15 16:33:06
 * @Last Modified by:   Mehdi
 * @Last Modified time: 2014-11-15 20:09:07
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
		
		//Affichage du template
		return $this->render('OCPlatformBundle:Advert:index.html.twig');
	}

	/**
	 * affiche l'annonce correspondant à l'id $id
	 * @param  [int] $id [clé id de l'annonce]
	 * @return [render]     [template de vue]
	 */
	public function viewAction($id){

		return $this->render('OCPlatformBundle:Advert:view.html.twg', array('id' => $id));
	
	}

	/**
	 * ajout d'une annonce
	 * @param Request $request [methode http]
	 */
	public function addAction(Request $request){

		if($request->isMethod('POST')){
			//Traitement: création et gestion du formulaire d'ajout
			//
			$request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.'); //message flash: tout est OK

			//Redirection vers la page de visu de cette annonce
			return $this->redirect($this->generateUrl('oc_platform_view', array('id' => 5)));
		}

		//si not POST, on affiche le formulaire
		return $this->render('OCPlatformBundle:Advert:add.html.twig');
	}

	/**
	 * édition d'une annonce
	 * @param  [int]  $id      [clé id de l'annonce]
	 * @param  Request $request 
	 * @return [vue d'édition ou vue de l'annonce]
	 */
	public function editAction($id, Request $request){ //méthode appellée par le noyau
		//
		//Récupération de l'annonce d'id $id
		//
		
		if($request->isMethod('POST')){

			$request->getSession()->getFlashBag()->add('notice', 'Annonce bien modifiée.');
			return $this->redirect($this->generateUrl('oc_platform_view', array('id' => 5)));
		}

		//Si not POST -> formulaire d'édition
		return $this->render('OCPlatformBundle:Advert:edit.html.twig');
	}

	public function deleteAction($id){ //méthode appellée par le noyau
		//
		//On récupère l'annonce d'id $id à supprimer
		//
		
		//
		//on la supprime
		//
		
		return $this->render('OCPlatformBundle:Advert:delete.html.twig');
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