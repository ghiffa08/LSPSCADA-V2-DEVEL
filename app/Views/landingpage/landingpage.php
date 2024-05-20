<?= $this->extend("layouts/landingpage/layout-2"); ?>
<?= $this->section("hero"); ?>

<section id="hero-fullscreen" class="hero-fullscreen d-flex align-items-center">
    <div class="container d-flex flex-column align-items-center position-relative " data-aos="zoom-out">
        <h2><?= $siteTitle ?></h2>
        <p><?= $siteSubtitle ?></p>
        <div class="d-flex">
            <a href="#about" class="btn-get-started scrollto">Get Started</a>
            <a href="https://www.youtube.com/watch?v=LXb3EKWsInQ" class="glightbox btn-watch-video d-flex align-items-center"><i class="bi bi-play-circle"></i><span>Watch Video</span></a>
        </div>
    </div>
</section>


<?= $this->endSection() ?>

<?= $this->section("content"); ?>



<?= $this->endSection() ?>