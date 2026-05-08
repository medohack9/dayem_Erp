<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\SalaryModel;
use App\Models\SalaryConfigModel;
use App\Models\EmployeeModel;
use App\Models\DepartmentModel;
use App\Models\LogModel;

class SalaryController extends Controller
{
    protected SalaryModel $salaryModel;
    protected SalaryConfigModel $configModel;
    protected EmployeeModel $employeeModel;
    protected DepartmentModel $departmentModel;
    protected LogModel $logModel;

    public function __construct()
    {
        $this->salaryModel = new SalaryModel();
        $this->configModel = new SalaryConfigModel();
        $this->employeeModel = new EmployeeModel();
        $this->departmentModel = new DepartmentModel();
        $this->logModel = new LogModel();
    }

    public function index(): void
    {
        $config = require ROOT_PATH . '/config/app.php';
        $perPage = (int)($config['per_page'] ?? 15);

        if (Auth::isAdmin()) {
            $this->adminIndex($perPage);
        } else {
            $this->employeeIndex($perPage);
        }
    }

    private function adminIndex(int $perPage): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $month = isset($_GET['month']) && $_GET['month'] !== '' ? (int)$_GET['month'] : null;
        $year = isset($_GET['year']) && $_GET['year'] !== '' ? (int)$_GET['year'] : null;
        $departmentId = isset($_GET['department_id']) && $_GET['department_id'] !== '' ? (int)$_GET['department_id'] : null;
        $status = $_GET['status'] ?? 'all';
        $search = trim($_GET['search'] ?? '');

        if ($month === null && $year === null) {
            $year = (int)date('Y');
            $month = (int)date('n');
        }

        $result = $this->salaryModel->findAllPaginated($page, $perPage, $month, $year, $departmentId, $status !== 'all' ? $status : null, $search);
        $summary = $this->salaryModel->getSummaryStats($month, $year, $departmentId);
        $departments = $this->departmentModel->getAllActive();

        $months = $this->getMonthsAr();

