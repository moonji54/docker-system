import debounce from './utils/debounce';

class ParallaxScroll {
    constructor (context, settings, $, Drupal) {
        // Values from Drupal
        this.context = context;
        this.settings = settings;
        this.$ = $;
        this.Drupal = Drupal;

        this.$window = this.$(window);
        this.$parallaxContainer = this.$('.js-parallax-container');

        this.$window.on('load scroll', debounce(() => {
            if (this.$parallaxContainer.length && this.$window.width() >= 768) {
                this.applyClassOnScroll();
            }
        }));
    }

    applyClassOnScroll () {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    this.translateCalc(entry);
                }
            });
        });

        this.$parallaxContainer.each((index, container) => {
            observer.observe(container);
        });
    }

    translateCalc (entry) {
        const parallaxBlock = this.$(entry.target).find(this.$('.js-parallax-block'));

        const entryRect = entry.target.getBoundingClientRect();
        const progress = 80 * ((entryRect.y - 200) / window.innerHeight);

        if (progress <= 0) {
            parallaxBlock[0].style.transform = 'translateY(0%)';
        } else {
            parallaxBlock[0].style.transform = `translateY(${progress}%)`;
        }
    }
}

export default ParallaxScroll;
