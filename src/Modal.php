<?php
/**
 * @author Triawarman <3awarman@gmail.com>
 * @license MIT
 */
namespace triawarman\yii2VanillaJs;

use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Modal renders an modal html-component.
 *
 * For example,
 *
 * ```php
 * echo Modal::widget([
 *     'options' => [
 *         'class' => 'some-class',
 *     ],
 *     'body' => 'Hey this is modal content...',
 *     'tittle' => 'This is modal tittle'
 * ]);
 * ```
 *
 * The following example will show the content enclosed between the [[begin()]]
 * and [[end()]] calls within the alert box:
 *
 * ```php
 * Modal::begin([
 *     'options' => [
 *         'class' => 'alert-warning',
 *     ],
 *     'tittle' => 'Some Tittle',
 *     'footer' => 'You can put something for footer modal'
 * ]);
 *
 * echo 'Yup this is modal content';
 *
 * Modal::end();
 * ```
 */
class Modal extends \yii\base\Widget{
    /**
    * @var boolean indicate that Modal use html element to showing message.
    */
    private $staticMessage = false;
    /**
     * @var string of cascading style sheet.
     */
    private $defaultStyles = (YII_ENV_DEV ? '
        .modal-toggler:checked + .modal-container {
            opacity: 1;
            visibility: visible;
            transition: opacity 0.5s;
        }
        [aria-hidden="true"] {
            display: none;
        }
        .modal-container {
            background: rgba(0, 0, 0, 0.75);
            opacity: 0;
            color: white;
            width: 100%;
            height: 100%;
            position: fixed;
            top: 0;
            left: 0;
            visibility: hidden;
            transition: opacity 0.5s, visibility 0s linear 0.5s;
            z-index: 2;
        }
        {close_any_where}
        .modal-container>.modal-box {
            position: absolute;
            top: 50%;
            left: 50%;
            border-radius: 0.5em;
            transform: translateX(-50%) translateY(-50%);
            display: flex;
            flex-direction: column;
            align-items: stretch;
            background: indianred;
            padding: 1.3rem;

            /*INFO: These props are for override bootstrap 4 modal stylesheet
            display: initial;
            overflow: initial;
            outline: initial;
             */
            
            /*INFO: Override these value for different dimension*/
            right: 0;
            bottom: 0;
        }
        .modal-container>.modal-box>header{
            padding: .7rem 0;
            flex-shrink: 0;
        }
        .modal-container>.modal-box>.close{
            position: absolute;
            font-size: 1.5rem;
            right: 0;
            top: 0;
            padding: .5rem;
            
            float: right;
            font-weight: bold;
            line-height: 1;
            color: #000;
            text-shadow: 0 1px 0 #fff;
            /*filter: alpha(opacity=20);*/
            opacity: 0.2;
        }
        .modal-container>.modal-box>.content {
            flex-grow: 1;
        }
        .modal-container>.modal-box footer {
            flex-shrink: 0;
        }
        ' : '.modal-toggler:checked+.modal-container{opacity:1;visibility:visible;transition:opacity .5s}[aria-hidden="true"]{display:none}.modal-container{background:rgba(0,0,0,0.75);opacity:0;color:white;width:100%;height:100%;position:fixed;top:0;left:0;visibility:hidden;transition:opacity .5s,visibility 0s linear .5s;z-index:2}{close_any_where}.modal-container>.modal-box{position:absolute;top:50%;left:50%;border-radius:.5em;transform:translateX(-50%) translateY(-50%);display:flex;flex-direction:column;align-items:stretch;background:indianred;padding:1.3rem;right:0;bottom:0}.modal-container>.modal-box>header{padding:.7rem 0;flex-shrink:0}.modal-container>.modal-box>.close{position:absolute;font-size:1.5rem;right:0;top:0;padding:.5rem;float:right;font-weight:bold;line-height:1;color:#000;text-shadow:0 1px 0 #fff;opacity:.2}.modal-container>.modal-box>.content{flex-grow:1}.modal-container>.modal-box footer{flex-shrink:0}');
    /**
     * @var string the body content in the modal component. Note that anything between
     * the [[begin()]] and [[end()]] calls of the Modal widget will also be treated
     * as the body content, and will be rendered before this.
     */
    public $body;
    /**
     * @var boolean that click any location out of modal element, 
     * will close the modal.
     */
    public $clickOuterModalCloseModal = true;
    /**
     * @var array|false the options for rendering the close button tag.
     * The close button is displayed in the header of the modal window. Clicking
     * on the button will hide the modal window. If this is false, no close button will be rendered.
     *
     * The following special options are supported:
     *
     * - tag: string, the tag name of the button. Defaults to 'button'.
     * - label: string, the label of the button. Defaults to '&times;'.
     *
     * The rest of the options will be rendered as the HTML attributes of the button tag.
     * Please refer to the [Alert documentation](http://getbootstrap.com/components/#alerts)
     * for the supported HTML attributes.
     */
    public $closeButton = [];
    /**
     * @var string the footer content in the modal component.
     */
    public $footer;
    /**
     * @var array the HTML attributes for the widget container tag.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $options = [];
    /**
     * @var string of cascading style sheet.
     */
    public $styles;
    /**
     * @var null|string tittle of modal window.
     */
    public $tittle;
    /**
     * @var boolean that render default button for shows modal window.
     */
    public $toggleButton = true;
    
    /**
     * Initializes the widget.
     */
    public function init(){
        parent::init();
        if(isset($this->options['staticMessage'])) {
            $this->staticMessage = $this->options['staticMessage'];
            unset($this->options['staticMessage']);
        }
        
        if(isset($this->tittle)){
            if(!is_string($this->tittle))
                throw new InvalidConfigException(\yii::t('ATTRIBUTE_MUST_BE_A_STRING', ['attribute' => 'tittle']));
        }
        
        if($this->staticMessage || isset($this->body)) {
            if($this->toggleButton)
                echo '<label for="'.$this->id.'">'.\yii::t('app', 'SHOW_THE', ['paramater' => 'Modal']).'</label>';
            
            echo '<input id="'.$this->id.'" class="modal-toggler" type="checkbox" aria-hidden="true">';
            Html::addCssClass($this->options, ['modal-container']);
            echo Html::beginTag('section', $this->options) . "\n"; //begin overlay modal
                echo '<label for="'.$this->id.'" class="close-area"></label>';
                
                echo Html::beginTag('div', ['class' => 'modal-box']) . "\n"; //begin window modal
                    echo (isset($this->tittle) ? '<header><h3>'.$this->tittle.'</h3></header>' : '');
                    if (($closeButton = $this->closeButton) !== false) {
                        ArrayHelper::remove($closeButton, 'tag');
                        ArrayHelper::remove($closeButton, 'label');
                        ArrayHelper::remove($closeButton, 'type');
                        $closeButton = array_merge($closeButton, [ 'for' => $this->id ]);
                        Html::addCssClass($closeButton, ['close']);
                        echo Html::tag('label', '&times;', $closeButton). "\n";
                    }
                    echo Html::beginTag('div', ['class' => 'content']) . "\n";
        }
    }
    
     /**
     * Set $this->body variable to null, because widget showing message of html element.
     * in page
     * 
     * {@inheritdoc}
     */
    public static function begin($config = array()) {
        unset($config['body']);
        $config = \yii\helpers\ArrayHelper::merge($config, [
            'options' =>[
                'staticMessage' => true
            ]
        ]);
        
        return parent::begin($config);
    }
    
    /**
     * Renders the widget.
     */
    public function run(){
        if($this->staticMessage || isset($this->body)){
                        echo "\n" . $this->body . "\n";
                    echo "\n" . Html::endTag('div') . "\n";
                    if($this->footer !== false){
                        echo Html::beginTag('footer') . "\n";
                            echo $this->footer;
                        echo Html::endTag('footer') . "\n";
                    }
                echo Html::endTag('div') . "\n"; //end window modal
            echo Html::endTag('section') . "\n"; //end overlay modal
        }
        elseif(isset($this->body))
            echo self::widget([
                //'body' => $this->body,
                'closeButton' => $this->closeButton,
                'options' => $this->options,
                'tittle' => $this->tittle,
                'toggleButton' => $this->toggleButton
            ]);
        
        if($this->staticMessage || isset($this->body)){
            if($this->clickOuterModalCloseModal)
                $this->defaultStyles = str_replace('{close_any_where}', '.modal-container>.close-area{width: 100%;height: 100%;}', $this->defaultStyles);
            else
                $this->defaultStyles = str_replace('{close_any_where}', '', $this->defaultStyles);
            if(is_string($this->styles))
                $this->defaultStyles .= $this->styles;

            $this->getView()->registerCss($this->defaultStyles);
        }
    }
}