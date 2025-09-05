<?php
session_start();
require_once 'config.php';
require_once 'models/Case.php';

$caseModel = new CaseModel();

// Handle case status update and deletion
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['status']) && isset($_GET['id'])) {
        $caseId = intval($_GET['id']);
        $status = ($_GET['status'] == '1') ? 1 : 0;
        $caseModel->updateCaseStatus($caseId, $status);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
    
    if (isset($_GET['hapus'])) {
        $caseId = intval($_GET['hapus']);
        $caseModel->deleteCase($caseId);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Add new case with redirect after POST (PRG)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['todo'])) {
    $title = htmlspecialchars(trim($_POST['todo']));
    $caseModel->createCase($title);
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Get all cases
$cases = $caseModel->getAllCases();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>DAFTAR CASE</title>
<link rel="stylesheet" href="style.css" />
</head>
<body class="body-page">

   <button class="toggle-btn" id="toggle-btn"><i class="fas fa-bars"></i></button>
    <?php include "../sidebar.php" ?>
    <main id="main" class="main">

<h1>Daftar Case</h1>

<!-- Form tambah case langsung di halaman utama -->
<form action="" method="POST" style="margin-bottom: 20px;">
  <input type="text" name="todo" placeholder="Nama Case..." required />
  <button type="submit">Simpan</button>
</form>

<!-- Daftar case -->
<ul>
  <?php
  if (empty($cases)):
    ?>
    <li>Belum ada case. Tambahkan case baru di atas.</li>
  <?php
  else:
    foreach ($cases as $case):
    ?>
    <li>
      <input type="checkbox" 
        onclick="window.location.href='?status=<?php echo ($case['status']==1)? '0': '1'; ?>&id=<?php echo $case['id']; ?>'" 
        <?php if ($case['status']==1) echo 'checked'; ?> />
      <label>
        <!-- Link ke halaman detail -->
        <a href="detail_case.php?id=<?php echo $case['id']; ?>">
          <?php echo htmlspecialchars($case['title']); ?>
          <?php if ($case['message_count'] > 0): ?>
            <span style="color: #666; font-size: 0.9em;">(<?php echo $case['message_count']; ?> pesan)</span>
          <?php endif; ?>
        </a>
      </label>
      <!-- Link hapus -->
      <a href="?hapus=<?php echo $case['id']; ?>" onclick="return confirm('Yakin mau dihapus?')">Hapus</a>
    </li>
  <?php 
    endforeach;
  endif;
  ?>
</ul>

</body>
</html>