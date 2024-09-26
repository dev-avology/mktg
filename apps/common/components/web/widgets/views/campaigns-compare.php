<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * This file is part of the MailWizz EMA application.
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.9.17
 */

?>

<div class="modal modal-info modal-compare fade" id="campaigns-compare-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo IconHelper::make('fa-compress') . t('app', 'Campaigns comparison'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="modal-body-loader">
                        <div class="text-center">
                            <?php echo IconHelper::make('fa-spinner fa-spin') . ' ' . t('app', 'Please wait...'); ?>
                        </div>
                    </div>
                    <?php
                    $form = $this->beginWidget('CActiveForm', [
                        'action'      => ['campaigns/compare'],
                        'id'          => 'campaigns-compare-form',
                        'htmlOptions' => [
                            'style' => 'display:none',
                        ],
                    ]);
$this->endWidget(); ?>
                    <div class="modal-body-content" style="display:none">

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
