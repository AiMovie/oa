<?php
// +----------------------------------------------------------------------
// | 功能：租赁管理后台
// +----------------------------------------------------------------------
// | 作者：程龙飞
// +----------------------------------------------------------------------
// | 日期：2017-6-28
// +----------------------------------------------------------------------
namespace app\contract\Controller;

use app\common\controller\Auth;
use think\Db;
class Contractadmin extends Auth
{
    /**
     * 信息列表
     * @return mixed
     */
    public function index()
    {
        if ($_POST) {
            if (input('province_id')) {
                $map['a.province_id'] = input('province_id');
                $city = Db::name('region')->where('pid', $info['province_id'])->select();
                $this->assign('city_list', $city);
            }

            if (input('city_id')) {
                $map['a.city_id'] = input('city_id');
            }

            if (input('type')) {
                $map['a.type'] = input('type');
            }

            if (input('class_id')) {
                $map['a.class_id'] = input('class_id');
            }

            if (input('telphone')) {
                $map['a.telphone'] = input('telphone');
            }

            $list = Db::name('lease')
                ->alias('a')
                ->join('parameter b ', 'b.id= a.province_id', 'LEFT')
                ->join('region c ','c.id= a.province_id','LEFT')
                ->join('region d ','d.id= a.city_id','LEFT')
                ->join('member_list f ','f.member_list_id= a.member_list_id','LEFT')
                ->join('mechanical e ','e.id= a.mechanical_id','LEFT')
                ->join('brand g ','g.id= a.brand_id','LEFT')
                ->join('mechanical_model h ','h.id= a.mechanical_model_id','LEFT')
                ->where($map)
                ->order('create_time desc')
                ->field('a.*,b.name AS parameter_name,c.name AS province,d.name AS city,f.member_list_tel,e.mechanical_name,g.brand_name,h.model_name')
                ->paginate(15);
        } else {
            $list = Db::name('contract')
                ->where('status',1)
                ->order('create_time desc')
                ->paginate(15);
        }
//        p($list);
        //分页
        $this->assign('page', $list->render());
        $this->assign('list', $list);
        return $this->fetch();
    }


    /**
     * 添加
     * @return mixed
     */
    public function contractAdd()
    {
        if($_POST)
        {
            $data = [
                'contract_name'=> input('contract_name'),
                'main'=> input('main'),
                'amount'=> input('amount'),
                'start_time'=> strtotime( input('start_time')),
                'end_time'=> strtotime(input('end_time')),
                'auth'=> input('auth',0),
                'content'=> input('content'),
                'create_time'=>time(),
            ];
            $contractId=Db::name('contract')->insertGetId($data);
            $res=$this->upload($contractId);
            if(!$res){
                $this->success('添加成功',url('index'),1);
            }
        }
        else
        {
            return $this->fetch();
        }
    }


    /**
     * 接收图片
     */
    public function upload($contractId){
        if (!empty($_FILES["fileselect"])) { //提取文件域内容名称，并判断
            foreach ($_FILES["fileselect"]["name"] as $k => $v) {
                $path = "./uploads/"; //上传路径
                $path .= date('Ymd', time()) . '/';
                if (!file_exists($path))//文件夹不存在，先生成文件夹
                {
                    //检查是否有该文件夹，如果没有就创建，并给予最高权限
                    mkdir($path, 0700);
                }
                //将文件格式改为utf-8
                $v = iconv('utf-8', 'GBK', $v);
                //获取文件类型
                $ext = strtolower(substr(strrchr($v, '.'), 1));
                //允许上传的文件格式
                $typeArr = array("zip", "rar", "jpg", "jpeg", "gif", "png", "bmp", "mp4", "flv", "xls", "doc", "docx", "xlsx");
                if (!in_array($ext, $typeArr)) {
                    echo "请上传指定类型的文件！";
                    exit;
                }
                //原始文件名
                $name = explode('.', $v)[0];
                //日期
                $date=date('YmdHis',time());
                //获取ip
//                $ip=getIP();
//                $ip=str_replace(".","-",$ip);
                $ip='255_255_255_255';
                //生成12位随机字符串
                $randStr = str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghigklmnopqrstuvwxyz1234567890');
                $rand = substr($randStr, 0, 12);
                $filename = $path.$date.$ip.$rand.$name."." . $ext; //图片的完整路径
                //这里的操作是文件上传到服务器的操作，move_uploaded_file将临时文件移动到指定路径
                if (move_uploaded_file($_FILES["fileselect"]["tmp_name"][$k], $filename)) {
                    //echo "上传成功";
                    $filename = iconv('GBK', 'utf-8', $filename);
                    $filename=substr($filename,1);
                    $name=iconv('GBK', 'utf-8', $name);
                    $file_name = $name."." . $ext;
                    $data = array(
                        'uptime' => time(),
                        'path' => $filename,
                        'oa_id' => $contractId,
                        'type' => 1,
                        'file_name' => $file_name
                    );
                   Db::name('file_upload')->insert($data);
                }
            }
        }
    }

