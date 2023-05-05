class ShareLinks {
    constructor (context, settings, $, Drupal) {
        // Values from Drupal
        this.context = context;
        this.settings = settings;
        this.$ = $;
        this.Drupal = Drupal;
        this.$window = this.$(window);

        this.$shareButton = this.$('.js-share-button', this.context);
        this.$shareLinks = this.$('.js-share-links', this.context);

        // Events
        if (navigator.share && this.$window.width() <= 768) {
            this.$shareButton.on('click', this.$.proxy(this.webShare, this));
            this.$shareLinks.addClass('is-hidden');
            this.$shareButton.addClass('is-visible');
        } else {
            this.$shareButton.removeClass('is-visible');
        }
    }

    webShare (e) {
        const $elem = this.$(e.currentTarget);
        navigator.share({
            title: $elem.data('share-title'),
            url: $elem.data('share-url')
        }).catch(console.error);
    }
}

export default ShareLinks;
