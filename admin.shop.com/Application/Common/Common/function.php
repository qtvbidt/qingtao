<?php

/**
 * 将模型的错误信息转换成一个有序列表。
 * @param \Think\Model $model 模型对象
 * @return string
 */
function get_error(\Think\Model $model){
    $errors = $model->getError();
    if(!is_array($errors)){
        $errors = [$errors];
    }
    
    $html = '<ol>';
    foreach($errors as $error){
        $html .= '<li>' . $error . '</li>';
    }
    $html .= '</ol>';
    return $html;
    
}

/**
 * 将一个关联数组转换成下拉列表
 * @param array  $data        关联数组,二维数组.
 * @param string $name_field  提示文本的字段名.
 * @param string $value_field value数据的字段名.
 * @param string $name        表单控件的name属性.
 * @return string 下拉列表的html代码.
 */
function arr2select(array $data, $name_field = 'name', $value_field = 'id', $name = '',$default_value='') {
    $html = '<select name="' . $name . '" class="' . $name . '">';
    $html .= '<option value=""> 请选择 </option>';
    foreach ($data as $key => $value) {
        if($value[$value_field] == $default_value){
            $html .= '<option value="' . $value[$value_field] . '" selected="selected">' . $value[$name_field] . '</option>';
        }else{
            $html .= '<option value="' . $value[$value_field] . '">' . $value[$name_field] . '</option>';
        }
    }
    $html .= '</select>';
    return $html;
}



/**
 * 加盐加密
 * @param string $password 原密码
 * @param string $salt     盐
 */
function salt_mcrypt($password,$salt){
    return md5(md5($password).$salt);
}

/**
 * 获取和设置用户session
 * @param mixed $data
 * @return type
 */
function login($data=null){
    if(is_null($data)){
        return session('USERINFO');
    }else{
        session('USERINFO',$data);
    }
}

/**
 * 获取和设置用户权限session
 * @param mixed $data
 * @return type
 */
function permission_pathes($data=null){
    if(is_null($data)){
        $pathes = session('PERMISSION_PATHES');
        if(!is_array($pathes)){
            $pathes = [];
        }
        return $pathes;
    }else{
        session('PERMISSION_PATHES',$data);
    }
}
/**
 * 获取和设置用户权限ID session
 * @param mixed $data
 * @return type
 */
function permission_pids($data=null){
    if(is_null($data)){
        $pids = session('PERMISSION_PIDS');
        if(!is_array($pids)){
            $pids = [];
        }
        return $pids;
    }else{
        session('PERMISSION_PIDS',$data);
    }
}
