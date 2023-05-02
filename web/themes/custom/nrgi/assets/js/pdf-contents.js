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
var PdfContents = /*#__PURE__*/function () {
  function PdfContents($, context) {
    _classCallCheck(this, PdfContents);
    this.$ = $;
    this.context = context;

    // Elements
    this.$pdfContentsWrapper = this.$('.js-table-of-contents-wrapper', this.context);
    this.$pdfWysiwyg = this.$('.js-text-block', this.context);
    this.$pdfContentsList = this.$('#pdf-contents', this.context);
    this.$pdfWysiwygHeadingTwos = this.$pdfWysiwyg.find('h2');
    if (this.$pdfWysiwygHeadingTwos.length >= 1) {
      this.printAllHeadings();
    } else {
      this.$pdfContentsWrapper.css('display', 'none');
    }

    // Start page to pagedjs to resize the entire page.
    window.PagedPolyfill.preview();
  }
  _createClass(PdfContents, [{
    key: "printAllHeadings",
    value: function printAllHeadings() {
      var _this = this;
      var wysiwygs = this.$pdfWysiwyg;
      var mainChapterNumber = 0;
      var secondaryChapterNumber = 0;
      wysiwygs.each(function (index, element) {
        var headings = _this.$(element).find('h2, h3');
        headings.each(function (subIndex, subElement) {
          // this.$headings.push(subElement);
          // Find the text in each
          var $jQueryEl = _this.$(subElement);
          var headingText = $jQueryEl.text();

          // Create an ID for use on each heading
          var headingID = headingText.toLowerCase().replace(/\s/g, '-');

          // Add an ID to each h2
          $jQueryEl.attr('id', headingID);

          // Found element tag name
          var subElementTagName = subElement.tagName.toLowerCase();

          // Let's increase the chapter numbers based on what we have found
          var chapterNumber = '';
          switch (subElementTagName) {
            case 'h2':
              mainChapterNumber++;
              chapterNumber = mainChapterNumber;
              secondaryChapterNumber = 0;
              break;
            case 'h3':
              secondaryChapterNumber++;
              chapterNumber = "".concat(mainChapterNumber, ".").concat(secondaryChapterNumber);
              break;
          }
          _this.$pdfContentsList.append(_this.buildListItem(headingID, headingText, subElementTagName));
        });
      });
    }
  }, {
    key: "buildListItem",
    value: function buildListItem(headingTarget, headingText, tagName) {
      var listItem;
      switch (tagName) {
        case 'h2':
          listItem = "<li class=\"c-pdf__toc-item-heading\">\n                    <a href=\"#".concat(headingTarget, "\">").concat(headingText, "</a>\n                </li>");
          break;
        case 'h3':
          listItem = "<li class=\"c-pdf__toc-item-heading c-pdf__toc-item-heading--sub\">\n                    <a href=\"#".concat(headingTarget, "\">").concat(headingText, "</a>\n                </li>");
          break;
      }
      return listItem;
    }
  }]);
  return PdfContents;
}();
var _default = PdfContents;
exports["default"] = _default;

},{}],2:[function(require,module,exports){
"use strict";

var _pdfContents = _interopRequireDefault(require("./modules/pdf-contents"));
function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
/* global jQuery Drupal */

(function ($, Drupal) {
  // eslint-disable-next-line no-param-reassign
  Drupal.behaviors.pdfcontents = {
    attach: function attach(context, settings) {
      once('pdf-contents', 'html', context).forEach(function (element) {
        new _pdfContents["default"]($, element);
      });
    }
  };
})(jQuery, Drupal);

},{"./modules/pdf-contents":1}]},{},[2])

