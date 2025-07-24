<?php
include '../includes/db.php';
$ds = mysqli_query($conn, "SELECT * FROM hoidap WHERE duyet=0");
while($r = mysqli_fetch_assoc($ds)) {
    echo "<form method='post'>
        <p>Q: {$r['cauhoi']}</p>
        <textarea name='traloi'></textarea>
        <input type='hidden' name='id' value='{$r['id']}'>
        <button name='traloi_btn'>Trả lời</button>
    </form>";
}
if (isset($_POST['traloi_btn'])) {
    $id = $_POST['id'];
    $traloi = $_POST['traloi'];
    mysqli_query($conn, "UPDATE hoidap SET traloi='$traloi', duyet=1 WHERE id=$id");
}
?>
