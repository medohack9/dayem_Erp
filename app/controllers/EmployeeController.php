<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\EmployeeModel;
use App\Models\DepartmentModel;
use App\Models\LogModel;
use App\Models\DocumentModel;

class EmployeeController extends Controller
{
    protected EmployeeModel $employeeModel;
    protected DepartmentModel $departmentModel;
    protected LogModel $logModel;
    protected DocumentModel $documentModel;

    public function __construct()
    {
        $this->employeeModel = new EmployeeModel();
        $this->departmentModel = new DepartmentModel();
        $this->logModel = new LogModel();
        $this->documentModel = new DocumentModel();
    }

    public function index(): void
    {
        $config = require ROOT_PATH . '/config/app.php';
        $perPage = (int)($config['per_page'] ?? 15);
        $page = (int)($_GET['page'] ?? 1);
        $search = trim($_GET['search'] ?? '');
        $departmentId = isset($_GET['department_id']) && $_GET['department_id'] !== '' ? (int)$_GET['department_id'] : null;
        $statusFilter = $_GET['status'] ?? 'active';

        if ($page < 1) {
            $page = 1;
        }

        $result = $this->employeeModel->findAllActive($search, $page, $perPage, $departmentId, $statusFilter);
        $departments = $this->departmentModel->getAllActive();

        $this->render('employees/index', [
            'pageTitle' => 'الموظفين',
            'activePage' => 'employees',
            'employees' => $result['employees'],
            'total' => $result['total'],
            'page' => $result['page'],
            'totalPages' => $result['totalPages'],
            'search' => $search,
            'departmentId' => $departmentId,
            'statusFilter' => $statusFilter,
            'departments' => $departments,
            'perPage' => $perPage,
            'pageScripts' => ['/js/employees.js'],
        ]);
    }

    public function createForm(): void
    {
        $departments = $this->departmentModel->getAllActive();

        $this->render('employees/create', [
            'pageTitle' => 'إضافة موظف جديد',
            'activePage' => 'employees',
            'departments' => $departments,
            'pageScripts' => ['/js/employees.js'],
        ]);
    }

    public function create(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $csrfToken = $_SESSION['csrf_token'] ?? '';
        $providedToken = '';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (strpos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
            $name = trim($input['name'] ?? '');
            $email = trim($input['email'] ?? '');
            $phone = trim($input['phone'] ?? '');
            $nationalId = trim($input['national_id'] ?? '');
            $birthDate = $input['birth_date'] ?? null;
            $salary = $input['salary'] ?? null;
            $departmentId = $input['department_id'] ?? null;
            $hireDate = $input['hire_date'] ?? null;
            $password = $input['password'] ?? '';
            $passwordConfirm = $input['password_confirm'] ?? '';
            $providedToken = $input['_csrf_token'] ?? '';
        } else {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $nationalId = trim($_POST['national_id'] ?? '');
            $birthDate = $_POST['birth_date'] ?? null;
            $salary = $_POST['salary'] ?? null;
            $departmentId = $_POST['department_id'] ?? null;
            $hireDate = $_POST['hire_date'] ?? null;
            $password = $_POST['password'] ?? '';
            $passwordConfirm = $_POST['password_confirm'] ?? '';
            $providedToken = $_POST['_csrf_token'] ?? '';
        }

        if (empty($providedToken) || empty($csrfToken) || !hash_equals($csrfToken, $providedToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'طلب غير مصرح به']);
            return;
        }

        $errors = [];

        if (empty($name)) {
            $errors['name'] = 'اسم الموظف مطلوب';
        } elseif (mb_strlen($name) > 100) {
            $errors['name'] = 'اسم الموظف يجب أن لا يتجاوز 100 حرف';
        }

        if (empty($email)) {
            $errors['email'] = 'البريد الإلكتروني مطلوب';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'صيغة البريد الإلكتروني غير صحيحة';
        } elseif ($this->employeeModel->findByEmail($email)) {
            $errors['email'] = 'البريد الإلكتروني مستخدم بالفعل';
        }

        if (empty($phone)) {
            $errors['phone'] = 'رقم الموبايل مطلوب';
        } elseif (!preg_match('/^01[0-9]{9}$/', $phone)) {
            $errors['phone'] = 'صيغة رقم الموبايل غير صحيحة';
        } elseif ($this->employeeModel->findByPhone($phone)) {
            $errors['phone'] = 'رقم الموبايل مستخدم بالفعل';
        }

        if (empty($nationalId)) {
            $errors['national_id'] = 'الرقم القومي مطلوب';
        } elseif (!preg_match('/^[0-9]{14}$/', $nationalId)) {
            $errors['national_id'] = 'الرقم القومي يجب أن يكون 14 رقم';
        } elseif ($this->employeeModel->findByNationalId($nationalId)) {
            $errors['national_id'] = 'الرقم القومي مستخدم بالفعل';
        }

        if (empty($birthDate)) {
            $errors['birth_date'] = 'تاريخ الميلاد مطلوب';
        }

