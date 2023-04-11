(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
"use strict";

var _filters = _interopRequireDefault(require("./modules/filters"));
function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
/* global jQuery Drupal */

(function ($, Drupal) {
  // eslint-disable-next-line no-param-reassign
  Drupal.behaviors.filters = {
    attach: function attach(context, settings) {
      new _filters["default"](context, settings, $, Drupal);
    }
  };
})(jQuery, Drupal);

},{"./modules/filters":2}],2:[function(require,module,exports){
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
var Filters = /*#__PURE__*/function () {
  function Filters(context, settings, $, Drupal) {
    _classCallCheck(this, Filters);
    this.context = context;
    this.settings = settings;
    this.Drupal = Drupal;
    this.$ = $;
    this.$window = this.$(window);
    this.$filterButton = this.$('.js-filter-button', this.context).once();
    this.$filterItems = this.$('.js-filter-items', this.context).once();
    this.$toggleFiltersButton = this.$('.js-toggle-filters-button', this.context).once();
    this.$exposedFilters = this.$('.views-exposed-form', this.context).once();
    this.$filterDropdowns = this.$('.js-filter-dropdowns', this.context).once();
    this.$filterButton.on('click', this.$.proxy(this.toggleFilterItems, this));
  }
  _createClass(Filters, [{
    key: "toggleFilterItems",
    value: function toggleFilterItems(e) {
      var $elem = this.$(e.currentTarget);
      var $filters = $elem.next(this.$filterItems);
      $elem.toggleClass('is-clicked');
      $filters.slideToggle(function () {
        $filters.toggleClass('is-open');
      });
      if ($elem.hasClass('is-clicked')) {
        return $elem.attr('aria-expanded', 'true');
      }
      return $elem.removeAttr('aria-expanded');
    }
  }]);
  return Filters;
}();
var _default = Filters;
exports["default"] = _default;

},{}]},{},[1])

