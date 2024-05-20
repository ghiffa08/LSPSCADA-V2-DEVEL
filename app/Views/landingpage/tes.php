<section id="hero-static" class="hero-static d-flex align-items-center">
    <div class="container d-flex flex-column justify-content-center align-items-center text-center position-relative" data-aos="zoom-out">
        <h2>Tabungan Bank <span>Raksa</span></h2>
        <p>Ikuti langkah-langkah berikut dan isi data diri anda untuk membuka rekening tabungan Bank <strong>Raksa</strong>.</p>
    </div>
</section>

<main id="main">
    <!-- ======= Features Section ======= -->
</main>


<!-- ======= Clients Section ======= -->
<section id="clients" class="clients">
    <div class="container" data-aos="zoom-out">

        <div class="clients-slider swiper">
            <div class="swiper-wrapper align-items-center">
                <div class="swiper-slide"><img src="assets/img/clients/logobpr.png" class="img-fluid" alt=""></div>
                <div class="swiper-slide"><img src="assets/img/clients/bpr.png" class="img-fluid" alt=""></div>
                <div class="swiper-slide"><img src="assets/img/clients/ojk.png" class="img-fluid" alt=""></div>
                <div class="swiper-slide"><img src="assets/img/clients/lps.png" class="img-fluid" alt=""></div>
                <div class="swiper-slide"><img src="assets/img/clients/ayo-ke-bank.png" class="img-fluid" alt=""></div>
                <div class="swiper-slide"><img src="assets/img/clients/perbarindo.png" class="img-fluid" alt=""></div>
                <div class="swiper-slide"><img src="assets/img/clients/bi.png" class="img-fluid" alt=""></div>
            </div>
        </div>

    </div>
</section><!-- End Clients Section -->

<!-- ======= Footer ======= -->
<footer id="footer" class="footer">

    <div class="footer-content">
        <div class="container">
            <div class="row">
                <div class="col-md-12 mb-3">
                    <div class="footer-info">
                        <h3 class="fs-1">PT. RAKSA WACANA AGRI PURNAMA</h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="footer-info">
                        <h3>Kantor Pusat</h3>
                        <p>
                            <strong>Kantor Pusat Cilimus </strong><br>
                            Alamat: Jl. Raya Bojong No. 229 Cilimus - Kuningan<br>
                            Telepon: (0232) 613418
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="footer-info">
                        <h3>Kantor Cabang</h3>
                        <p>
                            <strong>Kantor Cabang Kuningan </strong><br>
                            Alamat: Jl Raya Siliwangi Ruko Taman Kota Blok A No. 01 Kuningan<br>
                            Telepon: (0232) 881969<br>
                            <strong>Kantor Cabang Luragung </strong><br>
                            Alamat: Jl Raya Luragung Desa Cirahayu. Luragung<br>
                            Telepon: (0232) 870104
                        </p>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="footer-info">
                        <h3>Kantor Kas</h3>
                        <p>
                            <strong>Kantor Kas Cibingbin </strong><br>
                            Alamat: Pasar Cibingbin RT.15 RW.01 Dusun Kliwon Desa Cibingbin Cibingbin <br>
                            Telepon: (0232) 881969<br>
                            <strong>Kantor Kas Mandirancan </strong><br>
                            Alamat: Jl. Raya Kasab No. 75 Desa Nanggela Kec. Mandirancan <br>
                            Telepon: (0232) 881969<br>
                            <strong>Kantor Kas Darma </strong><br>
                            Alamat: Jl. Raya Pasar Darma Dusun Paleben RT.012 RW.002 Darma <br>
                            Telepon: (0232) 881969<br>
                            <strong>Kantor Kas Ciawigebang </strong><br>
                            Alamat: Jl. Raya Susukan Blok Pasar Ciawigebang Kec. Ciawigebang <br>
                            Telepon: (0232) 881969<br>

                        </p>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="footer-legal text-center">
        <div class="container d-flex flex-column flex-lg-row justify-content-center justify-content-lg-between align-items-center">

            <div class="d-flex flex-column align-items-center align-items-lg-start">
                <div class="copyright">
                    &copy; Copyright <strong><span>PT. BPR RAKSA WACANA AGRI PURNAMA <?php echo date('Y') ?>.</span></strong> All Rights Reserved
                </div>
            </div>

            <div class="social-links order-first order-lg-last mb-3 mb-lg-0">
                <a href="https://twitter.com/bpr_rwap" class="twitter"><i class="bi bi-twitter"></i></a>
                <a href="https://www.facebook.com/bpr.rwap/" class="facebook"><i class="bi bi-facebook"></i></a>
                <a href="https://www.instagram.com/bank_raksa/" class="instagram"><i class="bi bi-instagram"></i></a>
            </div>

        </div>
    </div>

