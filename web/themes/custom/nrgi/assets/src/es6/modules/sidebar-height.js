import debounce from './utils/debounce';

class SidebarHeight {
    constructor (context, settings, $, Drupal, element) {
        // Values from Drupal
        this.context = context;
        this.settings = settings;
        this.$ = $;
        this.Drupal = Drupal;
        this.element = element;

        this.$window = this.$(window);

        this.$topContainer = this.$('.js-sidebar-container', this.context).once();
        this.$sidebar = this.$('.js-sidebar', this.context);

        this.$window.on('load resize', debounce(() => {
            this.heightCalculation();
        }));
    }

    heightCalculation () {
        const sidebarHeight = this.$sidebar.height();
        const containerHeight = this.$topContainer.height();
        const $heightDiff = sidebarHeight - containerHeight;
        const nextSibling = this.$topContainer.next();

        if ((containerHeight === 0) && (!nextSibling.hasClass('c-text-block'))) {
            this.$(':root').css({'--sidebar-height': `${$heightDiff}px`});
        }
    }
}

export default SidebarHeight;
