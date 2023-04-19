/* global jQuery Drupal */
import SubNavigation from './modules/sub-navigation';

(function ($, Drupal, once) {
    // eslint-disable-next-line no-param-reassign
    Drupal.behaviors.SubNavigation = {
        attach: function (context, settings) {
            once('SubNavigation', 'html', context).forEach(function (element) {
                new SubNavigation(context, settings, $, Drupal, element);
            });
        }
    };
}(jQuery, Drupal, once));
