import Ps from 'perfect-scrollbar';

class Toc {
    constructor ($, context) {
        // Values from Drupal
        this.$ = $;
        this.context = context;

        // Document
        this.$window = this.$(window, this.context);
        this.$document = this.$(document, this.context);
        this.$body = this.$('body', this.context);

        // Elements
        this.$toggleButton = this.$('.js-toggle-button', this.context).once();
        this.$toggleContent = this.$('.js-toggle-content', this.context).once();
        this.$scrollWrapper = this.$('.js-scroll-wrapper', this.context).once();
        this.$wysiwyg = this.$('.js-text-block', this.context);
        this.$tocWrapper = this.$('.js-table-of-contents-wrapper', this.context);
        this.$toc = this.$('.js-toc-list', this.context);
        this.$tocItem = this.$('.js-toc-list-item', this.context).once();
        this.$headings = this.$wysiwyg.find('h2, h3');

        // Scrollbars
        this.scrollbars = [];

        this.$toggleButton.on('click', this.$.proxy(this.toggleToc, this));
        this.$tocItem.on('click', this.$.proxy(this.toggleToc, this));

        // Close TOC on document click
        this.$document.on('click', $.proxy(this.documentClick, this));

        // On load init the scrollbars
        this.$window.on('load', this.$.proxy(this.addScrollbars, this));

        this.$window.on('resize', this.$.proxy(this.updateScrollbars, this));

        // Format headings and build TOC
        this.buildTableOfContents();

        this.showToc();
    }

    toggleToc (e) {
        const $elem = this.$(e.currentTarget);
        const $toc = $elem.closest(this.$tocWrapper);

        $elem.toggleClass('is-clicked').attr('aria-expanded', 'true');

        this.$body.toggleClass('has-overlay');

        if (!$elem.hasClass('is-clicked')) {
            $elem.removeAttr('aria-expanded').blur();

            this.$toggleContent.slideUp(() => {
                $toc.removeClass('is-open');
            });
        } else {
            this.$toggleContent.slideDown(() => {
                $toc.addClass('is-open');
            });
        }
    }

    showToc () {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.intersectionRatio === 0) {
                    this.$tocWrapper.addClass('is-fixed');
                } else {
                    this.$tocWrapper.removeClass('is-fixed');
                }
            });
        });

        this.$('.js-single-header').each((index, container) => {
            observer.observe(container);
        });
    }

    buildTableOfContents () {
        // Only show the TOC if there are more any headings
        if (this.$headings.length) {
            this.$tocWrapper.addClass('is-visible');

            // Loop through each of the headings
            this.$headings.each((index, element) => {
                // Find the text in each
                const headingText = this.$(element).text();

                // Create an ID for use on each heading
                const headingID = `${headingText.toLowerCase().replace(/\s/g, '-')}-${index}`;

                this.$(element).attr('id', headingID);

                if (this.$(element).is('h2')) {
                    const isTopLevel = true;

                    // Create a list item with the correct URL and ID
                    this.$toc.append(this.buildListItem(headingID, headingText, index, isTopLevel));
                } else {
                    // if h3
                    // Find the previous heading (i.e the 'parent').
                    const parentListItem = this.$toc.find('> li').last();

                    // If it doesn't already have a sub nav appended, add one.
                    if (!parentListItem.find('> ul').length) {
                        parentListItem.append('<ul class="c-table-of-contents__nested-list js-toc-sub-nav"></ul>');
                    }

                    // Find the sub nav and append our list item to it.
                    parentListItem.find('> ul').append(this.buildListItem(headingID, headingText, index));
                }
            });
        }
    }

    buildListItem (headingTarget, headingText, index, isTopLevel = false) {
        let listClass = '';

        if (isTopLevel) {
            listClass = 'class="c-table-of-contents__item-heading js-toc-list-item"';
        } else {
            listClass = 'class="c-table-of-contents__item-sub-heading js-toc-list-item"';
        }
        return `<li ${listClass}>
      <a href="#${headingTarget}" title="Anchor link to ${headingText}">${headingText}</a>
    </li>`;
    }

    documentClick (e) {
        const $elem = this.$(e.target);

        if (this.$tocWrapper.is(':visible') && !$elem.closest(this.$toggleButton).length) {
            this.closeToc();
        }
    }

    closeToc () {
        if (this.$toggleButton.hasClass('is-clicked')) {
            this.$toggleButton.trigger('click');
        }
    }

    addScrollbars () {
        for (let i = 0; i < this.$scrollWrapper.length; i++) {
            this.scrollbars.push(new Ps(this.$scrollWrapper[0], {
                suppressScrollX: true,
                maxScrollbarLength: 180,
                wheelSpeed: 0.5
            }));
        }
    }

    updateScrollbars () {
        this.scrollbars.forEach((ps) => ps.update());
    }

    destroyScrollbars () {
        this.scrollbars.forEach((ps) => ps.destroy());
    }
}

export default Toc;
