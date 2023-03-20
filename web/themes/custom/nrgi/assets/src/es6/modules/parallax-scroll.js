import debounce from './utils/debounce';

class ParallaxScroll {
  constructor(context, settings, $, Drupal) {
// Values from Drupal
    this.context = context;
    this.settings = settings;
    this.$ = $;
    this.Drupal = Drupal;

    this.$root = $(':root');
    this.$window = this.$(window);
    this.$parallaxContainer = this.$('.js-parallax-container');

    this.$window.on('scroll', debounce(() => {
      this.applyClassOnScroll();
    }));
  }

  applyClassOnScroll() {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          this.$(entry.target).addClass('is-visible');

          this.$root.css({
            "--scroll": window.scrollY / document.body.offsetHeight
          });
        }
        else {
          this.$(entry.target).removeClass('is-visible');
        }
      });
    });

    observer.observe(this.$parallaxContainer[0]);
  }
}

export default ParallaxScroll;
