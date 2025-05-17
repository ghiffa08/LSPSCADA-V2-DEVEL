<?php

namespace App\Controllers;

use App\Models\AKModel;
use App\Models\KUKModel;
use App\Models\TUKModel;
use App\Models\APL1Model;
use App\Models\APL2Model;
use App\Models\UnitModel;

// Model imports (grouped by domain)
use App\Models\UserModel;
use App\Models\AsesiModel;
use App\Models\SkemaModel;
use App\Models\ElemenModel;

use App\Models\GroupsModel;
use CodeIgniter\Controller;
use App\Models\AsesmenModel;
use Psr\Log\LoggerInterface;
use App\Models\FeedbackModel;
use App\Models\DashboardModel;

use App\Models\GroupUserModel;
use App\Models\ObservasiModel;
use App\Models\SettanggalModel;
use App\Models\SkemaSiswaModel;

use App\Models\APL2JawabanModel;
use App\Models\DokumenApl1Model;
use App\Models\DynamicDependent;
use App\Models\PersyaratanModel;

use CodeIgniter\HTTP\CLIRequest;
use App\Models\KelompokUnitModel;
use App\Models\KelompokKerjaModel;
use App\Services\ExcelImportService;
use App\Models\PengajuanAsesmenModel;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Myth\Auth\Models\UserModel as MythUserModel;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 */
abstract class BaseController extends Controller
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

    protected  $importService;

    /**
     * Helpers to be loaded automatically
     */
    protected $helpers = ['auth', 'form', 'url'];

    /**
     * Initialize controller
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->initializeModels();
        $this->initializeServices();
    }

    /**
     * Initialize all model instances
     */
    protected function initializeModels(): void
    {
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
    }

    protected function initializeServices(): void
    {

        $this->importService = new ExcelImportService();
    }
}
