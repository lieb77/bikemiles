<?php

declare(strict_types=1);

namespace Drupal\bikemiles\Hook;


use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\bikemiles\BikeMiles;


/**
 * Implement hooks per Drupal 11 specs.
 */
final class BikeMilesHooks {
	
protected LoggerChannelFactoryInterface $loggerFactory;

	/**
	 *
	 * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
	 *   The logger channel factory.
	 */
	public function __construct(LoggerChannelFactoryInterface $logger_factory) {
		$this->loggerFactory = $logger_factory;
	}


	/**
	 * Implements hook_cron
	 *
	 * Update users email from members database
	 */
	#[Hook('cron')]
	function cron() {
		$this->loggerFactory->get('bikemiles')->info('BikeMiles Cron job started');
		\Drupal::service('bikemiles:bikemiles')->setMiles();		 
	}
	
}

