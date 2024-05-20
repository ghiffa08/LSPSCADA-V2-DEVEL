<?= $this->extend("layouts/landingpage/layout"); ?>
<?= $this->section("content"); ?>
<div class="row">

</div>
<div class="row">
    <?php
    foreach ($listSkema as $value) {
    ?>
        <div class="col-12 col-md-6 col-lg-4">

            <div class="card" style="width: 22rem;">
                <div class="card-body">
                    <h5 class="card-title"><?= $value['kode_skema'] ?></h5>
                    <p class="card-text"><?= $value['nama_skema'] ?></p>
                </div>
            </div>

        </div>
    <?php } ?>
</div>

<?= $this->endSection() ?>