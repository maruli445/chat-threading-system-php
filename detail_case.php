<?php
session_start();
require_once 'config.php';
require_once 'models/Case.php';
require_once 'models/Message.php';

// Get case ID from URL
$caseId = isset($_GET['id']) ? intval($_GET['id']) : 0;

$caseModel = new CaseModel();
$messageModel = new MessageModel();

// Get case details
$case = $caseModel->getCaseById($caseId);
if (!$case) {
    header("Location: index.php");
    exit;
}

// Get current user
$currentUser = getCurrentUser();

// Upload directory
$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim($_POST['catatan']);
    $parentMessageId = isset($_POST['parent_message_id']) && !empty($_POST['parent_message_id']) ? intval($_POST['parent_message_id']) : null;

    $imagePath = '';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['gambar']['tmp_name'];
        $fileName = basename($_FILES['gambar']['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileExt, $allowedExts)) {
            $newFileName = uniqid('img_') . '.' . $fileExt;
            $destPath = $uploadDir . $newFileName;
            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $imagePath = $destPath;
            }
        }
    }

    // Save message
    if (!empty($content) || !empty($imagePath)) {
        $messageModel->createMessage($caseId, $currentUser['id'], $content, $imagePath, $parentMessageId);
    }

    // Redirect to avoid resubmission
    header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $caseId);
    exit;
}

// Get threaded messages
$messages = $messageModel->getThreadedMessages($caseId);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Detail Case - ID <?php echo htmlspecialchars($caseId); ?></title>
<!-- Trix Editor CSS & JS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/trix/1.3.1/trix.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/trix/1.3.1/trix.js"></script>

<style>
body {
    font-family: Arial, sans-serif;
    margin: 20px;
    background: #f9f9f9;
    line-height: 1.6;
}
h1 {
    color: #333;
}
a {
    color: #0066cc;
    text-decoration: none;
}
a:hover {
    text-decoration: underline;
}
h2 {
    margin-top: 30px;
    color: #444;
}
ul {
    list-style: none;
    padding: 0;
}
li {
    background: #fff;
    margin-bottom: 10px;
    padding: 10px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
li strong {
    display: block;
    margin-bottom: 5px;
}
img {
    max-width: 200px;
    height: auto;
    cursor: pointer;
    border-radius: 4px;
    transition: transform 0.2s;
}
img:hover {
    transform: scale(1.05);
}
form {
    margin-top: 30px;
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
button {
    background-color: #0066cc;
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
}
button:hover {
    background-color: #005bb5;
}
#imgModal {
  display: none;
  position: fixed;
  z-index: 9999;
  padding-top: 60px;
  left: 0; top: 0;
  width: 100%; height: 100%;
  overflow: auto;
  background-color: rgba(0,0,0,0.8);
}
#imgModal img {
  margin: auto;
  display: block;
  max-width: 90%;
  max-height: 80%;
}
#imgModal span {
  position: absolute;
  top: 20px;
  right: 35px;
  color: #fff;
  font-size: 40px;
  font-weight: bold;
  cursor: pointer;
}
</style>
</head>
<body>
<h1>Detail Case: <?php echo htmlspecialchars($case['title']); ?></h1>
<a href="index.php">Kembali ke Daftar Case</a>

<div style="margin: 20px 0; padding: 10px; background: #e8f4fd; border-radius: 8px;">
    <strong>User saat ini:</strong> <?php echo htmlspecialchars($currentUser['display_name']); ?>
    <span style="display: inline-block; width: 20px; height: 20px; background: <?php echo $currentUser['avatar_color']; ?>; border-radius: 50%; margin-left: 10px;"></span>
</div>

<h2>Chat Thread</h2>
<div id="chat-container" style="max-height: 500px; overflow-y: auto; border: 1px solid #ddd; padding: 15px; background: #f9f9f9; border-radius: 8px;">
<?php
function displayMessage($message, $level = 0) {
    $indent = $level * 30;
    $borderColor = $message['avatar_color'];
    ?>
    <div class="message-item" style="margin-left: <?php echo $indent; ?>px; margin-bottom: 15px; border-left: 3px solid <?php echo $borderColor; ?>; padding-left: 10px;" data-message-id="<?php echo $message['id']; ?>">
        <div class="message-header" style="display: flex; align-items: center; margin-bottom: 5px;">
            <div class="user-avatar" style="width: 30px; height: 30px; background: <?php echo $message['avatar_color']; ?>; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; margin-right: 10px;">
                <?php echo strtoupper(substr($message['display_name'], 0, 1)); ?>
            </div>
            <div class="message-info">
                <strong><?php echo htmlspecialchars($message['display_name']); ?></strong>
                <span style="color: #666; font-size: 0.9em; margin-left: 10px;">
                    <?php echo date('d/m/Y H:i', strtotime($message['created_at'])); ?>
                </span>
                <?php if ($message['reply_count'] > 0): ?>
                    <span style="color: #0066cc; font-size: 0.8em; margin-left: 10px;">
                        (<?php echo $message['reply_count']; ?> balasan)
                    </span>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="message-content" style="background: white; padding: 10px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <?php if (!empty($message['content'])): ?>
                <div><?php echo $message['content']; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($message['image_path'])): ?>
                <div style="margin-top: 8px;">
                    <img src="<?php echo htmlspecialchars($message['image_path']); ?>" 
                         alt="Gambar" 
                         class="popup-img" 
                         data-full="<?php echo htmlspecialchars($message['image_path']); ?>"
                         style="max-width: 200px; cursor: pointer; border-radius: 4px;" />
                </div>
            <?php endif; ?>
            
            <div class="message-actions" style="margin-top: 8px; text-align: right;">
                <button onclick="replyToMessage(<?php echo $message['id']; ?>, '<?php echo htmlspecialchars($message['display_name']); ?>')" 
                        style="background: #0066cc; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; font-size: 0.8em;">
                    Balas
                </button>
            </div>
        </div>
        
        <?php
        // Display replies
        if (!empty($message['replies'])) {
            foreach ($message['replies'] as $reply) {
                displayMessage($reply, $level + 1);
            }
        }
        ?>
    </div>
    <?php
}

