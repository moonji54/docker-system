(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

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
var Test =
/*#__PURE__*/
function () {
  function Test(context, settings, Drupal, testVariable) {
    _classCallCheck(this, Test);

    // The context is the piece of the DOM that has been rendered before
    // Drupal.behaviors are invoked. Typicall this is the `document`
    this.context = context; // The settings passed to Javascript by Drupal

    this.settings = settings; // The Drupal core object that manages everything!
    // this.Drupal = Drupal;
    // use `jQuery('.selector') to select DOM elements

    jQuery('.c-test').text('Is $() working inside the class constructor?');
    console.log(jQuery('.c-test', this.context).once()); // Context
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

  _createClass(Test, [{
    key: "message",
    value: function message() {
      console.log('Is $() working inside class functions?');
      console.log(jQuery('.c-test', this.context).once()); // jQuery() works inside our classes methods!
      // - $() doesn't work!
    }
  }]);

  return Test;
}();

var _default = Test;
exports.default = _default;

},{}],2:[function(require,module,exports){
"use strict";

var _test = _interopRequireDefault(require("./modules/test"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

/* global jQuery Drupal */
// Import your modules
(function ($, Drupal) {
  // Drupal.behaviors is the Drupal version of an onload event handler (sort of)
  // Make sure your new behaviour has a unique name
  // - Drupal.behaviors.NameHere
  Drupal.behaviors.test = {
    attach: function attach(context, settings) {
      // Make a new instance of your imported ES6 class
      new _test.default(context, settings, Drupal, 'testing-variable-here');
    }
  };
})(jQuery, Drupal);

},{"./modules/test":1}]},{},[2])

