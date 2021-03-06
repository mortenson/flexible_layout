<?php

namespace Drupal\flexible_layout\Plugin\Layout;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Form\FormStateInterface;
use Drupal\layout_plugin\Plugin\Layout\LayoutBase;

/**
 * Provides a layout plugin with dynamic theme regions.
 */
class FlexibleLayout extends LayoutBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'layout' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['container']['#markup'] = '<div class="flexible-layout-container"></div>';
    $layout = !empty($this->configuration['layout']) ? $this->configuration['layout'] : [];
    $form['layout'] = [
      '#type' => 'textfield',
      '#default_value' => Json::encode($layout),
      '#maxlength' => 1000000000,
      '#attributes' => [
        'class' => ['flexible-layout-json-field', 'visually-hidden'],
      ]
    ];
    $form['#attached']['library'][] = 'flexible_layout/form';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->configuration['layout'] = JSON::decode($values['layout']);
  }

  protected function getRegionsFromLayout($current) {
    $regions = [];
    $regions[$current['machine_name']] = [
      'label' => $current['name'],
    ];
    foreach ($current['children'] as $column) {
      $regions = array_merge($regions, $this->getRegionsFromLayout($column));
    }
    return $regions;
  }

  /**
   * {@inheritdoc}
   */
  public function getRegionDefinitions() {
    $regions = $this->getRegionsFromLayout($this->configuration['layout']);
    return $regions;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginDefinition() {
    $definition = $this->pluginDefinition;
    $definition['region_names'] = [];
    $definition['regions'] = $this->getRegionDefinitions();
    foreach ($definition['regions'] as $region_id => $region_definition) {
      $definition['region_names'][$region_id] = $region_definition['label'];
    }
    return $definition;
  }

}
