<?php

namespace Drupal\openig_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\openig_search\SearchQueryService;

/**
 * Class SearchController.
 */
class SearchController extends ControllerBase {

    private $searchQueryService;

    public function __construct(SearchQueryService $searchQueryService) {
        $this->searchQueryService = $searchQueryService;
    }

    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('openig_search.search_query_service')
        );
    }

    /**
     * Querying, build & show search results
     */
    public function search_results() {
        // Initialize facets array
        $filters = [];
        // Initialize min score
        $min_score = 0;

        // Fulltext search - Barre de recherche
        $session = \Drupal::request()->getSession();
        $fulltext = $session->get('searchFulltext');
        if ($fulltext) {
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
        // lineage=CKAN+Extension+Showcases&lineage=OpenIG&
        // category=Arts+plastiques&category=Cin%C3%A9ma&
        // resource_format=CSV&
        //op=Valider

        $category_field = \Drupal::request()->get('category');
        $lineage_field = \Drupal::request()->get('lineage');
        $resource_format_field = \Drupal::request()->get('resource_format');
        dump($lineage_field);
        dump($category_field);
        dump($resource_format_field);


        $pathSearchExternal = "/recherche/ressources_externes?";
        if(!empty($lineage_field)){
          $lineage_field_trim = explode("|", $lineage_field);
          dump($lineage_field_trim);
          foreach ($lineage_field_trim as $lineage){
            $pathSearchExternal .= "lineage=".str_replace(" ", "+", $lineage)."&";
          }
        }
        if(!empty($category_field)){
          $category_field_trim = explode("|", $lineage_field);
          dump($category_field_trim);
          foreach ($category_field_trim as $category){
            $pathSearchExternal .= "category=".str_replace(" ", "+", $category)."&";
          }
        }
        if(!empty($resource_format_field)){
          $resource_format_field_trim = explode("|", $lineage_field);
          dump($resource_format_field_trim);
          foreach ($resource_format_field_trim as $resource_format){
            $pathSearchExternal .= "resource_format=".str_replace(" ", "+", $resource_format)."&";
          }
        }
        dump($pathSearchExternal);
        $session->set('pathSearchExternal', $pathSearchExternal);

        return [
            '#theme'    => 'openig_search_results',
            '#items'    => $search['results'],
            '#facets'   => $search['facets'],
            '#filters'  => $filters,
            '#page'     => $page ? $page : 1,
            '#pager'    => $search['pager'],
            "#url"      => $search['url'],
            "#count"    => $search['count'],
            '#pathSearchInternal'  => $pathSearchInternal,
            '#pathSearchExternal'  => $pathSearchExternal,
            '#search_filter_form'  => \Drupal::formBuilder()->getForm('Drupal\openig_search\Form\SearchFilterForm'),
        ];
    }


  public function search_internal_results() {

    // Récupération des filtres du recherche saisie
    $search_types = \Drupal::request()->get('type');
    $search_tag = \Drupal::request()->get('field_tag');
    $search_fulltext = \Drupal::request()->get('search_api_fulltext');

    $session = \Drupal::request()->getSession();

    // Reconstruction du path
    $pathSearchInternal = "/recherche?";
    if($search_types !== null){
      foreach ($search_types as $type){
        $pathSearchInternal .= "type[$type]=$type&";
      }
    }
    if($search_tag !== null){
      foreach ($search_tag as $tag){
        $pathSearchInternal .= "field_tag[$tag]=$tag&";
      }
    }
    if($search_fulltext !== null){
      $pathSearchInternal .= "search_api_fulltext=".str_replace(" ", "+", $search_fulltext)."";
      // Enregistrement barre de recherche en session pour les contenu externes
      $session->set('searchFulltext', str_replace(" ", "+", $search_fulltext));
    }

    // Enregistrement du path avec les filtres en session pour les onglets
    $session->set('pathSearchInternal', $pathSearchInternal);

    // Récupération du path de la recherche externe avec les filtres - onglet
    $session = \Drupal::request()->getSession();
    $pathSearchExternal = $session->get('pathSearchExternal');

    return [
      '#theme'              => 'openig_search_internal_results',
      '#pathSearchInternal' => $pathSearchInternal,
      '#pathSearchExternal' => $pathSearchExternal,
    ];
  }

}
