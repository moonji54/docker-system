(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;
var _debounce = _interopRequireDefault(require("./utils/debounce"));
function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, _toPropertyKey(descriptor.key), descriptor); } }
function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); Object.defineProperty(Constructor, "prototype", { writable: false }); return Constructor; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }
var ParallaxScroll = /*#__PURE__*/function () {
  function ParallaxScroll(context, settings, $, Drupal) {
    var _this = this;
    _classCallCheck(this, ParallaxScroll);
    // Values from Drupal
    this.context = context;
    this.settings = settings;
    this.$ = $;
    this.Drupal = Drupal;
    this.$window = this.$(window);
    this.$parallaxContainer = this.$('.js-parallax-container');
    this.$window.on('load scroll', (0, _debounce["default"])(function () {
      if (_this.$parallaxContainer.length && _this.$window.width() >= 768) {
        _this.applyClassOnScroll();
      }
    }));
  }
  _createClass(ParallaxScroll, [{
    key: "applyClassOnScroll",
    value: function applyClassOnScroll() {
      var _this2 = this;
      var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            _this2.translateCalc(entry);
          }
        });
      });
      this.$parallaxContainer.each(function (index, container) {
        observer.observe(container);
      });
    }
  }, {
    key: "translateCalc",
    value: function translateCalc(entry) {
      var parallaxBlock = this.$(entry.target).find(this.$('.js-parallax-block'));
      var entryRect = entry.target.getBoundingClientRect();
      var progress = 80 * ((entryRect.y - 200) / window.innerHeight);
      if (progress <= 0) {
        parallaxBlock[0].style.transform = 'translateY(0%)';
      } else {
        parallaxBlock[0].style.transform = "translateY(".concat(progress, "%)");
      }
    }
  }]);
  return ParallaxScroll;
}();
var _default = ParallaxScroll;
exports["default"] = _default;

},{"./utils/debounce":2}],2:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = _default;
/**
 * Simple debouncer
 *
 * @param {Function} func
 * @param {int} wait
 * @returns {Function}
 */
function _default(func) {
  var wait = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 0;
  var timeout;
  return function () {
    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
      args[_key] = arguments[_key];
    }
    var context = this;
    clearTimeout(timeout);
    timeout = setTimeout(function () {
      return func.apply(context, args);
    }, wait);
  };
}

},{}],3:[function(require,module,exports){
"use strict";

var _parallaxScroll = _interopRequireDefault(require("./modules/parallax-scroll"));
function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
/* global jQuery, Drupal, drupalSettings, once */

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
    attach: function attach(context, settings) {
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
      .forEach(function (element) {
        // Trigger our class for each element
        // - Param 1 - Pass jQuery to the class
        // - Param 2 - The single element from our for each loop.
        new _parallaxScroll["default"](context, settings, $, Drupal, element);
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
})(jQuery, Drupal, once);

},{"./modules/parallax-scroll":1}]},{},[3])

