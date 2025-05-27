<?php
if (isset($_SESSION['message'])) {
    echo '<div class="message ' . htmlspecialchars($_SESSION['message_type']) . '">';
    echo htmlspecialchars($_SESSION['message']);
    echo '</div>';
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}
