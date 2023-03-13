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
 * Notice renders an notice html-component.
 *
 * For example,
 *
 * ```php
 * echo Notice::widget([
 *     'options' => [
 *         'class' => 'notice notice-info',
 *     ],
 *     'body' => 'Say hello...',
 *     //or you can just write
 *     'body' => \yii::$app->session->getFlash('postDeleted'),
 *      //or
 *      'body' => ['message1', 'message2']
 * ]);
 * ```
 *
 * The following example will show the content enclosed between the [[begin()]]
 * and [[end()]] calls within the notice box:
 *
 * ```php
 * Notice::begin([
 *     'options' => [
 *         'class' => 'notice notice-warning',
 *     ],
 * ]);
 *
 * echo 'Say hello...';
 *
 * Notice::end();
 * ```
 * 
 * Notice can renders a message from session flash. All flash messages are displayed
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
 * echo Notice::widget();
 * ```
 * 
 * or you can add some options array.
 * 
 */
class Notice extends \yii\base\Widget {
    const NOTICE_TYPE_INFO = 'info';
    const NOTICE_TYPE_SUCCESS = 'success';
    const NOTICE_TYPE_CAUTION = 'caution';
    const NOTICE_TYPE_WARNING = 'warning';
    const NOTICE_TYPE_ERROR = 'error';
    const NOTICE_TYPE_DANGER = 'danger';
    
    /**
    * @var boolean indicate that Notice use html element to showing message.
    */
    private $staticMessage = false;
    /**
     * @var string of javascript that handle Notice close button.
     */
    private $script = (YII_ENV_DEV ? 'var e = document.querySelectorAll("{$class}>button{$closeButtonClass}");
e.forEach(el => el.addEventListener("click", event => {
    //console.log(event.target);
    event.target.parentElement.remove();
}));' : 'var e = document.querySelectorAll("{$class}>button{$closeButtonClass}"); e.forEach(el => el.addEventListener("click", event => {event.target.parentElement.remove();}));');
    /**
     * @var string css class for notice container
     */
    public $noticeClass = 'notice';
    /**
     * @var array the notice types configuration for the flash messages.
     * This array is setup as $key => $value, where:
     * - key: the name of the session flash variable
     * - value: the css class that will applied to notice html-component.
     */
    public $alertStyles = [
        self::NOTICE_TYPE_SUCCESS => 'success',
        self::NOTICE_TYPE_INFO    => 'info',
        self::NOTICE_TYPE_CAUTION => 'caution',
        self::NOTICE_TYPE_WARNING => 'warning',
        self::NOTICE_TYPE_ERROR   => 'error',
        self::NOTICE_TYPE_DANGER  => 'danger',
    ];
    /**
     * @var boolean default true if you want to combine these both values that will result to "notice-success".
     */
    public $combineNoticeClassWithAlertStyles = true;
    /**
     * @var string the body content in the notice component. Note that anything between
     * the [[begin()]] and [[end()]] calls of the Notice widget will also be treated
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
    public function init() {
        parent::init();
        
        $type = ($this->options['type'] ?? null);
        
        if(!empty($this->options['yvjsScript']))
            if($this->options['yvjsScript'] == false) {
                unset($this->options['yvjsScript']);
                $this->script = null;
            }
            
        if(!empty($this->options['staticMessage'])) {
            $this->staticMessage = $this->options['staticMessage'];
            unset($this->options['staticMessage']);
        }
        
        if(!empty($this->noticeClass))
            Html::addCssClass($this->options, [$this->noticeClass]);

        if($this->staticMessage || !empty($this->body)) {
            $class = [];
            if(!empty($this->options['class']) && is_array($this->options['class']))
                $class = array_merge($class, $this->options['class']);
            elseif(!empty($this->options['class']) && is_string($this->options['class']))
                $class = array_merge($class, explode (' ', $this->options['class']));
        
            if(!empty($this->alertStyles[$type])) {
                if(!empty($this->noticeClass))
                    $class[] = ($this->combineNoticeClassWithAlertStyles ? ($this->noticeClass.'-'.$this->alertStyles[$type]) : $this->alertStyles[$type]);
                else
                    $class[] = $this->alertStyles[$type];
            }

            $this->options['class'] = $class;
            $this->options['id'] = (!empty($this->noticeClass) ? ($this->noticeClass.'-') : '').$this->getId();
            
            echo Html::beginTag('div', $this->options) . "\n"; 
            if (($options = $this->closeButton) !== false) {
                Html::addCssClass($options, 'close');
                $tag = ArrayHelper::remove($options, 'tag', 'button');
                $label = ArrayHelper::remove($options, 'label', '&times;');
                if ($tag === 'button' && empty($options['type']))
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
    public function run() {
        if($this->staticMessage || !empty($this->body)) {
            echo "\n" . $this->body . "\n";
            echo "\n" . Html::endTag('div');
        }
        elseif(empty($this->body)) {
            $session = \yii::$app->session;
            $flashes = $session->getAllFlashes();
            
            foreach ($flashes as $type => $flash) {
                foreach ((array) $flash as $i => $messages) {
                    if(is_string($messages))
                        echo self::widget([
                            'noticeClass' => $this->noticeClass,
                            'body' => $messages,
                            'closeButton' => $this->closeButton,
                            'options' => array_merge($this->options, [
                                //'id' => $id,
                                //'class' => $class,
                                'type' => $type,
                                'yvjsScript' => false 
                            ]),
                        ]);
                    else {
                        foreach ($messages as $message)
                            echo self::widget([
                                'noticeClass' => $this->noticeClass,
                                'body' => $message,
                                'closeButton' => $this->closeButton,
                                'options' => array_merge($this->options, [
                                    //'id' => $id,
                                    //'class' => $class,
                                    'type' => $type,
                                    'yvjsScript' => false 
                                ]),
                            ]);
                    }
                }

                $session->removeFlash($type);
            }
        }
        
        if(!empty($this->script)) {
            if(!empty($this->noticeClass))
                $class = '.'.$this->noticeClass;
            elseif(!empty($this->options['class'])) {
                if(is_string($this->options['class']))
                    $class = '.'.str_replace(' ', '.', $this->options['class']);
                else
                    $class = '.'.implode('.', $this->options['class']);
            }
            
            if(empty($this->closeButton['class']))
                $closeButtonClass = '.close';
            else
                $closeButtonClass = '.'.str_replace(' ', '.', $this->closeButton['class']);
            
            $this->script = str_replace('{$class}', $class, $this->script);
            $this->script = str_replace('{$closeButtonClass}', $closeButtonClass, $this->script);
            $this->getView()->registerJs($this->script, $this->scriptPosition);
        }
    }
}
