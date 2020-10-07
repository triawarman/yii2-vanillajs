<?php
/**
 * @author Triawarman <3awarman@gmail.com>
 * @license MIT
 */
namespace triawarman\yii2VanillaJs;

use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * This code base are from yii\bootstrap\Alert and app\widget\Alert.
 * 
 * Alert renders an alert html-component.
 *
 * For example,
 *
 * ```php
 * echo Alert::widget([
 *     'options' => [
 *         'class' => 'alert-info',
 *     ],
 *     'body' => 'Say hello...',
 * ]);
 * ```
 *
 * The following example will show the content enclosed between the [[begin()]]
 * and [[end()]] calls within the alert box:
 *
 * ```php
 * Alert::begin([
 *     'options' => [
 *         'class' => 'alert-warning',
 *     ],
 * ]);
 *
 * echo 'Say hello...';
 *
 * Alert::end();
 * ```
 * 
 * Alert can renders a message from session flash. All flash messages are displayed
 * in the sequence they were assigned using setFlash. You can set message as following:
 * 
 * ```php
 * Yii::$app->session->setFlash('error', 'This is the message');
 * Yii::$app->session->setFlash('success', 'This is the message');
 * Yii::$app->session->setFlash('info', 'This is the message');
 * ```
 *
 * Multiple messages could be set as follows:
 *
 * ```php
 * Yii::$app->session->setFlash('error', ['Error 1', 'Error 2']);
 * ```
 * 
 * To render these message, just write a simple 
 * 
 * ```php
 * echo Alert::widget();
 * ```
 * 
 * or you can add some options array.
 * 
 */
class Alert extends \yii\base\Widget{
    const ALERT_TYPE_ERROR = 'error';
    const ALERT_TYPE_DANGER = 'danger';
    const ALERT_TYPE_SUCCESS = 'success';
    const ALERT_TYPE_INFO = 'info';
    const ALERT_TYPE_WARNING = 'warning';
    
    
    /**
    * @var boolean indicate that Alert use html element to showing message.
    */
    private $staticMessage = false;
    /**
     * @var string of javascript that handle Alert close button.
     */
    private $script = (YII_ENV_DEV ? 'var e = document.querySelectorAll("{$class}>button{$closeButtonClass}");
e.forEach(el => el.addEventListener("click", event => {
    //console.log(event.target);
    event.target.parentElement.remove();
}));' : 'var e = document.querySelectorAll("{$class}>button{$closeButtonClass}"); e.forEach(el => el.addEventListener("click", event => {event.target.parentElement.remove();}));');
    /**
     * @var array the alert types configuration for the flash messages.
     * This array is setup as $key => $value, where:
     * - key: the name of the session flash variable
     * - value: the css class that will applied to alert html-component.
     */
    public $alertStyles = [
        self::ALERT_TYPE_ERROR   => 'alert-danger',
        self::ALERT_TYPE_DANGER  => 'alert-danger',
        self::ALERT_TYPE_SUCCESS => 'alert-success',
        self::ALERT_TYPE_INFO    => 'alert-info',
        self::ALERT_TYPE_WARNING => 'alert-warning'
    ];
    /**
     * @var string the body content in the alert component. Note that anything between
     * the [[begin()]] and [[end()]] calls of the Alert widget will also be treated
     * as the body content, and will be rendered before this.
     */
    public $body;
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
     * @var array the HTML attributes for the widget container tag.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $options = [];
    
    /**
     * Initializes the widget.
     */
    public function init(){
        parent::init();
        
        if(isset($this->options['yvjsScript']))
            if($this->options['yvjsScript'] == false){
                unset($this->options['yvjsScript']);
                $this->script = null;
            }

        if(isset($this->options['staticMessage'])){
            $this->staticMessage = $this->options['staticMessage'];
            unset($this->options['staticMessage']);
        }
        
        if($this->staticMessage || isset($this->body)){
            Html::addCssClass($this->options, ['alert']);
            echo Html::beginTag('div', $this->options) . "\n";
            
            Html::addCssClass($this->closeButton, 'close');
            if (($options = $this->closeButton) !== false) {
                $tag = ArrayHelper::remove($options, 'tag', 'button');
                $label = ArrayHelper::remove($options, 'label', '&times;');
                if ($tag === 'button' && !isset($options['type']))
                    $options['type'] = 'button';
                
                echo Html::tag($tag, $label, $options). "\n";
            }
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
            echo "\n" . Html::endTag('div');
        }
        elseif(!isset($this->body)){
            $session = \yii::$app->session;
            $flashes = $session->getAllFlashes();
            $appendClass = isset($this->options['class']) ? ' ' . $this->options['class'] : '';
            
            foreach ($flashes as $type => $flash) {
                if (!isset($this->alertStyles[$type]))
                    continue;

                foreach ((array) $flash as $i => $message) {
                    echo self::widget([
                        'body' => $message,
                        'closeButton' => $this->closeButton,
                        'options' => array_merge($this->options, [
                            'id' => $this->getId() . '-' . $type . '-' . $i,
                            'class' => $this->alertStyles[$type] . $appendClass,
                            'yvjsScript' => false 
                        ]),
                    ]);
                }

                $session->removeFlash($type);
            }
        }
        
        if(!is_null($this->script)){
            if(!isset($this->options['class']))
                $class = '.alert';
            else{
                if(is_string($this->options['class']))
                    $class = '.'.str_replace(' ', '.', $this->options['class']);
                else
                    $class = '.'.implode('.', $this->options['class']);
            }
            
            if(!isset($this->closeButton['class']))
                $closeButtonClass = '.close';
            else
                $closeButtonClass = '.'.str_replace(' ', '.', $this->closeButton['class']);
            
            $this->script = str_replace('{$class}', $class, $this->script);
            $this->script = str_replace('{$closeButtonClass}', $closeButtonClass, $this->script);
            $this->getView()->registerJs($this->script, $this->scriptPosition);
        }
    }
}