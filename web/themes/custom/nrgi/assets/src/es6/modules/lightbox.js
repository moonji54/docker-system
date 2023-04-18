class Lightbox {
    constructor (context, settings, $, Drupal) {
        this.$ = $;
        this.settings = settings;
        this.Drupal = Drupal;
        this.context = context;

        this.$window = this.$(window);

        this.$image = this.$('.js-text-block').find(this.$('.cboxElement')).once();

        this.$(context).on('cbox_complete', this.$.proxy(this.lightboxComplete, this));
        this.$(context).on('cbox_closed', this.$.proxy(this.lightboxClosed, this));

        this.$image.on('click', this.$.proxy(this.createCaption, this));
    }

    createCaption (e) {
        this.$('#cboxTitle').hide();

        const target = this.$(e.currentTarget);

        const targetCaption = this.$(target).closest(this.$('.c-media--image')).next('figcaption')[0];

        const targetCaptionText = this.$(targetCaption).text();

        const createCaption = '<p class="cboxCaption"></p>';

        const newCaption = this.$(createCaption).text(targetCaptionText);

        if (this.$('.cboxCaption').length === 0) {
            this.$('#cboxContent').prepend(newCaption);
        } else {
            this.$('.cboxCaption').text(targetCaptionText);
        }
    }

    lightboxComplete () {
        // Make all the controls invisible.
        this.$('#cboxCurrent, #cboxSlideshow, #cboxPrevious, #cboxNext').addClass('visually-hidden');
        // Replace "Close" with "×" and show.
        this.$('#cboxClose').html('\u00d7').addClass('cbox-close-plain');
        // Hide empty title.
        this.$('#cboxTitle').hide();

        this.$('body').addClass('is-scroll-locked');
    }

    lightboxClosed () {
        this.$('#cboxClose').removeClass('cbox-close-plain');
        this.$('body').removeClass('is-scroll-locked');
    }
}

export default Lightbox;
