<?php

declare(strict_types=1);

namespace Drupal\custom_general_features\Plugin\UiPatterns\Source;

use Drupal\Component\Plugin\Exception\ContextException;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\file\FileInterface;
use Drupal\image\Entity\ImageStyle;
use Drupal\ui_patterns\Attribute\Source;
use Drupal\ui_patterns\SourcePluginBase;

/**
 * Resuelve la URL del logo de un sponsor, con el image style aplicado.
 *
 * A partir de `field_sponsor_logo` del nodo `sponsor` de la fila actual,
 * pasada por el image style `sponsor_logo` (el mismo que ya usa
 * `core.entity_view_display.node.sponsor.teaser.yml`), en vez de la URL
 * del fichero original — para mantener el mismo tratamiento (WebP,
 * tamaño) que ya tenía la vista antes de esta integración. Los SVG se
 * devuelven sin image style (URL del original).
 */
#[Source(
    id: 'sponsor_logo_url',
    label: new TranslatableMarkup('[Sponsor] Logo URL (image style sponsor_logo)'),
    description: new TranslatableMarkup('URL del logo del sponsor de la fila actual, con el image style sponsor_logo aplicado.'),
    prop_types: ['string'],
    tags: ['views'],
    context_requirements: ['views:row'],
    context_definitions: [
      'entity' => new ContextDefinition('entity', label: new TranslatableMarkup('Entity'), required: FALSE),
    ]
)]
class SponsorLogoUrlSource extends SourcePluginBase {

  /**
   * {@inheritdoc}
   */
  public function getPropValue(): mixed {
    $entity = $this->getEntity();
    if (!$entity instanceof ContentEntityInterface || !$entity->hasField('field_sponsor_logo')) {
      return '';
    }
    $file = $entity->get('field_sponsor_logo')->entity;
    if (!$file instanceof FileInterface) {
      return '';
    }
    // GD no puede rasterizar SVG: pasarlo por el image style (conversión a
    // WebP + escalado) da un 500 al generar el derivado. Se sirve el original,
    // igual que hacía antes el formateador image con svg_render_as_image.
    if ($file->getMimeType() === 'image/svg+xml') {
      return $file->createFileUrl(FALSE);
    }
    $style = ImageStyle::load('sponsor_logo');
    if (!$style) {
      return $file->createFileUrl(FALSE);
    }
    return $style->buildUrl($file->getFileUri());
  }

  /**
   * Obtiene la entidad de la fila actual desde el contexto.
   *
   * No lanza si no hay ninguna disponible.
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
