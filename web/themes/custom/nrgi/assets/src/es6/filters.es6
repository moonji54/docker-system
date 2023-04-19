/* global jQuery Drupal */

import Filters from './modules/filters';

(function ($, Drupal) {
    // eslint-disable-next-line no-param-reassign
    Drupal.behaviors.filters = {
        attach (context, settings) {
            new Filters(context, settings, $, Drupal);
        }
    };
}(jQuery, Drupal));
