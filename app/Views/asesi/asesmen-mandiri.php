<?= $this->extend("layouts/asesi/layout-app") ?>
<?= $this->section("styles") ?>
<style>
    /* Menargetkan tombol di dalam navigasi pengisian dan read-only */
    #navigation-list .btn,
    #navigation-list-readonly .btn {
        width: 45px;
        height: 45px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;

    }
</style>

<?= $this->endSection() ?>

<?= $this->section("content") ?>

<section id="features" class="features">
    <form id="upload-form" action="javascript:void(0);" method="POST">
        <?php if ($dataAPL2 == null) : ?>
            <input type="hidden" name="id_pengajuan" id="id_pengajuan" value="<?= esc($dataPengajuan['pengajuan']['id_pengajuan']) ?>">
            <?php if (isset($dataPengajuan) && $dataPengajuan['pengajuan']['status_pengajuan'] == "diterima") : ?>
                <div class="row">
                    <div class="col-12 col-md-4 order-2">
                        <div class="card card-primary ">
                            <div class="card-header">
                                <h4 class="card-title mx-auto">Navigasi Jawaban</h4>
                            </div>
                            <div class="card-body">
                                <div id="nav-pagination-wrapper">
                                    <ul id="navigation-list" class="nav nav-tabs d-flex justify-content-around">
                                        <?php
                                        $step_nav = 0;
                                        foreach ($listKukNav as $kukNav) {
                                            $step_nav++;
                                            $formattedStep = sprintf("%02d", $step_nav);
                                        ?>
                                            <li class="nav-item">
                                                <button type="button" class="btn btn-outline-primary mx-auto m-2" data-step="<?= $step_nav ?>" data-kuk-id="<?= $kukNav['id_kuk'] ?>">
                                                    <?= $formattedStep ?>
                                                </button>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                </div>
                                <div id="nav-pagination-controls" class="d-flex justify-content-between mt-2">
                                    <button type="button" class="btn btn-sm btn-light" id="nav-prev-page"><i class="fas fa-chevron-left"></i></button>
                                    <span id="nav-page-info"></span>
                                    <button type="button" class="btn btn-sm btn-light" id="nav-next-page"><i class="fas fa-chevron-right"></i></button>
                                </div>
                            </div>
                            <div class="card-footer text-center">
                                <a class="card-link" href="#" data-step="<?= $totalKuk + 1 ?>">Selesai & Submit</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-8 order-1">
                        <div id="asesmen-content-wrapper">
                            <div class="text-center p-5">
                                <div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>
                                <p class="mt-2">Memuat pertanyaan...</p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="empty-state" data-height="400">
                            <div class="empty-state-icon bg-danger"><i class="fas fa-times"></i></div>
                            <h2>Pengajuan Asesmen Anda belum diterima</h2>
                            <p class="lead">Untuk melanjutkan, harap tunggu pengajuan asesmen Anda diterima oleh Admin/Asesor.</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php else : ?>
            <div class="row">
                <div class="col-12 col-md-4 order-2">
                    <div class="card card-primary ">
                        <div class="card-header">
                            <h4 class="card-title mx-auto">Jawaban Anda</h4>
                        </div>
                        <div class="card-body">
                            <div id="nav-pagination-wrapper-readonly">
                                <ul class="nav nav-tabs d-flex justify-content-around" id="navigation-list-readonly">
                                    <?php
                                    $step_nav_read = 0;
                                    foreach ($dataAPL2 as $jawaban) {
                                        $step_nav_read++;
                                        $formattedStep = sprintf("%02d", $step_nav_read);
                                    ?>
                                        <li class="nav-item">
                                            <button type="button" class="btn <?= $jawaban['tk'] == 'K' ? 'btn-success' : 'btn-secondary' ?> mx-auto m-2 nav-button-readonly <?= ($step_nav_read === 1) ? 'active show' : ''; ?>" data-step="<?= $step_nav_read ?>"><?= $formattedStep ?></button>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </div>
                            <div id="nav-pagination-controls-readonly" class="d-flex justify-content-between mt-2">
                                <button type="button" class="btn btn-sm btn-light" id="nav-prev-page-readonly"><i class="fas fa-chevron-left"></i></button>
                                <span id="nav-page-info-readonly"></span>
                                <button type="button" class="btn btn-sm btn-light" id="nav-next-page-readonly"><i class="fas fa-chevron-right"></i></button>
                            </div>
                        </div>
                        <div class="card-footer text-center">Anda sudah menyelesaikan asesmen mandiri ini.</div>
                    </div>
                </div>
                <div class="col-12 col-md-8 order-1">
                    <div class="tab-content" style="margin-top: 0;">
                        <?php
                        $step_read = 0;
                        $total_apl2 = count($dataAPL2);
                        foreach ($dataAPL2 as $jawaban) {
                            $step_read++;
                            $nextStep = $step_read + 1;
                            $prevStep = $step_read - 1;
                        ?>
                            <div class="tab-pane-readonly <?= ($step_read === 1) ? 'd-block' : 'd-none'; ?>" id="tab-readonly-<?= $step_read ?>">
                                <div class="card card-primary mb-3">
                                    <div class="card-header">
                                        <h4 class="card-title mx-auto"><?= esc($jawaban['nama_unit']) ?></h4>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header">
                                        <h4>Elemen: <?= esc($jawaban['nama_elemen']) ?></h4>
                                    </div>
                                    <div class="card-body">
                                        <p class="font-weight-bold">Kriteria Unjuk Kerja:</p>
                                        <p><?= esc($jawaban['pertanyaan']) ?></p>
                                        <hr>
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item"><input type="radio" <?= $jawaban['tk'] == 'K' ? 'checked' : '' ?> disabled><label class="ml-2">Kompeten</label></li>
                                            <li class="list-group-item"><input type="radio" <?= $jawaban['tk'] == 'BK' ? 'checked' : '' ?> disabled><label class="ml-2">Belum Kompeten</label></li>
                                        </ul>
                                        <?php if ($jawaban['tk'] == 'K') : ?>
                                            <div class="mt-3">
                                                <label>Bukti Pendukung:</label>
                                                <input type="text" class="form-control" value="<?= esc($jawaban['bukti_pendukung']) ?>" readonly>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-footer text-center bg-whitesmoke br">
                                        <div class="btn-group">
                                            <?php if ($step_read > 1) : ?><button type="button" class="btn btn-primary btn-prev-readonly" data-step="<?= $prevStep ?>"><i class="fas fa-arrow-left"></i><span class="ml-2">Kembali</span></button><?php endif; ?>
                                            <button type="button" class="btn btn-primary disabled"><?= $step_read ?> / <?= $total_apl2 ?></button>
                                            <?php if ($step_read < $total_apl2) : ?><button type="button" class="btn btn-primary btn-next-readonly" data-step="<?= $nextStep ?>"><span class="mr-2">Selanjutnya</span><i class="fas fa-arrow-right"></i></button><?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </form>
</section>
<?= $this->endSection() ?>

<?= $this->section("js") ?>
<script>
    $(document).ready(function() {
        <?php if ($dataAPL2 == null && isset($dataPengajuan) && $dataPengajuan['pengajuan']['status_pengajuan'] == "diterima") : ?>

            // =================================================================
            // KONFIGURASI DAN STATE UTAMA
            // =================================================================
            const config = {
                itemsPerPage: 15,
                totalSteps: <?= $totalKuk + 1 ?>,
                id_pengajuan: $('#id_pengajuan').val(),
                csrfName: '<?= csrf_token() ?>',
                csrfHash: '<?= csrf_hash() ?>',
                urls: {
                    getStep: '<?= site_url('asesmen/get-step/') ?>',
                    validateStep: '<?= site_url('asesmen/validate-step') ?>',
                    store: '<?= site_url("asesmen-mandiri/store") ?>'
                }
            };

            let currentStep = 1;
            let savedAnswers = JSON.parse('<?= $savedAnswers ?>' || '{}');
            const contentWrapper = $('#asesmen-content-wrapper');

            // =================================================================
            // LOGIKA PAGINASI NAVIGASI
            // =================================================================
            const navItems = $('#navigation-list .nav-item');
            const totalNavPages = Math.ceil(navItems.length / config.itemsPerPage);
            let currentNavPage = 1;

            function showNavPage(page) {
                if (page < 1 || page > totalNavPages) return;
                navItems.hide().slice((page - 1) * config.itemsPerPage, page * config.itemsPerPage).show();
                $('#nav-page-info').text(`Hal ${page} / ${totalNavPages}`);
                $('#nav-prev-page').prop('disabled', page === 1);
                $('#nav-next-page').prop('disabled', page === totalNavPages);
                currentNavPage = page;
            }
            if (navItems.length <= config.itemsPerPage) {
                $('#nav-pagination-controls').hide();
            }

            // =================================================================
            // FUNGSI UTAMA (RENDER, SHOW, UPDATE)
            // =================================================================
            function updateNavStatus() {
                $('#navigation-list button').each(function() {
                    const kukId = $(this).data('kuk-id');
                    if (savedAnswers[kukId]) {
                        $(this).removeClass('btn-outline-primary btn-danger').addClass('btn-primary');
                    }
                });
            }

            function showStep(step) {
                const pageOfStep = Math.ceil(step / config.itemsPerPage);
                if (pageOfStep !== currentNavPage && step < config.totalSteps) showNavPage(pageOfStep);

                $('#navigation-list button, .card-footer a').removeClass('active show');
                $(`[data-step=${step}]`).addClass('active show');

                contentWrapper.html(`<div class="text-center p-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Memuat...</p></div>`);

                if (step < config.totalSteps) {
                    const kukId = $(`[data-step=${step}]`).data('kuk-id');
                    $.get(`${config.urls.getStep}${kukId}`, (response) => {
                        if (response.success) renderKuk(response.data, step);
                    }, 'json');
                } else {
                    renderSubmitPage();
                }
                currentStep = step;
            }

            function renderKuk(data, step) {
                const prevBtn = step > 1 ? `<button type="button" class="btn btn-primary btn-prev"><i class="fas fa-arrow-left"></i> Kembali</button>` : '';
                const nextBtnLabel = step === (config.totalSteps - 1) ? 'Selesai' : 'Selanjutnya';
                const nextBtnIcon = step === (config.totalSteps - 1) ? 'fa-check' : 'fa-arrow-right';
                const nextBtn = `<button type="button" class="btn btn-primary btn-next"><span class="mr-2">${nextBtnLabel}</span><i class="fas ${nextBtnIcon}"></i></button>`;

                const answer = savedAnswers[data.id_kuk] || {};
                const isK = answer.tk === 'K' ? 'checked' : '';
                const isBK = answer.tk === 'BK' ? 'checked' : '';
                const buktiVisible = answer.tk === 'K' ? '' : 'd-none';

                const html = `
                <div class="card card-primary mb-3"><div class="card-header"><h4 class="card-title mx-auto">${data.nama_unit}</h4></div></div>
                <div class="card" data-current-kuk-id="${data.id_kuk}">
                    <div class="card-header"><h4>Elemen: ${data.nama_elemen}</h4></div>
                    <div class="card-body">
                        <p class="font-weight-bold">Kriteria Unjuk Kerja:</p><p>${data.pertanyaan}</p><hr>
                        <div class="validation-error text-danger font-weight-bold mb-3"></div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><input class="bk-option" type="radio" name="bk_${data.id_kuk}" id="bk_k_${data.id_kuk}" value="K" data-bukti-kompeten="bukti_${data.id_kuk}" ${isK}><label for="bk_k_${data.id_kuk}" class="ml-2">Kompeten</label></li>
                            <li class="list-group-item"><input class="bk-option" type="radio" name="bk_${data.id_kuk}" id="bk_bk_${data.id_kuk}" value="BK" data-bukti-kompeten="bukti_${data.id_kuk}" ${isBK}><label for="bk_bk_${data.id_kuk}" class="ml-2">Belum Kompeten</label></li>
                        </ul>
                        <div class="mt-3 bukti-kompeten ${buktiVisible}" id="bukti_${data.id_kuk}">
                            <label>Bukti Pendukung:</label>
                            <select class="form-control" name="bukti_pendukung_${data.id_kuk}">
                                <option value="">Pilih Bukti Pendukung</option>
                                <option value="Nilai Raport" ${answer.bukti_pendukung === 'Nilai Raport' ? 'selected' : ''}>Nilai Raport</option>
                                <option value="Sertifikat Praktek Kerja Lapangan (PKL)" ${answer.bukti_pendukung === 'Sertifikat Praktek Kerja Lapangan (PKL)' ? 'selected' : ''}>Sertifikat Praktek Kerja Lapangan (PKL)</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-footer text-center bg-whitesmoke br"><div class="btn-group">${prevBtn}<button type="button" class="btn btn-primary disabled">${step}/${config.totalSteps - 1}</button>${nextBtn}</div></div>
                </div>`;
                contentWrapper.html(html);
            }

            function renderSubmitPage() {
                const html = `
                <div class="card">
                    <div class="card-body"><div class="empty-state" data-height="400">
                        <div class="empty-state-icon"><i class="fas fa-rocket"></i></div>
                        <h2>Submit Asesmen Mandiri</h2>
                        <p class="lead">Pastikan semua jawaban telah terisi. Jawaban yang sudah divalidasi akan berwarna biru di panel navigasi.</p>
                        <button type="button" id="btn-submit-asesmen" class="btn btn-primary mt-4"><i class="fas fa-upload"></i> Submit Jawaban Saya</button>
                        <div id="submit-spinner" class="mt-3 d-none"><div class="spinner-border text-primary" role="status"></div></div>
                    </div></div>
                    <div class="card-footer text-center"><button type="button" class="btn btn-primary btn-prev"><i class="fas fa-arrow-left"></i> Kembali</button></div>
                </div>`;
                contentWrapper.html(html);
            }

            // =================================================================
            // EVENT HANDLERS
            // =================================================================
            $('#nav-prev-page').on('click', () => showNavPage(currentNavPage - 1));
            $('#nav-next-page').on('click', () => showNavPage(currentNavPage + 1));
            $(document).on('click', '.btn-prev', () => showStep(currentStep - 1));
            $('#navigation-list button, .card-footer a').on('click', function(e) {
                e.preventDefault();
                const stepToShow = $(this).data('step');
                if (stepToShow !== currentStep) showStep(stepToShow);
            });

            $(document).on('click', '.btn-next', function() {
                const card = $(this).closest('.card');
                const kukId = card.data('current-kuk-id');
                let dataToValidate = {
                    id_pengajuan: config.id_pengajuan,
                    id_kuk: kukId,
                    [config.csrfName]: config.csrfHash
                };
                dataToValidate['bk_' + kukId] = $(`input[name=bk_${kukId}]:checked`).val();
                dataToValidate['bukti_pendukung_' + kukId] = $(`select[name=bukti_pendukung_${kukId}]`).val();

                $.post(config.urls.validateStep, dataToValidate, function(response) {
                    if (response.csrf_hash) {
                        config.csrfHash = response.csrf_hash;
                    }
                    if (response.success) {
                        savedAnswers = response.savedAnswers;
                        updateNavStatus();
                        showStep(currentStep + 1);
                    } else {
                        card.find('.validation-error').text(response.message || 'Jawaban tidak valid.');
                        $(`button[data-kuk-id=${kukId}]`).removeClass('btn-primary').addClass('btn-danger');
                    }
                }, 'json').fail(() => card.find('.validation-error').text('Gagal terhubung ke server.'));
            });

            $(document).on('click', '#btn-submit-asesmen', function() {
                const btn = $(this);
                btn.prop('disabled', true);
                $('#submit-spinner').removeClass('d-none');

                let dataToSubmit = {
                    id_pengajuan: config.id_pengajuan,
                    [config.csrfName]: config.csrfHash
                };

                $.post(config.urls.store, dataToSubmit, function(response) {
                        if (response.csrf_hash) {
                            config.csrfHash = response.csrf_hash;
                        }
                        if (response.success) {
                            Swal.fire({
                                    title: 'Berhasil!',
                                    text: 'Asesmen mandiri Anda telah berhasil disubmit.',
                                    icon: 'success',
                                    timer: 2000,
                                    showConfirmButton: false
                                })
                                .then(() => {
                                    window.location.href = response.redirectUrl;
                                });
                        } else {
                            Swal.fire({
                                title: 'Gagal!',
                                text: response.message || 'Terjadi kesalahan.',
                                icon: 'error'
                            });
                        }
                    }, 'json')
                    .fail(() => Swal.fire({
                        title: 'Error!',
                        text: 'Tidak dapat terhubung ke server.',
                        icon: 'error'
                    }))
                    .always(() => {
                        btn.prop('disabled', false);
                        $('#submit-spinner').addClass('d-none');
                    });
            });

            // =================================================================
            // INISIALISASI
            // =================================================================
            updateNavStatus();
            showNavPage(1);
            showStep(1);
        <?php endif; ?>

        <?php if ($dataAPL2 != null) : ?>
            // PAGINASI NAVIGASI READ-ONLY
            const itemsPerPageRO = 15;
            const navItemsRO = $('#navigation-list-readonly .nav-item');
            const totalNavPagesRO = Math.ceil(navItemsRO.length / itemsPerPageRO);
            let currentNavPageRO = 1;

            function showNavPageRO(page) {
                if (page < 1 || page > totalNavPagesRO) return;
                navItemsRO.hide().slice((page - 1) * itemsPerPageRO, page * itemsPerPageRO).show();
                $('#nav-page-info-readonly').text(`Hal ${page} / ${totalNavPagesRO}`);
                $('#nav-prev-page-readonly').prop('disabled', page === 1);
                $('#nav-next-page-readonly').prop('disabled', page === totalNavPagesRO);
                currentNavPageRO = page;
            }
            if (navItemsRO.length <= itemsPerPageRO) {
                $('#nav-pagination-controls-readonly').hide();
            }

            $('#nav-prev-page-readonly').on('click', () => showNavPageRO(currentNavPageRO - 1));
            $('#nav-next-page-readonly').on('click', () => showNavPageRO(currentNavPageRO + 1));

            function showReadOnlyStep(step) {
                const pageOfStep = Math.ceil(step / itemsPerPageRO);
                if (pageOfStep !== currentNavPageRO) showNavPageRO(pageOfStep);
                $('.tab-pane-readonly').removeClass('d-block').addClass('d-none');
                $('#tab-readonly-' + step).removeClass('d-none').addClass('d-block');
                $('.nav-button-readonly').removeClass('active show');
                $(`.nav-button-readonly[data-step=${step}]`).addClass('active show');
            }
            $(document).on('click', '.nav-button-readonly, .btn-next-readonly, .btn-prev-readonly', function() {
                const stepToShow = $(this).data('step');
                showReadOnlyStep(stepToShow);
            });
            showNavPageRO(1);
        <?php endif; ?>
    });

    $(document).on('change', '.bk-option', function() {
        const buktiKompetenId = $(this).data('bukti-kompeten');
        const buktiKompetenDiv = $('#' + buktiKompetenId);
        if (this.value === 'K') {
            buktiKompetenDiv.removeClass('d-none');
        } else {
            buktiKompetenDiv.addClass('d-none');
        }
    });
</script>
<?= $this->endSection() ?>