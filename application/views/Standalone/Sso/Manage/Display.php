<p>
    Below are the current details stored by the Single Sign-On (SSO) system.
    Please note that as not all data has been transitioned from other (older) systems,
    some data might be recorded incorrectly.
</p>

<table class="table">
    <tr>
        <th>CID</th>
        <td><?= $_account->id ?></td>
    </tr>
    <tr>
        <th>First Name</th>
        <td><?= $_account->name_first ?></td>
    </tr>
    <tr>
        <th>Last Name</th>
        <td><?= $_account->name_last ?></td>
    </tr>
    <tr>
        <th>Primary Email Address</th>
        <td>
            <strong>
                <?= $_account->emails->get_active_primary()->email ?>
            </strong>
            <a class="tooltip_displays" href="#" data-toggle="tooltip" title="<?= gmdate("D jS M Y \@ H:i:s \G\M\T", strtotime($_account->emails->get_active_primary()->created)) ?>">
                <em>added <?= Date::fuzzy_span(strtotime($_account->emails->get_active_primary()->created)) ?></em>
            </a>
            <?php if(count($_account->emails->get_active_primary()->sso_email->find_all()) > 0): ?>
                <br />
                <em style="margin-left: 25px;">Assigned to: 
                <?php foreach($_account->emails->get_active_primary()->sso_email->find_all() as $key => $sso): ?>
                    <?=$sso->sso_system?>
                    <?=($key+1 < count($_account->emails->get_active_primary()->sso_email->find_all())) ? ", " : ""?>
                <?php endforeach; ?>
                </em>
            <?php endif; ?>
        </td>
    </tr>
    <tr>
        <th>Secondary Email Addresses</th>
        <td>
            <?php if (count($_account->emails->get_active_secondary()) > 0): ?>
                <?php foreach ($_account->emails->get_active_secondary() as $email): ?>
                    <strong>
                    <?= $email->email ?>
                    </strong>
                    <a class="tooltip_displays" href="#" data-toggle="tooltip" title="<?= gmdate("D jS M Y \@ H:i:s \G\M\T", strtotime($email->created)) ?>">
                        <em>added <?= Date::fuzzy_span(strtotime($email->created)) ?></em>
                    </a>
                    <?php if(count($email->sso_email->find_all()) > 0): ?>
                        <br />
                        <em style="margin-left: 25px;">Assigned to: 
                        <?php foreach($email->sso_email->find_all() as $key => $sso): ?>
                            <?=$sso->sso_system?>
                            <?=($key+1 < count($email->sso_email->find_all())) ? ", " : ""?>
                        <?php endforeach; ?>
                        </em>
                    <?php endif; ?>
                    <br />
                <?php endforeach; ?>
            <?php else: ?>
                No secondary email addresses are currently set.
            <?php endif; ?>
        </td>
    </tr>
    <tr>
        <th>Second Layer Security</th>
        <td>
            <?php if ($_account->security->loaded() && $_account->security->value != null): ?>
                You currently have second layer security enabled.
                <?php if ($_account->security->type == Enum_Account_Security::MEMBER): ?>
                    <strong>You are allowed to disable this.</strong>
                <?php else: ?>
                    <strong>You are not allowed to disable this.</strong>
                <?php endif; ?>
            <?php else: ?>
                Second layer security is disabled on your account.
            <?php endif; ?>
            <br />
            <a class="tooltip_displays" href="#" data-toggle="tooltip" title="
               To protect your account further, you can add a secondary password to your account.  You will then be required to enter this password
               after logging in, prior to being granted access to your account or our systems."><em>What is this?</em></a>
        </td>
    </tr>
    <tr>
        <th>Last SSO Login</th>
        <td>
            <?php if ($_account->last_login_ip != 0): ?>
                <strong><?= $_account->get_last_login_ip() ?></strong>
                <a class="tooltip_displays" href="#" data-toggle="tooltip" title="<?= gmdate("D jS M Y \@ H:i:s \G\M\T", strtotime($_account->last_login)) ?>">
                    <em><?= Date::fuzzy_span(strtotime($_account->last_login)) ?></em> 
                </a>
            <?php else: ?>
                No login history available.
            <?php endif; ?>
        </td>
    </tr>
    <?php if(count($_account->qualifications->get_all_admin(true)) > 0): ?>
    <tr>
        <th>Administrative Ratings<br /><small>Past and Present</small></th>
        <td> 
            <?php foreach ($_account->qualifications->get_all_admin(true, "created") as $qual): ?>
                <?= $qual->formatQualification(true) ?> (<?= $qual ?>)
                <a class="tooltip_displays" href="#" data-toggle="tooltip" title="<?= gmdate("D jS M Y \@ H:i:s \G\M\T", strtotime($qual->created)) ?>">
                    <em>granted <?= Date::fuzzy_span(strtotime($qual->created)) ?></em>
                </a>
                <?php if($qual->removed != NULL): ?>
                    -
                    <a class="tooltip_displays" href="#" data-toggle="tooltip" title="<?= gmdate("D jS M Y \@ H:i:s \G\M\T", strtotime($qual->removed)) ?>">
                        <em>revoked <?= Date::fuzzy_span(strtotime($qual->removed)) ?></em>
                    </a>
                <?php endif; ?>
                <br />
            <?php endforeach; ?>
        </td>
    </tr>
    <?php endif; ?>
    <tr>
        <th>ATC Qualifications<br /><small>Showing all achieved</small></th>
        <td> 
            <?php foreach ($_account->qualifications->get_all_atc(true) as $qual): ?>
                <?= $qual->formatQualification(true) ?> (<?= $qual ?>)
                <em>awarded 
                <a class="tooltip_displays" href="#" data-toggle="tooltip" title="<?= gmdate("D jS M Y \@ H:i:s \G\M\T", strtotime($qual->created)) ?>">
                    <?= Date::fuzzy_span(strtotime($qual->created)) ?>
                </a>
                    
                <?php if($qual->removed != null): ?>
                    - expired 
                    <a class="tooltip_displays" href="#" data-toggle="tooltip" title="<?= gmdate("D jS M Y \@ H:i:s \G\M\T", strtotime($qual->removed)) ?>">
                        <?= Date::fuzzy_span(strtotime($qual->removed)) ?>
                    </a>
                <?php endif; ?>
                .</em>
                <br />
            <?php endforeach; ?>
            <?php if (count($_account->qualifications->get_all_atc()) < 1): ?>
                You have no ATC ratings.
            <?php endif; ?>
        </td>
    </tr>
    <?php if(count($_account->qualifications->get_all_training_atc(true)) > 0): ?>
    <tr>
        <th>ATC Training Ratings<br /><small>Past and Present</small></th>
        <td> 
            <?php foreach ($_account->qualifications->get_all_training_atc(true, "created") as $qual): ?>
                <?= $qual->formatQualification(true) ?> (<?= $qual ?>)
                <a class="tooltip_displays" href="#" data-toggle="tooltip" title="<?= gmdate("D jS M Y \@ H:i:s \G\M\T", strtotime($qual->created)) ?>">
                    <em>granted <?= Date::fuzzy_span(strtotime($qual->created)) ?></em>
                </a>
                <?php if($qual->removed != NULL): ?>
                    -
                    <a class="tooltip_displays" href="#" data-toggle="tooltip" title="<?= gmdate("D jS M Y \@ H:i:s \G\M\T", strtotime($qual->removed)) ?>">
                        <em>revoked <?= Date::fuzzy_span(strtotime($qual->removed)) ?></em>
                    </a>
                <?php endif; ?>
                <br />
            <?php endforeach; ?>
        </td>
    </tr>
    <?php endif; ?>
    <tr>
        <th>Pilot Qualifications<br /><small>Showing all achieved</small></th>
        <td>
            <?php foreach ($_account->qualifications->get_all_pilot(true) as $qual): ?>
                <?= $qual->formatQualification(true) ?> (<?= $qual ?>)
                <em>awarded 
                <a class="tooltip_displays" href="#" data-toggle="tooltip" title="<?= gmdate("D jS M Y \@ H:i:s \G\M\T", strtotime($qual->created)) ?>">
                    <?= Date::fuzzy_span(strtotime($qual->created)) ?>
                </a>
                    
                <?php if($qual->removed != null): ?>
                    - expired 
                    <a class="tooltip_displays" href="#" data-toggle="tooltip" title="<?= gmdate("D jS M Y \@ H:i:s \G\M\T", strtotime($qual->removed)) ?>">
                        <?= Date::fuzzy_span(strtotime($qual->removed)) ?>
                    </a>
                <?php endif; ?>
                .</em>
                <br />
            <?php endforeach; ?>
            <?php if (count($_account->qualifications->get_all_pilot()) < 1): ?>
                You have no Pilot ratings.
            <?php endif; ?>
        </td>
    </tr>
    <?php if(count($_account->qualifications->get_all_training_pilot(true)) > 0): ?>
    <tr>
        <th>Pilot Training Ratings<br /><small>Past and Present</small></th>
        <td> 
            <?php foreach ($_account->qualifications->get_all_training_pilot(true, "created") as $qual): ?>
                <?= $qual->formatQualification(true) ?> (<?= $qual ?>)
                <a class="tooltip_displays" href="#" data-toggle="tooltip" title="<?= gmdate("D jS M Y \@ H:i:s \G\M\T", strtotime($qual->created)) ?>">
                    <em>granted <?= Date::fuzzy_span(strtotime($qual->created)) ?></em>
                </a>
                <?php if($qual->removed != NULL): ?>
                    -
                    <a class="tooltip_displays" href="#" data-toggle="tooltip" title="<?= gmdate("D jS M Y \@ H:i:s \G\M\T", strtotime($qual->removed)) ?>">
                        <em>revoked <?= Date::fuzzy_span(strtotime($qual->removed)) ?></em>
                    </a>
                <?php endif; ?>
                <br />
            <?php endforeach; ?>
        </td>
    </tr>
    <?php endif; ?>
    <tr>
        <th>Account Status</th>
        <td> 
            <?=$_account->getStatus()?> <?=$_account->states->getCurrent()->formatState(true)?>
        </td>
    </tr>
    <tr>
        <th>Actions</th>
        <td>
            <?php if($_account->is_overriding()): ?>
                [<?= HTML::anchor("sso/auth/logout?override=1", "Cancel Override") ?>]
            <?php else: ?>
                [<?= HTML::anchor("sso/auth/logout", "Logout") ?>]
            <?php endif; ?>

            <?php if ($_account->security->loaded() && $_account->security->type == Enum_Account_Security::MEMBER): ?>
                &nbsp;&nbsp;
                [<?= HTML::anchor("sso/security/disable", "Disable") ?> | <?= HTML::anchor("sso/security/replace", "Modify")?> Secondary Password]
            <?php elseif (!$_account->security->loaded()): ?>
                &nbsp;&nbsp;
                [<?= HTML::anchor("sso/security/enable", "Enable Secondary Password") ?>]
            <?php endif; ?>

            <?php if (in_array($_account->id, array(980234, 1010573))): ?>
                &nbsp;&nbsp;
                [<?= HTML::anchor("sso/auth/override", "Account Override") ?>]
            <?php endif; ?>
        </td>
    </tr>
</table>