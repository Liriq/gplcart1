<form method="post" id="edit-city" class="form-horizontal" onsubmit="return confirm();">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="row">
    <div class="col-md-6 col-md-offset-6 text-right">
      <div class="btn-toolbar">
        <?php if (isset($city['city_id']) && $this->access('city_delete')) {
    ?>
        <button class="btn btn-danger delete" name="delete" value="1">
          <i class="fa fa-trash"></i> <?php echo $this->text('Delete');
    ?>
        </button>
        <?php 
} ?>
        <a href="<?php echo $this->url("admin/settings/cities/{$country['code']}/{$state['state_id']}"); ?>" class="btn btn-default">
          <i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?>
        </a>
        <?php if ($this->access('city_edit') || $this->access('city_add')) {
    ?>
        <button class="btn btn-primary save" name="save" value="1">
          <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save');
    ?>
        </button>
        <?php 
} ?>
      </div>
    </div>
  </div>
  <div class="row margin-top-20">
    <div class="col-md-12">
      <div class="form-group">
        <label class="col-md-2 control-label">
          <span class="hint" title="<?php echo $this->text('Disabled cities will not be displayed to customers'); ?>">
            <?php echo $this->text('Status'); ?>
          </span>
        </label>
        <div class="col-md-4">
          <div class="btn-group" data-toggle="buttons">
            <label class="btn btn-default<?php echo empty($city['status']) ? '' : ' active'; ?>">
              <input name="city[status]" type="radio" autocomplete="off" value="1"<?php echo empty($city['status']) ? '' : ' checked'; ?>>
              <?php echo $this->text('Enabled'); ?>
            </label>
            <label class="btn btn-default<?php echo empty($city['status']) ? ' active' : ''; ?>">
              <input name="city[status]" type="radio" autocomplete="off" value="0"<?php echo empty($city['status']) ? ' checked' : ''; ?>>
              <?php echo $this->text('Disabled'); ?>
            </label>
          </div>
        </div>
      </div>
      <div class="form-group<?php echo isset($form_errors['name']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label">
          <span class="hint" title="<?php echo $this->text('Native name of the city'); ?>">
          <?php echo $this->text('Name'); ?>
          </span>
        </label>
        <div class="col-md-4">
          <input maxlength="255" name="city[name]" class="form-control" value="<?php echo isset($city['name']) ? $this->escape($city['name']) : ''; ?>" autofocus>
          <?php if (isset($form_errors['name'])) {
    ?>
          <div class="help-block"><?php echo $form_errors['name'];
    ?></div>
          <?php 
} ?>
        </div>
      </div>
    </div>
  </div>
</form>