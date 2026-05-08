<?php

// Route definitions
// Format: 'METHOD /path' => ['ControllerName', 'actionMethod', 'auth' => bool, 'roles' => ['admin', 'employee']]
// 'auth' => true means any authenticated user can access
// 'auth' => false or absent means public access
// 'auth' => true, 'roles' => ['admin'] means only admin can access

return [
    'GET /' => ['HomeController', 'index', 'auth' => true],
    'GET /auth/login' => ['AuthController', 'loginForm', 'auth' => false],
    'POST /auth/login' => ['AuthController', 'login', 'auth' => false],
    'GET /auth/logout' => ['AuthController', 'logout', 'auth' => true],
    'GET /auth/change-password' => ['PasswordController', 'changeForm', 'auth' => true],
    'POST /auth/change-password/post' => ['PasswordController', 'change', 'auth' => true],

    'GET /departments' => ['DepartmentController', 'index', 'auth' => true],
    'GET /departments/create' => ['DepartmentController', 'createForm', 'auth' => true, 'roles' => ['admin']],
    'POST /departments' => ['DepartmentController', 'create', 'auth' => true, 'roles' => ['admin']],
    'GET /departments/{id}/edit' => ['DepartmentController', 'editForm', 'auth' => true, 'roles' => ['admin']],
    'PUT /departments/{id}' => ['DepartmentController', 'update', 'auth' => true, 'roles' => ['admin']],
    'DELETE /departments/{id}' => ['DepartmentController', 'delete', 'auth' => true, 'roles' => ['admin']],
    'GET /api/departments/active' => ['DepartmentController', 'activeList', 'auth' => true],
    'GET /api/employees/available-managers' => ['DepartmentController', 'availableManagers', 'auth' => true, 'roles' => ['admin']],

    'GET /employees' => ['EmployeeController', 'index', 'auth' => true, 'roles' => ['admin']],
    'GET /employees/create' => ['EmployeeController', 'createForm', 'auth' => true, 'roles' => ['admin']],
    'POST /employees' => ['EmployeeController', 'create', 'auth' => true, 'roles' => ['admin']],
    'GET /employees/{id}/edit' => ['EmployeeController', 'editForm', 'auth' => true, 'roles' => ['admin']],
    'PUT /employees/{id}' => ['EmployeeController', 'update', 'auth' => true, 'roles' => ['admin']],
    'DELETE /employees/{id}' => ['EmployeeController', 'delete', 'auth' => true, 'roles' => ['admin']],
    'PUT /employees/{id}/password' => ['EmployeeController', 'changePassword', 'auth' => true, 'roles' => ['admin']],
    'GET /api/departments/{id}/default-salary' => ['DepartmentController', 'defaultSalary', 'auth' => true, 'roles' => ['admin']],

    'POST /documents/upload' => ['DocumentController', 'upload', 'auth' => true, 'roles' => ['admin']],
    'DELETE /documents/{id}' => ['DocumentController', 'delete', 'auth' => true, 'roles' => ['admin']],
    'GET /documents/{id}/{filename}' => ['DocumentController', 'serve', 'auth' => true],
    'GET /api/employees/{id}/documents' => ['DocumentController', 'list', 'auth' => true],

    'GET /tasks' => ['TaskController', 'index', 'auth' => true],
    'GET /tasks/create' => ['TaskController', 'createForm', 'auth' => true, 'roles' => ['admin']],
    'POST /tasks' => ['TaskController', 'create', 'auth' => true, 'roles' => ['admin']],
    'GET /tasks/{id}/edit' => ['TaskController', 'editForm', 'auth' => true, 'roles' => ['admin']],
    'PUT /tasks/{id}' => ['TaskController', 'update', 'auth' => true, 'roles' => ['admin']],
    'PUT /tasks/{id}/status' => ['TaskController', 'updateStatus', 'auth' => true],
    'DELETE /tasks/{id}' => ['TaskController', 'delete', 'auth' => true, 'roles' => ['admin']],
    'GET /api/tasks/active-employees' => ['TaskController', 'activeEmployees', 'auth' => true, 'roles' => ['admin']],
    'GET /tasks/{id}' => ['TaskController', 'show', 'auth' => true],
    'POST /tasks/{id}/notes' => ['TaskController', 'addNote', 'auth' => true],
    'DELETE /tasks/notes/{noteId}' => ['TaskController', 'deleteNote', 'auth' => true],
    'POST /tasks/{id}/media' => ['TaskController', 'uploadMedia', 'auth' => true],
    'DELETE /tasks/media/{mediaId}' => ['TaskController', 'deleteMedia', 'auth' => true],
    'GET /tasks/media/{mediaId}/{filename}' => ['TaskController', 'serveMedia', 'auth' => true],

    'GET /tickets' => ['TicketController', 'index', 'auth' => true],
    'GET /tickets/create' => ['TicketController', 'createForm', 'auth' => true],
    'POST /tickets' => ['TicketController', 'create', 'auth' => true],
    'GET /tickets/{id}' => ['TicketController', 'show', 'auth' => true],
    'POST /tickets/{id}/messages' => ['TicketController', 'addMessage', 'auth' => true],
    'PUT /tickets/{id}/status' => ['TicketController', 'updateStatus', 'auth' => true, 'roles' => ['admin']],
    'DELETE /tickets/{id}' => ['TicketController', 'delete', 'auth' => true, 'roles' => ['admin']],
    'GET /tickets/attachment/{id}/{filename}' => ['TicketController', 'serveAttachment', 'auth' => true],
    'GET /api/tickets/unread-count' => ['TicketController', 'unreadCount', 'auth' => true],
    'POST /tickets/{id}/mark-read' => ['TicketController', 'markRead', 'auth' => true],

    'GET /salaries' => ['SalaryController', 'index', 'auth' => true],
    'GET /salaries/my' => ['SalaryController', 'myHistory', 'auth' => true],
    'POST /salaries/generate' => ['SalaryController', 'generate', 'auth' => true, 'roles' => ['admin']],
    'POST /salaries/pay/{id}' => ['SalaryController', 'pay', 'auth' => true, 'roles' => ['admin']],
    'POST /salaries/recalculate' => ['SalaryController', 'recalculate', 'auth' => true, 'roles' => ['admin']],
    'GET /salaries/config' => ['SalaryController', 'configList', 'auth' => true, 'roles' => ['admin']],
    'GET /salaries/config/{id}' => ['SalaryController', 'configShow', 'auth' => true, 'roles' => ['admin']],
    'POST /salaries/config/{id}' => ['SalaryController', 'configUpdate', 'auth' => true, 'roles' => ['admin']],

    'GET /files' => ['FileController', 'index', 'auth' => true],
    'GET /files/create' => ['FileController', 'createForm', 'auth' => true],
    'POST /files' => ['FileController', 'create', 'auth' => true],
    'GET /files/{id}/edit' => ['FileController', 'editForm', 'auth' => true],
    'PUT /files/{id}' => ['FileController', 'update', 'auth' => true],
    'DELETE /files/{id}' => ['FileController', 'delete', 'auth' => true],
    'GET /files/{id}/download' => ['FileController', 'download', 'auth' => true],
    'GET /api/files/storage' => ['FileController', 'storageInfo', 'auth' => true],
    'PUT /api/files/user/{userId}/quota' => ['FileController', 'updateQuota', 'auth' => true, 'roles' => ['admin']],

    'GET /logs' => ['LogController', 'index', 'auth' => true, 'roles' => ['admin']],
    'GET /api/logs' => ['LogController', 'list', 'auth' => true, 'roles' => ['admin']],
    'GET /logs/export' => ['LogController', 'export', 'auth' => true, 'roles' => ['admin']],
    'GET /logs/export/today' => ['LogController', 'exportToday', 'auth' => true, 'roles' => ['admin']],
    'GET /logs/{id}' => ['LogController', 'detail', 'auth' => true, 'roles' => ['admin']],
    'GET /api/logs/filters' => ['LogController', 'filterOptions', 'auth' => true, 'roles' => ['admin']],

    'GET /api/dashboard/stats' => ['HomeController', 'stats', 'auth' => true],
    'GET /api/dashboard/tasks-chart' => ['HomeController', 'tasksChart', 'auth' => true],
    'GET /api/dashboard/tickets-chart' => ['HomeController', 'ticketsChart', 'auth' => true],
];