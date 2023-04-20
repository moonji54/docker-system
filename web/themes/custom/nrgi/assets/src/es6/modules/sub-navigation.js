class SubNavigation {
    constructor (context, settings, $, Drupal, element) {
        // Values from Drupal
        this.context = context;
        this.settings = settings;
        this.$ = $;
        this.Drupal = Drupal;
        this.element = element;

        this.speed = 300;

        this.$button = this.$('.js-sub-navigation-button', this.context).once();
        this.$button.on('click', this.$.proxy(this.toggleContent, this));
    }

    toggleContent (e) {
        const $elem = this.$(e.currentTarget);

        $elem.attr('aria-expanded', 'true');
        $elem.toggleClass('is-open').next().slideToggle(this.speed);

        if (!$elem.hasClass('is-open')) {
            $elem.attr('aria-expanded', 'false');
        }
    }
}

export default SubNavigation;
