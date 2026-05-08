<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\TaskModel;
use App\Models\EmployeeModel;
use App\Models\DepartmentModel;
use App\Models\LogModel;

class TaskController extends Controller
{
    protected TaskModel $taskModel;
    protected EmployeeModel $employeeModel;
    protected DepartmentModel $departmentModel;
    protected LogModel $logModel;

    public function __construct()
    {
        $this->taskModel = new TaskModel();
        $this->employeeModel = new EmployeeModel();
        $this->departmentModel = new DepartmentModel();
        $this->logModel = new LogModel();
    }

    public function index(): void
    {
        $config = require ROOT_PATH . '/config/app.php';
        $perPage = (int)($config['per_page'] ?? 15);
        $page = (int)($_GET['page'] ?? 1);
        $search = trim($_GET['search'] ?? '');
        $statusFilter = $_GET['status'] ?? 'all';
        $priorityFilter = $_GET['priority'] ?? '';
        $departmentId = isset($_GET['department_id']) && $_GET['department_id'] !== '' ? (int)$_GET['department_id'] : null;
        $assignedTo = isset($_GET['assigned_to']) && $_GET['assigned_to'] !== '' ? (int)$_GET['assigned_to'] : null;

        if ($page < 1) {
            $page = 1;
        }

        $adminView = Auth::isAdmin();

        if (!$adminView) {
            $assignedTo = Auth::id();
            $statusFilter = $_GET['status'] ?? 'all';
        }

        $result = $this->taskModel->findAllActive(
            $search, $page, $perPage,
            $statusFilter !== 'all' ? $statusFilter : null,
            $priorityFilter !== '' ? $priorityFilter : null,
            $departmentId,
            $assignedTo,
            $adminView
        );

        $departments = $this->departmentModel->getAllActive();
        $employees = $adminView ? $this->employeeModel->findAllActive('', 1, 1000, null, 'active')['employees'] : [];

        $this->render('tasks/index', [
            'pageTitle' => 'المهام',
            'activePage' => 'tasks',
            'tasks' => $result['tasks'],
            'total' => $result['total'],
            'page' => $result['page'],
            'totalPages' => $result['totalPages'],
            'search' => $search,
            'statusFilter' => $statusFilter,
            'priorityFilter' => $priorityFilter,
            'departmentId' => $departmentId,
            'assignedTo' => $assignedTo,
            'departments' => $departments,
            'employees' => $employees,
            'adminView' => $adminView,
            'perPage' => $perPage,
            'pageScripts' => ['/js/tasks.js'],
        ]);
    }

