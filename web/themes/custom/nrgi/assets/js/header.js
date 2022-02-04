/**
 * Header
 */
(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.header = {
    attach : function (context, settings) {
      var $responsiveMenu = $('.js-responsive-menu', context).once();
      var $menuBurger     = $('.js-header-burger', context).once();
      var $burgerText     = $('.js-burger-text', context).once();
      var $nonMenu        = $('#js-non-menu', context).once();
      var $chevron        = $('.js-responsive-menu-toggle', context).once();


      function changeMenuText () {
        // Change button text
        if ($burgerText.text() === 'Menu') {
          $burgerText.text('Close');
        } else {
          $burgerText.text('Menu');
        }
      }

      // Toggle responsive nav.
      $menuBurger.on('click', function (event) {
        event.preventDefault();

        changeMenuText();
        $responsiveMenu.slideToggle(300);
        $nonMenu.toggleClass('is-fixed');
        $(this).toggleClass('is-open');
      });

      // Toggle elements within responsive nav.
      $chevron.on('click', function (event) {

        event.preventDefault();

        $(this).parent().next().slideToggle();
        $(this).toggleClass('is-open');
        $(this).parent().toggleClass('is-open');
      });
    }
  };
})(jQuery, Drupal);
