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
 * @since 1.3.4.3
 */

/** @var Controller $controller */
$controller = controller();

/** @var DeliveryServer $server */
$server = $controller->getData('server');

/** @var CActiveForm $form */
$form = $controller->getData('form');

?>

<hr />

<div class="box box-primary borderless">
    <div class="box-header">
        <div class="pull-left">
            <h3 class="box-title"><?php echo IconHelper::make('glyphicon-user') . t('servers', 'Customer'); ?></h3>
        </div>
        <div class="pull-right"></div>
        <div class="clearfix"><!-- --></div>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-lg-3">
                <div class="form-group">
                    <?php echo $form->labelEx($server, 'customer_id'); ?>
                    <?php echo $form->hiddenField($server, 'customer_id', $server->fieldDecorator->getHtmlOptions('customer_id')); ?>
                    <?php
                    $controller->widget('zii.widgets.jui.CJuiAutoComplete', [
                        'name'          => 'customer',
                        'value'         => !empty($server->customer) ? ($server->customer->getFullName() ? $server->customer->getFullName() : $server->customer->email) : null,
                        'source'        => createUrl('customers/autocomplete'),
                        'cssFile'       => false,
                        'options'       => [
                            'minLength' => '2',
                            'select'    => 'js:function(event, ui) {
                        $("#' . CHtml::activeId($server, 'customer_id') . '").val(ui.item.customer_id);
                    }',
                            'search'    => 'js:function(event, ui) {
                        $("#' . CHtml::activeId($server, 'customer_id') . '").val("");
                    }',
                            'change'    => 'js:function(event, ui) {
                        if (!ui.item) {
                            $("#' . CHtml::activeId($server, 'customer_id') . '").val("");
                        }
                    }',
                        ],
                        'htmlOptions'   => $server->fieldDecorator->getHtmlOptions('customer_id'),
                    ]);
                    ?>
                    <?php echo $form->error($server, 'customer_id'); ?>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group">
                    <?php echo $form->labelEx($server, 'locked'); ?>
                    <?php echo $form->dropDownList($server, 'locked', $server->getYesNoOptions(), $server->fieldDecorator->getHtmlOptions('locked')); ?>
                    <?php echo $form->error($server, 'locked'); ?>
                </div>
            </div>
        </div>
    </div>
</div>
