<?php
//Deny direct access
defined("_LOAD") or die("Access denied");

/**
 * @param array $attributes
 * @return string
 */
function attribute($attributes){
    $attr_str = '';
    unset($attributes['class']);
    if (sizeof($attributes)){
        foreach($attributes as $key=>$val){
            if(is_bool($val)){
                if($val === true){
                    $attr_str .= "{$key} ";
                }
                continue;
            }
            $attr_str .= "{$key}=\"{$val}\" ";
        }
    }
    return $attr_str;
}

class Form {
    
    //Form elements
    static $element = array();
    static $hidden = array();
    
    static function Open($action = '', $attributes = array()) {
        global $config;
        $config['csrf_protection'] = TRUE;
        
        $attributes = attribute($attributes);

        if (stripos($attributes, 'method=') === FALSE) {
            $attributes .= ' method="post"';
        }

        if (stripos($attributes, 'accept-charset=') === FALSE) {
            $attributes .= ' accept-charset="utf-8"';
        }

        if (stripos($attributes, 'enctype=') !== FALSE) {
            $attributes .= ' enctype="multipart/form-data"';
        }

        if(empty($action)) $action = FUSION_REQUEST;

        $form = '<form action="'.$action.'"'.$attributes.">\n";
        // Add CSRF field if enabled, but leave it out for GET requests and requests to external websites
        if ($config['csrf_protection'] === TRUE && !stripos($form, 'method="get"')) {

            $tok = Utils::GenerateToken();
            $tok = explode(":",$tok);
            
            $form .= "<input name='ctok' type='hidden' value='".$tok[0]."' />\n";
            $form .= "<input name='ftok' type='hidden' value='".$tok[1]."' />\n";
        }

        return $form;
    }
    
    function FieldsetStart($legend=false) {
        if ($this->infieldset==true)
        $this->AddElement(array('element'   =>'fieldset_end'));
        
        $this->AddElement(array('element'   =>'fieldset_start',
                                'legend'    =>$legend));
        $this->infieldset = true;
    }
    
    function FieldsetEnd() {
        if ($this->infieldset==true) {
            $this->AddElement(array('element'   =>'fieldset_end'));
            $this->infieldset = false;
        }
    }
    
    static function Hidden($label,$value) {
        return "<input name='$label' id='$label' type='hidden' value='".str_replace("'","&#039;",$value)."' />\n";
    }
    
