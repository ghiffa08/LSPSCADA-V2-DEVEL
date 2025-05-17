 <script src="<?= base_url("/assets/thirdparty/signature-pad/js/signature_pad.umd.min.js"); ?>" defer></script>
 <script>
     document.addEventListener("DOMContentLoaded", function() {
         // DOM Elements
         const canvas = document.getElementById("signature-pad");
         const clearBtn = document.getElementById("clear-signature");
         const uploadBtn = document.getElementById("upload-signature");
         const uploadInput = document.getElementById("signature-upload");
         const signatureData = document.getElementById("signature-data");
         const statusDiv = document.getElementById("signature-status");

         // Get the form element
         const form = document.querySelector('.needs-validation');

         // Initialize signature pad
         let signaturePad;

         /**
          * Resize canvas for retina displays and mobile devices
          */
         function resizeCanvas() {
             // Skip if canvas is not visible yet
             if (canvas.offsetWidth === 0 || canvas.offsetHeight === 0) return;

             const ratio = Math.max(window.devicePixelRatio || 1, 1);
             const context = canvas.getContext("2d");

             // Save signature data before resize (if exists)
             const imageData = signaturePad ? signaturePad.toData() : null;

             canvas.width = canvas.offsetWidth * ratio;
             canvas.height = canvas.offsetHeight * ratio;
             context.scale(ratio, ratio);

             // Restore signature data
             if (signaturePad && imageData) {
                 signaturePad.clear();
                 signaturePad.fromData(imageData);
             }
         }

         /**
          * Show status message
          * @param {string} message - Message to display
          * @param {string} type - Message type (success or error)
          */
         function showStatus(message, type = 'error') {
             if (type === 'error') {
                 Swal.fire({
                     icon: 'error',
                     title: 'Oops!',
                     text: message,
                     timer: 3000,
                     showConfirmButton: false
                 });
             }
         }

         /**
          * Handle file upload for signature
          * @param {Event} e - File input change event
          */
         function handleSignatureUpload(e) {
             const file = e.target.files[0];

             if (!file) return;

             if (!['image/png', 'image/jpeg', 'image/jpg'].includes(file.type)) {
                 showStatus("Hanya file PNG atau JPEG yang diperbolehkan.", "error");
                 return;
             }

             if (file.size > 1024 * 1024) {
                 showStatus("Ukuran file maksimal 1MB.", "error");
                 return;
             }

             const reader = new FileReader();

             reader.onload = function(event) {
                 const img = new Image();
                 img.onload = function() {
                     signaturePad.clear();

                     const ctx = canvas.getContext("2d");

                     const hRatio = canvas.width / img.width;
                     const vRatio = canvas.height / img.height;
                     const ratio = Math.min(hRatio, vRatio);

                     const centerX = (canvas.width - img.width * ratio) / 2;
                     const centerY = (canvas.height - img.height * ratio) / 2;

                     ctx.drawImage(img, 0, 0, img.width, img.height,
                         centerX, centerY, img.width * ratio, img.height * ratio);

                     // Save to hidden input
                     signatureData.value = canvas.toDataURL('image/png');

                     showStatus("Tanda tangan berhasil diunggah!", "success");
                 };
                 img.src = event.target.result;
             };

             reader.readAsDataURL(file);
         }

         // Initialize and setup
         function init() {
             // Resize canvas to fit container
             resizeCanvas();

             // Initialize SignaturePad
             signaturePad = new SignaturePad(canvas, {
                 minWidth: 1,
                 maxWidth: 3,
                 penColor: "black",
                 backgroundColor: "rgba(255, 255, 255, 0)"
             });

             // Save data to input whenever user stops writing
             signaturePad.addEventListener("endStroke", () => {
                 if (!signaturePad.isEmpty()) {
                     const dataURL = signaturePad.toDataURL("image/png");
                     signatureData.value = dataURL;
                 }
             });

             // Event listeners
             window.addEventListener("resize", resizeCanvas);

             clearBtn.addEventListener("click", () => {
                 signaturePad.clear();
                 signatureData.value = ''; // Reset input when cleared
             });

             uploadBtn.addEventListener("click", () => uploadInput.click());
             uploadInput.addEventListener("change", handleSignatureUpload);

             // Handle form submission
             if (form) {
                 form.addEventListener("submit", function(e) {
                     // Validate form first
                     if (!form.checkValidity()) {
                         e.preventDefault();
                         e.stopPropagation();
                         form.classList.add('was-validated');
                         return;
                     }

                     // Check if signature exists
                     if (signaturePad.isEmpty() && !signatureData.value) {
                         e.preventDefault();
                         showStatus("Silakan tanda tangani terlebih dahulu!", "error");
                         return;
                     }

                     // If we got here, form is valid and has signature
                     showStatus("Formulir dikirim...", "success");
                 });
             }
         }

         // Start the application
         init();
     });
 </script>