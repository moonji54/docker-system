import Ps from 'perfect-scrollbar';
import debounce from './utils/debounce';

class Header {
    constructor (context, settings, $, Drupal, element) {
        // Values from Drupal
        this.context = context;
        this.settings = settings;
        this.$ = $;
        this.Drupal = Drupal;
        this.element = element;

        this.$document = this.$(document);
        this.$window = this.$(window);
        this.$body = this.$('body', this.context).once();
        this.tabletBreakpoint = 1024;
        this.speed = 300;

        // Menu elements
        this.$header = this.$('.js-header', this.context);
        this.$mainMenu = this.$('.js-main-menu-content', this.context);
        this.$burgerButton = this.$('.js-burger-button', this.context).once();
        this.$subMenuButton = this.$('.js-sub-menu-button', this.context).once();
        this.$subMenu = this.$('.js-sub-menu', this.context);

        // Search elements
        this.$searchButton = this.$('.js-search-button', this.context).once();

        // Scrollbars
        //----------------------------------------
        this.scrollbars = [];

        this.$burgerButton.on('click', this.$.proxy(this.openBurgerMenu, this));
        this.$subMenuButton.on('click', this.$.proxy(this.toggleSubMenu, this));
        this.$searchButton.on('click', this.$.proxy(this.openSearch, this));

        // Escape Key
        this.$document.keyup((e) => {
            if (e.keyCode === 27) {
                this.closeBurgerMenu();
                this.closeSearch();
            }
        });

        this.$window.on('resize', debounce(() => {
            if (this.$window.width() > this.tabletBreakpoint) {
                this.resetHeader();
                this.destroyScrollbars();
            } else {
                this.addScrollbars();
            }
        }, 300))
            .on('load', () => {
                if (this.$window.width() <= this.tabletBreakpoint) {
                    this.addScrollbars();
                }
            });
    }

    addScrollbars () {
        for (let i = 0; i < this.$mainMenu.length; i++) {
            this.scrollbars.push(new Ps(this.$mainMenu[0], {
                suppressScrollX: true,
            }));
        }
    }

    destroyScrollbars () {
        this.scrollbars.forEach((ps) => ps.destroy());
    }

    updateScrollbars () {
        this.scrollbars.forEach((ps) => ps.update());
    }

    openBurgerMenu (e) {
        const $elem = this.$(e.currentTarget);
        this.$header.addClass('has-menu-overlay');
        this.$body.addClass('is-scroll-locked');

        if (this.$header.hasClass('has-search-overlay')) {
            this.closeSearch();
            this.$body.addClass('is-scroll-locked');
        }

        if (!$elem.hasClass('is-open')) {
            $elem.toggleClass('is-open').attr('aria-expanded', 'true');
        } else {
            this.closeBurgerMenu();
        }
    }

    closeBurgerMenu () {
        if (this.$burgerButton.hasClass('is-open')) {
            this.$burgerButton.removeClass('is-open').attr('aria-expanded', 'false');
            this.$header.removeClass('has-menu-overlay');
            this.$body.removeClass('is-scroll-locked');
        }
    }

    toggleSubMenu (e) {
        const $elem = this.$(e.currentTarget);
        const $elemToToggle = $elem.parent().next(this.$subMenu);

        if (!$elem.hasClass('is-clicked')) {
            this.closeSubMenu();
            $elem.toggleClass('is-clicked')
                .attr('aria-expanded', 'true');
            $elemToToggle.slideDown(this.speed);
        } else {
            this.closeSubMenu();
        }
    }

    openSearch (e) {
        const $elem = this.$(e.currentTarget);
        this.$header.addClass('has-search-overlay');
        this.$body.addClass('is-scroll-locked');

        if (this.$header.hasClass('has-menu-overlay')) {
            this.closeBurgerMenu();
            this.$body.addClass('is-scroll-locked');
        }

        if (!$elem.hasClass('is-open')) {
            $elem.toggleClass('is-open').attr('aria-expanded', 'true');
        } else {
            this.closeSearch();
        }
    }

    closeSearch () {
        if (this.$searchButton.hasClass('is-open')) {
            this.$searchButton.removeClass('is-open').attr('aria-expanded', 'false');
            this.$header.removeClass('has-search-overlay');
            this.$body.removeClass('is-scroll-locked');
        }
    }

    closeSubMenu () {
        if (this.$subMenuButton.hasClass('is-clicked')) {
            this.$subMenuButton.removeClass('is-clicked')
                .attr('aria-expanded', 'false');
            this.$subMenu.slideUp(this.speed);
        }
    }

    resetHeader () {
        this.closeSearch();
        this.closeBurgerMenu();
        this.closeSubMenu();
        this.$body.removeClass('is-scroll-locked');
    }
}

export default Header;
