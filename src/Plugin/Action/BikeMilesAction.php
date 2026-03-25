<?php

declare(strict_types=1);

namespace Drupal\bikemiles\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\bikemiles\BikeMiles;


/**
 * Provides a 'Bike Miles' Action.
 *
 * @Action(
 * id = "bikemiles_update",
 * label = @Translation("Updates a bicycle with miles from a new ride."),
 * type = "node"
 * )
 */
final class BikeMilesAction extends ActionBase implements ContainerFactoryPluginInterface {

    public function __construct(
        array $configuration,
        $plugin_id,
        $plugin_definition,
        protected BikeMiles $bikeMiles,
    ) {
        parent::__construct($configuration, $plugin_id, $plugin_definition);
    }

    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
        return new self(
            $configuration,
            $plugin_id,
            $plugin_definition,
            $container->get('bikemiles.bikemiles')
        );
    }
    
    /**
     * {@inheritdoc}
     */
	public function execute($entity = NULL): void {
		if (!$entity instanceof \Drupal\node\NodeInterface || $entity->bundle() !== 'ride') {
			return;
		}
		$this->bikeMiles->updateBikeMiles($entity);
	}

    /**
     * {@inheritdoc}
     */
    public function access($object, ?AccountInterface $account = NULL, $return_as_object = FALSE) {
        $result = AccessResult::allowed();
        return $return_as_object ? $result : $result->isAllowed();
    }
    
}
