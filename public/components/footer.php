<?php 
include 'includes/config.php';
include './includes/db_connection.php';

$topStmt = $pdo->query("
    SELECT keyword, COUNT(*) AS count
    FROM search_logs
    WHERE created_at >= NOW() - INTERVAL 7 DAY
    GROUP BY keyword
    ORDER BY count DESC
    LIMIT 50
");
$topSearches = $topStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<footer>
        <div class="inner-footer">
            <div>
                <p>Top Searches</p>
            </div>

            <div class="top-searches">
                <?php foreach ($topSearches as $index => $row): ?>
                    <a href="/search.php?search_query=<?= urlencode($row['keyword']) ?>">
                        <?= htmlspecialchars($row['keyword']) ?>
                    </a>
                    <?php if ($index < count($topSearches) - 1): ?> | <?php endif; ?>
                <?php endforeach; ?>
            </div>

        </div>

        <div class=bottom-footer>
            <div><img src="/public/assets/images/temp_logo.png" height="auto" width="32px" alt=""></div>
            <div><?php echo "© ". date("Y") . " " . $title ?></div>
        </div>

</footer>