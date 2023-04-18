/* global jQuery Drupal */
import LanguagesOverlay from './modules/languages-overlay';

(function ($, Drupal) {
    // eslint-disable-next-line no-param-reassign
    Drupal.behaviors.LanguagesOverlay = {
        attach (context, settings) {
            new LanguagesOverlay(context, settings, $, Drupal);
        }
    };
}(jQuery, Drupal));
