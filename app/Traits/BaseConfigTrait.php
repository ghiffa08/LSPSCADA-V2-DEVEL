<?php

namespace App\Traits;

use App\Models\FeedbackAsesiModel;
use App\Models\KomponenFeedbackModel;
use App\Models\KomponenUmpanBalikModel;
use Config\Services;

// Model imports (grouped by domain)
use App\Models\AKModel;
use App\Models\KUKModel;
use App\Models\TUKModel;
use App\Models\APL1Model;

use App\Models\APL2Model;
use App\Models\UnitModel;
use App\Models\UserModel;
use App\Models\AsesiModel;
use App\Models\SkemaModel;
use App\Models\ElemenModel;

use App\Models\GroupsModel;
use App\Models\AsesmenModel;
use App\Models\FeedbackModel;
use App\Services\DataService;

use App\Models\DashboardModel;
use App\Models\GroupUserModel;
use App\Models\ObservasiModel;
use App\Models\SettanggalModel;

use App\Models\SkemaSiswaModel;
use App\Models\APL2JawabanModel;
use App\Models\DokumenApl1Model;
use App\Models\DynamicDependent;
use App\Models\PersyaratanModel;
use App\Models\KelompokUnitModel;
use App\Models\KelompokKerjaModel;
use App\Models\PengajuanAsesmenModel;
use Myth\Auth\Models\UserModel as MythUserModel;


trait BaseConfigTrait
{
    /**
     * Instance of the main Request object.
     */
    protected $request;

    // Authentication & Authorization
    protected $groupsModel;
    protected $groupUserModel;
    protected $userModel;
    protected $authUserModel;

    // Scheme & Competency
    protected $skemaModel;
    protected $skemaSiswaModel;
    protected $unitModel;
    protected $elemenModel;
    protected $kukModel;

    protected $feedbackAsesiModel;

    protected $KomponenfeedbackModel;
    protected $tukModel;

    // Application Forms
    protected $apl1Model;
    protected $apl2Model;
    protected $apl2JawabanModel;
    protected $dokumenApl1Model;

    // Assessment
    protected $asesmenModel;
    protected $pengajuanAsesmenModel;
    protected $asesiModel;
    protected $akModel;

    // Supporting Components
    protected $dashboardModel;
    protected $settanggalModel;
    protected $persyaratanModel;
    protected $feedbackModel;
    protected $kelompokKerjaModel;
    protected $kelompokUnitModel;
    protected $observasiModel;
    protected $dynamicDependentModel;

    protected $session;
    protected $validation;

    /**
     * Data Service instance
     *
     * @var DataService
     */
    protected $dataService;


    protected function initBaseConfig()
    {
        $this->session = session();
        $this->validation = Services::validation();
        // Authentication & Authorization
        $this->groupsModel = new GroupsModel();
        $this->groupUserModel = new GroupUserModel();
        $this->userModel = new UserModel();
        $this->authUserModel = new MythUserModel();

        // Scheme & Competency
        $this->skemaModel = new SkemaModel();
        $this->skemaSiswaModel = new SkemaSiswaModel();
        $this->unitModel = new UnitModel();
        $this->elemenModel = new ElemenModel();
        $this->kukModel = new KUKModel();
        $this->tukModel = new TUKModel();
        $this->KomponenfeedbackModel = new KomponenFeedbackModel();
        $this->feedbackAsesiModel = new FeedbackAsesiModel();

        // Application Forms
        $this->apl1Model = new APL1Model();
        $this->apl2Model = new APL2Model();
        $this->apl2JawabanModel = new APL2JawabanModel();
        $this->dokumenApl1Model = new DokumenApl1Model();

        // Assessment
        $this->asesmenModel = new AsesmenModel();
        $this->pengajuanAsesmenModel = new PengajuanAsesmenModel();
        $this->asesiModel = new AsesiModel();
        $this->akModel = new AKModel();

        // Supporting Components
        $this->dashboardModel = new DashboardModel();
        $this->settanggalModel = new SettanggalModel();
        $this->persyaratanModel = new PersyaratanModel();
        $this->feedbackModel = new FeedbackModel();
        $this->kelompokKerjaModel = new KelompokKerjaModel();
        $this->kelompokUnitModel = new KelompokUnitModel();
        $this->observasiModel = new ObservasiModel();
        $this->dynamicDependentModel = new DynamicDependent();


        $this->dataService = new DataService();
    }
}
