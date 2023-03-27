/* global jQuery Drupal */
import Header from './modules/header';

(function ($, Drupal) {
    // eslint-disable-next-line no-param-reassign
    Drupal.behaviors.Header = {
        attach: function (context, settings) {
            new Header(context, settings, $, Drupal);
        }
    }
}(jQuery, Drupal));
