class ShareLinks {
    constructor (context, settings, $, Drupal) {
        // Values from Drupal
        this.context = context;
        this.settings = settings;
        this.$ = $;
        this.Drupal = Drupal;
        this.$window = this.$(window);
        this.mobileBreakpoint = 767;

        this.$shareButton = this.$('.js-share-button', this.context);
        this.$shareLinks = this.$('.js-share-links', this.context);

        // Events
        if (this.$window.width() <= this.mobileBreakpoint) {
            if (navigator.share) {
                this.$shareButton.on('click', this.$.proxy(this.webShare, this));
                this.$shareLinks.addClass('is-hidden');
            } else {
                this.$shareButton.removeClass('is-visible');
            }
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
