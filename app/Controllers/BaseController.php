<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

use App\Models\GroupsModel;
use App\Models\GroupUserModel;
use App\Models\UserModel;
use App\Models\SkemaModel;
use App\Models\SkemaSiswaModel;
use App\Models\UnitModel;
use App\Models\ElemenModel;
use App\Models\SubelemenModel;
use App\Models\TUKModel;
use App\Models\DynamicDependent;
use App\Models\APL1Model;
use App\Models\DashboardModel;
use App\Models\SettanggalModel;
use App\Models\PersyaratanModel;
use App\Models\APL2Model;
use Myth\Auth\Models\UserModel as MythUser;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;
    protected $groups;
    protected $group_users;
    protected $usermodel;
    protected $skema;
    protected $skema_siswa;
    protected $unit;
    protected $elemen;
    protected $subelemen;
    protected $tuk;
    protected $dependent;
    protected $apl1;
    protected $dashboard;
    protected $settanggal;
    protected $persyaratan;
    protected $apl2;
    protected $userMyth;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var list<string>
     */
    protected $helpers = ['auth'];

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);
        $this->dashboard = new DashboardModel();
        $this->groups = new GroupsModel();
        $this->group_users = new GroupUserModel();
        $this->usermodel = new UserModel();
        $this->skema = new SkemaModel();
        $this->skema_siswa = new SkemaSiswaModel();
        $this->unit = new UnitModel();
        $this->elemen = new ElemenModel();
        $this->subelemen = new SubelemenModel();
        $this->tuk = new TUKModel();
        $this->dependent = new DynamicDependent();
        $this->apl1 = new APL1Model();
        $this->settanggal = new SettanggalModel();
        $this->persyaratan = new PersyaratanModel();
        $this->apl2 = new APL2Model();
        $this->userMyth = new MythUser();
        // Preload any models, libraries, etc, here.

        // E.g.: $this->session = \Config\Services::session();
    }
}
