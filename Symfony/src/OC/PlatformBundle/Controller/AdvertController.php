<?php
/**
 * @Author: Mehdi
 * @Date:   2014-11-15 16:33:06
 * @Last Modified by:   Mehdi
 * @Last Modified time: 2014-11-15 16:51:01
 */

//src/OC/PlatformBundle/Controller/AdvertController.php

namespace OC\PlatformBundle\Controller; //ns des controllers du bundle

use Symfony\Bundle\FrameworkBundle\Controller\Controller; //hérite du contrôleur de base de S2
use Symfony\Component\HttpFoundation\Response; //utilis° de l'objet Response avec use

class AdvertController extends Controller{ 

	public function indexAction(){ //méthode appellée par le noyau
		$content = $this->get('templating')->render('OCPlatformBundle:Advert:index.html.twig', array('nom' => 'Mehdi'));
		return new Response($content);
	}
}