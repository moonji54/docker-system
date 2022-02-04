/* global jQuery */

class Example {
  constructor(context, settings, Drupal, testVariable) {
    // Values from Drupal
    this.context = context;
    this.settings = settings;
    // this.Drupal = Drupal;

    // Our Variables
    this.testVariable = testVariable;

    // Elements
    this.$wrapper = jQuery('.js-example', this.context).once();

    console.log(this.$wrapper);

    this.message();
  }

  message() {
    console.log('Is jQuery working inside class functions?');
    console.log(jQuery('.js-example', this.context).once());
    console.log(this.$wrapper);
  }
}

export default Example;