        $this->render('salaries/index', [
            'pageTitle' => 'المرتبات',
            'activePage' => 'salaries',
            'salaries' => $result['salaries'],
            'total' => $result['total'],
            'page' => $result['page'],
            'totalPages' => $result['totalPages'],
            'perPage' => $perPage,
            'adminView' => true,
            'summary' => $summary,
            'departments' => $departments,
            'months' => $months,
            'selectedMonth' => $month,
            'selectedYear' => $year,
            'selectedDepartment' => $departmentId,
            'selectedStatus' => $status,
            'search' => $search,
            'pageScripts' => ['/js/salaries.js'],
        ]);
    }

    private function getMonthsAr(): array
    {
        return [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
            5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر',
        ];
    }

    private function employeeIndex(int $perPage): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));

        $result = $this->salaryModel->findUserHistory(Auth::id(), $page, $perPage);

        $this->render('salaries/index', [
            'pageTitle' => 'سجل المرتبات',
            'activePage' => 'salaries',
            'salaries' => $result['salaries'],
            'total' => $result['total'],
            'page' => $result['page'],
            'totalPages' => $result['totalPages'],
            'perPage' => $perPage,
            'adminView' => false,
            'summary' => null,
            'departments' => [],
            'months' => $this->getMonthsAr(),
            'selectedMonth' => null,
            'selectedYear' => null,
            'selectedDepartment' => null,
            'selectedStatus' => 'all',
            'search' => '',
            'pageScripts' => [],
        ]);
    }

    public function generate(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'غير مصرح بهذا الإجراء']);
            return;
        }

        $csrfToken = $_SESSION['csrf_token'] ?? '';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (strpos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
        } else {
            $input = $_POST;
        }

        $providedToken = $input['_csrf_token'] ?? '';
        if (empty($providedToken) || empty($csrfToken) || !hash_equals($csrfToken, $providedToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'طلب غير مصرح به']);
            return;
        }

        $month = (int)($input['month'] ?? 0);
        $year = (int)($input['year'] ?? 0);

        if ($month < 1 || $month > 12 || $year < 2000 || $year > 2100) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'الشهر أو السنة غير صحيحة']);
            return;
        }

        if ($this->salaryModel->hasRecordsForMonth($month, $year)) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'يوجد سجلات مرتبات لهذا الشهر بالفعل']);
            return;
        }

        $result = $this->salaryModel->generateMonthlySalaries($month, $year);

        $this->logModel->logAction('salary', 0, 'generate', [
            'month' => $month,
            'year' => $year,
            'generated' => $result['generated'],
            'skipped' => $result['skipped'],
        ], null, $_SERVER['REMOTE_ADDR'] ?? null);

        echo json_encode([
            'success' => true,
            'message' => "تم إنشاء {$result['generated']} سجل مرتبات" . ($result['skipped'] > 0 ? " وتخطي {$result['skipped']}" : ''),
            'data' => $result,
        ]);
    }

    public function pay(int $id): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'غير مصرح بهذا الإجراء']);
            return;
        }

        $csrfToken = $_SESSION['csrf_token'] ?? '';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (strpos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
        } else {
            $input = $_POST;
        }

        $providedToken = $input['_csrf_token'] ?? '';
        if (empty($providedToken) || empty($csrfToken) || !hash_equals($csrfToken, $providedToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'طلب غير مصرح به']);
            return;
        }

        $salary = $this->salaryModel->findById($id);
        if (!$salary) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'سجل المرتب غير موجود']);
            return;
        }

        if ($salary['status'] === 'paid') {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'هذا السجل مدفوع بالفعل']);
            return;
        }

        $success = $this->salaryModel->markAsPaid($id, Auth::id());

        if ($success) {
            $this->logModel->logAction('salary', $id, 'pay', [
                'employee' => $salary['employee_name'],
                'month' => $salary['month'],
                'year' => $salary['year'],
                'amount' => $salary['net_amount'],
            ], null, $_SERVER['REMOTE_ADDR'] ?? null);

            $updated = $this->salaryModel->findById($id);
            echo json_encode([
                'success' => true,
                'message' => 'تم تأكيد الدفع بنجاح',
                'paid_at' => $updated['paid_at'],
                'paid_by_name' => Auth::user()['name'] ?? 'مدير',
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'فشل في تأكيد الدفع']);
        }
    }

    public function recalculate(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'غير مصرح بهذا الإجراء']);
            return;
        }

        $csrfToken = $_SESSION['csrf_token'] ?? '';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (strpos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
        } else {
            $input = $_POST;
        }

        $providedToken = $input['_csrf_token'] ?? '';
        if (empty($providedToken) || empty($csrfToken) || !hash_equals($csrfToken, $providedToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'طلب غير مصرح به']);
            return;
        }

        $month = (int)($input['month'] ?? 0);
        $year = (int)($input['year'] ?? 0);

        if ($month < 1 || $month > 12 || $year < 2000 || $year > 2100) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'الشهر أو السنة غير صحيحة']);
            return;
        }

        $updatedCount = $this->salaryModel->recalculateUnpaid($month, $year);

        $this->logModel->logAction('salary', 0, 'recalculate', [
            'month' => $month,
            'year' => $year,
            'updated_count' => $updatedCount,
        ], null, $_SERVER['REMOTE_ADDR'] ?? null);

        echo json_encode([
            'success' => true,
            'message' => "تم تحديث {$updatedCount} سجل",
            'updated_count' => $updatedCount,
        ]);
    }

    public function myHistory(): void
    {
        $this->requireAuth();

        $config = require ROOT_PATH . '/config/app.php';
        $perPage = (int)($config['per_page'] ?? 15);
        $page = max(1, (int)($_GET['page'] ?? 1));

        $result = $this->salaryModel->findUserHistory(Auth::id(), $page, $perPage);

        $this->render('salaries/index', [
            'pageTitle' => 'سجل المرتبات',
            'activePage' => 'salaries',
            'salaries' => $result['salaries'],
            'total' => $result['total'],
            'page' => $result['page'],
            'totalPages' => $result['totalPages'],
            'perPage' => $perPage,
            'adminView' => false,
            'summary' => null,
            'departments' => [],
            'months' => $this->getMonthsAr(),
            'selectedMonth' => null,
            'selectedYear' => null,
            'selectedDepartment' => null,
            'selectedStatus' => 'all',
            'search' => '',
            'pageScripts' => [],
        ]);
    }

    public function configList(): void
    {
        if (!Auth::isAdmin()) {
            $this->redirect('/salaries');
            return;
        }

        $employees = $this->employeeModel->findAllActive('', 1, 1000, null, 'active');
        $configs = $this->configModel->getAllWithUsers();

        $configMap = [];
        foreach ($configs as $cfg) {
            $configMap[$cfg['user_id']] = $cfg;
        }

        $effectiveSalaries = [];
        foreach ($employees['employees'] as $emp) {
            $effectiveSalaries[$emp['id']] = $this->configModel->getEffectiveSalary($emp['id'], $emp['department_id'] ?? null);
        }

        $this->render('salaries/config', [
            'pageTitle' => 'إعدادات المرتبات',
            'activePage' => 'salaries',
            'employees' => $employees['employees'],
            'configMap' => $configMap,
            'effectiveSalaries' => $effectiveSalaries,
            'departments' => $this->departmentModel->getAllActive(),
            'pageScripts' => ['/js/salaries.js'],
        ]);
    }

    public function configShow(int $userId): void
    {
        if (!Auth::isAdmin()) {
            $this->redirect('/salaries');
            return;
        }

        $employee = $this->employeeModel->findById($userId);
        if (!$employee) {
            http_response_code(404);
            $this->render('errors/404', ['pageTitle' => 'الموظف غير موجود', 'activePage' => '']);
            return;
        }

        $config = $this->configModel->findByUserId($userId);
        $department = null;
        if ($employee['department_id']) {
            $department = $this->departmentModel->findById($employee['department_id']);
        }

        $effectiveSalary = $this->configModel->getEffectiveSalary($userId, $employee['department_id'] ?? null);

        $this->render('salaries/config', [
            'pageTitle' => 'إعدادات مرتب: ' . $employee['name'],
            'activePage' => 'salaries',
            'employee' => $employee,
            'config' => $config,
            'department' => $department,
            'effectiveSalary' => $effectiveSalary,
            'departments' => $this->departmentModel->getAllActive(),
            'employees' => [],
            'configMap' => [],
            'pageScripts' => ['/js/salaries.js'],
        ]);
    }

    public function configUpdate(int $userId): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'غير مصرح بهذا الإجراء']);
            return;
        }

        $csrfToken = $_SESSION['csrf_token'] ?? '';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (strpos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
        } else {
            $input = $_POST;
        }

        $providedToken = $input['_csrf_token'] ?? '';
        if (empty($providedToken) || empty($csrfToken) || !hash_equals($csrfToken, $providedToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'طلب غير مصرح به']);
            return;
        }

        $employee = $this->employeeModel->findById($userId);
        if (!$employee) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'الموظف غير موجود']);
            return;
        }

        $basicSalary = (float)($input['basic_salary'] ?? 0);
        $allowances = (float)($input['allowances'] ?? 0);
        $deductions = (float)($input['deductions'] ?? 0);
        $notes = trim($input['notes'] ?? '');

        if ($basicSalary < 0) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'الراتب الأساسي يجب أن يكون رقماً موجباً']);
            return;
        }

        $this->configModel->upsert($userId, $basicSalary, $allowances, $deductions, $notes ?: null);

        $this->logModel->logAction('salary_config', $userId, 'update', [
            'basic_salary' => $basicSalary,
            'allowances' => $allowances,
            'deductions' => $deductions,
        ], null, $_SERVER['REMOTE_ADDR'] ?? null);

        echo json_encode([
            'success' => true,
            'message' => 'تم تحديث إعدادات المرتب بنجاح',
        ]);
    }

    }