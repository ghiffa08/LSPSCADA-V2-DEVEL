  <!-- General JS Scripts -->
  <script src="<?= base_url('assets/stisla/node_modules/jquery/dist/jquery.min.js') ?>"></script>
  <script src="<?= base_url('assets/stisla/node_modules/popper.js/dist/umd/popper.min.js') ?>"></script>
  <script src="<?= base_url('assets/stisla/node_modules/bootstrap/dist/js/bootstrap.min.js') ?>"></script>
  <script src="<?= base_url('assets/stisla/node_modules/jquery.nicescroll/dist/jquery.nicescroll.min.js') ?>"></script>
  <script src="<?= base_url('assets/stisla/node_modules/moment/min/moment.min.js') ?>"></script>
  <script src="<?= base_url('assets/stisla/assets/js/stisla.js') ?>"></script>

  <!-- JS Libraries -->
  <script src="<?= base_url('assets/stisla/node_modules/summernote/dist/summernote-bs4.js') ?>"></script>
  <script src="<?= base_url('assets/stisla/node_modules/sweetalert2/dist/sweetalert2.min.js') ?>"></script>
  <script src="<?= base_url('assets/stisla/node_modules/chocolat/dist/js/jquery.chocolat.min.js') ?>"></script>
  <script src="<?= base_url('assets/stisla/node_modules/selectric/public/jquery.selectric.min.js') ?>"></script>
  <script src="<?= base_url('assets/stisla/node_modules/select2/dist/js/select2.full.min.js') ?>"></script>
  <script src="<?= base_url('assets/stisla/node_modules/jquery_upload_preview/assets/js/jquery.uploadPreview.min.js') ?>"></script>

  <!-- Data Tables -->
  <script src="<?= base_url('assets/stisla/node_modules/datatables/media/js/jquery.dataTables.min.js') ?>"></script>
  <script src="<?= base_url('assets/stisla/node_modules/datatables.net-bs4/js/dataTables.bootstrap4.min.js') ?>"></script>
  <script src="<?= base_url('assets/stisla/node_modules/datatables.net-select-bs4/js/select.bootstrap4.min.js') ?>"></script>

  <!-- FilePond Libraries -->
  <script src="<?= base_url('assets/stisla/node_modules/filepond-plugin-file-validate-size/filepond-plugin-file-validate-size.min.js') ?>"></script>
  <script src="<?= base_url('assets/stisla/node_modules/filepond-plugin-file-validate-type/filepond-plugin-file-validate-type.min.js') ?>"></script>
  <script src="<?= base_url('assets/stisla/node_modules/filepond-plugin-image-crop/filepond-plugin-image-crop.min.js') ?>"></script>
  <script src="<?= base_url('assets/stisla/node_modules/filepond-plugin-image-exif-orientation/filepond-plugin-image-exif-orientation.min.js') ?>"></script>
  <script src="<?= base_url('assets/stisla/node_modules/filepond-plugin-image-filter/filepond-plugin-image-filter.min.js') ?>"></script>
  <script src="<?= base_url('assets/stisla/node_modules/filepond-plugin-image-preview/filepond-plugin-image-preview.min.js') ?>"></script>
  <script src="<?= base_url('assets/stisla/node_modules/filepond-plugin-image-resize/filepond-plugin-image-resize.min.js') ?>"></script>
  <script src="<?= base_url('assets/stisla/node_modules/filepond/filepond.js') ?>"></script>
  <script src="<?= base_url('assets/stisla/node_modules/izitoast/dist/js/iziToast.min.js') ?>"></script>
  <script src="<?= base_url('assets/stisla/assets/js/page/modules-toastr.js') ?>"></script>
  <!-- DATATABLE -->
  <?= initDataTable('#basicTable') ?>

  <?= $this->include('layouts/admin/datatable-helper') ?>

  <!-- 
    Basic:
  initDataTable('#basicTable')

  Custom Option:
  initDataTable('#tableUser', [
  'serverSide' => true,
  'ajax' => base_url('/user/data'), // atau url apapun
  ])

-->


  <!-- FilePond Configuration -->
  <script>
      FilePond.registerPlugin(
          FilePondPluginImagePreview,
          FilePondPluginImageCrop,
          FilePondPluginImageExifOrientation,
          FilePondPluginImageFilter,
          FilePondPluginImageResize,
          FilePondPluginFileValidateSize,
          FilePondPluginFileValidateType
      );

      const filePondConfig = {
          credits: null,
          allowImagePreview: true,
          allowImageFilter: true,
          allowImageExifOrientation: false,
          allowImageCrop: false,
          imageFilterColorMatrix: [
              0.299, 0.587, 0.114, 0, 0,
              0.299, 0.587, 0.114, 0, 0,
              0.299, 0.587, 0.114, 0, 0,
              0.0, 0.0, 0.0, 1, 0
          ],
          acceptedFileTypes: ["image/png", "image/jpg", "image/jpeg"],
          fileValidateTypeDetectType: (source, type) =>
              new Promise((resolve) => {
                  resolve(type);
              }),
          storeAsFile: true,
      };

      // Initialize FilePond elements
      document.querySelectorAll('.filepond-upload').forEach(element => {
          FilePond.create(element, filePondConfig);
      });
  </script>

  <!-- Sweet Alert Notifications -->
  <script>
      <?php if (session()->getFlashdata('pesan')) : ?>
          Swal.fire({
              title: 'Sukses!',
              text: '<?= session()->getFlashdata('pesan') ?>',
              icon: 'success',
              confirmButtonText: 'OK'
          });
      <?php endif; ?>

      <?php if (session()->getFlashdata('warning')) : ?>
          Swal.fire({
              title: 'Peringatan!',
              text: '<?= session()->getFlashdata('warning') ?>',
              icon: 'warning',
              allowOutsideClick: false,
              allowEscapeKey: false,
              allowEnterKey: false,
              showConfirmButton: true,
              confirmButtonText: 'OK',
          }).then((result) => {
              if (result.isConfirmed) {
                  var modalId = '<?= session()->getFlashdata('modal_id') ?>';
                  if (modalId) {
                      $('#' + modalId).modal('show');
                  }
              }
          });
      <?php endif; ?>
  </script>

  <!-- Upload Preview Configuration -->
  <script>
      $(document).ready(function() {
          // Configure all upload previews
          $('.image-upload-container').each(function() {
              const previewId = $(this).data('preview');
              const uploadId = $(this).data('upload');
              const labelId = $(this).data('label');

              $.uploadPreview({
                  input_field: "#" + uploadId,
                  preview_box: "#" + previewId,
                  label_field: "#" + labelId,
                  label_default: "Pilih File",
                  label_selected: "Ganti File",
                  no_label: false
              });
          });
      });
  </script>

  <!-- Card Tabs -->
  <script>
      function showCard(cardNumber) {
          // Hide all tabs
          for (let i = 1; i <= 3; i++) {
              const tab = document.getElementById('tab-' + i);
              if (tab) tab.style.display = 'none';

              const button = document.getElementById('nextToCard' + i);
              if (button) button.classList.remove('active');
          }

          // Show active tab
          const activeTab = document.getElementById('tab-' + cardNumber);
          if (activeTab) activeTab.style.display = 'block';

          const activeButton = document.getElementById('nextToCard' + cardNumber);
          if (activeButton) activeButton.classList.add('active');
      }
  </script>

  <!-- Template JS Files -->
  <script src="<?= base_url('assets/stisla/assets/js/scripts.js') ?>"></script>
  <script src="<?= base_url('assets/stisla/assets/js/custom.js') ?>"></script>