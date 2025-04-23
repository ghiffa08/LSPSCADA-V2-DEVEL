<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>Umpan Balik</h4>

            </div>
            <div class="card-body">


                <div class="table-responsive">

                    <table id="table-2" class="table table-bordered table-md">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>No Handphone</th>
                                <th>Feedback</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = null;
                            foreach ($listFeedback as $value) {
                                $no++;
                            ?>
                                <tr>
                                    <td><?= $no ?></td>
                                    <td><?= $value['nama'] ?></td>
                                    <td><?= $value['email'] ?></td>
                                    <td><?= $value['no_hp'] ?></td>
                                    <td><?= $value['feedback'] ?></td>
                                    <td>
                                        <div class="btn-group mb-3" role="group" aria-label="Basic example">
                                            <button type="button" class="btn btn-icon btn-danger" data-toggle="modal" data-target="#deleteFeedbackModal-<?= $value['id']; ?>"><i class="fas fa-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('modals') ?>

!-- Delete-Group-Modal -->
<?php foreach ($listFeedback as $row => $value) { ?>
    <form action="<?= site_url('/delete-umpan-balik'); ?>" method="post">
        <div class="modal fade" id="deleteFeedbackModal-<?= $value['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog  modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Delete Feedback</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <h5>Apakah anda yakin akan menghapus umpan balik dari <span class="text-danger font-weight-bold">"<?= $value['nama']; ?>"</span>?</h5>
                        <input type="hidden" name="id" value="<?= $value['id']; ?>">

                    </div>
                    <div class="modal-footer bg-whitesmoke br">
                        <button type="submit" class="btn btn-danger btn-lg btn-block">
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
<?php } ?>
<?= $this->endSection() ?>