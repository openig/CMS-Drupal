<?php

namespace Drupal\openig_sql_migrate\Plugin\migrate\source;

use Drupal\migrate\Annotation\MigrateSource;
use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;
use Drupal\taxonomy\Entity\Term;

/**
 * Minimalistic example for a SqlBase source plugin.
 *
 * @MigrateSource(
 *   id = "structures",
 *   source_module = "openig_sql_migrate",
 * )
 */
class Structures extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('idgo_admin_organisation', 'o');
    $query->distinct();
    $query->leftJoin('idgo_admin_organisationtype','ot', 'ot.code = o.organisation_type_id');

    $query->fields('o', [
        'id',
        'legal_name',
        'slug',
        'email',
        'website',
        'description',
        'address',
        'postcode',
        'city',
        'phone',
        'organisation_type_id',
        'logo',
        'siren',
        'is_active',
        'is_crige_partner'
    ]);
    $query->addField('ot', 'name', 'organisation_type_name');
    
    // Do something with each $record

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
/*
    return [
        'id',
        'legal_name',
        'slug',
        'email',
        'website',
        'description',
        'address',
        'postcode',
        'city',
        'phone',
        'organisation_type_id',
        'organisation_type_name',
        'siren',
        'is_active',
        'is_crige_partner'
    ];
*/
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'id' => [
        'type' => 'integer',
        'alias' => 'o',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {

    // Pour le logo, on génère l'URL
    $logo = $row->getSourceProperty('logo');
    if($logo) {
        $row->setSourceProperty('logo', "https://idgo.openig.org/media/".$logo);
    }
    // TODO: upload image
    //

    /*
     Type de structure
    */
    dump( $row->getSourceProperty('organisation_type_name') );
   
    if($row->getSourceProperty('organisation_type_name') !== null) { 
        //récupération de la taxonomie
        $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['name' => $row->getSourceProperty('organisation_type_name'), 'vid' => 'typologie_de_structure']);
        // Si terme non trouvé, on le créé
        if(empty($terms)) {
            $term = \Drupal\taxonomy\Entity\Term::create(array(  'name' => $row->getSourceProperty('organisation_type_name'),  'vid' => 'typologie_de_structure'));
            $term->save();
        } else {
            $term = $terms[1];
        }
        if($term) {
            // Mise à dispo du champ
            $row->setSourceProperty('organisation_type_id', $term->get('tid')->getValue()[0]['value']);
        }
    }

    return parent::prepareRow($row);
  }
}