    static function AddElement() {
        global $config;
        
        $args = func_get_args();
        $args = $args[0];
        $html = "";
        switch ($args['element']) {
            case "fieldset_start":
                $html .= "<fieldset class='sys_form_fieldset'>\n";
                if (!empty($args['legend'])) {
                    $html .= "<legend class='sys_form_legend'>".$args['legend']."</legend>\n";
                }
                break;
            case "fieldset_end":
                $html .= "</fieldset>\n";
                break;
            case "text":
                $html .= "<div class='form-group'>\n";
                    if (isset($args['label'])) $html .= "<label>".$args['label']."</label>\n";
                    if (isset($args['prefix'])) $html .= $args['prefix'];
                    $html .= "<input type=";
                            $html .= (isset($args['password'])) ? "'password'" : "'text'" ;
                        if (isset($args['name'])) $html .= " name='".$args['name']."'";
                        if (isset($args['id'])) $html .= " id='".$args['id']."'";
                        if (isset($args['value'])) $html .= " value='".str_replace("'","&#039;",$args['value'])."'";
                        if (isset($args['disabled']) && $args['disabled']) $html .= " disabled='disabled'";
                        $html .= " style='margin:2px 0; width:";
                            $html .= (isset($args['width'])) ? $args['width'].";" : "100%;" ;
                            if (isset($args['style'])) $html .= " ".$args['style'];
                            $html .= "'";
                        $required = isset($args['required']) ? " sys_form_required" : "" ;
                        if (isset($args['class'])) { $html .= " class='".$args['class']."{$required}'"; } else { $html .= " class='form-control{$required}'"; }
                        if (isset($args['extra'])) $html .= " ".$args['extra'];
                        if (isset($args['placeholder'])) $html .= " placeholder=".$args['placeholder'];
                        if (isset($args['readonly'])) $html .= " readonly='readonly'";
                    $html .= " />\n";
                    if (isset($args['suffix'])) $html .= $args['suffix'];
                    if (isset($args['info'])) $html .= "<span class='sys_form_info'>".$args['info']."</span>\n";
                $html .= "</div>\n";
                break;
            case "textarea":
                $html .= "<div class='form-group'>\n";
                    if (isset($args['label'])) $html .= "<label>".$args['label']."</label>\n";
                    $html .= "<textarea";
                        if (isset($args['name'])) $html .= " name='".$args['name']."'";
                        if (isset($args['id'])) $html .= " id='".$args['id']."'";
                        $required = isset($args['required']) ? " sys_form_required" : "" ;
                        $html .= " class='";
                            $html .= (isset($args['class'])) ? $args['class'] : "advanced" ;
                            $html .= "{$required}'";
                        if (isset($args['extra'])) $html .= " ".$args['extra'];
                        if (isset($args['readonly'])) $html .= " readonly='readonly'";
                        if (isset($args['disabled']) && $args['disabled']) $html .= " disabled='disabled'";
                $html .= " cols='60' rows='10'";
                    $html .= " style='margin:2px 0; width:";
                        $html .= (isset($args['width'])) ? $args['width'] : "100%" ;
                        $html .= "; height:";
                        $html .= (isset($args['height'])) ? $args['height'].";" : "200px;" ;
                        if (isset($args['style'])) $html .= " ".$args['style'];
                        $html .= "'";
                    $html .= ">";
                        if (isset($args['value'])) $html .= str_replace("'","&#039;",$args['value']);
                    $html .= "</textarea>\n";
                    if (isset($args['info'])) $html .= "<span class='sys_form_info'>".$args['info']."</span>\n";
                $html .= "</div>\n";
                break;
            
            case "textarea_adv":
                $html .= "<script type='text/javascript' src='".INFUSIONS."media/inc/assets/ckeditor/ckeditor.js'></script>";      
                $html .= "<div class='form-group'>";
                    $html .= "<label for='text'>Content:</label>";
                    $html .= "<textarea";
                        if (isset($args['name'])) $html .= " name='".$args['name']."'";
                        $html .= (isset($args['id'])) ? " id='".$args['id']."'" : " id='editor'" ;
                        $required = isset($args['required']) ? " sys_form_required" : "" ;
                        $html .= (isset($args['class'])) ? " class='form-control ".$args['class']."" : " class='form-control editor" ;
                            $html .= "{$required}'";
                        if (isset($args['extra'])) $html .= " ".$args['extra'];
                        if (isset($args['readonly'])) $html .= " readonly='readonly'";
                        if (isset($args['disabled']) && $args['disabled']) $html .= " disabled='disabled'";
                        $html .= " cols='60' rows='10'";
                        $html .= " style='margin:2px 0; width:";
                        $html .= (isset($args['width'])) ? $args['width'] : "100%" ;
                        $html .= "; height:";
                        $html .= (isset($args['height'])) ? $args['height'].";" : "200px;" ;
                        if (isset($args['style'])) $html .= " ".$args['style'];
                        $html .= "'";
                        $html .= ">";
                        if (isset($args['value'])) $html .= str_replace("'","&#039;",$args['value']);
                    $html .= "</textarea>\n";
                $html .= "</div>";

                $html .= "<script>
                    var config = {
                        customConfig : '',
                        // Add the required plugin
                        extraPlugins : 'simpleuploads',
                        // Required config to tell CKEditor what's the script that will process uploads
                        filebrowserUploadUrl : '".INFUSIONS."media/inc/assets/ckeditor/upload.php?type=Files',
                        filebrowserImageUploadUrl : '".INFUSIONS."media/inc/assets/ckeditor/upload.php?type=Images',
                        toolbar :   // Sample toolbar
                        [
                        
                            
                            { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ], items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl' ] },
                            { name: 'tools',       items : [ 'Maximize' ] },
                            { name: 'document',    items : [ 'Source' ] },
                            { name: 'insert', items: [ 'Table', 'HorizontalRule', 'SpecialChar', 'PageBreak', 'Iframe', 'Syntaxhighlight' ] }, '/',
                            { name: 'styles', items: [ 'Format', 'Font', 'FontSize' ] },
                            { name: 'basicstyles', items : [ 'Bold', 'Italic', 'Underline' ] },
                            { name: 'insert',      items : [ 'Link', 'addFile', 'addImage' ] },
                        ],
                        height : '300px',
                        simpleuploads_acceptedExtensions : '7z|avi|csv|doc|docx|flv|gif|gz|gzip|jpeg|jpg|mov|mp3|mp4|mpc|mpeg|mpg|ods|odt|pdf|png|ppt|pxd|rar|rtf|tar|tgz|txt|vsd|wav|wma|wmv|xls|xml|zip'
                    };

                    CKEDITOR.replace( '".$args['id']."', config );
                </script>";
            break;
            case "select":
                $html .= "<div class='form-group'>\n";
                    if (isset($args['label'])) $html .= "<label>".$args['label']."</label>\n";
                    if (isset($args['prefix'])) $html .= $args['prefix'];
                    $html .= "<select" ;
                        if (isset($args['name'])) $html .= " name='".$args['name']."'";
                        if (isset($args['multiple'])) $html .= " multiple='multiple'";
                        if (isset($args['size'])) $html .= " size='".$args['size']."'";
                        if (isset($args['id'])) $html .= " id='".$args['id']."'";
                        if (isset($args['disabled']) && $args['disabled']) $html .= " disabled='disabled'";
                        $html .= " style='margin:2px 0; width:";
                            $html .= (isset($args['width'])) ? $args['width'].";" : "100%;" ;
                            if (isset($args['style'])) $html .= " ".$args['style'];
                            $html .= "'";
                        $required = isset($args['required']) ? " sys_form_required" : "" ;
                        if (isset($args['class'])) { $html .= " class='".$args['class']."{$required}'"; } else { $html .= " class='form-control select2{$required}'"; }
                        if (isset($args['extra'])) $html .= " ".$args['extra'];
                    $html .= ">\n";
                        //Options
                        if (isset($args['values'])) {
                            foreach ($args['values'] as $key => $value) {
                                //Selected
                                if (isset($args['selected'])) {
                                    if (is_array($args['selected'])) {
                                        $selected = (in_array($value,$args['selected'])) ? " selected='selected'" : "" ;
                                    } else {
                                        $selected = ($args['selected']==$value) ? " selected='selected'" : "" ;
                                    }
                                } else {
                                    $selected = "";
                                }
                                //Disabled
                                if (isset($args['optdisabled'])) {
                                    if (is_array($args['optdisabled'])) {
                                        $disabled = (in_array($value,$args['optdisabled'],true)) ? " disabled='disabled'" : "" ;
                                    } else {
                                        $disabled = ($args['optdisabled']==$value) ? " disabled='disabled'" : "" ;
                                    }
                                } else {
                                    $disabled = "";
                                }
                                $html .= "<option value='".str_replace("'","&#039;",$value)."'{$selected}{$disabled}>".str_replace("'","&#039;",$key)."</option>\n";
                            }
                        }
                    $html .= "</select>\n";
                    if (isset($args['suffix'])) $html .= $args['suffix'];
                    if (isset($args['info'])) $html .= "<span class='sys_form_info'>".$args['info']."</span>\n";
                $html .= "</div>\n";
                $html .= "<script type='text/javascript' src='".INFUSIONS."media/assets/js/select2/js/select2.min.js'></script>";
                $html .= "<link rel='stylesheet' href='".INFUSIONS."media/assets/js/select2/css/select2.min.css' type='text/css' media='screen' />";
                if (isset($args['id'])) {
                    $html .= '<script type="text/javascript">
                    $(document).ready(function() {
                        $("#'.$args['id'].'").select2();
                    });
                    </script>';
                }
                break;
            
            case "checkbox":
                $html .= "<div class='form-check form-check-inline'>\n";
                    if (isset($args['prefix'])) $html .= $args['prefix'];
                    if (isset($args['label'])) $html .= "<label class='form-check-label'>";
                    $html .= "<input type='checkbox'";
                        if (isset($args['name'])) $html .= " name='".$args['name']."'";
                        if (isset($args['id'])) $html .= " id='".$args['id']."'";
                        if (isset($args['value'])) $html .= " value='".str_replace("'","&#039;",$args['value'])."'";
                        if ($args['value'] == 1) $html .= " checked";
                        $html .= " style='margin:2px 0;";
                        if (isset($args['style'])) $html .= " ".$args['style'];
                        $html .= "'";
                        $required = isset($args['required']) ? " sys_form_required" : "" ;
                        if (isset($args['class'])) { 
                            $html .= " class='".$args['class']."{$required}'";
                        } else if ($required) {
                            $html .= " class='{$required}'";
                        } else {
                            $html .= " class='form-check-input'";
                        }
                        if (isset($args['extra'])) $html .= " ".$args['extra'];
                    $html .= " />";
                    if (isset($args['label'])) $html .= $args['label']."</label>";
                    if (isset($args['suffix'])) $html .= $args['suffix'];
                    if (isset($args['info'])) $html .= "<span class='sys_form_info'>".$args['info']."</span>\n";
                $html .= "</div>\n";
                break;
            
            case "radio":
                $html .= "<div class='form-group'>\n";
                    if (isset($args['prefix'])) $html .= $args['prefix'];
                    $html .= "<input type='radio'";
                        if (isset($args['name'])) $html .= " name='".$args['name']."'";
                        if (isset($args['id'])) $html .= " id='".$args['id']."'";
                        if (isset($args['value'])) $html .= " value='".str_replace("'","&#039;",$args['value'])."'";
                        if (isset($args['checked'])) $html .= " checked='checked'";
                        $html .= " style='margin:2px 0;";
                        if (isset($args['style'])) $html .= " ".$args['style'];
                        $html .= "'";
                        $required = isset($args['required']) ? " sys_form_required" : "" ;
                        if (isset($args['class'])) { 
                            $html .= " class='".$args['class']."{$required}'";
                        } else if ($required) {
                            $html .= " class='{$required}'";
                        }
                        if (isset($args['extra'])) $html .= " ".$args['extra'];
                    $html .= " />\n";
                    if (isset($args['label'])) $html .= "<span class='sys_form_label_inline'><label>".$args['label']."</label></span>\n";
                    if (isset($args['suffix'])) $html .= $args['suffix'];
                    if (isset($args['info'])) $html .= "<span class='sys_form_info'>".$args['info']."</span>\n";
                $html .= "</div>\n";
                break;
            
            case "file":
                $html .= "<div class='form-group'>\n";
                    if (isset($args['label'])) $html .= "<label>".$args['label']."</label>\n";
                    if (isset($args['prefix'])) $html .= $args['prefix'];
                    $html .= "<input type='file'";
                        if (isset($args['name'])) $html .= " name='".$args['name']."'";
                        if (isset($args['id'])) $html .= " id='".$args['id']."'";
                        $html .= " size='";
                            $html .= (isset($args['size'])) ? $args['size'] : "40" ;
                            $html .= "'";
                        if (isset($args['disabled']) && $args['disabled']) $html .= " disabled='disabled'";
                        $html .= " style='margin:2px 0;";
                        if (isset($args['style'])) $html .= " ".$args['style'];
                        $html .= "'";
                        $required = isset($args['required']) ? " sys_form_required" : "" ;
                        if (isset($args['class'])) { 
                            $html .= " class='".$args['class']."{$required}'";
                        } else if ($required) {
                            $html .= " class='{$required}'";
                        }
                        if (isset($args['extra'])) $html .= " ".$args['extra'];
                        if (isset($args['multiple'])) $html .= " multiple='multiple'";
                    $html .= " />\n";
                    if (isset($args['suffix'])) $html .= $args['suffix'];
                    if (isset($args['info'])) $html .= "<span class='sys_form_info'>".$args['info']."</span>\n";
                $html .= "</div>\n";
                break;
                
            case "image":
                $html .= "<div class='form-group'>\n";
                    if (isset($args['label'])) $html .= "<label>".$args['label']."</label>\n";
                    if (isset($args['prefix'])) $html .= $args['prefix'];
                    $html .= "<input type='image'" ;
                        if (isset($args['name'])) $html .= " name='".$args['name']."'";
                        if (isset($args['id'])) $html .= " id='".$args['id']."'";
                        if (isset($args['alt'])) $html .= " alt='".$args['alt']."'";
                        $html .= " src='".$args['src']."'";
                        $html .= " style='margin:2px 0;";
                        if (isset($args['style'])) $html .= " ".$args['style'];
                        $html .= "'";
                        $required = isset($args['required']) ? " sys_form_required" : "" ;
                        if (isset($args['class'])) { 
                            $html .= " class='".$args['class']."{$required}'";
                        } else if ($required) {
                            $html .= " class='{$required}'";
                        }
                        if (isset($args['extra'])) $html .= " ".$args['extra'];
                    $html .= " />\n";
                    if (isset($args['suffix'])) $html .= $args['suffix'];
                    if (isset($args['info'])) $html .= "<span class='sys_form_info'>".$args['info']."</span>\n";
                $html .= "</div>\n";
                break;
                
            case "submit":
                $html = "";
                if (isset($args['captcha']) && $config_sys['captcha']==1) {
                    $html .= "<p";
                        if ($this->inline || isset($args['inline'])) {
                            $html .= "display:inline;";
                        } else if ($this->VSpacer) {
                            $html .= " style='margin:10px 0;";
                        }
                $html .= "'>\n";
                    Captcha::Display();
                    $html .= "</p>\n";
                }
                //$html .= "<div class='form-group'>\n";
                    //if (isset($args['label'])) $html .= "<label>".$args['label']."</label>\n";
                    if (isset($args['prefix'])) $html .= $args['prefix'];
                    $html .= "<input type='submit'" ;
                        if (isset($args['name'])) $html .= " name='".$args['name']."'";
                        if (isset($args['id'])) $html .= " id='".$args['id']."'";
                        if (isset($args['value'])) $html .= " value='".str_replace("'","&#039;",$args['value'])."'";
                        $html .= " style='margin:2px 0;";
                        if (isset($args['style'])) $html .= " ".$args['style'];
                        $html .= "' class='";
                            $html .= (isset($args['class'])) ? $args['class'] : "btn btn-default" ;
                            $html .= "'";
                        if (isset($args['extra'])) $html .= " ".$args['extra'];
                    $html .= " />\n";
                    if (isset($args['suffix'])) $html .= $args['suffix'];
                    if (isset($args['info'])) $html .= "<span class='sys_form_info'>".$args['info']."</span>\n";
                //$html .= "</div>\n";
                    return $html;
                break;
                
            case "reset":
                $html .= "<div class='form-group'>\n";
                    if (isset($args['label'])) $html .= "<label>".$args['label']."</label>\n";
                    if (isset($args['prefix'])) $html .= $args['prefix'];
                    $html .= "<input type='reset'" ;
                        if (isset($args['name'])) $html .= " name='".$args['name']."'";
                        if (isset($args['id'])) $html .= " id='".$args['id']."'";
                        if (isset($args['value'])) $html .= " value='".str_replace("'","&#039;",$args['value'])."'";
                        $html .= " style='margin:2px 0;";
                        if (isset($args['style'])) $html .= " ".$args['style'];
                        $html .= "' class='";
                            $html .= (isset($args['class'])) ? $args['class'] : "btn btn-default" ;
                            $html .= "'";
                        if (isset($args['extra'])) $html .= " ".$args['extra'];
                    $html .= " />\n";
                    if (isset($args['suffix'])) $html .= $args['suffix'];
                    if (isset($args['info'])) $html .= "<span class='sys_form_info'>".$args['info']."</span>\n";
                $html .= "</div>\n";
                break;
            
            case "submit_and_reset":
                $html .= "<div class='form-group'>\n";
                    //Submit
                    if (isset($args['prefix'])) $html .= $args['prefix'];
                    $html .= "<input type='submit'" ;
                        if (isset($args['s_name'])) $html .= " name='".$args['s_name']."'";
                        if (isset($args['s_id'])) $html .= " id='".$args['s_id']."'";
                        if (isset($args['s_value'])) $html .= " value='".str_replace("'","&#039;",$args['s_value'])."'";
                        $html .= " style='margin:2px 0;";
                        if (isset($args['s_style'])) $html .= " ".$args['s_style'];
                        $html .= "' class='";
                            $html .= (isset($args['s_class'])) ? $args['s_class'] : "btn btn-default" ;
                            $html .= "'";
                        if (isset($args['s_extra'])) $html .= " ".$args['s_extra'];
                    $html .= " /> &nbsp;";
                    //Reset
                    $html .= "<input type='submit'" ;
                        if (isset($args['r_name'])) $html .= " name='".$args['r_name']."'";
                        if (isset($args['r_id'])) $html .= " id='".$args['r_id']."'";
                        if (isset($args['r_value'])) $html .= " value='".str_replace("'","&#039;",$args['r_value'])."'";
                        $html .= " style='margin:2px 0;";
                        if (isset($args['r_style'])) $html .= " ".$args['r_style'];
                        $html .= "' class='";
                            $html .= (isset($args['r_class'])) ? $args['r_class'] : "btn btn-default" ;
                            $html .= "'";
                        if (isset($args['r_extra'])) $html .= " ".$args['r_extra'];
                    $html .= " />\n";
                $html .= "</div>\n";
                break;
            
            case "button":
                $html .= "<div class='form-group'>\n";
                    
                    if (isset($args['prefix'])) $html .= $args['prefix'];
                    $html .= "<button type='submit'" ;
                        if (isset($args['name'])) $html .= " name='".$args['name']."'";
                        if (isset($args['id'])) $html .= " id='".$args['id']."'";
                        if (isset($args['value'])) $html .= " value='".str_replace("'","&#039;",$args['value'])."'";
                        $html .= " style='margin:2px 0;";
                        if (isset($args['style'])) $html .= " ".$args['style'];
                        $html .= "' class='";
                            $html .= (isset($args['class'])) ? $args['class'] : "btn btn-default" ;
                            $html .= "'";
                        if (isset($args['extra'])) $html .= " ".$args['extra'];
                    $html .= " />";
                    if (isset($args['label'])) $html .= $args['label'];
                    $html .= "</button>\n";
                    if (isset($args['suffix'])) $html .= $args['suffix'];
                    if (isset($args['info'])) $html .= "<span class='sys_form_info'>".$args['info']."</span>\n";
                $html .= "</div>\n";
                break;
        }
        return $html;
    }
    
