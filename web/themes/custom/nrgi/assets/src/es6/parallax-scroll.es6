/* global jQuery Drupal */
import ParallaxScroll from './modules/parallax-scroll';

(function ($, Drupal) {
  // eslint-disable-next-line no-param-reassign
  Drupal.behaviors.ParallaxScroll = {
    attach(context, settings) {
      new ParallaxScroll(context, settings, $, Drupal);
    }
  };
}(jQuery, Drupal));
