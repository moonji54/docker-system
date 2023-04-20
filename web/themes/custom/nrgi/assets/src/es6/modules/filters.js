import debounce from './utils/debounce';

class Filters {
    constructor (context, settings, $, Drupal) {
        this.context = context;
        this.settings = settings;
        this.Drupal = Drupal;
        this.$ = $;

        this.$window = this.$(window);
        this.$document = this.$(document);

        this.$filterButton = this.$('.js-filter-button', this.context).once();
        this.$filterItems = this.$('.js-filter-items', this.context).once();
        this.$toggleFiltersButton = this.$('.js-toggle-filters-button', this.context).once();
        this.$exposedFilters = this.$('.views-exposed-form', this.context).once();
        this.$filterDropdowns = this.$('.js-filter-dropdowns', this.context).once();

        this.$filterButton.on('click', this.$.proxy(this.toggleFilterItems, this));
        this.$toggleFiltersButton.on('click', this.$.proxy(this.toggleFilters, this));

        this.$window.on('resize', debounce(() => {
            if (this.$window.width() >= 768) {
                this.resetFilters();
            }
        }));

        this.$document.keyup((e) => {
            if (e.key === 'Escape') {
                this.closeDropdown();
            }

            if (e.key === 'Enter') {
                this.toggleOnFocus();
            }
        });
        this.addTabindex();
    }

    resetFilters () {
        this.$filterDropdowns.removeAttr('style');
        this.$toggleFiltersButton.removeClass('is-hidden');
        this.$toggleFiltersButton.attr('aria-expanded', 'true');
    }

    toggleFilters (e) {
        const $elem = this.$(e.currentTarget);
        const $filters = $elem.next(this.$exposedFilters).find(this.$filterDropdowns);

        $elem.toggleClass('is-clicked');
        $filters.slideToggle();

        if ($elem.hasClass('is-clicked')) {
            return $elem.removeAttr('aria-expanded');
        }

        return $elem.attr('aria-expanded', 'true');
    }

    toggleFilterItems (e) {
        const $elem = this.$(e.currentTarget);

        $elem.toggleClass('is-clicked').attr('aria-expanded', 'true');

        if (!$elem.hasClass('is-clicked')) {
            $elem.attr('aria-expanded', 'false');
            $elem.next().removeClass('is-visible');
            $elem.next().slideUp(300);
        } else {
            $elem.next().addClass('is-visible');
            $elem.next().slideDown(300);
        }
    }

    toggleOnFocus () {
        if (this.$filterButton.is(':focus')) {
            this.$filterButton.trigger('click');
        }
    }

    closeDropdown () {
        this.$filterButton.removeClass('is-clicked').attr('aria-expanded', 'false');
        this.$filterItems.removeClass('is-visible');
    }

    addTabindex () {
        this.$filterButton.attr('tabindex', 0).attr('aria-expanded', 'false');
    }
}

export default Filters;
