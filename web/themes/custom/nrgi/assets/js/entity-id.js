(function () {
    function r (e, n, t) {
        function o (i, f) {
            if (!n[i]) {
                if (!e[i]) {
                    var c = "function" == typeof require && require;
                    if (!f && c) return c(i, !0);
                    if (u) return u(i, !0);
                    var a = new Error("Cannot find module '" + i + "'");
                    throw a.code = "MODULE_NOT_FOUND", a
                }
                var p = n[i] = {exports: {}};
                e[i][0].call(p.exports, function (r) {
                    var n = e[i][1][r];
                    return o(n || r)
                }, p, p.exports, r, e, n, t)
            }
            return n[i].exports
        }

        for (var u = "function" == typeof require && require, i = 0; i < t.length; i++) o(t[i]);
        return o
    }

    return r
})()({
    1: [function (require, module, exports) {
        "use strict";

        (function ($, Drupal) {
            'use strict';
            /**
             * Remove entity reference ID from "entity_autocomplete" field.
             *
             * @type {{attach: Drupal.behaviors.autocompleteReferenceEntityId.attach}}
             */

            Drupal.behaviors.autocompleteReferenceEntityId = {
                attach: function attach (context) {
                    // Remove reference IDs for autocomplete elements on init.
                    $('.form-autocomplete', context).once('replaceReferenceIdOnInit').each(function () {
                        var splitValues = this.value && this.value !== 'false' ? Drupal.autocomplete.splitValues(this.value) : [];

                        if (splitValues.length > 0) {
                            var labelValues = [];

                            for (var i in splitValues) {
                                var value = splitValues[i].trim();
                                var entityIdMatch = value.match(/\s*\((.*?)\)$/);

                                if (entityIdMatch) {
                                    labelValues[i] = value.replace(entityIdMatch[0], '');
                                }
                            }

                            if (labelValues.length > 0) {
                                $(this).data('real-value', splitValues.join(', '));
                                this.value = labelValues.join(', ');
                            }
                        }
                    });
                }
            };
            var autocomplete = Drupal.autocomplete.options;
            autocomplete.originalValues = [];
            autocomplete.labelValues = [];
            /**
             * Add custom select handler.
             */

            autocomplete.select = function (event, ui) {
                autocomplete.labelValues = Drupal.autocomplete.splitValues(event.target.value);
                autocomplete.labelValues.pop();
                autocomplete.labelValues.push(decodeHtml(ui.item.label));
                autocomplete.originalValues.push(ui.item.value);
                $(event.target).data('real-value', autocomplete.originalValues.join(', '));
                event.target.value = autocomplete.labelValues.join(', ');
                return false;
            };

            var decodeHtml = function decodeHtml (html) {
                var txt = document.createElement('textarea');
                txt.innerHTML = html;
                return txt.value;
            };
        })(jQuery, Drupal);

    }, {}]
}, {}, [1])
