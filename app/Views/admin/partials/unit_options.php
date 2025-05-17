<option value="">-- Pilih Unit --</option>
<?php foreach ($unit as $u): ?>
    <option value="<?= $u['id_unit'] ?>"><?= $u['kode_unit'] ?> - <?= $u['nama_unit'] ?></option>
<?php endforeach; ?>