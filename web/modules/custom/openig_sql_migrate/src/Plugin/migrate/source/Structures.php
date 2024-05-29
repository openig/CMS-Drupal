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

    $query->fields('o');
    $query->addField('ot', 'name', 'organisation_type_name');
    
    // Do something with each $record

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
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
    // This example shows how source properties can be added in
    // prepareRow(). The source dates are stored as 2017-12-17
    // and times as 16:00. Drupal 8 saves date and time fields
    // in ISO8601 format 2017-01-15T16:00:00 on UTC.
    // We concatenate source date and time and add the seconds.
    // The same result could also be achieved using the 'concat'
    // and 'format_date' process plugins in the migration
    // definition.

    return parent::prepareRow($row);
  }
}