    /**
     * 显示信息详情
     * @return mixed
     */
    public function contractShow()
    {
        $map['id'] = input('id');
        $condition['oa_id'] = input('id');
        $result = Db::name('contract')
            ->where($map)
            ->find();
//            p($result);
        $file=Db::name('file_upload')
            ->where($condition)
            ->select();
        $this->assign('result', $result);
        $this->assign('file', $file);
        return $this->fetch();
    }


    /**
     * 修改
     * @return mixed
     */
    public function contractedit()
    {
        //如果修改了文件
        if($_FILES){
            $contractId=$this->contractupdate();
            $this->upload($contractId);
            $this->success('修改成功', url('index'), 1);
        }else{
            //如果仅修改了信息
            if (($_POST)) {
                $res=$this->contractupdate();
                //将文件也挪过去，待完成
                if($res){
                    $this->success('修改成功', url('index'), 1);
                }
            //否则打开展示修改页
            } else {
                $map['id'] = input('id');
                $condition['oa_id'] = input('id');
                $result = Db::name('contract')
                    ->where($map)
                    ->find();
                $file=Db::name('file_upload')
                    ->where($condition)
                    ->select();
                $this->assign('result', $result);
                $this->assign('file', $file);
                return $this->fetch();
            }
        }
    }

    /**
     * 修改并更新版本
     * @return int
     */
    public function contractupdate(){
        $map['id'] = input('id');
        $res = Db::name('contract')
            ->where($map)
            ->find();
        $data = [
            'contract_name'=> $res['contract_name'],
            'main'=> $res['main'],
            'amount'=> $res['amount'],
            'start_time'=> $res['start_time'],
            'end_time'=> $res['end_time'],
            'auth'=> $res['auth'],
            'content'=> $res['content'],
            'create_time'=>time(),
        ];
        $contractId=Db::name('contract')->insertGetId($data);
        $data = [
            'contract_name'=> input('contract_name'),
            'main'=> input('main'),
            'amount'=> input('amount'),
            'start_time'=> strtotime( input('start_time')),
            'end_time'=> strtotime(input('end_time')),
            'auth'=> input('auth',0),
            'content'=> input('content'),
        ];
        Db::name('contract')->where('id', $contractId)->update($data);
        return $contractId;
    }

    /**
     * 删除信息
     */
    public function contractDelete()
    {
        //获取要删除的ID
        $tid = input('id');
        $p = input('page');
        // 判断ID是否为空
        if(empty($tid)){
            $this->error('参数错误',Url('index',array('page'=>$p)),0);
        }
        //删除数据
        $res = Db::name('contract')->where('id',$tid)->delete();
        // 判断是否删除成功
        if($res){
            $this->success('删除成功',Url('index',array('page'=>$p)),1);
        }
        else{
            $this->error('删除失败',Url('index',array('page'=>$p)),0);
        }
    }

    /**
     * 状态激活
     */
    public function Lease_active()
    {
        //获取要修改的id
        $id = input("x");
        $newsmechanics_db = db("lease");
        //判断此用户状态情况
        $status=$newsmechanics_db->where(array('id'=>$id))->value('is_effective');
        if($status == 1){
            //禁止
            $statedata = array('is_effective'=>0);
            $auth_group=$newsmechanics_db->where(array('id'=>$id))->setField($statedata);
            $this->success('未激活',1,1);
        }else{
            //开启
            $statedata = array('is_effective'=>1);
            $auth_group=$newsmechanics_db->where(array('id'=>$id))->setField($statedata);
            $this->success('已激活',1,1);
        }
    }

