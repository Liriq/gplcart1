<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 * @var $this \gplcart\core\controllers\backend\Controller
 * To see available variables <?php print_r(get_defined_vars()); ?>
 */
?>
<?php if (!empty($product['bundled_products'])) { ?>
<div class="card bundled-items">
  <div class="card-header clearfix">
    <h4 class="card-title"><?php echo $this->text('Bundled products'); ?></h4>
  </div>
  <div class="card-body">
    <div class="bundled-items">
      <?php foreach ($product['bundled_products'] as $product) { ?>
      <?php echo $product['rendered']; ?>
      <?php } ?>
    </div>
  </div>
</div>
<?php } ?>