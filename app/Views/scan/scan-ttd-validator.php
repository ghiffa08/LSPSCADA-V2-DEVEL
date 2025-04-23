<?= $this->extend("scan/layout/layout"); ?>
<?= $this->section("content"); ?>

<div class="card">
    <div class="card-header">
        <h4>Tanda Tangan <?= $data['nama_validator'] ?></h4>
    </div>
    <div class="card-body">
        <div class="mb-2 text-muted">Click Foto Untuk Melihat Detail!</div>
        <div class="chocolat-parent">
            <a href="<?= base_url('html/upload/tanda tangan/' . $data['tanda_tangan_validator']) ?>" class="chocolat-image" title="<?= $data['nama_validator'] ?>">
                <div data-crop-image="285">
                    <img alt="image" src="<?= base_url('html/upload/tanda tangan/' . $data['tanda_tangan_validator']) ?>" class="img-fluid">
                </div>
            </a>
        </div>
    </div>
    <div class="card-footer">
        <strong><?= date('d/m/Y', strtotime($data['created_at'])) ?></strong>
    </div>


</div>

<?= $this->endSection() ?>