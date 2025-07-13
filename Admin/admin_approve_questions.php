<?php
session_start();

// ✅ Optional: Check if admin
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
//     header("Location: ../User/Login.php");
//     exit;
// }

require_once '../DBConnection/DBConnector.php';

// ✅ Fetch pending questions with image (BLOB), image_mime, user info
$stmt = $pdo->query("
  SELECT q.id, q.title, q.description, q.tags, q.created_at, q.image,
         u.username, u.profile_image
  FROM questions q
  JOIN users u ON q.user_id = u.id
  WHERE q.is_approved = 0
  ORDER BY q.created_at DESC
");
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Pending Questions - Admin Approval</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen p-6">

    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-800 mb-8">Pending Questions for Approval</h1>

        <?php if (empty($questions)): ?>
            <p class="text-center text-gray-500">No pending questions found.</p>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($questions as $q): ?>
                    <div class="bg-white p-6 rounded-lg shadow">
                        <!-- User info -->
                        <div class="flex items-center space-x-4 mb-4">
                            <?php if (!empty($q['profile_image'])): ?>
                                <img src="<?= htmlspecialchars($q['profile_image']) ?>" class="w-10 h-10 rounded-full object-cover" />
                            <?php else: ?>
                                <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center text-gray-700 font-bold uppercase">
                                    <?= strtoupper(substr($q['username'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                            <div>
                                <p class="text-sm font-semibold text-gray-800"><?= htmlspecialchars($q['username']) ?></p>
                                <p class="text-xs text-gray-500"><?= date("F j, Y H:i", strtotime($q['created_at'])) ?></p>
                            </div>
                        </div>

                        <!-- Title & description -->
                        <h2 class="text-xl font-semibold text-orange-700 mb-2"><?= htmlspecialchars($q['title']) ?></h2>
                        <p class="text-gray-700 whitespace-pre-wrap"><?= htmlspecialchars($q['description']) ?></p>

                        <!-- Image preview from BLOB -->
                        <?php if (!empty($q['image'])): ?>
                            <?php
                            $mime = 'image/jpeg'; // force default
                            $base64 = base64_encode($q['image']);
                            $src = "data:$mime;base64,$base64";
                            ?>
                            <div class="mt-4">
                                <img src="<?= $src ?>" alt="Question Image"
                                    class="max-h-96 object-contain mx-auto border rounded-lg shadow" />
                            </div>
                        <?php endif; ?>

                        <!-- Tags -->
                        <div class="mt-4">
                            <?php foreach (explode(',', $q['tags'] ?? '') as $tag): ?>
                                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-1 rounded-full mr-2">
                                    <?= htmlspecialchars(trim($tag)) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>

                        <!-- Approve/Reject buttons -->
                        <div class="flex gap-4 mt-6">
                            <form action="admin_approve_action.php" method="POST" class="flex-1">
                                <input type="hidden" name="question_id" value="<?= $q['id'] ?>">
                                <button type="submit" name="approve" value="1"
                                    class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 rounded">
                                    ✅ Approve
                                </button>
                            </form>
                            <form action="admin_approve_action.php" method="POST" class="flex-1">
                                <input type="hidden" name="question_id" value="<?= $q['id'] ?>">
                                <button type="submit" name="reject" value="1"
                                    class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 rounded">
                                    ❌ Reject
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</body>

</html>