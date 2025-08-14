// Form handler untuk asesor dengan single select skema
$('#form-add-asesor').on('submit', function(e) {
    e.preventDefault();
    
    // Gunakan FormData untuk membuat data yang akan dikirim
    var formData = new FormData(this);
    
    // Ambil nilai skema dari Select2 (single value, bukan array)
    var skemaId = $('#asesor-skema-sertifikasi').val();
    
    // Log untuk debugging client-side
    console.log('Selected skema ID:', skemaId);
    
    // Validasi skema
    if (!skemaId) {
        alert('Pilih skema sertifikasi');
        return;
    }
    
    // Set skema_id ke formData (single value)
    formData.set('skema_id', skemaId);
    
    // Debug output - periksa apa yang akan dikirim
    console.log('Form data to be sent:');
    for (var pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }
    
    $.ajax({
        url: baseUrl + '/api/usermanagement/createAsesorUser',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            if (response.status) {
                Swal.fire({
                    title: 'Sukses!',
                    text: response.message,
                    icon: 'success'
                }).then(function() {
                    $('#modal-add-asesor').modal('hide');
                    $('#form-add-asesor')[0].reset();
                    reloadTable();
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: response.message,
                    icon: 'error'
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error details:', xhr.responseText);
            Swal.fire({
                title: 'Error!',
                text: 'Gagal menambahkan asesor. Silakan coba lagi.',
                icon: 'error'
            });
        }
    });
});