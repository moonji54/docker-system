/* global jQuery */

/*
 * Note: Be very careful what you import here, only import things that are ONLY used in this module and no other modules.
 * For example, if you were to do import jquery to use $:
 * `import $ from 'jquery';`
 * jQuery would be compiled in to the ES5 module for this file - ...and each and every file you build that imports jquery! Loading it multiple times!
 * jQuery is being loaded as a library by Drupal, so you should never need to import it.
 *
 * Further Reading:
 * https://www.lullabot.com/articles/understanding-javascript-behaviors-in-drupal
 */

class Test {
  constructor(context, settings, Drupal, testVariable) {
    // The context is the piece of the DOM that has been rendered before
    // Drupal.behaviors are invoked. Typicall this is the `document`
    this.context = context;

    // The settings passed to Javascript by Drupal
    this.settings = settings;

    // The Drupal core object that manages everything!
    // this.Drupal = Drupal;

    // use `jQuery('.selector') to select DOM elements
    jQuery('.c-test').text('Is $() working inside the class constructor?');
    console.log(jQuery('.c-test', this.context).once());

    // Context
    // - use this.context with your jQuery selector
    // - jQuery will find the document object in the context variable only the
    // first time that Drupal.attachBehaviors() is called

    // jQuery Once()
    // - use .once() after your jQuery selector
    // - This is the recommended approach at the Drupal.org JavaScript
    // documentation. (https://www.drupal.org/node/756722#jquery-once) jQuery
    // Once makes sure that something is processed just one time by adding a
    // class on a DOM element after the code has run.

    // Call methods like you would normally!
    this.message();
  }

  message() {
    console.log('Is $() working inside class functions?');
    console.log(jQuery('.c-test', this.context).once());

    // jQuery() works inside our classes methods!
    // - $() doesn't work!
  }
}

export default Test;
