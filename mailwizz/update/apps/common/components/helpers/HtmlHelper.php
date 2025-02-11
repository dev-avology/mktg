<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * HtmlHelper
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.5
 */

class HtmlHelper extends CHtml
{
    /**
     * @param string $text
     * @param string|array $url
     * @param array $htmlOptions
     *
     * @return string
     */
    public static function accessLink(string $text, $url = '#', array $htmlOptions = []): string
    {
        $fallbackText = false;
        if (isset($htmlOptions['fallbackText'])) {
            $fallbackText = (bool)$htmlOptions['fallbackText'];
            unset($htmlOptions['fallbackText']);
        }

        $app = Yii::app();
        if (is_array($url) && $app->apps->isAppName('backend') && $app->hasComponent('user') && $app->user->getId() && $app->user->getModel()) {
            if (!$app->user->getModel()->hasRouteAccess($url[0])) {
                return $fallbackText ? $text : '';
            }
        }

        return self::link($text, $url, $htmlOptions);
    }

    /**
     * @param string $content
     *
     * @return string
     */
    public static function fixDragAndDropBuilderMarkup(string $content): string
    {
        // handle orphaned 'draggable' attribute which blocks text selection
        return (string)preg_replace('/draggable(\s+)?=(\s+)?(\042|\047)?(true)(\042|\047)?/six', '', $content);
    }
}
