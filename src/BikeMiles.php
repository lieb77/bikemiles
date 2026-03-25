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
	
	
	/**
	 * Update a bike with miles from a new ride
	 *
	 */	
	public function updateBikeMiles($entity): void {
		$newMilesValue = $entity->get('field_miles')->value;
		if (empty($newMilesValue)) {
			$this->logger->error("Bail: Node @id has no mileage value.", ['@id' => $entity->id()]);
			return;
		}		
		$newMiles = (int) $newMilesValue;
		
		$original_entity = $entity->original ?? NULL;
		if ($original_entity) {
			$oldMilesValue = $original_entity->get('field_miles')->value;		
			if (empty($oldMilesValue)) {
				$this->logger->critical("Bail: Original version of Node @id missing mileage.", ['@id' => $entity->id()]);
				return;
			}
		
			$oldMiles = (int) $oldMilesValue;
			$delta = $newMiles - $oldMiles;
		} 
		else {
			$delta = $newMiles;
		}

		if ($delta === 0) {
			return;
		}
		
		$bikeTarget = $entity->get('field_bike')->target_id;
		if (!$bikeTarget) {
			return; // No bike associated with this ride.
		}

		$storage  = $this->entityTypeManager->getStorage('node');
		$bikeNode = $storage->load($bikeTarget);
		$currentMileage = (int) $bikeNode->get('field_mileage')->value;
		$bikeNode->set('field_mileage', $currentMileage + $delta);
		
		// Use setSyncing() to tell other hooks/ECA 
		// that this is an automated update and to stay quiet.
		$bikeNode->setSyncing(TRUE);
		$bikeNode->save();
			
		$this->logger->notice('Bicycle @bike (@nid) updated. Delta: @delta. Total: @total', [
			'@bike'  => $bikeNode->label(),
			'@nid'   => $bikeNode->id(),
			'@delta' => $delta,
			'@total' => $currentMileage + $delta,
		]);
 
	}
	
	
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