<?php
if (isset($_SESSION['success'])) {
    echo '<div class="alert success">'.htmlspecialchars($_SESSION['success']).'</div>';
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    echo '<div class="alert error">'.htmlspecialchars($_SESSION['error']).'</div>';
    unset($_SESSION['error']);
}
?>