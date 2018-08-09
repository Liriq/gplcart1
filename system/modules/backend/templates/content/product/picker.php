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
<div class="form-group row<?php echo empty($error) ? '' : ' has-error'; ?>">
  <div class="col-md-12">
    <?php if(!empty($label)) { ?>
    <label><?php echo $this->e($label); ?></label>
    <?php } ?>
    <div class="product-picker-results">
      <?php if (!empty($products)) { ?>
      <?php foreach ($products as $product) { ?>
      <div class="selected-item">
        <?php if(empty($multiple)) { ?>
        <input type="hidden" name="<?php echo $name; ?>" value="<?php echo $product[$key]; ?>">
        <?php } else { ?>
        <input type="hidden" name="<?php echo $name; ?>[]" value="<?php echo $product[$key]; ?>">
        <?php } ?>
        <?php echo $product['rendered']; ?>
      </div>
      <?php } ?>
      <?php } ?>
    </div>
    <input class="form-control product-picker"
           placeholder="<?php echo $this->text('Start to type product title or SKU'); ?>"
           data-name="<?php echo $name; ?>"
           data-multiple="<?php echo $multiple; ?>"
           data-store-id="<?php echo $store_id; ?>"
           data-key="<?php echo $key; ?>">
    <?php if(!empty($error) || !empty($description)) { ?>
    <div class="form-text">
        <?php echo empty($error) ? '' : $this->e($error); ?>
        <?php if(!empty($description)) { ?>
          <div class="description"><?php echo $this->e($description); ?></div>
        <?php } ?>
    </div>
    <?php } ?>
  </div>
</div>

