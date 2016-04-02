<form method="post" id="users" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="row">
    <div class="col-md-6">
      <?php if ($this->access('user_edit') || $this->access('user_delete')) {
    ?>
      <div class="btn-group">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
          <?php echo $this->text('With selected');
    ?> <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
          <?php if ($this->access('user_edit')) {
    ?>
          <li>
            <a data-action="status" data-action-value="1" href="#">
            <?php echo $this->text('Status');
    ?>: <?php echo $this->text('Enabled');
    ?>
            </a>
          </li>
          <li>
            <a data-action="status" data-action-value="0" href="#">
            <?php echo $this->text('Status');
    ?>: <?php echo $this->text('Disabled');
    ?>
            </a>
          </li>
          <?php 
}
    ?>
          <?php if ($this->access('user_delete')) {
    ?>
          <li>
            <a data-action="delete" href="#">
            <?php echo $this->text('Delete');
    ?>
            </a>
          </li>
          <?php 
}
    ?>
        </ul>
      </div>
      <?php 
} ?>
    </div>
    <div class="col-md-6 text-right">
      <?php if ($this->access('user_add')) {
    ?>
      <div class="btn-group">
        <a class="btn btn-success" href="<?php echo $this->url('register');
    ?>">
          <i class="fa fa-plus"></i> <?php echo $this->text('Add');
    ?>
        </a>
      </div>
      <?php if ($this->access('import')) {
    ?>
      <div class="btn-group">
        <a class="btn btn-primary" href="<?php echo $this->url('admin/tool/import/user');
    ?>">
          <i class="fa fa-upload"></i> <?php echo $this->text('Import');
    ?>
        </a>
      </div>
      <?php 
}
    ?>
      <?php 
} ?>
    </div>
  </div>
  <div class="row margin-top-20">
    <div class="col-md-12">
      <table class="table table-responsive users">
        <thead>
          <tr>
            <th><input type="checkbox" id="select-all" value="1"></th>
            <th>
              <a href="<?php echo $sort_name; ?>"><?php echo $this->text('Name'); ?> <i class="fa fa-sort"></i></a>
            </th>
            <th>
              <a href="<?php echo $sort_email; ?>"><?php echo $this->text('Email'); ?> <i class="fa fa-sort"></i></a>
            </th>
            <th>
              <a href="<?php echo $sort_role_id; ?>"><?php echo $this->text('Role'); ?> <i class="fa fa-sort"></i></a>
            </th>
            <th>
              <a href="<?php echo $sort_store_id; ?>"><?php echo $this->text('Store'); ?> <i class="fa fa-sort"></i></a>
            </th>
            <th>
              <a href="<?php echo $sort_status; ?>"><?php echo $this->text('Status'); ?> <i class="fa fa-sort"></i></a>
            </th>
            <th>
              <a href="<?php echo $sort_created; ?>"><?php echo $this->text('Created'); ?> <i class="fa fa-sort"></i></a>
            </th>
            <th></th>
          </tr>
          <tr class="filters active">
            <th></th>
            <th>
              <input class="form-control" name="name" maxlength="255" value="<?php echo $filter_name; ?>" placeholder="<?php echo $this->text('Any'); ?>">
            </th>
            <th>
              <input class="form-control" name="email" maxlength="255" value="<?php echo $filter_email; ?>" placeholder="<?php echo $this->text('Any'); ?>">
            </th>
            <th>
              <select class="form-control" name="role_id">
                <option value="any"><?php echo $this->text('Any'); ?></option>
                <?php foreach ($roles as $role_id => $role) {
    ?>
                <option value="<?php echo $role_id;
    ?>"<?php echo ($filter_role_id == $role_id) ? ' selected' : '';
    ?>>
                  <?php echo $this->escape($role['name']);
    ?>
                </option>
                <?php 
} ?>
              </select>
            </th>
            <th>
              <select class="form-control" name="store_id">
                <option value="any"><?php echo $this->text('Any'); ?></option>
                <?php foreach ($stores as $store_id => $store) {
    ?>
                <option value="<?php echo $store_id;
    ?>"<?php echo ($filter_store_id == $store_id) ? ' selected' : '';
    ?>>
                <?php echo $this->escape($store);
    ?>
                </option>
                <?php 
} ?>
              </select>
            </th>
            <th>
              <select class="form-control" name="status">
                <option value="any"><?php echo $this->text('Any'); ?></option>
                <option value="1"<?php echo ($filter_status === '1') ? ' selected' : ''; ?>>
                  <?php echo $this->text('Enabled'); ?>
                </option>
                <option value="0"<?php echo ($filter_status === '0') ? ' selected' : ''; ?>>
                  <?php echo $this->text('Disabled'); ?>
                </option>
              </select>
            </th>
            <th></th>
            <th>
              <button type="button" class="btn btn-default clear-filter" title="<?php echo $this->text('Reset filter'); ?>">
                <i class="fa fa-refresh"></i>
              </button>
              <button type="button" class="btn btn-default filter" title="<?php echo $this->text('Filter'); ?>">
                <i class="fa fa-search"></i>
              </button>
            </th>
          </tr>
        </thead>
        <tbody>
          <?php if ($filtering && !$users) {
    ?>
          <tr>
            <td colspan="8"><?php echo $this->text('No results');
    ?></td>
          </tr>
          <?php 
} ?>
          <?php foreach ($users as $id => $user) {
    ?>
          <tr class="<?php echo ($id == $superadmin) ? 'warning' : '';
    ?>">
            <td class="middle">
              <?php if ($id != $superadmin) {
    ?>
              <input type="checkbox" class="select-all" name="selected[]" value="<?php echo $id;
    ?>">
              <?php 
}
    ?>
            </td>
            <td class="middle"><?php echo $this->escape($user['name']);
    ?></td>
            <td class="middle"><?php echo $this->escape($user['email']);
    ?></td>
            <td class="middle">
              <?php if (isset($roles[$user['role_id']]['name'])) {
    ?>
              <?php echo $this->escape($roles[$user['role_id']]['name']);
    ?>
              <?php 
} else {
    ?>
              <span class="text-danger"><?php echo $this->text('Unknown');
    ?></span>
              <?php 
}
    ?>
            </td>
            <td class="middle">
              <?php if (isset($stores[$user['store_id']])) {
    ?>
              <?php echo $this->escape($stores[$user['store_id']]);
    ?>
              <?php 
} else {
    ?>
              <span class="text-danger"><?php echo $this->text('Unknown');
    ?></span>
              <?php 
}
    ?>
            </td>
            <td class="middle">
              <?php if (empty($user['status'])) {
    ?>
              <i class="fa fa-square-o"></i>
              <?php 
} else {
    ?>
              <i class="fa fa-check-square-o"></i>
              <?php 
}
    ?>
            </td>
            <td class="middle"><?php echo $this->date($user['created']);
    ?></td>
            <td class="middle">
              <div class="btn-group">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                  <i class="fa fa-bars"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-right">
                  <li>
                    <a href="<?php echo $this->escape($user['url']);
    ?>">
                    <?php echo $this->text('View');
    ?>
                    </a>
                  </li>
                  <?php if ($this->access('user_edit')) {
    ?>
                  <li>
                    <a href="<?php echo $this->url("account/$id/edit");
    ?>">
                    <?php echo $this->text('Edit');
    ?>
                    </a>
                  </li>
                  <?php 
}
    ?>
                </ul>
              </div>
            </td>
          </tr>
          <?php 
} ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="row">
    <div class="col-md-12">
      <?php echo $pager; ?>
    </div>
  </div>
</form>