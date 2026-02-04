<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sé parte del equipo - Mai Shop</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Custom Style -->
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="../../index.php" class="logo"
                style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 0.5rem;">
                <img src="../img/mai.png" alt="Mai Shop" style="height: 50px; width: auto;">
            </a>

            <a href="../../index.php" class="nav-link"
                style="text-decoration: none; color: var(--dark); font-weight: 500;">
                <i class="fas fa-arrow-left"></i> Volver al Inicio
            </a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="unete-hero">
        <h1>Únete a la Familia Mai Shop</h1>
        <p>
            Buscamos talento joven y apasionado. Si eres estudiante universitario y quieres generar ingresos extra
            siendo parte de una marca que endulza vidas, ¡este es tu lugar!
        </p>
    </section>

    <!-- Benefits Section -->
    <section class="benefits-grid">
        <div class="benefit-card">
            <i class="fas fa-laptop-house"></i>
            <h3>Trabajo Remoto</h3>
            <p>Trabaja desde casa, la universidad o tu cafetería favorita. Solo necesitas tu celular y buena actitud.
            </p>
        </div>

        <div class="benefit-card">
            <i class="fas fa-clock"></i>
            <h3>Horarios Flexibles</h3>
            <p>Sabemos que estudiar es tu prioridad. Maneja tu propio tiempo y ajusta tus horarios según tus clases.</p>
        </div>

        <div class="benefit-card">
            <i class="fas fa-chart-line"></i>
            <h3>Crecimiento</h3>
            <p>Aprende sobre ventas, marketing y emprendimiento mientras ganas dinero por tus resultados.</p>
        </div>
    </section>

    <!-- Form Section -->
    <section class="application-form-section">
        <h2 class="section-title">Postúlate Ahora</h2>

        <form action="#" method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label for="nombre">Nombre Completo</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" placeholder="Tu nombre" required>
                </div>

                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="tucorreo@ejemplo.com"
                        required>
                </div>

                <div class="form-group">
                    <label for="telefono">WhatsApp</label>
                    <input type="tel" id="telefono" name="telefono" class="form-control" placeholder="+57 300 000 0000"
                        required>
                </div>

                <div class="form-group">
                    <label for="universidad">Universidad</label>
                    <input type="text" id="universidad" name="universidad" class="form-control"
                        placeholder="Nombre de tu U" required>
                </div>

                <div class="form-group">
                    <label for="carrera">Carrera que estudias</label>
                    <input type="text" id="carrera" name="carrera" class="form-control"
                        placeholder="Ej: Administración, Ingeniería..." required>
                </div>

                <div class="form-group">
                    <label for="semestre">Semestre actual</label>
                    <select id="semestre" name="semestre" class="form-control" required>
                        <option value="">Selecciona...</option>
                        <option value="1-3">1° - 3° Semestre</option>
                        <option value="4-7">4° - 7° Semestre</option>
                        <option value="8-10">8° - 10° Semestre</option>
                        <option value="egresado">Egresado</option>
                    </select>
                </div>

                <div class="form-group full-width">
                    <label for="mensaje">Cuéntanos por qué quieres unirte (Opcional)</label>
                    <textarea id="mensaje" name="mensaje" class="form-control" rows="3"
                        placeholder="Breve descripción..."></textarea>
                </div>

                <button type="submit" class="btn-submit">
                    Enviar Postulación <i class="fas fa-paper-plane" style="margin-left: 0.5rem;"></i>
                </button>
            </div>
        </form>
    </section>

</body>

</html>