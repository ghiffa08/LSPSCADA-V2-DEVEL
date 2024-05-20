<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>

<form action="<?= base_url('store-skema-siswa') ?>" method="post">
    <div class="card">
        <div class="card-header">
            <h4>Pilih <?= $siteTitle ?></h4>
        </div>
        <div class="card-body">
            <input type="hidden" name="id" value="<?= isset($dataSkemaSiswa['id_skema_siswa']) ? $dataSkemaSiswa['id_skema_siswa'] : '' ?>">
            <input type="hidden" name="id_siswa" value="<?= isset($dataSkemaSiswa['id_siswa']) ? $dataSkemaSiswa['id_siswa'] : user()->id ?>">
            <input type="hidden" name="email_siswa" value="<?= isset($dataSkemaSiswa['email_siswa']) ? $dataSkemaSiswa['email_siswa'] : user()->email ?>">

            <div class="form-group">
                <label>Skema</label>
                <select class="form-control selectric" name="id_skema">
                    <option value="">Pilih Skema</option>
                    <?php

                    if (isset($listSkema)) {
                        foreach ($listSkema as $row) {
                            isset($dataSkemaSiswa['id_skema']) && $dataSkemaSiswa['id_skema'] == $row['id_skema'] ? $pilih = 'selected' : $pilih = null;
                            echo '<option ' . $pilih . ' value="' . $row['id_skema'] . '">' . $row['nama_skema'] . '</option>';
                        }
                    }

                    ?>
                </select>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                Simpan
            </button>
        </div>
    </div>
</form>
<?= $this->endSection() ?>