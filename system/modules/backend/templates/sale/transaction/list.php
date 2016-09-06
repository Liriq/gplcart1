<?php if (!empty($transactions) || $filtering) { ?>
<form method="post" id="transactions">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="panel panel-default">
    <div class="panel-heading clearfix">
      <?php if ($this->access('transaction_edit') || $this->access('transaction_delete')) { ?>
      <div class="btn-group pull-left">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
          <?php echo $this->text('With selected'); ?> <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
          <?php if ($this->access('transaction_edit')) { ?>
          <li>
            <a data-action="status" data-action-value="1" href="#">
              <?php echo $this->text('Status'); ?>: <?php echo $this->text('Enabled'); ?>
            </a>
          </li>
          <li>
            <a data-action="status" data-action-value="0" href="#">
              <?php echo $this->text('Status'); ?>: <?php echo $this->text('Disabled'); ?>
            </a>
          </li>
          <?php } ?>
          <?php if ($this->access('transaction_delete')) { ?>
          <li>
            <a data-action="delete" href="#">
              <?php echo $this->text('Delete'); ?>
            </a>
          </li>
          <?php } ?>
        </ul>
      </div>
      <?php } ?>
    </div>
    <div class="panel-body">
      <table class="table table-striped transactions">
        <thead>
          <tr>
            <th>
              <input type="checkbox" id="select-all" value="1">
            </th>
            <th>
              <a href="<?php echo $sort_order_id; ?>">
                <?php echo $this->text('Order ID'); ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th>
              <a href="<?php echo $sort_payment_service; ?>">
                <?php echo $this->text('Service'); ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th>
              <a href="<?php echo $sort_service_transaction_id; ?>">
                <?php echo $this->text('Service transaction ID'); ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th>
              <a href="<?php echo $sort_created; ?>">
                <?php echo $this->text('Created'); ?> <i class="fa fa-sort"></i>
              </a>
            </th>
            <th></th>
          </tr>
          <tr class="filters active">
            <th></th>
            <th>
              <input class="form-control" name="order_id" value="<?php echo $filter_order_id; ?>" placeholder="<?php echo $this->text('Any'); ?>">
            </th>
            <th>
              <select name="payment_service" class="form-control">
                <option value="any"><?php echo $this->text('Any'); ?></option>
                <?php foreach ($payment_services as $service_id => $service) { ?>
                <option value="<?php echo $this->escape($service_id); ?>"<?php echo ($filter_payment_service == $service_id) ? ' selected' : '' ?>>
                <?php echo $this->escape($service['name']); ?>
                </option>
                <?php } ?>
              </select>
            </th>
            <th>
              <input class="form-control" name="service_transaction_id" value="<?php echo $filter_service_transaction_id; ?>" placeholder="<?php echo $this->text('Any'); ?>">
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
          <?php if ($filtering && empty($transactions)) { ?>
          <tr>
            <td colspan="6">
              <?php echo $this->text('No results'); ?>
              <a href="#" class="clear-filter"><?php echo $this->text('Reset'); ?></a>
            </td>
          </tr>
          <?php } ?>
          <?php foreach ($transactions as $transaction_id => $transaction) { ?>
          <tr>
            <td class="middle">
              <input type="checkbox" class="select-all" name="selected[]" value="<?php echo $transaction_id; ?>">
            </td>
            <td class="middle">
              <?php echo $this->escape($transaction['order_id']); ?>
            </td>
            <td class="middle">
              <?php echo $this->escape($transaction['payment_service']); ?>
            </td>
            <td class="middle">
              <?php echo $this->escape($transaction['service_transaction_id']); ?>
            </td>
            <td class="middle">
              <?php echo $this->date($transaction['created']); ?>
            </td>
            <td></td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
    <?php if (!empty($pager)) { ?>
    <div class="panel-footer text-right"><?php echo $pager; ?></div>
    <?php } ?>
  </div>
</form>
<?php } else { ?>
<div class="row">
  <div class="col-md-12">
    <?php echo $this->text('You have no transactions'); ?>
  </div>
</div>
<?php } ?>
