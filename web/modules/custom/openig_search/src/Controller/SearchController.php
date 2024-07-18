<?php

namespace Drupal\openig_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\openig_search\SearchQueryService;

/**
 * Class SearchController.
 */
class SearchController extends ControllerBase
{

    private $searchQueryService;

    public function __construct(SearchQueryService $searchQueryService)
    {
      $this->searchQueryService = $searchQueryService;
    }

    public static function create(ContainerInterface $container)
    {
      return new static(
        $container->get('openig_search.search_query_service')
      );
    }

    /**
     * Querying, build & show search results
     */
    public function search_results()
    {
        // Initialize facets array
        $filters = [];
        // Initialize min score
        $min_score = 0;

        $session = \Drupal::request()->getSession();

        // Fulltext search - Barre de recherche
        $fulltext = $session->get('searchFulltext');
        $host = \Drupal::request()->server->get('SITE_URL');
        $referer = \Drupal::request()->headers->get('referer');
        $pathReferer = $host . '/recherche?search_api_fulltext=' . $fulltext;
        if ($fulltext && $pathReferer === $referer) {
          $filters['query'] = $fulltext;
          $min_score = $min_score + 0.1;
        }

        // Content type - (todo A confirmer si plus utile)
        //        $resource_data_type = \Drupal::request()->query->get('resource_data_type');
        //        if ($resource_data_type) {
        //            $filters['resource_data_type'] = $resource_data_type;
        //            $min_score = $min_score + 100;
        //        }

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

        // Récupération du path de la recherche interne avec les filtres - onglet
        $pathSearchInternal = $session->get('pathSearchInternal');

        // Enregistrement du path (recherche externe API) avec les filtres en session pour les onglets
        $pathSearchExternal = \Drupal::request()->getRequestUri();
        $session->set('pathSearchExternal', $pathSearchExternal);

        return [
            '#theme' => 'openig_search_results',
            '#items' => $search['results'],
            '#facets' => $search['facets'],
            '#filters' => $filters,
            '#page' => $page ? $page : 1,
            '#pager' => $search['pager'],
            "#url" => $search['url'],
            "#count" => $search['count'],
            '#pathSearchInternal' => $pathSearchInternal,
            '#pathSearchExternal' => $pathSearchExternal,
            '#search_filter_form' => \Drupal::formBuilder()->getForm('Drupal\openig_search\Form\SearchFilterForm'),
        ];
    }


    public function search_internal_results()
    {
        // Récupération champ de recherche saisie
        $search_fulltext = \Drupal::request()->get('search_api_fulltext');

        $session = \Drupal::request()->getSession();

        if ($search_fulltext !== null) {
          // Enregistrement barre de recherche en session pour les contenu externes
          $session->set('searchFulltext', str_replace(" ", "+", $search_fulltext));
        }

        $pathSearchInternal = \Drupal::request()->getRequestUri();
        // Enregistrement du path avec les filtres en session pour les onglets
        $session->set('pathSearchInternal', $pathSearchInternal);

        // Récupération du path de la recherche externe avec les filtres - onglet
        $pathSearchExternal = $session->get('pathSearchExternal');

        return [
          '#theme' => 'openig_search_internal_results',
          '#pathSearchInternal' => $pathSearchInternal,
          '#pathSearchExternal' => $pathSearchExternal,
        ];
    }

}
