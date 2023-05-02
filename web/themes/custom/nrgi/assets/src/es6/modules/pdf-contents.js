class PdfContents {
    constructor ($, context) {
        this.$ = $;
        this.context = context;

        // Elements
        this.$pdfContentsWrapper = this.$('.js-table-of-contents-wrapper', this.context);
        this.$pdfWysiwyg = this.$('.js-text-block', this.context);
        this.$pdfContentsList = this.$('#pdf-contents', this.context);
        this.$pdfWysiwygHeadingTwos = this.$pdfWysiwyg.find('h2');

        if (this.$pdfWysiwygHeadingTwos.length >= 1) {
            this.printAllHeadings();
        } else {
            this.$pdfContentsWrapper.css('display', 'none');
        }

        // Start page to pagedjs to resize the entire page.
        window.PagedPolyfill.preview();
    }

    printAllHeadings () {
        const wysiwygs = this.$pdfWysiwyg;
        let mainChapterNumber = 0;
        let secondaryChapterNumber = 0;

        wysiwygs.each((index, element) => {
            const headings = this.$(element).find('h2, h3');
            headings.each((subIndex, subElement) => {
                // this.$headings.push(subElement);
                // Find the text in each
                const $jQueryEl = this.$(subElement);
                const headingText = $jQueryEl.text();

                // Create an ID for use on each heading
                const headingID = headingText.toLowerCase().replace(/\s/g, '-');

                // Add an ID to each h2
                $jQueryEl.attr('id', headingID);

                // Found element tag name
                const subElementTagName = subElement.tagName.toLowerCase();

                // Let's increase the chapter numbers based on what we have found
                let chapterNumber = '';
                switch (subElementTagName) {
                    case 'h2':
                        mainChapterNumber++;
                        chapterNumber = mainChapterNumber;
                        secondaryChapterNumber = 0;
                        break;
                    case 'h3':
                        secondaryChapterNumber++;
                        chapterNumber = `${mainChapterNumber}.${secondaryChapterNumber}`;
                        break;
                }

                this.$pdfContentsList.append(this.buildListItem(headingID, headingText, subElementTagName));
            });
        });
    }

    buildListItem (headingTarget, headingText, tagName) {
        let listItem;
        switch (tagName) {
            case 'h2':
                listItem = `<li class="c-pdf__toc-item-heading">
                    <a href="#${headingTarget}">${headingText}</a>
                </li>`;
                break;
            case 'h3':
                listItem = `<li class="c-pdf__toc-item-heading c-pdf__toc-item-heading--sub">
                    <a href="#${headingTarget}">${headingText}</a>
                </li>`;
                break;
        }

        return listItem;
    }
}

export default PdfContents;
