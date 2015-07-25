<?php
/**
 * Created by PhpStorm.
 * User: caipeichao
 * Date: 14-3-10
 * Time: PM9:14
 */

namespace Spbcn\Controller;

use Think\Controller;
use Think\Hook;
use Think\Exception;
use Common\Exception\ApiException;

class ActionController extends Controller
{
    public function index()
    {
    }

    //创建动作文件存放路路径
    //从文件夹目录生成动作文件表
    public function createActionDb()
    {
        //windows文件系统编码格式是'gb2312',需要转为utf-8
        $filenames = get_filenamesbydir(iconv("UTF-8", "gb2312", 'D:/007训练动作'));

        $module = M('Spbcn_actionfiles');

        foreach ($filenames as $value) {
//            var_dump($value);
            $strArray = explode('/', $value);
            if (count($strArray) > 4) {
                //var_dump($strArray[2]);
                $fileNameArray = explode('.', $strArray[5]);

                //检测编码格式，检测结果为'EUC-CN'
//                $encode = mb_detect_encoding($value, "UTF-8, GB2312, GBK", true);
//                var_dump($encode);

                $resultArray = array(
                    'body' => iconv("EUC-CN", "UTF-8", $strArray[2]),
                    'bodydetail' => iconv("EUC-CN", "UTF-8", $strArray[3]),
                    'action' => iconv("EUC-CN", "UTF-8", $strArray[4]),
                    'filename' => iconv("EUC-CN", "UTF-8", $fileNameArray[0]),
                    'filetype' => iconv("EUC-CN", "UTF-8", $fileNameArray[1]),
                );
                var_dump($resultArray);
//                var_dump(urldecode(json_encode($resultArray)));
//                $module -> create($resultArray);
                $module -> add($resultArray);
            }
        }

        echo '成功';
    }

    //创建动作文字描述库模板
    //从文件夹目录生成文字描述表模板，用户zn等帮忙录入
    public function createActionTextDb()
    {
        //windows文件系统编码格式是'gb2312',需要转为utf-8
        $filenames = get_filenamesbydir(iconv("UTF-8", "gb2312", 'D:/007训练动作'));

        $module = M('Spbcn_actionguide');

        foreach ($filenames as $value) {
            $strArray = explode('/', $value);
            if (count($strArray) > 4) {
                $fileNameArray = explode('.', $strArray[5]);
                $fileName = iconv("EUC-CN", "UTF-8", $fileNameArray[0]);
                $fileType = iconv("EUC-CN", "UTF-8", $fileNameArray[1]);
                if('doc'== $fileType || 'docx' == $fileType){
                    //检测编码格式，检测结果为'EUC-CN'
//                $encode = mb_detect_encoding($value, "UTF-8, GB2312, GBK", true);
//                var_dump($encode);

                    $resultArray = array(
                        'body' => iconv("EUC-CN", "UTF-8", $strArray[2]),
                        'bodydetail' => iconv("EUC-CN", "UTF-8", $strArray[3]),
                        'action' => iconv("EUC-CN", "UTF-8", $strArray[4]),
                    );
//                var_dump(urldecode(json_encode($resultArray)));
//                $module -> create($resultArray);
                    $module -> add($resultArray);
//                var_dump($fileName.'.'.$fileType);
                }
            }
        }

        echo '成功';
    }

