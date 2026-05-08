import { createBrowserRouter } from "react-router";
import LoginPage from "./pages/LoginPage";
import DashboardLayout from "./layouts/DashboardLayout";
import MainDashboard from "./pages/MainDashboard";
import EmployeesPage from "./pages/EmployeesPage";
import AddEmployeePage from "./pages/AddEmployeePage";
import DepartmentsPage from "./pages/DepartmentsPage";
import TasksPage from "./pages/TasksPage";
import TicketsPage from "./pages/TicketsPage";
import SalariesPage from "./pages/SalariesPage";
import FilesPage from "./pages/FilesPage";
import LogsPage from "./pages/LogsPage";
import SettingsPage from "./pages/SettingsPage";

export const router = createBrowserRouter([
  {
    path: "/",
    element: <LoginPage />,
  },
  {
    path: "/dashboard",
    element: <DashboardLayout />,
    children: [
      { index: true, element: <MainDashboard /> },
      { path: "employees", element: <EmployeesPage /> },
      { path: "employees/add", element: <AddEmployeePage /> },
      { path: "departments", element: <DepartmentsPage /> },
      { path: "tasks", element: <TasksPage /> },
      { path: "tickets", element: <TicketsPage /> },
      { path: "salaries", element: <SalariesPage /> },
      { path: "files", element: <FilesPage /> },
      { path: "logs", element: <LogsPage /> },
      { path: "settings", element: <SettingsPage /> },
    ],
  },
]);
