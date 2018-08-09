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
  <div class="form-group">
    <label class="col-md-2 col-form-label"><?php echo $this->text('Status'); ?></label>
    <div class="col-md-4">
      <div class="btn-group" data-toggle="buttons">
        <label class="btn<?php echo empty($zone['status']) ? '' : ' active'; ?>">
          <input name="zone[status]" type="radio" autocomplete="off" value="1"<?php echo empty($zone['status']) ? '' : ' checked'; ?>><?php echo $this->text('Enabled'); ?>
        </label>
        <label class="btn<?php echo empty($zone['status']) ? ' active' : ''; ?>">
          <input name="zone[status]" type="radio" autocomplete="off" value="0"<?php echo empty($zone['status']) ? ' checked' : ''; ?>><?php echo $this->text('Disabled'); ?>
        </label>
      </div>
      <div class="text-muted">
        <?php echo $this->text('Disabled zones will not be available to users'); ?>
      </div>
    </div>
  </div>
  <div class="form-group required<?php echo $this->error('title', ' has-error'); ?>">
    <label class="col-md-2 col-form-label"><?php echo $this->text('Name'); ?></label>
    <div class="col-md-4">
      <input name="zone[title]" maxlength="255" class="form-control" value="<?php echo isset($zone['title']) ? $this->e($zone['title']) : ''; ?>">
      <div class="form-text">
        <?php echo $this->error('title'); ?>
        <div class="text-muted">
           <?php echo $this->text('Name for administrators'); ?>
        </div>
      </div>
    </div>
  </div>
  <div class="form-group">
    <div class="col-md-10 offset-md-2">
      <div class="btn-toolbar">
        <?php if ($can_delete) { ?>
        <button class="btn btn-danger delete" name="delete" value="1" onclick="return confirm('<?php echo $this->text('Are you sure? It cannot be undone!'); ?>');">
          <?php echo $this->text('Delete'); ?>
        </button>
        <?php } ?>
        <a class="btn" href="<?php echo $this->url('admin/settings/zone'); ?>">
          <?php echo $this->text('Cancel'); ?>
        </a>
        <?php if ($this->access('zone_edit') || $this->access('zone_add')) { ?>
        <button class="btn" name="save" value="1">
          <?php echo $this->text('Save'); ?>
        </button>
        <?php } ?>
      </div>
    </div>
  </div>
</form>