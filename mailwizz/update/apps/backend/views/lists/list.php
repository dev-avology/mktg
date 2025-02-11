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
        'status' => array_keys($list->getStatusesList()),
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
                    ->addIf($controller->widget('common.components.web.widgets.GridViewToggleColumns', ['model' => $list, 'columns' => ['list_id', 'list_uid', 'customer_id', 'display_name', 'default_from_name', 'default_from_email', 'subscribers_count', 'last_updated']], true), $itemsCount && !$list->getIsArchived())
                    ->addIf(CHtml::link(IconHelper::make('fa-users') . t('app', 'All subscribers'), ['lists/all_subscribers'], ['class' => 'btn btn-primary btn-flat', 'title' => t('app', 'All subscribers')]), $itemsCount && !$list->getIsArchived())
                    ->addIf(CHtml::link(IconHelper::make('glyphicon-compressed') . t('app', 'Archived lists'), ['lists/index', 'Lists[status]' => Lists::STATUS_ARCHIVED], ['class' => 'btn btn-primary btn-flat', 'title' => t('lists', 'View archived lists')]), !$list->getIsArchived())
                    ->addIf(CHtml::link(IconHelper::make('glyphicon-list-alt') . t('app', 'All lists'), ['lists/index'], ['class' => 'btn btn-primary btn-flat', 'title' => t('lists', 'View all lists')]), $list->getIsArchived())
                    ->add(HtmlHelper::accessLink(IconHelper::make('refresh') . t('app', 'Refresh'), $refreshRoute, ['class' => 'btn btn-primary btn-flat', 'title' => t('app', 'Refresh')]))
                    ->render(); ?>
            </div>
            <div class="clearfix"><!-- --></div>
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
                            'name'  => 'list_id',
                            'value' => '$data->list_id',
                            'filter'=> false,
                        ],
                        [
                            'name'  => 'list_uid',
                            'value' => 'HtmlHelper::accessLink($data->list_uid, array("lists/overview", "list_uid" => $data->list_uid), array("fallbackText" => true))',
                            'type'  => 'raw',
                        ],
                        [
                            'name'  => 'customer_id',
                            'value' => 'HtmlHelper::accessLink($data->customer->getFullName(), array("customers/update", "id" => $data->customer_id), array("fallbackText" => true))',
                            'type'  => 'raw',
                        ],
                        [
                            'name'  => 'display_name',
                            'value' => 'HtmlHelper::accessLink($data->display_name, array("lists/overview", "list_uid" => $data->list_uid), array("fallbackText" => true))',
                            'type'  => 'raw',
                        ],
                        [
                            'name'      => 'default_from_name',
                            'value'     => '$data->default->from_name',
                            'sortable'  => false,
                        ],
                        [
                            'name'      => 'default_from_email',
                            'value'     => '$data->default->from_email',
                            'sortable'  => false,
                        ],
                        [
                            'name'      => 'subscribers_count',
                            'value'     => 'formatter()->formatNumber($data->getConfirmedSubscribersCount(true)) . " / " . formatter()->formatNumber($data->getSubscribersCount(true))',
                            'filter'    => false,
                            'sortable'  => false,
                        ],
                        [
                            'name'  => 'last_updated',
                            'value' => '$data->lastUpdated',
                            'filter'=> false,
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
                                    'visible'   => 'AccessHelper::hasRouteAccess("lists/overview") && !$data->isPendingDelete',
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
                                    'visible'   => 'AccessHelper::hasRouteAccess("lists/delete") && $data->getIsRemovable()',
                                ],
                            ],
                            'headerHtmlOptions' => ['style' => 'text-align: right'],
                            'footerHtmlOptions' => ['align' => 'right'],
                            'htmlOptions'       => ['align' => 'right', 'class' => 'options'],
                            'template'          =>'{overview} {archive} {unarchive} {confirm_delete}',
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
