<?php if (!$search): ?>
    <nav class="nav" aria-label="pagination">
        <ul class="pagination">
            <?php
            $params = $_GET;

            if ($page > 1):
                $params['page'] = $page - 1;
            ?>
                <li><a href="?<?= http_build_query($params) ?>">Précédent</a></li>
            <?php endif; ?>

            <?php
            $max_links = 3;
            $start = max(1, $page - floor($max_links / 2));
            $end = min($totalPages, $start + $max_links - 1);

            if ($end - $start + 1 < $max_links) {
                $start = max(1, $end - $max_links + 1);
            }

            if ($start > 1):
                $params['page'] = 1;
            ?>
                <li><a href="?<?= http_build_query($params) ?>">1</a></li>
                <?php if ($start > 2): ?>
                    <li><span>…</span></li>
                <?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $start; $i <= $end; $i++): ?>
                <?php
                $params['page'] = $i;
                ?>
                <li>
                    <a class="<?= $i == $page ? 'active' : '' ?>" href="?<?= http_build_query($params) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($end < $totalPages): ?>
                <?php if ($end < $totalPages - 1): ?>
                    <li><span>…</span></li>
                <?php endif; ?>
                <?php
                $params['page'] = $totalPages;
                ?>
                <li><a href="?<?= http_build_query($params) ?>"><?= $totalPages ?></a></li>
            <?php endif; ?>

            <?php if ($page < $totalPages):
                $params['page'] = $page + 1;
            ?>
                <li><a href="?<?= http_build_query($params) ?>">Suivant</a></li>
            <?php endif; ?>
        </ul>
    </nav>
<?php endif; ?>

