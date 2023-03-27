import Ps from 'perfect-scrollbar';
import debounce from './utils/debounce';

class Header {
    constructor (context, settings, $, Drupal) {
        // Values from Drupal
        this.context = context;
        this.settings = settings;
        this.$ = $;
        this.Drupal = Drupal;
        this.$document = this.$(document);
        this.$window = this.$(window);
        this.$body = this.$('body', this.context).once();
        this.tabletBreakpoint = 1024;
        this.speed = 300;

        this.$header = this.$('.js-header', this.context);
        this.$mainMenu = this.$('.js-main-menu', this.context);
        this.$burgerButton = this.$('.js-burger-button', this.context).once();
        this.$subMenuButton = this.$('.js-sub-menu-button', this.context).once();
        this.$subMenu = this.$('.js-sub-menu', this.context);
        this.$itemToExpand = this.$('.js-menu-expand', this.context);

        // Scrollbars
        //----------------------------------------
        this.scrollbars = [];

        this.$burgerButton.on('click', this.$.proxy(this.openBurgerMenu, this));
        this.$subMenuButton.on('click', this.$.proxy(this.toggleSubMenu, this));

        // Escape Key
        this.$document.keyup((e) => {
            if (e.keyCode === 27) {
                this.closeBurgerMenu();
            }
        });

        this.$window.on('scroll', debounce(() => {
            const scroll = this.$window.scrollTop();
            if (scroll >= 250) {
                this.$header.addClass('is-fixed');
                this.$mainMenu.addClass('is-fixed');
                this.$body.addClass('has-padding-top');
            } else {
                this.$header.removeClass('is-fixed');
                this.$mainMenu.removeClass('is-fixed');
                this.$body.removeClass('has-padding-top');
            }
        }, 10));

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
        this.$header.addClass('has-overlay');
        this.$mainMenu.addClass('has-overlay');
        this.$body.addClass('is-scroll-locked');

        if (!$elem.hasClass('is-open')) {
            $elem.toggleClass('is-open').attr('aria-expanded', 'true');
        } else {
            this.closeBurgerMenu();
        }
    }

    closeBurgerMenu () {
        this.$burgerButton.removeClass('is-open').attr('aria-expanded', 'false');
        this.$header.removeClass('has-overlay');
        this.$mainMenu.removeClass('has-overlay');
        this.$body.removeClass('is-scroll-locked');
    }

    toggleSubMenu (e) {
        const $elem = this.$(e.currentTarget);
        this.$itemToExpand.toggleClass('is-clicked');
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

    closeSubMenu () {
        this.$subMenuButton.removeClass('is-clicked')
            .attr('aria-expanded', 'false');
        this.$subMenu.slideUp(this.speed);
    }

    resetHeader () {
        this.closeBurgerMenu();
        this.closeSubMenu();
        this.$body.removeClass('is-scroll-locked');
    }
}

export default Header;
