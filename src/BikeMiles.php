<?php

namespace Drupal\bikemiles;

use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

class BikeMiles{

    protected $bikes = [];
    
	// constructor initializes database query
	public function __construct(
		protected EntityTypeManagerInterface $entityTypeManager,
		protected LoggerChannelInterface $logger,
	 ) {}
	
	
	public function setMiles() {
		$this->logger->notice("Starting setMiles");
		$this->getRides();
		$this->setBikes();
		$this->logger->notice("Finished setMiles");

	}
	
	public function getRides() {
		$storage = $this->entityTypeManager->getStorage('node');

		$nids = $storage->getQuery()
			->accessCheck(TRUE)
			->condition('type', 'ride')
			->execute();
		
		$nodes = $storage->loadMultiple($nids);
		
		
		// loop through the results
		foreach ($nodes as $nid => $ride) {			
			// Get the bike
			$value = $ride->get('field_bike')->getValue();
			$bikeNid = $value[0]['target_id'];
			
			
			// Get the miles
			$value = $ride->get('field_miles')->getValue();
			$miles = $value[0]['value'];
			if (!isset($this->bikes[$bikeNid])){
				$this->bikes[$bikeNid] = 0;
			}
			$this->bikes[$bikeNid] += $miles;
		}
		
	}
	
	public function setBikes() {
		$storage = $this->entityTypeManager->getStorage('node');

		$nids = $storage->getQuery()
			->accessCheck(TRUE)
			->condition('type', 'bicycle')
			->execute();
		
		$nodes = $storage->loadMultiple($nids);
		// loop through the results
		foreach ($nodes as $nid => $bike) {				
			$bike->set('field_mileage', $this->bikes[$nid]);
			$bike->save();
		}
	}
	
	public function getBikes() {
		$storage = $this->entityTypeManager->getStorage('node');

		$nids = $storage->getQuery()
			->accessCheck(TRUE)
			->condition('type', 'bicycle')
			->sort('field_mileage', 'DESC')
			->execute();
		
		$nodes = $storage->loadMultiple($nids);
		
		$bikes = [];
		// loop through the results
		foreach ($nodes as $nid => $bike) {				
			$title = $bike->getTitle();
			$miles = $bike->get('field_mileage')->value;
			$bikes[$title] = $miles;
		}
		return $bikes;
	}
	
	
}