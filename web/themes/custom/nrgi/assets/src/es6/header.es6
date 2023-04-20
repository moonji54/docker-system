/* global jQuery Drupal */
import Header from './modules/header';

(function ($, Drupal, once) {
    // eslint-disable-next-line no-param-reassign
    Drupal.behaviors.nrgiHeader = {
        attach: function (context, settings) {
            once('nrgiHeader', 'html', context).forEach(function (element) {
                new Header(context, settings, $, Drupal, element);
            });
        }
    };
}(jQuery, Drupal, once));
