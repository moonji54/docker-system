/* global jQuery, Drupal, drupalSettings, once */
import ParallaxScroll from './modules/parallax-scroll';

// Example using once() to select the `element` and init a class from that element.
(function ($, Drupal, once) {
    // eslint-disable-next-line no-param-reassign
    Drupal.behaviors.ParallaxScroll = {
        /**
         * Behaviour attach
         * @param context
         *   The context is the piece of the DOM that has been rendered before Drupal.behaviors are invoked. (It should have your HTML in it!)
         * @param settings
         *   This will be the same DrupalSettings.
         */
        attach (context, settings) {
            /**
             * Once()
             * @param identifier
             *   The ID of this once instance (just think of it as a way to keep track of which ones have triggered).
             * @param selector
             *   The selector of the element you want to interact with (use a class or an id)
             * @param context
             *   The context from the Drupal behaviour, don't remove this.
             */
            once('ParallaxScroll', 'html', context)
                // Loop through the found elements (the things that match the selector in Param 2).
                .forEach((element) => {
                    // Trigger our class for each element
                    // - Param 1 - Pass jQuery to the class
                    // - Param 2 - The single element from our for each loop.
                    new ParallaxScroll(context, settings, $, Drupal, element);
                });
        }
    };

    /**
     * Based on your libraries.yml this script may have access to:
     * - Drupal
     *     - The Drupal object, in here you'll find lots of things but the ones you'll probably come in to contact with are things like behaviors, ajax.
     *
     * - drupalSettings
     *     - An object used to pass variables from PHP to Javascript.
     *
     * - jQuery
     *     - The jQuery instance from Drupal. (Pass this around your classes, don't import jQuery multiple times!).
     */
}(jQuery, Drupal, once));
