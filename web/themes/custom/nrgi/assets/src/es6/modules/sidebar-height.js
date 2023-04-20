import debounce from "./utils/debounce";

console.log('running sidebar');

class SidebarHeight {
    constructor (context, settings, $, Drupal, element) {
        // Values from Drupal
        this.context = context;
        this.settings = settings;
        this.$ = $;
        this.Drupal = Drupal;
        this.element = element;

        this.$window = this.$(window);

        this.$topContent = this.$('.js-single-content-container', this.context).once();
        this.$sidebar = this.$('.c-single-content__image-wrapper', this.context);

        this.$window.on('load', debounce(() => {
            heightCalculation();
        }));
    }

    heightCalculation () {
        const height = this.$sidebar.offsetHeight;
        console.log(height);
    }
}

export default SidebarHeight;
