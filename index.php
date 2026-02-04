<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Mai Shop - Repostería artesanal de alta calidad. Tortas, cupcakes, galletas y más delicias hechas con amor.">
    <title>Mai Shop - Repostería Artesanal</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer">

    <link rel="stylesheet" href="Front/landing/style.css?v=2.6">
</head>


<body>
    <!-- Navigation -->
    <nav class="navbar" id="navbar">
        <div class="nav-container">
            <div class="logo">
                <img src="Front/img/mai.png" alt="Mai Shop" style="height: 50px; width: auto;">
            </div>

            <div class="nav-menu" id="navMenu">
                <a href="#inicio" class="nav-link">Inicio</a>
                <a href="#nosotros" class="nav-link">Nosotros</a>
                <a href="#productos" class="nav-link">Productos</a>
                <a href="#galeria" class="nav-link">Galería</a>
                <a href="#contacto" class="nav-link">Contacto</a>
                <a href="https://wa.me/573244917185?text=Hola%20Mai,%20quiero%20hacer%20un%20pedido"
                    class="btn-whatsapp-nav" target="_blank">
                    <i class="fab fa-whatsapp"></i> Pedir Ahora
                </a>
                <a href="Front/login/login.php" class="nav-link" style="margin-left: 10px;">
                    <i class="fas fa-user"></i> Iniciar Sesión
                </a>

            </div>

            <button class="hamburger" id="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero" id="inicio">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1 class="hero-title">
                <span class="hero-subtitle">Bienvenido a</span>
                <img src="Front/img/mai.png" alt="Mai Shop Logo" class="hero-logo-img">
            </h1>
            <p class="hero-description">
                Endulzamos tus momentos especiales con repostería artesanal de la más alta calidad
            </p>
            <div class="hero-buttons">
                <a href="#productos" class="btn btn-primary">
                    <i class="fas fa-cookie-bite"></i> Ver Productos
                </a>
                <a href="https://wa.me/573244917185?text=Hola%20Mai,%20quiero%20información" class="btn btn-secondary"
                    target="_blank">
                    <i class="fab fa-whatsapp"></i> Contáctanos
                </a>
            </div>
        </div>
        <div class="scroll-indicator">
            <i class="fas fa-chevron-down"></i>
        </div>
    </header>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-award"></i>
                </div>
                <h3>Calidad Premium</h3>
                <p>Ingredientes seleccionados de la más alta calidad</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <h3>Hecho con Amor</h3>
                <p>Cada producto es elaborado con dedicación y pasión</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-truck"></i>
                </div>
                <h3>Entrega Rápida</h3>
                <p>Llevamos tus pedidos frescos a tu puerta</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-palette"></i>
                </div>
                <h3>Diseños Únicos</h3>
                <p>Personalizamos según tus preferencias</p>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about" id="nosotros">
        <div class="container">
            <div class="about-content">
                <div class="about-image">
                    <img src="https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=800&q=80"
                        alt="Repostería artesanal">
                    <div class="about-badge">
                        <i class="fas fa-star"></i>
                        <span>+5 Años</span>
                    </div>
                </div>

                <div class="about-text">
                    <span class="section-label">Nuestra Historia</span>
                    <h2 class="section-title">Quiénes Somos</h2>
                    <p class="about-description">
                        En <strong>Mai Shop</strong>, transformamos momentos ordinarios en experiencias extraordinarias
                        a través de la repostería artesanal. Desde 2019, nos hemos dedicado a crear delicias únicas
                        que endulzan los momentos más especiales de nuestros clientes.
                    </p>
                    <p class="about-description">
                        Cada torta, cupcake y postre es elaborado con ingredientes premium, técnicas tradicionales
                        y un toque de creatividad que nos distingue. Nuestro compromiso es superar tus expectativas
                        en cada bocado.
                    </p>

                    <div class="about-stats">
                        <div class="stat">
                            <h3>500+</h3>
                            <p>Clientes Felices</p>
                        </div>
                        <div class="stat">
                            <h3>1000+</h3>
                            <p>Productos Creados</p>
                        </div>
                        <div class="stat">
                            <h3>100%</h3>
                            <p>Satisfacción</p>
                        </div>
                    </div>

                    <div style="margin-top: 2rem; text-align: left;">
                        <p style="margin-bottom: 0.5rem; color: var(--gray-dark); font-weight: 500;">¿Eres universitario
                            y buscas ingresos extra?</p>
                        <a href="Front/unete/unete.php" class="btn btn-primary">
                            Sé parte del equipo <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section class="products" id="productos">
        <div class="container">
            <div class="section-header">
                <span class="section-label">Nuestros Productos</span>
                <h2 class="section-title">Delicias Artesanales</h2>
                <p class="section-description">
                    Descubre nuestra selección de productos elaborados con los mejores ingredientes
                </p>
            </div>

            <div class="products-grid">
                <div class="product-card">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=600&q=80"
                            alt="Tortas Personalizadas">
                        <div class="product-overlay">
                            <a href="https://wa.me/573244917185?text=Quiero%20información%20sobre%20tortas"
                                class="btn-order" target="_blank">
                                <i class="fab fa-whatsapp"></i> Pedir
                            </a>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3>Tortas Personalizadas</h3>
                        <p>Diseños únicos para tus celebraciones especiales</p>
                        <div class="product-price">Desde $35.000</div>
                    </div>
                </div>

                <div class="product-card">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1614707267537-b85aaf00c4b7?w=600&q=80"
                            alt="Cupcakes">
                        <div class="product-overlay">
                            <a href="https://wa.me/573244917185?text=Quiero%20información%20sobre%20cupcakes"
                                class="btn-order" target="_blank">
                                <i class="fab fa-whatsapp"></i> Pedir
                            </a>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3>Cupcakes Gourmet</h3>
                        <p>Deliciosos cupcakes con decoraciones creativas</p>
                        <div class="product-price">$5.000 c/u</div>
                    </div>
                </div>

                <div class="product-card">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=600&q=80"
                            alt="Galletas Decoradas">
                        <div class="product-overlay">
                            <a href="https://wa.me/573244917185?text=Quiero%20información%20sobre%20galletas"
                                class="btn-order" target="_blank">
                                <i class="fab fa-whatsapp"></i> Pedir
                            </a>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3>Galletas Decoradas</h3>
                        <p>Galletas artesanales con glaseado personalizado</p>
                        <div class="product-price">$2.500 c/u</div>
                    </div>
                </div>

                <div class="product-card">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1624353365286-3f8d62daad51?w=600&q=80"
                            alt="Brownies">
                        <div class="product-overlay">
                            <a href="https://wa.me/573244917185?text=Quiero%20información%20sobre%20brownies"
                                class="btn-order" target="_blank">
                                <i class="fab fa-whatsapp"></i> Pedir
                            </a>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3>Brownies Premium</h3>
                        <p>Brownies de chocolate con nueces y más</p>
                        <div class="product-price">$4.000 c/u</div>
                    </div>
                </div>

                <div class="product-card">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1565958011703-44f9829ba187?w=600&q=80"
                            alt="Cheesecake">
                        <div class="product-overlay">
                            <a href="https://wa.me/573244917185?text=Quiero%20información%20sobre%20cheesecake"
                                class="btn-order" target="_blank">
                                <i class="fab fa-whatsapp"></i> Pedir
                            </a>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3>Cheesecake</h3>
                        <p>Suave y cremoso con salsa de frutos rojos</p>
                        <div class="product-price">$28.000</div>
                    </div>
                </div>

                <div class="product-card">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1586985289688-ca3cf47d3e6e?w=600&q=80"
                            alt="Alfajores">
                        <div class="product-overlay">
                            <a href="https://wa.me/573244917185?text=Quiero%20información%20sobre%20alfajores"
                                class="btn-order" target="_blank">
                                <i class="fab fa-whatsapp"></i> Pedir
                            </a>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3>Alfajores</h3>
                        <p>Rellenos de arequipe y cubiertos de coco</p>
                        <div class="product-price">$2.000 c/u</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery Section -->
    <section class="gallery" id="galeria">
        <div class="container">
            <div class="section-header">
                <span class="section-label">Galería</span>
                <h2 class="section-title">Nuestras Creaciones</h2>
                <p class="section-description">
                    Cada producto es una obra de arte comestible
                </p>
            </div>

            <div class="gallery-grid">
                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1562440499-64c9a111f713?w=600&q=80" alt="Galería 1">
                </div>
                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1557925923-cd4648e211a0?w=600&q=80" alt="Galería 2">
                </div>
                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1464349095431-e9a21285b5f3?w=600&q=80" alt="Galería 3">
                </div>
                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1563729784474-d77dbb933a9e?w=600&q=80" alt="Galería 4">
                </div>
                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1486427944299-d1955d23e34d?w=600&q=80" alt="Galería 5">
                </div>
                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1576618148400-f54bed99fcfd?w=600&q=80" alt="Galería 6">
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials">
        <div class="container">
            <div class="section-header">
                <span class="section-label">Testimonios</span>
                <h2 class="section-title">Lo Que Dicen Nuestros Clientes</h2>
            </div>

            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">
                        "La torta de cumpleaños que pedí superó todas mis expectativas. ¡Deliciosa y hermosa!"
                    </p>
                    <div class="testimonial-author">
                        <strong>María González</strong>
                        <span>Cliente Frecuente</span>
                    </div>
                </div>

                <div class="testimonial-card">
                    <div class="stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">
                        "Los cupcakes son increíbles. Perfectos para eventos corporativos. Muy recomendados."
                    </p>
                    <div class="testimonial-author">
                        <strong>Carlos Ramírez</strong>
                        <span>Empresario</span>
                    </div>
                </div>

                <div class="testimonial-card">
                    <div class="stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">
                        "Excelente servicio y productos de calidad. Mi familia quedó encantada con el cheesecake."
                    </p>
                    <div class="testimonial-author">
                        <strong>Ana López</strong>
                        <span>Ama de Casa</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact" id="contacto">
        <div class="container">
            <div class="contact-content">
                <div class="contact-info">
                    <span class="section-label">Contáctanos</span>
                    <h2 class="section-title">Haz Tu Pedido</h2>
                    <p class="contact-description">
                        Estamos listos para endulzar tu próximo evento especial.
                        Contáctanos por WhatsApp y cuéntanos qué necesitas.
                    </p>

                    <div class="contact-details">
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <div>
                                <h4>Teléfono</h4>
                                <p>+57 324 491 7185</p>
                            </div>
                        </div>

                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <h4>Email</h4>
                                <p>maira.sierra@email.com</p>
                            </div>
                        </div>

                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <h4>Ubicación</h4>
                                <p>Bucaramanga, Santander</p>
                            </div>
                        </div>

                        <div class="contact-item">
                            <i class="fas fa-clock"></i>
                            <div>
                                <h4>Horario</h4>
                                <p>Lun - Sáb: 8:00 AM - 6:00 PM</p>
                            </div>
                        </div>
                    </div>

                    <div class="social-links">
                        <a href="https://wa.me/573244917185" target="_blank" class="social-link">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="fab fa-facebook"></i>
                        </a>
                    </div>
                </div>

                <div class="contact-cta">
                    <div class="cta-card">
                        <i class="fab fa-whatsapp"></i>
                        <h3>¿Listo para hacer tu pedido?</h3>
                        <p>Chatea con nosotros en WhatsApp y te ayudaremos a crear el postre perfecto</p>
                        <a href="https://wa.me/573244917185?text=Hola%20Mai,%20quiero%20hacer%20un%20pedido"
                            class="btn btn-whatsapp" target="_blank">
                            <i class="fab fa-whatsapp"></i> Chatear Ahora
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer" id="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <div class="logo">
                        <img src="Front/img/mai.png" alt="Mai Shop"
                            style="height: 50px; width: auto; filter: brightness(0) invert(1);">
                    </div>
                    <p>Endulzando momentos especiales desde 2019</p>
                </div>

                <div class="footer-links">
                    <h4>Enlaces Rápidos</h4>
                    <a href="#inicio">Inicio</a>
                    <a href="#nosotros">Nosotros</a>
                    <a href="#productos">Productos</a>
                    <a href="Front/unete/unete.php">Sé parte del equipo</a>
                    <a href="#contacto">Contacto</a>
                </div>

                <div class="footer-contact">
                    <h4>Contacto</h4>
                    <p><i class="fas fa-phone"></i> +57 324 491 7185</p>
                    <p><i class="fas fa-envelope"></i> maira.sierra@email.com</p>
                    <p><i class="fas fa-map-marker-alt"></i> Bucaramanga, Santander</p>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; 2025 Mai Shop. Todos los derechos reservados.</p>
                <p>Desarrollado con <i class="fas fa-heart"></i> por Maira Alejandra David</p>
            </div>
        </div>
    </footer>

    <!-- WhatsApp Floating Button -->
    <a href="https://wa.me/573244917185?text=Hola%20Mai,%20quiero%20información" class="whatsapp-float" target="_blank">
        <i class="fab fa-whatsapp"></i>
    </a>

    <!-- Scroll to Top Button -->
    <button class="scroll-top" id="scrollTop">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script src="Front/landing/script.js?v=2.7"></script>
</body>

</html> <!-- Login Selection Modal -->
<div class='login-modal-overlay' id='loginModal'>
    <div class='login-modal-content'>
        <div class='modal-cookie-shape'>
            <div class='cookie-bite'></div>
            <h2 class='modal-title'>MAI CONNECT</h2>
            <div class='modal-buttons'>
                <a href='Front/login/login.php?role=admin' class='btn-modal'>Admi</a>
                <a href='Front/login/login.php?role=team' class='btn-modal'>Equipo</a>
            </div>
        </div>
        <button class='modal-close' id='closeModal'>&times;</button>
    </div>
</div>