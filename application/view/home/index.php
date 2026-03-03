<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$role = $_SESSION['role'] ?? null;
?>
<style>
  @font-face {
    font-family: ArchivoBlack;
    src: url("<?php echo URL; ?>fonts/ArchivoBlack-Regular.ttf");
  }

</style>

<link href="<?php echo URL; ?>css/home.css" rel="stylesheet">
<link href="<?php echo URL; ?>css/styles.css" rel="stylesheet">
<div class="grey-bc">
  <div class="container">

    <?php if($role === 'staff'): ?>
      <div class="card" style="background-color:#05545a;" 
           onClick="to_url('<?php echo URL . 'inventoryreturn'; ?>')">
        <b style="color: #fff; margin-left:3rem; font-family: ArchivoBlack; font-size:20px;">ELECTRONICS INVENTORY TOOL</b>
      </div>
      <?php endif; ?>

      <?php if( $role === 'super_admin'): ?>
      <div class="card" style="background-color:#20253a;" 
           onClick="to_url('<?php echo URL . 'users/getUsers'; ?>')">
        <b style="color: #fff; margin-left:3rem; font-family: ArchivoBlack; font-size:20px;">ELECTRONICS INVENTORY TOOL</b>
      </div>
      <?php endif; ?>

      <?php if($role === 'admin' ): ?>
      <div class="card" style="background-color:#e600a0;" 
           onClick="to_url('<?php echo URL . 'inventoryassignment'; ?>')">
        <b style="color: #fff; margin-left:3rem; font-family: ArchivoBlack; font-size:20px;">ELECTRONICS INVENTORY TOOL</b>
      </div>
      <?php endif; ?>

      <!-- <?php if ($role === 'admin' || $role === 'super_admin'): ?>
      <div class="card" style="background-color:#e600a0;" 
           onClick="to_url('<?php echo URL . 'consumables'; ?>')">
        <b style="color: #fff; margin-left:3rem; font-family: ArchivoBlack; font-size:20px;">LAB INVENTORY TOOL</b>
      </div>
      <?php endif; ?> -->

  </div>
</div>

<script src="<?php echo URL; ?>js/utils.js"></script>
