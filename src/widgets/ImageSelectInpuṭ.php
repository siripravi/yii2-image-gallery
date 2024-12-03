<?php

namespace siripravi\gallery\widgets;

use yii;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\InputWidget;
use yii\bootstrap5\ActiveForm;
use siripravi\gallery\assets\ImageWidgetAsset;
use siripravi\gallery\models\Image;
use yii\base\ErrorException;
use yii\helpers\Json;

class ImageSelectInpuṭ extends InputWidget
{

    public $remoteClientOptions = [];
    public $url = "/gallery/default/";
    public $multiple = false;
    public $clientEvents = [];
    public $remote;

    public $label = 'upload';
    public function init()
    {
        // Manual setting element ID.
        if ($this->id) {
            $this->options['id'] = $this->id;
        }
        parent::init();
    }

    public function run()
    {
        $this->registerClientScript();
        $reference = Yii::$app->gallery->getSessionUploadKey();
        //echo $reference; die;

        $image = Image::findOne(['reference' => $reference]);
        if (!array_key_exists('class', $this->options)) {
            $this->options['class'] = 'form-control';
        }
        $imgId = ($image != null) ? $image->id : 0;
        $this->options = array_merge($this->options, ['readonly' => true]);
        $input         = Html::hiddenInput($this->name, $this->value, $this->options);
        if ($this->hasModel()) {
            $this->model->{$this->attribute} = $imgId;
            $input = Html::activeHiddenInput($this->model, $this->attribute, $this->options);
        }
        $url          = Url::to(['/gallery/default/create', 'id' => $this->model->{$this->attribute}, 'version' => 'medium']);

        $selectImgBtn = Html::a('Choose file', "", [
            'class' => 'image-select-edit rel', // 'btn iframe-btn btn-dark btn-sm',
            'type'  => 'button',
        ]);
        return $this->render('single', ['input' => $input, 'url' => $url, 'imgId' => $imgId]);
    }

    protected function registerClientScript()
    {
        //$clientOptions = $this->clientOptions;  //array_merge($this->clientOptions, $this->remoteClientOptions());
        // $clientOptions = Json::encode($this->clientOptions);
        $view = $this->getView();
        $id = isset($this->options['id']) ? $this->options['id'] : $this->getId();
        $asset = ImageWidgetAsset::register($view);
        $js = <<< JS
                $(document).on("click", ".delete", function (e) {
                    e.preventDefault();
                    $("#load").fadeIn();
                    var id = $(this).attr("id");
                    var string = "id=" + id;
                    var imageContainer = $("#img-" + id);
                    var pic_id;
                    $.ajax({
                    type: "POST",
                    url: "/gallery/default/remove-image",
                    data: string,
                    cache: false,
                    success: function () {
                        imageContainer.hide();
                        $("#ximg-" + id).show();
                        $("#" + id).hide();
                        $.pjax.reload({ container: "#gallery", async: false });
                    },
                    });
                    return false;
                });
                var lastForm = $('form').last();
                lastForm.hide();
                $(".image_select a")
                    .file()
                    .choose(function (e, input) {
                        console.log(input);                      
                        lastForm.attr("method","post");
                        lastForm.attr("enctype","multipart/form-data");
                        lastForm.attr("action","/gallery/default/upload-photo?multiple=0");
                        lastForm.innerHtml = "";
                        input.appendTo(lastForm);
                  
                    lastForm.ajaxSubmit({
                        beforeSubmit: function (formData, jqForm, options) {
                       // var theId = $(".image_select form");

                        var queryString = $.param(formData);
                      //  var formElement = formData[1]["pict"];
                        console.log(queryString);
                        pic_id = "0";//formElement;        
                        return true;
                        },
                        success: function (responseText) {
                            console.log(responseText);
                        var span = $("<span/>");
                        var btn = $("<i/>");
                        btn.attr("id", responseText);

                        btn.addClass("fa fa-minus-inverse");
                        var img = $("<img/>")
                            .bind("load", function (e) {
                            $(e.target).click(function () {
                                $(this).hide();
                            });
                            span.html(e.target);
                            img.closest("span").append(btn);
                            $("#add-file-" + pic_id).append(span);
                           // $("#ximg-" + parseInt(responseText) + " form").hide();
                            })
                            .attr("id", "img-" + responseText)
                            .attr(
                            "src",
                            "/gallery/default/create?id=" +
                                responseText.toString() +
                                "&version=small&key=" +
                                new Date().getTime()
                            );        
                           // $("#gallery").append(img);
                            console.log(img.source);
                            console.log("reloading...");
                        
                        $.pjax.reload({ container: "#gallery", async: false });        
                        },
                    });
                    });
                JS;
        $view->registerJs($js);

        if ($this->clientEvents) {
            $js = [];
            foreach ($this->clientEvents as $event => $callback) {
                if (!$callback instanceof JsExpression) {
                    $callback = new JsExpression($callback);
                }
                $js[] = "jQuery('#$id').on('$event', $callback);";
            }
            if (!empty($js)) {
                $js = implode("\n", $js);
                $view->registerJs($js);
            }
        }
    }
    protected function remoteClientOptions()
    {
        if ($this->remote) {
            $options = [
                'url' => Url::to($this->remote),
                'delay' => 500,
                'dataType' => 'json',
                'data' => new JsExpression('function (params) {
                    return {
                        q: params.term,
                    };
                }'),
                'processResults' => new JsExpression('function (data, params) {
                    return { results: data };
                }'),
                'cache' => true,
            ];

            return ['ajax' => array_merge($options, $this->remoteOptions)];
        }
        return [];
    }
}
