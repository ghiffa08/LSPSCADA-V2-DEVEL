/**
 * ObservasiManager - Frontend JavaScript untuk managing observasi checklist
 * Production-ready dengan UX optimization
 */
class ObservasiManager {
    constructor(options = {}) {
        this.apiUrl = options.apiUrl || '/api/observasi';
        this.formContainer = options.formContainer || '#observasi-form';
        this.listContainer = options.listContainer || '#observasi-list';
        this.currentObservation = null;
        this.kukStructure = null;
        this.isSubmitting = false;
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadKukStructure();
    }

    bindEvents() {
        // Auto-save functionality
        $(document).on('change', '.kuk-assessment', this.debounce(this.autoSave.bind(this), 1000));
        
        // Batch operations
        $(document).on('click', '.btn-save-all', this.saveAllObservations.bind(this));
        $(document).on('click', '.btn-toggle-all-kompeten', this.toggleAllKompeten.bind(this));
        
        // Form validation
        $(document).on('submit', this.formContainer, this.handleFormSubmit.bind(this));
        
        // Dynamic loading
        $(document).on('change', '#id_skema', this.loadKukStructure.bind(this));
    }

    /**
     * Load KUK structure for selected schema
     */
    async loadKukStructure() {
        const idSkema = $('#id_skema').val();
        if (!idSkema) return;

        try {
            this.showLoading('Memuat struktur KUK...');
            
            const response = await fetch(`${this.apiUrl}/kuk-structure/${idSkema}`);
            const data = await response.json();
            
            if (response.ok) {
                this.kukStructure = data;
                this.renderKukForm(data);
            } else {
                this.showError('Gagal memuat struktur KUK');
            }
        } catch (error) {
            this.showError('Terjadi kesalahan saat memuat struktur KUK');
            console.error('Load KUK Structure Error:', error);
        } finally {
            this.hideLoading();
        }
    }

    /**
     * Render KUK form with grouped structure for better UX
     */
    renderKukForm(kukData) {
        let html = '<div class="observasi-checklist">';
        
        Object.keys(kukData).forEach(unitKey => {
            const unit = kukData[unitKey];
            html += `
                <div class="unit-section card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <button class="btn btn-link" type="button" data-toggle="collapse" 
                                    data-target="#unit-${unit.unit_info.id_unit}">
                                ${unit.unit_info.kode_unit} - ${unit.unit_info.nama_unit}
                            </button>
                        </h5>
                    </div>
                    <div id="unit-${unit.unit_info.id_unit}" class="collapse show">
                        <div class="card-body">
            `;
            
            Object.keys(unit.elements).forEach(elemenKey => {
                const element = unit.elements[elemenKey];
                html += `
                    <div class="element-section mb-3">
                        <h6 class="text-primary">${element.element_info.kode_elemen} - ${element.element_info.nama_elemen}</h6>
                        <div class="kuk-list">
                `;
                
                element.kuks.forEach(kuk => {
                    html += `
                        <div class="kuk-item row mb-2 p-2 border rounded">
                            <div class="col-md-6">
                                <small class="text-muted">${kuk.kode_kuk}</small>
                                <p class="mb-1">${kuk.nama_kuk}</p>
                            </div>
                            <div class="col-md-3">
                                <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                    <label class="btn btn-outline-success btn-sm">
                                        <input type="radio" name="kuk_${kuk.id_kuk}" value="Y" class="kuk-assessment"> Kompeten
                                    </label>
                                    <label class="btn btn-outline-danger btn-sm">
                                        <input type="radio" name="kuk_${kuk.id_kuk}" value="N" class="kuk-assessment"> Belum
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control form-control-sm" 
                                       name="keterangan_${kuk.id_kuk}" placeholder="Keterangan..."
                                       data-kuk-id="${kuk.id_kuk}">
                                <input type="hidden" name="id_skema_${kuk.id_kuk}" value="${idSkema}">
                                <input type="hidden" name="id_kuk_${kuk.id_kuk}" value="${kuk.id_kuk}">
                            </div>
                        </div>
                    `;
                });
                
                html += '</div></div>';
            });
            
            html += '</div></div></div>';
        });
        
        html += `
            <div class="form-actions mt-4">
                <div class="row">
                    <div class="col-md-6">
                        <button type="button" class="btn btn-secondary btn-toggle-all-kompeten">
                            <i class="fas fa-check-double"></i> Tandai Semua Kompeten
                        </button>
                    </div>
                    <div class="col-md-6 text-right">
                        <button type="button" class="btn btn-primary btn-save-all">
                            <i class="fas fa-save"></i> Simpan Semua
                        </button>
                    </div>
                </div>
            </div>
        </div>`;
        
        $('#kuk-form-container').html(html);
        
        // Initialize tooltips and other UI enhancements
        $('[data-toggle="tooltip"]').tooltip();
    }

    /**
     * Auto-save functionality for better UX
     */
    async autoSave() {
        if (this.isSubmitting) return;
        
        const formData = this.collectFormData();
        if (!formData || !formData.details.length) return;
        
        try {
            this.showSaveIndicator('Menyimpan...');
            
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(formData)
            });
            
            const result = await response.json();
            
