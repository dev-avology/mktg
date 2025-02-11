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
 * @since 1.0
 */

/** @var Controller $controller */
$controller = controller();

/** @var string $pageHeading */
$pageHeading = (string)$controller->getData('pageHeading');

/** @var Lists $list */
$list = $controller->getData('list');

/** @var array $refreshRoute */
$refreshRoute = (array)$controller->getData('refreshRoute');

/** @var string $gridAjaxUrl */
$gridAjaxUrl = (string)$controller->getData('gridAjaxUrl');

/**
 * This hook gives a chance to prepend content or to replace the default view content with a custom content.
 * Please note that from inside the action callback you can access all the controller view
 * variables via {@CAttributeCollection $collection->controller->getData()}
 * In case the content is replaced, make sure to set {@CAttributeCollection $collection->add('renderContent', false)}
 * in order to stop rendering the default content.
 * @since 1.3.3.1
 */
hooks()->doAction('before_view_file_content', $viewCollection = new CAttributeCollection([
    'controller'    => $controller,
    'renderContent' => true,
]));

// and render if allowed
if ($viewCollection->itemAt('renderContent')) {
    /**
     * @since 1.3.9.2
     */
    $itemsCount = (int)Lists::model()->countByAttributes([
        'customer_id' => (int)customer()->getId(),
        'status'      => array_keys($list->getStatusesList()),
    ]); ?>
    <div class="box box-primary borderless">
        <div class="box-header">
            <div class="pull-left">
                <?php BoxHeaderContent::make(BoxHeaderContent::LEFT)
                    ->add('<h3 class="box-title">' . IconHelper::make('glyphicon-list-alt') . html_encode((string)$pageHeading) . '</h3>')
                    ->render(); ?>
            </div>
            <div class="pull-right">
                <?php BoxHeaderContent::make(BoxHeaderContent::RIGHT)
                    ->addIf($controller->widget('common.components.web.widgets.GridViewToggleColumns', ['model' => $list, 'columns' => ['list_uid', 'name', 'display_name', 'subscribers_count', 'opt_in', 'opt_out', 'date_added', 'last_updated']], true), $itemsCount && !$list->getIsArchived())
                    ->addIf(CHtml::link(IconHelper::make('fa-users') . t('app', 'All subscribers'), ['lists/all_subscribers'], ['class' => 'btn btn-primary btn-flat', 'title' => t('app', 'All subscribers')]), $itemsCount && !$list->getIsArchived())
                    ->addIf(CHtml::link(IconHelper::make('create') . t('app', 'Create new'), ['lists/create'], ['class' => 'btn btn-primary btn-flat', 'title' => t('app', 'Create new')]), !$list->getIsArchived())
                    ->addIf(CHtml::link(IconHelper::make('glyphicon-compressed') . t('app', 'Archived lists'), ['lists/index', 'Lists[status]' => Lists::STATUS_ARCHIVED], ['class' => 'btn btn-primary btn-flat', 'title' => t('lists', 'View archived lists')]), !$list->getIsArchived())
                    ->addIf(CHtml::link(IconHelper::make('glyphicon-list-alt') . t('app', 'All lists'), ['lists/index'], ['class' => 'btn btn-primary btn-flat', 'title' => t('lists', 'View all lists')]), $list->getIsArchived())
                    ->addIf(CHtml::link(IconHelper::make('export') . t('app', 'Export'), ['lists/export'], ['target' => '_blank', 'class' => 'btn btn-primary btn-flat', 'title' => t('app', 'Export')]), $itemsCount && !$list->getIsArchived())
                    ->add(CHtml::link(IconHelper::make('refresh') . t('app', 'Refresh'), $refreshRoute, ['class' => 'btn btn-primary btn-flat', 'title' => t('app', 'Refresh')]))
                    ->render(); ?>
            </div>
            <div class="clearfix"><!-- --></div>
            <p style="margin:10px;padding:10px">NOTE: If you add/remove contacts or contact lists in your Booostr Contact Manager in another tab while you have your newsletter tab open - you will need to refresh this page to see any contact and/or contact list updates that you made in your Booostr Contact Manager.</p>
        </div>
        <div class="box-body">
            <div class="table-responsive">
            <?php
            /**
             * This hook gives a chance to prepend content or to replace the default grid view content with a custom content.
             * Please note that from inside the action callback you can access all the controller view
             * variables via {@CAttributeCollection $collection->controller->getData()}
             * In case the content is replaced, make sure to set {@CAttributeCollection $collection->itemAt('renderGrid')} to false
             * in order to stop rendering the default content.
             * @since 1.3.3.1
             */
            hooks()->doAction('before_grid_view', $collection = new CAttributeCollection([
                'controller'    => $controller,
                'renderGrid'    => true,
            ]));

    /**
     * This widget renders default getting started page for this particular section.
     * @since 1.3.9.2
     */
    $controller->widget('common.components.web.widgets.StartPagesWidget', [
                'collection' => $collection,
                'enabled'    => !$itemsCount,
            ]);

    // and render if allowed
    if ($collection->itemAt('renderGrid')) {
        $controller->widget('zii.widgets.grid.CGridView', hooks()->applyFilters('grid_view_properties', [
                    'ajaxUrl'           => $gridAjaxUrl,
                    'id'                => $list->getModelName() . '-grid',
                    'dataProvider'      => $list->search(),
                    'filter'            => $list,
                    'filterPosition'    => 'body',
                    'filterCssClass'    => 'grid-filter-cell',
                    'itemsCssClass'     => 'table table-hover',
                    'selectableRows'    => 0,
                    'enableSorting'     => true,
                    'cssFile'           => false,
                    'pagerCssClass'     => 'pagination pull-right',
                    'pager'             => [
                        'class'         => 'CLinkPager',
                        'cssFile'       => false,
                        'header'        => false,
                        'htmlOptions'   => ['class' => 'pagination'],
                    ],
                    'columns' => hooks()->applyFilters('grid_view_columns', [
                        [
                            'name'  => 'list_uid',
                            'value' => 'CHtml::link($data->list_uid,createUrl("lists/overview", array("list_uid" => $data->list_uid)))',
                            'type'  => 'raw',
                        ],
                        [
                            'name'  => 'name',
                            'value' => 'CHtml::link($data->name,createUrl("lists/overview", array("list_uid" => $data->list_uid)))',
                            'type'  => 'raw',
                        ],
                        [
                            'name'  => 'display_name',
                            'value' => 'CHtml::link($data->display_name,createUrl("lists/overview", array("list_uid" => $data->list_uid)))',
                            'type'  => 'raw',
                        ],
                        [
                            'name'     => 'subscribers_count',
                            'value'    => 'formatter()->formatNumber($data->getConfirmedSubscribersCount(true)) . " / " . formatter()->formatNumber($data->getSubscribersCount(true))',
                            'filter'   => false,
                            'sortable' => false,
                        ],
                        [
                            'name'      => 'opt_in',
                            'value'     => 't("lists", ucfirst($data->opt_in))',
                            'filter'    => $list->getOptInArray(),
                            'sortable'  => false,
                        ],
                        [
                            'name'      => 'opt_out',
                            'value'     => 't("lists", ucfirst($data->opt_out))',
                            'filter'    => $list->getOptOutArray(),
                            'sortable'  => false,
                        ],
                        [
                            'name'      => 'date_added',
                            'value'     => '$data->dateAdded',
                            'filter'    => false,
                        ],
                        [
                            'name'      => 'last_updated',
                            'value'     => '$data->lastUpdated',
                            'filter'    => false,
                        ],
                        [
                            'class'     => 'DropDownButtonColumn',
                            'header'    => t('app', 'Options'),
                            'footer'    => $list->paginationOptions->getGridFooterPagination(),
                            'buttons'   => [
                                'overview' => [
                                    'label'     => IconHelper::make('info'),
                                    'url'       => 'createUrl("lists/overview", array("list_uid" => $data->list_uid))',
                                    'imageUrl'  => null,
                                    'options'   => ['title' => t('lists', 'Overview'), 'class' => 'btn btn-primary btn-flat'],
                                    'visible'   => '!$data->getIsPendingDelete()',
                                ],
                                'campaigns' => [
                                    'label'     => IconHelper::make('envelope'),
                                    'url'       => '$data->getPublicCampaignsListUrl()',
                                    'imageUrl'  => null,
                                    'options'   => ['target' => '_blank', 'title' => t('lists', 'Public campaigns history'), 'class' => 'btn btn-primary btn-flat'],
                                    'visible'   => '!$data->isPendingDelete',
                                ],
                                'copy'=> [
                                    'label'     => IconHelper::make('copy'),
                                    'url'       => 'createUrl("lists/copy", array("list_uid" => $data->list_uid))',
                                    'imageUrl'  => null,
                                    'options'   => ['title' => t('app', 'Copy'), 'class' => 'btn btn-primary btn-flat copy-list'],
                                    'visible'   => '!$data->getIsPendingDelete() && !$data->getIsArchived()',
                                ],
                                'update' => [
                                    'label'     => IconHelper::make('update'),
                                    'url'       => 'createUrl("lists/update", array("list_uid" => $data->list_uid))',
                                    'imageUrl'  => null,
                                    'options'   => ['title' => t('app', 'Update'), 'class' => 'btn btn-primary btn-flat'],
                                    'visible'   => '$data->getEditable()',
                                ],
                                'import'=> [
                                    'label'     => IconHelper::make('import'),
                                    'url'       => 'createUrl("lists/import", array("list_uid" => $data->list_uid))',
                                    'imageUrl'  => null,
                                    'options'   => ['title' => t('app', 'Import'), 'class' => 'btn btn-primary btn-flat'],
                                    'visible'   => '!$data->getIsPendingDelete() && !$data->getIsArchived()',
                                ],
                                'archive' => [
                                    'label'     => IconHelper::make('glyphicon-compressed'),
                                    'url'       => 'createUrl("lists/toggle_archive", array("list_uid" => $data->list_uid))',
                                    'imageUrl'  => null,
                                    'options'   => ['title' => t('app', 'Archive'), 'class' => 'btn btn-primary btn-flat'],
                                    'visible'   => '!$data->getIsPendingDelete() && !$data->getIsArchived()',
                                ],
                                'unarchive' => [
                                    'label'     => IconHelper::make('glyphicon-expand'),
                                    'url'       => 'createUrl("lists/toggle_archive", array("list_uid" => $data->list_uid))',
                                    'imageUrl'  => null,
                                    'options'   => ['title' => t('app', 'Unarchive'), 'class' => 'btn btn-primary btn-flat'],
                                    'visible'   => '!$data->getIsPendingDelete() && $data->getIsArchived()',
                                ],
                                'confirm_delete' => [
                                    'label'     => IconHelper::make('delete'),
                                    'url'       => 'createUrl("lists/delete", array("list_uid" => $data->list_uid))',
                                    'imageUrl'  => null,
                                    'options'   => ['title' => t('app', 'Delete'), 'class' => 'btn btn-danger btn-flat'],
                                    'visible'   => '$data->getIsRemovable()',
                                ],
                            ],
                            'headerHtmlOptions' => ['style' => 'text-align: right'],
                            'footerHtmlOptions' => ['align' => 'right'],
                            'htmlOptions'       => ['align' => 'right', 'class' => 'options'],
                            'template'          =>'{overview} {campaigns} {copy} {update} {import} {archive} {unarchive} {confirm_delete}',
                        ],
                    ], $controller),
                ], $controller));
    }
    /**
     * This hook gives a chance to append content after the grid view content.
     * Please note that from inside the action callback you can access all the controller view
     * variables via {@CAttributeCollection $collection->controller->getData()}
     * @since 1.3.3.1
     */
    hooks()->doAction('after_grid_view', new CAttributeCollection([
                'controller'    => $controller,
                'renderedGrid'  => $collection->itemAt('renderGrid'),
            ])); ?>
            <div class="clearfix"><!-- --></div>
            </div>
        </div>
    </div>
<?php
}
/**
 * This hook gives a chance to append content after the view file default content.
 * Please note that from inside the action callback you can access all the controller view
 * variables via {@CAttributeCollection $collection->controller->getData()}
 * @since 1.3.3.1
 */
hooks()->doAction('after_view_file_content', new CAttributeCollection([
    'controller'        => $controller,
    'renderedContent'   => $viewCollection->itemAt('renderContent'),
]));
