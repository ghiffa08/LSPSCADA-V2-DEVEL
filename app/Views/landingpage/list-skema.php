<?= $this->extend("layouts/landingpage/layout-2") ?>
<?= $this->section("content") ?>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
            <!-- Main Content Section -->
            <div class="col-10 px-0">
                <div class="badge badge-success mb-2">Buka</div>
                <h4 class="mb-0 font-weight-bold">Mastery Deep Learning</h4>
                <p class="text-muted">Lulusan Baru (Fresh Graduate Academy)</p>

                <!-- Metadata Row 1 -->
                <div class="d-flex flex-wrap align-items-center mt-3 text-muted small">
                    <div class="mr-4 mb-2"><i class="fa fa-calendar-alt mr-1"></i> 16 - 22 September 2025</div>
                    <div class="mr-4 mb-2"><i class="fa fa-tag mr-1"></i> Deep Learning</div>
                    <div class="mb-2"><i class="fa fa-map-marker-alt mr-1"></i> Self-Paced (Mandiri)</div>
                </div>

                <!-- Metadata Row 2 -->
                <div class="d-flex flex-wrap align-items-center mt-1 text-muted small">
                    <div class="mr-4 mb-2"><i class="fa fa-microphone mr-1"></i> Pusat Pengembangan Talenta Digital</div>
                    <div class="badge badge-light p-2 font-weight-normal mb-2">
                        <i class="fa fa-clock mr-1"></i> Batas Pendaftaran 14 September 2025
                    </div>
                </div>
            </div>

            <!-- Button Section -->
            <div class="col-2 px-0 text-right">
                <a href="#" class="btn btn-primary">Lihat Detail</a>
            </div>
        </div>
    </div>
</div>

<?= $this->section("js"); ?>

<?= $this->endSection() ?>