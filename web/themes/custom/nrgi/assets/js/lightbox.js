(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
"use strict";

var _lightbox = _interopRequireDefault(require("./modules/lightbox"));
function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
/* global jQuery Drupal */

(function ($, Drupal) {
  // eslint-disable-next-line no-param-reassign
  Drupal.behaviors.lightbox = {
    attach: function attach(context, settings) {
      new _lightbox["default"](context, settings, $, Drupal);
    }
  };
})(jQuery, Drupal);

},{"./modules/lightbox":2}],2:[function(require,module,exports){
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
var Lightbox = /*#__PURE__*/function () {
  function Lightbox(context, settings, $, Drupal) {
    _classCallCheck(this, Lightbox);
    this.$ = $;
    this.settings = settings;
    this.Drupal = Drupal;
    this.context = context;
    this.$window = this.$(window);
    this.$image = this.$('.js-text-block').find(this.$('.cboxElement')).once();
    this.$(context).on('cbox_complete', this.$.proxy(this.lightboxComplete, this));
    this.$(context).on('cbox_closed', this.$.proxy(this.lightboxClosed, this));
    this.$image.on('click', this.$.proxy(this.createCaption, this));
  }
  _createClass(Lightbox, [{
    key: "createCaption",
    value: function createCaption(e) {
      this.$('#cboxTitle').hide();
      var target = this.$(e.currentTarget);
      var targetCaption = this.$(target).closest(this.$('.c-media--image')).next('figcaption')[0];
      var targetCaptionText = this.$(targetCaption).text();
      var createCaption = '<p class="cboxCaption"></p>';
      var newCaption = this.$(createCaption).text(targetCaptionText);
      if (this.$('.cboxCaption').length === 0) {
        this.$('#cboxContent').prepend(newCaption);
      } else {
        this.$('.cboxCaption').text(targetCaptionText);
      }
    }
  }, {
    key: "lightboxComplete",
    value: function lightboxComplete() {
      // Make all the controls invisible.
      this.$('#cboxCurrent, #cboxSlideshow, #cboxPrevious, #cboxNext').addClass('visually-hidden');
      // Replace "Close" with "×" and show.
      this.$('#cboxClose').html("\xD7").addClass('cbox-close-plain');
      // Hide empty title.
      this.$('#cboxTitle').hide();
      this.$('body').addClass('is-scroll-locked');
    }
  }, {
    key: "lightboxClosed",
    value: function lightboxClosed() {
      this.$('#cboxClose').removeClass('cbox-close-plain');
      this.$('body').removeClass('is-scroll-locked');
    }
  }]);
  return Lightbox;
}();
var _default = Lightbox;
exports["default"] = _default;

},{}]},{},[1])

