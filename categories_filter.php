<!-- categories_filter.php -->
<div class="categories-filter">
    <a href="shop.php" class="<?php echo (!isset($_GET['category']) || $_GET['category'] == 0) ? 'active' : ''; ?>">All</a>
    <?php foreach ($categories as $category): ?>
        <a href="shop.php?category=<?php echo htmlspecialchars($category['id']); ?>" class="<?php echo (isset($_GET['category']) && $_GET['category'] == $category['id']) ? 'active' : ''; ?>">
            <?php echo htmlspecialchars($category['name']); ?>
        </a>
    <?php endforeach; ?>
</div>