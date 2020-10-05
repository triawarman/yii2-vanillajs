<?php
/**
 * @author Triawarman <3awarman@gmail.com>
 * @license MIT
 */
namespace triawarman\yii2VanillaJs;

use yii\helpers\Html;

/**
 * Choices renders components based on Choices.js, for more options how to use,
 * please check to [jshjohnson/Choices](https://github.com/jshjohnson/Choices).
 * 
 * You can use this widget in an [[yii\bootstrap\ActiveForm|ActiveForm]] using the [[yii\widgets\ActiveField::widget()|widget()]]
 * method, for example like this:
 *
 * ```php
 * <?= $form->field($model, 'item_id')->widget(\triawarman\yii2VanillaJs\Choices::classname(), [
 *     // configure additional widget properties here
 * ]) ?>
 * ```
 * 
 * or when you using this widget without form model, you can write like
 * 
 * ```php
 * <?= Choices::widget([
 *      'name' => 'timezone'
 *      'clientOptions' => [
 *          'choices' => [
 *              [
 *                  'value'=> 'Pacific\/Midway',
 *                  'label'=> '(UTC -11:00) Pacific\/Midway'
 *              ],
 *              [
 *                  'value'=> 'Pacific\/Niue',
 *                  'label'=> '(UTC -11:00) Pacific\/Niue'
 *              ]
 *          ],
 *          'removeItemButton' => true,
 *          'searchFields' => ['label', 'value'],
 *          'shouldSort' => false
 *      ],
 * ]); ?>
 * ```
 * 
 * and you can see other option at `yii\helpers\Html\dropDownList` 
 */
class Choices extends \yii\widgets\InputWidget {
    /*
     * const type of choices control type when use as dropdown
     */
    const TYPE_IS_SELECT = 'select';
    /*
     * const type of choices control type when use as text-choices
     */
    const TYPE_IS_TEXT = 'text';
    
    /**
     * @var null|string function to run on template creation. Through this callback 
     * it is possible to provide custom templates for the various components of Choices 
     * (see terminology). For Choices to work with custom templates, 
     * it is important you maintain the various data attributes defined here. 
     * If you want just extend a little original template then you may use 
     * Choices.defaults.templates to get access to original template function.
     * 
     * see jshjohnson/Choices for more detail.
     */
    public $callbackOnCreateTemplates;
    /**
     * @var null|string function to run once Choices initialises.
     * 
     * see jshjohnson/Choices for more detail.
     */
    public $callbackOnInit;
    /**
     * @var null|array the options for the underlying jsjonhson/Choices JS plugin.
     */
    public $clientOptions = [];
    /**
     * @var array the HTML attributes for the widget container tag.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $options = [];
    /**
     * @param int $position the position at which the JS script tag should be inserted
     * in a page. The possible values are:
     *
     * - 1 [[POS_HEAD]]: in the head section
     * - 2 [[POS_BEGIN]]: at the beginning of the body section
     * - 3 [[POS_END]]: at the end of the body section
     * - 4 [[POS_READY]]: enclosed within jQuery(document).ready(). This is the default value.
     * - 5 [[POS_LOAD]]: enclosed within jQuery(window).load().
     *   Note that by using this position, the method will automatically register the jQuery js file.
     *   Note that by using this position, the method will automatically register the jQuery js file.
     */
    public $scriptPosition = 4;
    /**
     * @var null|array $items the option data items. The array keys are option values, and the array values
     * are the corresponding option labels. The array can also be nested (i.e. some array values are arrays too).
     * For each sub-array, an option group will be generated whose label is the key associated with the sub-array.
     * If you have a list of data models, you may convert them into the format described above using
     * [[\yii\helpers\ArrayHelper::map()]].
     *
     * Note, the values and labels will be automatically HTML-encoded by this method, and the blank spaces in
     * the labels will also be HTML-encoded.
     * 
     * Affects when control type is 'select', see [[yii\helpers\Html::dropDownList()]].
     */
    public $selectItems = [];
    /*
     *  @var string|null|array $selection the selected value(s). String for single or array for multiple selection(s).
     * 
     * Affects when control type is 'select', see [[yii\helpers\Html::dropDownList()]].
     */
    public $selectSelection = null;
    /*
     * @var string type of input, the values is "select" and "text", default value is "select". 
     */
    public $type = "select";
    /*
     * @var null|string that use for naming javasricpt variable.
     */
    public $variableName;
    
    /**
     * {@inheritdoc}
     */
    public function init(){
        parent::init();
        
        if (isset($this->callbackOnCreateTemplates)) 
            $this->clientOptions['callbackOnCreateTemplates'] = $this->callbackOnCreateTemplates;
        if (isset($this->callbackOnInit)) 
            $this->clientOptions['callbackOnInit'] = $this->callbackOnInit;
        
        if(isset($this->type)){
            $this->type = strtolower($this->type);
            if(!in_array($this->type, [self::TYPE_IS_SELECT, self::TYPE_IS_TEXT]))
                $this->type = self::TYPE_IS_SELECT;
        }
    }
    
    /**
     * Registers client script.
     */
    protected function regClientScript(){
        $view = $this->getView();
        ChoicesAssets::register($view);
        
        if ($this->clientOptions !== false) {
            $this->clientOptions = (empty($this->clientOptions) ? '' : ', '.\yii\helpers\Json::htmlEncode($this->clientOptions));
            
            if(!isset($this->variableName))
                $this->variableName = $this->options['id'];
            $this->variableName = \yii\helpers\Inflector::variablize($this->variableName);
            
            $js = 'var '. $this->variableName .'= new Choices(document.querySelector("#'.$this->options['id'].'")'. $this->clientOptions .');';
            
            $view->registerJs($js, $this->scriptPosition);
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function run(){
        $this->regClientScript();
        
        if($this->type == self::TYPE_IS_TEXT)
            return $this->renderInputHtml($this->type);
        else{
            if ($this->hasModel())
                return Html::activeDropDownList ($this->model, $this->attribute, $this->selectItems, $this->options);
            
            return Html::dropDownList($this->name, $this->selectSelection, $this->selectItems, $this->options);
        }
    }
}
