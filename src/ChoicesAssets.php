<?php
/**
 * @author Triawarman <3awarman@gmail.com>
 * @license MIT
 */
namespace triawarman\yii2VanillaJs;

/**
 * Choices asset bundle.
 *
 */
class ChoicesAssets extends \yii\web\AssetBundle{
    public $sourcePath = '@bower/choices.js/public/assets';
    public $publishOptions = [
            //'forceCopy' => YII_DEBUG,
        ];
    public $css = (YII_ENV_DEV ? [
            //'styles/base.css',
            'styles/choices.css',
        ] : [
            //'styles/base.min.css',
            'styles/choices.min.css',
        ]);
    public $js = (YII_ENV_DEV ? [
            'scripts/choices.min.js'
        ] : [
            'scripts/choices.js'
        ]);
}