    /**
     * 根据类型、品牌获取型号
     */
    public function getModle()
    {
        $mechanical_id = input('mechanical_id');
        $brand_id = input('brand_id');
        $map = [
            'mechanical_id' => $mechanical_id,
            'brand_id' => $brand_id
        ];
        $list = Db::name('mechanical_model')->where($map)->select();
        $html = '<option value="">请选择</option>';
        foreach($list as $key => $value)
        {
            $html .= "<option value=\"{$value['id']}\">{$value['model_name']}</option>";
        }
        echo $html;exit;

    }


    /**
     * 根据省获取城市
     */
    public function getCity()
    {
        $province_id = input('province_id');
        $zhi=array(2,25,27,32);
        if(in_array($province_id,$zhi)){
            $id = Db::name('region')->where('pid',$province_id)->field('id,name')->value('id');
            $list = Db::name('region')->where('pid',$id)->field('id,name')->select();
        }else{
            $list = Db::name('region')->where('pid',$province_id)->field('id,name')->select();
        }
        $html = '<option value="">请选择</option>';
        foreach($list as $key => $value)
        {
            $html .= "<option value=\"{$value['id']}\">{$value['name']}</option>";
        }
        echo $html;exit;
    }


    /**
     * 配置信息
     * @return mixed
     */
    public function setting()
    {
        if(request()->isAjax())
        {
            $options = input('options/a');
            $options['check_open'] = request()->post('check_open',0,'intval');
            $options['see_open'] = request()->post('see_open',0,'intval');
            Db::name('options')->where(array('option_name'=>'LeaseSetting'))->setField('option_value',serialize($options));
            $this->success('修改成功',url('setting'),1);
        }
        else
        {
            $sys = Db::name('options')->where(array('option_name'=>'LeaseSetting'))->value("option_value");

            if($sys)
            {
                $sys=unserialize($sys);
            }
            else
            {
                $sys =  [];
            }
            $this->assign('sys',$sys);
            return $this->fetch();
        }
    }


    /**
     * 参数管理
     * @return mixed
     */
    public function paramsSetting()
    {
        $map = [
            'module' => 3, //1机械买卖；2机械租赁；3租赁运输；4修理厂；5机械配件；6司机；7证件；8融资；
            'parent_id' => 0
        ];
        $list = Db::name('parameter')->where($map)->select();

        $this->assign('list',$list);

        return $this->fetch();
    }


    /**
     * 添加参数
     * @return mixed
     */
    public function paramsSettingAdd()
    {
        //有参数提交
        if(request()->isAjax())
        {
            $data = [
                'module' => 3,//1机械买卖；2机械租赁；3租赁运输；4修理厂；5机械配件；6司机；7证件；8融资；
                'name' => input('name'),
                'multi' => input('multi',0),
                'state' => input('state',0),
                'value' => '',
                'parent_id' => 0,
                 'type'=>input('type'),
                'input_type'=>input('input_type'),
            ];

            Db::name('parameter')->insert($data);


            $this->success('添加成功',url('paramsSetting'),1);
        }
        //没有参数提交显示页面
        else
        {
            return $this->fetch();
        }
    }


    /**
     * 修改参数
     * @return mixed
     */
    public function paramsSettingEdit()
    {
        $id = input('id');
        if(!id)
        {
            $this->redirect(url('paramsSetting'));
        }
        //有参数提交
        if(request()->isAjax())
        {
            $data = [
                'module' => 3,//1机械买卖；2机械租赁；3租赁运输；4修理厂；5机械配件；6司机；7证件；8融资；
                'name' => input('name'),
                'multi' => input('multi',0),
                'state' => input('state',0),
                'value' => '',
                'parent_id' => 0,
                 'type'=>input('type'),
                'input_type'=>input('input_type'),
            ];

            Db::name('parameter')->where(['id'=>$id])->update($data);


            $this->success('修改成功',url('paramsSetting'),1);
        }
        //没有参数提交显示页面
        else
        {
            $info = Db::name('parameter')->where('id',$id)->find();
            $this->assign('info',$info);
            $this->assign('id',$id);
            return $this->fetch();
        }
    }


