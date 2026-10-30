<?php

declare(strict_types=1);

namespace Drupal\custom_general_features\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Contains custom hooks for general features.
 */
class CustomGeneralFeaturesHooks {

  public function __construct(
    private readonly RouteMatchInterface $routeMatch,
  ) {}

  /**
   * Implements hook_entity_type_alter().
   *
   * Adds custom link templates to the taxonomy_term entity type.
   */
  #[Hook('entity_type_alter')]
  public function entityTypeAlter(array &$entity_types): void {
    /** @var \Drupal\Core\Entity\EntityTypeInterface[] $entity_types */
    if (isset($entity_types['taxonomy_term'])) {
      $entity_types['taxonomy_term']->setLinkTemplate('event_platform:sessions', '/taxonomy/term/{taxonomy_term}/sessions');
      $entity_types['taxonomy_term']->setLinkTemplate('event_platform:schedule', '/taxonomy/term/{taxonomy_term}/schedule');
    }
  }

  /**
   * Implements hook_preprocess_HOOK() for views_view_summary_tabs.
   *
   * Pestañas de día del programa (vista session_schedule): views_summary_tabs
   * marca la primera pestaña como activa siempre que ninguna coincide con la
   * URL. Eso es correcto en /sessions/schedule sin fecha (se muestra el día
   * por defecto), pero con una fecha sin sesiones marcaba un día que no es el
   * que se muestra. Si la URL trae fecha, solo queda activa la pestaña de esa
   * fecha, o ninguna.
   */
  #[Hook('preprocess_views_view_summary_tabs')]
  public function preprocessViewsViewSummaryTabs(array &$variables): void {
    if ($variables['view']->id() !== 'session_schedule') {
      return;
    }
    $date = $this->routeMatch->getParameter('arg_0');
    if (empty($date) || !is_string($date)) {
      return;
    }
    foreach ($variables['rows'] as $row) {
      if (is_object($row) && property_exists($row, 'active')) {
        $path = (string) parse_url((string) $row->url, PHP_URL_PATH);
        $row->active = str_ends_with($path, '/' . $date);
      }
    }
  }

}
