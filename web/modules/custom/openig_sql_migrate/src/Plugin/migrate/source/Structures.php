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

    // $query->range(0, 1); // limit to 1, debug only
    
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

    // Link du logo vers les fichiers déjà importés
    $database = \Drupal::database();
    $query = $database->query("SELECT sourceid1, destid1 FROM {migrate_map_logos_structures} WHERE sourceid1 = '".intval($row->getSourceProperty('id'))."'");
    $results = $query->fetchAllKeyed();

    foreach ($results as $key => $value) {
      if ($key == $row->getSourceProperty('id')) {
        $row->setSourceProperty('logo', $value);
      }
    }


    /*
     * Type de structure
     */
    // Si non défini en fait en sorte de créer une taxo "indéfini"
    if($row->getSourceProperty('organisation_type_name') === null) $row->setSourceProperty('organisation_type_name', 'Indéfini');
    //récupération de la taxonomie
    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['name' => $row->getSourceProperty('organisation_type_name'), 'vid' => 'typologie_de_structure']);
    // Si terme non trouvé, on le créé
    if(empty($terms)) {
        $term = \Drupal\taxonomy\Entity\Term::create(array('name' => $row->getSourceProperty('organisation_type_name'),  'vid' => 'typologie_de_structure'));
        $term->save();
    } else {
        $term = reset($terms); //first element
    }
    // Mise à dispo du champ
    $row->setSourceProperty('organisation_type_id', $term->get('tid')->getValue()[0]['value']);


    return parent::prepareRow($row);
  }
}

