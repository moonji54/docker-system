class Filters {
    constructor (context, settings, $, Drupal) {
        this.context = context;
        this.settings = settings;
        this.Drupal = Drupal;
        this.$ = $;
        
        this.$document = this.$(document);

        this.$filterButton = this.$('.js-filter-button', this.context).once();
        this.$filterItems = this.$('.js-filter-items', this.context).once();

        this.$filterButton.on('click', this.$.proxy(this.toggleFilterItems, this));

        this.$document.keyup((e) => {
            if (e.key === "Escape") {
                this.closeDropdown();
            }

            if (e.key === "Enter") {
                this.toggleOnFocus();
            }
        });
        this.addTabindex();
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
