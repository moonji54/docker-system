(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, _toPropertyKey(descriptor.key), descriptor); } }
function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); Object.defineProperty(Constructor, "prototype", { writable: false }); return Constructor; }
function _toPropertyKey(arg) { var key = _toPrimitive(arg, "string"); return _typeof(key) === "symbol" ? key : String(key); }
function _toPrimitive(input, hint) { if (_typeof(input) !== "object" || input === null) return input; var prim = input[Symbol.toPrimitive]; if (prim !== undefined) { var res = prim.call(input, hint || "default"); if (_typeof(res) !== "object") return res; throw new TypeError("@@toPrimitive must return a primitive value."); } return (hint === "string" ? String : Number)(input); }
var SubNavigation = /*#__PURE__*/function () {
  function SubNavigation(context, settings, $, Drupal, element) {
    _classCallCheck(this, SubNavigation);
    // Values from Drupal
    this.context = context;
    this.settings = settings;
    this.$ = $;
    this.Drupal = Drupal;
    this.element = element;
    this.speed = 300;
    this.$button = this.$('.js-sub-navigation-button', this.context).once();
    this.$button.on('click', this.$.proxy(this.toggleContent, this));
  }
  _createClass(SubNavigation, [{
    key: "toggleContent",
    value: function toggleContent(e) {
      var $elem = this.$(e.currentTarget);
      $elem.attr('aria-expanded', 'true');
      $elem.toggleClass('is-open').next().slideToggle(this.speed);
      if (!$elem.hasClass('is-open')) {
        $elem.attr('aria-expanded', 'false');
      }
    }
  }]);
  return SubNavigation;
}();
var _default = SubNavigation;
exports["default"] = _default;

},{}],2:[function(require,module,exports){
"use strict";

var _subNavigation = _interopRequireDefault(require("./modules/sub-navigation"));
function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
/* global jQuery Drupal */

(function ($, Drupal, once) {
  // eslint-disable-next-line no-param-reassign
  Drupal.behaviors.SubNavigation = {
    attach: function attach(context, settings) {
      once('SubNavigation', 'html', context).forEach(function (element) {
        new _subNavigation["default"](context, settings, $, Drupal, element);
      });
    }
  };
})(jQuery, Drupal, once);

},{"./modules/sub-navigation":1}]},{},[2])

