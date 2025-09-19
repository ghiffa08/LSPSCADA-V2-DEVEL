<?= $this->extend("layouts/landingpage/layout-2") ?>
<?= $this->section("content") ?>

<div class="card border- shadow-sm mb-4">
    <div class="card-body">
        <div class="input-group">
            <input type="text" class="form-control" id="search-input" placeholder="Cari berdasarkan nama skema, jenis, atau TUK...">
            <div class="input-group-append">
                <button class="btn btn-primary" id="search-button" type="button"><i class="fa fa-search"></i> Cari</button>
            </div>
        </div>
    </div>
</div>

<div id="asesmen-list">
</div>

<div id="loading-spinner" class="text-center my-5" style="display: none;">
    <div class="spinner-border text-primary" role="status">
        <span class="sr-only">Loading...</span>
    </div>
</div>


<div id="no-data-message" class="text-center my-5" style="display: none;">
    <div class="card-body">
        <i class="fa fa-box-open fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">Data Asesmen Tidak Ditemukan</h5>
        <p class="text-secondary">Coba ubah kata kunci pencarian Anda atau muat ulang halaman.</p>
    </div>
</div>

<nav class="mt-4 d-flex justify-content-center">
    <ul class="pagination" id="pagination-links">
    </ul>
</nav>

<?= $this->endSection() ?>

<?= $this->section("scripts") ?>
<script>
    $(function() {
        // --- CONFIGURATION ---
        const API_URL = "<?= site_url('api/getAsesmenJson') ?>";
        let currentPage = 1;
        let currentSearch = '';

        // --- CORE FUNCTION TO LOAD DATA ---
        function loadAsesmen(page = 1, search = '') {
            currentPage = page;
            currentSearch = search;

            $('#loading-spinner').show();
            $('#asesmen-list').empty();
            $('#no-data-message').hide();
            $('#pagination-links').empty();

            $.ajax({
                url: API_URL,
                type: 'GET',
                dataType: 'json',
                data: {
                    page: page,
                    search: search
                },
                success: function(response) {
                    $('#loading-spinner').hide();

                    // Check if data exists
                    if (response.data && response.data.length > 0) {
                        // Populate asesmen list
                        response.data.forEach(function(asesmen) {
                            const cardHtml = `
                            <div class="card border- shadow-sm mb-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="col-10 px-0">
                                            <div class="badge badge-success mb-2">Buka</div>
                                            <h5 class="mb-0 font-weight-bold">${asesmen.nama_skema || '-'}</h5>
                                            <p class="text-muted">${asesmen.jenis_skema || '-'}</p>
                                            <div class="d-flex flex-wrap align-items-center mt-3 text-muted small">
                                                <div class="mr-4 mb-2"><i class="fa fa-calendar-alt mr-1"></i> ${formatDate(asesmen.tanggal) || '-'}</div>
                                                <div class="mr-4 mb-2"><i class="fa fa-tag mr-1"></i> ${asesmen.jenis_tuk || '-'}</div>
                                                <div class="mb-2"><i class="fa fa-map-marker-alt mr-1"></i> ${asesmen.nama_tuk || '-'}</div>
                                            </div>
                                            <div class="d-flex flex-wrap align-items-center mt-1 text-muted small">
                                                 <div class="mr-4 mb-2"><i class="fa fa-microphone mr-1"></i> LSP-P1 SMKN 2 KUNINGAN</div>
                                                 <div class="badge badge-light p-2 font-weight-normal mb-2">
                                                     <i class="fa fa-clock mr-1"></i> Batas Pendaftaran 14 September 2025
                                                 </div>
                                             </div>
                                        </div>
                                        <div class="col-2 px-0 text-right">
                                            <a href="<?= site_url('asesmen-detail/') ?>${asesmen.id_asesmen}" class="btn btn-primary">Lihat Detail</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                            $('#asesmen-list').append(cardHtml);
                        });

                        // Build pagination
                        buildPagination(response.total, response.limit, currentPage);

                    } else {
                        $('#no-data-message').show();
                    }
                },
                error: function() {
                    $('#loading-spinner').hide();
                    alert('Gagal memuat data. Silakan coba lagi.');
                }
            });
        }

        // --- HELPER FUNCTION FOR PAGINATION ---
        function buildPagination(totalItems, limit, currentPage) {
            const totalPages = Math.ceil(totalItems / limit);
            if (totalPages <= 1) return; // Don't show pagination if there's only one page

            let paginationHtml = '';

            // Previous button
            paginationHtml += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${currentPage - 1}">«</a></li>`;

            // Page numbers
            for (let i = 1; i <= totalPages; i++) {
                paginationHtml += `<li class="page-item ${i === currentPage ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
            }

            // Next button
            paginationHtml += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${currentPage + 1}">»</a></li>`;

            $('#pagination-links').html(paginationHtml);
        }

        // --- HELPER FUNCTION TO FORMAT DATE ---
        function formatDate(dateString) {
            if (!dateString) return null;
            const options = {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                timeZone: 'Asia/Jakarta'
            };
            // Your format_tanggal_indonesia seems to handle this.
            // This is a simple JS equivalent if you don't have that helper in JS.
            return new Date(dateString).toLocaleDateString('id-ID', options);
        }


        // --- EVENT HANDLERS ---

        // Search button click
        $('#search-button').on('click', function() {
            loadAsesmen(1, $('#search-input').val().trim());
        });

        // Search on pressing Enter key
        $('#search-input').on('keypress', function(e) {
            if (e.which === 13) { // Enter key
                $('#search-button').click();
            }
        });

        // Pagination link click (using event delegation)
        $(document).on('click', '.page-link', function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            if (page) {
                loadAsesmen(page, currentSearch);
            }
        });

        // --- INITIAL LOAD ---
        loadAsesmen();
    });
</script>
<?= $this->endSection() ?>