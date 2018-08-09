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
<form method="post">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <div class="form-group row">
    <div class="col-md-6">
      <div class="btn-group btn-group-toggle" data-toggle="buttons">
        <label class="btn btn-outline-secondary<?php echo empty($review['status']) ? '' : ' active'; ?>">
          <input name="review[status]" type="radio" autocomplete="off" value="1"<?php echo empty($product['status']) ? '' : ' checked'; ?>><?php echo $this->text('Enabled'); ?>
        </label>
        <label class="btn btn-outline-secondary<?php echo empty($review['status']) ? ' active' : ''; ?>">
          <input name="review[status]" type="radio" autocomplete="off" value="0"<?php echo empty($review['status']) ? ' checked' : ''; ?>><?php echo $this->text('Disabled'); ?>
        </label>
      </div>
      <div class="form-text">
        <div class="description">
            <?php echo $this->text('Disabled reviews will not be available to customers and search engines'); ?>
        </div>
      </div>
    </div>
  </div>
  <div class="form-group row<?php echo $this->error('created', ' has-error'); ?>">
    <div class="col-md-4">
      <label><?php echo $this->text('Created'); ?></label>
      <input data-datepicker="true" data-datepicker-settings='{}' name="review[created]" class="form-control" value="<?php echo empty($review['created']) ? $this->date(null, false) : $this->date($review['created'], false); ?>">
      <div class="form-text">
        <?php echo $this->error('created'); ?>
        <div class="description"><?php echo $this->text('Date when the review was created'); ?></div>
      </div>
    </div>
  </div>
  <div class="form-group row required<?php echo $this->error('email', ' has-error'); ?>">
    <div class="col-md-4">
      <label><?php echo $this->text('Email'); ?></label>
      <input name="review[email]" class="form-control" value="<?php echo isset($review['email']) ? $this->e($review['email']) : ''; ?>">
      <div class="form-text">
        <?php echo $this->error('email'); ?>
        <div class="description"><?php echo $this->text('An E-mail of the person who will be author of the review'); ?></div>
      </div>
    </div>
  </div>
  <div class="form-group row<?php echo isset($review['review_id']) ? '' : ' required'; ?><?php echo $this->error('product_id', ' has-error'); ?>">
    <div class="col-md-4">
      <?php echo $product_picker; ?>
    </div>
  </div>
  <div class="form-group row required<?php echo $this->error('text', ' has-error'); ?>">
    <div class="col-md-10">
      <label><?php echo $this->text('Text'); ?></label>
      <textarea name="review[text]" rows="8" class="form-control"><?php echo isset($review['text']) ? $this->e($review['text']) : ''; ?></textarea>
      <div class="form-text">
        <?php echo $this->error('text'); ?>
        <div class="description"><?php echo $this->text('Text of the review. HTML tags are not allowed'); ?></div>
      </div>
    </div>
  </div>
      <div class="btn-toolbar">
        <?php if (isset($review['review_id']) && $this->access('review_delete')) { ?>
        <button class="btn btn-danger delete" name="delete" value="1" onclick="return confirm('<?php echo $this->text('Are you sure? It cannot be undone!'); ?>');">
          <?php echo $this->text('Delete'); ?>
        </button>
        <?php } ?>
        <a href="<?php echo $this->url('admin/content/review'); ?>" class="btn cancel">
          <?php echo $this->text('Cancel'); ?>
        </a>
        <?php if ($this->access('review_edit') || $this->access('review_add')) { ?>
        <button class="btn btn-success save" name="save" value="1">
          <?php echo $this->text('Save'); ?>
        </button>
        <?php } ?>
      </div>
</form>