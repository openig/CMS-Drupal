<?php

namespace Drupal\openig_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\openig_search\Controller\SearchController;
use Drupal\openig_search\SearchQueryService;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Provides a block with search bar
 *
 * @Block(
 *  id = "openig_search_external_filter_block",
 *  admin_label = @Translation("Search external filter block")
 * )
 */
class SearchExternalFilterBlock extends BlockBase implements ContainerFactoryPluginInterface {
//class SearchExternalFilterBlock extends BlockBase {


    private $searchQueryService;

    public function __construct(
      array $configuration,
      $plugin_id,
      $plugin_definition,
      SearchQueryService $searchQueryService)
    {
      parent::__construct($configuration, $plugin_id, $plugin_definition);
      $this->searchQueryService = $searchQueryService;
    }

    public static function create(ContainerInterface $container,$configuration, $plugin_id, $plugin_definition)
    {
      return new static(
        $configuration,
        $plugin_id,
        $plugin_definition,
        $container->get('openig_search.search_query_service')
      );
    }


    /**
     * {@inheritDoc}
     */
    public function build() {

      $controller_search = new SearchController($this->searchQueryService);
      $rendering_in_block = $controller_search->filter_search_results();
      dump($rendering_in_block['#filters']);
      return [
        '#theme'                => 'openig_search_external_filter_block',
        '#filters'              => $rendering_in_block['#filters'],
        '#facets'               => $rendering_in_block['#facets'],
        '#page'                 => $rendering_in_block['#page'],
        '#search_filter_form'   => $rendering_in_block['#search_filter_form'],
        '#attached' => [
          'library' => [
            'openig_search/search',
          ],
        ],
      ];
    }
}
