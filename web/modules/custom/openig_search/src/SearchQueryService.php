<?php

namespace Drupal\openig_search;

class SearchQueryService {

    private $search_api_url;
    private $per_page;

    public function __construct() {
//        $this->search_api_url = getenv('SEARCH_API_URL');
        $this->search_api_url = 'https://onegeo.openig.org';
        $this->per_page = 10;
    }

    /**
     * Compute the pagination from page parameter
     * @param $page integer the page number
     * @return array with from & size value regarding $page
     */
    private function computePage($page) {
        return [
            'from' => $page == 0 ? 0 : ($page * $this->per_page),
            'size' => $this->per_page
        ];
    }

    public function search($filters, $page) {
        $ch = curl_init();
        $headers = [ 'Accept: application/json', 'Content-Type: application/json' ];
        $query = array_merge($filters, $this->computePage($page));

        // Rewrite query to allow multiple value for one filter
        $rewrited_query = $this->rewrite_query($query);

        $query_string = http_build_query($rewrited_query);
        $url = "$this->search_api_url/api/services/public/search?$query_string";

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        $decode = json_decode($response);

        return $this->buildResult($decode, $query_string, $page, $url);
    }

    /**
     * Rewrite query params to allow multivalues filters
     * @param $query_params
     * @return array of parameters rewrited
     */
    private function rewrite_query($query_params) {
        foreach ($query_params as $key => $param) {
            if (is_array($param)) {
                // Remove from query string
                unset($query_params[$key]);
                // Concat with glue
                $query_params[$key] = implode('|', $param);
            }
        }
        return $query_params;
    }

    private function buildResult($decode, $query_string, $page, $url) {
        $results = [];
        $facets = [];
        $count = 0;

        // Build results
        if (isset($decode->results) && count($decode->results)) {
            $count = $decode->total;
            foreach ($decode->results as $row) {
                // Build result's row
                $item = [
                    'title' => $row->properties->title,
                    'link' => $this->build_result_link($row)
                ];

                // Fetch description
                if (isset($row->properties->description)) {
                    $item['description'] = substr($row->properties->description, 0, 1000);
                }

                // Fetch source
                if (isset($row->lineage->resource->name)) {
                    $item['source'] = $row->lineage->resource->name;
                }

                // Fetch thumbnail
                if (isset($row->properties->thumbnail)) {
                    $item['thumbnail'] = $row->properties->thumbnail;
                } elseif (isset($row->properties->org_thumbnail)) {
                    $item['thumbnail'] = $row->properties->org_thumbnail;
                } else {
                    if (isset($row->lineage->resource->name) && $row->lineage->resource->name == 'OpenIG') {
                        $item['thumbnail'] = theme_get_setting('logo.url');
                    }
                }

                // Fetch tags
                if (isset($row->properties->tags)) {
                    $item['tags'] = $row->properties->tags;
                }

                array_push($results, $item);
            }

        } else {
            \Drupal::logger('openig_search')->error('CURL request. Response : @response', [ '@response' => print_r($decode, true) ]);
            \Drupal::logger('openig_search')->error('Request : @request', [ '@request' => print_r("$this->search_api_url/api/services/public/search?$query_string", true) ]);
        }

        // Fetch facets
        if (isset($decode->aggregations->_global)) {
            // Category facets
            if (isset($decode->aggregations->_global->category->buckets) && count($decode->aggregations->_global->category->buckets)) {
                $facets['category'] = [];
                foreach ($decode->aggregations->_global->category->buckets as $cat) {
                    array_push($facets['category'], $cat->key);
                }
                natcasesort($facets['category']);
            }

            // Lineage facets
            if (isset($decode->aggregations->_global->lineage->buckets) && count($decode->aggregations->_global->lineage->buckets)) {
                $facets['lineage'] = [];
                foreach ($decode->aggregations->_global->lineage->buckets as $cat) {
                    array_push($facets['lineage'], $cat->key);
                }
                //natcasesort($facets['lineage']);
            }

            // Format facets
            if (isset($decode->aggregations->_global->resource_format->buckets) && count($decode->aggregations->_global->resource_format->buckets)) {
                $facets['resource_format'] = [];
                foreach ($decode->aggregations->_global->resource_format->buckets as $format) {
                    array_push($facets['resource_format'], $format->key);
                }
                natcasesort($facets['resource_format']);
            }

            // resource_data_type facets
            if (isset($decode->aggregations->_global->resource_data_type->buckets) && count($decode->aggregations->_global->resource_data_type->buckets)) {
                $facets['resource_data_type'] = [];
                $allowed_resource_data_type = ['Événement', 'Actualité', 'Ressource', 'Groupe de travail'];
                foreach ($decode->aggregations->_global->resource_data_type->buckets as $format) {
                    if (in_array($format->key, $allowed_resource_data_type)) {
                        array_push($facets['resource_data_type'], $format->key);
                    }
                }
                natcasesort($facets['resource_data_type']);
            }
        }

        return $this->return_result_with_pager($results, $facets, $count, $page, $url);
    }

