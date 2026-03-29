/**
 * ORCA V2 - JavaScript principal
 */

document.addEventListener('DOMContentLoaded', function() {
    // Header scroll effect
    initHeaderScroll();
    
    // Mobile menu
    initMobileMenu();
    
    // FAQ accordion
    initFAQ();
    
    // Smooth scroll
    initSmoothScroll();
    
    // Form validation
    initFormValidation();
    
    // Filtres modèles
    initModelFilters();
    
    // Gallery
    initGallery();
    
    // Animations on scroll
    initScrollAnimations();
});

/**
 * Header scroll effect
 */
function initHeaderScroll() {
    const header = document.querySelector('.header');
    if (!header) return;
    
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });
}

/**
 * Mobile menu toggle
 */
function initMobileMenu() {
    const toggle = document.querySelector('.menu-toggle');
    const nav = document.querySelector('.nav');
    
    if (!toggle || !nav) return;
    
    toggle.addEventListener('click', () => {
        nav.classList.toggle('active');
        toggle.classList.toggle('active');
    });
    
    // Close menu on link click
    const navLinks = nav.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            nav.classList.remove('active');
            toggle.classList.remove('active');
        });
    });
}

/**
 * FAQ Accordion
 */
function initFAQ() {
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        
        question.addEventListener('click', () => {
            const isActive = item.classList.contains('active');
            
            // Close all items
            faqItems.forEach(i => i.classList.remove('active'));
            
            // Open clicked item if it wasn't active
            if (!isActive) {
                item.classList.add('active');
            }
        });
    });
}

/**
 * Smooth scroll for anchor links
 */
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                e.preventDefault();
                const headerHeight = document.querySelector('.header').offsetHeight;
                const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - headerHeight;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
}

/**
 * Form validation
 */
function initFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const requiredFields = form.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                const formGroup = field.closest('.form-group');
                
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                    
                    if (formGroup) {
                        let error = formGroup.querySelector('.error-message');
                        if (!error) {
                            error = document.createElement('span');
                            error.className = 'error-message';
                            error.style.cssText = 'color: #E74C3C; font-size: 0.875rem; margin-top: 0.25rem; display: block;';
                            formGroup.appendChild(error);
                        }
                        error.textContent = 'Ce champ est obligatoire';
                    }
                } else {
                    field.classList.remove('is-invalid');
                    const error = formGroup?.querySelector('.error-message');
                    if (error) error.remove();
                }
            });
            
            // Email validation
            const emailField = form.querySelector('input[type="email"]');
            if (emailField && emailField.value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(emailField.value)) {
                    isValid = false;
                    emailField.classList.add('is-invalid');
                }
            }
            
            // Phone validation
            const phoneField = form.querySelector('input[type="tel"]');
            if (phoneField && phoneField.value) {
                const phoneRegex = /^(0[1-9])(?:[ \-\.]?[0-9]{2}){4}$/;
                if (!phoneRegex.test(phoneField.value)) {
                    isValid = false;
                    phoneField.classList.add('is-invalid');
                }
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
        
        // Clear error on input
        form.querySelectorAll('input, textarea, select').forEach(field => {
            field.addEventListener('input', function() {
                this.classList.remove('is-invalid');
                const formGroup = this.closest('.form-group');
                const error = formGroup?.querySelector('.error-message');
                if (error) error.remove();
            });
        });
    });
}

/**
 * Model filters
 */
function initModelFilters() {
    const filters = document.querySelectorAll('.filter-btn[data-filter]');
    const items = document.querySelectorAll('[data-category]');
    
    if (!filters.length || !items.length) return;
    
    filters.forEach(filter => {
        filter.addEventListener('click', () => {
            const category = filter.dataset.filter;
            
            // Update active filter
            filters.forEach(f => f.classList.remove('active'));
            filter.classList.add('active');
            
            // Filter items
            items.forEach(item => {
                if (category === 'all' || item.dataset.category === category) {
                    item.style.display = '';
                    item.classList.add('animate-fadeIn');
                } else {
                    item.style.display = 'none';
                    item.classList.remove('animate-fadeIn');
                }
            });
        });
    });
}

/**
 * Gallery
 */
function initGallery() {
    const mainImage = document.querySelector('.modele-image-main img');
    const thumbs = document.querySelectorAll('.modele-thumb');
    
    if (!mainImage || !thumbs.length) return;
    
    thumbs.forEach(thumb => {
        thumb.addEventListener('click', () => {
            const img = thumb.querySelector('img');
            if (img) {
                mainImage.src = img.dataset.full || img.src;
                
                thumbs.forEach(t => t.classList.remove('active'));
                thumb.classList.add('active');
            }
        });
    });
}

/**
 * Scroll animations
 */
function initScrollAnimations() {
    const animatedElements = document.querySelectorAll('[data-animate]');
    
    if (!animatedElements.length) return;
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fadeIn');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    animatedElements.forEach(el => observer.observe(el));
}

/**
 * Show/hide elements based on select value
 */
function toggleConditionalField(triggerSelector, targetSelector, showValue) {
    const trigger = document.querySelector(triggerSelector);
    const target = document.querySelector(targetSelector);
    
    if (!trigger || !target) return;
    
    trigger.addEventListener('change', () => {
        if (trigger.value === showValue) {
            target.style.display = '';
        } else {
            target.style.display = 'none';
        }
    });
}

/**
 * Counter animation
 */
function animateCounter(element, target, duration = 2000) {
    const start = 0;
    const increment = target / (duration / 16);
    let current = start;
    
    const updateCounter = () => {
        current += increment;
        if (current < target) {
            element.textContent = Math.floor(current);
            requestAnimationFrame(updateCounter);
        } else {
            element.textContent = target;
        }
    };
    
    updateCounter();
}

/**
 * Lazy loading images
 */
if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                observer.unobserve(img);
            }
        });
    });
    
    document.querySelectorAll('img[data-src]').forEach(img => {
        imageObserver.observe(img);
    });
}
