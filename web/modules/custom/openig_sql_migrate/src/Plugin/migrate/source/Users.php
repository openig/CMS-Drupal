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
 *   id = "users",
 *   source_module = "openig_sql_migrate",
 * )
 */
class Users extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('auth_user', 'u');
    $query->distinct();
    $query->leftJoin('idgo_admin_profile','p', 'p.user_id = u.id');
    $query->leftJoin('idgo_admin_organisation','o', 'o.id = p.organisation_id');

    $query->fields('u', [
        'id',
        'username',
        'email',
        'last_login',
        'is_active',
        'is_staff',
        'first_name',
        'last_name',
        'date_joined',
    ]);
    $query->addField('p', 'phone');
    $query->addField('p', 'is_admin');
    $query->addField('p', 'organisation_id');

    // Exclude neogeo
    $query->condition('u.email', '%@neogeo%', 'NOT LIKE');
    $query->condition('u.username', 'admin', 'NOT LIKE');

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
        'alias' => 'u',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {

    // Link de l'organisation vers les structures déjà importés
    $database = \Drupal::database();
    $query = $database->query("SELECT sourceid1, destid1 FROM {migrate_map_structures} WHERE sourceid1 = '".intval($row->getSourceProperty('organisation_id'))."'");
    $results = $query->fetchAllKeyed();

    foreach ($results as $key => $value) {
      if ($key == $row->getSourceProperty('organisation_id')) {
        $row->setSourceProperty('organisation_id', $value);
      }
    }

    // Correction des dates
    $last_login = \DateTime::createFromFormat('Y-m-d H:i:s.uT', $row->getSourceProperty('last_login'));
    if($last_login) $row->setSourceProperty('last_login', $last_login->format('U'));
    
    $date_joined = \DateTime::createFromFormat('Y-m-d H:i:s.uT', $row->getSourceProperty('date_joined'));
    if($date_joined) $row->setSourceProperty('date_joined', $date_joined->format('U'));


    return parent::prepareRow($row);
  }
}

