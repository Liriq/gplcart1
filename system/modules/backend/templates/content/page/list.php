<?php if ($pages || $filtering) {
    ?>
<form method="post" id="pages" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $token;
    ?>">
  <div class="row">
    <div class="col-md-6">
      <div class="btn-group">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
          <?php echo $this->text('With selected');
    ?> <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
          <?php if ($this->access('page_edit')) {
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
          <li>
            <a data-action="front" data-action-value="1" href="#">
              <?php echo $this->text('Front page');
    ?>: <?php echo $this->text('Add');
    ?>
            </a>
          </li>
          <li>
            <a data-action="front" data-action-value="0" href="#">
              <?php echo $this->text('Front page');
    ?>: <?php echo $this->text('Remove');
    ?>
            </a>
          </li>
          <?php 
}
    ?>
          <?php if ($this->access('page_delete')) {
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
    </div>
    <div class="col-md-6 text-right">
      <?php if ($this->access('page_add')) {
    ?>
      <div class="btn-group">
        <a class="btn btn-success" href="<?php echo $this->url('admin/content/page/add');
    ?>">
          <i class="fa fa-plus"></i> <?php echo $this->text('Add');
    ?>
        </a>
      </div>
      <?php 
}
    ?>
    </div>
  </div>
  <div class="row">
    <div class="col-md-12">
      <table class="table table-responsive margin-top-20 pages-list">
        <thead>
          <tr>
            <th><input type="checkbox" id="select-all" value="1"></th>
            <th>
              <a href="<?php echo $sort_title;
    ?>">
                <?php echo $this->text('Title');
    ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th>
              <a href="<?php echo $sort_store_id;
    ?>">
                <?php echo $this->text('Store');
    ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th>
              <a href="<?php echo $sort_email;
    ?>">
                <?php echo $this->text('Author');
    ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th>
              <a href="<?php echo $sort_status;
    ?>">
                <?php echo $this->text('Status');
    ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th class="middle">
              <a href="<?php echo $sort_front;
    ?>">
                <?php echo $this->text('Front');
    ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th>
              <a href="<?php echo $sort_created;
    ?>">
                <?php echo $this->text('Created');
    ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th></th>
          </tr>
          <tr class="filters active">
            <th></th>
            <th>
              <input class="form-control" name="title" value="<?php echo $filter_title;
    ?>" placeholder="<?php echo $this->text('Any');
    ?>">
            </th>
            <th>
              <select class="form-control" name="store_id">
                <option value="any"><?php echo $this->text('Any');
    ?></option>
                <?php foreach ($stores as $store_id => $store_name) {
    ?>
                <option value="<?php echo $store_id;
    ?>"<?php echo ($filter_store_id == $store_id) ? ' selected' : '';
    ?>>
                <?php echo $this->escape($store_name);
    ?>
                </option>
                <?php 
}
    ?>
              </select>
            </th>
            <th>
              <input class="form-control" value="<?php echo $filter_email;
    ?>" placeholder="<?php echo $this->text('Any');
    ?>">
            </th>
            <th>
              <select class="form-control" name="status">
                <option value="any">
                  <?php echo $this->text('Any');
    ?>
                </option>
                <option value="1"<?php echo ($filter_status === '1') ? ' selected' : '';
    ?>>
                  <?php echo $this->text('Enabled');
    ?>
                </option>
                <option value="0"<?php echo ($filter_status === '0') ? ' selected' : '';
    ?>>
                  <?php echo $this->text('Disabled');
    ?>
                </option>
              </select>
            </th>
            <th class="middle">
              <select class="form-control" name="front">
                <option value="any">
                  <?php echo $this->text('Any');
    ?>
                </option>
                <option value="1"<?php echo ($filter_front === '1') ? ' selected' : '';
    ?>>
                    <?php echo $this->text('Yes');
    ?>
                </option>
                <option value="0"<?php echo ($filter_front === '0') ? ' selected' : '';
    ?>>
                  <?php echo $this->text('No');
    ?>
                </option>
              </select>
            </th>
            <th></th>
            <th>
              <button type="button" class="btn btn-default clear-filter" title="<?php echo $this->text('Reset filter');
    ?>">
                <i class="fa fa-refresh"></i>
              </button>
              <button type="button" class="btn btn-default filter" title="<?php echo $this->text('Filter');
    ?>">
                <i class="fa fa-search"></i>
              </button>
            </th>
          </tr>
        </thead>
        <tbody>
          <?php if ($filtering && !$pages) {
    ?>
          <tr>
            <td colspan="8"><?php echo $this->text('No results');
    ?></td>
          </tr>
          <?php 
}
    ?>
          <?php foreach ($pages as $id => $page) {
    ?>
          <tr>
            <td class="middle">
              <input type="checkbox" class="select-all" name="selected[]" value="<?php echo $id;
    ?>">
            </td>
            <td class="middle"><?php echo $this->truncate($this->escape($page['title']), 30);
    ?></td>
            <td class="middle">
              <?php if (isset($stores[$page['store_id']])) {
    ?>
              <?php echo $this->escape($stores[$page['store_id']]);
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
              <?php echo $this->escape($page['email']);
    ?>
            </td>
            <td class="middle">
              <?php if (empty($page['status'])) {
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
            <td class="middle">
              <?php if (empty($page['front'])) {
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
            <td class="middle"><?php echo $this->date($page['created']);
    ?></td>
            <td class="middle">
              <div class="btn-group">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                  <i class="fa fa-bars"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-right">
                  <li>
                    <a href="<?php echo $this->escape($page['url']);
    ?>">
                    <?php echo $this->text('View');
    ?>
                    </a>
                  </li>
                  <?php if ($this->access('page_edit')) {
    ?>
                  <li>
                    <a href="<?php echo $this->url("admin/content/page/edit/$id");
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
}
    ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="row"><div class="col-md-12"><?php echo $pager;
    ?></div></div>
</form>
<?php 
} else {
    ?>
<div class="row">
  <div class="col-md-12">
    <?php echo $this->text('You have no pages yet');
    ?>
    <?php if ($this->access('page_add')) {
    ?>
    <a href="<?php echo $this->url('admin/content/page/add');
    ?>"><?php echo $this->text('Add');
    ?></a>
    <?php 
}
    ?>
  </div>
</div>
<?php 
} ?>