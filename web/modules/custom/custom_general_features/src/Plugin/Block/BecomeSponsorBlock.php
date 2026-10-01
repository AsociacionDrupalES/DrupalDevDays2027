<?php

declare(strict_types=1);

namespace Drupal\custom_general_features\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Extension\ThemeExtensionList;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Theme\ComponentPluginManager;
use Drupal\Core\Theme\ThemeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a become a sponsor block.
 */
#[Block(
  id: 'custom_general_features_become_sponsor',
  admin_label: new TranslatableMarkup('Do you want to be a sponsor?'),
  category: new TranslatableMarkup('Custom'),
)]
final class BecomeSponsorBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Constructs the plugin instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    private readonly ThemeExtensionList $extensionListTheme,
    private readonly ThemeManagerInterface $themeManager,
    private readonly ComponentPluginManager $componentPluginManager,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('extension.list.theme'),
      $container->get('theme.manager'),
      $container->get('plugin.manager.sdc'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'title' => $this->t('Do you want to be a sponsor?'),
      'body' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state): array {
    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $this->configuration['title'] ?? '',
    ];
    $form['body'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Body'),
      '#format' => $this->configuration['body']['format'],
      '#default_value' => $this->configuration['body']['value'] ?? '',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state): void {
    $this->configuration['title'] = $form_state->getValue('title');
    $this->configuration['body'] = $form_state->getValue('body');
  }

  /**
   * Logos del bloque, por orden de preferencia, en la raíz del tema.
   */
  private const LOGO_CANDIDATES = [
    'ddd2027-logo.svg',
    'logo-gradient.svg',
  ];

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $theme = $this->resolveTheme();
    if ($theme === NULL) {
      return [];
    }

    $build['content'] = [
      '#type' => 'component',
      '#component' => $theme . ':become_sponsor',
      '#props' => [
        'title' => $this->configuration['title'] ?? '',
        'logo_svg' => $this->loadLogo($theme),
      ],
      '#slots' => [
        'text' => $this->configuration['body']['value'] ?? '',
      ],
    ];
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts(): array {
    return array_merge(parent::getCacheContexts(), ['theme']);
  }

  /**
   * Devuelve el tema activo, o su primer tema base, que tenga el componente.
   *
   * @return string|null
   *   El nombre del tema, o NULL si ninguno define become_sponsor.
   */
  private function resolveTheme(): ?string {
    $active_theme = $this->themeManager->getActiveTheme();
    $themes = array_merge(
      [$active_theme->getName()],
      array_keys($active_theme->getBaseThemeExtensions()),
    );
    foreach ($themes as $theme) {
      if ($this->componentPluginManager->hasDefinition($theme . ':become_sponsor')) {
        return $theme;
      }
    }
    return NULL;
  }

  /**
   * Lee el primer logo candidato que exista en la raíz del tema.
   */
  private function loadLogo(string $theme): string {
    $path = $this->extensionListTheme->getPath($theme);
    foreach (self::LOGO_CANDIDATES as $file) {
      $candidate = $path . '/' . $file;
      if (is_file($candidate)) {
        return (string) file_get_contents($candidate);
      }
    }
    return '';
  }

}