    //查询动作文件列表
    //根据部位、动作等查询文件列表
    public function getActionFilesList()
    {
        $body = I('body');
        if(!$body){
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason'=>'缺少body参数',
                'data'=>'',
            ));
            return false;
        }

        $bodydetail = I('bodydetail');
        if(!$bodydetail){
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason'=>'缺少bodydetail参数',
                'data'=>'',
            ));
            return false;
        }

        $action = I('action');
        if(!$action){
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason'=>'缺少action参数',
                'data'=>'',
            ));
            return false;
        }

        $module = M('Spbcn_action');

        $where = array(
            'body' => $body,
            'bodydetail' => $bodydetail,
            'action' => $action
        );

        $pathHeader = $body.'/'.$bodydetail.'/'.$action.'/';

        $result = $module -> where($where) -> select();

        foreach($result as $value){
            if('png'==$value['filetype']){
                $png[] = $pathHeader.$value['filename'].'.'.$value['filetype'];
            }
            if('mp4'==$value['filetype']){
                $video[] = $pathHeader.$value['filename'].'.'.$value['filetype'];
            }
            if('gif'==$value['filetype']){
                $gif[] = $pathHeader.$value['filename'].'.'.$value['filetype'];
            }
        }

        $data = array(
            'png' => $png,
            'mp4' => $video,
            'gif' => $gif
        );

        echo json_encode(array(
            'status'=> 'SUCCESS',
            'reason' => '',
            'date' => date('Y-m-d'),
            'data' => $data,
        ));
    }

    //查询动作文字描述
    //根据部位、动作等查询文字说明
    public function getActionGuideText(){

        $body = I('body');
        if(!$body){
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason'=>'缺少body参数',
                'data'=>'',
            ));
            return false;
        }

        $bodydetail = I('bodydetail');
        if(!$bodydetail){
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason'=>'缺少bodydetail参数',
                'data'=>'',
            ));
            return false;
        }

        $action = I('action');
        if(!$action){
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason'=>'缺少action参数',
                'data'=>'',
            ));
            return false;
        }

        $module = M('Spbcn_actionguide');

        $where = array(
            'body' => $body,
            'bodydetail' => $bodydetail,
            'action' => $action
        );

        $result = $module -> where($where) -> select();

        echo json_encode(array(
            'status'=> 'SUCCESS',
            'reason' => '',
            'date' => date('Y-m-d'),
            'data' => $result,
        ));

    }

    //导入身体部位编号以及动作编号
    //用张悦的库来匹配数据库
    //已弃用
    public function setActionCode(){
        $jsonStringMale = '[{"title":"男性背阔肌","pid":100,"parts":[{"partname":"并握引体向上","spid":10001},{"partname":"俯身杠铃划船","spid":10002},{"partname":"俯身壶铃划船","spid":10003},{"partname":"俯身支撑单臂哑铃划船","spid":10004},{"partname":"壶铃单臂抛甩","spid":10005},{"partname":"壶铃双臂抛甩","spid":10006},{"partname":"健身球滚轮","spid":10007},{"partname":"宽握引体向上","spid":10008},{"partname":"史密斯机俯身划船","spid":10009},{"partname":"哑铃屈腿硬拉","spid":10010},{"partname":"站姿弹力绳划船","spid":10011},{"partname":"站姿直臂下压","spid":10012},{"partname":"坐姿宽握距下拉","spid":10013},{"partname":"坐姿器械划船","spid":10014},{"partname":"坐姿器械下拉","spid":10015},{"partname":"坐姿绳索划船","spid":10016},{"partname":"坐姿下拉（反握）","spid":10017}]},{"title":"男性肱三头肌","pid":101,"parts":[{"partname":"凳上体后臂屈伸","spid":10101},{"partname":"俯身弹力带臂屈伸","spid":10102},{"partname":"俯身支撑哑铃臂屈伸","spid":10103},{"partname":"夹肘俯卧撑","spid":10104},{"partname":"健身球俯卧撑","spid":10105},{"partname":"史密斯机仰卧推举","spid":10106},{"partname":"双杠臂屈伸","spid":10107},{"partname":"仰卧杠铃窄握距推举","spid":10108},{"partname":"仰卧曲柄杠铃臂屈伸","spid":10109},{"partname":"仰卧双臂杠铃臂屈伸","spid":10110},{"partname":"站姿单臂绳索屈伸","spid":10111},{"partname":"站姿颈后哑铃臂屈伸","spid":10112},{"partname":"站姿绳索臂屈伸","spid":10113},{"partname":"站姿绳索臂屈伸（粗绳）","spid":10114},{"partname":"站姿绳索头上臂屈伸","spid":10115},{"partname":"站姿V把臂屈伸","spid":10116},{"partname":"坐姿头上哑铃双臂臂屈伸","spid":10117}]},{"title":"男性股四头肌","pid":102,"parts":[{"partname":"侧跳跃","spid":10201},{"partname":"单臂哑铃对侧上举","spid":10202},{"partname":"单腿深蹲","spid":10203},{"partname":"弹力绳侧弓步","spid":10204},{"partname":"杠铃屈腿硬拉","spid":10205},{"partname":"杠铃深蹲","spid":10206},{"partname":"壶铃高翻","spid":10207},{"partname":"壶铃胸前深蹲","spid":10208},{"partname":"靠球深蹲","spid":10209},{"partname":"器械腿举（倒蹬）","spid":10210},{"partname":"器械腿举（平蹬）","spid":10211},{"partname":"史密斯机箭步蹲","spid":10212},{"partname":"史密斯机深蹲","spid":10213},{"partname":"土耳其起立","spid":10214},{"partname":"哑铃侧弓步","spid":10215},{"partname":"哑铃弓步走","spid":10216},{"partname":"哑铃交替前弓步","spid":10217},{"partname":"自重深蹲","spid":10218},{"partname":"坐姿器械腿屈伸","spid":10219}]},{"title":"男性肩部","pid":103,"parts":[{"partname":"弹力带侧平举","spid":10301},{"partname":"俯身绳索侧平举","spid":10302},{"partname":"站姿杠铃划船","spid":10303},{"partname":"站姿杠铃前平举","spid":10304},{"partname":"站姿绳索侧平举","spid":10305},{"partname":"站姿绳索前平举","spid":10306},{"partname":"站姿哑铃侧平举","spid":10307},{"partname":"站姿哑铃前平举","spid":10308},{"partname":"坐姿杠铃肩部推举","spid":10309},{"partname":"坐姿器械反式飞鸟","spid":10310},{"partname":"坐姿器械肩部推举","spid":10311},{"partname":"坐姿史密斯机肩部推举","spid":10312},{"partname":"坐姿史密斯推肩（颈后）","spid":10313},{"partname":"坐姿哑铃阿诺德推举","spid":10314},{"partname":"坐姿哑铃肩部推举","spid":10315}]},{"title":"男性臀 股二头肌","pid":104,"parts":[{"partname":"侧跳跃","spid":10201},{"partname":"单臂哑铃对侧上举","spid":10202},{"partname":"单腿深蹲","spid":10203},{"partname":"弹力绳侧弓步","spid":10204},{"partname":"俯卧器械腿弯举","spid":10401},{"partname":"杠铃屈腿硬拉","spid":10205},{"partname":"杠铃深蹲","spid":10206},{"partname":"杠铃直腿硬拉","spid":10402},{"partname":"壶铃高翻","spid":10207},{"partname":"壶铃胸前深蹲","spid":10208},{"partname":"健身球仰卧顶髋","spid":10403},{"partname":"健身球仰卧静态支撑","spid":10404},{"partname":"靠球深蹲","spid":10209},{"partname":"器械腿举（倒蹬）","spid":10210},{"partname":"器械腿举（平蹬）","spid":10211},{"partname":"史密斯机箭步蹲","spid":10212},{"partname":"史密斯机深蹲","spid":10213},{"partname":"土耳其起立","spid":10214},{"partname":"哑铃侧弓步","spid":10215},{"partname":"哑铃弓步走","spid":10216},{"partname":"哑铃交替前弓步","spid":10217},{"partname":"仰卧负重顶髋","spid":10405},{"partname":"站姿绳索臀举","spid":10406},{"partname":"自重深蹲","spid":10218},{"partname":"坐姿器械腿弯举","spid":10407}]},{"title":"男性下背部","pid":105,"parts":[{"partname":"俯卧挺身","spid":10501},{"partname":"壶铃单臂抛甩","spid":10502},{"partname":"壶铃双臂抛甩","spid":10503},{"partname":"罗马椅挺身","spid":10504},{"partname":"山羊挺身","spid":10505},{"partname":"哑铃屈腿硬拉","spid":10506}]},{"title":"男性小腿","pid":106,"parts":[{"partname":"腿举机小腿蹬举","spid":10601},{"partname":"坐姿哑铃提踵","spid":10602}]},{"title":"男性斜方肌","pid":107,"parts":[{"partname":"站姿杠铃耸肩","spid":10701}]},{"title":"男性胸部","pid":108,"parts":[{"partname":"凳上俯卧撑","spid":10801},{"partname":"俯卧撑","spid":10802},{"partname":"俯卧撑架","spid":10803},{"partname":"健身球俯卧撑 脚上","spid":10804},{"partname":"器械上斜推胸","spid":10805},{"partname":"史密斯机仰卧平板推举","spid":10806},{"partname":"史密斯机仰卧上斜板推举","spid":10807},{"partname":"双杠臂屈伸","spid":10808},{"partname":"双药球俯卧撑","spid":10809},{"partname":"仰卧杠铃平板推举","spid":10810},{"partname":"仰卧杠铃上斜板推举","spid":10811},{"partname":"仰卧平板哑铃飞鸟","spid":10812},{"partname":"仰卧平板哑铃推举","spid":10813},{"partname":"仰卧上斜板哑铃飞鸟","spid":10814},{"partname":"仰卧上斜板哑铃推举","spid":10815},{"partname":"仰卧下斜板哑铃飞鸟","spid":10816},{"partname":"仰卧下斜板哑铃推举","spid":10817},{"partname":"仰卧哑铃直臂上拉","spid":10818},{"partname":"药球换手俯卧撑","spid":10819},{"partname":"站姿龙门架夹胸（上胸）","spid":10820},{"partname":"站姿龙门架夹胸（下胸）","spid":10821},{"partname":"站姿龙门架夹胸（中胸）","spid":10822},{"partname":"坐姿器械夹胸","spid":10823},{"partname":"坐姿器械胸部推举","spid":10824}]},{"title":"男腹部","pid":109,"parts":[{"partname":"侧卧举腿收腹","spid":10901},{"partname":"侧卧支撑提髋","spid":10902},{"partname":"侧握举腿收腹（单腿）","spid":10903},{"partname":"跪姿腹肌轮收腹","spid":10904},{"partname":"跪姿绳索收腹","spid":10905},{"partname":"健身球平板支撑","spid":10906},{"partname":"健身球屈膝收腹","spid":10907},{"partname":"健身球直腿收腹","spid":10908},{"partname":"两侧抛球","spid":10909},{"partname":"平板支撑","spid":10910},{"partname":"上斜仰卧举腿收腹","spid":10911},{"partname":"悬垂举腿（屈腿）","spid":10912},{"partname":"悬垂举腿（直腿）","spid":10913},{"partname":"仰卧健身球收腹","spid":10914},{"partname":"仰卧举腿收腹","spid":10915},{"partname":"仰卧卷腹","spid":10916},{"partname":"仰卧两头起收腹","spid":10917},{"partname":"仰卧起坐俄罗斯收腹","spid":10918},{"partname":"仰卧双腿交叉收腹","spid":10919},{"partname":"仰卧元宝收腹","spid":10920},{"partname":"仰卧自行车收腹","spid":10921},{"partname":"药球侧转体收腹","spid":10922},{"partname":"药球俄罗斯收腹","spid":10923},{"partname":"药球伐木","spid":10924},{"partname":"药球两侧交替","spid":10925},{"partname":"直立侧身哑铃提拉","spid":10926},{"partname":"坐姿凳上举腿收腹","spid":10927}]},{"title":"男性正面肱二头肌","pid":110,"parts":[{"partname":"托臂曲杠宽握弯举","spid":11001},{"partname":"托臂曲杠窄握弯举","spid":11002},{"partname":"托臂直杠弯举","spid":11003},{"partname":"哑铃集中弯举","spid":11004},{"partname":"站姿弹力绳弯举","spid":11005},{"partname":"站姿宽握 曲杠弯举","spid":11006},{"partname":"站姿窄握曲杠弯举","spid":11007},{"partname":"站姿直杠弯举","spid":11008},{"partname":"直立绳索臂弯举","spid":11009},{"partname":"直立哑铃交替弯举","spid":11010}]}]';
        $jsonStringFemale = '[{"title":"女性背阔肌","pid":200,"parts":[{"partname":"单臂哑铃对侧上举","spid":20001},{"partname":"俯身杠铃划船","spid":20002},{"partname":"俯身站立单臂哑铃划船","spid":20003},{"partname":"俯身支撑单臂哑铃划船","spid":20004},{"partname":"哑铃俯卧撑划船","spid":20005},{"partname":"站姿绳索下压","spid":20006},{"partname":"助力引体向上","spid":20007},{"partname":"坐姿宽握下拉","spid":20008},{"partname":"坐姿器械划船","spid":20009},{"partname":"T型举腿划船","spid":20010}]},{"title":"女性肱三头肌","pid":201,"parts":[{"partname":"俯身弹力绳臂屈伸","spid":20101},{"partname":"俯身支撑单臂哑铃臂屈伸","spid":20102},{"partname":"屈膝俯卧撑","spid":20103},{"partname":"站姿绳索下压","spid":20104},{"partname":"站姿哑铃头上臂屈伸","spid":20105},{"partname":"坐姿颈后哑铃臂屈伸","spid":20106}]},{"title":"女性股四头肌","pid":202,"parts":[{"partname":"半蹲跳","spid":20201},{"partname":"凳上台阶","spid":20202},{"partname":"负重青蛙蹲","spid":20203},{"partname":"杠铃深蹲","spid":20204},{"partname":"弓步蹲哑铃上举","spid":20205},{"partname":"交替前弓步","spid":20206},{"partname":"交替前弓步弯举","spid":20207},{"partname":"深蹲单臂哑铃上举","spid":20208},{"partname":"深蹲双臂哑铃上举","spid":20209},{"partname":"深蹲药球上举","spid":20210},{"partname":"史密斯机深蹲","spid":20211},{"partname":"史密斯箭步蹲","spid":20212},{"partname":"史密斯屈腿硬拉","spid":20213},{"partname":"哑铃弓步走","spid":20214},{"partname":"哑铃屈腿硬拉","spid":20215},{"partname":"药球前弓步+后弓步","spid":20216},{"partname":"坐姿普拉提圈腿内收","spid":20217},{"partname":"坐姿器械腿举","spid":20218},{"partname":"坐姿器械腿内收","spid":20219},{"partname":"坐姿器械腿屈伸","spid":20220}]},{"title":"女性肱三头肌","pid":203,"parts":[{"partname":"俯身哑铃侧平举","spid":20301},{"partname":"站姿弹力带侧平举","spid":20302},{"partname":"站姿哑铃侧平举","spid":20303},{"partname":"站姿哑铃前平举","spid":20304},{"partname":"站姿哑铃推举","spid":20305},{"partname":"坐姿器械肩部推举","spid":20306},{"partname":"坐姿史密斯肩部推举","spid":20307}]},{"title":"女性股四头肌","pid":204,"parts":[{"partname":"单腿三点触地","spid":20401},{"partname":"单腿支撑哑铃硬拉","spid":20402},{"partname":"凳上台阶","spid":20202},{"partname":"俯卧器械腿弯举","spid":20403},{"partname":"负重青蛙蹲","spid":20203},{"partname":"杠铃深蹲","spid":20204},{"partname":"杠铃直腿硬拉","spid":20404},{"partname":"弓步蹲哑铃上举","spid":20205},{"partname":"跪姿弹力绳腿上举","spid":20405},{"partname":"跪姿腿上举","spid":20406},{"partname":"健身球仰卧顶髋","spid":20407},{"partname":"交替前弓步","spid":20206},{"partname":"交替前弓步弯举","spid":20207},{"partname":"三方向弓步蹲","spid":20408},{"partname":"深蹲单臂哑铃上举","spid":20208},{"partname":"深蹲双臂哑铃上举","spid":20209},{"partname":"深蹲药球上举","spid":20210},{"partname":"史密斯机宽站距深蹲","spid":20409},{"partname":"史密斯机直腿硬拉","spid":20410},{"partname":"史密斯箭步蹲","spid":20212},{"partname":"史密斯屈腿硬拉","spid":20213},{"partname":"哑铃弓步走","spid":20214},{"partname":"哑铃屈腿硬拉","spid":20215},{"partname":"哑铃直腿硬拉","spid":20411},{"partname":"仰卧健身球腿弯举","spid":20412},{"partname":"仰卧桥式支撑","spid":20413},{"partname":"仰卧屈膝抬髋","spid":20414},{"partname":"药球前弓步+后弓步","spid":20216},{"partname":"站姿绳索臀举","spid":20415},{"partname":"坐姿器械腿举","spid":20218},{"partname":"坐姿器械腿外展","spid":20416}]},{"title":"女性下背部","pid":205,"parts":[{"partname":"单臂哑铃对侧上举","spid":20001},{"partname":"俯卧挺身","spid":20501},{"partname":"罗马椅挺身","spid":20502},{"partname":"T型举腿划船","spid":20010}]},{"title":"女性小腿","pid":206,"parts":[{"partname":"腿举机小腿蹬举","spid":10601},{"partname":"坐姿哑铃提踵","spid":10602}]},{"title":"女性胸部","pid":208,"parts":[{"partname":"俯卧撑架","spid":20801},{"partname":"跪姿俯卧撑","spid":20802},{"partname":"史密斯机上斜板推举","spid":20803},{"partname":"史密斯机仰卧平板推举","spid":20804},{"partname":"仰卧平板哑铃飞鸟","spid":20805},{"partname":"仰卧平板哑铃推举","spid":20806},{"partname":"仰卧上斜板哑铃飞鸟","spid":20807},{"partname":"仰卧上斜板哑铃推举","spid":20808},{"partname":"药球换手俯卧撑","spid":20809},{"partname":"站姿弹力带夹胸","spid":20810},{"partname":"站姿弹力带胸部推举","spid":20811},{"partname":"站姿龙门架夹胸 上胸","spid":20812},{"partname":"站姿龙门架夹胸 下胸","spid":20813},{"partname":"站姿龙门架夹胸 中胸","spid":20814},{"partname":"直腿俯卧撑","spid":20815},{"partname":"坐姿普拉提圈夹胸","spid":20816},{"partname":"坐姿器械夹胸","spid":20817},{"partname":"坐姿器械胸部推举","spid":20818}]},{"title":"女腹部","pid":209,"parts":[{"partname":"侧支撑抬髋","spid":20901},{"partname":"侧支撑抬髋（静态）","spid":20902},{"partname":"俯身换手支撑","spid":20903},{"partname":"俯身前进","spid":20904},{"partname":"俯卧撑侧吸腿","spid":20905},{"partname":"俯卧撑侧移动","spid":20906},{"partname":"俯卧撑交替臂上举","spid":20907},{"partname":"俯卧撑吸腿跳","spid":20908},{"partname":"健身球平板支撑","spid":20909},{"partname":"健身球屈腿收腹","spid":20910},{"partname":"健身球仰卧收腹","spid":20911},{"partname":"桥式收腹抬髋","spid":20912},{"partname":"桥式支撑","spid":20913},{"partname":"桥式支撑侧点地","spid":20914},{"partname":"屈膝腹肌轮收腹","spid":20915},{"partname":"悬垂举腿（屈腿）","spid":20916},{"partname":"悬垂举腿（直腿）","spid":20917},{"partname":"仰卧侧摆腿","spid":20918},{"partname":"仰卧侧收腹","spid":20919},{"partname":"仰卧弹力带举腿","spid":20920},{"partname":"仰卧举腿","spid":20921},{"partname":"仰卧卷腹","spid":20922},{"partname":"仰卧双腿开合收腹","spid":20923},{"partname":"仰卧自行车收腹","spid":20924},{"partname":"药球俄罗斯收腹","spid":20925}]},{"title":"女性正面肱二头肌","pid":210,"parts":[{"partname":"站姿弹力带臂弯举","spid":21001},{"partname":"站姿杠铃弯举","spid":21002},{"partname":"站姿哑铃弯举","spid":21003}]}]';
        $jsonObjectMale = json_decode($jsonStringMale);
        $jsonObjectFemale = json_decode($jsonStringFemale);

        $module = M('Spbcn_action');

        foreach($jsonObjectMale as $value){
            $value = get_object_vars($value);
            $body = $value['title'];
            $bodyCode = $value['pid'];
            foreach($value['parts'] as $val){
                $val = get_object_vars($val);
                $action = $val['partname'];
                $actionCode = $val['spid'];
                $data = array(
                    'body' => '',
                    'bodydetail' => $body,
                    'action' => $action,
                    'pid' => $bodyCode,
                    'spid' => $actionCode,
                );

                $module -> add($data);
//                echo $body.' = '.$bodyCode .'/'.$bodyDetail.'='.$actionCode;
            }
        }
        foreach($jsonObjectFemale as $value){
            $value = get_object_vars($value);
            $body = $value['title'];
            $bodyCode = $value['pid'];
            foreach($value['parts'] as $val){
                $val = get_object_vars($val);
                $action = $val['partname'];
                $actionCode = $val['spid'];
                $data = array(
                    'body' => '',
                    'bodydetail' => $body,
                    'action' => $action,
                    'pid' => $bodyCode,
                    'spid' => $actionCode,
                );

                $module -> add($data);
            }
        }

        //var_dump($jsonObjectMale);
        //var_dump($jsonObjectFemale);
    }



    //创建动作列表actionlist模板
    public function createActionListDb()
    {
        //windows文件系统编码格式是'gb2312',需要转为utf-8
        $filenames = get_filesDir(iconv("UTF-8", "gb2312", 'D:/007训练动作'));

        $module = M('Spbcn_actionlist');

        foreach ($filenames as $value) {
            $strArray = explode('/', $value);
            if (count($strArray) > 4) {
                    //检测编码格式，检测结果为'EUC-CN'
//                $encode = mb_detect_encoding($value, "UTF-8, GB2312, GBK", true);
//                var_dump($encode);
                $resultArray = array(
                    'body' => iconv("EUC-CN", "UTF-8", $strArray[2]),
                    'bodydetail' => iconv("EUC-CN", "UTF-8", $strArray[3]),
                    'action' => iconv("EUC-CN", "UTF-8", $strArray[4]),
                );
//                var_dump(urldecode(json_encode($resultArray)));
//                $module -> create($resultArray);
                $module -> add($resultArray);
//                var_dump($fileName.'.'.$fileType);
            }
        }

        echo '成功';
    }

    //为动作文件表更新actionid
    public function setActionIdOfFiles(){
        $actionModule = M('Spbcn_actionlist');
        $actionFilesModule = M('Spbcn_actionfiles');

        $result = $actionModule -> select();

        foreach($result as $value){
            $bodydetail = $value['bodydetail'];
            $actionCode = $value['id'];

            $where = array(
                'bodydetail' => $bodydetail,
                'action' => $value['action']
            );

            $data = array(
                'actionId' => $actionCode
            );

            $actionFilesModule -> where($where) -> setField($data);
        }

    }

    //为文字说明增加动作编号
    public function setActionIdOfGuide()
    {
        $textModule = M('Spbcn_actionguide');
        $actionModule = M('Spbcn_actionlist');

        $result = $actionModule -> select();

        foreach($result as $value){
            $bodydetail = $value['bodydetail'];
            $actionCode = $value['id'];

            $where = array(
                'bodydetail' => $bodydetail,
                'action' => $value['action']
            );

            $data = array(
                'actionId' => $actionCode
            );

            $textModule -> where($where) -> setField($data);
        }
    }

    //查询动作列表
    public function getActionList()
    {
        $actionModule = M('Spbcn_actionlist');

        $actionFilesModule = M('Spbcn_actionfiles');

        $pathinfo = 'http://'.$_SERVER['SERVER_NAME'].__ROOT__;

        //$sex = I('sex');//0为男，1为女

        $module = M('Spbcn_actionbodylist');

//        if('0'==$sex){
//            $where['bodydetail'] = array('like', '男%');
//        } else{
//            $where['bodydetail'] = array('like', '女%');
//        }

        $where['bodydetail'] = array('like', '男%');

        $result = $module -> where($where) -> getField('id, bodydetail, bodyid');

        foreach($result as $value){
            $whereTmp['bodydetail'] = $value['bodydetail'];
            $actionDataArray = $actionModule -> where($whereTmp) -> select();
            foreach($actionDataArray as $key => $actionData){
                $actionId = $actionData['id'];
                $refId = $actionModule -> where('id='.$actionId) -> getField('refId');
                if($refId > 0){
                    $actionId = $refId;
                }
                $whereFiles['actionId'] = $actionId;
                $whereFiles['filename'] = array('like', 'xt%');
                $fileResult = $actionFilesModule -> where($whereFiles) -> find();
                //$actionDataArray[$key]['pic'] = $pathinfo.'/Uploads/spbcn/actions/'.$fileResult['body'].'/'.$fileResult['bodydetail'].'/'.$fileResult['action'].'/'.$fileResult['filename'].'.'.$fileResult['filetype'];
                $actionDataArray[$key]['pic'] = $pathinfo.'/Uploads/spbcn/actions/'.charsetTrans($fileResult['body']).'/'.charsetTrans($fileResult['bodydetail']).'/'.charsetTrans($fileResult['action']).'/'.charsetTrans($fileResult['filename']).'.'.$fileResult['filetype'];
            }


            $data['body'] = $value['bodydetail'];
            $data['bodyid'] = $value['bodyid'];
            $data['action'] = $actionDataArray;

            $resultFinal[] = $data;
        }

        $where['bodydetail'] = array('like', '女%');
        $result = $module -> where($where) -> getField('id, bodydetail, bodyid');

        foreach($result as $value){
            $whereTmp['bodydetail'] = $value['bodydetail'];
            $actionDataArray = $actionModule -> where($whereTmp) -> select();
            foreach($actionDataArray as $key => $actionData){
                $actionId = $actionData['id'];
                $refId = $actionModule -> where('id='.$actionId) -> getField('refId');
                if($refId > 0){
                    $actionId = $refId;
                }
                $whereFiles['actionId'] = $actionId;
                $whereFiles['filename'] = array('like', 'xt%');
                $fileResult = $actionFilesModule -> where($whereFiles) -> find();
//                $actionDataArray[$key]['pic'] = $pathinfo.'/Uploads/spbcn/actions/'.$fileResult['body'].'/'.$fileResult['bodydetail'].'/'.$fileResult['action'].'/'.$fileResult['filename'].'.'.$fileResult['filetype'];
                $actionDataArray[$key]['pic'] = $pathinfo.'/Uploads/spbcn/actions/'.charsetTrans($fileResult['body']).'/'.charsetTrans($fileResult['bodydetail']).'/'.charsetTrans($fileResult['action']).'/'.charsetTrans($fileResult['filename']).'.'.$fileResult['filetype'];
            }


            $data['body'] = $value['bodydetail'];
            $data['bodyid'] = $value['bodyid'];
            $data['action'] = $actionDataArray;

            $resultFinal[] = $data;
        }

        echo json_encode(array(
            'status'=> 'SUCCESS',
            'reason' => '',
            'date' => date('Y-m-d'),
            'data' => $resultFinal,
        ));
    }

    //根据actionid查询动作文件
    public function getActionFiles(){
        $actionId = I('actionId');
        if(!$actionId){
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason'=>'缺少actionid参数',
                'data'=>'',
            ));
            return false;
        }

        //查询是否有参考动作，如果有，则用参考动作id代替本动作
        $actionModule = M('Spbcn_actionlist');

        $refId = $actionModule -> where('id='.$actionId) -> getField('refId');

        if($refId > 0){
            $actionId = $refId;
        }

        $module = M('Spbcn_actionfiles');

        $where['actionId'] = $actionId;

        $result = $module -> where($where) -> select();

        $pathinfo = 'http://'.$_SERVER['SERVER_NAME'].__ROOT__;

        foreach($result as $value){
//            $data[] = $pathinfo.'/Uploads/spbcn/actions/'.$value['body'].'/'.$value['bodydetail'].'/'.$value['action'].'/'.$value['filename'].'.'.$value['filetype'];
            $data[] = $pathinfo.'/Uploads/spbcn/actions/'.charsetTrans($value['body']).'/'.charsetTrans($value['bodydetail']).'/'.charsetTrans($value['action']).'/'.charsetTrans($value['filename']).'.'.$value['filetype'];
        }

        echo json_encode(array(
            'status'=> 'SUCCESS',
            'reason' => '',
            'date' => date('Y-m-d'),
            'data' => $data,
        ));
    }

    //根据actionid查询动作文字描述
    public function getActionGuideTextByActionId(){
        $actionId = I('actionId');
        if(!$actionId){
            echo json_encode(array(
                'status'=> 'FAILURE',
                'reason'=>'缺少actionid参数',
                'data'=>'',
            ));
            return false;
        }

        //查询是否有参考动作，如果有，则用参考动作id代替本动作
        $actionModule = M('Spbcn_actionlist');

        $refId = $actionModule -> where('id='.$actionId) -> getField('refId');

        if($refId > 0){
            $actionId = $refId;
        }

        $module = M(Spbcn_actionguide);

        $where['actionId'] = $actionId;

        $result = $module -> where($where) -> select();

        echo json_encode(array(
            'status'=> 'SUCCESS',
            'reason' => '',
            'date' => date('Y-m-d'),
            'data' => $result,
        ));
    }

    //获取身体部位列表
    public function getActionBodyList()
    {
        $module = M('Spbcn_actionbodylist');

        $where['bodydetail'] = array('like', '男%');

        $result = $module -> where($where) -> getField('body', true);

        echo json_encode(array(
            'status'=> 'SUCCESS',
            'reason' => '',
            'date' => date('Y-m-d'),
            'data' => $result,
        ));
    }


}