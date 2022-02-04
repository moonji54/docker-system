/* global jQuery Drupal */
import Example from './modules/example';

(function ($, Drupal) {
  // eslint-disable-next-line no-param-reassign
  Drupal.behaviors.example = {
    attach(context, settings) {
      new Example(context, settings, Drupal, 'testing-variable-here');
    }
  };
}(jQuery, Drupal));
