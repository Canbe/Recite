<?php
namespace app\index\model;
use think\Model;
use think\Db;

class Words extends Model
{
    //获得随机单词列表
    public static function randomSelect($num){
        $str="SELECT * FROM words WHERE id >= ((SELECT MAX(id) FROM words)-(SELECT MIN(id) FROM words)) * RAND() + (SELECT MIN(id) FROM words)  LIMIT ?";

        $list = Db::query($str,[$num]);
        return $list;
    }

    //模糊搜索列表
    public static function likeSelect($en,$num){
        $en = '%%'.$en.'%%';
        $str = "select * from words where en like ? or trans like ? order by en limit ?";
        return  Db::query($str,[$en,$en,$num]);
    }

    //更新记录表分数
    public static function UpdateRecord($wordid,$userid,$score)
    {
        $str = "insert into record values (DEFAULT,?,?,?) ON DUPLICATE KEY UPDATE score = ?";

        return Db::query($str,[$wordid,$userid,$score,$score]);
    }

    //更新记录表相对分数
    public static function UpdateRelativeRecord($wordid,$userid,$score)
    {
        $str = "insert into record values (DEFAULT,?,?,?) ON DUPLICATE KEY UPDATE score = score + ?";

        return Db::query($str,[$wordid,$userid,$score,$score]);
    }

    public static function SelectWordList($userid,$order,$start,$lenght,$level)
    {
        $order = Common::getOrder($order);

        $str = "select 
        words.id as id,
        words.class as class,
        en,trans,
        sentence,
        link,
        ifNULL(record.score,0) as score 
        from words left join record on words.id = record.wordid and userid = ? where class <= ? ORDER BY ".$order." limit ?,?";

        return Db::query($str,[$userid,$level,$start,$lenght]);
    }

    public static function getWordsTotal(){
        return Db::query("select count(*)as total from words")[0]["total"];
    }

    //获得统计分类的单词
    public static function getStatisticClassWords()
    {
        return DB::query("select 
        count(*) as total,
        sum(IF(class=0,1,0)) as CEE,
        sum(IF(class=1,1,0)) as CET4,
        sum(IF(class=2,1,0)) as CET6
        from words")[0];
    }

    public static function SelectWordWithUser($en,$userid)
    {
        $str = "select words.id as id,en,trans,sentence,link,class,ifNULL(record.score,0) as score from words left join record on words.id = record.wordid and userid = ? where words.en  = ?";

        return Db::query($str,[$userid,$en]);
    }

    public static function SelectWord($en)
    {
        $str = "select * from words where en = ? ";

        return Db::query($str,[$en]);
    }

    public static function InsertWord($en,$trans,$sentence,$class)
    {
        $res = Db::query("insert into words (id,en,trans,sentence,link,class) values (default,?,?,?,'',?)",[$en,$trans,$sentence,$class]);
    }

    public static function UpdateWord($id,$en,$trans,$sentence,$link,$class)
    {
        $str = "update words set en = ?, trans = ?,sentence= ?,link= ? ,class = ? where id = ?";
        return Db::query($str,[$en,$trans,$sentence,$link,$class,$id]);
    }
}