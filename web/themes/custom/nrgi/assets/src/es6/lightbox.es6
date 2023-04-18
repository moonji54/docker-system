/* global jQuery Drupal */
import Lightbox from './modules/lightbox';

(function ($, Drupal) {
    // eslint-disable-next-line no-param-reassign
    Drupal.behaviors.lightbox = {
        attach (context, settings) {
            new Lightbox(context, settings, $, Drupal);
        }
    };

})(jQuery, Drupal);