    /**
     * 删除参数
     */
    public function paramsSettingDelete()
    {
        $id = input('id');
        if (empty($id)){
            $this->error('数据不存在',url('paramsSetting'),0);
        }
        Db::name('parameter')->where(array('id'=>input('id')))->delete();

        if($rst!==false){
            $this->success('删除成功',url('paramsSetting'),1);
        }else{
            $this->error('删除失败',url('paramsSetting'),0);
        }
    }

    /**
     * 查看参数值
     * @return mixed
     */
    public function paramsSettingValue()
    {
        $pid = input('pid');
        if(empty($pid))
        {
            $this->redirect(url('paramsSetting'));
        }
        $name = Db::name('parameter')->where('id',$pid)->value('name');
        $this->assign('name',$name);
        $this->assign('pid',$pid);

        $map = [
            'parent_id' => $pid,
        ];
        $list = Db::name('parameter')->where($map)->select();
        $this->assign('list',$list);

        return $this->fetch();
    }


    /***
     * 添加参数值
     * @return mixed
     */
    public function paramsSettingValueAdd()
    {
        $pid = input('pid');
        if(empty($pid))
        {
            $this->redirect(url('paramsSetting'));
        }
        //有参数提交
        if(request()->isAjax())
        {
            $data = [
                'module' => 3,//1机械买卖；2机械租赁；3租赁运输；4修理厂；5机械配件；6司机；7证件；8融资；
                'name' => input('name'),
                'multi' => 1,
                'state' => input('state',0),
                'value' => input('name'),
                'parent_id' => $pid
            ];

            Db::name('parameter')->insert($data);


            $this->success('添加成功',url('paramsSettingValue',array('pid'=>$pid)),1);
        }
        //没有参数提交显示页面
        else
        {
            $name = Db::name('parameter')->where('id',$pid)->value('name');
            $this->assign('name',$name);
            $this->assign('pid',$pid);
            return $this->fetch();
        }
    }


    /**
     * 修改参数值
     * @return mixed
     */
    public function paramssettingvalueedit()
    {
        $id = input('id');
        if(!id)
        {
            $this->redirect(url('paramsSettingValue',array('pid'=>input('pid'))));
        }

        //有参数提交
        if(request()->isAjax())
        {
            $data = [
                'module' => 3,//1机械买卖；2机械租赁；3租赁运输；4修理厂；5机械配件；6司机；7证件；8融资；
                'name' => input('name'),
                'multi' => 1,
                'state' => input('state',0),
                'value' => input('name'),
                'parent_id' => input('pid'),
            ];

            Db::name('parameter')->where(['id'=>$id])->update($data);


            $this->success('修改成功',url('paramsSetting'),1);
        }
        //没有参数提交显示页面
        else
        {
            $info = Db::name('parameter')->where('id',$id)->find();
            $this->assign('info',$info);
            $this->assign('id',$id);
            $name = Db::name('parameter')->where('id',input('pid'))->value('name');
            $this->assign('name',$name);
            return $this->fetch();
        }
    }


    /**
     * 修改参数状态（开启关闭）
     */
    public function paramsSettingState()
    {
        $id = input('x');
        $status = Db::name('parameter')->where(array('id'=>$id))->value('state');//判断当前状态情况
        if($status==1){
            $statedata = array('state'=>0);
            $auth_group=Db::name('parameter')->where(array('id'=>$id))->setField($statedata);
            $this->success('状态禁止',1,1);
        }else{
            $statedata = array('state'=>1);
            $auth_group=Db::name('parameter')->where(array('id'=>$id))->setField($statedata);
            $this->success('状态开启',1,1);
        }
    }

    /**
     * 删除参数
     */
    public function paramsSettingValueDelete()
    {
        $id = input('id');
        if (empty($id)){
            $this->error('数据不存在',url('paramsSetting'),0);
        }
        Db::name('parameter')->where(array('id'=>input('id')))->delete();

        if($rst!==false){
            $this->success('删除成功',url('paramsSettingValue',array('pid'=>input('pid'))),1);
        }else{
            $this->error('删除失败',url('paramsSettingValue',array('pid'=>input('pid'))),0);
        }
    }
}