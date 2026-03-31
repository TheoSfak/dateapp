<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Core\Session;
use App\Core\CSRF;
use App\Models\User;
use App\Models\Profile;
use App\Models\Report;
use App\Models\Block;
use App\Models\Interaction;

class AdminController extends Controller
{
    private function requireAdmin(): array
    {
        $user = $this->requireAuth();
        $dbUser = User::findById($user['id']);
        if (!$dbUser || $dbUser['role'] !== 'admin') {
            http_response_code(403);
            echo "Access denied.";
            exit;
        }
        return $user;
    }

    public function dashboard(): void
    {
        $this->requireAdmin();

        $db = \App\Core\Database::getInstance();
        $stats = [
            'total_users'   => $db->query("SELECT COUNT(*) as c FROM users")->fetch()['c'],
            'active_today'  => $db->query("SELECT COUNT(*) as c FROM users WHERE DATE(last_login_at)=CURDATE()")->fetch()['c'],
            'premium_users' => $db->query("SELECT COUNT(*) as c FROM users WHERE is_premium=1")->fetch()['c'],
            'total_matches' => $db->query("SELECT COUNT(*) as c FROM matches")->fetch()['c'],
            'total_messages'=> $db->query("SELECT COUNT(*) as c FROM messages")->fetch()['c'],
            'pending_reports'=> $db->query("SELECT COUNT(*) as c FROM reports WHERE status='pending'")->fetch()['c'],
            'new_today'     => $db->query("SELECT COUNT(*) as c FROM users WHERE DATE(created_at)=CURDATE()")->fetch()['c'],
        ];

        View::render('admin/dashboard', ['stats' => $stats]);
    }

    public function users(): void
    {
        $this->requireAdmin();
        $db = \App\Core\Database::getInstance();

        $search = trim($_GET['q'] ?? $_GET['search'] ?? '');
        $status = $_GET['status'] ?? '';

        $sql = "SELECT u.*, p.name, p.city,
                       (SELECT COUNT(*) FROM matches m WHERE m.user_1_id=u.id OR m.user_2_id=u.id) as match_count
                FROM users u
                JOIN profiles p ON p.user_id = u.id
                WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (u.email LIKE ? OR p.name LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        if ($status !== '' && in_array($status, ['active','suspended','banned'])) {
            $sql .= " AND u.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY u.created_at DESC LIMIT 100";
        $stmt = $db->query($sql, $params);
        $users = $stmt->fetchAll();

        View::render('admin/users', ['users' => $users, 'search' => $search, 'statusFilter' => $status]);
    }

    public function updateUserStatus(): void
    {
        $this->requireAdmin();
        CSRF::validate();

        $userId = (int)($_POST['user_id'] ?? 0);
        $status = $_POST['status'] ?? '';

        if ($userId <= 0 || !in_array($status, ['active','suspended','banned'])) {
            Session::flash('error', 'Invalid user or status.');
        } else {
            \App\Core\Database::getInstance()->query(
                "UPDATE users SET status = ? WHERE id = ?",
                [$status, $userId]
            );
            Session::flash('success', "User #{$userId} status updated to {$status}.");
        }
        $this->redirect('/admin/users');
    }

    public function reports(): void
    {
        $this->requireAdmin();
        $reports = Report::getPending();
        View::render('admin/reports', ['reports' => $reports]);
    }

    public function handleReport(): void
    {
        $this->requireAdmin();
        CSRF::validate();

        $reportId = (int)($_POST['report_id'] ?? 0);
        $action   = $_POST['action'] ?? '';

        if ($reportId > 0) {
            $report = Report::findById($reportId);
            if ($report) {
                if ($action === 'ban') {
                    \App\Core\Database::getInstance()->query(
                        "UPDATE users SET status = 'banned' WHERE id = ?",
                        [$report['reported_id']]
                    );
                    Report::updateStatus($reportId, 'reviewed');
                    Session::flash('success', 'User banned and report resolved.');
                } elseif ($action === 'warn') {
                    Report::updateStatus($reportId, 'reviewed');
                    Session::flash('success', 'Warning noted. Report marked as reviewed.');
                } elseif ($action === 'dismiss') {
                    Report::updateStatus($reportId, 'dismissed');
                    Session::flash('success', 'Report dismissed.');
                }
            }
        }
        $this->redirect('/admin/reports');
    }
}