        if (empty($salary) && $salary !== '0' && $salary !== 0) {
            $errors['salary'] = 'المرتب مطلوب';
        } elseif (!is_numeric($salary) || (float)$salary <= 0) {
            $errors['salary'] = 'المرتب يجب أن يكون رقماً موجباً';
        }

        if (empty($departmentId)) {
            $errors['department_id'] = 'القسم مطلوب';
        } elseif (!$this->departmentModel->findById((int)$departmentId)) {
            $errors['department_id'] = 'القسم غير موجود';
        }

        if (empty($password)) {
            $errors['password'] = 'كلمة المرور مطلوبة';
        } elseif (mb_strlen($password) < 8) {
            $errors['password'] = 'كلمة المرور يجب أن تكون 8 أحرف على الأقل';
        }

        if (empty($passwordConfirm)) {
            $errors['password_confirm'] = 'تأكيد كلمة المرور مطلوب';
        } elseif ($password !== $passwordConfirm) {
            $errors['password_confirm'] = 'كلمة المرور وتأكيدها غير متطابقين';
        }

        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'errors' => $errors]);
            return;
        }

        $employeeCode = $this->employeeModel->generateEmployeeCode();
        $tempPassword = $this->employeeModel->generateTempPassword();

        $data = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'role' => 'employee',
            'status' => 'active',
            'must_change_password' => 1,
            'national_id' => $nationalId,
            'birth_date' => $birthDate,
            'salary' => (float)$salary,
            'department_id' => (int)$departmentId,
            'hire_date' => $hireDate ?: null,
            'employee_code' => $employeeCode,
        ];

        $id = $this->employeeModel->create($data);

        $this->logModel->logAction('employee', (int)$id, 'create', null, null, $_SERVER['REMOTE_ADDR'] ?? null);

        $employee = $this->employeeModel->findById((int)$id);

        echo json_encode([
            'success' => true,
            'message' => 'تم إضافة الموظف بنجاح',
            'employee' => $employee,
        ]);
    }

    public function editForm(int $id): void
    {
        $employee = $this->employeeModel->findById($id);

        if (!$employee) {
            http_response_code(404);
            $this->render('errors/404', ['pageTitle' => 'الموظف غير موجود', 'activePage' => '']);
            return;
        }

        $departments = $this->departmentModel->getAllActive();
        $documents = $this->documentModel->findByUserId($id);

        $this->render('employees/edit', [
            'pageTitle' => 'تعديل بيانات الموظف',
            'activePage' => 'employees',
            'employee' => $employee,
            'departments' => $departments,
            'documents' => $documents,
            'pageScripts' => ['/js/employees.js'],
        ]);
    }

    public function update(int $id): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $csrfToken = $_SESSION['csrf_token'] ?? '';
        $providedToken = '';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (strpos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
            $name = trim($input['name'] ?? '');
            $email = trim($input['email'] ?? '');
            $phone = trim($input['phone'] ?? '');
            $nationalId = trim($input['national_id'] ?? '');
            $birthDate = $input['birth_date'] ?? null;
            $salary = $input['salary'] ?? null;
            $departmentId = $input['department_id'] ?? null;
            $hireDate = $input['hire_date'] ?? null;
            $status = $input['status'] ?? null;
            $providedToken = $input['_csrf_token'] ?? '';
        } else {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $nationalId = trim($_POST['national_id'] ?? '');
            $birthDate = $_POST['birth_date'] ?? null;
            $salary = $_POST['salary'] ?? null;
            $departmentId = $_POST['department_id'] ?? null;
            $hireDate = $_POST['hire_date'] ?? null;
            $status = $_POST['status'] ?? null;
            $providedToken = $_POST['_csrf_token'] ?? '';
        }

        if (empty($providedToken) || empty($csrfToken) || !hash_equals($csrfToken, $providedToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'طلب غير مصرح به']);
            return;
        }

        $employee = $this->employeeModel->findById($id);
        if (!$employee) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'الموظف غير موجود']);
            return;
        }

        $errors = [];

        if (empty($name)) {
            $errors['name'] = 'اسم الموظف مطلوب';
        } elseif (mb_strlen($name) > 100) {
            $errors['name'] = 'اسم الموظف يجب أن لا يتجاوز 100 حرف';
        }

        if (empty($email)) {
            $errors['email'] = 'البريد الإلكتروني مطلوب';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'صيغة البريد الإلكتروني غير صحيحة';
        } elseif ($existing = $this->employeeModel->findByEmail($email, $id)) {
            $errors['email'] = 'البريد الإلكتروني مستخدم بالفعل';
        }

        if (empty($phone)) {
            $errors['phone'] = 'رقم الموبايل مطلوب';
        } elseif (!preg_match('/^01[0-9]{9}$/', $phone)) {
            $errors['phone'] = 'صيغة رقم الموبايل غير صحيحة';
        } elseif ($existing = $this->employeeModel->findByPhone($phone, $id)) {
            $errors['phone'] = 'رقم الموبايل مستخدم بالفعل';
        }

        if (empty($nationalId)) {
            $errors['national_id'] = 'الرقم القومي مطلوب';
        } elseif (!preg_match('/^[0-9]{14}$/', $nationalId)) {
            $errors['national_id'] = 'الرقم القومي يجب أن يكون 14 رقم';
        } elseif ($this->employeeModel->findByNationalId($nationalId, $id)) {
            $errors['national_id'] = 'الرقم القومي مستخدم بالفعل';
        }

        if (empty($birthDate)) {
            $errors['birth_date'] = 'تاريخ الميلاد مطلوب';
        }

        if (empty($salary) && $salary !== '0' && $salary !== 0 && $salary !== $employee['salary']) {
            $errors['salary'] = 'المرتب مطلوب';
        } elseif (isset($salary) && !is_numeric($salary)) {
            $errors['salary'] = 'المرتب يجب أن يكون رقماً';
        } elseif (isset($salary) && is_numeric($salary) && (float)$salary <= 0) {
            $errors['salary'] = 'المرتب يجب أن يكون رقماً موجباً';
        }

        if (empty($departmentId)) {
            $errors['department_id'] = 'القسم مطلوب';
        } elseif (!$this->departmentModel->findById((int)$departmentId)) {
            $errors['department_id'] = 'القسم غير موجود';
        }

        if ($status && !in_array($status, ['active', 'suspended', 'terminated'])) {
            $errors['status'] = 'حالة غير صحيحة';
        }

        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'errors' => $errors]);
            return;
        }

        $data = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'national_id' => $nationalId,
            'birth_date' => $birthDate,
            'salary' => $salary !== null ? (float)$salary : $employee['salary'],
            'department_id' => (int)$departmentId,
            'hire_date' => $hireDate ?: null,
        ];

        if ($status && in_array($status, ['active', 'suspended', 'terminated'])) {
            $data['status'] = $status;
        }

        $this->employeeModel->updateEmployee($id, $data);

        $this->logModel->logAction('employee', $id, 'update', ['changes' => $data], null, $_SERVER['REMOTE_ADDR'] ?? null);

        $updated = $this->employeeModel->findById($id);

        echo json_encode([
            'success' => true,
            'message' => 'تم تحديث بيانات الموظف بنجاح',
            'employee' => $updated,
        ]);
    }

    public function delete(int $id): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
        } else {
            $input = $_POST;
        }

        $csrfToken = $_SESSION['csrf_token'] ?? '';
        $providedToken = $input['_csrf_token'] ?? '';

        if (empty($providedToken) || empty($csrfToken) || !hash_equals($csrfToken, $providedToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'طلب غير مصرح به']);
            return;
        }

        if ($id === Auth::id()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'لا يمكنك حذف حسابك الخاص']);
            return;
        }

        $employee = $this->employeeModel->findById($id);
        if (!$employee) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'الموظف غير موجود']);
            return;
        }

        $departmentModel = new DepartmentModel();
        $departmentModel->clearManager($id);

        $this->employeeModel->softDelete($id);

        $this->logModel->logAction('employee', $id, 'delete', ['name' => $employee['name']], null, $_SERVER['REMOTE_ADDR'] ?? null);

        echo json_encode([
            'success' => true,
            'message' => 'تم حذف الموظف بنجاح',
        ]);
    }

    public function changePassword(int $id): void
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

        $newPassword = $input['new_password'] ?? '';
        $confirmPassword = $input['confirm_password'] ?? '';
        $forceChange = isset($input['force_change']) ? (bool)$input['force_change'] : true;

        $errors = [];

        if (empty($newPassword)) {
            $errors['new_password'] = 'كلمة المرور الجديدة مطلوبة';
        } elseif (mb_strlen($newPassword) < 8) {
            $errors['new_password'] = 'كلمة المرور يجب أن تكون 8 أحرف على الأقل';
        }

        if (empty($confirmPassword)) {
            $errors['confirm_password'] = 'تأكيد كلمة المرور مطلوب';
        } elseif ($newPassword !== $confirmPassword) {
            $errors['confirm_password'] = 'كلمة المرور وتأكيدها غير متطابقين';
        }

        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'errors' => $errors]);
            return;
        }

        $employee = $this->employeeModel->findById($id);
        if (!$employee) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'الموظف غير موجود']);
            return;
        }

        $userModel = new \App\Models\UserModel();
        $userModel->updateUser($id, [
            'password' => password_hash($newPassword, PASSWORD_BCRYPT),
            'must_change_password' => $forceChange ? 1 : 0,
        ]);

        $this->logModel->logAction('employee', $id, 'password_change', ['changed_by' => 'admin'], null, $_SERVER['REMOTE_ADDR'] ?? null);

        echo json_encode([
            'success' => true,
            'message' => 'تم تغيير كلمة المرور بنجاح',
        ]);
    }
}