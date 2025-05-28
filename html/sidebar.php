<?php
if (isset($_SESSION['success'])) {
    echo '<div class="success">'.$_SESSION['success'].'</div>';
    unset($_SESSION['success']);
}
?>