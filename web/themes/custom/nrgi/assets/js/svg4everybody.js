/**
 * Svg4everybody.
 */
(function ($, Drupal) {
  'use strict';
  Drupal.behaviors.svg4everybody = {
    attach : function (context, settings) {
      svg4everybody();
    }
  };
})(jQuery, Drupal);
