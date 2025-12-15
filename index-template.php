<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MotivLab - Professional Website Solutions for Nigerian Businesses</title>
    <meta name="description" content="Get modern, fast websites with powerful admin dashboards. Schools, Restaurants, eCommerce, and more.">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="logo">
                <?php if (!empty($settings['site_logo']) && file_exists($settings['site_logo'])): ?>
                    <img src="<?php echo htmlspecialchars($settings['site_logo']); ?>" alt="MotivLab">
                <?php else: ?>
                    <span style="background: #f68b1e; color: white; padding: 10px 15px; border-radius: 8px; font-weight: 700;">ML</span>
                <?php endif; ?>
                <span>MotivLab</span>
            </div>
            <div class="nav-toggle" id="navToggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <ul class="nav-menu" id="navMenu">
                <li><a href="#home">Home</a></li>
                <li><a href="#categories">Categories</a></li>
                <li><a href="#portfolio">Portfolio</a></li>
                <li><a href="#why-us">Why Us</a></li>
                <li><a href="blog/">Blog</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
        </div>
    </nav>

    <section class="hero" id="home">
        <div class="container">
            <div class="hero-content">
                <h1><?php echo htmlspecialchars($settings['hero_headline']); ?></h1>
                <p><?php echo htmlspecialchars($settings['hero_subtext']); ?></p>
                <div class="hero-buttons">
                    <a href="#portfolio" class="btn btn-primary">View Portfolio</a>
                    <a href="#contact" class="btn btn-secondary">Request a Demo</a>
                </div>
            </div>
            <div class="hero-image">
                <?php if (!empty($settings['hero_image']) && file_exists($settings['hero_image'])): ?>
                    <img src="<?php echo htmlspecialchars($settings['hero_image']); ?>" alt="Website Mockups">
                <?php else: ?>
                    <img src="https://placehold.co/800x600/667eea/ffffff?text=Modern+Websites" alt="Website Mockups">
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Continue with rest of your template... I'll add the key dynamic sections -->
    
    <section class="portfolio-preview" id="portfolio">
        <div class="container">
            <h2>Recent Projects</h2>
            <p class="section-subtitle">See what we've built for businesses like yours</p>
            
            <div class="portfolio-grid">
                <?php if (!empty($portfolio_items)): ?>
                    <?php foreach (array_slice($portfolio_items, 0, 6) as $item): ?>
                    <div class="portfolio-item">
                        <?php if ($item['featured_image'] && file_exists($item['featured_image'])): ?>
                            <img src="<?php echo htmlspecialchars($item['featured_image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                        <?php else: ?>
                            <img src="https://placehold.co/600x400/667eea/ffffff?text=<?php echo urlencode($item['title']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                        <?php endif; ?>
                        <div class="portfolio-overlay">
                            <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                            <p><?php echo htmlspecialchars($item['category']); ?></p>
                            <a href="portfolio/<?php echo htmlspecialchars($item['category']); ?>.html">View Details</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Default fallback content -->
                    <div class="portfolio-item">
                        <img src="https://placehold.co/600x400/667eea/ffffff?text=Victory+Schools" alt="Project 1">
                        <div class="portfolio-overlay">
                            <h3>Victory Schools</h3>
                            <p>School Management System</p>
                            <a href="portfolio/school.html">View Details</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="testimonials">
        <div class="container">
            <h2>What Our Clients Say</h2>
            <p class="section-subtitle">Real feedback from real businesses</p>
            
            <div class="testimonial-grid">
                <?php if (!empty($testimonials)): ?>
                    <?php foreach ($testimonials as $testimonial): ?>
                    <div class="testimonial-card">
                        <div class="stars">
                            <?php for ($i = 0; $i < $testimonial['rating']; $i++): ?>
                            <i class="fas fa-star"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="testimonial-text"><?php echo htmlspecialchars($testimonial['review_text']); ?></p>
                        <div class="testimonial-author">
                            <?php if ($testimonial['client_photo'] && file_exists($testimonial['client_photo'])): ?>
                                <img src="<?php echo htmlspecialchars($testimonial['client_photo']); ?>" alt="<?php echo htmlspecialchars($testimonial['client_name']); ?>">
                            <?php else: ?>
                                <img src="https://placehold.co/60x60/667eea/ffffff?text=<?php echo substr($testimonial['client_name'], 0, 2); ?>" alt="<?php echo htmlspecialchars($testimonial['client_name']); ?>">
                            <?php endif; ?>
                            <div>
                                <h4><?php echo htmlspecialchars($testimonial['client_name']); ?></h4>
                                <p><?php echo htmlspecialchars($testimonial['business_name']); ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Default testimonials -->
                    <div class="testimonial-card">
                        <div class="stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="testimonial-text">"MotivLab transformed our school operations. The admin dashboard makes managing students, results, and admissions so easy!"</p>
                        <div class="testimonial-author">
                            <img src="https://placehold.co/60x60/667eea/ffffff?text=MA" alt="Mrs. Adeyemi">
                            <div>
                                <h4>Mrs. Adeyemi</h4>
                                <p>Victory International School</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="contact-section" id="contact">
        <div class="container">
            <h2>Get In Touch</h2>
            <p class="section-subtitle">Have a project in mind? Let's discuss how we can help</p>
            
            <div class="contact-wrapper">
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <div>
                            <h4>Call Us</h4>
                            <p><?php echo htmlspecialchars($settings['contact_phone']); ?></p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <h4>Email Us</h4>
                            <p><?php echo htmlspecialchars($settings['contact_email']); ?></p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <i class="fab fa-whatsapp"></i>
                        <div>
                            <h4>WhatsApp</h4>
                            <p><?php echo htmlspecialchars($settings['whatsapp_number']); ?></p>
                        </div>
                    </div>
                </div>
                
                <form class="contact-form" method="POST" action="assets/js/form-handler.php">
                    <div class="form-group">
                        <input type="text" name="name" placeholder="Your Name" required>
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" placeholder="Your Email" required>
                    </div>
                    <div class="form-group">
                        <input type="tel" name="phone" placeholder="Your Phone Number" required>
                    </div>
                    <div class="form-group">
                        <textarea name="message" placeholder="Tell us about your project" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-large">Send Message</button>
                </form>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> MotivLab. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $settings['whatsapp_number']); ?>" class="whatsapp-float" target="_blank">
        <i class="fab fa-whatsapp"></i>
    </a>

    <script src="assets/js/main.js"></script>
</body>
</html>