</footer><!-- End Footer -->

<a href="#" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

<div id="preloader"></div>

<!-- Vendor JS Files -->
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/vendor/aos/aos.js"></script>
<script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
<script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
<script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
<script src="assets/vendor/php-email-form/validate.js"></script>

<!-- Template Main JS File -->
<script src="assets/js/main.js"></script>
<script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>

<!--SweetAlert JS-->
<script src="assets/sweetalert2/sweetalert2.min.js"></script>
<script>
    document.getElementById('submit-button').addEventListener('click', function(event) {
        event.preventDefault();

        const formData = new FormData($('form')[0]);

        const loadingModal = Swal.fire({
            title: 'Proses Sedang Berlangsung',
            html: '<div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div><p class="mt-2">Tunggu sebentar...</p>',
            showConfirmButton: false,
            allowOutsideClick: false,
        });

        $.ajax({
            type: 'POST',
            url: '<?= site_url('Form_tabungan/tabungan_simpan'); ?>',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                setTimeout(function() {
                    loadingModal.close();

                    if (response === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sukses',
                            confirmButtonColor: "#0ea2bd",
                            text: 'Data permohonan pembukaan rekening tabungan berhasil disimpan, tunggu info lebih lanjut melalui whatsapp.'
                        }).then(function() {
                            window.location.href = '<?= site_url('landingpage'); ?>';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            confirmButtonColor: "#0ea2bd",
                            text: 'Gagal, periksa kembali dan isi form dengan teliti.'
                        });
                    }
                }, 2000);
            }
        });
    });
</script>
<script>
    $(document).ready(function() {
        $("#provinsi").change(function() {
            var url = "<?php echo site_url('Landingpage/add_ajax_kab'); ?>/" + $(this).val();
            $('#kabupaten').load(url);
            return false;
        })

        $("#kabupaten").change(function() {
            var url = "<?php echo site_url('Landingpage/add_ajax_kec'); ?>/" + $(this).val();
            $('#kecamatan').load(url);
            return false;
        })

        $("#kecamatan").change(function() {
            var url = "<?php echo site_url('Landingpage/add_ajax_des'); ?>/" + $(this).val();
            $('#desa').load(url);
            return false;
        })
    });

    function showCard(cardNumber) {
        for (let i = 1; i <= 3; i++) {
            const tab = document.getElementById('tab-' + i);
            tab.style.display = 'none';

            const button = document.getElementById('nextToCard' + i);
            button.classList.remove('active');
        }

        const activeTab = document.getElementById('tab-' + cardNumber);
        activeTab.style.display = 'block';

        const activeButton = document.getElementById('nextToCard' + cardNumber);
        activeButton.classList.add('active');
    }


    function enableNextButton(tabNumber) {
        const radioBersedia = document.getElementById("setuju");
        const checkBoxSyaratKetentuan = document.getElementById("syarat_ketentuan");
        const nextToCard3 = document.getElementById("nextToCard3");
        const nextToCard4 = document.getElementById("nextToCard4");

        if (tabNumber === 2) {
            if (radioBersedia.checked && checkBoxSyaratKetentuan.checked) {
                nextToCard3.disabled = false;
                nextbutton.disabled = false;
            } else {
                nextToCard3.disabled = true;
                nextbutton.disabled = true;
            }
        }
    }
</script>
</body>

</html>