    public function createForm(): void
    {
        $departments = $this->departmentModel->getAllActive();
        $employees = $this->employeeModel->findAllActive('', 1, 1000, null, 'active')['employees'];

        $this->render('tasks/create', [
            'pageTitle' => 'إضافة مهمة جديدة',
            'activePage' => 'tasks',
            'departments' => $departments,
            'employees' => $employees,
            'pageScripts' => ['/js/tasks.js'],
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
            $title = trim($input['title'] ?? '');
            $description = trim($input['description'] ?? '');
            $priority = $input['priority'] ?? 'متوسط';
            $dueDate = $input['due_date'] ?? null;
            $assignedTo = $input['assigned_to'] ?? null;
            $departmentId = $input['department_id'] ?? null;
            $providedToken = $input['_csrf_token'] ?? '';
        } else {
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $priority = $_POST['priority'] ?? 'متوسط';
            $dueDate = $_POST['due_date'] ?? null;
            $assignedTo = $_POST['assigned_to'] ?? null;
            $departmentId = $_POST['department_id'] ?? null;
            $providedToken = $_POST['_csrf_token'] ?? '';
        }

        if (empty($providedToken) || empty($csrfToken) || !hash_equals($csrfToken, $providedToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'طلب غير مصرح به']);
            return;
        }

        $errors = [];

        if (empty($title)) {
            $errors['title'] = 'عنوان المهمة مطلوب';
        } elseif (mb_strlen($title) > 200) {
            $errors['title'] = 'عنوان المهمة يجب أن لا يتجاوز 200 حرف';
        }

        if (!empty($description) && mb_strlen($description) > 2000) {
            $errors['description'] = 'وصف المهمة يجب أن لا يتجاوز 2000 حرف';
        }

        $validPriorities = ['عاجل', 'متوسط', 'منخفض'];
        if (!in_array($priority, $validPriorities)) {
            $errors['priority'] = 'الأولية غير صحيحة';
        }

        if (empty($assignedTo)) {
            $errors['assigned_to'] = 'يجب اختيار موظف';
        } else {
            $employee = $this->employeeModel->findById((int)$assignedTo);
            if (!$employee) {
                $errors['assigned_to'] = 'الموظف غير موجود';
            } elseif ($employee['status'] !== 'active') {
                $errors['assigned_to'] = 'لا يمكن تعيين مهمة لموظف غير نشط';
            }
        }

        if ($departmentId !== null && $departmentId !== '' && $departmentId !== 0) {
            if (!$this->departmentModel->findById((int)$departmentId)) {
                $errors['department_id'] = 'القسم غير موجود';
            }
        } else {
            $departmentId = null;
        }

        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'errors' => $errors]);
            return;
        }

        $taskCode = $this->taskModel->generateTaskCode();

        $data = [
            'task_code' => $taskCode,
            'title' => $title,
            'description' => $description ?: null,
            'priority' => $priority,
            'status' => 'جديد',
            'assigned_to' => (int)$assignedTo,
            'department_id' => $departmentId ? (int)$departmentId : null,
            'due_date' => $dueDate ?: null,
            'created_by' => Auth::id(),
        ];

        $id = $this->taskModel->create($data);

        $this->logModel->logAction('task', (int)$id, 'create', null, null, $_SERVER['REMOTE_ADDR'] ?? null);

        $task = $this->taskModel->findById((int)$id);

        echo json_encode([
            'success' => true,
            'message' => 'تم إنشاء المهمة بنجاح',
            'task' => $task,
        ]);
    }

    public function editForm(int $id): void
    {
        $task = $this->taskModel->findById($id);

        if (!$task) {
            http_response_code(404);
            $this->render('errors/404', ['pageTitle' => 'المهمة غير موجودة', 'activePage' => '']);
            return;
        }

        $departments = $this->departmentModel->getAllActive();
        $employees = $this->employeeModel->findAllActive('', 1, 1000, null, 'active')['employees'];

        $this->render('tasks/edit', [
            'pageTitle' => 'تعديل المهمة',
            'activePage' => 'tasks',
            'task' => $task,
            'departments' => $departments,
            'employees' => $employees,
            'pageScripts' => ['/js/tasks.js'],
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
            $title = trim($input['title'] ?? '');
            $description = trim($input['description'] ?? '');
            $priority = $input['priority'] ?? null;
            $status = $input['status'] ?? null;
            $dueDate = $input['due_date'] ?? null;
            $assignedTo = $input['assigned_to'] ?? null;
            $departmentId = $input['department_id'] ?? null;
            $providedToken = $input['_csrf_token'] ?? '';
        } else {
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $priority = $_POST['priority'] ?? null;
            $status = $_POST['status'] ?? null;
            $dueDate = $_POST['due_date'] ?? null;
            $assignedTo = $_POST['assigned_to'] ?? null;
            $departmentId = $_POST['department_id'] ?? null;
            $providedToken = $_POST['_csrf_token'] ?? '';
        }

        if (empty($providedToken) || empty($csrfToken) || !hash_equals($csrfToken, $providedToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'طلب غير مصرح به']);
            return;
        }

        $task = $this->taskModel->findById($id);
        if (!$task) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'المهمة غير موجودة']);
            return;
        }

        $errors = [];

        if (empty($title)) {
            $errors['title'] = 'عنوان المهمة مطلوب';
        } elseif (mb_strlen($title) > 200) {
            $errors['title'] = 'عنوان المهمة يجب أن لا يتجاوز 200 حرف';
        }

        if (!empty($description) && mb_strlen($description) > 2000) {
            $errors['description'] = 'وصف المهمة يجب أن لا يتجاوز 2000 حرف';
        }

        $validPriorities = ['عاجل', 'متوسط', 'منخفض'];
        if ($priority !== null && !in_array($priority, $validPriorities)) {
            $errors['priority'] = 'الأولية غير صحيحة';
        }

        $validStatuses = ['جديد', 'قيد التنفيذ', 'مكتمل'];
        if ($status !== null && !in_array($status, $validStatuses)) {
            $errors['status'] = 'الحالة غير صحيحة';
        }

        if (empty($assignedTo)) {
            $errors['assigned_to'] = 'يجب اختيار موظف';
        } else {
            $employee = $this->employeeModel->findById((int)$assignedTo);
            if (!$employee) {
                $errors['assigned_to'] = 'الموظف غير موجود';
            }
        }

        if ($departmentId !== null && $departmentId !== '' && $departmentId !== 0) {
            if (!$this->departmentModel->findById((int)$departmentId)) {
                $errors['department_id'] = 'القسم غير موجود';
            }
        } else {
            $departmentId = null;
        }

        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'errors' => $errors]);
            return;
        }

        $data = [
            'title' => $title,
            'description' => $description ?: null,
            'assigned_to' => (int)$assignedTo,
            'department_id' => $departmentId ? (int)$departmentId : null,
            'due_date' => $dueDate ?: null,
        ];

        if ($priority !== null && in_array($priority, $validPriorities)) {
            $data['priority'] = $priority;
        }

        if ($status !== null && in_array($status, $validStatuses)) {
            $data['status'] = $status;
            if ($status === 'مكتمل' && $task['status'] !== 'مكتمل') {
                $data['completed_at'] = date('Y-m-d H:i:s');
            } elseif ($status !== 'مكتمل' && $task['status'] === 'مكتمل') {
                $data['completed_at'] = null;
            }
        }

        $this->taskModel->updateTask($id, $data);

        $this->logModel->logAction('task', $id, 'update', ['changes' => $data], null, $_SERVER['REMOTE_ADDR'] ?? null);

        $updated = $this->taskModel->findById($id);

        echo json_encode([
            'success' => true,
            'message' => 'تم تحديث المهمة بنجاح',
            'task' => $updated,
        ]);
    }

    public function updateStatus(int $id): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $csrfToken = $_SESSION['csrf_token'] ?? '';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (strpos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
        } else {
            $input = $_POST;
        }

        $providedToken = $input['_csrf_token'] ?? '';
        $status = $input['status'] ?? '';

        if (empty($providedToken) || empty($csrfToken) || !hash_equals($csrfToken, $providedToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'طلب غير مصرح به']);
            return;
        }

        $validStatuses = ['جديد', 'قيد التنفيذ', 'مكتمل'];
        if (!in_array($status, $validStatuses)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'حالة غير صحيحة']);
            return;
        }

        $task = $this->taskModel->findById($id);
        if (!$task) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'المهمة غير موجودة']);
            return;
        }

        if (!Auth::isAdmin()) {
            if ((int)$task['assigned_to'] !== Auth::id()) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'غير مصرح بتعديل هذه المهمة']);
                return;
            }

            $currentIdx = array_search($task['status'], $validStatuses);
            $newIdx = array_search($status, $validStatuses);
            if ($newIdx < $currentIdx) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'لا يمكن الرجوع في حالة المهمة']);
                return;
            }
        }

        $data = ['status' => $status];

        if ($status === 'مكتمل' && $task['status'] !== 'مكتمل') {
            $data['completed_at'] = date('Y-m-d H:i:s');
        } elseif ($status !== 'مكتمل' && $task['status'] === 'مكتمل') {
            $data['completed_at'] = null;
        }

        $this->taskModel->updateTask($id, $data);

        $this->logModel->logAction('task', $id, 'status_change', ['from' => $task['status'], 'to' => $status], null, $_SERVER['REMOTE_ADDR'] ?? null);

        echo json_encode([
            'success' => true,
            'message' => 'تم تحديث حالة المهمة بنجاح',
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

        $task = $this->taskModel->findById($id);
        if (!$task) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'المهمة غير موجودة']);
            return;
        }

        $this->taskModel->softDelete($id);

        $this->logModel->logAction('task', $id, 'delete', ['title' => $task['title']], null, $_SERVER['REMOTE_ADDR'] ?? null);

        echo json_encode([
            'success' => true,
            'message' => 'تم حذف المهمة بنجاح',
        ]);
    }

    public function activeEmployees(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $employees = $this->employeeModel->findAllActive('', 1, 1000, null, 'active')['employees'];
        $result = array_map(function ($emp) {
            return [
                'id' => $emp['id'],
                'name' => $emp['name'],
                'employee_code' => $emp['employee_code'] ?? '',
            ];
        }, $employees);
        echo json_encode(['success' => true, 'employees' => $result]);
    }
}