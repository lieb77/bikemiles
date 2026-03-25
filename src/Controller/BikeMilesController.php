<?php

namespace Drupal\bikemiles\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\bikemiles\BikeMiles;

class BikeMilesController extends ControllerBase {


	public function __construct(protected BikeMiles $bikeMiles){}


	public static function create(ContainerInterface $container): static {
		return new static(
		  $container->get('bikemiles.bikemiles'),
		);
	}

	public function tally(){
		$bikes = $this->bikeMiles->getBikes();
		foreach ($bikes as $bike => $miles){
			$rows[] = [$bike, $miles];
		}		
		return [
			'#type'   => 'table',
			'#header' => ['Bike', 'Miles'],
			'#rows'	  => $rows,
		];
	}

}