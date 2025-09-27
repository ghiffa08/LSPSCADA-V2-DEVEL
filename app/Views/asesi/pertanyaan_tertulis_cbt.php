<?= $this->extend("layouts/asesi/layout-app") ?>
<?= $this->section("content") ?>

<section id="cbt-section" class="features">
    <?php if ($dataJawaban == null) : ?>
        <!-- Mode Pengisian -->
        <form id="cbt-form" action="<?= site_url('/api/pertanyaan-tertulis/save') ?>" method="POST">
            <?= csrf_field(); ?>
            <input type="hidden" name="id_pengajuan" value="<?= esc($pengajuan_data['id_pengajuan']) ?>">
            <input type="hidden" name="id_skema" value="<?= esc($id_skema) ?>">
            <input type="hidden" name="id_asesor" value="<?= esc($id_asesor) ?>">
            <input type="hidden" name="id_ujian" id="id_ujian">

            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-info border-left-info">
                        <div class="row">
                            <div class="col-md-4">
                                <h6 class="font-weight-bold mb-1"><i class="fas fa-user mr-1"></i>Asesi</h6>
                                <p class="mb-0"><?= esc($pengajuan_data['nama_lengkap'] ?? 'N/A') ?></p>
                            </div>
                            <div class="col-md-4">
                                <h6 class="font-weight-bold mb-1"><i class="fas fa-bookmark mr-1"></i>Skema Sertifikasi</h6>
                                <p class="mb-0"><?= esc($pengajuan_data['nama_skema'] ?? 'N/A') ?></p>
                            </div>
                            <div class="col-md-4">
                                <h6 class="font-weight-bold mb-1"><i class="fas fa-user-tie mr-1"></i>Asesor</h6>
                                <p class="mb-0"><?= esc($pengajuan_data['nama_asesor'] ?? 'N/A') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-md-4 order-md-2">
                    <div class="card card-primary sticky-top">
                        <div class="card-header">
                            <h4 class="card-title">Navigasi Soal</h4>
                        </div>
                        <div class="card-body">
                            <p>Klik nomor untuk pindah ke soal yang dituju.</p>
                            <div id="question-navigation" class="d-flex flex-wrap">
                                <div class="text-center w-100">Memuat navigasi...</div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="#" class="card-link" id="finish-button">Selesai & Kirim Jawaban</a>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-8 order-md-1">
                    <div id="question-container">
                        <div class="text-center p-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <p class="mt-3">Memuat soal ujian...</p>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    <?php else : ?>
        <!-- Mode Readonly -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-success border-left-success">
                    <div class="row">
                        <div class="col-md-4">
                            <h6 class="font-weight-bold mb-1"><i class="fas fa-user mr-1"></i>Asesi</h6>
                            <p class="mb-0"><?= esc($pengajuan_data['nama_lengkap'] ?? 'N/A') ?></p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="font-weight-bold mb-1"><i class="fas fa-bookmark mr-1"></i>Skema Sertifikasi</h6>
                            <p class="mb-0"><?= esc($pengajuan_data['nama_skema'] ?? 'N/A') ?></p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="font-weight-bold mb-1"><i class="fas fa-user-tie mr-1"></i>Asesor</h6>
                            <p class="mb-0"><?= esc($pengajuan_data['nama_asesor'] ?? 'N/A') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Jawaban -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-info">
                    <h6 class="font-weight-bold mb-1"><i class="fas fa-chart-bar mr-1"></i>Summary Jawaban</h6>
                    <p class="mb-0">Benar: <?= $benar ?> / <?= $totalSoal ?></p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-md-4 order-2">
                <div class="card card-primary">
                    <div class="card-header">
                        <h4 class="card-title mx-auto">Jawaban Anda</h4>
                    </div>
                    <div class="card-body">
                        <div id="nav-pagination-wrapper-readonly">
                            <ul class="nav nav-tabs d-flex justify-content-around" id="navigation-list-readonly">
                                <?php
                                $step_read = 0;
                                $total_soal = count($dataJawaban);
                                foreach ($dataJawaban as $jawaban) {
                                    $step_read++;
                                    $formattedStep = sprintf("%02d", $step_read);
                                ?>
                                    <li class="nav-item">
                                        <button type="button" class="btn btn-success mx-auto m-2 nav-button-readonly <?= ($step_read === 1) ? 'active show' : ''; ?>" data-step="<?= $step_read ?>"><?= $formattedStep ?></button>
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
                    <div class="card-footer text-center">Anda sudah menyelesaikan ujian tertulis ini.</div>
                </div>
            </div>
            <div class="col-12 col-md-8 order-1">
                <div class="tab-content" style="margin-top: 0;">
                    <?php
                    $step_read = 0;
                    foreach ($dataJawaban as $jawaban) {
                        $step_read++;
                        $nextStep = $step_read + 1;
                        $prevStep = $step_read - 1;
                    ?>
                        <div class="tab-pane-readonly <?= ($step_read === 1) ? 'd-block' : 'd-none'; ?>" id="tab-readonly-<?= $step_read ?>">
                            <div class="card card-primary mb-3">
                                <div class="card-header">
                                    <h4 class="card-title mx-auto">Pertanyaan Nomor <?= $step_read ?> dari <?= $total_soal ?></h4>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body">
                                    <div class="question-text mb-4">
                                        <!-- Ambil soal berdasarkan id_soal -->
                                        <?php
                                        $soalModel = new \App\Models\PertanyaanTertulisSoalModel();
                                        $soal = $soalModel->find($jawaban['id_soal']);
                                        echo esc($soal['soal'] ?? 'Soal tidak ditemukan');
                                        ?>
                                    </div>
                                    <div class="answer-options">
                                        <!-- Render jawaban readonly -->
                                        <?php
                                        $pilihanModel = new \App\Models\PertanyaanTertulisPilihanModel();
                                        $pilihan = $pilihanModel->where('id_soal', $jawaban['id_soal'])->findAll();
                                        if ($soal['jenis_soal'] === 'PILIHAN_GANDA') {
                                            foreach ($pilihan as $p) {
                                                $checked = $jawaban['jawaban_pilihan'] == $p['id_pilihan'] ? 'checked' : '';
                                                echo '<div class="custom-control custom-radio mb-2">
                                                        <input type="radio" class="custom-control-input" disabled ' . $checked . '>
                                                        <label class="custom-control-label">' . esc($p['pilihan']) . '</label>
                                                      </div>';
                                            }
                                        } elseif ($soal['jenis_soal'] === 'BENAR_SALAH') {
                                            $yChecked = $jawaban['jawaban_benar_salah'] == 'Y' ? 'checked' : '';
                                            $nChecked = $jawaban['jawaban_benar_salah'] == 'N' ? 'checked' : '';
                                            echo '<div class="custom-control custom-radio custom-control-inline">
                                                    <input type="radio" class="custom-control-input" disabled ' . $yChecked . '>
                                                    <label class="custom-control-label">Benar</label>
                                                  </div>
                                                  <div class="custom-control custom-radio custom-control-inline">
                                                    <input type="radio" class="custom-control-input" disabled ' . $nChecked . '>
                                                    <label class="custom-control-label">Salah</label>
                                                  </div>';
                                        } elseif ($soal['jenis_soal'] === 'ESSAY') {
                                            echo '<textarea class="form-control" rows="5" readonly>' . esc($jawaban['jawaban_essay']) . '</textarea>';
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="card-footer text-center bg-whitesmoke">
                                    <div class="btn-group">
                                        <?php if ($step_read > 1) : ?><button type="button" class="btn btn-primary btn-prev-readonly" data-step="<?= $prevStep ?>"><i class="fas fa-arrow-left"></i> Kembali</button><?php endif; ?>
                                        <button type="button" class="btn btn-primary disabled"><?= $step_read ?> / <?= $total_soal ?></button>
                                        <?php if ($step_read < $total_soal) : ?><button type="button" class="btn btn-primary btn-next-readonly" data-step="<?= $nextStep ?>">Selanjutnya <i class="fas fa-arrow-right"></i></button><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</section>

<?= $this->endSection() ?>

<?= $this->section("js") ?>
<?php if ($dataJawaban == null) : ?>
    <!-- JS untuk mode pengisian -->
    <script>
        const cbtManager = (function() {
            'use strict';
            // --- STATE & CONFIG ---
            const state = {
                id_pengajuan: '<?= esc($pengajuan_data['id_pengajuan']) ?>',
                id_skema: '<?= esc($id_skema) ?>',
                id_ujian: null,
                totalSoal: 0,
                currentQuestion: 1,
                soalList: [],
                csrfName: '<?= csrf_token() ?>',
                csrfHash: '<?= csrf_hash() ?>'
            };
            const selectors = {
                form: '#cbt-form',
                navContainer: '#question-navigation',
                questionContainer: '#question-container',
                finishButton: '#finish-button',
                finalSubmitBtn: '#final-submit-btn'
            };
            // --- AUTOSAVE ---
            const debouncedAutosave = debounce(async function() {
                const formData = $(selectors.form).serialize();
                try {
                    const response = await $.ajax({
                        url: $(selectors.form).attr('action'),
                        type: 'POST',
                        data: formData,
                        dataType: 'json'
                    });
                    if (response.success) {
                        if (response.id_ujian && !state.id_ujian) {
                            state.id_ujian = response.id_ujian;
                            $('#id_ujian').val(state.id_ujian);
                        }
                        updateNavButtonStatus(state.currentQuestion, true);
                        showAutoSaveIndicator();
                    }
                } catch (error) {
                    console.error('Autosave error:', error);
                }
            }, 1000);
            // --- INITIALIZATION ---
            async function init() {
                try {
                    const response = await $.ajax({
                        url: '<?= site_url('api/pertanyaan-tertulis/loadUjian') ?>',
                        type: 'GET',
                        data: {
                            id_skema: state.id_skema,
                            id_pengajuan: state.id_pengajuan
                        },
                        dataType: 'json'
                    });
                    if (response.success) {
                        state.id_ujian = response.ujian_data?.id_ujian || null;
                        $('#id_ujian').val(state.id_ujian);
                        state.soalList = response.struktur.soal_list;
                        state.totalSoal = state.soalList.length;
                        renderUI(state.soalList, response.existing_jawaban);
                        bindEvents();
                    } else {
                        $(selectors.questionContainer).html('<div class="alert alert-danger">Gagal memuat data ujian.</div>');
                    }
                } catch (error) {
                    console.error('Init error:', error);
                    $(selectors.questionContainer).html('<div class="alert alert-danger">Terjadi kesalahan jaringan saat memuat ujian.</div>');
                }
            }
            // --- UI RENDERING ---
            function renderUI(soalList, existingJawaban) {
                if (state.totalSoal === 0) {
                    $(selectors.navContainer).html('<p>Tidak ada soal.</p>');
                    $(selectors.questionContainer).html('<div class="alert alert-warning">Tidak ada soal yang tersedia untuk skema ini.</div>');
                    return;
                }
                renderNavigation(existingJawaban);
                renderQuestions(soalList, existingJawaban);
                renderFinishCard();
                showQuestion(1);
            }

            function renderNavigation(existingJawaban) {
                let navHtml = '';
                for (let i = 1; i <= state.totalSoal; i++) {
                    const soalId = state.soalList[i - 1].id_soal;
                    const isAnswered = !!existingJawaban[soalId];
                    const btnClass = isAnswered ? 'btn-success' : 'btn-outline-primary';
                    navHtml += `<button type="button" class="btn ${btnClass} m-1 nav-button" data-question="${i}">${String(i).padStart(2, '0')}</button>`;
                }
                $(selectors.navContainer).html(navHtml);
            }

            function renderQuestions(soalList, existingJawaban) {
                let questionsHtml = '';
                soalList.forEach((soal, index) => {
                    const jawaban = existingJawaban[soal.id_soal] || {};
                    const questionNumber = index + 1;
                    questionsHtml += `
<div class="question-card" id="question-${questionNumber}" style="display: none;">
<div class="card card-primary mb-3">
<div class="card-header">
<h4 class="card-title mx-auto">Pertanyaan Nomor ${questionNumber} dari ${state.totalSoal}</h4>
</div>
</div>
<div class="card">
<div class="card-body">
<div class="question-text mb-4">${soal.soal}</div>
<div class="answer-options">${renderJawabanInput(soal, jawaban)}</div>
</div>
<div class="card-footer text-center bg-whitesmoke">
<div class="btn-group">
${questionNumber > 1 ? `<button type="button" class="btn btn-primary prev-btn" data-question="${questionNumber - 1}"><i class="fas fa-arrow-left"></i> Kembali</button>` : ''}
<button type="button" class="btn btn-primary" disabled>${questionNumber}</button>
${questionNumber < state.totalSoal ? `<button type="button" class="btn btn-primary next-btn" data-question="${questionNumber + 1}">Selanjutnya <i class="fas fa-arrow-right"></i></button>` : ''}
</div>
</div>
</div>
</div>`;
                });
                $(selectors.questionContainer).html(questionsHtml);
            }

            function renderFinishCard() {
                const finishStepNumber = state.totalSoal + 1;
                const finishHtml = `
<div class="question-card" id="question-${finishStepNumber}" style="display: none;">
<div class="card card-primary mb-3">
<div class="card-header">
<h4 class="card-title mx-auto">Selesai Mengisi Ujian</h4>
</div>
</div>
<div class="card">
<div class="card-body text-center">
<div class="empty-state" data-height="300">
<div class="empty-state-icon bg-success">
<i class="fas fa-check"></i>
</div>
<h2>Anda Telah Mencapai Akhir Ujian</h2>
<p class="lead">
Harap periksa kembali jawaban Anda melalui panel navigasi sebelum mengirimkan hasil ujian.
</p>
</div>
</div>
<div class="card-footer text-center bg-whitesmoke">
<button type="button" class="btn btn-primary prev-btn" data-question="${state.totalSoal}"><i class="fas fa-arrow-left"></i> Kembali ke Soal Terakhir</button>
<button type="button" id="final-submit-btn" class="btn btn-success">
<i class="fas fa-upload"></i> Submit Jawaban Akhir
</button>
</div>
</div>
</div>`;
                $(selectors.questionContainer).append(finishHtml);
            }

            function renderJawabanInput(soal, jawaban) {
                const id = soal.id_soal;
                switch (soal.jenis_soal) {
                    case 'PILIHAN_GANDA':
                        return soal.pilihan.map(p => `
<div class="custom-control custom-radio mb-2">
<input type="radio" id="pilihan_${p.id_pilihan}" name="jawaban[${id}][jawaban_pilihan]" class="custom-control-input" value="${p.id_pilihan}" ${jawaban.jawaban_pilihan == p.id_pilihan ? 'checked' : ''}>
<label class="custom-control-label" for="pilihan_${p.id_pilihan}">${p.pilihan}</label>
</div>`).join('');
                    case 'BENAR_SALAH':
                        return `
<div class="custom-control custom-radio custom-control-inline">
<input type="radio" id="bs_y_${id}" name="jawaban[${id}][jawaban_benar_salah]" class="custom-control-input" value="Y" ${jawaban.jawaban_benar_salah == 'Y' ? 'checked' : ''}>
<label class="custom-control-label" for="bs_y_${id}">Benar</label>
</div>
<div class="custom-control custom-radio custom-control-inline">
<input type="radio" id="bs_n_${id}" name="jawaban[${id}][jawaban_benar_salah]" class="custom-control-input" value="N" ${jawaban.jawaban_benar_salah == 'N' ? 'checked' : ''}>
<label class="custom-control-label" for="bs_n_${id}">Salah</label>
</div>`;
                    case 'ESSAY':
                        return `<textarea name="jawaban[${id}][jawaban_essay]" class="form-control" rows="5" placeholder="Tuliskan jawaban Anda...">${jawaban.jawaban_essay || ''}</textarea>`;
                }
            }
            // --- EVENT HANDLING & LOGIC ---
            function bindEvents() {
                $(document).on('click', '.nav-button, .next-btn, .prev-btn', function() {
                    showQuestion($(this).data('question'));
                });
                $(document).on('change', `${selectors.form} :input`, debouncedAutosave);
                $(selectors.finishButton).on('click', (e) => {
                    e.preventDefault();
                    showQuestion(state.totalSoal + 1);
                });
                $(document).on('click', selectors.finalSubmitBtn, handleFinish);
            }

            function showQuestion(questionNumber) {
                state.currentQuestion = parseInt(questionNumber);
                $('.question-card').hide();
                $(`#question-${state.currentQuestion}`).show();
                $('.nav-button').removeClass('active');
                $(`.nav-button[data-question="${state.currentQuestion}"]`).addClass('active');
            }

            function updateNavButtonStatus(questionNumber, isAnswered) {
                const $button = $(`.nav-button[data-question="${questionNumber}"]`);
                if (isAnswered) {
                    $button.removeClass('btn-outline-primary').addClass('btn-success');
                }
            }

            function handleFinish() {
                const $btn = $(selectors.finalSubmitBtn);
                $btn.html('<i class="fas fa-spinner fa-spin"></i> Memproses...').attr('disabled', true);
                debouncedAutosave.flush();
                setTimeout(() => {
                    Swal.fire('Berhasil!', 'Semua jawaban Anda telah berhasil disimpan.', 'success').then(() => {
                        window.location.href = '<?= site_url('asesi/pertanyaan-tertulis/cbt/' . $pengajuan_data['id_pengajuan']) ?>';
                    });
                }, 1000);
            }
            // --- UTILITY FUNCTIONS ---
            function debounce(func, wait) {
                let timeout;
                const later = function(...args) {
                    clearTimeout(timeout);
                    func.apply(this, args);
                };
                const debounced = function(...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => later.apply(this, args), wait);
                };
                debounced.flush = function() {
                    clearTimeout(timeout);
                    func.apply(this);
                };
                return debounced;
            }

            function showAutoSaveIndicator() {
                let i = $('#autosave-indicator');
                if (i.length === 0) {
                    i = $('<div id="autosave-indicator" style="position: fixed; top: 20px; right: 20px; background-color: #28a745; color: white; padding: 10px 15px; border-radius: 5px; z-index: 1050; display: none;"></div>');
                    $('body').append(i);
                }
                i.html('<i class="fas fa-check-circle mr-2"></i>Jawaban tersimpan').fadeIn().delay(2000).fadeOut();
            }

            function showError(t, m) {
                Swal.fire({
                    icon: 'error',
                    title: t,
                    text: m
                });
            }
            // --- START ---
            init();
        })();
    </script>
<?php else : ?>
    <!-- JS untuk mode readonly -->
    <script>
        $(document).ready(function() {
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
        });
    </script>
<?php endif; ?>
<?= $this->endSection() ?>