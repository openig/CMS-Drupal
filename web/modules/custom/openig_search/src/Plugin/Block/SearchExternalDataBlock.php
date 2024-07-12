<?php

namespace Drupal\openig_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\openig_search\SearchQueryService;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Provides a block with search bar
 *
 * @Block(
 *  id = "openig_search_external_data_block",
 *  admin_label = @Translation("Search external data block")
 * )
 */
class SearchExternalDataBlock extends BlockBase implements ContainerFactoryPluginInterface {



    private $searchQueryService;

    public function __construct(SearchQueryService $searchQueryService) {
      $this->searchQueryService = $searchQueryService;
    }

    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
      return new static(
        $container->get('openig_search.search_query_service')
      );
    }

    /**
     * {@inheritDoc}
     */
    public function build() {

      $filters = [];
      // Initialize min score
      $min_score = 0;

      // Fulltext search
      $fulltext = \Drupal::request()->query->get('fulltext');
      if ($fulltext) {
        $filters['query'] = $fulltext;
        $min_score = $min_score + 0.1;
      }

      // Content type
      $resource_data_type = \Drupal::request()->query->get('resource_data_type');
      if ($resource_data_type) {
        $filters['resource_data_type'] = $resource_data_type;
        $min_score = $min_score + 100;
      }

      // Source search
      $lineage = \Drupal::request()->query->get('lineage');
      if ($lineage) {
        $filters['lineage'] = count(explode('|', $lineage)) > 1 ? explode('|', $lineage) : $lineage;
        $min_score = $min_score + 100;
      }


      // Category search
      $category = \Drupal::request()->query->get('category');
      if ($category) {
        $filters['category'] = count(explode('|', $category)) > 1 ? explode('|', $category) : $category;
        $min_score = $min_score + 100;
      }

      // Format search
      $resource_format = \Drupal::request()->query->get('resource_format');
      if ($resource_format) {
        $filters['resource_format'] = count(explode('|', $resource_format)) > 1 ? explode('|', $resource_format) : $resource_format;
        $min_score = $min_score + 100;
      }

      // Add min score
      $filters['min_score'] = ($min_score + 0.1) === 0.2 ? 1.2 : ($min_score + 0.1);

      // Get page from request
      $page = \Drupal::request()->query->get('page');

      // Call serach service with current page & facets
      $search = $this->searchQueryService->search($filters, $page ? $page : 0);

      return [
            '#attributes' => [
                'class' => ['site-search'],
            ],
            '#theme'    => 'views_view__block_recherche_externe',
            '#items'    => $search['results'],
            '#facets'   => $search['facets'],
            '#filters'  => $filters,
            '#page'     => $page ? $page : 1,
            '#pager'    => $search['pager'],
            "#url"      => $search['url'],
            "#count"    => $search['count'],
            '#search_bar_form'  => \Drupal::formBuilder()->getForm('Drupal\openig_search\Form\SearchBarForm'),
        ];
    }
}
