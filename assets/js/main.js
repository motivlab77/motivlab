// ===================================
// MOBILE NAVIGATION TOGGLE
// ===================================
const navToggle = document.getElementById('navToggle');
const navMenu = document.getElementById('navMenu');

if (navToggle) {
    navToggle.addEventListener('click', () => {
        navMenu.classList.toggle('active');
        navToggle.classList.toggle('active');
    });

    // Close menu when clicking on a link
    const navLinks = document.querySelectorAll('.nav-menu a');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            navMenu.classList.remove('active');
            navToggle.classList.remove('active');
        });
    });

    // Close menu when clicking outside
    document.addEventListener('click', (e) => {
        if (!navToggle.contains(e.target) && !navMenu.contains(e.target)) {
            navMenu.classList.remove('active');
            navToggle.classList.remove('active');
        }
    });
}

// ===================================
// SMOOTH SCROLLING FOR ANCHOR LINKS
// ===================================
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        
        // Only prevent default if it's not just "#"
        if (href !== '#') {
            e.preventDefault();
            const target = document.querySelector(href);
            
            if (target) {
                const headerOffset = 80;
                const elementPosition = target.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        }
    });
});

// ===================================
// NAVBAR SCROLL EFFECT
// ===================================
const navbar = document.querySelector('.navbar');
let lastScroll = 0;

window.addEventListener('scroll', () => {
    const currentScroll = window.pageYOffset;

    // Add shadow when scrolling
    if (currentScroll > 50) {
        navbar.style.boxShadow = '0 4px 6px rgba(0,0,0,0.1)';
    } else {
        navbar.style.boxShadow = '0 2px 4px rgba(0,0,0,0.05)';
    }

    lastScroll = currentScroll;
});

// ===================================
// SCROLL ANIMATIONS (Fade In on Scroll)
// ===================================
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Observe all cards and sections
const animatedElements = document.querySelectorAll(`
    .category-card,
    .demo-card,
    .feature-card,
    .portfolio-item,
    .testimonial-card,
    .blog-card
`);

animatedElements.forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(30px)';
    el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    observer.observe(el);
});

// ===================================
// CONTACT FORM HANDLING
// ===================================
const contactForm = document.getElementById('contactForm');

if (contactForm) {
    contactForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(contactForm);
        const submitBtn = contactForm.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.textContent;

        // Disable button and show loading
        submitBtn.disabled = true;
        submitBtn.textContent = 'Sending...';

        try {
            // Get form values
            const name = contactForm.querySelector('input[type="text"]').value;
            const email = contactForm.querySelector('input[type="email"]').value;
            const phone = contactForm.querySelector('input[type="tel"]').value;
            const websiteType = contactForm.querySelector('select').value;
            const message = contactForm.querySelector('textarea').value;

            // Here you would normally send to your backend
            // For now, we'll simulate a successful submission
            await simulateFormSubmission({
                name,
                email,
                phone,
                websiteType,
                message
            });

            // Show success message
            showNotification('Thank you! Your message has been sent successfully. We will contact you soon.', 'success');
            
            // Reset form
            contactForm.reset();

        } catch (error) {
            showNotification('Sorry, something went wrong. Please try again or contact us via WhatsApp.', 'error');
        } finally {
            // Re-enable button
            submitBtn.disabled = false;
            submitBtn.textContent = originalBtnText;
        }
    });
}

// Simulate form submission (replace with actual API call)
function simulateFormSubmission(data) {
    return new Promise((resolve) => {
        // Log form data to console for testing
        console.log('Form submitted:', data);
        
        // Simulate network delay
        setTimeout(() => {
            resolve({ success: true });
        }, 1500);
    });
}