            if (response.ok) {
                this.showSaveIndicator('Tersimpan', 'success');
                this.currentObservation = result.data;
            } else {
                this.showSaveIndicator('Gagal menyimpan', 'error');
            }
        } catch (error) {
            this.showSaveIndicator('Error', 'error');
            console.error('Auto-save Error:', error);
        }
    }

    /**
     * Save all observations with batch operation
     */
    async saveAllObservations(e) {
        e.preventDefault();
        if (this.isSubmitting) return;
        
        const formData = this.collectFormData();
        if (!formData) return;
        
        // Validate required fields
        if (!this.validateForm(formData)) {
            return;
        }
        
        this.isSubmitting = true;
        const $btn = $(e.target);
        const originalText = $btn.html();
        
        try {
            $btn.html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...').prop('disabled', true);
            
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(formData)
            });
            
            const result = await response.json();
            
            if (response.ok) {
                this.showSuccess('Observasi berhasil disimpan!');
                this.currentObservation = result.data;
                this.updateSummaryStats(result.data.summary);
            } else {
                this.showError(result.message || 'Gagal menyimpan observasi');
            }
        } catch (error) {
            this.showError('Terjadi kesalahan saat menyimpan');
            console.error('Save Error:', error);
        } finally {
            this.isSubmitting = false;
            $btn.html(originalText).prop('disabled', false);
        }
    }

    /**
     * Toggle all KUK to kompeten for bulk operations
     */
    toggleAllKompeten(e) {
        e.preventDefault();
        const $btn = $(e.target);
        const allKompeten = $('input[value="Y"]:checked').length === $('input[value="Y"]').length;
        
        if (allKompeten) {
            // Uncheck all
            $('input[value="Y"]').prop('checked', false).parent().removeClass('active');
            $btn.html('<i class="fas fa-check-double"></i> Tandai Semua Kompeten');
        } else {
            // Check all as kompeten
            $('input[value="Y"]').prop('checked', true).parent().addClass('active');
            $('input[value="N"]').prop('checked', false).parent().removeClass('active');
            $btn.html('<i class="fas fa-times-circle"></i> Batal Semua');
        }
        
        // Trigger auto-save
        this.autoSave();
    }

    /**
     * Collect form data for submission
     */
    collectFormData() {
        const mainData = {
            id_asesor: $('#id_asesor').val(),
            id_asesi: $('#id_asesi').val(),
            id_pegajuan: $('#id_pegajuan').val(),
            tanggal_observasi: $('#tanggal_observasi').val(),
            details: []
        };
        
        // Validate main data
        if (!mainData.id_asesor || !mainData.id_asesi || !mainData.id_pegajuan || !mainData.tanggal_observasi) {
            this.showError('Data utama observasi belum lengkap');
            return null;
        }
        
        // Collect KUK assessments
        $('.kuk-assessment:checked').each((index, element) => {
            const $input = $(element);
            const kukId = $input.attr('name').replace('kuk_', '');
            const kompeten = $input.val();
            const keterangan = $(`input[name="keterangan_${kukId}"]`).val() || '';
            const idSkema = $(`input[name="id_skema_${kukId}"]`).val();
            
            mainData.details.push({
                id_skema: parseInt(idSkema),
                id_kuk: parseInt(kukId),
                kompeten: kompeten,
                keterangan: keterangan.trim()
            });
        });
        
        return mainData;
    }

    /**
     * Form validation
     */
    validateForm(data) {
        if (!data.details.length) {
            this.showError('Belum ada KUK yang dinilai');
            return false;
        }
        
        return true;
    }

    /**
     * Update summary statistics display
     */
    updateSummaryStats(summary) {
        if (!summary) return;
        
        $('#total-kuk').text(summary.total_kuk);
        $('#kompeten-count').text(summary.kompeten);
        $('#belum-kompeten-count').text(summary.belum_kompeten);
        $('#persentase-kompeten').text(summary.persentase_kompeten + '%');
        
        // Update progress bar
        const $progressBar = $('#progress-kompeten');
        $progressBar.css('width', summary.persentase_kompeten + '%')
                   .attr('aria-valuenow', summary.persentase_kompeten);
    }

    /**
     * Utility functions
     */
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    showLoading(message = 'Memuat...') {
        // Implement loading indicator
        $('.loading-indicator').text(message).show();
    }

    hideLoading() {
        $('.loading-indicator').hide();
    }

    showSaveIndicator(message, type = 'info') {
        const $indicator = $('#save-indicator');
        $indicator.removeClass('alert-info alert-success alert-danger')
                  .addClass(`alert-${type === 'error' ? 'danger' : type}`)
                  .text(message)
                  .fadeIn();
        
        if (type === 'success') {
            setTimeout(() => $indicator.fadeOut(), 2000);
        }
    }

    showSuccess(message) {
        // Use your preferred notification system (e.g., toastr, SweetAlert, etc.)
        alert('Success: ' + message); // Replace with better notification
    }

    showError(message) {
        // Use your preferred notification system
        alert('Error: ' + message); // Replace with better notification
    }
}

// Initialize when document is ready
$(document).ready(function() {
    const observasiManager = new ObservasiManager({
        apiUrl: '/api/observasi',
        formContainer: '#observasi-form',
        listContainer: '#observasi-list'
    });
});

// Example usage for batch operations
const batchObservasiExample = {
    async processBatchObservations(observationsList) {
        try {
            const response = await fetch('/api/observasi/batch', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    observations: observationsList
                })
            });
            
            const result = await response.json();
            
            if (response.ok) {
                console.log('Batch processing result:', result);
                return result;
            } else {
                throw new Error(result.message || 'Batch processing failed');
            }
        } catch (error) {
            console.error('Batch processing error:', error);
            throw error;
        }
    }
};
