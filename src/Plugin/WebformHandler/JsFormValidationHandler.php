<?php
/**
 * Created by PhpStorm.
 * User: artem
 * Date: 26/01/2017
 * Time: 15:22
 */

namespace Drupal\webform_js_validation\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\webform\Utility\WebformElementHelper;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;


/**
 * Add javascript form validation
 *
 * @WebformHandler(
 *   id = "js_form_validation",
 *   label = @Translation("Javascript Form Validation"),
 *   category = @Translation("Form Alters"),
 *   description = @Translation("Adds javascript form validation"),
 * )
 */
class JsFormValidationHandler extends WebformHandlerBase {
  /**
   * Implements alterForm() method for fixing states from another pages.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param \Drupal\webform\WebformSubmissionInterface $webform_submission
   */
  public function alterForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
    $form['#attributes']['novalidate'] = 'novalidate';
    if (!empty($form['actions']['next']['#ajax'])) {
      $form['actions']['next']['#attributes']['class'][] = 'validation-hide-me';
      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $form['actions']['next']['#value'],
        '#attributes' => [
          'class' => [
            'substitution',
          ]
        ]
      ];
    }
    $form['#attached']['library'][] = 'webform_js_validation/form_validation';
    // get current page.
    $current_page = $webform_submission->getCurrentPage();
    if (!$current_page) {
      $pages = $this->getWebform()->getPages();
      $pageKeys = array_keys($pages);
      $current_page = array_shift($pageKeys);
    }
    // get form elements.
    $elements = $webform_submission->getWebform()->getElementsDecoded();
    $current_page_elements = [];
    if (!empty($elements[$current_page])) {
      // get flattened the elements only from current page.
      $this->getElementsForCurrentPageFlattened($elements[$current_page], $current_page_elements);
      // fix the states of the elements
      if (!empty($form['elements'][$current_page])) {
        $this->setValidation($form['elements'][$current_page], $webform_submission, $current_page_elements);
      }
    }
  }


  /**
   * Recursively goes through all the elements to find the states and fix.
   *
   * @param array $elements
   * @param \Drupal\webform\WebformSubmissionInterface $webform_submission
   * @param $page_elements
   */
  protected function setValidation(array &$elements, WebformSubmissionInterface $webform_submission, $page_elements) {
    foreach ($elements as $key => $element) {
      if (Element::property($key) || !is_array($element)) {
        continue;
      }
      $elementProperties = WebformElementHelper::getProperties($element);
      if (!empty($elementProperties['#required'])) {
        // ensure every required element has the correct class
        $elements[$key]['#attributes']['class'][] = 'required';
        if (!empty($elementProperties['#required_error']) && $elementProperties['#required_error'] != '') {
          $validationMessage = $elementProperties['#required_error'];
        } else {
          $validationMessage = $this->t('Field @field is requried', ['@field' => $elementProperties['#title']]);
        }
        if (!isset($elements[$key]['#description'])) {
          $elements[$key]['#description'] = '';
        }
        $elements[$key]['#description'] .= '<p class="validation for-' . $elementProperties['#webform_id'].  '">' . $validationMessage;
        if ($elements[$key]['#type'] == 'email') {
          $validationMessage = $this->t('Email address is not valid.');
          $elements[$key]['#description'] .= '<span class="email">' . $validationMessage . '</span>';
        }
        $elements[$key]['#description'] .= '</p>';
      }
      if (!empty($elementProperties['#pattern'])) {
        $elements[$key]['#attributes']['class'][] = 'pattern-validation';
        $validationMessage = $this->t('Field @field is using wrong format', ['@field' => $elementProperties['#title']]);
        if (!isset($elements[$key]['#description'])) {
          $elements[$key]['#description'] = '';
        }
        $elements[$key]['#description'] .= '<p class="validation pattern for-' . $elementProperties['#webform_id'].  '">'. $validationMessage . '</p>';
      }
      $this->setValidation($elements[$key], $webform_submission, $page_elements);
    }
  }

  /**
   * Get flattened form element for current page.
   *
   * @param $elements
   * @param array $current_page_elements
   */
  protected function getElementsForCurrentPageFlattened($elements, array &$current_page_elements) {
    foreach ($elements as $key => $element) {
      if (Element::property($key) || !is_array($element)) {
        continue;
      }
      $current_page_elements[$key] = WebformElementHelper::getProperties($element);
      $this->getElementsForCurrentPageFlattened($element, $current_page_elements);
    }
  }

}