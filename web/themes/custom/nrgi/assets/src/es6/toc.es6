/* global jQuery Drupal */
import Toc from './modules/toc';

(function ($, Drupal) {
    // eslint-disable-next-line no-param-reassign
    Drupal.behaviors.toc = {
        attach (context, settings) {
            once('toc', 'html', context).forEach(function (element) {
                new Toc($, element);
            });
        }
    };
}(jQuery, Drupal));
