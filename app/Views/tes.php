<?= $this->extend("layouts/admin/layout-admin"); ?>
<?= $this->section("content"); ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>Tabel <?= $siteTitle ?></h4>
                <div class="card-header-action">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#addSubelemenModal">
                        Tambah Subelemen
                    </button>

                    <div class="dropdown d-inline">
                        <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Import Exel
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item has-icon" href="<?= base_url('Contoh Exel/CONTOH SUBELEMEN LSP SCADA.xlsx') ?>"><i class="fas fa-file"></i> Contoh Exel</a>
                            <a class="dropdown-item has-icon" type="button" data-toggle="modal" data-target="#importExelModal"><i class="fas fa-upload"></i> Import Exel</a>

                        </div>
                    </div>
                    <button class="btn btn-primary" data-toggle="modal" data-target="#addSkemaModal">
                        Export Exel
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div>
                    <a class="button" id="startButton">Start</a>
                    <a class="button" id="resetButton">Reset</a>
                </div>

                <div>
                    <video id="video" width="300" height="200" style="border: 1px solid gray"></video>
                </div>

                <div id="sourceSelectPanel" style="display:none">
                    <label for="sourceSelect">Change video source:</label>
                    <select id="sourceSelect" style="max-width:400px">
                    </select>
                </div>

                <div style="display: table">
                    <label for="decoding-style"> Decoding Style:</label>
                    <select id="decoding-style" size="1">
                        <option value="once">Decode once</option>
                        <option value="continuously">Decode continuously</option>
                    </select>
                </div>

                <label>Result:</label>
                <pre><code id="result"></code></pre>
                <a id="resultLink" class="btn btn-primary" href="#">Lihat</a>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script type="text/javascript" src="<?= base_url('stisla/node_modules/@zxing/library/umd/index.min.js') ?>"></script>
<script type="text/javascript">
    const resultLink = document.getElementById('resultLink');

    function decodeOnce(codeReader, selectedDeviceId) {
        codeReader.decodeFromInputVideoDevice(selectedDeviceId, 'video').then((result) => {
            console.log(result)
            document.getElementById('result').textContent = result.text
            resultLink.href = result.text;
        }).catch((err) => {
            console.error(err)
            document.getElementById('result').textContent = err
        })
    }

    function decodeContinuously(codeReader, selectedDeviceId) {
        codeReader.decodeFromInputVideoDeviceContinuously(selectedDeviceId, 'video', (result, err) => {
            if (result) {
                // properly decoded qr code
                console.log('Found QR code!', result)
                document.getElementById('result').textContent = result.text
            }

            if (err) {
                // As long as this error belongs into one of the following categories
                // the code reader is going to continue as excepted. Any other error
                // will stop the decoding loop.
                //
                // Excepted Exceptions:
                //
                //  - NotFoundException
                //  - ChecksumException
                //  - FormatException

                if (err instanceof ZXing.NotFoundException) {
                    console.log('No QR code found.')
                }

                if (err instanceof ZXing.ChecksumException) {
                    console.log('A code was found, but it\'s read value was not valid.')
                }

                if (err instanceof ZXing.FormatException) {
                    console.log('A code was found, but it was in a invalid format.')
                }
            }
        })
    }

    window.addEventListener('load', function() {
        let selectedDeviceId;
        const codeReader = new ZXing.BrowserQRCodeReader()
        console.log('ZXing code reader initialized')

        codeReader.getVideoInputDevices()
            .then((videoInputDevices) => {
                const sourceSelect = document.getElementById('sourceSelect')
                selectedDeviceId = videoInputDevices[0].deviceId
                if (videoInputDevices.length >= 1) {
                    videoInputDevices.forEach((element) => {
                        const sourceOption = document.createElement('option')
                        sourceOption.text = element.label
                        sourceOption.value = element.deviceId
                        sourceSelect.appendChild(sourceOption)
                    })

                    sourceSelect.onchange = () => {
                        selectedDeviceId = sourceSelect.value;
                    };

                    const sourceSelectPanel = document.getElementById('sourceSelectPanel')
                    sourceSelectPanel.style.display = 'block'
                }

                document.getElementById('startButton').addEventListener('click', () => {

                    const decodingStyle = document.getElementById('decoding-style').value;

                    if (decodingStyle == "once") {
                        decodeOnce(codeReader, selectedDeviceId);
                    } else {
                        decodeContinuously(codeReader, selectedDeviceId);
                    }

                    console.log(`Started decode from camera with id ${selectedDeviceId}`)
                })

                document.getElementById('resetButton').addEventListener('click', () => {
                    codeReader.reset()
                    document.getElementById('result').textContent = '';
                    console.log('Reset.')
                })

            })
            .catch((err) => {
                console.error(err)
            })
    })
</script>
<?= $this->endSection() ?>