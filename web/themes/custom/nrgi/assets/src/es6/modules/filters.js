class Filters {
    constructor (context, settings, $, Drupal) {
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

    toggleFilterItems (e) {
        const $elem = this.$(e.currentTarget);
        const $filters = $elem.next(this.$filterItems);

        $elem.toggleClass('is-clicked');

        $filters.slideToggle(() => {
            $filters.toggleClass('is-open');
        });

        if ($elem.hasClass('is-clicked')) {
            return $elem.attr('aria-expanded', 'true');
        }

        return $elem.removeAttr('aria-expanded');
    }
}

export default Filters;