    static function Close() {
        return "</form>\n";
    }

    /**
     * Creates a new input element
     *
     * @param string $type       input type
     * @param string $name       input name
     * @param string $value      (optional) input value
     * @param array  $attributes (optional) input tag attributes
     *
     * @return string
     */
    public static function input($type, $name, $value = '', $attributes = []) {
        $attributes['type'] = $type;

        $attributes['name'] = $name;

        $attributes['class'] = 'form-control';

        if ($value) {
            $attributes['value'] = $value;
        }

        if(empty($attributes['placeholder'])) $label = "<label>".MB::ucfirst($name)."</label>";

        return $label.static::element('input', $attributes, $value);
    }

    /**
     * Creates a form element
     *
     * @param string $name       element name
     * @param string $content    element value or content
     * @param null   $attributes element attributes
     *
     * @return string
     */
    public static function element($name, $attributes = null, $content = '') {
        $short = [
            'img',
            'input',
            'br',
            'hr',
            'frame',
            'area',
            'base',
            'basefont',
            'col',
            'isindex',
            'link',
            'meta',
            'param'
        ];

        if (in_array($name, $short)) {
            if ($content) {
                $attributes['value'] = $content;
            }

            return '<' . $name . static::attributes($attributes) . '>';
        }

        return '<' . $name . static::attributes($attributes) . '>' . $content . '</' . $name . '>';
    }

    /**
     * Creates an HTML attribute string
     *
     * @param string|array $attributes list of attributes to include
     *
     * @return string
     */
    public static function attributes($attributes) {
        if (empty($attributes)) {
            return '';
        }

        if (is_string($attributes)) {
            return ' ' . $attributes;
        }

        foreach ($attributes as $key => $val) {
            $pairs[] = $key . '="' . $val . '"';
        }

        return ' '.implode(' ', $pairs);
    }
}

// public static function attributes($attributes){
//     $attr_str = '';
//     unset($attributes['class']);
//     if (sizeof($attributes)){
//         foreach($attributes as $key=>$val){
//             if(is_bool($val)){
//                 if($val === true){
//                     $attr_str .= "{$key} ";
//                 }
//                 continue;
//             }
//             $attr_str .= "{$key}=\"{$val}\" ";
//         }
//     }
//     return $attr_str;
// }

?>