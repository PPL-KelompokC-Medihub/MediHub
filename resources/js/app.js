import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    initSmoothScroll();
    initStaggeredScrollAnimations();
    initNavbarEffect();
    initButtonInteractions();
    initParallaxHero();
    initLazyLoading();
});

// 1. Smooth Scrolling Behavior
function initSmoothScroll() {
    document.documentElement.style.scrollBehavior = 'smooth';
    
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// 2 & 5. Staggered Intersection Observer Scroll Reveal
function initStaggeredScrollAnimations() {
    // Select elements to reveal
    const animateSelectors = [
        '.service-card-v3', '.doctor-card', '.stat-item', 
        '.hero-content h1', '.hero-content p', '.hero-btns', 
        '.about-desc p', '.footer-col'
    ];
    
    const elements = document.querySelectorAll(animateSelectors.join(', '));

    // Prepare initial state (GPU accelerated)
    elements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), transform 0.8s cubic-bezier(0.16, 1, 0.3, 1)';
        el.style.willChange = 'opacity, transform';
    });

    let currentVisibleGroup = [];
    let staggerTimeout = null;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                currentVisibleGroup.push(entry.target);
                observer.unobserve(entry.target);
            }
        });

        if (currentVisibleGroup.length > 0) {
            // Apply stagger delay to each element in the visible batch
            if(staggerTimeout) clearTimeout(staggerTimeout);
            
            staggerTimeout = setTimeout(() => {
                currentVisibleGroup.forEach((el, index) => {
                    setTimeout(() => {
                        requestAnimationFrame(() => {
                            el.style.opacity = '1';
                            el.style.transform = 'translateY(0)';
                        });
                    }, index * 100); // 100ms stagger delay
                });
                currentVisibleGroup = []; // Clear current queue handled
            }, 10);
        }

    }, {
        root: null,
        threshold: 0.1,
        rootMargin: "0px 0px -40px 0px"
    });

    elements.forEach(el => observer.observe(el));
}

// 3 & 4. Dynamic Sticky Navbar (Blur shadow effect)
function initNavbarEffect() {
    const header = document.querySelector('header');
    if (!header) return;

    header.style.position = 'sticky';
    header.style.top = '0';
    header.style.zIndex = '1000';
    header.style.transition = 'all 0.4s cubic-bezier(0.16, 1, 0.3, 1)';

    window.addEventListener('scroll', () => {
        requestAnimationFrame(() => {
            if (window.scrollY > 40) {
                header.style.backgroundColor = 'rgba(255, 255, 255, 0.85)';
                header.style.backdropFilter = 'blur(16px)';
                header.style.webkitBackdropFilter = 'blur(16px)';
                header.style.boxShadow = '0 12px 34px rgba(0, 0, 0, 0.05)';
                header.style.paddingTop = '12px';
                header.style.paddingBottom = '8px';
            } else {
                header.style.backgroundColor = '#fff';
                header.style.backdropFilter = 'none';
                header.style.webkitBackdropFilter = 'none';
                header.style.boxShadow = 'none';
                header.style.paddingTop = '24px';
                header.style.paddingBottom = '16px';
            }
        });
    }, { passive: true });
}

// 6 & 7. Buttons and Cards Micro-Interactions
function initButtonInteractions() {
    const interactiveElements = document.querySelectorAll('.btn-register, .cta-btn, .service-card-v3, .doctor-card, .btn-signin');

    interactiveElements.forEach(el => {
        el.style.transition = 'transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.4s ease';
        el.style.willChange = 'transform, box-shadow';
        
        const isButton = el.tagName === 'BUTTON' || el.tagName === 'A';
        
        if (isButton) {
            el.addEventListener('mousedown', () => el.style.transform = 'scale(0.94)');
            el.addEventListener('mouseup', () => el.style.transform = 'scale(1.03)');
            el.addEventListener('mouseleave', () => el.style.transform = 'scale(1)');
            el.addEventListener('mouseenter', () => el.style.transform = 'scale(1.03)');
        } else {
            // Card Depth
            el.addEventListener('mouseenter', () => {
                el.style.transform = 'translateY(-6px) scale(1.02)';
                el.style.boxShadow = '0 24px 48px rgba(0, 0, 0, 0.08)';
                el.style.zIndex = '10';
            });
            el.addEventListener('mouseleave', () => {
                el.style.transform = 'translateY(0) scale(1)';
                el.style.boxShadow = '0 4px 14px rgba(0, 0, 0, 0.03)';
                el.style.zIndex = '1';
            });
        }
    });

    // Sub-interaction: Image Hover Zoom inside Cards
    const cardsWithImages = document.querySelectorAll('.doctor-card, .service-card-v3');
    cardsWithImages.forEach(card => {
        const img = card.querySelector('.doctor-photo, .icon-v3');
        if (img) {
            img.style.transition = 'transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
            card.addEventListener('mouseenter', () => img.style.transform = 'scale(1.05)');
            card.addEventListener('mouseleave', () => img.style.transform = 'scale(1)');
        }
    });
}

// 8. Subtle Lightweight Parallax for Hero
function initParallaxHero() {
    const heroBg = document.querySelector('.hero-bg');
    if (!heroBg) return;

    window.addEventListener('scroll', () => {
        requestAnimationFrame(() => {
            const scroll = window.scrollY;
            if (scroll < window.innerHeight) {
                // Parallax transform formula
                heroBg.style.transform = `translateY(${scroll * 0.25}px)`;
            }
        });
    }, { passive: true });
}

// 9. Lazy Loading images
function initLazyLoading() {
    document.querySelectorAll('img:not([loading])').forEach(img => {
        img.setAttribute('loading', 'lazy');
    });
}