if (empty($messages)) {
    echo "<div style='text-align: center; color: #666; padding: 20px;'>Belum ada pesan. Mulai percakapan di bawah!</div>";
} else {
    foreach ($messages as $message) {
        displayMessage($message);
    }
}
?>
</div>

<!-- Form input -->
<div id="reply-info" style="display: none; background: #fff3cd; padding: 10px; border-radius: 4px; margin-bottom: 10px; border: 1px solid #ffeaa7;">
    <strong>Membalas pesan dari:</strong> <span id="reply-to-user"></span>
    <button type="button" onclick="cancelReply()" style="float: right; background: #dc3545; color: white; border: none; padding: 2px 8px; border-radius: 4px; cursor: pointer;">Batal</button>
</div>

<form method="POST" action="" enctype="multipart/form-data" id="message-form">
    <input id="catatan" type="hidden" name="catatan" />
    <input id="parent_message_id" type="hidden" name="parent_message_id" value="" />
    
    <trix-editor input="catatan" style="min-height:120px; border:1px solid #ccc; padding:8px; border-radius:4px; background:#fff;"></trix-editor>
    <br>
    
    <div style="display: flex; align-items: center; gap: 15px; margin: 10px 0;">
        <div>
            <label for="gambar"><strong>Upload Gambar:</strong></label>
            <input type="file" name="gambar" accept="image/*" id="gambar" />
        </div>
        <button type="submit" style="background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: bold;">
            <span id="submit-text">Kirim Pesan</span>
        </button>
    </div>
</form>

<!-- Modal gambar besar -->
<div id="imgModal">
  <span>&times;</span>
  <img id="modalImg" src="" alt="Gambar Besar" />
</div>

<script>
  // Sinkronisasi isi editor ke hidden input
  document.querySelector("trix-editor").addEventListener("trix-change", function() {
    document.getElementById("catatan").value = document.querySelector("trix-editor").editor.getDocument().toString();
  });

  // Reply functionality
  function replyToMessage(messageId, userName) {
    document.getElementById('parent_message_id').value = messageId;
    document.getElementById('reply-to-user').textContent = userName;
    document.getElementById('reply-info').style.display = 'block';
    document.getElementById('submit-text').textContent = 'Kirim Balasan';
    
    // Focus on editor
    document.querySelector("trix-editor").focus();
    
    // Scroll to form
    document.getElementById('message-form').scrollIntoView({ behavior: 'smooth' });
  }

  function cancelReply() {
    document.getElementById('parent_message_id').value = '';
    document.getElementById('reply-info').style.display = 'none';
    document.getElementById('submit-text').textContent = 'Kirim Pesan';
  }

  // Auto-refresh messages every 10 seconds
  let lastMessageCount = <?php echo count($messages); ?>;
  
  function checkForNewMessages() {
    fetch('ajax_check_messages.php?case_id=<?php echo $caseId; ?>&count=' + lastMessageCount)
      .then(response => response.json())
      .then(data => {
        if (data.hasNew) {
          // Reload page to show new messages
          window.location.reload();
        }
      })
      .catch(error => console.log('Auto-refresh error:', error));
  }

  // Check for new messages every 10 seconds
  setInterval(checkForNewMessages, 10000);

  // Modal gambar besar
  var modal = document.getElementById("imgModal");
  var modalImg = document.getElementById("modalImg");
  var spanClose = document.getElementById("imgModal").getElementsByTagName("span")[0];

  // Re-bind popup images (for dynamically loaded content)
  function bindPopupImages() {
    document.querySelectorAll('.popup-img').forEach(function(img) {
      img.onclick = function() {
        modal.style.display = "block";
        modalImg.src = this.getAttribute('data-full');
      }
    });
  }

  // Initial bind
  bindPopupImages();

  spanClose.onclick = function() {
    modal.style.display = "none";
  };

  window.onclick = function(event) {
    if (event.target == modal) {
      modal.style.display = "none";
    }
  };

  // Auto-scroll to bottom of chat on page load
  document.addEventListener('DOMContentLoaded', function() {
    const chatContainer = document.getElementById('chat-container');
    chatContainer.scrollTop = chatContainer.scrollHeight;
  });

  // Form submission enhancement
  document.getElementById('message-form').addEventListener('submit', function(e) {
    const content = document.getElementById('catatan').value.trim();
    const fileInput = document.getElementById('gambar');
    
    if (!content && !fileInput.files.length) {
      e.preventDefault();
      alert('Silakan masukkan pesan atau pilih gambar untuk dikirim.');
      return false;
    }
  });
</script>
</body>
</html>