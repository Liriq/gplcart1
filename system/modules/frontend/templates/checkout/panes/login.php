<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 * @var $this \gplcart\core\controllers\frontend\Controller
 * To see available variables <?php print_r(get_defined_vars()); ?>
 */
?>
<?php if($this->error('login', true)) { ?>
<div class="alert alert-danger alert-dismissible clearfix">
  <button type="button" class="close" data-dismiss="alert">
    <span aria-hidden="true">&times;</span>
  </button>
  <?php echo $this->error('login'); ?>
</div>
<?php } ?>
<div class="form-group<?php echo $this->error('order.user.email', ' has-error'); ?>">
  <label class="col-md-2 col-form-label"><?php echo $this->text('E-mail'); ?></label>
  <div class="col-md-4">
    <input class="form-control" name="order[user][email]" data-ajax="false" value="<?php echo isset($order['user']['email']) ? $this->e($order['user']['email']) : ''; ?>" autofocus>
    <div class="form-text"><?php echo $this->error('order.user.email'); ?></div>
  </div>
</div>
<div class="form-group">
  <label class="col-md-2 col-form-label"><?php echo $this->text('Password'); ?></label>
  <div class="col-md-4">
    <input type="password" class="form-control" data-ajax="false" name="order[user][password]" value="">
  </div>
</div>
<div class="form-group">
  <div class="offset-md-2 col-md-4">
    <button class="btn" name="login" data-ajax="false" value="1"><?php echo $this->text('Log in'); ?></button>
    <button class="btn" name="checkout_anonymous" value="1"><?php echo $this->text('Continue as guest'); ?></button>
  </div>
</div>