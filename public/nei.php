<?php
error_reporting(0);

header("Content-type:text/html;charset=utf8");

$api = array(
    '1' => array(
            'name' => '注册',
            'url' => 'http://www.app.com/api/userlogin/UserRegister',
            'data' => 'PhoneNumber=18571512321&validateCode=543161&PassWord=aaa222'
        ),
    '2' => array(
        'name' => '登陆',
        'url' => 'http://www.app.com/api/userlogin/UserLogin',
        'data' => 'PhoneNumber=18571512321&PassWord=p123456'
    ),
    '3' => array(
        'name' => '发送短信验证码',
        'url' => 'http://www.app.com/api/userlogin/sendSMS',
        'data' => 'PhoneNumber=18571512321'
    ),
    '4' => array(
        'name' => '发送验证码（测试）',
        'url' => 'http://www.app.com/api/userlogin/sendSMSCode',
        'data' => 'PhoneNumber=18571512321'
    ),
    '5' => array(
        'name' => '修改密码',
        'url' => 'http://www.app.com/api/userlogin/FindPassword',
        'data' => 'PhoneNumber=18571512321&validateCode=602871&Password=p123456'
    ),
    '6' => array(
        'name' => '提交新机械',
        'url' => 'http://www.app.com/api/publish/newsmechanicsAdd',
        'data' => 'producing_country=1&mechanical_id=4&brand_id=23&mechanical_model_id=2&province_id=9&city_id=112&company_name=中兴实业&price=678000&is_invoice=1&telphone=18571512321&member_list_id=2'
    ),
    '7' => array(
        'name' => '提交二手机械',
        'url' => 'http://www.app.com/api/publish/usedmechanicsAdd',
        'data' => 'producing_country=1&mechanical_id=4&brand_id=23&mechanical_model_id=2&province_id=9&city_id=112&company_name=中兴实业&price=678000&is_invoice=1&telphone=18571512321&tonnage=10&work_hours=1000&car_number=苏MM1000&manufacture_date=2017-07-26&member_list_id=2'
    ),
    '8' => array(
        'name' => '提交司机信息',
        'url' => 'http://www.app.com/api/publish/driverAdd',
        'data' => 'type=29&class_id=10&salary=3&driving_age=10&education_level=4&province_id=9&city_id=112&province=9&city=112&name=强哥&age=36&sex=1&nation=汉&telphone=18571512321&member_list_id=2'
    ),
    '9' => array(
        'name' => '提交租赁信息',
        'url' => 'http://www.app.com/api/publish/LeaseAdd',
        'data' => 'type=60&class_id=62&mechanical_id=4&brand_id=23&hours=100&manufacture_date=2017-07-27&car_num=京K154785&mechanical_model_id=2&province_id=9&city_id=112&name=强哥&nation=汉&telphone=18571512321&member_list_id=2'
    ),
    '10' => array(
        'name' => '提交融资按揭信息',
        'url' => 'http://www.app.com/api/publish/financingadd',
        'data' => 'type=1&company_name=朝阳轮胎&province_id=9&city_id=112&telphone=18571512321&member_list_id=2'
    ),
    '11' => array(
        'name' => '提交机械配件信息',
        'url' => 'http://www.app.com/api/publish/certificatesAdd',
        'data' => 'car_type=48&parts_tid=75&username=大运摩托配件&service_tid=128&province_id=9&city_id=112&phone=18571512321&member_list_id=2'
    ),
    '12' => array(
        'name' => '提交修理厂信息',
        'url' => 'http://www.app.com/api/publish/repairAdd',
        'data' => 'type=1&class_id=5，6&name=大运摩托配件&service_id=0&province_id=9&city_id=112&shop_img_url=/data/uploads/20170718/3fbb746e07aaae9d4902ffeb6b603988.jpg&telphone=18571512321&member_list_id=2'
    ),
    '13' => array(
        'name' => '提交代办证书信息',
        'url' => 'http://www.app.com/api/publish/cardsAdd',
        'data' => 'type=61&certificate_level=99,104&school=新东方&province_id=9&city_id=112&phone=18571512321&member_list_id=2'
    ),
    '14'=>[
        'name'=>'修改用户名',
        'url'=>'http://www.app.com/api/Usercenter/editName',
        'data' =>'nickname=jeck'
    ],
    '15'=>[
        'name'=>'修改密码',
        'url'=>'http://www.app.com/api/Usercenter/editPassword',
        'data' =>'oldPassword=p123456&newPassword=p1234567'
    ],
    '16'=>[
        'name'=>'发布新机械',
        'url'=>'http://www.app.com/api/publish/CreateNewsmechanics',
        'data' =>''
    ],
    '17'=>[
        'name'=>'发布二手机械',
        'url'=>'http://www.app.com/api/publish/Createusedmechanics',
        'data' =>''
    ],
     '18'=>[
            'name'=>'获取首页广告',
            'url'=>'http://admin.app.com/api/ads/banner',
            'data' => 'type=1'
        ],
    '19'=>[
        'name'=>'发布修理厂信息',
        'url'=>'http://www.app.com/api/publish/CreateRepair',
        'data' =>''
    ],
     '20'=>[
        'name'=>'发布配件信息',
        'url'=>'http://www.app.com/api/publish/CreatCertificates',
        'data' =>''
    ],
    '21'=>[
        'name'=>'新机械列表',
        'url'=>'http://www.jxie.com/api/Mechanical/newsMechanicsList',
        'data' =>'brand_name=徐工&province_id=3&city=46&producing_country=160&page=1'
    ],
    '22'=>[
        'name'=>'二手机械列表',
        'url'=>'http://www.jxie.com/api/Mechanical/oldMechanicsList',
        'data' =>'brand_name=徐工&manufacture_date=2017年&province_id=3&city=46&price=3万以下&work_hours=100-200小时&product_country=1&page=1'
    ],
    '23'=>[
        'name'=>'机械详情',
        'url'=>'http://www.jxie.com/api/Mechanical/getMechanicsDetail',
        'data' =>'mechanical_id=20'
    ],
    '24'=>[
        'name'=>'获取品牌列表',
        'url'=>'http://www.jxie.com/api/Mechanical/getCarBrand',
        'data' =>''
    ],
    '25'=>[
        'name'=>'获取省份列表',
        'url'=>'http://www.jxie.com/api/Mechanical/getProvince',
        'data' =>''
    ],
    '26'=>[
        'name'=>'获取价格列表',
        'url'=>'http://www.jxie.com/api/Mechanical/getPriceList',
        'data' =>'type=2&price=价格区间'
    ],
    '27'=>[
        'name'=>'获取吨位列表',
        'url'=>'http://www.jxie.com/api/Mechanical/getTonnageList',
        'data' =>'type=2&tonnage=吨位'
    ],
    '28'=>[
        'name'=>'机械类型列表',
        'url'=>'http://www.jxie.com/api/Mechanical/getModelsList',
        'data' =>'type=1'
    ],
    '29'=>[
        'name'=>'机型列表',
        'url'=>'http://www.jxie.com/api/Mechanical/getModelTypeList',
        'data' =>'mechanical_id=8&brand_id=29'
    ],
    '30'=>[
        'name'=>'方数列表',
        'url'=>'http://www.jxie.com/api/Mechanical/getSquaresList',
        'data' =>''
    ],
    '31'=>[
        'name'=>'租赁列表--工程翻斗车',
        'url'=>'http://www.jxie.com/api/Mechanical/rentTrunckList',
        'data' =>'squares=3-5方&mechanical_id=1&province_id=3&city=46&page=1'
    ],
    '32'=>[
        'name'=>'租赁列表--机械拖板车',
        'url'=>'http://www.jxie.com/api/Mechanical/rentMechanicalList',
        'data' =>'page=1'
    ],
    '33'=>[
        'name'=>'租赁列表--工程机械车',
        'url'=>'http://www.jxie.com/api/Mechanical/rentMachineryList',
        'data' =>'province_id=3&city=46&brand_id=1&mechanical_model_id=2&work_hours=3-5小时&manufacture_date=2017&page=1'
    ],
    '34'=>[
        'name'=>'修理厂列表',
        'url'=>'http://www.jxie.com/api/Repair/repairList',
        'data' =>'up_server=128&type=211&province_id=3&city=46&page=1'
    ],
    '35'=>[
        'name'=>'工种类型列表',
        'url'=>'http://www.jxie.com/api/Repair/workTypeList',
        'data' =>''
    ],
    '36'=>[
        'name'=>'服务类型列表',
        'url'=>'http://www.jxie.com/api/Repair/serviceTypeList',
        'data' =>''
    ],
    '37'=>[
        'name'=>'修理厂详情页',
        'url'=>'http://www.jxie.com/api/Repair/repairDetail',
        'data' =>'repair_id=9'
    ],
    '38'=>[
        'name'=>'城市列表',
        'url'=>'http://www.jxie.com/api/Mechanical/getCity',
        'data' =>'parent_id=9'
    ],
    '39'=>[
        'name'=>'生产国列表',
        'url'=>'http://www.jxie.com/api/Mechanical/getProductCountry',
        'data' =>''
    ],
    '86'=>[
        'name'=>'获取司机列表',
        'url'=>'http://www.app.com/api/Search/DriverList',
        'data' =>'id=23'
    ],
    '87'=>[
        'name'=>'获取司机详情',
        'url'=>'http://www.app.com/api/Search/Driver',
        'data' =>'id=23'
    ],
    '88'=>[
        'name'=>'获取性别列表',
        'url'=>'http://www.app.com/api/Search/SexList',
        'data' =>''
    ],
    '89'=>[
        'name'=>'获取年龄列表',
        'url'=>'http://www.app.com/api/Search/AgeList',
        'data' =>''
    ],
     '90'=>[
        'name'=>'获取司机证件类型列表',
        'url'=>'http://www.app.com/api/Search/DriverClassList',
        'data' =>''
    ],
    '91'=>[
        'name'=>'获取司机类型列表',
        'url'=>'http://www.app.com/api/Search/DriverTypeList',
        'data' =>''
    ],
    '92'=>[
        'name'=>'获取薪资列表',
        'url'=>'http://www.app.com/api/Search/SalaryList',
        'data' =>''
    ],
    '93'=>[
        'name'=>'获取驾龄列表',
        'url'=>'http://www.app.com/api/Search/DrivingAgeList',
        'data' =>''
    ],
    '94'=>[
        'name'=>'获取教育经历列表',
        'url'=>'http://www.app.com/api/Search/EducationLevelList',
        'data' =>''
    ],
    '95'=>[
        'name'=>'获取民族列表',
        'url'=>'http://www.app.com/api/Search/NationList',
        'data' =>''
    ],
    '40'=>[
        'name'=>'融资按揭列表',
        'url'=>'http://www.jxie.com/api/Financing/financingList',
        'data' =>'financing_type =1&province_id=9&city_id=112'
    ],
    '41'=>[
        'name'=>'融资按揭详情',
        'url'=>'http://www.jxie.com/api/Financing/financingDetail',
        'data' =>'financing_id=58'
    ],
    '42'=>[
        'name'=>'融资种类列表',
        'url'=>'http://www.jxie.com/api/Financing/financingTypeList',
        'data' =>''
    ],
    '43'=>[
        'name'=>'新机械修改',
        'url'=>'http://www.jxie.com/api/Updatepublish/getNewsMechanics',
        'data' =>'mechanics_id=20'
    ],
    '44'=>[
        'name'=>'新机械修改提交',
        'url'=>'http://www.jxie.com/api/Updatepublish/upNewsMechanics',
        'data' =>'producing_country_id=162&mechanical_id=4&brand_id=23&mechanical_model_id=2&province_id=9&city_id=112&company_name=中兴实业&is_invoice=1&is_certificate=1&telphone=18571512321&linkman=中兴实业公司&id=2'
    ],
    '45'=>[
        'name'=>'二手机械修改',
        'url'=>'http://www.jxie.com/api/Updatepublish/getOldMechanical',
        'data' =>'mechanics_id=22'
    ],
    '46'=>[
        'name'=>'二手机械修改提交',
        'url'=>'http://www.jxie.com/api/Updatepublish/upOldMechanics',
        'data' =>'producing_country_id=160&mechanical_id=4&brand_id=23&mechanical_model_id=2&province_id=9&city_id=112&company_name=中兴实业&price=678000&is_invoice=1&telphone=18571512321&linkman=实业集团&work_hours=1000&manufacture_date=2017-8-4&id=22'
    ],


    '96'=>[
        'name'=>'获取配件详情',
        'url'=>'http://www.app.com/api/Search/GetPart',
        'data' =>'parts_id=57'
    ],
    '97'=>[
        'name'=>'获取车辆种类列表',
        'url'=>'http://www.app.com/api/Search/GetCarList',
        'data' =>''
    ],
    '98'=>[
        'name'=>'服务类型列表',
        'url'=>'http://www.app.com/api/Search/GetServiceList',
        'data' =>''
    ],
    '99'=>[
        'name'=>'配件种类列表',
        'url'=>'http://www.app.com/api/Search/GetPartsList',
        'data' =>''
    ],
    '100'=>[
        'name'=>'获取配件搜索列表',
        'url'=>'http://www.app.com/api/Search/SearchPart',
        'data' =>'car_tid=202&province_id=9&city_id=112&parts_tid=78&service_tid=78'
    ],

    );

