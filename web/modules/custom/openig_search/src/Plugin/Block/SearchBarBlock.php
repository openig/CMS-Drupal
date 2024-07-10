<?php

namespace Drupal\openig_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;


/**
 * Provides a block with search bar
 *
 * @Block(
 *  id = "openig_search_block",
 *  admin_label = @Translation("Search bar block")
 * )
 */
class SearchBarBlock extends BlockBase {

    /**
     * {@inheritDoc}
     */
    public function build() {
        return [
            'form' => \Drupal::formBuilder()->getForm('Drupal\openig_search\Form\SearchBarForm'),
            '#attributes' => [
                'class' => ['site-search'],
            ],
        ];
    }
}