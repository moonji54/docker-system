class Lightbox {
    constructor (context, settings, $, Drupal) {
        this.$ = $;
        this.settings = settings;
        this.Drupal = Drupal;
        this.context = context;

        this.$(context).on('cbox_complete', this.$.proxy(this.lightboxComplete, this));

        this.$(context).on('cbox_closed', this.$.proxy(this.lightboxClosed, this));
    }

    lightboxComplete () {
        // Make all the controls invisible.
        this.$('#cboxCurrent, #cboxSlideshow, #cboxPrevious, #cboxNext').addClass('visually-hidden');
        // Replace "Close" with "×" and show.
        this.$('#cboxClose').html('\u00d7').addClass('cbox-close-plain');
        // Hide empty title.
        if (this.$('#cboxTitle:empty').length === true) {
            this.$('#cboxTitle').hide();
        }
    }

    lightboxClosed () {
        this.$('#cboxClose').removeClass('cbox-close-plain');
    }
}

export default Lightbox;
