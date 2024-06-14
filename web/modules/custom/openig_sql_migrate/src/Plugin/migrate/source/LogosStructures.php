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
 *   id = "logos_structures",
 *   source_module = "openig_sql_migrate",
 * )
 */
class LogosStructures extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('idgo_admin_organisation', 'o');
    $query->distinct();

    $query->fields('o', [
        'id',
        'logo',
    ]);
    
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
    return parent::prepareRow($row);
  }
}

