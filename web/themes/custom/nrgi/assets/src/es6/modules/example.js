class Example {
  constructor($, element) {
    this.$ = $;
    this.$window = this.$(window);
    this.$container = this.$(element);
    this.$button = this.$container.find('.js-button');

    this.$button.on('click', this.$.proxy(this.checkScrollbarsResize, this));

    this.$contentFromEditor = this.$(element);
    this.wrapTable();
  }

  wrapTable() {
    const $tables = this.$contentFromEditor.find('table');

    if ($tables) {
      $tables.wrap('<figure class="table-wrapper"></figure>');
    }
  }
}

export default Example;
