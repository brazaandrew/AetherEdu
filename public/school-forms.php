<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/Helpers/env.php';
require_once __DIR__ . '/../src/Helpers/database.php';
require_once __DIR__ . '/../src/Helpers/session.php';
require_once __DIR__ . '/../src/Helpers/auth.php';

loadEnv(__DIR__ . '/../.env');
startSecureSession();
$user = requireRole(['admin','registrar']);

// Search filters
$search = trim($_GET['search'] ?? '');
$gradeFilter = trim($_GET['grade'] ?? '');

$where = "u.role = 'student' AND u.archived = 0";
$params = [];
if ($search !== '') {
    $where .= " AND (u.name LIKE ? OR u.lrn_number LIKE ? OR u.empidno LIKE ?)";
    $like = "%{$search}%";
    $params = array_merge($params, [$like, $like, $like]);
}
if ($gradeFilter !== '') {
    $where .= " AND (SELECT s.grade_level FROM subjects s JOIN enrollments e2 ON e2.subject_id=s.id WHERE e2.student_id=u.id AND s.grade_level IS NOT NULL LIMIT 1) = ?";
    $params[] = $gradeFilter;
}

$stmt = db()->prepare("SELECT u.id, u.name, u.empidno, u.lrn_number, u.gender, u.date_of_birth, u.image
    FROM users u WHERE {$where} ORDER BY u.name ASC LIMIT 80");
$stmt->execute($params);
$students = $stmt->fetchAll();

$pageTitle = 'School Forms';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>School Forms – eLMS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<link rel="stylesheet" href="assets/css/style.css?v=2028">
<style>
.student-card{border-radius:12px;transition:.2s;cursor:pointer;border:2px solid transparent;}
.student-card:hover{border-color:var(--primary,#2563eb);box-shadow:0 6px 20px rgba(37,99,235,.12);transform:translateY(-2px);}
.student-avatar{width:56px;height:56px;border-radius:50%;object-fit:cover;background:#e2e8f0;}
.form-btn{min-width:90px;}
</style>
</head>
<body>
<?php include __DIR__.'/includes/sidebar.php'; ?>
<div class="main-content">
<?php $pageTitle='School Forms'; include __DIR__.'/includes/topbar.php'; ?>
<div class="container-fluid p-4">
    <div class="page-header mb-4">
        <h2><i class="bi bi-file-earmark-ruled me-2"></i>School Forms Generator</h2>
        <p class="text-muted mb-0">Generate official DepEd SF9 (Report Card) and SF10 (Permanent Record) for any learner.</p>
    </div>

    <!-- Search bar -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Search Learner</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Name, LRN, or Student ID…" value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Grade Level</label>
                    <select name="grade" class="form-select">
                        <option value="">All Grades</option>
                        <?php foreach(['Grade 7','Grade 8','Grade 9','Grade 10','Grade 11','Grade 12'] as $g): ?>
                        <option value="<?= $g ?>" <?= $gradeFilter===$g?'selected':'' ?>><?= $g ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Search</button>
                    <a href="school-forms.php" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Results -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-people me-2"></i>Learners <span class="badge bg-primary ms-1"><?= count($students) ?></span></h5>
        </div>
        <div class="card-body p-0">
            <?php if(empty($students)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-person-x" style="font-size:3rem;"></i>
                <p class="mt-2">No learners found. Try a different search.</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Learner</th>
                            <th>LRN</th>
                            <th>Student ID</th>
                            <th>Gender</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach($students as $s): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <?php if($s['image']): ?>
                                    <img src="<?= htmlspecialchars($s['image']) ?>" class="student-avatar" alt="">
                                <?php else: ?>
                                    <div class="student-avatar d-flex align-items-center justify-content-center bg-primary text-white fw-bold"><?= strtoupper(substr($s['name'],0,1)) ?></div>
                                <?php endif; ?>
                                <div>
                                    <div class="fw-semibold"><?= htmlspecialchars($s['name']) ?></div>
                                    <?php if($s['date_of_birth']): ?><small class="text-muted"><?= date('M d, Y', strtotime($s['date_of_birth'])) ?></small><?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($s['lrn_number'] ?: '—') ?></td>
                        <td><?= htmlspecialchars($s['empidno'] ?: '—') ?></td>
                        <td><?= htmlspecialchars($s['gender'] ?: '—') ?></td>
                        <td class="text-center">
                            <div class="d-flex gap-2 justify-content-center flex-wrap">
                                <a href="sf9.php?id=<?= $s['id'] ?>" class="btn btn-primary btn-sm form-btn">
                                    <i class="bi bi-file-earmark-person me-1"></i>SF9
                                </a>
                                <a href="sf10.php?id=<?= $s['id'] ?>" class="btn btn-outline-primary btn-sm form-btn">
                                    <i class="bi bi-journal-richtext me-1"></i>SF10
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
