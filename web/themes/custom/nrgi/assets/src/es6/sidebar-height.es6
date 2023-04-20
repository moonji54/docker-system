/* global jQuery Drupal */
import SidebarHeight from './modules/sidebar-height';

(function ($, Drupal, once) {
    // eslint-disable-next-line no-param-reassign
    Drupal.behaviors.SidebarHeight = {
        attach: function (context, settings) {
            once('SidebarHeight', 'html', context).forEach(function (element) {
                new SidebarHeight(context, settings, $, Drupal, element);
            });
        }
    };
}(jQuery, Drupal, once));
