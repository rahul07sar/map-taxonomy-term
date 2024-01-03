<?php

namespace Drupal\map_taxonomy_terms\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
Use Drupal\taxonomy\Entity\Vocabulary;

/**
 * The form for configuring taxonomy.
 *
 * @package Drupal\map_taxonomy_terms\Form
 */
class TaxonomyTermMappingForm extends ConfigFormBase {

  const Taxonomy_TERMS_SETTINGS = 'map_taxonomy_terms.taxonomy.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return self::Taxonomy_TERMS_SETTINGS;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [self::Taxonomy_TERMS_SETTINGS];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $mapping_settings = $this->config(self::Taxonomy_TERMS_SETTINGS)->get('mapping');
    $vocabularies = Vocabulary::loadMultiple();
    $i = 0;
    $triggering_element = $form_state->getTriggeringElement();
    $form['#tree'] = TRUE;
    $form['info_wrapper'] = [
      '#type' => 'markup',
      '#markup' => 'Select the terms to be mapped (hide/show) from the available Vocabulary list.<br><br> If Enabled, the terms selected here will be rendered on the custom form Selectlist/Checkboxes.',
    ];

    foreach($vocabularies as $voc) {
        //get label names of vocabulary.
        $vocabulary[$voc->id()] = $voc->label();
        $form['names_fieldset_wrapper'][$voc->id()] = [
            '#type' => 'details',
            '#title' => $this->t('@elem', ['@elem' => "Select the terms for Mapping in " . $voc->label()]),
            '#title_display' => 'invisible',
            '#false' => TRUE,
            '#weight' => $i,
            '#attributes' => [
              'class' => ['fields-wrapper-edit'],
              'style' => "border: #00008B 3px solid;",
            ],
          ];

          $form['names_fieldset_wrapper'][$voc->id()]['vid'] = [
            '#type' => 'textbox',
            '#title' => $this->t('Vocabulary'),
           // '#empty_option' => "- Select -",
            '#options' => $vocabulary,
            '#weight' => $i,
            '#default_value' => !empty($mapping_settings['names_fieldset_wrapper'][$voc->id()]['vid']) ? $mapping_settings['names_fieldset_wrapper'][$voc->id()]['vid'] : $voc->id(),
            '#disabled' => TRUE,
          ];

          $form['names_fieldset_wrapper'][$voc->id()]['terms'] = [
            '#type' => 'checkboxes',
            '#title' => $this->t('Vocabulary Terms'),
           // '#empty_option' => "- Select -",
            '#weight' => $i,
            '#multiple' => TRUE,
            '#options' => $this->getTermsByVocabulary($voc->id(), $triggering_element),
            '#default_value' => !empty($mapping_settings['names_fieldset_wrapper'][$voc->id()]['terms']) ? $mapping_settings['names_fieldset_wrapper'][$voc->id()]['terms'] : $voc->id(),
          ];

          $form['names_fieldset_wrapper'][$voc->id()]['enable_override'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('Enable Mapping'),
            '#default_value' => $mapping_settings['names_fieldset_wrapper'][$voc->id()]['enable_override'],
            '#weight' => $i,
          ];

          $i++;
    }

    return parent::buildForm($form, $form_state);
  }

  protected function getTermsByVocabulary($vid, $triggering_element) {
    $options = [];
    foreach (\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid) as $item) {
      $options[$item->tid] = str_repeat('-', $item->depth) . $item->name;
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config(self::Taxonomy_TERMS_SETTINGS)
      ->set('mapping', $form_state->cleanValues()->getValues())
      ->save();

    parent::submitForm($form, $form_state);
  }

}