    private function build_result_link($row) {
        $url = '';

        if (isset($row->lineage) && isset($row->lineage->resource) && isset($row->lineage->source)) {
            switch ($row->lineage->source->protocol) {
                case 'ckan':
                    switch ($row->lineage->resource->name) {
                        case 'CKAN Extension Showcases':
                            $uri = $row->lineage->source->uri;
                            $id = $row->properties->id;
                            $url = "$uri/showcase/$id";
                            break;
                        case 'CKAN Datasets':
                            $uri = $row->lineage->source->uri;
                            $id = $row->properties->id;
                            $url = "$uri/dataset/$id";
                            break;
                    }

                case 'opendatasoft':
                    switch ($row->lineage->source->uri) {
                        case 'https://data.laregion.fr:/':
                            $urlData = parse_url($row->lineage->source->uri);
                            $uri = $urlData['scheme'] . '://'.$urlData['host'];
                            $id = $row->properties->id;
                            $url = "$uri/explore/dataset/$id/information/";
                            break;
                    }

                case 'csw':
                    switch ($row->lineage->source->uri) {
                        // TODO : passer cette URL en variable
                        // Pour le moment, on passe par le GN de qualif car le CSW de picto ne fonctionne pas sur le mode "full"
                        case 'https://geonetwork.openig.org/geonetwork/srv/fre/csw-picto':
                            $urlData = parse_url("https://www.picto-occitanie.fr/");
                            $uri = $urlData['scheme'] . '://'.$urlData['host'];
                            $id = $row->properties->id;
                            $url = "$uri/geonetwork/srv/fre/catalog.search#/metadata/$id";
                            break;
                    }
                case 'atom':
                default:
                    if (isset($row->properties->uri)) {
                        $url = $row->properties->uri;
                    } else if (isset($row->properties->links_href)) {
                        $url = $row->properties->links_href[0];
                    }
                    break;
            }
        }
        return $url;
    }

    private function return_result_with_pager($results, $facets, $count, $current, $url) {

        $page_count = floor($count / ($this->per_page));

        $pager = [
            'pages' => [],
            'current' => $current,
            'count' => $page_count
        ];

        // Push first label
        $prev = $current - 1;
        array_push($pager['pages'], [
            'number' => $prev,
            'label' => 'Précédent',
            'url' => preg_replace('/?page=(\d+)', "page=$prev", \Drupal::request()->getUri())
        ]);

        for ($i = 0; $i <= $page_count; $i++) {
            if ($i == 0 || ($i == $current - 2) || ($i == $current - 1) || $current == $i || ($i == $current + 1) || ($i == $current + 2) || $i == $page_count) {
              array_push($pager['pages'], [
                    'number' => $i,
                    'label' => $i + 1,
                    'url' => preg_replace('/?page=(\d+)', "page=$i", \Drupal::request()->getUri())
                ]);
            }
        }

        // Push last label
        $next = $current + 1;
        array_push($pager['pages'], [
            'number' => $next,
            'label' => 'Suivant',
            'url' => preg_replace('/?page=(\d+)', "page=$next", \Drupal::request()->getUri())
        ]);

        return [
            'results' => $results,
            'facets' => $facets,
            'pager' => $pager,
            'count' => $count,
            'url' => $url
        ];
    }
}
