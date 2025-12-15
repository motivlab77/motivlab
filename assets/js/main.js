// ===================================
// MOBILE NAVIGATION TOGGLE
// ===================================
const navToggle = document.getElementById('navToggle');
const navMenu = document.getElementById('navMenu');

if (navToggle) {
    navToggle.addEventListener('click', () => {
        navMenu.classList.toggle('active');
        navToggle.classList.toggle('active');
        
        // Animate hamburger to X
        const spans = navToggle.querySelectorAll('span');
        if (navMenu.classList.contains('active')) {
            spans[0].style.transform = 'rotate(45deg) translate(6px, 6px)';
            spans[1].style.opacity = '0';
            spans[2].style.transform = 'rotate(-45deg) translate(6px, -6px)';
        } else {
            spans[0].style.transform = 'none';
            spans[1].style.opacity = '1';
            spans[2].style.transform = 'none';
        }
    });

    // Close menu when clicking on a link
    const navLinks = document.querySelectorAll('.nav-menu a');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            navMenu.classList.remove('active');
            navToggle.classList.remove('active');
            
            // Reset hamburger animation
            const spans = navToggle.querySelectorAll('span');
            spans[0].style.transform = 'none';
            spans[1].style.opacity = '1';
            spans[2].style.transform = 'none';
        });
    });

    // Close menu when clicking outside
    document.addEventListener('click', (e) => {
        if (!navToggle.contains(e.target) && !navMenu.contains(e.target)) {
            navMenu.classList.remove('active');
            navToggle.classList.remove('active');
            
            // Reset hamburger animation
            const spans = navToggle.querySelectorAll('span');
            spans[0].style.transform = 'none';
            spans[1].style.opacity = '1';
            spans[2].style.transform = 'none';
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
        if (href !== '#' && href !== '#!') {
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
        navbar.style.backgroundColor = 'rgba(255, 255, 255, 0.98)';
        navbar.style.backdropFilter = 'blur(10px)';
    } else {
        navbar.style.boxShadow = '0 2px 4px rgba(0,0,0,0.05)';
        navbar.style.backgroundColor = 'var(--white)';
        navbar.style.backdropFilter = 'none';
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
            entry.target.classList.add('animated');
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
    .blog-card,
    .contact-form,
    .portfolio-full-item
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
        submitBtn.style.opacity = '0.7';

        try {
            // Get form values
            const name = contactForm.querySelector('input[name="name"]').value;
            const email = contactForm.querySelector('input[name="email"]').value;
            const phone = contactForm.querySelector('input[name="phone"]').value;
            const websiteType = contactForm.querySelector('select[name="websiteType"]').value;
            const message = contactForm.querySelector('textarea[name="message"]').value;

            // Basic validation
            if (!name || !email || !phone || !websiteType || !message) {
                throw new Error('Please fill all required fields');
            }

            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                throw new Error('Please enter a valid email address');
            }

            // Phone validation (Nigerian format)
            const phoneRegex = /^[0-9\-\+\s\(\)]{10,}$/;
            if (!phoneRegex.test(phone)) {
                throw new Error('Please enter a valid phone number');
            }

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
            showNotification('✅ Thank you! Your message has been sent successfully. We will contact you soon.', 'success');
            
            // Reset form
            contactForm.reset();

        } catch (error) {
            showNotification(`❌ ${error.message}`, 'error');
            console.error('Form submission error:', error);
        } finally {
            // Re-enable button
            submitBtn.disabled = false;
            submitBtn.textContent = originalBtnText;
            submitBtn.style.opacity = '1';
        }
    });
}

