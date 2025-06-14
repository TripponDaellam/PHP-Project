<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);

require_once 'DBConnection/DBConnector.php';

if (!$pdo) {
    die("❌ Database connection failed. Check DBConnector.php.");
}

$search = $_GET['search'] ?? '';
$searchParam = '%' . $search . '%';

if (!empty($search)) {
    $query = "
        SELECT tag_name, description, question_count 
        FROM tags 
        WHERE tag_name LIKE :search 
        ORDER BY question_count DESC 
        LIMIT 8";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':search', $searchParam, PDO::PARAM_STR);
} else {
    $query = "
        SELECT tag_name, description, question_count 
        FROM tags 
        ORDER BY question_count DESC 
        LIMIT 8";
    $stmt = $pdo->prepare($query);
}

$stmt->execute();
$tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tags</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="flex flex-col min-h-screen bg-gray-50 text-black pt-20 overflow-x-hidden">

    <?php include 'Partials/nav.php'; ?>

    <aside class="hidden lg:block fixed top-[90px] left-0 h-[calc(100%-4rem)] w-[200px] bg-white z-10 shadow">
        <?php include 'Partials/left_nav.php'; ?>
    </aside>

    <main class="flex-1 min-w-[500px] max-w-screen-full md:ml-50 lg:mr-10 lg:ml-[250px]">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Tags</h1>
            <!-- <select class="bg-orange-500 text-white px-5 py-2 rounded text-sm">
                <option>Popular</option>
                <option>Newest</option>
            </select> -->

            <!-- Dropdown -->
            <div class="relative inline-block text-left">
                <button id="selectedItem"
                    class="inline-flex justify-between items-center w-[7.5rem] h-10 px-3 py-1.5 text-sm text-white bg-orange-500 rounded-md hover:bg-orange-600 focus:outline-none"
                    onclick="toggleItemDropdown()">
                    Sort by
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- Dropdown Options -->
                <div id="itemDropdown"
                    class="hidden absolute z-10 mt-1 w-[7.5rem] bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5">
                    <ul class="py-1 text-sm text-gray-700">
                        <li><button onclick="selectItem('Popular')"
                                class="block w-full text-left px-3 py-1 hover:bg-gray-100">Popular</button></li>
                        <li><button onclick="selectItem('Newest')"
                                class="block w-full text-left px-3 py-1 hover:bg-gray-100">Newest</button></li>
                    </ul>
                </div>
            </div>

            <script>
                function toggleItemDropdown() {
                    document.getElementById("itemDropdown").classList.toggle("hidden");
                }

                function selectItem(item) {
                    document.getElementById("selectedItem").innerHTML = `
      ${item}
      <svg class="w-4 h-4 ml-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"
        xmlns="http://www.w3.org/2000/svg">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M19 9l-7 7-7-7" />
      </svg>`;
                    toggleItemDropdown();
                }

                window.addEventListener('click', function(e) {
                    const dropdown = document.getElementById("itemDropdown");
                    const button = document.getElementById("selectedItem");
                    if (!button.contains(e.target) && !dropdown.contains(e.target)) {
                        dropdown.classList.add("hidden");
                    }
                });
            </script>

        </div>

        <div class="mb-6">
            <form method="GET" action="tag.php" class="flex items-center space-x-2">
                <input
                    type="text"
                    name="search"
                    placeholder="Search by tags"
                    value="<?= htmlspecialchars($search) ?>"
                    class="px-3.5 py-1.5 border border-gray-300 rounded w-72 focus:outline-none focus:ring-2 focus:ring-orange-400">
                <button type="submit" class="px-3.5 py-2.5 bg-orange-500 text-white rounded hover:bg-orange-600">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </button>
            </form>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php if (!empty($tags)): ?>
                <?php foreach ($tags as $tag): ?>
                    <div class="bg-white shadow rounded-lg p-4 hover:shadow-md transition">
                        <span class="inline-block bg-orange-100 text-orange-600 text-xs font-semibold px-2 py-1 rounded mb-2">
                            <?= htmlspecialchars($tag['tag_name']) ?>
                        </span>
                        <p class="text-gray-700 text-sm mb-1 line-clamp-3">
                            <?= htmlspecialchars($tag['description']) ?>
                        </p>
                        <p class="text-xs text-gray-500">
                            <?= htmlspecialchars($tag['question_count']) ?> questions
                        </p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-gray-500 col-span-full">No tags found.</p>
            <?php endif; ?>
        </div>

        <div class="flex justify-center items-center mt-8 space-x-2 text-sm">
            <a href="#" class="px-3 py-1 bg-orange-500 text-white rounded">1</a>
            <a href="#" class="px-3 py-1 hover:bg-orange-100 rounded">2</a>
            <a href="#" class="px-3 py-1 hover:bg-orange-100 rounded">3</a>
            <span>...</span>
            <a href="#" class="px-3 py-1 hover:bg-orange-100 rounded">68</a>
        </div>
    </main>

    <div class="mt-auto z-10">
        <?php include 'Partials/footer.php'; ?>
    </div>

</body>

</html>