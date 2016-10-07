<form method="post" enctype="multipart/form-data" id="common-settings" class="form-horizontal" onsubmit="return confirm();">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="panel panel-default">
    <div class="panel-heading"><?php echo $this->text('Google API'); ?></div>
    <div class="panel-body">
      <div class="form-group<?php echo isset($this->errors['gapi_browser_key']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Google API browser key'); ?></label>
        <div class="col-md-4">
          <input name="settings[gapi_browser_key]" class="form-control" value="<?php echo $this->escape($settings['gapi_browser_key']); ?>">
          <div class="help-block">
            <?php if (isset($this->errors['gapi_browser_key'])) { ?>
            <?php echo $this->errors['gapi_browser_key']; ?>
            <?php } ?>
            <div class="text-muted">
              <?php echo $this->text('A browser key from Google Developers Console. Used for standard API like Google Maps etc'); ?>
            </div>
          </div>
        </div>
      </div>
      <div class="form-group<?php echo isset($this->errors['gapi_email']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Google API service e-mail'); ?></label>
        <div class="col-md-4">
          <input name="settings[gapi_email]" class="form-control" value="<?php echo $this->escape($settings['gapi_email']); ?>">
          <div class="help-block">
            <?php if (isset($this->errors['gapi_email'])) { ?>
            <?php echo $this->errors['gapi_email']; ?>
            <?php } ?>
            <div class="text-muted">
              <?php echo $this->text('A service account e-mail from Google Developers Console'); ?>
            </div>
          </div>
        </div>
      </div>
      <div class="form-group<?php echo isset($this->errors['gapi_certificate']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Google API certificate'); ?></label>
        <div class="col-md-4">
          <input type="file" accept=".p12" name="gapi_certificate" class="form-control">
          <div class="help-block">
            <?php if (isset($this->errors['gapi_certificate'])) { ?>
            <?php echo $this->errors['gapi_certificate']; ?>
            <?php } ?>
            <div class="text-muted">
              <?php echo $this->text('Upload your .p12 certificate file you got from Google Developers Console'); ?>
            </div>
          </div>
        </div>
      </div>
      <?php if (!empty($settings['gapi_certificate'])) { ?>
      <div class="form-group">
          <div class="col-md-4 col-md-offset-2">
            <div class="checkbox">
              <label>
                <input type="checkbox" name="delete_gapi_certificate" value="1">
                <?php echo $this->text('Delete %file', array('%file' => $settings['gapi_certificate'])); ?>
              </label>
            </div>
          </div>
      </div>
      <?php } ?>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading"><?php echo $this->text('E-mail'); ?></div>
    <div class="panel-body">
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Mailer'); ?></label>
        <div class="col-md-4">
          <select  name="settings[email_method]" class="form-control">
            <option value="mail"<?php echo ($settings['email_method'] == 'mail') ? ' selected' : ''; ?>>
            <?php echo $this->text('mail()'); ?>
            </option>
            <option value="smtp"<?php echo ($settings['email_method'] == 'smtp') ? ' selected' : ''; ?>>
            <?php echo $this->text('SMTP'); ?>
            </option>
          </select>
          <div class="help-block">
            <?php echo $this->text('Select a method to send e-mails. mail() is very limited but does not require any addition configuration'); ?>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('SMTP authentication'); ?></label>
        <div class="col-md-4">
          <div class="btn-group" data-toggle="buttons">
            <label class="btn btn-default<?php echo empty($settings['smtp_auth']) ? '' : ' active'; ?>">
              <input name="settings[smtp_auth]" type="radio" autocomplete="off" value="1"<?php echo empty($settings['smtp_auth']) ? '' : ' checked'; ?>>
              <?php echo $this->text('Enabled'); ?>
            </label>
            <label class="btn btn-default<?php echo empty($settings['smtp_auth']) ? ' active' : ''; ?>">
              <input name="settings[smtp_auth]" type="radio" autocomplete="off" value="0"<?php echo empty($settings['smtp_auth']) ? ' checked' : ''; ?>>
              <?php echo $this->text('Disabled'); ?>
            </label>
          </div>
          <div class="help-block">
            <?php echo $this->text('Log in using an authentication mechanism supported by the SMTP server'); ?>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-2 control-label">
          <?php echo $this->text('SMTP encryption'); ?>
        </label>
        <div class="col-md-4">
          <select  name="settings[smtp_secure]" class="form-control">
            <option value="tls"<?php echo ($settings['smtp_secure'] == 'tls') ? ' selected' : ''; ?>>
            <?php echo $this->text('TLS'); ?>
            </option>
            <option value="ssl"<?php echo ($settings['smtp_secure'] == 'ssl') ? ' selected' : ''; ?>>
            <?php echo $this->text('SSL'); ?>
            </option>
          </select>
          <div class="help-block">
            <?php echo $this->text('Select a authentication protocol for the SMTP server'); ?>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('SMTP hosts'); ?></label>
        <div class="col-md-4">
          <textarea name="settings[smtp_host]" class="form-control"><?php echo $this->escape($settings['smtp_host']); ?></textarea>
          <div class="help-block">
            <?php echo $this->text('Enter a list of SMTP hosts, one per line. The very first host will be main, other - backup'); ?>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-2 control-label">
          <?php echo $this->text('SMTP user'); ?>
        </label>
        <div class="col-md-4">
          <input name="settings[smtp_username]" class="form-control" value="<?php echo $this->escape($settings['smtp_username']); ?>">
          <div class="help-block">
            <?php echo $this->text('A username to be used for authentication on the SMTP server'); ?>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('SMTP password'); ?></label>
        <div class="col-md-4">
          <input name="settings[smtp_password]" type="password" class="form-control" autocomplete="new-password" value="<?php echo $this->escape($settings['smtp_password']); ?>">
          <div class="help-block">
            <?php echo $this->text('A password to be used for authentication on the SMTP server'); ?>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('SMTP port'); ?></label>
        <div class="col-md-4">
          <input name="settings[smtp_port]" class="form-control" value="<?php echo $this->escape($settings['smtp_port']); ?>">
          <div class="help-block">
            <?php echo $this->text('Enter a numeric SMTP port. SMTP by default uses for submissions port 587, SMTPS (secured) uses 465'); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading"><?php echo $this->text('Miscellaneous'); ?></div>
    <div class="panel-body">
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Cron key'); ?></label>
        <div class="col-md-4">
          <input name="settings[cron_key]" maxlength="255" class="form-control" value="<?php echo $settings['cron_key']; ?>">
          <div class="help-block">
            <div class="text-muted">
              <?php echo $this->text('The key is used to run scheduled operations from outside of the site. Leave empty to generate a random key'); ?>
            </div>
            <?php if (!empty($settings['cron_key']) && $this->access('cron')) { ?>
            <a target="_blank" href="<?php echo $this->url('cron', array('key' => $settings['cron_key'])); ?>">
              <?php echo $this->text('Run cron'); ?>
            </a>
            <?php } ?>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Display errors'); ?></label>
        <div class="col-md-4">
          <select  name="settings[error_level]" class="form-control">
            <option value="0"<?php echo ($settings['error_level'] == 0) ? ' selected' : ''; ?>>
            <?php echo $this->text('None'); ?>
            </option>
            <option value="1"<?php echo ($settings['error_level'] == 1) ? ' selected' : ''; ?>>
            <?php echo $this->text('Errors, notices, warnings'); ?>
            </option>
            <option value="2"<?php echo ($settings['error_level'] == 2) ? ' selected' : ''; ?>>
            <?php echo $this->text('All'); ?>
            </option>
          </select>
          <div class="help-block">
            <?php echo $this->text('Select which PHP errors are reported. You must disable reporting on production for security reason'); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="row">
        <div class="col-md-6 col-md-offset-2">
          <button class="btn btn-default" name="save" value="1">
            <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?>
          </button>
        </div>
      </div>
    </div>
  </div>
</form>
