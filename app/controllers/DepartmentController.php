<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\DepartmentModel;
use App\Models\UserModel;
use App\Models\LogModel;

class DepartmentController extends Controller
{
    protected DepartmentModel $departmentModel;
    protected UserModel $userModel;
    protected LogModel $logModel;

    public function __construct()
    {
        $this->departmentModel = new DepartmentModel();
        $this->userModel = new UserModel();
        $this->logModel = new LogModel();
    }

    public function index(): void
    {
        $config = require ROOT_PATH . '/config/app.php';
        $perPage = (int)($config['per_page'] ?? 15);
        $page = (int)($_GET['page'] ?? 1);
        $search = trim($_GET['search'] ?? '');

        if ($page < 1) {
            $page = 1;
        }

        $result = $this->departmentModel->findAllActive($search, $page, $perPage);

        $this->render('departments/index', [
            'pageTitle' => 'الأقسام',
            'activePage' => 'departments',
            'departments' => $result['departments'],
            'total' => $result['total'],
            'page' => $result['page'],
            'totalPages' => $result['totalPages'],
            'search' => $search,
            'perPage' => $perPage,
            'pageScripts' => ['/js/departments.js'],
        ]);
    }

    public function createForm(): void
    {
        $managers = $this->departmentModel->getAllActive();
        $availableManagers = $this->userModel->findAvailableManagers();

        $this->render('departments/create', [
            'pageTitle' => 'إضافة قسم',
            'activePage' => 'departments',
            'availableManagers' => $availableManagers,
            'managers' => $managers,
            'pageScripts' => ['/js/departments.js'],
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
            $managerId = $input['manager_id'] ?? null;
            $defaultSalary = $input['default_salary'] ?? null;
            $providedToken = $input['_csrf_token'] ?? '';
        } else {
            $name = trim($_POST['name'] ?? '');
            $managerId = $_POST['manager_id'] ?? null;
            $defaultSalary = $_POST['default_salary'] ?? null;
            $providedToken = $_POST['_csrf_token'] ?? '';
        }

        if (empty($providedToken) || empty($csrfToken) || !hash_equals($csrfToken, $providedToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'طلب غير مصرح به']);
            return;
        }

        $errors = [];
        if (empty($name)) {
            $errors['name'] = 'اسم القسم مطلوب';
        } elseif (mb_strlen($name) > 100) {
            $errors['name'] = 'اسم القسم يجب أن لا يتجاوز 100 حرف';
        }

        if ($name && $this->departmentModel->findByName($name)) {
            $errors['name'] = 'اسم القسم موجود بالفعل';
        }

        if ($managerId !== null && $managerId !== '' && $managerId !== '0') {
            $managerId = (int)$managerId;
            $existingDept = $this->departmentModel->findByName('', 0);
            $managerDeptResult = $this->departmentModel->query(
                'SELECT id FROM departments WHERE manager_id = :mid AND deleted_at IS NULL',
                ['mid' => $managerId]
            )->fetch();
            if ($managerDeptResult) {
                $errors['manager_id'] = 'هذا الموظف already يدير قسماً آخر';
            }
        } else {
            $managerId = null;
        }

        if ($defaultSalary !== null && $defaultSalary !== '') {
            if (!is_numeric($defaultSalary) || (float)$defaultSalary < 0) {
                $errors['default_salary'] = 'الراتب الافتراضي يجب أن يكون رقماً موجباً';
            }
            $defaultSalary = $defaultSalary !== '' ? (float)$defaultSalary : null;
        } else {
            $defaultSalary = null;
        }

        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'errors' => $errors]);
            return;
        }

        $data = [
            'name' => $name,
            'manager_id' => $managerId,
            'default_salary' => $defaultSalary,
        ];

        $id = $this->departmentModel->create($data);

        $this->logModel->logAction('department', (int)$id, 'create', null, null, $_SERVER['REMOTE_ADDR'] ?? null);

        $department = $this->departmentModel->findById((int)$id);

        echo json_encode([
            'success' => true,
            'message' => 'تم إنشاء القسم بنجاح',
            'department' => $department,
        ]);
    }

    public function editForm(int $id): void
    {
        $department = $this->departmentModel->findById($id);

        if (!$department) {
            http_response_code(404);
            $this->render('errors/404', ['pageTitle' => 'القسم غير موجود', 'activePage' => '']);
            return;
        }

        $availableManagers = $this->userModel->findAvailableManagers($id);

        $this->render('departments/edit', [
            'pageTitle' => 'تعديل القسم',
            'activePage' => 'departments',
            'department' => $department,
            'availableManagers' => $availableManagers,
            'pageScripts' => ['/js/departments.js'],
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
            $managerId = $input['manager_id'] ?? null;
            $defaultSalary = $input['default_salary'] ?? null;
            $providedToken = $input['_csrf_token'] ?? '';
        } else {
            $name = trim($_POST['name'] ?? '');
            $managerId = $_POST['manager_id'] ?? null;
            $defaultSalary = $_POST['default_salary'] ?? null;
            $providedToken = $_POST['_csrf_token'] ?? '';
        }

        if (empty($providedToken) || empty($csrfToken) || !hash_equals($csrfToken, $providedToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'طلب غير مصرح به']);
            return;
        }

        $department = $this->departmentModel->findById($id);
        if (!$department) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'القسم غير موجود']);
            return;
        }

        $errors = [];
        if (empty($name)) {
            $errors['name'] = 'اسم القسم مطلوب';
        } elseif (mb_strlen($name) > 100) {
            $errors['name'] = 'اسم القسم يجب أن لا يتجاوز 100 حرف';
        }

        if ($name && $existing = $this->departmentModel->findByName($name, $id)) {
            $errors['name'] = 'اسم القسم موجود بالفعل';
        }

        if ($managerId !== null && $managerId !== '' && $managerId !== '0') {
            $managerId = (int)$managerId;
            $managerDeptResult = $this->departmentModel->query(
                'SELECT id FROM departments WHERE manager_id = :mid AND deleted_at IS NULL AND id != :dept_id',
                ['mid' => $managerId, 'dept_id' => $id]
            )->fetch();
            if ($managerDeptResult) {
                $errors['manager_id'] = 'هذا الموظف يدير قسماً آخر';
            }
        } else {
            $managerId = null;
        }

        if ($defaultSalary !== null && $defaultSalary !== '') {
            if (!is_numeric($defaultSalary) || (float)$defaultSalary < 0) {
                $errors['default_salary'] = 'الراتب الافتراضي يجب أن يكون رقماً موجباً';
            }
            $defaultSalary = $defaultSalary !== '' ? (float)$defaultSalary : null;
        } else {
            $defaultSalary = null;
        }

        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'errors' => $errors]);
            return;
        }

        $data = [
            'name' => $name,
            'manager_id' => $managerId,
            'default_salary' => $defaultSalary,
        ];

        $this->departmentModel->updateDepartment($id, $data);

        $this->logModel->logAction('department', $id, 'update', ['changes' => $data], null, $_SERVER['REMOTE_ADDR'] ?? null);

        $updated = $this->departmentModel->findById($id);

        echo json_encode([
            'success' => true,
            'message' => 'تم تحديث القسم بنجاح',
            'department' => $updated,
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

        $department = $this->departmentModel->findById($id);
        if (!$department) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'القسم غير موجود']);
            return;
        }

        $activeCount = $this->departmentModel->countActive();
        if ($activeCount <= 1) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'لا يمكن حذف القسم الأخير في النظام']);
            return;
        }

        $employeeCount = $this->departmentModel->getEmployeeCount($id);
        $reassignTo = isset($input['reassign_to']) ? (int)$input['reassign_to'] : null;

        if ($employeeCount > 0) {
            if (!$reassignTo) {
                http_response_code(422);
                echo json_encode(['success' => false, 'message' => 'يجب تحديد قسم لإعادة تعيين الموظفين', 'require_reassign' => true, 'employee_count' => $employeeCount]);
                return;
            }

            if ($reassignTo === $id) {
                http_response_code(422);
                echo json_encode(['success' => false, 'message' => 'لا يمكن إعادة تعيين الموظفين إلى نفس القسم المحذوف']);
                return;
            }

            $targetDept = $this->departmentModel->findById($reassignTo);
            if (!$targetDept) {
                http_response_code(422);
                echo json_encode(['success' => false, 'message' => 'القسم المحدد لإعادة التعيين غير موجود']);
                return;
            }

            $this->departmentModel->reassignEmployees($id, $reassignTo);
        }

        $this->departmentModel->softDelete($id);

        $this->logModel->logAction('department', $id, 'delete', ['reassign_to' => $reassignTo, 'employee_count' => $employeeCount], null, $_SERVER['REMOTE_ADDR'] ?? null);

        echo json_encode([
            'success' => true,
            'message' => $employeeCount > 0 ? 'تم حذف القسم وإعادة تعيين الموظفين بنجاح' : 'تم حذف القسم بنجاح',
        ]);
    }

    public function activeList(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $departments = $this->departmentModel->getAllActive();
        echo json_encode(['success' => true, 'departments' => $departments]);
    }

    public function availableManagers(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $managers = $this->userModel->findAvailableManagers();
        echo json_encode(['success' => true, 'managers' => $managers]);
    }

    public function defaultSalary(int $id): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $department = $this->departmentModel->findById($id);

        if (!$department) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'القسم غير موجود']);
            return;
        }

        echo json_encode(['success' => true, 'default_salary' => (float)$department['default_salary']]);
    }
}