if($_POST)
{

    $num = $_POST['num'] != '' ? intval($_POST['num']) : '';


    if($num != '')
    {

        $result = curl_post($api[$num]['url'],$api[$num]['data']);
    }
}
function curl_post($url,$data)
{
        $ch = curl_init();
        $header = "Accept-Charset: utf-8";
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, "1.txt");
        curl_setopt($ch, CURLOPT_COOKIEFILE, "1.txt");
        $tmpInfo = curl_exec($ch);
        $errorno=curl_errno($ch);
        return $tmpInfo;
}
?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="#">接口文档下载</a><br>
<br>
<form action="" method="post">
接口
<select name="num">
    <option value="">选择接口</option>
    <?php
        foreach ($api as $key => $value) {
            if($key === $num)
            {
                echo '<option value="'.$key.'" selected="selected">'.$value['name'].'</option>';
            }
            else
            {
                echo '<option value="'.$key.'">'.$value['name'].'</option>';
            }
        }
    ?>
</select>&nbsp;<input type="submit" value="确定" /><br />
</form>

入参参数：
<div id="info" style="width: 90%; color: red; line-height: 50px; padding-left: 10px; overflow: auto; height:100px; background-color:aliceblue; border: solid 1px #999;">
<?php 
    if($num != '')
    {
        echo $api[$num]['data'];
    }
?>
</div>
<br>

出参参数：
<div  style="width: 100%; padding:20 20 20 20; overflow: auto; height:800px; background-color:aliceblue; border: solid 1px #999;">

    <?php 
        if($num != ''){
            
              echo $result;exit;
              var_dump($result);exit;
            $r_a = json_decode($result,true);
            if(is_array($r_a))
            {
                echo '<pre>';print_r($r_a);echo '</pre>';
            }
            // else
            // {
            //     echo $result;
            // }
        }

    ?>

</div>
