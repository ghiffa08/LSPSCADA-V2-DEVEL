<?= $this->extend("layouts/admin/layout-admin") ?>
<?= $this->section("content") ?>

<section id="cbt-section" class="features">
    <form id="cbt-form" action="<?= site_url('/api/pertanyaan-tertulis/save') ?>" method="POST">
        <?= csrf_field(); ?>
        <input type="hidden" name="id_apl1" value="<?= esc($apl1_data['id_apl1']) ?>">
        <input type="hidden" name="id_skema" value="<?= esc($id_skema) ?>">
        <input type="hidden" name="id_asesor" value="<?= esc($id_asesor) ?>">
        <input type="hidden" name="id_ujian" id="id_ujian">

        <!-- Detail Asesi, Skema, dan Asesor -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-info border-left-info">
                    <div class="row">
                        <div class="col-md-4">
                            <h6 class="font-weight-bold mb-1"><i class="fas fa-user mr-1"></i>Asesi</h6>
                            <p class="mb-0"><?= esc($apl1_data['nama_siswa'] ?? 'N/A') ?></p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="font-weight-bold mb-1"><i class="fas fa-bookmark mr-1"></i>Skema Sertifikasi</h6>
                            <p class="mb-0"><?= esc($apl1_data['nama_skema'] ?? 'N/A') ?></p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="font-weight-bold mb-1"><i class="fas fa-user-tie mr-1"></i>Asesor</h6>
                            <p class="mb-0"><?= esc($apl1_data['nama_asesor'] ?? 'N/A') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Kolom Navigasi Soal (Sebelah Kanan di Desktop) -->
            <div class="col-12 col-md-4 order-md-2">
                <div class="card card-primary sticky-top">
                    <div class="card-header">
                        <h4 class="card-title">Navigasi Soal</h4>
                    </div>
                    <div class="card-body">
                        <p>Klik nomor untuk pindah ke soal yang dituju.</p>
                        <div id="question-navigation" class="d-flex flex-wrap">
                            <!-- Tombol navigasi akan di-generate oleh JavaScript -->
                            <div class="text-center w-100">Memuat navigasi...</div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="#" class="card-link" id="finish-button">Selesai & Kirim Jawaban</a>
                    </div>
                </div>
            </div>

            <!-- Kolom Konten Soal (Sebelah Kiri di Desktop) -->
            <div class="col-12 col-md-8 order-md-1">
                <!-- Kontainer Soal -->
                <div id="question-container">
                    <!-- Kartu soal akan di-generate oleh JavaScript -->
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
</section>

<?= $this->endSection() ?>

<?= $this->section("js") ?>
<script>
    const cbtManager = (function() {

        'use strict';



        // --- STATE & CONFIG ---

        const state = {

            id_apl1: '<?= esc($apl1_data['id_apl1']) ?>',

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

            finalSubmitBtn: '#final-submit-btn' // Tombol submit di kartu terakhir

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

                        id_apl1: state.id_apl1

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

            renderFinishCard(); // Tambahkan kartu terakhir

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

            // --- PERBAIKAN: Gunakan .html() untuk mengganti spinner dengan konten soal ---

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

            // Gunakan .append() di sini karena konten soal sudah ada

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



            // Trigger one last save to ensure all data is sent

            debouncedAutosave.flush();



            // Beri sedikit jeda agar save terakhir selesai

            setTimeout(() => {

                Swal.fire('Berhasil!', 'Semua jawaban Anda telah berhasil disimpan.', 'success').then(() => {

                    window.location.href = '<?= site_url('admin/pertanyaan-tertulis') ?>';

                });

            }, 1000); // Jeda 1 detik

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


<?= $this->endSection() ?>