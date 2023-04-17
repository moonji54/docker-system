class LanguagesOverlay {
    constructor (context, settings, $, Drupal) {
        // Values from Drupal
        this.context = context;
        this.settings = settings;
        this.$ = $;
        this.Drupal = Drupal;

        this.speed = 300;

        this.$button = this.$('.js-languages-button', this.context).once();
        this.$button.on('click', this.$.proxy(this.toggleContent, this));
    }

    toggleContent (e) {
        const $elem = this.$(e.currentTarget);

        $elem.attr('aria-expanded', 'true');
        $elem.toggleClass('is-clicked').prev().toggleClass('is-open').slideToggle(this.speed);

        if (!$elem.hasClass('is-clicked')) {
            $elem.attr('aria-expanded', 'false');
        }
    }
}

export default LanguagesOverlay;
