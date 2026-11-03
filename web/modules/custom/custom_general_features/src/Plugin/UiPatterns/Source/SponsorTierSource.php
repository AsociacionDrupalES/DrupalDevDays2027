<?php

declare(strict_types=1);

namespace Drupal\custom_general_features\Plugin\UiPatterns\Source;

use Drupal\Component\Plugin\Exception\ContextException;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\taxonomy\TermInterface;
use Drupal\ui_patterns\Attribute\Source;
use Drupal\ui_patterns\SourcePluginBase;

/**
 * Resuelve el tier de un sponsor a partir de field_sponsor_level.
 *
 * Tier: platinum|gold|silver|bronze|media|individual, a partir del campo
 * `field_sponsor_level` (entity reference a la taxonomía `sponsor_level`)
 * del nodo `sponsor` de la fila actual.
 *
 * Se usa el `weight` real del término (no su `tid`, no su nombre traducible)
 * como identificador estable. El tier
 * "individual" se detecta vía el booleano `field_is_individual` del propio
 * término, que ya es la señal nativa que usa el sitio para ese caso.
 */
#[Source(
    id: 'sponsor_tier',
    label: new TranslatableMarkup('[Sponsor] Tier'),
    description: new TranslatableMarkup('Tier del sponsor (platinum|gold|silver|bronze|media|individual), resuelto por weight de field_sponsor_level, no por su nombre traducible.'),
    prop_types: ['enum'],
    tags: ['views'],
    context_requirements: ['views:row'],
    context_definitions: [
      'entity' => new ContextDefinition('entity', label: new TranslatableMarkup('Entity'), required: FALSE),
    ]
)]
class SponsorTierSource extends SourcePluginBase {

  /**
   * Mapa de weight de field_sponsor_level a clave de tier.
   *
   * Platino=0, Oro=1, Plata=2, Bronce=3, Media partner=4; Individual se
   * resuelve aparte, vía field_is_individual.
   */
  private const WEIGHT_TO_TIER = [
    0 => 'platinum',
    1 => 'gold',
    2 => 'silver',
    3 => 'bronze',
    4 => 'media',
  ];

  /**
   * {@inheritdoc}
   */
  public function getPropValue(): mixed {
    $entity = $this->getEntity();
    if (!$entity instanceof ContentEntityInterface || !$entity->hasField('field_sponsor_level')) {
      return NULL;
    }
    $term = $entity->get('field_sponsor_level')->entity;
    if (!$term instanceof TermInterface) {
      return NULL;
    }
    if ($term->hasField('field_is_individual') && (bool) $term->get('field_is_individual')->value) {
      return 'individual';
    }
    return self::WEIGHT_TO_TIER[(int) $term->getWeight()] ?? NULL;
  }

  /**
   * Obtiene la entidad de la fila actual desde el contexto.
   *
   * No lanza si no hay ninguna disponible (p. ej. al configurar el
   * componente en la UI de Views antes de tener filas reales).
   */
  private function getEntity(): mixed {
    try {
      return $this->getContextValue('entity');
    }
    catch (ContextException) {
      return NULL;
    }
  }

}
