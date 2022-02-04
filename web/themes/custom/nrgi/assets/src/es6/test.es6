/* global jQuery Drupal */
// Import your modules
import Test from './modules/test';

(function ($, Drupal) {
  // Drupal.behaviors is the Drupal version of an onload event handler (sort of)
  // Make sure your new behaviour has a unique name
  // - Drupal.behaviors.NameHere
  Drupal.behaviors.test = {
    attach (context, settings) {
      // Make a new instance of your imported ES6 class
      new Test(context, settings, Drupal, 'testing-variable-here');
    }
  };
})(jQuery, Drupal);


/*
 * How to use
 * - Add to the gulpfile.js' build-js task
 * - Refresh PHPstorms gulp task list and build, you should now have a .js file!
 * - Add you ES5 js file to {project}.libraries.yml
 */