// ===================================
// NOTIFICATION SYSTEM
// ===================================
function showNotification(message, type = 'success') {
    // Remove any existing notification
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
        existingNotification.remove();
    }

    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;

    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        background-color: ${type === 'success' ? '#10b981' : '#ef4444'};
        color: white;
        padding: 16px 24px;
        border-radius: 8px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        z-index: 10000;
        max-width: 400px;
        font-weight: 500;
        animation: slideIn 0.3s ease;
    `;

    // Add to page
    document.body.appendChild(notification);

    // Remove after 5 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 5000);
}

// Add notification animations to CSS dynamically
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// ===================================
// LAZY LOADING IMAGES
// ===================================
if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                }
                observer.unobserve(img);
            }
        });
    });

    // Observe all images with data-src attribute
    document.querySelectorAll('img[data-src]').forEach(img => {
        imageObserver.observe(img);
    });
}

// ===================================
// DEMO CAROUSEL (for Featured Demos)
// ===================================
function initializeDemoCarousel() {
    const demoGrid = document.querySelector('.demo-grid');
    
    if (!demoGrid || window.innerWidth > 768) {
        return; // Only apply on mobile
    }

    let isDown = false;
    let startX;
    let scrollLeft;

    demoGrid.addEventListener('mousedown', (e) => {
        isDown = true;
        demoGrid.style.cursor = 'grabbing';
        startX = e.pageX - demoGrid.offsetLeft;
        scrollLeft = demoGrid.scrollLeft;
    });

    demoGrid.addEventListener('mouseleave', () => {
        isDown = false;
        demoGrid.style.cursor = 'grab';
    });

    demoGrid.addEventListener('mouseup', () => {
        isDown = false;
        demoGrid.style.cursor = 'grab';
    });

    demoGrid.addEventListener('mousemove', (e) => {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - demoGrid.offsetLeft;
        const walk = (x - startX) * 2;
        demoGrid.scrollLeft = scrollLeft - walk;
    });
}

// ===================================
// PORTFOLIO FILTER (for full portfolio page)
// ===================================
function initializePortfolioFilter() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const portfolioItems = document.querySelectorAll('.portfolio-item');

    if (filterButtons.length === 0) return;

    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Update active button
            filterButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');

            const filter = button.getAttribute('data-filter');

            // Filter portfolio items
            portfolioItems.forEach(item => {
                const category = item.getAttribute('data-category');
                
                if (filter === 'all' || category === filter) {
                    item.style.display = 'block';
                    setTimeout(() => {
                        item.style.opacity = '1';
                        item.style.transform = 'scale(1)';
                    }, 10);
                } else {
                    item.style.opacity = '0';
                    item.style.transform = 'scale(0.8)';
                    setTimeout(() => {
                        item.style.display = 'none';
                    }, 300);
                }
            });
        });
    });
}

// ===================================
// COUNTER ANIMATION (for statistics)
// ===================================
function animateCounter(element, target, duration = 2000) {
    let start = 0;
    const increment = target / (duration / 16);
    
    const timer = setInterval(() => {
        start += increment;
        if (start >= target) {
            element.textContent = target;
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(start);
        }
    }, 16);
}

// Observe counters
const counterElements = document.querySelectorAll('.counter');
if (counterElements.length > 0) {
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !entry.target.classList.contains('counted')) {
                const target = parseInt(entry.target.getAttribute('data-target'));
                animateCounter(entry.target, target);
                entry.target.classList.add('counted');
            }
        });
    }, { threshold: 0.5 });

    counterElements.forEach(counter => {
        counterObserver.observe(counter);
    });
}

// ===================================
// FAQ ACCORDION (for FAQ page)
// ===================================
function initializeFAQ() {
    const faqItems = document.querySelectorAll('.faq-item');

    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        const answer = item.querySelector('.faq-answer');

        if (question && answer) {
            question.addEventListener('click', () => {
                const isOpen = item.classList.contains('active');

                // Close all other items
                faqItems.forEach(otherItem => {
                    otherItem.classList.remove('active');
                    const otherAnswer = otherItem.querySelector('.faq-answer');
                    if (otherAnswer) {
                        otherAnswer.style.maxHeight = '0';
                    }
                });

                // Toggle current item
                if (!isOpen) {
                    item.classList.add('active');
                    answer.style.maxHeight = answer.scrollHeight + 'px';
                }
            });
        }
    });
}

// ===================================
// WHATSAPP FLOATING BUTTON ANIMATION
// ===================================
const whatsappFloat = document.querySelector('.whatsapp-float');

if (whatsappFloat) {
    // Pulse animation
    setInterval(() => {
        whatsappFloat.style.animation = 'none';
        setTimeout(() => {
            whatsappFloat.style.animation = 'pulse 1s ease';
        }, 10);
    }, 3000);

    // Add pulse animation to CSS
    const pulseStyle = document.createElement('style');
    pulseStyle.textContent = `
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }
    `;
    document.head.appendChild(pulseStyle);
}

// ===================================
// PRICING TABLE TOGGLE (Annual/Monthly)
// ===================================
function initializePricingToggle() {
    const pricingToggle = document.querySelector('.pricing-toggle');
    
    if (!pricingToggle) return;

    const monthlyBtn = pricingToggle.querySelector('[data-period="monthly"]');
    const annualBtn = pricingToggle.querySelector('[data-period="annual"]');
    const prices = document.querySelectorAll('.price-amount');

    const togglePricing = (period) => {
        prices.forEach(price => {
            const monthly = price.getAttribute('data-monthly');
            const annual = price.getAttribute('data-annual');
            
            if (period === 'monthly') {
                price.textContent = monthly;
                monthlyBtn.classList.add('active');
                annualBtn.classList.remove('active');
            } else {
                price.textContent = annual;
                annualBtn.classList.add('active');
                monthlyBtn.classList.remove('active');
            }
        });
    };

    if (monthlyBtn) {
        monthlyBtn.addEventListener('click', () => togglePricing('monthly'));
    }
    if (annualBtn) {
        annualBtn.addEventListener('click', () => togglePricing('annual'));
    }
}

// ===================================
// BACK TO TOP BUTTON
// ===================================
function createBackToTopButton() {
    const backToTop = document.createElement('button');
    backToTop.className = 'back-to-top';
    backToTop.innerHTML = '<i class="fas fa-arrow-up"></i>';
    backToTop.style.cssText = `
        position: fixed;
        bottom: 100px;
        right: 30px;
        width: 50px;
        height: 50px;
        background-color: var(--primary-color);
        color: white;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        z-index: 998;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    `;

    document.body.appendChild(backToTop);

    // Show/hide based on scroll position
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            backToTop.style.opacity = '1';
            backToTop.style.visibility = 'visible';
        } else {
            backToTop.style.opacity = '0';
            backToTop.style.visibility = 'hidden';
        }
    });

    // Scroll to top on click
    backToTop.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

// ===================================
// INITIALIZE ALL FUNCTIONS ON PAGE LOAD
// ===================================
document.addEventListener('DOMContentLoaded', () => {
    console.log('MotivLab Website Loaded');
    
    // Initialize all features
    initializeDemoCarousel();
    initializePortfolioFilter();
    initializeFAQ();
    initializePricingToggle();
    createBackToTopButton();

    // Add loading animation end
    document.body.classList.add('loaded');
});

// ===================================
// PERFORMANCE: Debounce Function
// ===================================
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// ===================================
// WINDOW RESIZE HANDLER
// ===================================
const handleResize = debounce(() => {
    // Reinitialize carousel if needed
    initializeDemoCarousel();
}, 250);

window.addEventListener('resize', handleResize);

// ===================================
// PREVENT FORM RESUBMISSION ON REFRESH
// ===================================
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}

// ===================================
// CONSOLE BRANDING (Optional)
// ===================================
console.log('%cMotivLab', 'color: #f68b1e; font-size: 24px; font-weight: bold;');
console.log('%cBuilding Smart Websites for Nigerian Businesses', 'color: #666; font-size: 12px;');
console.log('%cVisit: www.motivlab.name.ng', 'color: #2c2c2c; font-size: 12px;');