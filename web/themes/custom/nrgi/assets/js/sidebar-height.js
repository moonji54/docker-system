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
var SidebarHeight = /*#__PURE__*/function () {
  function SidebarHeight(context, settings, $, Drupal, element) {
    var _this = this;
    _classCallCheck(this, SidebarHeight);
    // Values from Drupal
    this.context = context;
    this.settings = settings;
    this.$ = $;
    this.Drupal = Drupal;
    this.element = element;
    this.$window = this.$(window);
    this.$topContainer = this.$('.js-sidebar-container', this.context).once();
    this.$sidebar = this.$('.js-sidebar', this.context);
    this.$window.on('load resize', (0, _debounce["default"])(function () {
      _this.heightCalculation();
    }));
  }
  _createClass(SidebarHeight, [{
    key: "heightCalculation",
    value: function heightCalculation() {
      var sidebarHeight = this.$sidebar.height();
      var containerHeight = this.$topContainer.height();
      var $heightDiff = sidebarHeight - containerHeight;
      var nextSibling = this.$topContainer.next();
      if (containerHeight === 0 && !nextSibling.hasClass('c-text-block')) {
        this.$(':root').css({
          '--sidebar-height': "".concat($heightDiff, "px")
        });
      }
    }
  }]);
  return SidebarHeight;
}();
var _default = SidebarHeight;
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

var _sidebarHeight = _interopRequireDefault(require("./modules/sidebar-height"));
function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
/* global jQuery Drupal */

(function ($, Drupal, once) {
  // eslint-disable-next-line no-param-reassign
  Drupal.behaviors.SidebarHeight = {
    attach: function attach(context, settings) {
      once('SidebarHeight', 'html', context).forEach(function (element) {
        new _sidebarHeight["default"](context, settings, $, Drupal, element);
      });
    }
  };
})(jQuery, Drupal, once);

},{"./modules/sidebar-height":1}]},{},[3])

