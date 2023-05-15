/* global jQuery Drupal */
import PdfContents from "./modules/pdf-contents";

(function ($, Drupal) {
    // eslint-disable-next-line no-param-reassign
    Drupal.behaviors.pdfcontents = {
        attach(context, settings) {
            once('pdf-contents', 'html', context).forEach(function (element) {
                new PdfContents($, element);
            });
        }
    };
}(jQuery, Drupal));
