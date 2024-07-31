<?php

namespace Drupal\openig_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\openig_search\SearchQueryService;
use \Drupal\search_api\Entity\Index;

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
        $datas = $this->filter_search_results();
        // Récupération du path de la recherche interne avec les filtres - onglet
        // $pathSearchInternal = $datas['#session']->get('pathSearchInternal');

        // Enregistrement du path (recherche externe API) avec les filtres en session pour les onglets
        // $pathSearchExternal = \Drupal::request()->getRequestUri();
        // $datas['#session']->set('pathSearchExternal', $pathSearchExternal);

        // Récupération du nombre d'éléments de la recherche interne
        $session = \Drupal::request()->getSession();
        $nb_resultats = $session->get('nb_resultats');

        $search_fulltext = $datas['#search_filter_form']['search_api_fulltext']['#value'];
        return [
            '#theme'    => 'openig_search_results',
            '#items'    => $datas['#items'], //$search['results'],
            '#facets'   => $datas['#facets'], // $search['facets'],
            '#filters'  => $datas['#filters'],// $filters,
            '#page'     => $datas['#page'], //$page ? $page : 1,
            '#pager'    => $datas['#pager'], // $search['pager'],
            "#url"      => $datas['#url'], //$search['url'],
            "#count"    => $datas['#count'], //$search['count'],
            '#search_api_fulltext' => $search_fulltext,
            '#search_filter_form' => $datas['#search_filter_form'], // \Drupal::formBuilder()->getForm('Drupal\openig_search\Form\SearchFilterForm'),
            '#pathSearchInternal' => '/recherche/?search_api_fulltext='.$search_fulltext, // $pathSearchInternal,
            '#pathSearchExternal' => '/recherche/ressources_externes?search_api_fulltext='.$search_fulltext, // $pathSearchExternal,
            '#nbResultats'        => $nb_resultats,
            '#ressourceInterne'   => false
        ];
    }



  /**
   * Querying, build & show search results
   */
  public function filter_search_results()
  {
    // Initialize facets array
    $filters = [];
    // Initialize min score
    $min_score = 0;

    // Fulltext search - Barre de recherche récupération en session todo plus utile récupéré du form directement
    // $search_api_fulltext = $session->get('search_api_fulltext');
    $search_fulltext = \Drupal::request()->query->get('search_api_fulltext');

    // Récupération du path referer
    // $host = \Drupal::request()->server->get('SITE_URL');
    // $referer = \Drupal::request()->headers->get('referer');
    // $pathReferer = $host . '/recherche?search_api_fulltext=' . $search_api_fulltext;
    // si path identique on conserve la recherche (barre de recherche - Fulltext)
    // if ($search_api_fulltext && $pathReferer === $referer) {
       $filters['query'] = $search_fulltext;
       $min_score = $min_score + 0.1;
    //}
    // si recherche réinitialisé on nettoye le champ de recherche
    //else{
      //$search_fulltext = null;
    //}

    // Content type - (todo A confirmer si plus utile)
    // $resource_data_type = \Drupal::request()->query->get('resource_data_type');
    // if ($resource_data_type) {
    //    $filters['resource_data_type'] = $resource_data_type;
    //    $min_score = $min_score + 100;
    // }

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

    // Permet la correction des filtres de recherche Sources - twig pas de différence entre dataset et datasets
    foreach ($search['facets']['lineage'] as $key => $lineage) {
      if($lineage === 'datasets'){
        $search['facets']['lineage'][$key] = 'region Datasets';
      }
    }

    if(is_array($filters['lineage'])){
      foreach ($filters['lineage'] as $key => $lineage) {
        if($lineage === 'datasets'){
          $filters['lineage'][$key] = 'region Datasets';
        }
      }
    }
    else{
      if($filters['lineage'] === 'datasets'){
        $filters['lineage'] = 'region Datasets';
      }
    }

    return [
      '#facets'   => $search['facets'],
      '#filters'  => $filters,
      '#page'     => $page ? $page : 1,
      '#search_filter_form' => \Drupal::formBuilder()->getForm('Drupal\openig_search\Form\SearchFilterForm'),
      '#pager'    => $search['pager'],
      "#url"      => $search['url'],
      "#count"    => $search['count'],
      '#items'    => $search['results'],
    ];
  }


    public function search_internal_results()
    {
        // Récupération champ de recherche saisie et filtres
        $search_fieldTags = \Drupal::request()->get('field_tag');
        $search_types = \Drupal::request()->get('type');
        $search_fulltext = \Drupal::request()->get('search_api_fulltext');

        // Requête pour la récupération du nombre de contenu récupéré
        $index = Index::load('index_general');
        $query = $index->query();
        if($search_fulltext){
          $query->keys($search_fulltext);
        }
        if(!empty($search_types) && is_array($search_types)) {
          // Conditions sur le type de contenu
          $conditions = $query->createConditionGroup('OR');
          foreach($search_types as $type) {
            $conditions->addCondition('type', $type);
          }
          $query->addConditionGroup($conditions);
        }

        if(!empty($search_fieldTags) && is_array($search_fieldTags)){
          //Conditions sur les thématiques
          $conditions = $query->createConditionGroup('OR');
          foreach ($search_fieldTags as $thematique) {
            $conditions->addCondition('field_tag', $thematique);
          }
          $query->addConditionGroup($conditions);
        }

        $query->setLanguages(['fr']);
        $query->addCondition('status', 1);

        $nb_resultats = $query->execute()->getResultCount();

        // Enregistrement en session du nombre de résultat onglet contenu interne (pour la page contenu externes)
        $session = \Drupal::request()->getSession();
        $session->set('nb_resultats', $nb_resultats);

        // Enregistrement barre de recherche en session pour les contenu externes
        // $session->set('search_api_fulltext', $search_fulltext);

        // Récupération et Enregistrement du path avec les filtres en session pour les onglets
        // $pathSearchInternal = \Drupal::request()->getRequestUri();
        // $session->set('pathSearchInternal', $pathSearchInternal);

        // Récupération du path de la recherche externe avec les filtres - onglet
        /*if ($session->get('pathSearchExternal') !== null) {
          if (!str_contains($session->get('pathSearchExternal'), 'search_api_fulltext')) {
            if (str_contains($session->get('pathSearchExternal'), '?')) {
              $pathSearchExternal = $session->get('pathSearchExternal').'&search_api_fulltext='.$search_fulltext;
            }
            else{
              $pathSearchExternal = $session->get('pathSearchExternal').'?search_api_fulltext='.$search_fulltext;
            }
          }else{
            $pathSearchExternal = '/recherche/ressources_externes?search_api_fulltext='.$search_fulltext;
          }

        }else{
          $pathSearchExternal = '/recherche/ressources_externes?search_api_fulltext='.$search_fulltext;
        }*/
        //$pathSearchExternal = $session->get('pathSearchExternal') !== null ? $session->get('pathSearchExternal') : '/recherche/ressources_externes?search_api_fulltext='.$search_fulltext;

        return [
          '#theme' => 'openig_search_internal_results',
          '#pathSearchInternal' => '/recherche/?search_api_fulltext='.$search_fulltext, // $pathSearchInternal,
          '#pathSearchExternal' => '/recherche/ressources_externes?search_api_fulltext='.$search_fulltext, // $pathSearchExternal,
          '#nbResultats'        => $nb_resultats,
          '#ressourceInterne'   => true,
          '#search_api_fulltext' => $search_fulltext
        ];
    }

}
