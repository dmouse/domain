<?php

/**
 * @file
 * Definition of Drupal\domain\DomainLoader.
 */

namespace Drupal\domain;

use Drupal\domain\DomainLoaderInterface;
use Drupal\domain\DomainInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;

class DomainLoader implements DomainLoaderInterface {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The typed config handler.
   *
   * @var Drupal\Core\Config\TypedConfigManagerInterface
   */
  protected $typed_config;

  /**
   * Constructs a DomainLoader object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param Drupal\Core\Config\TypedConfigManagerInterface $typed_config
   *   The typed config handler.
   */
  public function __construct(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
    $this->typedConfig = $typed_config;
  }

  /**
   * @return array
   *   An array keyed by field name and containing the name and
   *   label for the field.
   */
  public function getSchema() {
    $fields = $this->typedConfig->getDefinition('domain.record.*');
    return isset($fields['mapping']) ? $fields['mapping'] : array();
  }

  /**
   * Returns the id of the default domain.
   *
   * @return
   *   The id of the default domain or FALSE if none is set.
   */
  public function getDefaultId() {
    // manager getDefaultId.
    $result = entity_load_multiple_by_properties('domain', array('is_default' => TRUE));
    if (!empty($result)) {
      return key($result);
    }
    return FALSE;
  }

  /**
   * Gets the default domain object.
   */
  public function getDefaultDomain() {
    $result = entity_load_multiple_by_properties('domain', array('is_default' => TRUE));
    if (!empty($result)) {
      return current($result);
    }
    return FALSE;
  }

  /**
   * Loads multiple domains.
   */
  public function loadMultiple($ids = array(), $reset = FALSE) {
    return entity_load_multiple('domain', $ids, $reset);
  }

  /**
   * Loads multiple domains and sorts by weight.
   */
  public function loadMultipleSorted($ids = array()) {
    $domains = $this->loadMultiple();
    return uasort($domains, array($this, 'sort'));
  }

  /**
   * Loads a domain record by hostname lookup.
   */
  public function loadByHostname($hostname) {
    $entities = entity_load_multiple_by_properties('domain', array('hostname' => $hostname));
    if (empty($entities)) {
      return FALSE;
    }
    return current($entities);
  }

  /**
   * Gets the id of the active domain.
   */
  public function getActiveId() {
    return $this->getActiveDomain()->id();
  }

  /**
   * Returns the list of domains formatted for a form options list.
   */
  public function optionsList() {
    $list = array();
    foreach ($this->loadMultipleSorted() as $id => $domain) {
      $list[$id] = $domain->name;
    }
    return $list;
  }

  /**
   * Sorts domains by weight.
   */
  public function sort($a, $b) {
    return $a->weight > $b->weight;
  }

  /**
   * Gets the list of required fields.
   */
  public function getRequiredFields() {
    return array('hostname', 'name', 'id', 'scheme', 'status', 'weight');
  }


}