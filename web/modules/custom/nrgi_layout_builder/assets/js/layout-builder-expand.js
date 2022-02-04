Drupal.behaviors.layoutBuilderExpand = {
  attach: function (context, settings) {
    jQuery('form.node-form').on('click', '.js-layout-expand', function (event) {
      event.preventDefault();
      jQuery('.layout-node-form').addClass('is-expanded');
    });
    jQuery('form.node-form').on('click', '.js-layout-collapse', function (event) {
      event.preventDefault();
      jQuery('.layout-node-form').removeClass('is-expanded');
    });
  }
};