// Simulate form submission (replace with actual API call)
function simulateFormSubmission(data) {
    return new Promise((resolve, reject) => {
        // Log form data to console for testing
        console.log('Form Data Submitted:', data);
        console.log('To send to backend, use:');
        console.log('1. API endpoint: https://your-backend.com/api/contact');
        console.log('2. Method: POST');
        console.log('3. Headers: { "Content-Type": "application/json" }');
        console.log('4. Body: JSON.stringify(data)');
        
        // Simulate network delay
        setTimeout(() => {
            // For demo purposes, we'll simulate success
            // In real implementation, use fetch() to send to your backend
            const isSuccess = Math.random() > 0.1; // 90% success rate for demo
            
            if (isSuccess) {
                resolve({ 
                    success: true, 
                    message: 'Message sent successfully' 
                });
            } else {
                reject(new Error('Network error. Please try again.'));
            }
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
    notification.innerHTML = `
        <div class="notification-content">
            <span class="notification-icon">${type === 'success' ? '✅' : '❌'}</span>
            <span class="notification-text">${message}</span>
        </div>
        <button class="notification-close">&times;</button>
    `;

    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        background-color: ${type === 'success' ? '#10b981' : '#ef4444'};
        color: white;
        padding: 16px 24px;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        z-index: 10000;
        max-width: 400px;
        min-width: 300px;
        font-weight: 500;
        animation: slideIn 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        font-family: inherit;
    `;

    // Add close button styles
    const closeBtn = notification.querySelector('.notification-close');
    closeBtn.style.cssText = `
        background: none;
        border: none;
        color: white;
        font-size: 24px;
        cursor: pointer;
        padding: 0;
        margin: 0;
        line-height: 1;
        opacity: 0.8;
        transition: opacity 0.2s;
    `;
    
    closeBtn.addEventListener('mouseenter', () => {
        closeBtn.style.opacity = '1';
    });
    
    closeBtn.addEventListener('mouseleave', () => {
        closeBtn.style.opacity = '0.8';
    });
    
    closeBtn.addEventListener('click', () => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            notification.remove();
        }, 300);
    });

    // Add content styles
    const content = notification.querySelector('.notification-content');
    content.style.cssText = `
        display: flex;
        align-items: center;
        gap: 12px;
        flex: 1;
    `;

    // Add to page
    document.body.appendChild(notification);

    // Add notification animations to CSS if not already added
    if (!document.querySelector('#notification-styles')) {
        const style = document.createElement('style');
        style.id = 'notification-styles';
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
    }

    // Remove after 5 seconds
    const autoRemove = setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 5000);

    // Clear timeout on hover
    notification.addEventListener('mouseenter', () => {
        clearTimeout(autoRemove);
    });

    notification.addEventListener('mouseleave', () => {
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    });
}

// ===================================
// LAZY LOADING IMAGES
// ===================================
if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                
                // Load the image
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                }
                
                // Load srcset if exists
                if (img.dataset.srcset) {
                    img.srcset = img.dataset.srcset;
                }
                
                // Remove data attributes
                img.removeAttribute('data-src');
                img.removeAttribute('data-srcset');
                
                // Add loaded class for styling
                img.classList.add('loaded');
                
                observer.unobserve(img);
            }
        });
    }, {
        rootMargin: '50px 0px',
        threshold: 0.1
    });

    // Observe all images with data-src attribute
    document.querySelectorAll('img[data-src]').forEach(img => {
        imageObserver.observe(img);
        
        // Add placeholder styling
        if (!img.complete) {
            img.style.opacity = '0';
            img.style.transition = 'opacity 0.3s ease';
            
            img.addEventListener('load', () => {
                img.style.opacity = '1';
            });
            
            img.addEventListener('error', () => {
                img.style.opacity = '1';
                console.warn('Failed to load image:', img.dataset.src);
            });
        }
    });
}

// ===================================
// PORTFOLIO FILTER (for main portfolio page)
// ===================================
function initializePortfolioFilter() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const portfolioItems = document.querySelectorAll('.portfolio-full-item');

    if (filterButtons.length === 0) return;

    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Update active button
            filterButtons.forEach(btn => {
                btn.classList.remove('active');
                btn.style.transform = 'scale(1)';
            });
            
            button.classList.add('active');
            button.style.transform = 'scale(1.05)';

            const filter = button.getAttribute('data-filter');

            // Filter portfolio items with animation
            portfolioItems.forEach(item => {
                const category = item.getAttribute('data-category');
                
                if (filter === 'all' || category === filter) {
                    // Show item with animation
                    item.style.display = 'block';
                    
                    // Trigger reflow for animation
                    void item.offsetWidth;
                    
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0) scale(1)';
                    item.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                } else {
                    // Hide item with animation
                    item.style.opacity = '0';
                    item.style.transform = 'translateY(20px) scale(0.95)';
                    
                    // Hide after animation completes
                    setTimeout(() => {
                        item.style.display = 'none';
                    }, 400);
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
            element.textContent = target.toLocaleString();
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(start).toLocaleString();
        }
    }, 16);
}

// Observe counters
const counterElements = document.querySelectorAll('.counter');
if (counterElements.length > 0) {
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !entry.target.classList.contains('counted')) {
                const target = parseInt(entry.target.getAttribute('data-target')) || 0;
                animateCounter(entry.target, target);
                entry.target.classList.add('counted');
            }
        });
    }, { 
        threshold: 0.5,
        rootMargin: '0px 0px 50px 0px'
    });

    counterElements.forEach(counter => {
        counterObserver.observe(counter);
    });
}

// ===================================
// FAQ ACCORDION (for FAQ pages)
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
                    if (otherItem !== item) {
                        otherItem.classList.remove('active');
                        const otherAnswer = otherItem.querySelector('.faq-answer');
                        if (otherAnswer) {
                            otherAnswer.style.maxHeight = '0';
                            otherAnswer.style.opacity = '0';
                        }
                    }
                });

                // Toggle current item
                if (!isOpen) {
                    item.classList.add('active');
                    answer.style.maxHeight = answer.scrollHeight + 'px';
                    answer.style.opacity = '1';
                    
                    // Add slight delay for smooth transition
                    setTimeout(() => {
                        answer.style.overflow = 'visible';
                    }, 300);
                } else {
                    item.classList.remove('active');
                    answer.style.maxHeight = '0';
                    answer.style.opacity = '0';
                    
                    setTimeout(() => {
                        answer.style.overflow = 'hidden';
                    }, 300);
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
    // Pulse animation every 3 seconds
    setInterval(() => {
        whatsappFloat.style.animation = 'none';
        void whatsappFloat.offsetWidth; // Trigger reflow
        whatsappFloat.style.animation = 'pulse 2s ease';
    }, 3000);

    // Add pulse animation to CSS if not already added
    if (!document.querySelector('#pulse-animation')) {
        const pulseStyle = document.createElement('style');
        pulseStyle.id = 'pulse-animation';
        pulseStyle.textContent = `
            @keyframes pulse {
                0% {
                    transform: scale(1);
                    box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.7);
                }
                70% {
                    transform: scale(1.05);
                    box-shadow: 0 0 0 15px rgba(37, 211, 102, 0);
                }
                100% {
                    transform: scale(1);
                    box-shadow: 0 0 0 0 rgba(37, 211, 102, 0);
                }
            }
        `;
        document.head.appendChild(pulseStyle);
    }

    // Add click tracking
    whatsappFloat.addEventListener('click', () => {
        // You can add analytics tracking here
        console.log('WhatsApp button clicked');
        
        // Optional: Track in Google Analytics
        if (typeof gtag !== 'undefined') {
            gtag('event', 'click', {
                'event_category': 'WhatsApp',
                'event_label': 'Floating Button'
            });
        }
    });
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
                
                // Add animation
                price.style.transform = 'scale(1.1)';
                setTimeout(() => {
                    price.style.transform = 'scale(1)';
                }, 200);
            } else {
                price.textContent = annual;
                annualBtn.classList.add('active');
                monthlyBtn.classList.remove('active');
                
                // Add animation
                price.style.transform = 'scale(1.1)';
                setTimeout(() => {
                    price.style.transform = 'scale(1)';
                }, 200);
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
    // Check if button already exists
    if (document.querySelector('.back-to-top')) return;

    const backToTop = document.createElement('button');
    backToTop.className = 'back-to-top';
    backToTop.innerHTML = '<i class="fas fa-arrow-up"></i>';
    backToTop.setAttribute('aria-label', 'Back to top');
    
    // Add styles
    const style = document.createElement('style');
    style.textContent = `
        .back-to-top {
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
            box-shadow: var(--shadow-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        
        .back-to-top.visible {
            opacity: 1;
            visibility: visible;
        }
        
        .back-to-top:hover {
            background-color: #e57a0d;
            transform: translateY(-5px);
        }
        
        @media (max-width: 768px) {
            .back-to-top {
                bottom: 80px;
                right: 20px;
                width: 45px;
                height: 45px;
                font-size: 18px;
            }
        }
    `;
    document.head.appendChild(style);

    document.body.appendChild(backToTop);

    // Show/hide based on scroll position
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            backToTop.classList.add('visible');
        } else {
            backToTop.classList.remove('visible');
        }
    });

    // Scroll to top on click
    backToTop.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
        
        // Add click animation
        backToTop.style.transform = 'scale(0.9)';
        setTimeout(() => {
            backToTop.style.transform = 'scale(1)';
        }, 200);
    });
}

// ===================================
// FORM INPUT VALIDATION
// ===================================
function initializeFormValidation() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input, textarea, select');
        
        inputs.forEach(input => {
            // Real-time validation
            input.addEventListener('blur', () => {
                validateInput(input);
            });
            
            input.addEventListener('input', () => {
                clearError(input);
            });
        });
    });
}

function validateInput(input) {
    const value = input.value.trim();
    const name = input.getAttribute('name') || input.getAttribute('type');
    
    // Clear previous error
    clearError(input);
    
    // Required validation
    if (input.hasAttribute('required') && !value) {
        showInputError(input, 'This field is required');
        return false;
    }
    
    // Email validation
    if (input.type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            showInputError(input, 'Please enter a valid email address');
            return false;
        }
    }
    
    // Phone validation
    if (input.type === 'tel' && value) {
        const phoneRegex = /^[0-9\-\+\s\(\)]{10,}$/;
        if (!phoneRegex.test(value)) {
            showInputError(input, 'Please enter a valid phone number');
            return false;
        }
    }
    
    return true;
}

function showInputError(input, message) {
    // Remove existing error
    clearError(input);
    
    // Create error element
    const error = document.createElement('div');
    error.className = 'input-error';
    error.textContent = message;
    error.style.cssText = `
        color: #ef4444;
        font-size: 12px;
        margin-top: 5px;
        animation: slideIn 0.3s ease;
    `;
    
    // Add error styling to input
    input.style.borderColor = '#ef4444';
    input.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.1)';
    
    // Insert error after input
    input.parentNode.insertBefore(error, input.nextSibling);
}

function clearError(input) {
    // Remove error message
    const error = input.parentNode.querySelector('.input-error');
    if (error) {
        error.remove();
    }
    
    // Reset input styling
    input.style.borderColor = '';
    input.style.boxShadow = '';
}

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
    // Reinitialize features if needed
    console.log('Window resized, checking features...');
    
    // Reinitialize FAQ if needed
    const faqItems = document.querySelectorAll('.faq-item');
    if (faqItems.length > 0) {
        faqItems.forEach(item => {
            const answer = item.querySelector('.faq-answer');
            if (answer && item.classList.contains('active')) {
                answer.style.maxHeight = answer.scrollHeight + 'px';
            }
        });
    }
}, 250);

window.addEventListener('resize', handleResize);

// ===================================
// PREVENT FORM RESUBMISSION ON REFRESH
// ===================================
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}

// ===================================
// INITIALIZE ALL FUNCTIONS ON PAGE LOAD
// ===================================
document.addEventListener('DOMContentLoaded', () => {
    console.log('%c🚀 MotivLab Website Loaded', 'color: #f68b1e; font-size: 18px; font-weight: bold;');
    console.log('%c📍 www.motivlab.name.ng', 'color: #666; font-size: 12px;');
    
    // Initialize all features
    initializePortfolioFilter();
    initializeFAQ();
    initializePricingToggle();
    initializeFormValidation();
    createBackToTopButton();
    
    // Add loading animation end
    document.body.classList.add('loaded');
    
    // Remove loading state after all images are loaded
    const images = document.querySelectorAll('img');
    let imagesLoaded = 0;
    
    images.forEach(img => {
        if (img.complete) {
            imagesLoaded++;
        } else {
            img.addEventListener('load', () => {
                imagesLoaded++;
                if (imagesLoaded === images.length) {
                    document.body.classList.add('images-loaded');
                }
            });
        }
    });
    
    // If all images are already loaded
    if (imagesLoaded === images.length) {
        document.body.classList.add('images-loaded');
    }
    
    // Add page transition effect
    const mainContent = document.querySelector('main') || document.body;
    mainContent.style.opacity = '0';
    mainContent.style.transition = 'opacity 0.3s ease';
    
    setTimeout(() => {
        mainContent.style.opacity = '1';
    }, 100);
});

// ===================================
// PAGE TRANSITIONS (Smooth page changes)
// ===================================
document.addEventListener('click', (e) => {
    const link = e.target.closest('a');
    
    if (link && link.href && 
        !link.target && 
        !link.href.includes('#') &&
        link.href.startsWith(window.location.origin)) {
        
        e.preventDefault();
        
        // Add loading state
        document.body.classList.add('page-transition');
        
        // Navigate after delay
        setTimeout(() => {
            window.location.href = link.href;
        }, 300);
    }
});

// ===================================
// SERVICE WORKER FOR OFFLINE SUPPORT (Optional)
// ===================================
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').then(registration => {
            console.log('ServiceWorker registration successful');
        }).catch(err => {
            console.log('ServiceWorker registration failed: ', err);
        });
    });
}

// ===================================
// ANALYTICS INTEGRATION (Optional)
// ===================================
function trackEvent(category, action, label) {
    // Google Analytics
    if (typeof gtag !== 'undefined') {
        gtag('event', action, {
            'event_category': category,
            'event_label': label
        });
    }
    
    // Facebook Pixel
    if (typeof fbq !== 'undefined') {
        fbq('trackCustom', action, {
            category: category,
            label: label
        });
    }
    
    // Custom tracking
    console.log(`Track: ${category} - ${action} - ${label}`);
}

// Track page views
window.addEventListener('load', () => {
    trackEvent('Page', 'view', window.location.pathname);
});

// Track button clicks
document.addEventListener('click', (e) => {
    const button = e.target.closest('button, .btn, a[href]');
    if (button) {
        const text = button.textContent.trim() || button.getAttribute('aria-label') || 'Unknown';
        const href = button.getAttribute('href') || window.location.pathname;
        trackEvent('Button', 'click', `${text} (${href})`);
    }
});

// ===================================
// COOKIE CONSENT BANNER (Optional)
// ===================================
function initializeCookieConsent() {
    if (!localStorage.getItem('cookieConsent')) {
        const banner = document.createElement('div');
        banner.id = 'cookie-banner';
        banner.innerHTML = `
            <div class="cookie-content">
                <p>🍪 We use cookies to improve your experience. By continuing to use our site, you accept our <a href="/privacy.html">Privacy Policy</a>.</p>
                <div class="cookie-buttons">
                    <button class="btn btn-secondary" id="cookie-decline">Decline</button>
                    <button class="btn btn-primary" id="cookie-accept">Accept</button>
                </div>
            </div>
        `;
        
        // Add styles
        const style = document.createElement('style');
        style.textContent = `
            #cookie-banner {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                background: var(--white);
                padding: 20px;
                box-shadow: 0 -2px 20px rgba(0,0,0,0.1);
                z-index: 1001;
                border-top: 1px solid var(--border-color);
                animation: slideUp 0.3s ease;
            }
            
            .cookie-content {
                max-width: 1200px;
                margin: 0 auto;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 20px;
            }
            
            .cookie-content p {
                margin: 0;
                flex: 1;
            }
            
            .cookie-buttons {
                display: flex;
                gap: 10px;
            }
            
            @keyframes slideUp {
                from {
                    transform: translateY(100%);
                }
                to {
                    transform: translateY(0);
                }
            }
            
            @media (max-width: 768px) {
                .cookie-content {
                    flex-direction: column;
                    text-align: center;
                }
                
                .cookie-buttons {
                    width: 100%;
                    justify-content: center;
                }
            }
        `;
        document.head.appendChild(style);
        
        document.body.appendChild(banner);
        
        // Add event listeners
        document.getElementById('cookie-accept').addEventListener('click', () => {
            localStorage.setItem('cookieConsent', 'accepted');
            banner.style.animation = 'slideDown 0.3s ease';
            setTimeout(() => banner.remove(), 300);
        });
        
        document.getElementById('cookie-decline').addEventListener('click', () => {
            localStorage.setItem('cookieConsent', 'declined');
            banner.style.animation = 'slideDown 0.3s ease';
            setTimeout(() => banner.remove(), 300);
        });
    }
}

// ===================================
// ERROR HANDLING
// ===================================
window.addEventListener('error', (e) => {
    console.error('JavaScript Error:', e.error);
    // You can send this to your error tracking service
    // Example: trackEvent('Error', 'JavaScript', e.message);
});

window.addEventListener('unhandledrejection', (e) => {
    console.error('Unhandled Promise Rejection:', e.reason);
});
