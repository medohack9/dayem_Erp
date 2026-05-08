<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\SettingModel;
use App\Models\DashboardModel;

class HomeController extends Controller
{
    public function index(): void
    {
        $isAdmin = Auth::isAdmin();
        $userId = Auth::id();

        $dashboardModel = new DashboardModel();

        if ($isAdmin) {
            $kpiStats = $dashboardModel->getKpiStats();
            $taskBreakdown = $dashboardModel->getTaskStatusBreakdown();
            
            $data = [
                'pageTitle' => 'Dayem ERP - الرئيسية',
                'activePage' => 'home',
                'isAdmin' => true,
                'kpiStats' => $kpiStats,
                'taskBreakdown' => $taskBreakdown,
                'pageScripts' => ['/js/dashboard.js'],
            ];
        } else {
            $employeeStats = $dashboardModel->getEmployeeStats($userId);
            
            $data = [
                'pageTitle' => 'Dayem ERP - الرئيسية',
                'activePage' => 'home',
                'isAdmin' => false,
                'employeeStats' => $employeeStats,
                'pageScripts' => ['/js/dashboard.js'],
            ];
        }

        $this->render('home/index', $data);
    }

    public function stats(): void
    {
        $isAdmin = Auth::isAdmin();
        $userId = Auth::id();

        $dashboardModel = new DashboardModel();

        if ($isAdmin) {
            $kpiStats = $dashboardModel->getKpiStats();
            $taskBreakdown = $dashboardModel->getTaskStatusBreakdown();
            
            $this->json([
                'success' => true,
                'data' => [
                    'employees' => $kpiStats['employees'],
                    'tasks' => $kpiStats['tasks'],
                    'tasksBreakdown' => $taskBreakdown,
                    'tickets' => $kpiStats['tickets'],
                    'salaries' => $kpiStats['salaries'],
                ],
            ]);
        } else {
            $employeeStats = $dashboardModel->getEmployeeStats($userId);
            
            $this->json([
                'success' => true,
                'data' => $employeeStats,
            ]);
        }
    }

    public function tasksChart(): void
    {
        $isAdmin = Auth::isAdmin();
        $userId = Auth::id();

        $dashboardModel = new DashboardModel();
        $chartData = $dashboardModel->getTasksChartData($isAdmin, $userId);

        $this->json([
            'success' => true,
            'data' => $chartData,
        ]);
    }

    public function ticketsChart(): void
    {
        $isAdmin = Auth::isAdmin();
        $userId = Auth::id();

        $dashboardModel = new DashboardModel();
        $chartData = $dashboardModel->getTicketsChartData($isAdmin, $userId);

        $this->json([
            'success' => true,
            'data' => $chartData,
        ]);
    }

    public function notFound(): void
    {
        $this->render('errors/404', [
            'pageTitle' => 'الصفحة غير موجودة',
            'activePage' => '',
        ]);
    }
}