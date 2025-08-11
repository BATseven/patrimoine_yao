<?php
// Start output buffering
ob_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Accueil - Gestion de Patrimoine</title>
  <meta name="description" content="Expert en gestion de patrimoine, nous offrons des solutions personnalisées pour optimiser votre avenir financier.">
  <meta name="keywords" content="gestion de patrimoine, conseil financier, audit patrimonial, investissement immobilier">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">

  <!-- Template Metadata -->
  <!-- Template Name: Dewi -->
  <!-- Template URL: https://bootstrapmade.com/dewi-free-multi-purpose-html-template/ -->
  <!-- Updated: Aug 07 2024 with Bootstrap v5.3.3 -->
  <!-- Author: BootstrapMade.com -->
  <!-- License: https://bootstrapmade.com/license/ -->
</head>

<body class="index-page">

  <header id="header" class="header d-flex align-items-center fixed-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center">

      <a href="index.php" class="logo d-flex align-items-center me-auto">
        <h1 class="sitename">Patrimoine Plus</h1>
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="#accueil" class="active">Accueil</a></li>
          <li><a href="#a-propos">À propos</a></li>
          <li><a href="#services">Nos services</a></li>
          <li><a href="#temoignages">Témoignages</a></li>
          <li><a href="#blog">Blog</a></li>
          <li><a href="#contact">Contact</a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

      <a class="cta-btn" href="login.php">Démarrer</a>

    </div>
  </header>

  <main class="main">

    <!-- Accueil Section -->
    <section id="accueil" class="hero section dark-background">
      <img src="assets/img/hero-bg.jpg" alt="" data-aos="fade-in">

      <div class="container d-flex flex-column align-items-center">
        <h2 data-aos="fade-up" data-aos-delay="100">SÉCURISEZ VOTRE AVENIR FINANCIER</h2>
        <p data-aos="fade-up" data-aos-delay="200">Nos experts en gestion de patrimoine vous accompagnent pour optimiser vos investissements et atteindre vos objectifs.</p>
        <div class="d-flex mt-4" data-aos="fade-up" data-aos-delay="300">
          <a href="#a-propos" class="btn-get-started">Découvrir nos services</a>
          <a href="https://www.youtube.com/watch?v=Y7f98aduVJ8" class="glightbox btn-watch-video d-flex align-items-center"><i class="bi bi-play-circle"></i><span>Voir la vidéo</span></a>
        </div>
      </div>
    </section><!-- /Accueil Section -->

    <!-- À propos Section -->
    <section id="a-propos" class="about section">
      <div class="container">
        <div class="row gy-4">
          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
            <h3>Notre mission : Votre prospérité financière</h3>
            <img src="assets/img/about.jpg" class="img-fluid rounded-4 mb-4" alt="">
            <p>Chez Patrimoine Plus, nous croyons en une gestion patrimoniale sur mesure. Notre mission est de maximiser la valeur de vos actifs tout en protégeant votre avenir.</p>
            <p>Avec une vision axée sur l'excellence et l'innovation, notre équipe d'experts travaille à vos côtés pour élaborer des stratégies financières adaptées à vos besoins.</p>
          </div>
          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="250">
            <div class="content ps-0 ps-lg-5">
              <p class="fst-italic">
                Nous combinons expertise et engagement pour offrir des solutions financières durables.
              </p>
              <ul>
                <li><i class="bi bi-check-circle-fill"></i> <span>Conseil personnalisé en gestion de patrimoine.</span></li>
                <li><i class="bi bi-check-circle-fill"></i> <span>Stratégies d'investissement adaptées à vos objectifs.</span></li>
                <li><i class="bi bi-check-circle-fill"></i> <span>Accompagnement à long terme pour sécuriser votre patrimoine.</span></li>
              </ul>
              <p>
                Notre équipe, composée de conseillers expérimentés, est dédiée à votre succès financier.
              </p>
              <div class="position-relative mt-4">
                <img src="assets/img/about-2.jpg" class="img-fluid rounded-4" alt="">
                <a href="https://www.youtube.com/watch?v=Y7f98aduVJ8" class="glightbox pulsating-play-btn"></a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section><!-- /À propos Section -->

    <!-- Nos services Section -->
    <section id="services" class="services section">
      <div class="container section-title" data-aos="fade-up">
        <h2>Nos services</h2>
        <p>Découvrez nos solutions en gestion de patrimoine<br></p>
      </div>
      <div class="container" data-aos="fade-up" data-aos-delay="100">
        <div class="row gy-5">
          <div class="col-xl-4 col-md-6" data-aos="zoom-in" data-aos-delay="200">
            <div class="service-item">
              <div class="img">
                <img src="assets/img/services-1.jpg" class="img-fluid" alt="">
              </div>
              <div class="details position-relative">
                <div class="icon">
                  <i class="bi bi-activity"></i>
                </div>
                <a href="service-details.php" class="stretched-link">
                  <h3>Conseil patrimonial</h3>
                </a>
                <p>Analyse approfondie de votre situation financière pour des recommandations sur mesure.</p>
              </div>
            </div>
          </div>
          <div class="col-xl-4 col-md-6" data-aos="zoom-in" data-aos-delay="300">
            <div class="service-item">
              <div class="img">
                <img src="assets/img/services-2.jpg" class="img-fluid" alt="">
              </div>
              <div class="details position-relative">
                <div class="icon">
                  <i class="bi bi-broadcast"></i>
                </div>
                <a href="service-details.php" class="stretched-link">
                  <h3>Audit patrimonial</h3>
                </a>
                <p>Évaluation complète de vos actifs pour optimiser votre stratégie d'investissement.</p>
              </div>
            </div>
          </div>
          <div class="col-xl-4 col-md-6" data-aos="zoom-in" data-aos-delay="400">
            <div class="service-item">
              <div class="img">
                <img src="assets/img/services-3.jpg" class="img-fluid" alt="">
              </div>
              <div class="details position-relative">
                <div class="icon">
                  <i class="bi bi-easel"></i>
                </div>
                <a href="service-details.php" class="stretched-link">
                  <h3>Gestion immobilière</h3>
                </a>
                <p>Accompagnement dans l'acquisition, la gestion et l'optimisation de vos biens immobiliers.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section><!-- /Nos services Section -->

    <!-- Témoignages Section -->
    <section id="temoignages" class="testimonials section dark-background">
      <img src="assets/img/testimonials-bg.jpg" class="testimonials-bg" alt="">
      <div class="container" data-aos="fade-up" data-aos-delay="100">
        <div class="swiper init-swiper">
          <script type="application/json" class="swiper-config">
            {
              "loop": true,
              "speed": 600,
              "autoplay": {
                "delay": 5000
              },
              "slidesPerView": "auto",
              "pagination": {
                "el": ".swiper-pagination",
                "type": "bullets",
                "clickable": true
              }
            }
          </script>
          <div class="swiper-wrapper">
            <div class="swiper-slide">
              <div class="testimonial-item">
                <img src="assets/img/testimonials/testimonials-1.jpg" class="testimonial-img" alt="">
                <h3>Jean Dupont</h3>
                <h4>Entrepreneur</h4>
                <div class="stars">
                  <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                </div>
                <p>
                  <i class="bi bi-quote quote-icon-left"></i>
                  <span>Grâce à Patrimoine Plus, j'ai optimisé mes investissements et sécurisé mon avenir financier. Leur approche personnalisée est remarquable.</span>
                  <i class="bi bi-quote quote-icon-right"></i>
                </p>
              </div>
            </div>
            <div class="swiper-slide">
              <div class="testimonial-item">
                <img src="assets/img/testimonials/testimonials-2.jpg" class="testimonial-img" alt="">
                <h3>Marie Lefèvre</h3>
                <h4>Directrice financière</h4>
                <div class="stars">
                  <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                </div>
                <p>
                  <i class="bi bi-quote quote-icon-left"></i>
                  <span>L'audit patrimonial réalisé par l'équipe a transformé ma stratégie d'investissement. Des conseils clairs et efficaces.</span>
                  <i class="bi bi-quote quote-icon-right"></i>
                </p>
              </div>
            </div>
            <div class="swiper-slide">
              <div class="testimonial-item">
                <img src="assets/img/testimonials/testimonials-3.jpg" class="testimonial-img" alt="">
                <h3>Paul Martin</h3>
                <h4>Investisseur immobilier</h4>
                <div class="stars">
                  <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                </div>
                <p>
                  <i class="bi bi-quote quote-icon-left"></i>
                  <span>Leur expertise en gestion immobilière m'a permis d'optimiser mes rendements tout en réduisant les risques.</span>
                  <i class="bi bi-quote quote-icon-right"></i>
                </p>
              </div>
            </div>
          </div>
          <div class="swiper-pagination"></div>
        </div>
      </div>
    </section><!-- /Témoignages Section -->

    <!-- Blog / Actualités financières Section -->
    <section id="blog" class="portfolio section">
      <div class="container section-title" data-aos="fade-up">
        <h2>Blog / Actualités financières</h2>
        <p>Dernières nouvelles et conseils en gestion de patrimoine</p>
      </div>
      <div class="container">
        <div class="row gy-4">
          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
            <div class="portfolio-content h-100">
              <img src="assets/img/portfolio/app-1.jpg" class="img-fluid" alt="">
              <div class="portfolio-info">
                <h4>Comment diversifier vos investissements</h4>
                <p>Conseils pratiques pour répartir vos actifs et minimiser les risques.</p>
                <a href="blog-details.php" title="Lire l'article" class="details-link"><i class="bi bi-link-45deg"></i></a>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
            <div class="portfolio-content h-100">
              <img src="assets/img/portfolio/product-1.jpg" class="img-fluid" alt="">
              <div class="portfolio-info">
                <h4>Les tendances du marché immobilier 2025</h4>
                <p>Analyse des opportunités d'investissement immobilier pour l'année à venir.</p>
                <a href="blog-details.php" title="Lire l'article" class="details-link"><i class="bi bi-link-45deg"></i></a>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="400">
            <div class="portfolio-content h-100">
              <img src="assets/img/portfolio/branding-1.jpg" class="img-fluid" alt="">
              <div class="portfolio-info">
                <h4>Planification successorale : ce qu'il faut savoir</h4>
                <p>Comment protéger votre patrimoine pour les générations futures.</p>
                <a href="blog-details.php" title="Lire l'article" class="details-link"><i class="bi bi-link-45deg"></i></a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section><!-- /Blog Section -->

    <!-- Contact Section -->
    <section id="contact" class="contact section">
      <div class="container section-title" data-aos="fade-up">
        <h2>Contact</h2>
        <p>Contactez-nous pour une consultation personnalisée</p>
      </div>
      <div class="container" data-aos="fade-up" data-aos-delay="100">
        <div class="row gy-4">
          <div class="col-lg-6">
            <div class="row gy-4">
              <div class="col-lg-12">
                <div class="info-item d-flex flex-column justify-content-center align-items-center" data-aos="fade-up" data-aos-delay="200">
                  <i class="bi bi-geo-alt"></i>
                  <h3>Adresse</h3>
                  <p>01 BP 5245 Abidjan 01, Cocody, Riviera Palmeraie, Abidjan, Côte d'Ivoire</p>
                </div>
                <div class="info-item d-flex flex-column justify-content-center align-items-center" data-aos="fade-up" data-aos-delay="250">
                  <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d19827.3555270857!2d-3.97076164802848!3d5.3705745962214655!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1sfr!2sci!4v1754469420264!5m2!1sfr!2sci" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
              </div>
              <div class="col-md-6">
                <div class="info-item d-flex flex-column justify-content-center align-items-center" data-aos="fade-up" data-aos-delay="300">
                  <i class="bi bi-telephone"></i>
                  <h3>Téléphone</h3>
                  <p>+225 27 22 49 37 36<br>+225 07 07 05 84 77</p>
                </div>
              </div>
              <div class="col-md-6">
                <div class="info-item d-flex flex-column justify-content-center align-items-center" data-aos="fade-up" data-aos-delay="400">
                  <i class="bi bi-envelope"></i>
                  <h3>Email</h3>
                  <p>info@burinfort.ci</p>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-6">
            <form action="forms/contact.php" method="post" class="php-email-form" data-aos="fade-up" data-aos-delay="500">
              <div class="row gy-4">
                <div class="col-md-6">
                  <input type="text" name="name" class="form-control" placeholder="Votre nom" required>
                </div>
                <div class="col-md-6">
                  <input type="email" class="form-control" name="email" placeholder="Votre email" required>
                </div>
                <div class="col-md-12">
                  <input type="text" class="form-control" name="subject" placeholder="Sujet" required>
                </div>
                <div class="col-md-12">
                  <textarea class="form-control" name="message" rows="4" placeholder="Votre message" required></textarea>
                </div>
                <div class="col-md-12 text-center">
                  <div class="loading">Envoi en cours...</div>
                  <div class="error-message"></div>
                  <div class="sent-message">Votre message a été envoyé. Merci !</div>
                  <button type="submit">Envoyer</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </section><!-- /Contact Section -->

  </main>

  <footer id="footer" class="footer dark-background">
    <div class="container footer-top">
      <div class="row gy-4">
        <div class="col-lg-4 col-md-6 footer-about">
          <a href="index.php" class="logo d-flex align-items-center">
            <span class="sitename">Patrimoine Plus</span>
          </a>
          <div class="footer-contact pt-3">
            <p>01 BP 5245 Abidjan 01, Cocody, Riviera Palmeraie</p>
            <p>Abidjan, Côte d'Ivoire</p>
            <p class="mt-3"><strong>Téléphone :</strong> <span>+225 27 22 49 37 36</span></p>
            <p><strong>Email :</strong> <span>info@burinfort.ci</span></p>
          </div>
          <div class="social-links d-flex mt-4">
            <a href=""><i class="bi bi-twitter-x"></i></a>
            <a href=""><i class="bi bi-facebook"></i></a>
            <a href=""><i class="bi bi-instagram"></i></a>
            <a href=""><i class="bi bi-linkedin"></i></a>
          </div>
        </div>
        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Liens utiles</h4>
          <ul>
            <li><i class="bi bi-chevron-right"></i> <a href="#accueil">Accueil</a></li>
            <li><i class="bi bi-chevron-right"></i> <a href="#a-propos">À propos</a></li>
            <li><i class="bi bi-chevron-right"></i> <a href="#services">Nos services</a></li>
            <li><i class="bi bi-chevron-right"></i> <a href="#">Mentions légales</a></li>
            <li><i class="bi bi-chevron-right"></i> <a href="#">Politique de confidentialité</a></li>
          </ul>
        </div>
        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Nos services</h4>
          <ul>
            <li><i class="bi bi-chevron-right"></i> <a href="#">Conseil patrimonial</a></li>
            <li><i class="bi bi-chevron-right"></i> <a href="#">Audit patrimonial</a></li>
            <li><i class="bi bi-chevron-right"></i> <a href="#">Gestion immobilière</a></li>
            <li><i class="bi bi-chevron-right"></i> <a href="#">Planification successorale</a></li>
            <li><i class="bi bi-chevron-right"></i> <a href="#">Optimisation fiscale</a></li>
          </ul>
        </div>
        <div class="col-lg-4 col-md-12 footer-newsletter">
          <h4>Notre newsletter</h4>
          <p>Abonnez-vous pour recevoir les dernières actualités financières et nos conseils exclusifs !</p>
          <form action="forms/newsletter.php" method="post" class="php-email-form">
            <div class="newsletter-form"><input type="email" name="email"><input type="submit" value="S'abonner"></div>
            <div class="loading">Envoi en cours...</div>
            <div class="error-message"></div>
            <div class="sent-message">Votre demande d'abonnement a été envoyée. Merci !</div>
          </form>
        </div>
      </div>
    </div>
    <div class="container copyright text-center mt-4">
      <p>© <span>Copyright</span> <strong class="px-1 sitename">Patrimoine Plus</strong> <span>Tous droits réservés</span></p>
      <div class="credits">
        Conçu par <a href="https://bootstrapmade.com/">BootstrapMade</a> Distribué par <a href="https://themewagon.com">ThemeWagon</a>
      </div>
    </div>
  </footer>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Preloader -->
  <div id="preloader"></div>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
  <script src="assets/vendor/imagesloaded/imagesloaded.pkgd.min.js"></script>
  <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>

  <!-- Main JS File -->
  <script src="assets/js/main.js"></script>

</body>

</html>
<?php
// Flush output buffer
ob_end_flush();
?>