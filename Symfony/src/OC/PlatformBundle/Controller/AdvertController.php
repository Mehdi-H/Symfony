<?php
/**
 * @Author: Mehdi
 * @Date:   2014-11-15 16:33:06
 * @Last Modified by:   Mehdi
 * @Last Modified time: 2014-11-15 17:38:50
 */

//src/OC/PlatformBundle/Controller/AdvertController.php

//=====NAMESPACE=====
namespace OC\PlatformBundle\Controller; //ns des controllers du bundle

//=====USE=====
use Symfony\Bundle\FrameworkBundle\Controller\Controller; //hérite du contrôleur de base de S2
use Symfony\Component\HttpFoundation\Response; //utilis° de l'objet Response avec use

//=====CLASS
class AdvertController extends Controller{ 

	//Route OCPlatformBundle:Advert:view avec path /platform/advert/{id}
	//=> besoin d'une méthode viewAction($id)
	public function viewAction($id){
		return new Response("Voici l'annonce d'id : ".$id);
	}

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

	public function indexAction(){ //méthode appellée par le noyau
		$content = $this->get('templating')->render('OCPlatformBundle:Advert:index.html.twig', array('nom' => 'Mehdi'));
		return new Response($content);
	}
}