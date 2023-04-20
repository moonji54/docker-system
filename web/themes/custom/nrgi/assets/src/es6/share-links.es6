/* global jQuery Drupal */
import ShareLinks from './modules/share-links';

(function ($, Drupal) {
    // eslint-disable-next-line no-param-reassign
    Drupal.behaviors.ShareLinks = {
        attach (context, settings) {
            new ShareLinks(context, settings, $, Drupal);
        }
    };
}(jQuery, Drupal));
