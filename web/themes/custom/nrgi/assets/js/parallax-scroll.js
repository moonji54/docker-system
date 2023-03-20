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
    this.$root = $(':root');
    this.$window = this.$(window);
    this.$parallaxContainer = this.$('.js-parallax-container');
    this.$window.on('scroll', (0, _debounce["default"])(function () {
      _this.applyClassOnScroll();
    }));
  }
  _createClass(ParallaxScroll, [{
    key: "applyClassOnScroll",
    value: function applyClassOnScroll() {
      var _this2 = this;
      var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            _this2.$(entry.target).addClass('is-visible');
            _this2.$root.css({
              "--scroll": window.scrollY / document.body.offsetHeight
            });
          } else {
            _this2.$(entry.target).removeClass('is-visible');
          }
        });
      });
      observer.observe(this.$parallaxContainer[0]);
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
/* global jQuery Drupal */

(function ($, Drupal) {
  // eslint-disable-next-line no-param-reassign
  Drupal.behaviors.ParallaxScroll = {
    attach: function attach(context, settings) {
      new _parallaxScroll["default"](context, settings, $, Drupal);
    }
  };
})(jQuery, Drupal);

},{"./modules/parallax-scroll":1}]},{},[3])

