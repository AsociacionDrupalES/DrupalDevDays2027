<?php

declare(strict_types=1);

namespace Drupal\custom_paragraphs_extras\Plugin\paragraphs\Behavior;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\paragraphs\ParagraphsBehaviorBase;

/**
 * Provides a custom paragraph behavior to set the content alignment.
 *
 * Añade la clase `align--<valor>` al paragraph; el tema decide qué alinea.
 * Sin valor, alineación por defecto del componente.
 *
 * @ParagraphsBehavior(
 *   id = "custom_alignment",
 *   label = @Translation("Alignment"),
 *   description = @Translation("Adds an alignment class to the Paragraph entity."), weight = 0,
 * )
 */
final class AlignmentBehavior extends ParagraphsBehaviorBase {

  /**
   * Return list of alignments.
   */
  public function getAlignments(): array {
    return [
      'center' => $this->t('Center'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildBehaviorForm(ParagraphInterface $paragraph, array &$form, FormStateInterface $form_state): array {
    $alignment = $paragraph->getBehaviorSetting($this->getPluginId(), 'alignment');
    $form['#attributes']['class'][] = 'paragraphs-subform';
    $form['alignment'] = [
      '#type'          => 'select',
      '#default_value' => $alignment ?? '',
      '#empty_option'  => $this->t('Default'),
      '#empty_value'   => '',
      '#options'       => $this->getAlignments(),
      '#required'      => FALSE,
      '#title'         => $this->t('Alignment'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function view(array &$build, Paragraph $paragraph, EntityViewDisplayInterface $display, $view_mode): void {
    $alignment = $paragraph->getBehaviorSetting($this->getPluginId(), 'alignment');
    if ($alignment && isset($this->getAlignments()[$alignment])) {
      $build['#attributes']['class'][] = 'align--' . $alignment;
    }
  }

}
