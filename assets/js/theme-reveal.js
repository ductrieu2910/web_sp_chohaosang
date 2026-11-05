/**
 * theme-reveal.js — Scroll reveal & micro-interaction enhancements
 * Inspired by Cho Hao Sang motion style:
 * Easing: cubic-bezier(0.22, 1, 0.36, 1), durations: 150/300/600ms
 * Automatically disables when user prefers reduced motion.
 */
(function () {
  'use strict';

  if (typeof window === 'undefined') return;

  // Respect user preference for reduced motion
  var prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (prefersReduced) return;

  // Graceful fallback if IntersectionObserver is unsupported
  if (!('IntersectionObserver' in window)) return;

  var selector = [
    '[data-theme-reveal]',
    '.section-heading',
    '.banner-slider-area',
    '.service-item',
    '.category-item',
    '.product-item',
    '.post-item',
    '.brand-item',
    '.contact-info-item',
    '.login-section .input-box',
    '.footer .widget',
    '.product-details-content',
    '.order-details-area',
    '.cart-table-area',
    '.contact-form-area',
    '.eflux-login-form-area'
  ].join(', ');

  var observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-revealed');
        observer.unobserve(entry.target);
      }
    });
  }, {
    root: null,
    rootMargin: '0px 0px -40px 0px',
    threshold: 0.06
  });

  function initReveal() {
    try {
      var elements = document.querySelectorAll(selector);
      var innerHeight = window.innerHeight || document.documentElement.clientHeight;
      for (var i = 0; i < elements.length; i++) {
        var el = elements[i];
        if (!el.classList.contains('theme-reveal')) {
          el.classList.add('theme-reveal');
          // If already in initial viewport, reveal immediately without animation delay
          var rect = el.getBoundingClientRect();
          if (rect.top < innerHeight && rect.bottom > 0) {
            el.classList.add('is-revealed');
          } else {
            observer.observe(el);
          }
        }
      }
    } catch (e) {
      // In case of any error, ensure elements remain visible
      console.warn('theme-reveal init failed:', e);
    }
  }

  // Safety fallback: after 2s, ensure everything is visible even if scroll did not trigger
  function fallbackRevealAll() {
    var unrevealed = document.querySelectorAll('.theme-reveal:not(.is-revealed)');
    for (var i = 0; i < unrevealed.length; i++) {
      unrevealed[i].classList.add('is-revealed');
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      initReveal();
      setTimeout(fallbackRevealAll, 2000);
    });
  } else {
    initReveal();
    setTimeout(fallbackRevealAll, 2000);
  }

  window.addEventListener('load', function () {
    initReveal();
    setTimeout(fallbackRevealAll, 1500);
  });

  // Custom trigger for dynamic content (sliders, modals, tab switching)
  document.addEventListener('theme:refresh', initReveal);
})();
